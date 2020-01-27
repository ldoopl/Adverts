<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class UserAdvertSearch extends Advert
{
    public function rules()
    {
        return [
            [['title', 'category_id', 'status'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $user_id)
    {
        unset($params['id']);

        //TODO: rewrite query so it wont return multiple instances of advert for each image
        $query = (new Query())->from('advert')
            ->select(['advert.id','title', 'price', 'advert.created_at', 'category_id',
                'city_id', 'description', 'status', 'image.url as first_image'])
            ->orderBy(['created_at' => SORT_DESC]);
        $query->innerJoin('image', 'image.advert_id = advert.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        if (!($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'category_id' => $this->category_id,
        ]);
        $query->andFilterWhere([
            'status' => $this->status,
        ]);

        if (isset($params['search'])){
            $search = $params['search'];

            $query->andFilterWhere ( [ 'OR' ,
                [ 'like' , 'title' , $search ],
                [ 'like' , 'description' , $search ],
            ] );
        }
        return $dataProvider;
    }
}