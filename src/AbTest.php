<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B (split) or multivariate tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest;

use Craft;
use craft\base\Plugin;

use craft\db\Query;
use craft\db\Table;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\PopulateElementEvent;
use craft\events\TemplateEvent;
use craft\helpers\ConfigHelper;
use craft\web\Application;
use craft\web\View;
use yii\base\Event;
use yii\db\Exception as DbException;
use yii\web\Cookie;

/**
 * Class AbTest
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 *
 */
class AbTest extends Plugin
{
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
    public $hasCpSection = false;

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

        Event::on(Application::class, Application::EVENT_INIT,
            function() use($request, $response) {
                if ($response->getIsOk() && $request->getIsSiteRequest()) {
                    $cookie = $request->getCookies()->get('abtest_1');
                    if (!$cookie) {
                        $cookie = new Cookie([
                            'name' => 'abtest_1'
                        ]);

                        // Decide if control or not
                        if (rand(0, 1) === 0) {
                            $cookie->value = 'control';
                        } else {
                            $cookie->value = 'test';
                        }

                        $response->getCookies()->add($cookie);
                    }
                }
            }
        );

        Event::on(ElementQuery::class, ElementQuery::EVENT_AFTER_POPULATE_ELEMENT,
            function(PopulateElementEvent $event) use($request, $response) {

                if ($response->getIsOk() && $request->getIsSiteRequest() && $event->element !== null && is_a($event->element, Entry::class)) {

                    /** @var Entry $entry */
                    $entry = $event->element;

                    // Dumb check if its a draft ID so we don’t get a loop due to this event handler firing again when
                    // we populate the draft lower down - refactor obvs
                    if (!$entry->draftId) {

                        $cookie = $request->getCookies()->get('abtest_1');

                        if (!$cookie) {
                            $cookie = $response->getCookies()->get('abtest_1');
                        }

                        if ($cookie && $cookie->value === 'test') {
                            // Get draft IDs
                            $query = Entry::find()
                                ->draftOf($entry)
                                ->siteId($entry->siteId)
                                ->anyStatus()
                                ->orderBy(['dateUpdated' => SORT_DESC])
                                ->limit(1);
                            $draftIds = $query->ids();
                            if ($draftIds) {
                                $selectedEntry = Craft::$app->getElements()->getElementById($draftIds[0]);
                                $event->element = $selectedEntry;
                            }
                        }
                    }
                }
            }
        );

        // Entries sidebar
        if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
            Craft::$app->getView()->hook('cp.entries.edit.details', function (&$context) {
                $html = '';

                /** @var  $entry Entry */
                $entry = $context['entry'];
                if ($entry !== null) {

                    $drafts = Entry::find()
                        ->draftOf($entry)
                        ->siteId($entry->siteId)
                        ->anyStatus()
                        ->orderBy(['dateUpdated' => SORT_DESC])
                        ->limit(null)
                        ->all();

                    $n = count($drafts);

                    if ($drafts) {
                        $html .= Craft::$app->view->renderTemplate('ab-test/entry-sidebar', [
                            'drafts' => $drafts
                        ]);
                    }
                }

                return $html;
            });
        }


    }

}
