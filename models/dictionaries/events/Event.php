<?php

namespace app\models\dictionaries\events;

use app\models\common\CommonModel;
use ReflectionClass;

/**
 * Класс событий ИС
 * @package app\models\events
 */
class Event {

    /**
     * ЭН создана
     */
    const EW_CREATE = "EW-1.0";
    /**
     * ЭН отредактирована
     */
    const EW_UPDATE = "EW-1.1";
    /**
     * ЭН закрыта
     */
    const EW_CLOSE = "EW-1.2";
    /**
     * ЭН удалена
     */
    const EW_DELETE = "EW-1.3";
    /**
     * ЭН добавлена в МН
     */
    const EW_LINK_MN = "EW-1.4";
    /**
     * ЭН удалена из МН
     */
    const EW_UNLINK_MN = "EW-1.5";
    /**
     * Распечатана маркировка 1-го места (<количество копий> коп., <количество страниц> стр.)
     */
    const EW_PRINT_1MARK = "EW-1.6";
    /**
     * Распечатан стикер (<количество копий> коп. <количество страниц> стр.)
     */
    const EW_PRINT_STICKER = "EW-1.7";
    /**
     * Распечатан инвойс (<количество копий> коп., <количество страниц> стр.)
     */
    const EW_PRINT_INVOICE = "EW-1.8";
    /**
     * ЭН добавлена в ВТБУ
     */
    const EW_LINK_CBSS = "EW-1.9";
    /**
     * ЭН удалена из ВТБУ
     */
    const EW_UNLINK_CBSS = "EW-1.10";
    /**
     * ЭН добавлена в СФ
     */
    const EW_LINK_SF = "EW-1.11";
    /**
     * ЭН удалена из СФ
     */
    const EW_UNLINK_SF = "EW-1.12";
    /**
     * Распечатана СФ по ЭН (<количество копий> коп., <количество страниц> стр.)
     */
    const EW_PRINT_SF = "EW-1.13";
    /**
     * Распечатан МН по ЭН (<количество копий> коп., <количество страниц> стр.)
     */
    const EW_PRINT_MN = "EW-1.14";
    /**
     * Распечатан ЕТД по ЭН (<количество копий> коп., <количество страниц> стр.)
     */
    const EW_PRINT_ETD = "EW-1.15";
    /**
     * Распечатано авиаместо по ЕТД (<количество копий> коп., <количество страниц> стр.)
     */
    const EW_PRINT_ETD_AVIA = "EW-1.16";
    /**
     * ЭН добавлена в АППГ
     */
    const EW_LINK_AR = "EW-1.17";
    /**
     * ЭН удалена из АППГ
     */
    const EW_UNLINK_AR = "EW-1.18";
    /**
     * Распечатан АППГ по ЭН (<количество копий> коп., <количество страниц> стр.)
     */
    const EW_PRINT_AR = "EW-1.19";
    /**
     * По ЭН отсканирован ШК <номер ШК АS1.2. п. 0.0.1> на <подразделении АD6.11 п. 1.1.3>
     */
    const EW_SCAN_BC = "EW-1.20";
    /**
     * К ЭН добавлено СКД (<количество стр. СКД> стр.)
     */
    const EW_LINK_SKD = "EW-1.21";
    /**
     * К ЭН добавлено файл
     */
    const EW_LINK_FILE = "EW-1.22";
    /**
     * К ЭН добавлено фото
     */
    const EW_LINK_IMAGE = "EW-1.23";
    /**
     * По ЭН отменено последнее редактирование
     */
    const EW_UPDATE_CANCEL = "EW-1.24";
    /**
     * По ЭН отменено удаление
     */
    const EW_DELETE_CANCEL = "EW-1.25";
    /**
     * По ЭН отменено закрытие
     */
    const EW_CLOSE_CANCEL = "EW-1.26";
    /**
     * Распечатан СКД по ЭН (<количество копий> коп., <количество страниц> стр.)
     */
    const EW_PRINT_SCD = "EW-1.27";
    /**
     * Распечатано фото по ЭН (<количество копий> коп.
     */
    const EW_PRINT_IMAGE = "EW-1.28";
    /**
     * ЭН добавлена в РПО
     */
    const EW_LINK_RAS = "EW-1.29";
    /**
     * ЭН удалена из РПО
     */
    const EW_UNLINK_RAS = "EW-1.30";
    /**
     * Распечатан РПО по ЭН (<количество копий> коп., <количество страниц> стр.)
     */
    const EW_PRINT_RAS = "EW-1.31";
    /**
     * Инвойс по ЭН выгружен в Excel
     */
    const EW_INVOICE_EXCEL = "EW-1.32";
    /**
     * ЭН добавлена в ЕТД (через МН)
     */
    const EW_LINK_ETD = "EW-1.33";
    /**
     * ЭН удалена из ЕТД (через МН)
     */
    const EW_UNLINK_ETD = "EW-1.34";
    /**
     * Печать заявления в таможню
     */
    const EW_PRINT_STATEMENT = "EW-1.35";
    /**
     * Заявление в таможню ексель
     */
    const EW_STATEMENT_EXCEL = "EW-1.36";
    /**
     * Выгрузка реестра ТММ в ексель
     */
    const EW_REGISTRY_TMM_EXCEL = "EW-1.37";
    /**
     * Печати Акта ПП
     */
    const EW_PRINT_FORM_CARGO_AR = "EW-1.38";
    /**
     * Выгрузки Акта ПП в эксель
     */
    const EW_FORM_CARGO_AR_EXCEL = "EW-1.39";
    /**
     * Печать упаковочного листа
     */
    const EW_PRINT_PACKING_LIST = "EW-1.40";
    /**
     * Выгрузка упаковочного листа в ексель
     */
    const EW_PACKING_LIST_EXCEL = "EW-1.41";
    /**
     * ЭН создана на основании ЗЗГ (вручную)
     */
    const EW_CREATE_PCR_MANUAL = "EW-2.1.0";
    /**
     * ЭН создана на основании ЗЗГ (автоматически)
     */
    const EW_CREATE_PCR_AUTO = "EW-2.2.0";
    /**
     * ЭН создана на основании ИД (вручную)
     */
    const EW_CREATE_ID_MANUAL = "EW-3.1.0";
    /**
     * ЭН создана на основании ИД (автоматически)
     */
    const EW_CREATE_ID_AUTO = "EW-3.2.0";
    /**
     * Отредактирован статус трекинга
     */
    const EW_STATUS_POSTED = "EW-1.42";
    /**
     * Отредактирована причина недоставки
     */
    const EW_NONDELIVERY_POSTED = "EW-1.43";

