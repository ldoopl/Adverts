<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%image}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%advert}}`
 */
class m200125_132849_create_image_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%image}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(),
            'advert_id' => $this->integer()->notNull(),
            'url' => $this->string()
        ]);

        // creates index for column `advert_id`
        $this->createIndex(
            '{{%idx-image-advert_id}}',
            '{{%image}}',
            'advert_id'
        );

        // add foreign key for table `{{%advert}}`
        $this->addForeignKey(
            '{{%fk-image-advert_id}}',
            '{{%image}}',
            'advert_id',
            '{{%advert}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%advert}}`
        $this->dropForeignKey(
            '{{%fk-image-advert_id}}',
            '{{%image}}'
        );

        // drops index for column `advert_id`
        $this->dropIndex(
            '{{%idx-image-advert_id}}',
            '{{%image}}'
        );

        $this->dropTable('{{%image}}');
    }
}
