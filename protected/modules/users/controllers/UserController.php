<?

class UserController extends Controller
{
    const ERROR_PASSWORD_RECOVER_AUTH = 'Вы не можете восстановить пароль будучи авторизованным!';

    public $layout = '//layouts/middle';


    public function filters()
    {
        return array('accessControl');
    }


    public function accessRules()
    {
        return array(
            array(
                'deny',
                'actions' => array(
                    'ActivateAccountRequest', 'ChangePasswordRequest', 'ActivateAccount', 'Registration',
                    'ChangePassword', 'Login'
                ),
                'users'   => array('@')
            )
        );
    }


    public static function actionsTitles()
    {
        return array(
            "Login"                  => "Авторизация",
            "Logout"                 => "Выход",
            "Registration"           => "Регистрация",
            "ActivateAccount"        => "Активация аккаунта",
            "ActivateAccountRequest" => "Запрос на активацию аккаунта",
            "ChangePassword"         => "Смена пароля",
            "ChangePasswordRequest"  => "Запрос на смену пароля",
        );
    }


    public function actionLogin()
    {
        $model = new User(User::SCENARIO_LOGIN);

        $this->performAjaxValidation($model);

        $form = new Form('users.LoginForm', $model);

        if (isset($_POST['User']['email']) && isset($_POST['User']["password"]))
        {
            $model->attributes = $_POST['User'];

            if ($model->validate())
            {
                $identity = new UserIdentity($model->email, $model->password);
                if ($identity->authenticate())
                {
                    $this->redirect('/');
                }
                else
                {
                    $auth_error = $identity->errorCode;
                    if ($auth_error == UserIdentity::ERROR_NOT_ACTIVE)
                    {
                        $auth_error .= "<br/><a href='" . $this->createUrl('/activateAccountRequest') . "'>Активировать аккаунт повторно</a>";
                    }
                    else if ($auth_error == UserIdentity::ERROR_UNKNOWN)
                    {
                        $auth_error .= "<br/><a href='" . $this->createUrl('changePasswordRequest') . "'>
                    							Восстановить пароль
                    						</a>";
                    }
                }
            }
        }

        $this->render('login', array(
            'form'       => $form,
            'auth_error' => isset($auth_error) ? $auth_error : null
        ));
    }


    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }


    /**
     * TODO: не работает капча
     * TODO: не проходит валидация по birthdate 18.9.1985, нужно чтобы было в календаре 18.09.1985
     * TODO
     */
    public function actionRegistration()
    {
        MailerTemplate::checkRequired(UsersModule::MAILER_TEMPLATE_REGISTRATION);
        Param::checkRequired(UsersModule::PARAM_REGISTRATION_DONE_MESSAGE);

        $user = new User(User::SCENARIO_REGISTRATION);
        $form = new Form('users.RegistrationForm', $user);

        $this->performAjaxValidation($user);

        if (isset($_POST['User']))
        {
            $user->attributes = $_POST['User'];
            if ($user->validate())
            {
                $user->password = md5($user->password);
                $user->generateActivateCode();

                if ($user->save(false))
                {
                    $assignment = new AuthAssignment();
                    $assignment->itemname = AuthItem::ROLE_DEFAULT;
                    $assignment->userid   = $user->id;
                    $assignment->save();

                    Yii::app()->user->setFlash('success', Param::model()->getValue(UsersModule::PARAM_REGISTRATION_DONE_MESSAGE));

                    $this->redirect($_SERVER['REQUEST_URI']);
                }
                else
                {
                    p($user->errors);
                    throw new CHttpException('Ошибка сохранения пользователя!');
                }
            }
        }

        $this->render('registration', array('form' => $form));
    }


    public function actionActivateAccount($code, $email)
    {
        $user = User::model()->findByAttributes(array('activate_code' => $code));

        if ($user && md5($user->email) == $email)
        {
            if (strtotime($user->date_create) + 24 * 3600 > time())
            {
                $user->activate_date = null;
                $user->activate_code = null;
                $user->status        = User::STATUS_ACTIVE;
                $user->save();

                Yii::app()->user->setFlash('acrivate_done', 'Активация аккаунта прошла успешно! Вы можете авторизоваться.');

                $this->redirect('/login');
            }
            else
            {
                $activate_error = 'С момента регистрации прошло больше суток!';
            }
        }
        else
        {
            $activate_error = 'Неверные данные активации аккаунта!';
        }

        $this->render('activateAccount', array(
            'activate_error' => isset($activate_error) ? $activate_error : null
        ));
    }


    public function actionActivateAccountRequest()
    {
        $model = new User(User::SCENARIO_ACTIVATE_REQUEST);
        $form  = new Form('users.ActivateRequestForm', $model);

        $this->performAjaxValidation($model);

        if (isset($_POST['User']))
        {
            $model->attributes = $_POST['User'];
            if ($model->validate())
            {
                $user = User::model()->findByAttributes(array('email' => $_POST['User']['email']));
                if (!$user)
                {
                    $error = UserIdentity::ERROR_UNKNOWN;
                }
                else
                {
                    switch ($user->status)
                    {
                        case User::STATUS_NEW:
                            $user->generateActivateCode();
                            $user->save();
                            $user->sendActivationMail();

                            Yii::app()->user->setFlash('done', 'На ваш Email отправлено письмо с дальнейшими инструкциями.');

                            $this->redirect($this->createUrl('activateAccountRequest'));
                            break;

                        case User::STATUS_ACTIVE:
                            $error = UserIdentity::ERROR_ALREADY_ACTIVE;
                            break;

                        case User::STATUS_BLOCKED:
                            $error = UserIdentity::ERROR_BLOCKED;
                            break;
                    }
                }
            }
        }

        $this->render('activateAccountRequest', array(
            'form'  => $form,
            'error' => isset($error) ? $error : null
        ));
    }


    public function actionChangePasswordRequest()
    {
        Param::model()->checkRequired(array(
            User::SETTING_CHANGE_PASSWORD_REQUEST_MAIL_SUBJECT,
            User::SETTING_CHANGE_PASSWORD_REQUEST_MAIL_BODY
        ));

        $model = new User(User::SCENARIO_CHANGE_PASSWORD_REQUEST);
        $form  = new Form('users.ChangePasswordRequestForm', $model);
        $this->performAjaxValidation($model);

        if (isset($_POST['User']))
        {
            $model->attributes = $_POST['User'];
            if ($model->validate())
            {
                $user = $model->findByAttributes(array('email' => $model->email));
                if ($user)
                {
                    if ($user->status == User::STATUS_ACTIVE)
                    {
                        $user->password_change_code = md5(
                            $user->password . $user->email . $user->id . time());
                        $user->password_change_date = new CDbExpression('NOW()');
                        $user->save();

                        $mailer_letter = MailerLetter::model();

                        $settings = Param::model()->findCodesValues();

                        $subject = $mailer_letter->compileText($settings[User::SETTING_CHANGE_PASSWORD_REQUEST_MAIL_SUBJECT], array('user' => $user));

                        $body = $mailer_letter->compileText($settings[User::SETTING_CHANGE_PASSWORD_REQUEST_MAIL_BODY], array('user' => $user));

                        MailerModule::sendMail($user->email, $subject, $body);

                        Yii::app()->user->setFlash('done', 'На ваш Email отправлено письмо с дальнейшими инструкциями.');

                        $this->redirect($this->createUrl('changePasswordRequest'));

                    }
                    else if ($user->status == User::STATUS_NEW)
                    {
                        $error = UserIdentity::ERROR_NOT_ACTIVE;
                    }
                    else
                    {
                        $error = UserIdentity::ERROR_BLOCKED;
                    }
                }
                else
                {
                    $error = UserIdentity::ERROR_UNKNOWN;
                }
            }
        }

        $this->render("changePasswordRequest", array(
            'form'  => $form,
            'error' => isset($error) ? $error : null
        ));
    }


    public function actionChangePassword($code, $email)
    {
        $model = new User(User::SCENARIO_CHANGE_PASSWORD);
        $form  = new Form('users.ChangePasswordForm', $model);
        $this->performAjaxValidation($model);

        $user = User::model()->findByAttributes(array('password_change_code' => $code));

        if (!$user || md5($user->email) != $email)
        {
            $error = 'Неверная ссылка изменения пароля!';
        }
        else
        {
            if (strtotime($user->password_change_date) + 24 * 3600 > time())
            {
                if (isset($_POST['User']))
                {
                    $model->attributes = $_POST['User'];
                    if ($model->validate())
                    {
                        $user->password_change_code = null;
                        $user->password_change_date = null;
                        $user->password             = md5($_POST['User']['password']);
                        $user->save();

                        Yii::app()->user->setFlash('change_password_done', 'Ваш пароль успешно изменен, вы можете авторизоваться!');

                        $this->redirect('/login');
                    }
                }
            }
            else
            {
                $error = 'С момента запроса на восстановление пароля прошло больше суток!';
            }
        }

        $this->render('changePassword', array(
            'model' => $model,
            'form'  => $form,
            'error' => isset($error) ? $error : null
        ));
    }
}
