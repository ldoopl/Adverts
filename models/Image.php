<?php


namespace app\models;


use yii\db\ActiveRecord;
use Yii;
use yii\web\UploadedFile;

class Image extends ActiveRecord
{
    /**
     * @var UploadedFile
     */
    public $image;

    public static function tableName()
    {
        return '{{%image}}';
    }

    public function rules()
    {
        return [
            ['image', 'image', 'extensions' => 'png, jpeg, jpg',
                'wrongExtension' => 'Неверный формат файла. Выберите аватар формата jpg, jpeg или png'],
            ['image', 'image', 'maxSize' => 1024 * 1024 * 10,
                'tooBig' => 'Максимальный размер файла 10 Мб'],
        ];
    }

    public function upload($advert_id)
    {
        if ($this->validate()){
            $imageName = time() . Yii::$app->security->generateRandomString(10) . '.' . $this->image->extension;
            $imageUrl = Yii::$app->params['advertImageUrl'] . $imageName;
            $this->advert_id = $advert_id;
            $this->url = $imageUrl;
            $this->created_at = time();
            $this->save();
            $this->image->saveAs(Yii::getAlias('@uploads') . 'adverts_images/' . $imageName);
            return true;
        }
        else{
            $this->getFirstError('image');
            return $this;
        }
    }

    public static function findAdvertImages($advert_id)
    {
        return Image::find()
            ->where(['advert_id' => $advert_id])
            ->select('id, created_at, url')
            ->all();
    }
}