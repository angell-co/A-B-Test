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