    /**
     * МН создан
     */
    const MN_CREATE = "MN-1.0";
    /**
     * МН отредактирован
     */
    const MN_UPDATE = "MN-1.1";
    /**
     * МН удален
     */
    const MN_DELETE = "MN-1.2";
    /*
     * МН добавлен в ЕТД
     */
    const MN_LINK_ETD = "MN-1.3";
    /*
     * МН удален из ЕТД
     */
    const MN_UNLINK_ETD = "MN-1.4";
    /**
     * МН распечатан (<количество копий> коп., <количество страниц> стр.)
     */
    const MN_PRINT = "MN-1.5";
    /**
     * К МН добавлено СКД (<количество стр. СКД> стр.)
     */
    const MN_LINK_SKD = "MN-1.6";
    /**
     * К МН добавлено файл
     */
    const MN_LINK_FILE = "MN-1.7";
    /**
     * К МН добавлено фото
     */
    const MN_LINK_IMAGE = "MN-1.8";
    /**
     * По МН отменено последнее редактирование
     */
    const MN_UPDATE_CANCEL = "MN-1.9";
    /**
     * По МН отменено удаление
     */
    const MN_DELETE_CACNEL = "MN-1.10";
    /**
     * Распечатан СКД по МН (<количество копий> коп., <количество страниц> стр.)
     */
    const MN_PRINT_SKD = "MN-1.11";
    /**
     * Распечатано фото по МН (<количество копий> коп.
     */
    const MN_PRINT_IMAGE = "MN-1.12";
    /**
     * МН выгружен в Excel
     */
    const MN_EXPORT_EXCEL = "MN-1.13";


