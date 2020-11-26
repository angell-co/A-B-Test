<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\base;

use angellco\abtest\AbTest;
use angellco\abtest\services\Experiments;
use angellco\abtest\services\Sections;
use angellco\abtest\services\Test;

/**
 * Trait PluginTrait
 *
 * @property-read Experiments $experiments The experiments service
 * @property-read Test $test The test service
 *
 * @package angellco\abtest\base
 */
trait PluginTrait
{
    // Static Properties
    // =========================================================================

    /**
     * @var AbTest
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @return Experiments
     */
    public function getExperiments(): Experiments
    {
        return $this->get('experiments');
    }

    /**
     * @return Sections
     */
    public function getSections(): Sections
    {
        return $this->get('sections');
    }

    /**
     * @return Test
     */
    public function getTest(): Test
    {
        return $this->get('test');
    }

    // Private Methods
    // =========================================================================

    private function _setPluginComponents()
    {
        $this->setComponents([
            'experiments' => Experiments::class,
            'sections' => Sections::class,
            'test' => Test::class,
        ]);
    }
}
