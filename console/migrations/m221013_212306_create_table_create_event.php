<?php

use yii\db\Migration;

/**
 * Class m221013_212306_create_table_create_event
 */
class m221013_212306_create_table_create_event extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%create_event}}', [
            'id'			 => $this->bigPrimaryKey(),
            'type'		 => $this->tinyInteger()->notNull(),
            'model_key'      => $this->integer(11)->notNull(),
            'text'  		 => $this->text(),
            'created_at'	 => $this->dateTime(),

        ], 'ENGINE = INNODB');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%create_event}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221013_212306_create_table_create_event cannot be reverted.\n";

        return false;
    }
    */
}
