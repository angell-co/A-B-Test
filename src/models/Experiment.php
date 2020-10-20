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

use angellco\abtest\records\Section as SectionRecord;
use Craft;
use craft\base\Model;
use craft\helpers\UrlHelper;

/**
 * Experiment model.
 *
 * An experiment is a container to hold sections and their control entries and drafts. It controls if the test can run
 * via the start and end dates and there is one cookie per experiment for each user.
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
     * @var int|null UID
     */
    public $uid;

    // Private Properties
    // =========================================================================

    /**
     * @var string|null The name of the cookie
     */
    private $_cookieName;

    /**
     * @var array|null An array of related sections
     */
    private $_sections;


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
        return [
            'cookieName',
            'sections'
        ];
    }

    /**
     * Returns the cookie name that should be used for this experiment.
     *
     * @return string|null
     */
    public function getCookieName()
    {
        if ($this->_cookieName !== null){
            return $this->_cookieName;
        }

        if ($this->uid) {
            $this->_cookieName = $this::COOKIE_PREFIX.$this->uid;
        } else {
            $this->_cookieName = $this::COOKIE_PREFIX.'unset';
        }

        return $this->_cookieName;
    }

    /**
     * Returns all the sections related to this experiment.
     *
     * @return Section[]
     */
    public function getSections()
    {
        if ($this->_sections !== null){
            return $this->_sections;
        }

        $records = SectionRecord::findAll(['experimentId' => $this->id]);

        $this->_sections = [];
        foreach ($records as $record) {
            $this->_sections[] = new Section($record->toArray([
                'id',
                'experimentId',
                'sourceId',
                // TODO uid ?
            ]));
        }

        return $this->_sections;
    }

    /**
     * @return string
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('ab-test/experiments/'.$this->id);
    }
}
