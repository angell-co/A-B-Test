<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\services;

use angellco\abtest\AbTest;
use Craft;
use craft\elements\Entry;
use yii\base\Component;
use yii\web\Cookie;

/**
 * Test service - everything to do with actively running a test.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Test extends Component
{
    // Private Properties
    // =========================================================================

    /**
     * @var array|null
     */
    private $_activeExperiments;

    // Public Methods
    // =========================================================================

    public function cookie()
    {
        if (!$this->getActiveExperiments()) {
            return;
        }

        $request = Craft::$app->getRequest();
        $response = Craft::$app->getResponse();

        // Sort out the cookies - one for each experiment
        foreach ($this->getActiveExperiments() as $activeExperiment) {

            $cookieName = 'abtest_'.$activeExperiment['uid'];
            $cookie = $request->getCookies()->get($cookieName);

            if (!$cookie) {
                $cookie = new Cookie([
                    'name' => $cookieName
                ]);

                // Decide which draft they should get or if they get the control
                $total = count($activeExperiment['drafts']) + 1;

                $r = rand(1, $total);
                if ($r === 1) {
                    $cookie->value = 'control';
                } else {
                    $cookie->value = 'test_'.$activeExperiment['drafts'][$r-2]['uid'];
                }

                $response->getCookies()->add($cookie);
            }
        }
    }


    public function getActiveExperiments()
    {
        if ($this->_activeExperiments !== null) {
            return $this->_activeExperiments;
        }

        $this->_activeExperiments = AbTest::$plugin->getExperiments()->getActiveExperiments();

        return $this->_activeExperiments;
    }

    // At this point we should make sure we know which source entry IDs we’re bothered about running tests with
    // So, probably get all active experiments, get the control off them and bail if this ID doesn’t match
    // any of our controls - this should be very performant! Check caching in the active experiments route.
    public function getAlternateEntry(Entry $entry)
    {
        if (!$this->getActiveExperiments()) {
            return false;
        }

        // Find the applicable experiment for this entry
        $applicableExperiment = null;
        foreach ($this->getActiveExperiments() as $activeExperiment) {
            if ($activeExperiment['control']['id'] === $entry->id) {
                $applicableExperiment = $activeExperiment;
                break;
            }
        }

        // Control isn’t set, because getControl() hasn’t been called in Experiments::_experimentWithDrafts

        // getControl() is only used in the CP edit view, so we should probably find another way to do that bit
        // and ditch the method as otherwise if we force it to get set in Experiments::_experimentWithDrafts we end up
        // in a loop as the ElementQuery::EVENT_AFTER_POPULATE_ELEMENT event handler gets called again ...
        Craft::dd($this->getActiveExperiments()[0]['control']);

        // If there isn’t one, then bail
        if (!$applicableExperiment) {
            return false;
        }

        Craft::dd($applicableExperiment);

        $cookie = $request->getCookies()->get('abtest_1');
//
//                        if (!$cookie) {
//                            $cookie = $response->getCookies()->get('abtest_1');
//                        }
//
//                        if ($cookie && $cookie->value === 'test') {
//                            // TODO: Get draft IDs - this is currently getting the latest draft available, not one in
//                            // our test
//                            $query = Entry::find()
//                                ->draftOf($entry)
//                                ->siteId($entry->siteId)
//                                ->anyStatus()
//                                ->orderBy(['dateUpdated' => SORT_DESC])
//                                ->limit(1);
//                            $draftIds = $query->ids();
//                            if ($draftIds) {
//                                $selectedEntry = Craft::$app->getElements()->getElementById($draftIds[0]);
//                                $event->element = $selectedEntry;
//                            }
//                        }

    }

}
