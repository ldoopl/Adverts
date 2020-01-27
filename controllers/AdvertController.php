<?php


namespace app\controllers;


use app\models\Advert;
use app\models\AdvertSearch;
use app\models\City;
use app\models\Image;
use app\models\User;
use app\models\UserAdvertSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

class AdvertController extends ActiveController
{
    public $modelClass = Advert::class;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => ['view', 'advert-images', 'index']
            ],
            'access' => [
                'class' => AccessControl::class,
                'except' => ['view', 'index', 'advert-images',],
                'rules' => [
                    [   'actions' => ['index', ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ]);
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['view']);
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    public function prepareDataProvider()
    {
        $searchModel = new AdvertSearch();
        return $searchModel->search(Yii::$app->request->get(), Yii::$app->user->getId());
    }

    public function actionView($id)
    {
        return Advert::findAdvert($id);
    }

    public function actionUserAdverts($id)
    {
        $this->checkAccess('user-adverts', $id);

        $searchModel = new UserAdvertSearch();
        return $searchModel->search(Yii::$app->request->get(), $id);
    }

    public function actionCreate()
    {
        $model = new Advert();
        $this->checkAccess('create');
        return $this->createOrUpdate($model);
    }

    public function actionPatch($id)
    {
        $model = Advert::findOne($id);
        if (!$model){
            throw new NotFoundHttpException();
        }
        if ($model->status == Advert::STATUS_CLOSED){
            throw new ServerErrorHttpException('Вы не можете редактировать закрытое объявление');
        }
        $this->checkAccess('patch', $model);
        return $this->createOrUpdate($model);
    }

    private function createOrUpdate($model)
    {
        $model->setScenario(Advert::SCENARIO_CREATE_OR_UPDATE);
        $model->load(Yii::$app->request->post(), '');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->save()) {
                $images = $this->uploadImages($model->id, $transaction);
                if ($images !== true) {
                    $transaction->rollBack();
                    return $images;
                };
                $transaction->commit();
            }
        }
        catch (\Exception $e){
            $transaction->rollBack();
        }
        return $model;
    }

    private function uploadImages($advert_id, $transaction)
    {
        $images = UploadedFile::getInstancesByName('images');
        foreach ($images as $image){
            $model = new Image();
            $model->image = $image;
            if ($model->upload($advert_id) !== true){
                return $model;
            };
        }
        return true;
    }

    public function actionClose($id)
    {
        $model = Advert::findOne($id);
        $this->checkAccess('close', $model);
        if ($model->status == Advert::STATUS_ACTIVE){
            $model->status = Advert::STATUS_CLOSED;
        }
        if ($model->save()){
            return $model;
        }
        else return $model->getFirstErrors();
    }

    public function actionAdvertImages($id)
    {
        return Image::findAdvertImages($id);
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        switch ($action){
            case 'patch':
                if ($model->user_id !== Yii::$app->user->id)
                    throw new ForbiddenHttpException('У вас нет прав редактировать это объявление');
                break;
            case 'close':
                if ($model->user_id !== Yii::$app->user->id)
                    throw new ForbiddenHttpException('У вас нет прав закрыть это объявление');
                break;
            case 'user-adverts':
                if ($model != Yii::$app->user->getId())
                    throw new ForbiddenHttpException('У вас нет прав получить эти объявления');
        }
    }
}
