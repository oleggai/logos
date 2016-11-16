<?php

namespace app\controllers;

use app\classes\AttachedDocClass;
use app\models\attached_doc\AttachedDoc;
use app\models\attached_doc\AttachedDocFile;
use app\models\common\CommonModel;
use app\models\dictionaries\access\LoginForm;
use app\models\common\sys\SysEntity;
use app\models\ew\ExpressWaybill;
use app\modules\ss\StorageServer;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\common\UploadManual;

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;


/**
 * Контроллер сайта (общий)
 * @package app\controllers
 */
class SiteController extends CommonController
{

    /**
     * Начальная инициализация контроллера
     */
    public function init(){

        $this->accessCheck = '_none'; // имя сущности для проверки
        parent::init();
    }

    /**
     * Поведения контроллера
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Доступные дейсвия контроллера
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Начальная страница сайта
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = 'index_page';
        return $this->render('index');
    }

    public function actionGrid()
    {
        $this->layout = 'index_page';
        return $this->render('mydatagrid');
    }

    /**
     * Авторизация
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Выход из системы
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        SysEntity::saveOperation(null,0,CommonModel::OPERATION_LOGOUT);

        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionShowManual()
    {
        $s = DIRECTORY_SEPARATOR;
        $fileName = Yii::getAlias('@app') . $s . 'own_files' . $s . 'instructions' . $s . 'manual.pdf';
        if (file_exists($fileName)) {
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $fileName . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($fileName));
            header('Accept-Ranges: bytes');

            return readfile($fileName);
        }
    }

    public function actionUploadManual()
    {
        $this->enableCsrfValidation = false;

        $model = new UploadManual();

        if (Yii::$app->request->isPost) {
            $model->pdfFile = UploadedFile::getInstance($model, 'pdfFile');
            if ($model->upload()) {

            }
        }

        return $this->render('manual', ['model' => $model]);
    }

    public function actionDeleteManual()
    {
        $s = DIRECTORY_SEPARATOR;
        $fileName = Yii::getAlias('@app') . $s . 'own_files' . $s . 'instructions' . $s . 'manual.pdf';
        if (file_exists($fileName))
            unlink($fileName);

        return $this->render('manual', ['model' => new UploadManual()]);
    }

    public function actionLoadGuideToExcel() {
        $data = json_decode(Yii::$app->request->post()['grid-data']);
        $guideName = Yii::$app->request->post()['guide-name'];
        $headers = explode(';', Yii::$app->request->post()['headers']);
        $ids = explode(',', Yii::$app->request->post()['ids']);
        $this->renderFile('@app/views/guide-xls/guidexls.php', ['data' => $data,
            'guideName' => $guideName, 'headers' => $headers, 'ids' => $ids]);
    }


   public function actionBoxSpout() {
       require_once '../vendor/spout-2.4.1/src/Spout/Autoloader/autoload.php';

       $writer = WriterFactory::create(Type::XLSX); // for XLSX files
//$writer = WriterFactory::create(Type::CSV); // for CSV files
//$writer = WriterFactory::create(Type::ODS); // for ODS files

       $filePath = '../views/guide-xls/grid.xlsx';
       $fileName = 'excel.xlsx';

       $writer->openToFile($filePath); // write data to a file or to a PHP stream
$writer->openToBrowser($fileName); // stream data directly to the browser

       $ews1 = ExpressWaybill::find()->asArray()->all();
       $ews = $ews1;
       foreach($ews1 as $ew) {
           $ews[] = $ew;
       }

       $ews1 = $ews;
       foreach($ews1 as $ew) {
           $ews[] = $ew;
       }

       Yii::trace('Before cycle');
/*       foreach($ews as $ew) {
           $writer->addRow($ew); // add a row at a time
       }*/

       $writer->addRows($ews);

       Yii::trace('After cycle');

