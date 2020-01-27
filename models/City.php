<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "city".
 *
 * @property int $id
 * @property string $name
 */
class City extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%city}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 30],
            [['name'], 'unique'],
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['city' => 'id']);
//            ->select(['id', 'username']);
    }

    public function extraFields()
    {
        return ['users'];
    }

}
