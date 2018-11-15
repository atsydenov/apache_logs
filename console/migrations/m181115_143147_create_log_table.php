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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('log');
    }
}
