<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B (split) or multivariate tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest;

use Craft;
use craft\base\Plugin;

use yii\base\Event;

/**
 * Class AbTest
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 *
 */
class AbTest extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var AbTest
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::info(
            Craft::t(
                'ab-test',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

}