    /**
     * ЕТД создан
     */
    const ETD_CREATE = "ETD-1.0";
    /**
     * ЕТД отредактирован
     */
    const ETD_UPDATE = "ETD-1.1";
    /**
     * ЕТД удален
     */
    const ETD_DELETE = "ETD-1.2";
    /**
     * ЕТД распечатан (<количество копий> коп., <количество страниц> стр.)
     */
    const ETD_PRINT = "ETD-1.3";
    /**
     * Распечатано авиаместо по ЕТД (<количество копий> коп., <количество страниц> стр.)
     */
    const ETD_PRINT_AVIA = "ETD-1.4";
    /**
     * К ЕТД добавлено СКД (<количество стр. СКД> стр.)
     */
    const ETD_LINK_SKD = "ETD-1.5";
    /**
     * К ЕТД добавлено файл
     */
    const ETD_LINK_FILE = "ETD-1.6";
    /**
     * К ЕТД добавлено фото
     */
    const ETD_LINK_IMAGE = "ETD-1.7";
    /**
     * По ЕТД удалено последнее редактирование
     */
    const ETD_UPDATE_CANCEL = "ETD-1.8";
    /**
     * ПО ЕТД отменено удаление
     */
    const ETD_DELETE_CANCEL = "ETD-1.9";
    /**
     * Распечатан СКД по ЕТД (<количество копий> коп., <количество страниц> стр.)
     */
    const ETD_PRINT_SKD = "ETD-1.10";
    /**
     * Распечатано фото по ЕТД (<количество копий> коп., <количество страниц> стр.)
     */
    const ETD_PRINT_IMAGE = "ETD-1.11";
    /**
     * ЕТД выгружен в Excel
     */
    const ETD_EXPORT_EXCEL = "ETD-1.12";
    /**
     * По ЕТД отсканирован ШК авиаместа <номер ШК АS1.2. п. 0.0.1> на <подразделении АD6.11 п. 1.1.3>
     */
    const ETD_SCAN_BC = "ETD-1.12";


    /**
     * ВТБУ создана
     */
    const CBSS_CREATE = "CBSS-1.0";
    /**
     * ВТБУ отредактирована
     */
    const CBSS_UPDATE = "CBSS-1.1";
    /**
     * ВТБУ удалена
     */
    const CBSS_DELETE = "CBSS-1.2";
    /**
     * К ВТБУ добавлено СКД (<количество стр. СКД> стр.)
     */
    const CBSS_LINK_SKD = "CBSS-1.3";
    /**
     * К ВТБУ добавлено файл
     */
    const CBSS_LINK_FILE = "CBSS-1.4";
    /**
     * К ВТБУ добавлено фото
     */
    const CBSS_LINK_IMAGE = "CBSS-1.5";
    /**
     * По ВТБУ отменено последнее редактирование
     */
    const CBSS_UPDATE_CANCEL = "CBSS-1.6";
    /**
     * ПО ВТБУ отменено удаление
     */
    const CBSS_DELETE_CANCEL = "CBSS-1.7";
    /**
     * Распечатан СКД по ВТБУ (<количество копий> коп., <количество страниц> стр.)
     */
    const CBSS_PRINT_SKD = "CBSS-1.8";
    /**
     * Распечатано фото по ВТБУ (<количество копий> коп.
     */
    const CBSS_PRINT_IMAGE = "CBSS-1.9";


    /**
     * АППГ создан
     */
    const AR_CREATE = "AR-1.0";
    /**
     * АППГ отредактирован
     */
    const AR_UPDATE = "AR-1.1";
    /**
     * АППГ удален
     */
    const AR_DELETE = "AR-1.2";
    /**
     * АППГ распечатан (<количество копий> коп., <количество страниц> стр.)
     */
    const AR_PRINT = "AR-1.3";
    /**
     * К АППГ добавлено СКД (<количество стр. СКД> стр.)
     */
    const AR_LINK_SKD = "AR-1.4";
    /**
     * К АППГ добавлено файл
     */
    const AR_LINK_FILE = "AR-1.5";
    /**
     * К АППГ добавлено фото
     */
    const AR_LINK_IMAGE = "AR-1.6";
    /**
     * По АППГ удалено последнее редактирование
     */
    const AR_UPDATE_CANCEL = "AR-1.7";
    /**
     * По АППГ отменено удаление
     */
    const AR_DELETE_CANCEL = "AR-1.8";
    /**
     * Распечатан СКД по АППГ (<количество копий> коп., <количество страниц> стр.)
     */
    const AR_PRINT_SCK = "AR-1.9";
    /**
     * Распечатано фото по АППГ (<количество копий> коп.
     */
    const AR_PRINT_IMAGE = "AR-1.10";
    /**
     * АППГ выгружен в Excel
     */
    const AR_EXPORT_EXCEL = "AR-1.11";

