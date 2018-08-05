<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 6/9/18
 * Time: 11:55 PM
 */

namespace Web;


class SessionHelper
{

    public static function purgeSession(){
        session_destroy();
    }

    public static function removeByKey($key){
        if(isset($_SESSION[$key])){
            unset($_SESSION[$key]);
        }
    }

    public static function setFormErrors($formErrors){
        if(isset($formErrors['site_name']) && $formErrors['site_name']){
            $_SESSION['form_errors']['site_name'] = $formErrors['site_name'];
        }
        if(isset($formErrors['sheet_link']) && $formErrors['sheet_link']){
            $_SESSION['form_errors']['sheet_link'] = $formErrors['sheet_link'];
        }
        if(isset($formErrors['domain']) && $formErrors['domain']){
            $_SESSION['form_errors']['domain'] = $formErrors['domain'];
        }
    }

    public static function getFormErrors(){
        return (isset($_SESSION['form_errors']) && $_SESSION['form_errors']) ? $_SESSION['form_errors'] : false;
    }

    public static function setErrors($errors){
        $_SESSION['errors'] = $errors;
    }

    public static function getErrors(){
        return ($_SESSION['errors']) ? $_SESSION['errors'] : false;
    }

    public static function setSheetId($sheetId){
        $_SESSION['sheet_id'] = isset($sheetId) ? $sheetId : false;
    }

    public static function setTemplate($template){
        $_SESSION['template'] = isset($template) ? $template : false;
    }

    public static function getSheetId(){
        return isset($_SESSION['sheet_id']) ? $_SESSION['sheet_id'] : false;
    }

    public static function getSiteName(){
        return isset($_SESSION['site_name']) ? $_SESSION['site_name'] : false;
    }

    public static function getTemplate(){
        return isset($_SESSION['template']) ? $_SESSION['template'] : false;
    }

    public static function setRequestValues($values){
        $_SESSION['site_name'] = isset($values['site_name']) ? $values['site_name'] : false;
        $_SESSION['sheet_link'] = isset($values['sheet_link']) ? $values['sheet_link'] : false;
    }

    public static function getRequestValues(){
        return [
            'site_name' => (isset($_SESSION['site_name'])) ? $_SESSION['site_name'] : false,
            'sheet_link' => (isset($_SESSION['sheet_link'])) ? $_SESSION['sheet_link'] : false,
            'template' => (isset($_SESSION['template'])) ? $_SESSION['template'] : false
        ];
    }

    public static function checkDataEmpty(){
        return (!isset($_SESSION['site_name']) || !$_SESSION['site_name']
            || !isset($_SESSION['sheet_id']) || !$_SESSION['sheet_id']
            || !isset($_SESSION['template']) || !$_SESSION['template']) ? true : false;
    }

    public static function setSuccess(){
        $_SESSION['success'] = true;
    }

    public static function getSuccess(){
        return isset($_SESSION['success']) ? $_SESSION['success'] : false;
    }
}