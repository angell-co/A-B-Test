<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run up A/B (split) or multivariate tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\PlainText as CraftPlainText;

/**
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class PlainText extends CraftPlainText
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ab-test', 'Plain Text (A/B Test)');
    }

    /**
     * TODO
     *
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return parent::normalizeValue($value, $element);
    }

    /**
     * TODO
     *
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('_components/fieldtypes/PlainText/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
            ]);
    }
}
