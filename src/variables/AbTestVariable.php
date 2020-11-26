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
use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\Template;
use Twig\Markup;

class AbTestVariable
{

    // Public Methods
    // =========================================================================

    /**
     * Returns the string needed by Google Optimize.
     *
     * This can then be used in your Google Analytics scripts like so:
     *
     * ```js
     * ga('set', 'exp', '{{ craft.abtest.getOptimizeJs() }}')
     * ```
     *
     * Or, you can set it in the data layer via SEOmatic:
     *
     * ```twig
     * {% do seomatic.script.get('googleTagManager').dataLayer({
     *    'exp': craft.abtest.getOptimizeJs()
     * }) %}
     * ```
     *
     * @return bool|string
     */
    public function getOptimizeJs()
    {
        $test = AbTest::$plugin->getTest();

        $activeCookies = $test->getActiveCookies();

        if (!$activeCookies) {
            return false;
        }

        $optimizeExperiments = [];
        foreach ($activeCookies as $activeCookie) {
            $sections = Json::decode($activeCookie->value);
            foreach ($sections as $section) {
                if ($section['optimizeId']) {
                    $optimizeExperiments[$section['optimizeId']][] = $section['index'];
                }
            }
        }

        if (empty($optimizeExperiments)) {
            return false;
        }

        $experimentStrings = [];
        foreach ($optimizeExperiments as $id => $variants) {

            // There may be more than one variant index, so concat them with "-" for MVT tests
            $variantsStr = implode('-', array_values($variants));
            $experimentStrings[] = $id.'.'.$variantsStr;
        }

        // For multiple experiments at the same time, we concatenate with a !
        // @see https://stackoverflow.com/a/62024740/956784
        return implode('!', array_values($experimentStrings));
    }

}
