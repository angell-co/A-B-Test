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
use craft\db\Table;
use craft\elements\Entry;
use craft\helpers\Db;
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

    /**
     * Cookies the user for each active experiment.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function cookie()
    {
        if (!$this->_getActiveExperiments()) {
            return;
        }

        // Sort out the cookies - one for each experiment
        foreach ($this->_getActiveExperiments() as $activeExperiment) {

            $cookie = Craft::$app->getRequest()->getCookies()->get($activeExperiment['cookieName']);

            if (!$cookie) {

                // Decide which draft they should get or if they get the control
                $total = count($activeExperiment['drafts']) + 1;

                $r = rand(1, $total);
                if ($r === 1) {
                    $cookieValue = 'control';
                } else {
                    $cookieValue = 'test:'.$activeExperiment['drafts'][$r-2]['uid'];
                }

                // Create the cookie and add it
                $cookie = Craft::createObject(array_merge(Craft::cookieConfig(), [
                    'class' => 'yii\web\Cookie',
                    'name' => $activeExperiment['cookieName'],
                    'value' => $cookieValue,
                ]));

                Craft::$app->getResponse()->getCookies()->add($cookie);
            }
        }
    }

    /**
     * Returns the altenative entry for this test based on the user’s cookie.
     *
     * @param Entry $entry
     * @return bool|\craft\base\ElementInterface|null
     */
    public function getAlternateEntry(Entry $entry)
    {
        if (!$this->_getActiveExperiments()) {
            return false;
        }

        // Find the applicable experiment for this entry
        $applicableExperiment = null;
        foreach ($this->_getActiveExperiments() as $activeExperiment) {
            if ($activeExperiment['controlId'] === $entry->id) {
                $applicableExperiment = $activeExperiment;
                break;
            }
        }

        // If there isn’t one, then bail
        if (!$applicableExperiment) {
            return false;
        }

        $cookie = Craft::$app->getRequest()->getCookies()->get($applicableExperiment['cookieName']);
        if (!$cookie) {
            $cookie = Craft::$app->getResponse()->getCookies()->get($applicableExperiment['cookieName']);
        }

        // If we still don’t have a cookie for whatever reason then default to showing the control by returning false
        if (!$cookie) {
            return false;
        }

        // We have a testable session, so check if its control and bail if so
        if (strpos($cookie->value, 'test:') === false) {
            return false;
        }

        // So, we now know we need to return the draft based on what is in the cookie
        $cookieParts = explode(':', $cookie->value, 2);
        $draftEntryUid = $cookieParts[1];
        $entryId = Db::idByUid(Table::ELEMENTS, $draftEntryUid);
        return Craft::$app->getElements()->getElementById($entryId, Entry::class);
    }

    // Private Methods
    // =========================================================================

    /**
     * @see Experiments::getActiveExperiments()
     * @return array|null
     */
    private function _getActiveExperiments()
    {
        if ($this->_activeExperiments !== null) {
            return $this->_activeExperiments;
        }

        $this->_activeExperiments = AbTest::$plugin->getExperiments()->getActiveExperiments();

        return $this->_activeExperiments;
    }

}
