<?php

namespace App\FileHandler;

use App\Exceptions\ValidatorExcetion;

class FileValidator{

    private $sitename;
    private $template;
    private $errors = [];

    public function __construct($sitename,$template)
    {
        $this->sitename = $sitename;
        $this->template = $template;
    }

    public function validate()
    {

        if(!$this->isCsvFileExist()){
            throw new ValidatorExcetion("Can't find csv file!");
        }

        foreach ([INDEX_TEMPLATE_NAME,TAG_TEMPLATE_NAME,POST_TEMPLATE_NAME] as $templateName) {
            if(!$this->isTemplateFileExists($templateName)){
                throw new ValidatorExcetion("There is no file '$templateName' for template = ".$this->template);
            }
        }

        return true;
    }

    public function getErrors(){
        return $this->errors;
    }

    private function isCsvFileExist()
    {
        $filePath = CSV_DIR.DIRECTORY_SEPARATOR.$this->sitename.'.csv';
        return file_exists($filePath);
    }

    private function isTemplateFileExists($filename)
    {
        $filePath = INPUT_TEMPLATES_DIR.DIRECTORY_SEPARATOR.$this->template.DIRECTORY_SEPARATOR.$filename.TEMPLATE_EXTENSION;
        return file_exists($filePath);
    }

}