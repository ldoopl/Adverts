<?php

namespace app\models;

use Imagine\Image\Box;
use Imagine\Image\Point;
use yii\base\Model;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;
use Yii;
use yii\imagine\Image;

class AvatarUploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $avatar;

    const MAX_LENGTH = 128;

    public function rules()
    {
        return [
            ['avatar', 'image', 'extensions' => 'png, jpeg, jpg',
                'wrongExtension' => 'Неверный формат файла. Выберите аватар формата jpg, jpeg или png'],
            ['avatar', 'image', 'maxSize' => 1024 * 1024 * 3,
                'tooBig' => 'Максимальный размер файла 3 Мб'],
        ];
    }

    public function upload($oldAvatar)
    {
        if ($this->validate()) {
            $avatarName = time() . Yii::$app->security->generateRandomString(10) . '.' . $this->avatar->extension;
            $avatarUrl = Yii::$app->params['avatarUrl'] . $avatarName;
            $this->avatar->saveAs(Yii::getAlias('@uploads') . 'avatars/' . $avatarName);
            if ($oldAvatar != Yii::$app->params['defaultAvatarUrl']) {
                $this->deleteOldAvatar($oldAvatar);
            }
            $this->resizeAndCrop($avatarName);
            return $avatarUrl;
        }
        throw new ServerErrorHttpException($this->getFirstError('avatar'));

    }

    private function deleteOldAvatar($oldAvatar)
    {
        $oldAvatarName = end( explode('/', $oldAvatar));
        $oldAvatarPath = Yii::getAlias('@uploads') . 'avatars/' . $oldAvatarName;
        if (file_exists($oldAvatarPath)){
            unlink($oldAvatarPath);
        }
    }

    private function resizeAndCrop($avatarName)
    {
        $imagine = Image::getImagine();
        $image = $imagine->open(Yii::getAlias('@uploads') . 'avatars/' . $avatarName);
        $size = $image->getSize();
        $size->getWidth() > $size->getHeight()
            ? $resizeCoeff = $size->getHeight() / self::MAX_LENGTH
            : $resizeCoeff = $size->getWidth() / self::MAX_LENGTH;
        $width = $size->getWidth() / $resizeCoeff;
        $height = $size->getHeight() / $resizeCoeff;
        $image->resize(new Box($width, $height))
            ->crop(new Point(($width - self::MAX_LENGTH) / 2, ($height - self::MAX_LENGTH) / 2),
                new Box(self::MAX_LENGTH, self::MAX_LENGTH))
            ->save(Yii::getAlias('@uploads') . 'avatars/' . $avatarName);
    }
}