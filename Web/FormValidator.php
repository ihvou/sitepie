<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 5/29/18
 * Time: 8:57 PM
 */

namespace Web;


class FormValidator
{

    private $requestData;
    private $errors = [];

    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    public function validateBindForm(){
        if(!$this->checkEmptyFields(['domain'])){
            return false;
        }
        if(!filter_var($this->requestData['domain'],FILTER_VALIDATE_URL)){
            $this->errors['domain'] = 'Should be like "http://example.com"';
            return false;
        }
        return true;
    }

    public function validateUrlForm(){
        if(!$this->checkEmptyFields(['site_name','sheet_link','template'])){
            return false;
        }
        if(strlen($this->requestData['site_name']) > 50){
            $this->errors['site_name'] = 'Too long Site Name';
            return false;
        }
        if(!preg_match('/[A-Za-z0-9\-]+/',$this->requestData['site_name'])){
            $this->errors['site_name'] = 'Incorrect format of Site Name';
            return false;
        }
        if(file_exists(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->requestData['site_name']) && (count(glob(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->requestData['site_name']."/*"))) > 0){
            $this->errors['site_name'] = 'Site with name '.htmlentities($this->requestData['site_name']).' already exists';
            return false;
        }
        if(!filter_var($this->requestData['sheet_link'],FILTER_VALIDATE_URL,FILTER_FLAG_SCHEME_REQUIRED)){
            $this->errors['sheet_link'] = 'Incorrect format of Google Spreadsheet link';
            return false;
        }
        if(strpos($this->requestData['sheet_link'],'https://docs.google.com/spreadsheets/') !== 0){
            $this->errors['sheet_link'] = 'Incorrect format of Google Spreadsheet link';
            return false;
        }
        return true;
    }

    public function getErrors(){
        return $this->errors;
    }

    public function getSiteNameValue(){
        return htmlentities($this->requestData['site_name']);
    }

    public function getTemplateValue(){
        return htmlentities($this->requestData['template']);
    }

    public function getDomainValue(){
        return $this->requestData['domain'];
    }

    public function getSheetLinkId(){
        $pathArr = explode('/',parse_url($this->requestData['sheet_link'])['path']);
        if(count($pathArr) < 4){
            return false;
        }
        return $pathArr[3];
    }

    public function getSafetyRequestData(){
        return [
            'site_name' => (isset($this->requestData['site_name'])) ? trim(htmlentities($this->requestData['site_name'])) : false,
            'sheet_link' => (isset($this->requestData['sheet_link'])) ? trim(htmlentities($this->requestData['sheet_link'])) : false,
            'template' => 'default'
        ];
    }

    private function checkEmptyFields($fieldNameArray){
        $result = true;
        foreach ($fieldNameArray as $fieldName){
            if(!isset($_POST[$fieldName]) || !$_POST[$fieldName]){
                $this->errors[$fieldName] = 'This field is empty';
                $result = false;
            }
        }
        return $result;
    }

}