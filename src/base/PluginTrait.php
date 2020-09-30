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

use angellco\abtest\services\Experiments;
use angellco\abtest\AbTest;

/**
 * Trait PluginTrait
 *
 * @property-read Experiments $experiments The experiments service
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

    // Private Methods
    // =========================================================================

    private function _setPluginComponents()
    {
        $this->setComponents([
            'experiments' => Experiments::class,
        ]);
    }
}
