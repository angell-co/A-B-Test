<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run up A/B (split) or multivariate tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\migrations;

use angellco\abtest\AbTest;
use angellco\abtest\db\Table;
use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use craft\db\Table as CraftTable;

/**
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema(Table::CONTENT);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                Table::CONTENT,
                [
                    'id' => $this->primaryKey(),
                    'contentId' => $this->integer()->notNull(),
                    'variantId' => $this->integer()->notNull(),
                    'weight' => $this->decimal()->unsigned(),
                    'title' => $this->string(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema(Table::RELATIONS);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                Table::RELATIONS,
                [
                    'id' => $this->primaryKey(),
                    'relationId' => $this->integer()->notNull(),
                    'targetId' => $this->integer()->notNull(),
                    'sortOrder' => $this->smallInteger()->unsigned(),
                    'variantId' => $this->integer()->notNull(),
                    'weight' => $this->decimal()->unsigned(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(null, Table::CONTENT, ['contentId', 'variantId'], true);
        $this->createIndex(null, Table::CONTENT, ['contentId'], false);
        $this->createIndex(null, Table::CONTENT, ['title'], false);

        $this->createIndex(null, Table::RELATIONS, ['relationId', 'targetId', 'variantId'], true);
        $this->createIndex(null, Table::RELATIONS, ['relationId'], false);
        $this->createIndex(null, Table::RELATIONS, ['targetId'], false);
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(null, Table::CONTENT, ['contentId'], CraftTable::CONTENT, ['id'], 'CASCADE', null);

        $this->addForeignKey(null, Table::RELATIONS, ['relationId'], CraftTable::RELATIONS, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::RELATIONS, ['targetId'], CraftTable::ELEMENTS, ['id'], 'CASCADE', null);
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists(Table::CONTENT);
        $this->dropTableIfExists(Table::RELATIONS);
    }
}
