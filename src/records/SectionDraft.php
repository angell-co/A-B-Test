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
use yii\db\ActiveQueryInterface;

/**
 * @property int $sectionId
 * @property int $draftId
 * @property ActiveQueryInterface $section
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class SectionDraft extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return Table::SECTION_DRAFTS;
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getSection(): ActiveQueryInterface
    {
        return $this->hasOne(Section::class, ['id' => 'sectionId']);
    }
}
