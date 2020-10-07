<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\models;

use angellco\abtest\records\ExperimentDraft;
use Craft;
use craft\base\Model;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Entry;

/**
 * Experiment model.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Experiment extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var string|null Name
     */
    public $name;

    /**
     * @var \DateTime Start date
     */
    public $startDate;

    /**
     * @var \DateTime End date
     */
    public $endDate;

    /**
     * @var int|null Field layout ID
     */
    public $uid;

    /**
     * @var Entry[]|null Array of draft entries
     */
    private $_drafts;

    /**
     * @var Entry|null
     */
    private $_control;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Craft::t('app', 'Name'),
        ];
    }

    /**
     * Use the translated experiments name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return Craft::t('site', $this->name) ?: static::class;
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'startDate';
        $attributes[] = 'endDate';

        return $attributes;
    }

    /**
     * Returns all the drafts attached to this experiment
     *
     * @return array
     */
    public function getDrafts(): array
    {
        if ($this->_drafts !== null) {
            return $this->_drafts;
        }

        $draftIds = ExperimentDraft::find()
            ->select('draftId')
            ->where(['experimentId' => $this->id])
            ->column();

        if (!$draftIds) {
            return [];
        }

        $this->_drafts = [];
        foreach ($draftIds as $draftId) {
            $this->_drafts[] = Entry::find()
                ->draftId($draftId)
                ->anyStatus()
                ->one();
        }

        return $this->_drafts;
    }

    /**
     * If there are drafts attached then this returns the control / primary entry
     *
     * @return Entry
     */
    public function getControl(): Entry
    {
        if ($this->_control !== null) {
            return $this->_control;
        }

        if (!$this->getDrafts()) {
            return false;
        }

        $this->_control = $this->getDrafts()[0]->getSource();

        return $this->_control;
    }

}
