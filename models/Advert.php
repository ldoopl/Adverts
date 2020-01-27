<?php


namespace app\models;


use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;

class Advert extends ActiveRecord
{
    const SCENARIO_CREATE_OR_UPDATE = 'update_and_create';

    const STATUS_ACTIVE = 1;
    const STATUS_CLOSED = 2;

    public static function tableName()
    {
        return '{{%advert}}';
    }

    public function rules()
    {
        return [
            ['title', 'required', 'skipOnEmpty' => false,'message' => 'Заполните заголовок объявления.',
                'on' => self::SCENARIO_CREATE_OR_UPDATE],
            ['city_id', 'required', 'skipOnEmpty' => false, 'message' => 'Укажите свой город.',
                'on' => self::SCENARIO_CREATE_OR_UPDATE],
            ['category_id', 'required', 'skipOnEmpty' => false, 'message' => 'Укажите категорию.',
                'on' => self::SCENARIO_CREATE_OR_UPDATE],
            ['price', 'required', 'skipOnEmpty' => false, 'message' => 'Укажите цену.',
                'on' => self::SCENARIO_CREATE_OR_UPDATE],
            ['description', 'required', 'skipOnEmpty' => false, 'message' => 'Заполните описание объявления.',
                'on' => self::SCENARIO_CREATE_OR_UPDATE],
            ['description', 'string'],
            ['city_id', 'exist', 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'id']],
            ['city_id', 'integer'],
            ['city_id', 'filter', 'filter' => 'intval'],
            ['category_id', 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            ['category_id', 'integer'],
            ['category_id', 'filter', 'filter' => 'intval'],
            ['title', 'string'],
            ['price', 'integer', 'min' => 0, 'message' => 'Цена должна быть целым числом.'],
            ['price', 'filter', 'filter' => 'intval'],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE_OR_UPDATE] = ['title', 'city_id', 'category_id', 'price', 'description'];
        return $scenarios;
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created_at = time();
            $this->status = self::STATUS_ACTIVE;
            $this->user_id = \Yii::$app->user->getId();
        }
        return parent::beforeSave($insert);
    }

    public function getFirstImage()
    {
        return $this->hasMany(Image::class, ['advert_id' => 'id'])
            ->min('created_at');
    }

    public static function findAdvert($advert_id)
    {
        $advert = Advert::findOne(['id' => $advert_id]);
        if ($advert->status === Advert::STATUS_CLOSED){
            throw new ForbiddenHttpException('Объявление закрыто');
        }

        $first_image = Image::findOne(['created_at' => $advert->firstImage]);
        $user = User::findOne(['id' => $advert->user_id]);
        $adverts_number = count($user->adverts);

        return [
            'advert' => [
                'title' => $advert->title,
                'created_at' => $advert->created_at,
                'category_id' => $advert->category_id,
                'city_id' => $advert->city_id,
                'price' => $advert->price,
                'description' => $advert->description,
                'first_image' => $first_image->url,
                'creator' => [
                    'username' => $user->username,
                    'created_at' => $user->created_at,
                    'description' => $user->description,
                    'phone_number' => $user->phone_number,
                    'adverts_number' => $adverts_number,
                ],
            ],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'price',
            'description',
            'city_id',
            'category_id',
            'user_id',
            'created_at',
            'status'
        ];
    }
}