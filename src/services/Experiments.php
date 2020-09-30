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
                ]));
            }

            $this->_experiments = new MemoizableArray($experiments);
        }

        return $this->_experiments;
    }

}
