<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\services;

use angellco\abtest\AbTest;
use angellco\abtest\db\Table;
use angellco\abtest\models\Section;
use angellco\abtest\records\Experiment as ExperimentRecord;
use angellco\abtest\records\Section as SectionRecord;
use angellco\abtest\records\SectionDraft;
use Craft;
use craft\helpers\StringHelper;
use Exception;
use yii\base\Component;

/**
 * Sections service.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Sections extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a section by its ID.
     *
     * @param int $sectionId
     * @return Section|null
     */
    public function getSectionById(int $sectionId)
    {
        $record = SectionRecord::findOne($sectionId);

        if (!$record) {
            return null;
        }

        return new Section($record->toArray([
            'id',
            'experimentId',
            'sourceId'
        ]));
    }

    /**
     * Returns a section by its source ID.
     *
     * @param int $sourceId
     * @return Section|null
     */
    public function getSectionBySourceId(int $sourceId)
    {
        $record = SectionRecord::findOne(['sourceId' => $sourceId]);

        if (!$record) {
            return null;
        }

        return new Section($record->toArray([
            'id',
            'experimentId',
            'sourceId'
        ]));
    }

    /**
     * Saves a section.
     *
     * @param Section $model
     * @param bool $runValidation
     * @return bool
     * @throws Exception
     */
    public function saveSection(Section $model, $runValidation = true): bool
    {
        if ($model->id) {
            $record = SectionRecord::findOne($model->id);

            if (!$record) {
                throw new \RuntimeException(Craft::t('ab-test', 'No section exists with the ID “{id}”', ['id' => $model->id]));
            }

            // For existing sections
        } else {
            $record = new SectionRecord();
        }

        if ($runValidation && !$model->validate()) {
            Craft::info('Section not saved due to validation error.', __METHOD__);

            return false;
        }

        // Prep the record
        $record->experimentId = $model->experimentId;
        $record->sourceId = $model->sourceId;

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();
        try {

            // Save it!
            $record->save(false);

            // Now that we have a record ID, save it on the model
            $model->id = $record->id;

            // Now clear out the current set of draft relations
            SectionDraft::deleteAll(['sectionId' => $model->id]);

            // And save the new ones
            foreach ($model->draftIds as $draftId) {
                $db->createCommand()->insert(Table::SECTION_DRAFTS, [
                    'sectionId' => $model->id,
                    'draftId' => $draftId
                ])->execute();
            }

            // Update the UID on the Experiment to clear the cookies
            $experimentRecord = ExperimentRecord::findOne($record->experimentId);
            if ($experimentRecord) {
                $experimentRecord->uid = StringHelper::UUID();
                $experimentRecord->save(false);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Deletes a section by its ID.
     *
     * @param int $sectionId
     * @return bool
     * @throws \Throwable
     */
    public function deleteSectionById(int $sectionId): bool
    {
        $section = $this->getSectionById($sectionId);

        if (!$section) {
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            // Update the UID on the Experiment to clear the cookies first
            $experimentRecord = ExperimentRecord::findOne($section->experimentId);
            if ($experimentRecord) {
                $experimentRecord->uid = StringHelper::UUID();
                $experimentRecord->save(false);
            }

            // Delete the section
            Craft::$app->getDb()->createCommand()
                ->delete(Table::SECTIONS, ['id' => $section->id])
                ->execute();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Deletes a section draft relationship.
     *
     * @param int $sectionId
     * @param int $draftId
     * @return bool
     * @throws \Throwable
     */
    public function deleteDraft(int $sectionId, int $draftId): bool
    {
        $section = $this->getSectionById($sectionId);

        if (!$section) {
            return false;
        }

        // If there is only one draft, then just delete the section
        if (count($section->getDrafts()) === 1) {
            return $this->deleteSectionById($section->id);
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            // Update the UID on the Experiment to clear the cookies first
            $experimentRecord = ExperimentRecord::findOne($section->experimentId);
            if ($experimentRecord) {
                $experimentRecord->uid = StringHelper::UUID();
                $experimentRecord->save(false);
            }

            // Delete the section
            Craft::$app->getDb()->createCommand()
                ->delete(Table::SECTION_DRAFTS, [
                    'sectionId' => $section->id,
                    'draftId' => $draftId,
                ])
                ->execute();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}
