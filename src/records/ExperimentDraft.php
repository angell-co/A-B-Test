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
use craft\db\ActiveRecord;

/**
 * Experiment / draft relationship record
 *
 * @property int $experimentId
 * @property int $draftId
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class ExperimentDraft extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return Table::EXPERIMENTS_DRAFTS;
    }
}
