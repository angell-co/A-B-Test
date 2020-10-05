<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */
namespace angellco\abtest\records;

use angellco\abtest\db\Table;
use Craft;
use craft\db\ActiveRecord;

/**
 * BlockType Record
 *
 * @property int $id ID
 * @property string $name Name
 * @property string $startDate Start date
 * @property string $endDate End date
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Experiment extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @return string the table name
     */
    public static function tableName()
    {
        return Table::EXPERIMENTS;
    }
}