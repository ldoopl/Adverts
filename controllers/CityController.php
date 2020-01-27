<?php


namespace app\controllers;


use app\models\City;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use Yii;

class CityController extends ActiveController
{
    public $modelClass = City::class;
}