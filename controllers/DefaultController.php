<?php

namespace humanized\scoopit\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use humanized\scoopit\models\gui\ScoopSearch;

/**
 * IconRegisterController implements the CRUD actions for IconRegister model.
 */
class DefaultController extends Controller
{

    /**
     * Lists all IconRegister models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ScoopSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('/scoops/index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOauthCallback($oauth_token, $oauth_verifier)
    {
        // echo $oauthToken;
        return $this->render('/authentication/index', [
                    't' => $oauth_token, 'v' => $oauth_verifier
        ]);
    }

}
