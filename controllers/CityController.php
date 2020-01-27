<?php


namespace app\controllers;


use app\models\City;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use Yii;

class CityController extends ActiveController
{
    public $modelClass = City::class;

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
//        unset($actions['index']);
        return $actions;
    }

//    public function actionView()
//    {
//        return City::find()->with('profiles')->all();
//        return 123;
//    }
//    public function actionIndex()
//    {
//        $provider = new ActiveDataProvider([
//            'query' => City::find()->joinWith([
//          'users' => function (\yii\db\ActiveQuery $query) {
//                 $query->select(['id', 'email']);
//             }
//        ], true),
//        ]);
//        return $provider;

    public function prepareDataProvider()
    {
//        $query = City::find();
//        $query = $query->profiles->select('username');
        return Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => City::find()->joinWith([
                'users' => function (\yii\db\ActiveQuery $query) {
                    $query->select(['id', 'username']);
                }]
            )
//            'query' => City::find()->with([
//                'profiles' //=> function (\yii\db\ActiveQuery $query) {
////                    $query->select('username, email');
////                }
//            ])
//             'query' => City::find()->joinWith([
//          'users' => function (\yii\db\ActiveQuery $query) {
//                 $query->select('id, username');
//             }
//        ]),
//            'query' => City::find()->joinWith('users u')
        ]);

    }
}