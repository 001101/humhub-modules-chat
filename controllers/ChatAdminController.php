<?php

/**
 * Connected Communities Initiative
 * Copyright (C) 2016  Queensland University of Technology
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/licences GNU AGPL v3
 *
 */

namespace humhub\modules\chat\controllers;

use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use humhub\modules\chat\models\WBSChat;
use humhub\modules\chat\models\WBSChatSmile;
use humhub\modules\user\models\User;

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class ChatAdminController extends Controller
{
    public $subLayout = "@humhub/modules/admin/views/layouts/main";
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(['query' => WBSChatSmile::find()->andWhere(['status' => WBSChatSmile::STATUS_VISIBLE])]);
        $dataProviderUser = new ActiveDataProvider(['query' => User::find()]);
        $model        = new WBSChatSmile; // error
        if (isset($_POST['WBSChatSmile'])) {
            $model->load(Yii::$app->request->post());
            $model->status = WBSChatSmile::STATUS_VISIBLE;
            $model->save();

            if(!$model->hasErrors()) {
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
        return $this->render("index", [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'dataProviderUser' => $dataProviderUser,
        ]);
    }

    public function actionDelete($id)
    {
        $data = WBSChatSmile::findOne($id);
        if(!empty($data)) {
            $data->status = WBSChatSmile::STATUS_HIDDEN;
            $data->save();
        }
        $this->redirect(Yii::$app->request->referrer);
    }
    
    public function actionBan()
    {
        if (isset($_POST['pk']) && isset($_POST['value'])) {
            $pk = $_POST['pk'];
            $value = $_POST['value'];
            User::updateAll(['is_chating' => $value], ['id' => $pk]);
        } else {
            echo "Error of data editing";
        }
    }
    
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form') {
            echo ActiveForm::validate($model);
            Yii::$app->end();
        }
    }
}
