<?php

use yii\db\Migration;

/**
 * Handles the creation of table `log`.
 */
class m181115_143147_create_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('log', [
            'id' => $this->primaryKey(),
            'ip' => $this->string(15)->notNull(),
            'time' => $this->integer()->notNull(),
            'method' => $this->string(10)->notNull(),
            'url' => $this->string(255)->notNull(),
            'response' => $this->integer(3)->notNull(),
            'byte' => $this->integer(10)->notNull(),
            'referrer' => $this->string(255),
            'user_agent' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'idx-ip',
            'log',
            'ip'
        );

        $this->createIndex(
            'idx-time',
            'log',
            'time'
        );

        $this->createIndex(
            'idx-method',
            'log',
            'method'
        );

        $this->createIndex(
            'idx-url',
            'log',
            'url'
        );

        $this->createIndex(
            'idx-response',
            'log',
            'response'
        );

        $this->createIndex(
            'idx-byte',
            'log',
            'byte'
        );

        $this->createIndex(
            'idx-referrer',
            'log',
            'referrer'
        );

        $this->createIndex(
            'idx-user_agent',
            'log',
            'user_agent'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-ip',
            'log'
        );

        $this->dropIndex(
            'idx-time',
            'log'
        );

        $this->dropIndex(
            'idx-method',
            'log'
        );

        $this->dropIndex(
            'idx-url',
            'log'
        );

        $this->dropIndex(
            'idx-response',
            'log'
        );

        $this->dropIndex(
            'idx-byte',
            'log'
        );

        $this->dropIndex(
            'idx-referrer',
            'log'
        );

        $this->dropIndex(
            'idx-user_agent',
            'log'
        );

        $this->dropTable('log');
    }
}