       $writer->close();
       Yii::trace('After close');
   }


    public function actionPhpExcel() {
        $objPHPExcel = \PHPExcel_IOFactory::load("../views/guide-xls/grid.xlsx");

        $objPHPExcel->getProperties()->setCreator("NP");
        $objPHPExcel->getProperties()->setLastModifiedBy("NP");
        $objPHPExcel->getProperties()->setTitle("Office 2003 XLS Document");
        $objPHPExcel->getProperties()->setSubject("Office 2003 XLS Document");
        $objPHPExcel->getProperties()->setDescription("Document for Office 2003 XLS.");

        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        $ews1 = ExpressWaybill::find()->asArray()->all();
        $ews = $ews1;
        foreach($ews1 as $ew) {
            $ews[] = $ew;
        }

        Yii::trace('Before cycle');
        $i = 1;
        foreach($ews as $ew) {
            $sheet->fromArray($ew, null, 'A'.$i);
            ++$i;
        }

        Yii::trace('After cycle');

        $guideName = 'phpExcel';
        header ( "Expires: Mon, 1 Apr 2050 05:00:00 GMT" );
        header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Cache-Control: no-cache, must-revalidate" );
        header ( "Pragma: no-cache" );
        header ('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header ( 'Content-Disposition: attachment; filename="'.$guideName.'_'.date('dmY_His').'.xlsx"' );

        Yii::trace('Before load');
// Выводим содержимое файла
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('php://output');
        Yii::trace('After load');
    }

    public function actionTestStorage(){

        $s = new \app\classes\StorageConnector();
        $user_id = Yii::$app->user->id;

        $file_id = $s->saveFile($user_id, 'test.txt','contentcontentcontentcontent',['entity_id'=>123, 'entity_code'=>'EW']);
        echo  'saveFile = '.$file_id . '<br>';

        $content  = $s->getFile($user_id,$file_id);
        echo  'getFile = '.$content. '<br>';

        $delete_result = $s->deleteFile($user_id,$file_id);
        echo  'deleteFile = '.$delete_result. '<br>';
    }

    /**
     * Скачивание файлов прикрепленных документов
     */
    public function actionGetFile() {
        $getParams = Yii::$app->request->get();
        $attachedDocFileId = $getParams['attached_doc_file_id'];
        $s = DIRECTORY_SEPARATOR;
        $attachedDocFileObj = AttachedDocFile::findOne(['id' => $attachedDocFileId]);
        $sysFile = $attachedDocFileObj->sysFile;
        $filePath = Yii::$app->basePath.$s.StorageServer::getFilesPath().$s.$sysFile->file_path.$s.$sysFile->file_name;
        Yii::$app->getResponse()->sendFile($filePath);
    }

    /**
     * Удаление файла ПД
     * @return string
     */
    public function actionDeleteFile() {
        $postParams = Yii::$app->request->post();
        // Массив ид файлов прикрепленных к ПД
        $attachedFileIds = explode(',', $postParams['attachedFileIds']);
        try {
            AttachedDocClass::deleteFiles($attachedFileIds);
        }
        catch(Exception $e) {
            return Json::encode(['res' => 1]);
        }
        return Json::encode(['res' => 1]);
    }

    /**
     * Удаление ПД, и всех прикрепленных файлов к ему
     */
    public function actionDeleteDocument() {
        $postParams = Yii::$app->request->post();
        // Массив ид ПД
        $attachedDocIds = explode(',', $postParams['attachedDocIds']);
        $entityName = $postParams['entityName'];
        $entityId = $postParams['entityId'];
        try {
            foreach ($attachedDocIds as $attachedDocId) {
                $attachedDocFiles = AttachedDocFile::findAll(['attacheddoc_id' => $attachedDocId]);
                $attachedFileIds = ArrayHelper::getColumn($attachedDocFiles, function ($element) {
                    return $element->id;
                });
                // Удаляем файлы прикрепленные к ПД
                AttachedDocClass::deleteFiles($attachedFileIds);

                // Удаляем связь сущность-ПД
                $modelEntity = AttachedDoc::getEntityModel($entityId, $attachedDocId, $entityName);
                $modelEntity->delete();

                // Удаляем ПД
                $attachedDoc = AttachedDoc::findOne(['id' => $attachedDocId]);
                $attachedDoc->delete();
            }
        }
        catch(Exception $e) {
            return Json::encode(['res' => 1]);
        }
        return Json::encode(['res' => 1]);
    }
}
