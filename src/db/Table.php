<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run up A/B (split) or multivariate tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\db;

/**
 * This class provides constants for defining A/B Test’s database table names.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
abstract class Table
{
    const CONTENT = '{{%abtest_content}}';
    const RELATIONS = '{{%abtest_relations}}';
}