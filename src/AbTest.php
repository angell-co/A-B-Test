<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run up A/B (split) or multivariate tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest;

use angellco\abtest\fields\PlainText as PlainTextField;
use angellco\abtest\fields\Assets as AssetsField;
use angellco\abtest\db\Table;
use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

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

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = PlainTextField::class;
                $event->types[] = AssetsField::class;
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_UNINSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    Craft::$app->db->createCommand()
                        ->dropTableIfExists(Table::CONTENT)
                        ->execute();
                    Craft::$app->db->createCommand()
                        ->dropTableIfExists(Table::RELATIONS)
                        ->execute();
                }
            }
        );

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
