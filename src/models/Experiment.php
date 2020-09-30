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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Craft::t('app', 'Name'),
        ];
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
//    protected function defineRules(): array
//    {
//        $rules = parent::defineRules();
//        $rules[] = [['id', 'fieldLayoutId'], 'number', 'integerOnly' => true];
//        $rules[] = [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']];
//        $rules[] = [['name', 'handle'], UniqueValidator::class, 'targetClass' => TagGroupRecord::class];
//        $rules[] = [['name', 'handle'], 'required'];
//        $rules[] = [['name', 'handle'], 'string', 'max' => 255];
//        return $rules;
//    }

    /**
     * Use the translated experiments name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return Craft::t('site', $this->name) ?: static::class;
    }

}
