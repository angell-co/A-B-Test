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
        if (!$this->_getActiveExperiments()) {
            return;
        }

        $request = Craft::$app->getRequest();
        $response = Craft::$app->getResponse();

        // Sort out the cookies - one for each experiment
        foreach ($this->_getActiveExperiments() as $activeExperiment) {

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


    private function _getActiveExperiments()
    {
        if ($this->_activeExperiments !== null) {
            return $this->_activeExperiments;
        }

        $this->_activeExperiments = AbTest::$plugin->getExperiments()->getActiveExperiments();

        return $this->_activeExperiments;
    }

}
