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
use craft\helpers\ElementHelper;

/**
 * Experiment model.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Experiment extends Model
{
    // Constants
    // =========================================================================

    const COOKIE_PREFIX = 'CRAFT_AB_';

    // Public Properties
    // =========================================================================

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
     * @var string|null The name of the cookie
     */
    public $cookieName;

    /**
     * @var Entry[]|null Array of draft entries
     */
    public $drafts;

    // Private Properties
    // =========================================================================

    /**
     * @var Entry|null
     */
    private $_control;

    // Public Methods
    // =========================================================================

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
     * @inheritdoc
     */
    public function extraFields()
    {
        return ['drafts','controlId','cookieName'];
    }

    /**
     * Returns all the drafts attached to this experiment
     *
     * @return array
     */
    public function getDrafts(): array
    {
        if ($this->drafts !== null) {
            return $this->drafts;
        }

        $draftIds = ExperimentDraft::find()
            ->select('draftId')
            ->where(['experimentId' => $this->id])
            ->orderBy(['draftId' => SORT_ASC])
            ->column();

        if (!$draftIds) {
            return [];
        }

        $this->drafts = [];
        foreach ($draftIds as $draftId) {
            $this->drafts[] = Entry::find()
                ->draftId($draftId)
                ->anyStatus()
                ->one();
        }

        return $this->drafts;
    }

    /**
     * If there are drafts attached then this returns the control / primary entry.
     *
     * You have to be careful _not_ to call this whilst the ElementQuery::EVENT_AFTER_POPULATE_ELEMENT
     * is running the show - if the request has come from there then we need
     * to not run this method.
     *
     * To that end, it bails if its a front-end request.
     *
     * @return Entry|bool
     */
    public function getControl(): Entry
    {
        if ($this->_control !== null) {
            return $this->_control;
        }

        if (!$this->getDrafts()) {
            return false;
        }

        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            return false;
        }

        $this->_control = $this->getDrafts()[0]->getSource();

        return $this->_control;
    }

    /**
     * Returns the source ID of the first draft, which is the root entry or
     * the "control" entry in the experiment.
     *
     * @return bool|int|null
     */
    public function getControlId()
    {
        if (!$this->getDrafts()) {
            return false;
        }

        return (int) $this->getDrafts()[0]->getSourceId();
    }


    public function getCookieName()
    {
        if ($this->cookieName !== null){
            return $this->cookieName;
        }

        if ($this->uid) {
            $this->cookieName = $this::COOKIE_PREFIX.$this->uid;
        } else {
            $this->cookieName = $this::COOKIE_PREFIX.'unset';
        }

        return $this->cookieName;
    }
}
