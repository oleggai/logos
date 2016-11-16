<?php

namespace app\models\common;

use Yii;
use yii\db\ActiveRecord;
use creocoder\nestedsets\NestedSetsBehavior;
use app\models\common\CommonModel;
use app\models\common\MenuItemQuery;
/**
 * Класс модели елемента меню
 *
 * @property string $state
 * 
 * http://www.getinfo.ru/article610.html
 */
class MenuItem extends CommonModel
{
    /**
     * Возвращает имя таблицы
     */
    public static function tableName()
    {
        return 'sys_menu';
    }
    
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->ownDb;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'tree' => [
                'class' => NestedSetsBehavior::className(),
            ],
        ];
    }
    
    /**
     * Переопределяет ActiveQuery класс
     * @return CommonQuery
     */
    public static function find()
    {
        return new MenuItemQuery(get_called_class());
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('menu', 'Name'),
            'url' => Yii::t('menu', 'Url'),
            'class' => Yii::t('menu', 'Class'),
            'name_ru' => Yii::t('menu', 'Name (Rus)'),
            'name_en' => Yii::t('menu', 'Name (Eng)'),
            'name_uk' => Yii::t('menu', 'Name (Ukr)'),
        ];
    }
    
    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules()
    {
        return array_merge (parent::rules(), [
            [['name_ru', 'name_en', 'name_uk', 'state'], 'required'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 150],
            ['url', 'string', 'max' => 100],
            ['class', 'string', 'max' => 250],
            ['state', 'integer', 'max' => 250],
        ]);
    }
    
    /**
     * Создает новый пункт меню и помещает его в начало списка
     */
    public function makeFirst()
    {
        // Получить самый первый пункт меню (с минимальным lft и depth)
        $firstItem = self::find()->orderBy('depth ASC, lft ASC')->one();
        if ($firstItem !== null) {
            // Помещаем новый пункт меню перед самым первым пунктом (делаем новый пункт первым)
            $this->insertBefore($firstItem);
        } else {
            // Если нет пунктов меню, делаем наш новый пункт root-ом
            $this->makeRoot();
            // Используется сторонняя js библиотека для администрированием меню, в ней у первого элемента depth = 1
            // /js/jquery.mjs.nestedSortable.js - http://mjsarfatti.com/sandbox/nestedSortable
            $this->depth = 1;
            $this->update(false, ['depth']);
            /**
             * @todo Если добавить через нашу функцию первый элемент, он станет root-ом. Следующий элемент не добавляется через эту функцию
             * На шаге $this->insertBefore($firstItem); - получим ошибку, т.к. в текущей структуре у нас root-ом является невидимый элемент, а мы сделали своего roota
             */
        }
    }
    
    /**
     * Возвращает названия пункта меню для текущего языка
     * @return string
     */
    public function getName()
    {
        $currLangAttribute = 'name_' . Yii::$app->language;
        return $this->$currLangAttribute;
    }
    
    /**
     * Удаляет вложенные пункты меню (устанавливает соответствующий флаг)
     */
    public function deleteChildren()
    {
        foreach($this->children()->all() as $child) {
            /* @var $child MenuItem */
            $child->state = CommonModel::STATE_DELETED;
            $child->update(false, ['state']);
        }
    }
    
}
