<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%advert}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%city}}`
 * - `{{%category}}`
 */
class m200125_130917_create_advert_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%advert}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'description' => $this->string(),
            'created_at' => $this->integer(),
            'price' => $this->integer(),
            'city_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1)
        ]);

        // creates index for column `city_id`
        $this->createIndex(
            '{{%idx-advert-city_id}}',
            '{{%advert}}',
            'city_id'
        );

        // add foreign key for table `{{%city}}`
        $this->addForeignKey(
            '{{%fk-advert-city_id}}',
            '{{%advert}}',
            'city_id',
            '{{%city}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            '{{%idx-advert-user_id}}',
            '{{%advert}}',
            'user_id'
        );

        $this->addForeignKey(
            '{{%fk-advert-user_id}}',
            '{{%advert}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `category_id`
        $this->createIndex(
            '{{%idx-advert-category_id}}',
            '{{%advert}}',
            'category_id'
        );

        // add foreign key for table `{{%category}}`
        $this->addForeignKey(
            '{{%fk-advert-category_id}}',
            '{{%advert}}',
            'category_id',
            '{{%category}}',
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
            '{{%fk-advert-city_id}}',
            '{{%advert}}'
        );

        $this->dropIndex(
            '{{%idx-advert-city_id}}',
            '{{%advert}}'
        );

        $this->dropForeignKey(
            '{{%fk-advert-user_id}}',
            '{{%advert}}'
        );

        $this->dropIndex(
            '{{%idx-advert-user_id}}',
            '{{%advert}}'
        );

        $this->dropForeignKey(
            '{{%fk-advert-category_id}}',
            '{{%advert}}'
        );

        $this->dropIndex(
            '{{%idx-advert-category_id}}',
            '{{%advert}}'
        );

        $this->dropTable('{{%advert}}');
    }
}
