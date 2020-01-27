<?php


namespace app\controllers;


use common\models\Category;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;

class CategoryController extends ActiveController
{
    public $modelClass = Category::class;

}