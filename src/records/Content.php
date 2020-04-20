<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run up A/B (split) or multivariate tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\records;

use angellco\abtest\AbTest;
use angellco\abtest\db\Table;
use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Content extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::CONTENT;
    }
}
