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
use angellco\abtest\records\SectionDraft;
use Craft;
use craft\base\Model;
use craft\elements\Entry;

/**
 * Section model.
 *
 * A section is a combination of a control (or source) entry and one or more drafts.
 * When attached to an experiment one section forms a classic A/B test while multiple sections form a multivariate test.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Section extends Model
{

    // Public Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int|null Experiment ID
     */
    public $experimentId;

    /**
     * @var int|null Source ID
     */
    public $sourceId;

    /**
     * @var Entry[] Array of draft entries
     */
    public $drafts = [];

    /**
     * @var array Array of draft IDs
     */
    public $draftIds = [];

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
    public function extraFields()
    {
        return [
            'drafts'  => function () {
                return $this->getDrafts();
            },
            'controlId'
        ];
    }

    /**
     * Returns all the drafts attached to this section.
     *
     * @return array
     */
    public function getDrafts(): array
    {
        if (!empty($this->drafts)) {
            return $this->drafts;
        }

        $draftIds = SectionDraft::find()
            ->select('draftId')
            ->where(['sectionId' => $this->id])
            ->orderBy(['draftId' => SORT_ASC])
            ->column();

        if (!$draftIds) {
            return [];
        }

        foreach ($draftIds as $draftId) {
            $this->drafts[] = Entry::find()
                ->draftId($draftId)
                ->anyStatus()
                ->one();
        }

        return $this->drafts;
    }

    /**
     * This returns the control / source entry.
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

        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            return false;
        }

        $this->_control = Craft::$app->getElements()->getElementById($this->sourceId, Entry::class);

        return $this->_control;
    }

    /**
     * Returns the source ID of the first draft, which is the root entry or
     * the "control" entry in the section.
     *
     * @return bool|int|null
     */
//    public function getControlId()
//    {
//        if (!$this->getDrafts()) {
//            return false;
//        }
//
//        return (int) $this->getDrafts()[0]->getSourceId();
//    }

}
