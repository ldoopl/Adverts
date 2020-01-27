<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\User;
use yii\filters\AccessControl;
use yii\rest\ActiveController;
use app\models\AvatarUploadForm;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\ServerErrorHttpException;
use yii\filters\auth\HttpBearerAuth;

class UserController extends ActiveController
{
    public $modelClass = User::class;

//    public function actions()
//    {
//        $actions = parent::actions();
//        unset($actions['update']);
//        return $actions;
//    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'only' => ['patch'],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['register', 'login', 'index', 'view'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]

        ]);
    }

    public function actionLogin()
    {
        $model = new LoginForm();

        $model->load(Yii::$app->request->post(), '');
        if ($token = $model->login()) {
            return [
                'token'=> $token,
                'id' => User::findByEmail($model->email)->getId()
            ];
        } else {
            return $model;
        }
    }

    public function actionRegister()
    {
        $model = new User();
        $model->setScenario(User::SCENARIO_CREDENTIALS);

        $model->load(Yii::$app->request->post(), '');
        if ($model->save()){
            return $this->actionLogin();
        }
        else{
            return $model->getFirstErrors();
        }
    }

    public function actionPatch($id)
    {
        $model = User::findOne($id);
        if (!$model){
            throw new NotFoundHttpException();
        }
        $this->checkAccess('patch', $model);

        $model->setScenario(User::SCENARIO_UPDATE);

        if (isset($_FILES['avatar'])){
            $avatarUrl = $this->uploadAvatar($model->avatar);
            $model->avatar = $avatarUrl;
        }
        $model->load(Yii::$app->request->post(), '');
        if ($model->save()){
            return $model;
        }
        else {
            return $model->getFirstErrors();
        }
    }

    private function uploadAvatar($oldAvatar)
    {
        $model = new AvatarUploadForm();

        $model->avatar = UploadedFile::getInstanceByName('avatar');
        return $model->upload($oldAvatar);
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'patch') {
            if ($model->id !== Yii::$app->user->id)
                throw new ForbiddenHttpException('You are not allowed to update this user');
        }
    }

}
