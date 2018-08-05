<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 6/28/18
 * Time: 8:41 PM
 */

namespace Web;

use App\Helper\Helper;
use App\ServerHandler\ServerHandler;

class AdminController extends BaseController
{
    public function index($siteName){
        if(!Helper::isSiteExists($siteName)){
            header('Location: /generator');
        }

        $renderData = ['sitename' => $siteName];

        $serverHandler = new ServerHandler($siteName);
        if(!$serverHandler->isSecureFilesExists()){
            $serverHandler->generateSecureFiles();
            $renderData['showpassword'] = $serverHandler->getPassword();
        }else{
            $this->auth($serverHandler);
        }
        $renderData['form_errors'] = SessionHelper::getFormErrors();
        $renderData['success'] = SessionHelper::getSuccess();
        SessionHelper::removeByKey('form_errors');
        SessionHelper::removeByKey('success');

        $renderData['url'] = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$siteName.".".$_SERVER['HTTP_HOST']."/index.html";;
        $renderData['admin_url'] = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST']."/sites/".$siteName."/admin";

        $this->render('admin_index', $renderData);
    }

    public function download($siteName){
        if(!$this->checkAuth($siteName)){
            return;
        }
        $fileName = HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$siteName.DIRECTORY_SEPARATOR.'archive.zip';
        if(!file_exists($fileName)){
            die('File not exists');
        }
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=archive.zip");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile("$fileName");
        exit;
    }

    public function delete($siteName){
        if(!$this->checkAuth($siteName)){
            return;
        }
        $directory = HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$siteName;
        Helper::deleteDir($directory);

        header('Location: /generator');
    }

    public function bindDomain($siteName){
        if(!$this->checkAuth($siteName)){
            return;
        }
        $formValidator = new FormValidator($_POST);
        if(!$formValidator->validateBindForm()){
            SessionHelper::setFormErrors($formValidator->getErrors());
            header('Location: /sites/'.$siteName.'/admin');
            return;
        }

        $handler = new ServerHandler($siteName);
        $currentUrl = $siteName.'.'.$_SERVER['HTTP_HOST'];
        $handler->makeSitesFile($formValidator->getDomainValue(), $currentUrl);
        $handler->enableSite();
        $handler->reload();

        SessionHelper::setSuccess();
        header('Location: /sites/'.$siteName.'/admin');
        return;
    }

    private function auth($serverHandler) {

        list($user,$hashedPassw) = $serverHandler->getCreditionals();
        if(!$user || !$hashedPassw){
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            exit;
        }
        if($user !== $serverHandler->getSitename()){
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            exit;
        }

        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        $is_not_authenticated = (
            !$has_supplied_credentials ||
            $_SERVER['PHP_AUTH_USER'] != $user ||
            $serverHandler->getHash($_SERVER['PHP_AUTH_PW']) != $hashedPassw
        );
        if ($is_not_authenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            exit;
        }
        return true;
    }

    public function checkAuth($siteName){
        if(!Helper::isSiteExists($siteName)){
            header('Location: /generator');
            return false;
        }
        $this->auth((new ServerHandler($siteName)));
        if($siteName != $_SERVER['PHP_AUTH_USER']){
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            return false;
        }
        return true;
    }
}