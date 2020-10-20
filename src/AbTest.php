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
use angellco\abtest\records\SectionDraft;
use angellco\abtest\services\Experiments;
use angellco\abtest\services\Test;
use angellco\abtest\variables\AbTestVariable;
use Craft;
use craft\base\Plugin;
use craft\db\Query;
use craft\db\Table;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\ElementEvent;
use craft\events\PopulateElementEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\TemplateEvent;
use craft\helpers\ConfigHelper;
use craft\services\Elements;
use craft\web\Application;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use putyourlightson\blitz\Blitz;
use putyourlightson\blitz\events\ResponseEvent;
use putyourlightson\blitz\events\SaveCacheEvent;
use putyourlightson\blitz\services\CacheRequestService;
use putyourlightson\blitz\services\GenerateCacheService;
use yii\base\Event;
use yii\db\Exception as DbException;
use yii\web\Cookie;

/**
 * Class AbTest
 *
 * @property Experiments $experiments The Experiments component.
 * @method Experiments getExperiments() Returns the Experiments component.
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

    // Static Properties
    // =========================================================================

    /**
     * @var AbTest
     */
    public static $plugin;
    public static $cookie;

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

        $this->_setPluginComponents();

        $request = Craft::$app->getRequest();
        $response = Craft::$app->getResponse();

        // Register the variable class
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('abtest', AbTestVariable::class);
            }
        );

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
        // TODO: refactor this out
        if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
            Craft::$app->getView()->hook('cp.entries.edit.details', function (&$context) {
                $html = '';

                /** @var  $entry Entry */
                $entry = $context['entry'];
                if ($entry !== null && !$entry->getIsDraft()) {

                    $experimentOptions = [];
                    $draftData = [];
                    $expDrafts = [];
                    $experiments = $this->getExperiments()->getAllExperiments();

                    if ($experiments) {
                        foreach ($experiments as $experiment) {
                            $experimentOptions[] = [
                                'label' => $experiment->name,
                                'value' => $experiment->id,
                                'checked' => false
                            ];
                        }

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

                            // Now we have the draft data we can get the relations records that already exist
                            // for those drafts, if there are any
                            $draftIds = array_column($draftData, 'draftId');
                            $expDraftRecords = SectionDraft::find()
                                ->where(['draftId' => $draftIds])
                                ->all();

                            if ($expDraftRecords) {
                                // Format them
                                foreach ($expDraftRecords as $expDraftRecord) {
                                    $expDrafts[] = [
                                        'id' => $expDraftRecord['id'],
                                        'experimentId' => $expDraftRecord['experimentId'],
                                        'draftId' => $expDraftRecord['draftId']
                                    ];
                                }

                                // Go through and select the experiment these drafts are related to
                                foreach ($experimentOptions as &$experimentOption) {
                                    if ($experimentOption['value'] === $expDrafts[0]['experimentId']) {
                                        $experimentOption['checked'] = true;
                                    }
                                }
                            }
                        }
                    }

                    $html .= Craft::$app->view->renderTemplate('ab-test/entry-sidebar', [
                        'experimentOptions' => $experimentOptions,
                        'drafts' => $draftData,
                        'experimentDrafts' => $expDrafts
                    ]);

                }

                return $html;
            });
        }

        // Cookie the user for all active experiments
        $test = $this->getTest();
        Event::on(Application::class, Application::EVENT_INIT,
            function() use($test) {
                $test->cookie();
            }
        );

        // When populating an element on the front-end, check the cookies to see if we need to swap in a draft
        Event::on(ElementQuery::class, ElementQuery::EVENT_AFTER_POPULATE_ELEMENT,
            function(PopulateElementEvent $event) use($request, $response, $test) {
                if ($response->getIsOk() && $request->getIsSiteRequest() && $event->element !== null && is_a($event->element, Entry::class)) {

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

        // Need a new event so we can get in front of the get value method and return a modded uri
//        Event::on(CacheRequestService::class, CacheRequestService::EVENT_BEFORE_GET_RESPONSE,
//            function(ResponseEvent $event) use($test) {
//                $test->cookie();
//                $cookieHash = $test->getActiveCookiesAsHash();
//                if ($cookieHash) {
//                    // Check if it already contains a query or not
//                    if (Craft::$app->getRequest()->getQueryStringWithoutPath()) {
//                        $siteUri->uri .= '&abtest=' . $cookieHash;
//                    } else {
//                        $siteUri->uri .= '?abtest=' . $cookieHash;
//                    }
//                }
//            }
//        );


        // TODO: when saving an experiment/draft relationship make sure to add element IDs and refresh


        // Make drafts purge the Blitz cache if needed
        Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT,
            /** @var ElementEvent $event */
            function($event) use ($test) {
                if ($event->element !== null && is_a($event->element, Entry::class)) {
                    // Check if its part of an experiment
                    if ($test->isDraftInExperiment($event->element)) {
                        Blitz::$plugin->refreshCache->addElementIds(Entry::class, [$event->element->id]);
                        Blitz::$plugin->refreshCache->refresh();
                    }
                }
            }
        );

        // Do we need this one? Don’t think so.
//        Event::on(GenerateCacheService::class, GenerateCacheService::EVENT_BEFORE_SAVE_CACHE,
//            function(SaveCacheEvent $event) use($test) {
//                Craft::dd($event);
//            }
//        );
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

}
