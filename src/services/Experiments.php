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

use angellco\abtest\db\Table;
use angellco\abtest\models\Experiment;
use angellco\abtest\records\Experiment as ExperimentRecord;
use Craft;
use craft\base\MemoizableArray;
use craft\helpers\DateTimeHelper;
use yii\base\Component;

/**
 * Experiments service.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Experiments extends Component
{

    // Private Properties
    // =========================================================================

    /**
     * @var MemoizableArray|null
     * @see _experiments()
     */
    private $_experiments;


    // Public Methods
    // =========================================================================

    /**
     * Returns all of the experiment IDs.
     *
     * @return array
     */
    public function getAllExperimentIds(): array
    {
        return ArrayHelper::getColumn($this->getAllExperiments(), 'id');
    }

    /**
     * Returns all experiments.
     *
     * @return Experiment[]
     */
    public function getAllExperiments(): array
    {
        return $this->_experiments()->all();
    }

    /**
     * Returns an experiment by its ID.
     *
     * @param int $experimentId
     * @return Experiment|null
     */
    public function getExperimentById(int $experimentId)
    {
        return $this->_experiments()->firstWhere('id', $experimentId);
    }

    /**
     * Returns a plain array of active experiments.
     *
     * @return array
     */
    public function getActiveExperiments(): array
    {
        $currentTime = DateTimeHelper::currentTimeStamp();

        // TODO: cache this?
        $active = [];

        /** @var Experiment $experiment */
        foreach ($this->getAllExperiments() as $experiment) {

            // No dates
            if (!$experiment->startDate && !$experiment->endDate) {
                if ($exp = $this->_experimentWithDrafts($experiment)) {
                    $active[] = $exp;
                }
                continue;
            }

            // Both dates
            if ($experiment->startDate && $experiment->endDate) {

                if ($currentTime >= $experiment->startDate->format('U') && $currentTime <= $experiment->endDate->format('U')) {
                    if ($exp = $this->_experimentWithDrafts($experiment)) {
                        $active[] = $exp;
                    }
                    continue;
                }

            }

            // End date
            if ($experiment->endDate && $currentTime <= $experiment->endDate->format('U')) {
                if ($exp = $this->_experimentWithDrafts($experiment)) {
                    $active[] = $exp;
                }
                continue;
            }

            // Start date
            if ($experiment->startDate && $currentTime >= $experiment->startDate->format('U')) {
                if ($exp = $this->_experimentWithDrafts($experiment)) {
                    $active[] = $exp;
                }
            }

        }

        return $active;
    }

    /**
     * Saves an experiment.
     *
     * @param Experiment $model
     * @param bool|bool $runValidation
     * @return bool
     */
    public function saveExperiment(Experiment $model, bool $runValidation = true): bool
    {
        if ($model->id) {
            $record = ExperimentRecord::findOne($model->id);

            if (!$record) {
                throw new Exception(Craft::t('ab-test', 'No experiment exists with the ID “{id}”', ['id' => $model->id]));
            }
        } else {
            $record = new ExperimentRecord();
        }

        if ($runValidation && !$model->validate()) {
            Craft::info('Experiment not saved due to validation error.', __METHOD__);

            return false;
        }

        $record->name = $model->name;
        $record->startDate = $model->startDate;
        $record->endDate = $model->endDate;

        // Save it!
        $record->save(false);

        // Now that we have a record ID, save it on the model
        $model->id = $record->id;

        return true;
    }

    /**
     * Deletes an experiment by its ID.
     *
     * @param int $experimentId
     * @return bool
     * @throws \Throwable
     */
    public function deleteExperimentById(int $experimentId): bool
    {

        if (!$experimentId) {
            return false;
        }

        $experiment = $this->getExperimentById($experimentId);

        if (!$experiment) {
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {

            // TODO: delete the relations

            // Delete the experiment
            Craft::$app->getDb()->createCommand()
                ->delete(Table::EXPERIMENTS, ['id' => $experiment->id])
                ->execute();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Clear caches
        $this->_experiments = null;

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns a memoizable array of all experiments.
     *
     * @return MemoizableArray
     */
    private function _experiments(): MemoizableArray
    {
        if ($this->_experiments === null) {
            $experiments = [];
            $records = ExperimentRecord::find()
                ->orderBy(['name' => SORT_ASC])
                ->all();

            foreach ($records as $record) {
                $experiments[] = new Experiment($record->toArray([
                    'id',
                    'name',
                    'startDate',
                    'endDate',
                    'uid'
                ]));
            }

            $this->_experiments = new MemoizableArray($experiments);
        }

        return $this->_experiments;
    }

    /**
     * Returns an experiment as an array with drafts nested on it.
     *
     * @param Experiment $experiment
     * @return array|bool
     */
    private function _experimentWithDrafts(Experiment $experiment)
    {
        // Need to call the getters so that they get expanded
        if ($experiment->getDrafts() && $experiment->getCookieName()) {
            return $experiment->toArray(['*'], ['drafts','controlId','cookieName']);
        }

        return false;
    }

}