    /**
     * СФ создана
     */
    const RC_CREATE = "RC-1.0";
    /**
     * СФ отредактирована
     */
    const RC_UPDATE = "RC-1.1";
    /**
     * СФ удалена
     */
    const RC_DELETE = "RC-1.2";
    /**
     * СФ распечатана (<количество копий> коп., <количество страниц> стр.)
     */
    const RC_PRINT = "RC-1.3";
    /**
     * К СФ добавлено СКД (<количество стр. СКД> стр.)
     */
    const RC_LINK_SKD = "RC-1.4";
    /**
     * К СФ добавлено файл
     */
    const RC_LINK_FILE = "RC-1.5";
    /**
     * К СФ добавлено фото
     */
    const RC_LINK_IMAGE = "RC-1.6";
    /**
     * По СФ отменено последнее редактирование
     */
    const RC_UPDATE_CANCEL = "RC-1.7";
    /**
     * По СФ отменено удаление
     */
    const RC_DELETE_CANCEL = "RC-1.8";
    /**
     * Распечатан СКД по СФ (<количество копий> коп., <количество страниц> стр.)
     */
    const RC_PRINT_SKD = "RC-1.9";
    /**
     * Распечатано фото по СФ (<количество копий> коп.
     */
    const RC_PRINT_IMGAGE = "RC-1.10";
    /**
     * СФ выгружен в Excel
     */
    const RC_EXPORT_EXCEL = "RC-1.11";


    /**
     * ЗЗГ создана
     */
    const PCR_CREATE = "PCR-1.0";
    /**
     * ЗЗГ отредактирована
     */
    const PCR_UPDATE = "PCR-1.1";
    /**
     * ЗЗГ удалена
     */
    const PCR_DELETE = "PCR-1.2";
    /**
     * Распечатана маркировка 1-го места (<количество копий> коп., <количество страниц> стр.)
     */
    const PCR_PRINT_1MARK = "PCR-1.3";
    /**
     * Распечатан стикер (<количество копий> коп., <количество страниц> стр.)
     */
    const PCR_PRINT_STICKER = "PCR-1.4";
    /**
     * Распечатан инвойс (<количество копий> коп., <количество страниц> стр.)
     */
    const PCR_PRINT_INVOICE = "PCR-1.5";
    /**
     * По ЗЗГ отсканирован ШК <номер ШК АS1.2. п. 0.0.1> на <подразделении АD6.11 п. 1.1.3>
     */
    const PCR_SCAN_BC = "PCR-1.6";
    /**
     * ЭН создана на основании ЗЗГ (вручную)
     */
    const PCR_EW_CREATED_MANUAL = "PCR-1.7";
    /**
     * ЭН создана на основании ЗЗГ (автоматически)
     */
    const PCR_EW_CREATED_AUTO = "PCR-1.8";
    /**
     * К ЭН добавлено СКД (<количество стр. СКД> стр.)
     */
    const PCR_LINK_SKD = "PCR-1.9";
    /**
     * К ЭН добавлено файл
     */
    const PCR_LINK_FILE = "PCR-1.10";
    /**
     * К ЭН добавлено фото
     */
    const PCR_LINK_IMAGE = "PCR-1.11";
    /**
     * По ЗЗГ отменено последнее редактирование
     */
    const PCR_UPDATE_CANCEL = "PCR-1.12";
    /**
     * По ЗЗГ отменено удаление
     */
    const PCR_DELETE_CANCEL = "PCR-1.13";
    /**
     * Распечатан СКД по ЗЗГ (<количество копий> коп., <количество страниц> стр.)
     */
    const PCR_PRINT_SKD = "PCR-1.14";
    /**
     * Распечатано фото по ЗЗГ (<количество копий> коп.
     */
    const PCR_PRINT_IMAGE = "PCR-1.15";
    /**
     * Инвойс по ЗЗГ выгружен в Excel
     */
    const PCR_EXPORT_EXCEL = "PCR-1.16";


    /**
     * РПО создан
     */
    const RAS_CREATE = "RAS-1.0";
    /**
     * РПО отредактирован
     */
    const RAS_UPDATE = "RAS-1.1";
    /**
     * РПО удален
     */
    const RAS_DELETE = "RAS-1.2";
    /**
     * Распечатан РПО (<количество копий> коп., <количество страниц> стр.)
     */
    const RAS_PRINT = "RAS-1.3";
    /**
     * К РПО добавлено СКД (<количество стр. СКД> стр.)
     */
    const RAS_LINK_SKD = "RAS-1.4";
    /**
     * К РПО добавлено файл
     */
    const RAS_LINK_FILE = "RAS-1.5";
    /**
     * К РПО добавлено фото
     */
    const RAS_LINK_IMAGE = "RAS-1.6";
    /**
     * По РПО отменено последнее редактирование
     */
    const RAS_UPDATE_CANCEL = "RAS-1.7";
    /**
     * По РПО отменено удаление
     */
    const RAS_DELETE_CANCEL = "RAS-1.8";
    /**
     * Распечатан СКД по РПО (<количество копий> коп., <количество страниц> стр.)
     */
    const RAS_PRINT_SKD = "RAS-1.9";
    /**
     * Распечатано фото по РПО (<количество копий> коп.
     */
    const RAS_PRINT_IMAGE = "RAS-1.10";
    /**
     * РПО выгружен в Excel
     */
    const RAS_EXPORT_EXCEL = "RAS-1.11";


