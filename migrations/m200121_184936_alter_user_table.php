<?php

use yii\db\Migration;

/**
 * Class m200121_184936_alter_user_table
 */
class m200121_184936_alter_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'city_id', $this->integer());

        $this->createIndex(
            'idx-user-city_id',
            'user',
            'city_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-user-city_id',
            'user',
            'city_id',
            'city',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-user-city_id',
            'user'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            'idx-user-city_id',
            'user'
        );

        $this->dropColumn('user', 'city_id');

    }
}
