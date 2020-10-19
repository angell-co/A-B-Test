<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\variables;

use angellco\abtest\AbTest;
use craft\helpers\ArrayHelper;
use craft\helpers\Template;

class AbTestVariable
{

    // Public Methods
    // =========================================================================

    /**
     * Returns script to get the output of a URI.
     *
     * @param string $uri
     * @param array $params
     *
     * @return Markup
     */
    public function getOptimizeJs()
    {
        $test = AbTest::$plugin->getTest();

        $str = $test->getActiveCookies()[0]->value;

        $cookieParts = explode(':', $str);

        $id = end($cookieParts);

        return Template::raw("ga('set', 'exp', 'mhhcA-P-TZOv4MPNbgi9Bw.$id');");
    }

}
