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
use angellco\abtest\records\SectionDraft;
use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\helpers\Json;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\web\Cookie;

/**
 * Test service - everything to do with actively running a test.
 *
 * @property-read bool|string $activeCookiesAsHash
 * @property-read bool|array $activeCookies
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
     * @throws InvalidConfigException
     */
    public function cookie()
    {
        if (!$this->_getActiveExperiments()) {
            return;
        }

        $response = Craft::$app->getResponse();

        // Sort out the cookies - one for each experiment
        foreach ($this->_getActiveExperiments() as $activeExperiment) {

            $cookie = $this->_getCookie($activeExperiment['cookieName']);

            if (!$cookie) {

                $values = [];

                // For each section, pick the draft or control they should get
                foreach ($activeExperiment['sections'] as $section) {

                    $total = count($section['drafts']) + 1;

                    $r = mt_rand(1, $total);

                    if ($r === 1) {
                        $values[$section['id']] = [
                            'control' => true,
                            'draftId' => null,
                            'index' => 0,
                            'optimizeId' => $activeExperiment['optimizeId']
                        ];
                    } else {
                        $draftIndex = $r-2;
                        $values[$section['id']] = [
                            'control' => false,
                            'draftId' => $section['drafts'][$draftIndex]['id'],
                            'index' => $draftIndex+1,
                            'optimizeId' => $activeExperiment['optimizeId']
                        ];
                    }
                }

                // Create the cookie and add it
                /** @var Cookie $cookie */
                $cookie = Craft::createObject(array_merge(Craft::cookieConfig(), [
                    'class' => Cookie::class,
                    'name' => $activeExperiment['cookieName'],
                    'value' => Json::encode($values),
                ]));

                $response->getCookies()->add($cookie);
            }
        }
    }

    /**
     * Returns the alternative entry for this test based on the user’s cookie.
     *
     * @param Entry $entry
     * @return bool|ElementInterface|null
     */
    public function getAlternateEntry(Entry $entry)
    {
        if (!$this->_getActiveExperiments()) {
            return false;
        }

        // Find the applicable experiment and section for this entry
        $cookieName = null;
        $applicableSection = null;
        foreach ($this->_getActiveExperiments() as $activeExperiment) {
            foreach ($activeExperiment['sections'] as $section) {
                if ($section['sourceId'] === $entry->id) {
                    $cookieName = $activeExperiment['cookieName'];
                    $applicableSection = $section;
                    break;
                }
            }
        }

        // If there isn’t one, then bail
        if (!$cookieName) {
            return false;
        }

        $cookie = $this->_getCookie($cookieName);

        // If we still don’t have a cookie for whatever reason then default to showing the control by returning false
        if (!$cookie) {
            return false;
        }

        // Get the cookie data and filter out the part we need for this entry
        $cookieData = Json::decode($cookie->value);
        if (!isset($cookieData[$applicableSection['id']])) {
            return false;
        }
        $sectionData = $cookieData[$applicableSection['id']];

        // We have a testable session, so check if its control and bail if so
        if ($sectionData['control'] === true) {
            return false;
        }

        // Return the draft based on the draft ID stored in the cookie
        return Craft::$app->getElements()->getElementById($sectionData['draftId'], Entry::class);
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
    public function isDraftInExperiment(Entry $entry): bool
    {
        if(!$entry->getIsDraft()) {
            return false;
        }

        // If we have a SectionDraft then it must be part of an experiment
        if (!SectionDraft::findOne(['draftId' => $entry->draftId])) {
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
