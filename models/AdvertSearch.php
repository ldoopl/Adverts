<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class AdvertSearch extends Advert
{
    public function rules()
    {
        return [
            [['title', 'category_id', 'city_id'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $user_id)
    {
        //TODO: rewrite query so it wont return multiple instances of advert for each image
        $query = (new Query())->from('advert')
            ->select(['advert.id','title', 'price', 'advert.created_at', 'image.url as first_image',])
            ->where(['status' => Advert::STATUS_ACTIVE])
            ->orderBy(['created_at' => SORT_DESC])
            ->rightJoin('image', 'image.advert_id = advert.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        if (!($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }

        if (!$this->city_id) {
            $user = User::findOne($user_id);
            $this->city_id = $user->city_id;
        };

        $query->andFilterWhere([
           'category_id' => $this->category_id,
        ]);

        $query->andFilterWhere([
            'city_id' => $this->city_id,
        ]);

        if (isset($params['search'])){
            $search = $params['search'];
            $query->andFilterWhere ( [ 'OR' ,
                [ 'like' , 'title' , $search ],
                [ 'like' , 'description' , $search ],
            ] );
        }
//        return $query[0];
        return $dataProvider;
    }
}