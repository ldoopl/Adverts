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
        // только поля определенные в rules() будут доступны для поиска
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
        $query = Advert::find()->where(['status' => Advert::STATUS_ACTIVE]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ]
        ]);

        if (!($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }

        if (!$this->city_id) {
            $user = User::findOne($user_id);
            $this->city_id = $user->city_id;
        };

//        $query->
        $query->where([
           'category_id' => $this->category_id,
        ]);
        $query->andWhere([
            'city_id' => $this->city_id,
        ]);

        if (isset($params['search'])){
            $search = $params['search'];
//            $words = preg_split('/\s+/', $search);

            $query->andFilterWhere(['like', 'title', $search])
                ->orFilterWhere(['like','description', $search]);

        }
        return $dataProvider;
    }
}