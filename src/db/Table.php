<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\db;

/**
 * Class Table
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
abstract class Table
{
    const EXPERIMENTS = '{{%abtest_experiments}}';
}
