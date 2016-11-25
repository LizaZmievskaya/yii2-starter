<?php

namespace app\modules\user\controllers;

use app\modules\mailTemplate\models\Mail;
use app\modules\mailTemplate\models\MailTemplate;
use app\modules\user\models\forms\LoginForm;
use app\modules\user\models\forms\PasswordForm;
use app\modules\user\models\forms\RecoveryForm;
use app\modules\user\models\Hash;
use app\modules\user\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\modules\user\models\forms\RegistrationForm;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `user` module
 */
class AuthController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['registration', 'login'],
                'rules' => [
                    [
                        'actions' => ['registration', 'login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRegistration()
    {
        $registrationForm = new RegistrationForm();

        if ($registrationForm->load(Yii::$app->request->post()) && $registrationForm->validate()) {
            $user = new User();

            if ($hashData = $user->register($registrationForm)) {

                if (!$mailTemplate = MailTemplate::findByKey('REGISTER')) {
                    throw new NotFoundHttpException('Template not found in database');
                }
                $mailTemplate->replacePlaceholders([
                    'name' => $user->first_name,
                    'link' => Yii::$app->urlManager->createAbsoluteUrl(['user/auth/confirm',
                        'user_id' => $hashData['user_id'],
                        'hash' => $hashData['hash']
                    ]),
                ]);

                $mail = new Mail();
                $mail->setTemplate($mailTemplate);
                $mail->sendTo($user->email);

                Yii::$app->session->setFlash('confirmRegistration');
                return $this->refresh();
            }
        }
        return $this->render('registration', [
            'model' => $registrationForm,
        ]);
    }

    /**
     * @return bool|\yii\web\Response
     */
    public function actionConfirm()
    {
        if (($user_id = Yii::$app->request->get('user_id')) && ($hash = Yii::$app->request->get('hash'))) {
            if (Hash::find()->where(['user_id' => $user_id, 'hash' => $hash])) {
                $user = User::findIdentity($user_id);
                $user->status = User::STATUS_ACTIVE;
                $user->update();
                if ($user->login()) {
                    return $this->goHome();
                }
            }
        }
        return false;
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Sends link for password recovery on user email
     *
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRecovery()
    {
        $recoveryForm = new RecoveryForm();
        if ($recoveryForm->load(Yii::$app->request->post()) && $recoveryForm->validate()) {

            if (($user = User::findByEmail($recoveryForm->email)) && ($user->status == User::STATUS_ACTIVE)) {

                if (!$mailTemplate = MailTemplate::findByKey('RECOVER')) {
                    throw new NotFoundHttpException('Template not found in database');
                }

                $hashKey = Hash::findByUserID($user->id);
                $hashKey->type = 'recover';
                $hashKey->update();
                $mailTemplate->replacePlaceholders([
                    'name' => $user->first_name,
                    'link' => Yii::$app->urlManager->createAbsoluteUrl(['user/auth/change-password',
                        'user_id' => $user->id,
                        'hash' => $hashKey->hash
                    ]),
                ]);

                $mail = new Mail();
                $mail->setTemplate($mailTemplate);
                $mail->sendTo($user->email);

                Yii::$app->session->setFlash('success',
                    Yii::t('user', 'Please check your email and follow instructions to recover password.'));
                return $this->refresh();
            }
        }
        return $this->render('recovery', [
            'model' => $recoveryForm,
        ]);
    }

    public function actionChangePassword()
    {
        $passwordForm = new PasswordForm();

        return $this->render('change-password', [
            'model' => $passwordForm,
        ]);
    }

}
