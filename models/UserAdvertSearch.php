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
        $query = Advert::find()->where(['user_id' => $user_id]);

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

        $query->where([
            'category_id' => $this->category_id,
        ]);
        $query->andWhere([
            'status' => $this->status,
        ]);

        if (isset($params['search'])){
            $search = $params['search'];

            $query->andFilterWhere(['like', 'title', $search])
                ->orFilterWhere(['like','description', $search]);
        }

        return $dataProvider;
    }
}