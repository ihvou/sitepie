<?php

namespace Web;

use App\FileHandler\GeneratorFacade;
use App\FileHandler\ZipHandler;
use Web\Google\GoogleDataSaver;
use Web\Google\GoogleSpreadsheet;
use App\Logger\Logger;

class WebController extends BaseController{

    public function index(){

        $renderData['form_errors'] = SessionHelper::getFormErrors();
        $renderData['errors'] = SessionHelper::getErrors();
        $renderData['values'] = SessionHelper::getRequestValues();
        $renderData['success'] = SessionHelper::getSuccess();
        if($renderData['success'] == 1){
            $renderData['url'] = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".SessionHelper::getSiteName().".".$_SERVER['HTTP_HOST']."/index.html";
            $renderData['admin_url'] = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST']."/sites/".SessionHelper::getSiteName()."/admin";
        }
        //echo WEB_VIEW_DIR;die();
        SessionHelper::purgeSession();
        $this->render('index',$renderData);
    }

    public function parseUrl(){
        $validator = new FormValidator($_POST);
        SessionHelper::setRequestValues($validator->getSafetyRequestData());

        if(!$validator->validateUrlForm()){
            SessionHelper::setFormErrors($validator->getErrors());
            header('Location: /generator');
            return;
        }

        $sheetId = $validator->getSheetLinkId();
        $siteName = $validator->getSiteNameValue();
        $template = $validator->getTemplateValue();

        if(!$sheetId){
            SessionHelper::setErrors(['Incorrect Sheet Id in Spreadsheet Link']);
            header('Location: /generator');
            return;
        }

        SessionHelper::setSheetId($sheetId);
        SessionHelper::setTemplate($template);

        $google = new GoogleSpreadsheet($siteName,$sheetId);
        $google->OAuthGetCredentals();
        header('Location: /make');
    }

    public function OAuth2Callback(){

        if(SessionHelper::checkDataEmpty())
        {
            SessionHelper::setErrors(['Authorization Error! Your Data is Empty']);
            header('Location: /generator');
            return;
        }
        if(!isset($_GET['code']) || !$_GET['code']){
            SessionHelper::setErrors(['Authorization Error! Empty Google Response']);
            header('Location: /make');
            return;
        }

        $google = new GoogleSpreadsheet(SessionHelper::getSiteName(),SessionHelper::getSheetId());
        $google->setNewAccessTokenFromGoogle($_GET['code']);
        header('Location: /make');
    }

    public function make(){
        if(SessionHelper::checkDataEmpty()){
            SessionHelper::setErrors(['Authorization Error! Your Data is Empty']);
            header('Location: /generator');
            return;
        }

        try {
            $google = new GoogleSpreadsheet(SessionHelper::getSiteName(), SessionHelper::getSheetId());
            $data = $google->getSpreadsheetData();

            $dataSaver = new GoogleDataSaver();
            $dataSaver->saveDataToCsv(SessionHelper::getSiteName(), $data);

        }catch (\Exception $e){
            SessionHelper::setErrors(['Authorization Error!']);
            Logger::writeLog('web_'.date('Y-m-d'),$e->getMessage()." : ".$e->getFile().' : '.$e->getLine());
            header('Location: /generator');
            return;
        }

        $webFacade = new GeneratorFacade(SessionHelper::getSiteName(), SessionHelper::getTemplate(),'web_'.date('Y-m-d'));
        $result = $webFacade->makeStaticSite();
        if(!$result){
            SessionHelper::setErrors($webFacade->getErrors());
            header('Location: /generator');
            return;
        }

        $zip = new ZipHandler(SessionHelper::getSiteName());
        if(!$zip->makeZip()){
            SessionHelper::setFormErrors(['Can\'t create zip archive. Try Again later']);
            header('Location: /generator');
            return;
        }

        SessionHelper::setSuccess();
        header('Location: /generator');
        return;
    }
}