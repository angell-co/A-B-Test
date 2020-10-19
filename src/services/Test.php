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
use angellco\abtest\records\ExperimentDraft;
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
        $request = Craft::$app->getRequest();
        $response = Craft::$app->getResponse();

        if (!$response->getIsOk() || !$request->getIsSiteRequest()) {
            return;
        }

        if (!$this->_getActiveExperiments()) {
            return;
        }

        // Sort out the cookies - one for each experiment
        foreach ($this->_getActiveExperiments() as $activeExperiment) {

            $cookie = $this->_getCookie($activeExperiment['cookieName']);

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

                $response->getCookies()->add($cookie);
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

        $cookie = $this->_getCookie($applicableExperiment['cookieName']);

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

    /**
     * Returns all the cookies that are currently actively in use.
     *
     * @return array|bool
     */
    public function getActiveCookies()
    {
        if (!$this->_getActiveExperiments()) {
            return false;
        }

        $cookies = [];
        foreach ($this->_getActiveExperiments() as $activeExperiment) {
            $cookies[] = $this->_getCookie($activeExperiment['cookieName']);
        }

        return $cookies;
    }

    /**
     * Returns all the active cookies as a hash of their name / value combinations.
     *
     * @return bool|string
     */
    public function getActiveCookiesAsHash()
    {
        $cookies = $this->getActiveCookies();

        if (!$cookies) {
            return false;
        }

        $arrayToHash = [];
        foreach ($cookies as $cookie) {
            $arrayToHash[] = $cookie->name . '_' . $cookie->value;
        }

        array_multisort($arrayToHash);

        return md5(json_encode($arrayToHash));
    }

    /**
     * Returns true if the Entry is a Draft and in an experiment.
     *
     * @param Entry $entry
     * @return bool
     */
    public function isDraftInExperiment(Entry $entry)
    {
        if(!$entry->getIsDraft()) {
            return false;
        }

        if (!ExperimentDraft::findOne(['draftId' => $entry->draftId])) {
            return false;
        }

        return true;
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

    /**
     * Gets a cookie from the request or failing that the response to catch those that have just been set.
     *
     * @param $name
     * @return Cookie|null
     */
    private function _getCookie($name)
    {
        $cookie = Craft::$app->getRequest()->getCookies()->get($name);

        if (!$cookie) {
            $cookie = Craft::$app->getResponse()->getCookies()->get($name);
        }

        return $cookie;
    }

}
