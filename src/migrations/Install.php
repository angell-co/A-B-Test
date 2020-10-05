<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\migrations;

use angellco\abtest\db\Table;
use Craft;
use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * Class Install
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class Install extends Migration
{

    // Public Methods
    // =========================================================================

    /**
     * @return bool
     */
    public function safeUp()
    {
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function safeDown()
    {
        $this->dropForeignKeys();
        $this->dropTables();

        return true;
    }


    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        // Experiments table
        $tableSchema = Craft::$app->db->schema->getTableSchema(Table::EXPERIMENTS);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                Table::EXPERIMENTS,
                [
                    'id' => $this->primaryKey(),
                    'name' => $this->string()->notNull(),
                    'startDate' => $this->dateTime(),
                    'startDate' => $this->dateTime(),
                    'endDate' => $this->dateTime(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        // Experiments_Drafts table
        $tableSchema = Craft::$app->db->schema->getTableSchema(Table::EXPERIMENTS_DRAFTS);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                Table::EXPERIMENTS_DRAFTS,
                [
                    'id' => $this->primaryKey(),
                    'experimentId' => $this->integer()->notNull(),
                    'draftId' => $this->integer()->notNull(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(null, Table::EXPERIMENTS_DRAFTS, ['experimentId', 'draftId'], true);
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(null, Table::EXPERIMENTS_DRAFTS, ['experimentId'], Table::EXPERIMENTS, ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::EXPERIMENTS_DRAFTS, ['draftId'], \craft\db\Table::DRAFTS, ['id'], 'CASCADE', 'CASCADE');
    }

    /**
     * Removes the foreign keys
     *
     * @return void
     */
    protected function dropForeignKeys()
    {
        if (Craft::$app->db->schema->getTableSchema(Table::EXPERIMENTS_DRAFTS)) {
            MigrationHelper::dropAllForeignKeysToTable(Table::EXPERIMENTS_DRAFTS, $this);
            MigrationHelper::dropAllForeignKeysOnTable(Table::EXPERIMENTS_DRAFTS, $this);
        }
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function dropTables()
    {
        $this->dropTableIfExists(Table::EXPERIMENTS_DRAFTS);
        $this->dropTableIfExists(Table::EXPERIMENTS);
    }

}
