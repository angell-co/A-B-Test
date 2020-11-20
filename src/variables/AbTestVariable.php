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
     * Returns script to get the output of a URI.
     *
     * @return Markup
     */
    public function getOptimizeJs(): Markup
    {
        $test = AbTest::$plugin->getTest();

        $activeCookies = $test->getActiveCookies();
        $optimizeExperiments = [];
        foreach ($activeCookies as $activeCookie) {
            $sections = Json::decode($activeCookie->value);
            foreach ($sections as $section) {
                if ($section['optimizeId']) {
                    $optimizeExperiments[$section['optimizeId']][] = $section['index'];
                }
            }
        }

        $output = '';
        foreach ($optimizeExperiments as $id => $variants) {

            // There may be more than one variant index, so concat them with "-" for MVT tests
            $variantsStr = implode('-', array_values($variants));
            $output .= "ga('set', 'exp', '$id.$variantsStr');\n";
        }

        return Template::raw($output);
    }

}
