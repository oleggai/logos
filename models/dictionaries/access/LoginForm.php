<?php

namespace app\models\dictionaries\access;

use app\models\common\PrintSetupUser;
use Yii;
use yii\base\Model;
use app\models\common\ProgramParams;

/**
 * Модель входа в систему
 */
class LoginForm extends Model
{
    /**
     * @var string Логин
     */
    public $username;
    /**
     * @var string Пароль
     */
    public $password;
    /**
     * @var bool Запомнить пользвателя на 3600*24*30 секунд
     */
    public $rememberMe = true;
    /**
     * @var string Используемый язык
     */
    public $language;
    /**
     * @var mixed Для временного хранения найденого пользователя
     */
    private $_user = false;


    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
            // логин, пароль, язык - необходимы для заполнения
            [['username', 'password','language'], 'required'],
            // запомнить - да или нет
            ['rememberMe', 'boolean'],
            // проверка пароля
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @return array Надписи к полям
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app','Username'),
            'password' => Yii::t('app','Password'),
            'rememberMe' => Yii::t('app','Remember me'),
        ];
    }

    /**
     * Метод проверки пароля
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('user','Incorrect username or password.'));
            }


            //если сайт в режиме обслуживания
            if (ProgramParams::get_parameter_by_id('maintenance_mode')=='1'&&$user->user_id!=1){
                $this->addError($attribute, Yii::t('app','Site in maintenance mode.'));
            }

        }
    }

    /**
     * Метод авторизации
     */
    public function login()
    {
        if ($this->validate()) {
            Yii::$app->session->set('user.language', $this->language);
//            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
            $login = Yii::$app->user->login($this->getUser(), 3600*24*30);
            // Настройки печати инвойса в сессию
            $printSetupUser = PrintSetupUser::findOne(['user_id' => Yii::$app->user->id]);

            if(!$printSetupUser) {
                $printSetupUser = new PrintSetupUser();
                $printSetupUser->load($printSetupUser->defaultPrintSetup, '');
                $printSetupUser->save();
            }
            \Yii::$app->session->set('printSetupUserModel', serialize($printSetupUser));
            return $login;
        } else {
            return false;
        }
    }

    /**
     * Поиск пользователя по имени
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
