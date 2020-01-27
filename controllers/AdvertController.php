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
                'except' => ['view', 'images', 'index']
            ],
            'access' => [
                'class' => AccessControl::class,
                'except' => ['view', 'index', 'images',],
                'rules' => [
                    [   'actions' => ['index', ],
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

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['view']);
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProviderForAllAdverts'];
        return $actions;
    }

    public function prepareDataProviderForAllAdverts()
    {
        $searchModel = new AdvertSearch();
        return $searchModel->search(Yii::$app->request->get(), Yii::$app->user->getId());
    }

    public function actionView($id)
    {
        $advert = Advert::find(['id' => $id])
            ->where(['id' => $id])
//            ->joinWith('image')
            ->one();
        $user = User::findOne(['id' => $advert->user_id]);
//            ->where(['id' => $advert->user_id])
//            ->one();
        return [
            'advert' => $advert,
            'user' => $user->username
        ];
    }

    public function actionUserAdverts($id)
    {
//        return 123;
        $this->checkAccess('user-adverts', $id);
        $searchModel = new UserAdvertSearch();
        return $searchModel->search(Yii::$app->request->get(), $id);
    }

//    public function prepareDataProviderForUserAdverts()
//    {
//        $searchModel = new UserAdvertSearch();
//        return $searchModel->search(Yii::$app->request->get());
//    }

    public function actionCreate()
    {
        $model = new Advert();
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

    public function actionImages($id)
    {
        return Image::find()
            ->where(['advert_id' => $id])
            ->select('id, created_at, url')
            ->all();
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'patch') {
            if ($model->user_id !== Yii::$app->user->id)
                throw new ForbiddenHttpException('У вас нет прав редактировать это объявление');
        }
        elseif ($action === 'close'){
            if ($model->user_id !== Yii::$app->user->id)
                throw new ForbiddenHttpException('У вас нет прав закрыть это объявление');
        }
        elseif ($action === 'user-advert'){
            if ($model != Yii::$app->user->id)
                throw new ForbiddenHttpException('У вас нет прав получить эти объявления');
        }
    }
}
