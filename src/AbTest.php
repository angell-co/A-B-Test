<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest;

use angellco\abtest\base\PluginTrait;
use angellco\abtest\models\Section;
use angellco\abtest\services\Experiments;
use angellco\abtest\services\Test;
use angellco\abtest\variables\AbTestVariable;
use angellco\abtest\web\assets\abtestui\AbTestUiAsset;
use Craft;
use craft\base\Plugin;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\ElementEvent;
use craft\events\PopulateElementEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\web\Application;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use putyourlightson\blitz\Blitz;
use putyourlightson\blitz\events\ResponseEvent;
use putyourlightson\blitz\services\CacheRequestService;
use yii\base\Event;

/**
 * Class AbTest
 *
 * @property Experiments $experiments The Experiments component.
 * @method Experiments getExperiments() Returns the Experiments component.
 * @property-read mixed $cpNavItem
 * @property Test $test The Test component.
 * @method Test getTest() Returns the Test component.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class AbTest extends Plugin
{
    // Traits
    // =========================================================================

    use PluginTrait;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $request = Craft::$app->getRequest();
        $response = Craft::$app->getResponse();

        $this->_setPluginComponents();
        $this->installGlobalEventListeners();

        if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
            $this->installCpEventListeners();
        }

        if (!$request->getIsConsoleRequest() && $request->getIsSiteRequest() && $response->getIsOk()) {
            $this->installSiteEventListeners();
        }

        // If blitz is installed, register the event listeners we need for that
        if (class_exists(Blitz::class)) {
            $this->installBlitzEventListeners();
        }
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        $ret = parent::getCpNavItem();

        $ret['label'] = Craft::t('ab-test', 'A/B Test');

        $ret['subnav']['experiments'] = [
            'label' => Craft::t('ab-test', 'Experiments'),
            'url' => 'ab-test/experiments'
        ];

        return $ret;
    }

    // Private Methods
    // =========================================================================

    /**
     * Global event listeners
     */
    protected function installGlobalEventListeners()
    {
        // Register the variable class
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('abtest', AbTestVariable::class);
            }
        );
    }

    /**
     * CP event listeners
     */
    protected function installCpEventListeners()
    {
        // Register the CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function(RegisterUrlRulesEvent $event) {
                $event->rules['ab-test/experiments'] = 'ab-test/experiments/index';
                $event->rules['ab-test/experiments/new'] = 'ab-test/experiments/edit';
                $event->rules['ab-test/experiments/<experimentId:\d+>'] = 'ab-test/experiments/edit';
            }
        );

        // Entries sidebar
        Craft::$app->getView()->hook('cp.entries.edit.details', function (&$context) {
            $html = '';

            /** @var  $entry Entry */
            $entry = $context['entry'];
            if ($entry !== null && !$entry->getIsDraft()) {

                $experimentOptions = [];
                $draftData = [];
                $experiments = $this->getExperiments()->getAllExperiments();
                $section = null;

                if ($experiments) {
                    foreach ($experiments as $experiment) {
                        $experimentOptions[] = [
                            'label' => $experiment->name,
                            'value' => $experiment->id,
                            'checked' => false
                        ];
                    }

                    // Drafts
                    $drafts = Entry::find()
                        ->draftOf($entry)
                        ->siteId($entry->siteId)
                        ->anyStatus()
                        ->orderBy(['id' => SORT_ASC])
                        ->limit(null)
                        ->all();

                    if ($drafts) {
                        $draftData = [];
                        foreach ($drafts as $draft) {
                            $draftData[] = [
                                'id' => $draft->id,
                                'draftId' => $draft->draftId,
                                'title' => $draft->draftName,
                                'note' => $draft->draftNotes,
                            ];
                        }
                    }

                    // Existing section
                    $section = $this->getSections()->getSectionBySourceId($entry->id);
                }

                if (!$section) {
                    $section = new Section(['sourceId' => $entry->id]);
                }

                $html .= Craft::$app->view->renderTemplate('ab-test/entry-sidebar', [
                    'experimentOptions' => $experimentOptions,
                    'drafts' => $draftData,
                    'section' => $section->toArray(['*'], ['drafts'])
                ]);

            }

            return $html;
        });
    }

    /**
     * Site event listeners
     */
    protected function installSiteEventListeners()
    {
        $test = $this->getTest();

        // Cookie the user for all active experiments
        Event::on(Application::class, Application::EVENT_INIT,
            function() use($test) {
                $test->cookie();
            }
        );

        // When populating an element on the front-end, check the cookies to see if we need to swap in a draft
        Event::on(ElementQuery::class, ElementQuery::EVENT_AFTER_POPULATE_ELEMENT,
            function(PopulateElementEvent $event) use($test) {
                if ($event->element !== null && is_a($event->element, Entry::class)) {

                    /** @var Entry $entry */
                    $entry = $event->element;

                    // Dumb check if its a draft ID so we don’t get a loop due to this event handler firing again when
                    // we populate the draft lower down
                    if (!$entry->draftId) {
                        $alternateEntry = $test->getAlternateEntry($entry);
                        if ($alternateEntry) {
                            $event->element = $alternateEntry;
                        }
                    }
                }
            }
        );
    }

    /**
     * Install Blitz-specific event listeners
     */
    protected function installBlitzEventListeners()
    {
        $test = AbTest::$plugin->getTest();

        // Modify the URI for Blitz so it caches alternative versions
        Event::on(CacheRequestService::class, CacheRequestService::EVENT_BEFORE_GET_RESPONSE,
            static function(ResponseEvent $event) use($test) {
                $test->cookie();
                $cookieHash = $test->getActiveCookiesAsHash();
                if ($cookieHash) {
                    // Check if it already contains a query or not
                    if (Craft::$app->getRequest()->getQueryStringWithoutPath()) {
                        $event->siteUri->uri .= '&abtest=' . $cookieHash;
                    } else {
                        $event->siteUri->uri .= '?abtest=' . $cookieHash;
                    }
                }
            }
        );


        // TODO: for blitz when saving an experiment/draft relationship make sure to add element IDs and refresh

        // Make drafts purge the Blitz cache if needed
        Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT,
            /** @var ElementEvent $event */
            static function($event) use ($test) {
                // Check if its an Entry and part of an Experiment
                if ($event->element !== null && is_a($event->element, Entry::class) && $test->isDraftInExperiment($event->element)) {
                    Blitz::$plugin->refreshCache->addElementIds(Entry::class, [$event->element->id]);
                    Blitz::$plugin->refreshCache->refresh();
                }
            }
        );
    }
}
