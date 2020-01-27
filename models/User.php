<?php
namespace app\models;

use app\models\Token;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\ServerErrorHttpException;

class User extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_CREDENTIALS = 'credentials';

    const STATUS_ACTIVE = 1;
    const STATUS_CLOSED = 2;

    public $password;
//    public $username;
//    public $city_id;
//    public $phone_number;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'required', 'message' => 'Заполните имя.','on' => self::SCENARIO_UPDATE],
            ['city_id', 'required', 'message' => 'Укажите свой город.','on' => self::SCENARIO_UPDATE],
            ['phone_number', 'required', 'message' => 'Укажите свой мобильный телефон.','on' => self::SCENARIO_UPDATE],
            ['email', 'required', 'message' => 'Заполните емэйл.','on' => self::SCENARIO_CREDENTIALS],
            ['password', 'required', 'message' => 'Заполните пароль.','on' => self::SCENARIO_CREDENTIALS],
            ['email', 'email', 'message' => 'Невалидный емэйл.'],
            ['username', 'string'],
            ['description', 'string'],
            ['city_id', 'exist', 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'id']],
            ['city_id', 'integer'],
            ['city_id', 'filter', 'filter' => 'intval'],
            ['avatar', 'string'],
            ['phone_number', 'string', 'max' => 10, 'min' => 10, 'message' => 'Невалидный телефон'],
            ['phone_number', 'unique']
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = ['username', 'city_id', 'phone_number', 'description'];
        $scenarios[self::SCENARIO_CREDENTIALS] = ['email', 'password'];
        return $scenarios;
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            if (User::findByEmail($this->email)){
                throw new ServerErrorHttpException('Этот емэйл уже занят');
            }
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
            $this->avatar = Yii::$app->params['defaultAvatarUrl'];
            $this->created_at = time();
        }

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()
            ->joinWith('tokens t')
            ->andWhere(['t.token' => $token])
            ->andWhere(['>', 't.expired_at', time()])
            ->one();
    }

    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }


    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function fields()
    {
        return [
            'id',
            'username',
            'email',
            'description',
            'phone_number',
            'city_id',
            'created_at',
            'avatar'
        ];
    }

    public function getTokens()
    {
        return $this->hasMany(Token::class, ['user_id' => 'id']);
    }

    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }
}
