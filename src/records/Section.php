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
use yii\db\ActiveQueryInterface;

/**
 * @property int $id ID
 * @property int $experimentId
 * @property int $sourceId
 * @property ActiveQueryInterface $sectionDrafts
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

    /**
     * @return ActiveQueryInterface
     */
    public function getSectionDrafts(): ActiveQueryInterface
    {
        return $this->hasMany(SectionDraft::class, ['draftId' => 'id']);
    }
}