    /**
     * Вызов события ИС
     * @param $event string Тип события (константы из класса Event, например Event::RAS_PRINT_IMAGE)
     * @param $entity_id int Идентификатор сущности
     * @param array $ext_params Дополнительные параметры события
     * @return bool|array true при успешной обработке события, иначе массив ошибок возникших при обработке события
     */
    public static function callEvent($event, $entity_id, $ext_params = []){

        self::initStdCodes();

        $result = true;

        // получение типа события из таблицы list_events
        $log_type_model = self::getLogTypeModel($event, $entity_id, $ext_params);
        if (!$log_type_model){
            $result = [\Yii::t("app", "Event type with code '$event' not found")];
        }
        // получение модели события
        else {

            $history_model = self::getHistoryModel($log_type_model, $entity_id, $ext_params);
            if (!$history_model) {
                $result = [\Yii::t("app", "No handler for event with code '$event'")];
            }

            else if (!$history_model->save()){
                $result =  $history_model->errors;
            }
        }

        if ($result !== true && $ext_params['model'])
            $ext_params['model']->addErrors($result);

        return $result;
    }

    /**
     * Получение модели типа события  по коду
     * @param $event string Тип события (константы из класса Event, например Event::RAS_PRINT_IMAGE)
     * @param $entity_id int Идентификатор сущности
     * @param array $ext_params Дополнительные параметры события
     * @return null|ListEvents Модель типа события
     */
    private static function getLogTypeModel($event, $entity_id, $ext_params){

        return ListEvents::findOne(['code'=>$event]);
    }

    /**
     * Получение модели события
     * @param $event ListEvents Модель события
     * @param $entity_id int Идентификатор сущности
     * @param array $ext_params Дополнительные параметры события
     * @return CommonModel Модель сущности
     */
    private static function getHistoryModel($event, $entity_id, $ext_params){

        $log_entry = null;

        if (!$entity_id){
            return null;
        }

        // todo добавить события остальных сущностей
        //  событие накладной
        if (self::startsWith($event->code,EwHistoryEvents::getEventPrefix())){

            $log_entry = new EwHistoryEvents();
            $log_entry->ew_id = $entity_id;
        }
        //  событие манифеста
        else if (self::startsWith($event->code,MnHistoryEvents::getEventPrefix())){

            $log_entry = new MnHistoryEvents();
            $log_entry->mn_id = $entity_id;
        }
        // событие другой сущности...
        else if (false){

        }


        // общие св-ва для всех событий
        if ($log_entry){
            $log_entry->creator_user_id = \Yii::$app->user->identity->getId();
            $log_entry->event_id = $event->id;
        }

        return $log_entry;
    }


    private static function startsWith($str, $needle){
        $length = strlen($needle);
        return (substr($str, 0, $length) === $needle);
    }

    public static function initStdCodes(){

        if (!ListEvents::find()->count()){

            $last_category_code = null;
            $reflection = new ReflectionClass(__CLASS__);

            foreach ($reflection->getConstants() as $const){

                $category_code = explode('-',$const)[0];
                if ($category_code != $last_category_code){

                    $last_category_code = $category_code;
                    $category = new ListEvents();
                    $category->code = $last_category_code;
                    $category->level = 1;
                    $category->state = 1;
                    $category->visible = 1;
                    $category->description = $last_category_code;
                    $category->nameEn = $last_category_code;
                    $category->nameRu = $last_category_code;
                    $category->nameUk = $last_category_code;
                    $category->save();
                }

                $event = new ListEvents();
                $event->code = $const;
                $event->level = 2;
                $event->state = 1;
                $event->visible = 1;
                $event->parent_id = $category->id;
                $event->description = $const;
                $event->nameEn = $const;
                $event->nameRu = $const;
                $event->nameUk = $const;
                $event->save();
            }
        }
    }
}