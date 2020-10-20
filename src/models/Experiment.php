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

use Craft;
use craft\base\Model;

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

    /**
     * @var string|null The name of the cookie
     */
    public $cookieName;


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
        return ['cookieName'];
    }

    /**
     * Returns the cookie name that should be used for this experiment.
     *
     * @return string|null
     */
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
