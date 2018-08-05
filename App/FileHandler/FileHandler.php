<?php

namespace App\FileHandler;

use App\Exceptions\CsvException;

class FileHandler{

    private $sitename;
    private $template;

    public function __construct($sitename,$template)
    {
        $this->sitename = $sitename;
        $this->template = $template;
    }

    public function validate(){
        $validator = new FileValidator($this->sitename, $this->template);
        return $validator->validate();
    }

    public function CsvToJson()
    {
        $csvFileFullName = $this->getCsvFileName();

        if(!is_dir(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->sitename)){
            mkdir(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->sitename);
        }else if(count(glob(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->sitename)) > 0){
            //clear output folder
            array_map('unlink', glob(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->sitename.DIRECTORY_SEPARATOR."*"));
        }

        $csv = new \ParseCsv\Csv($csvFileFullName);
        if(!$csv->data){
            throw new CsvException('Data is Empty');
        }

        $dataValidator = new CsvDataValidator();
        $dataValidator->validate($csv->data);

        file_put_contents(TMP_JSON_DIR.DIRECTORY_SEPARATOR.$this->sitename.'.json',json_encode($csv->data));
    }

    public function getCsvFileName()
    {
        return CSV_DIR.DIRECTORY_SEPARATOR.$this->sitename.'.csv';
    }

    public function removeJsonFile(){
        unlink(TMP_JSON_DIR.DIRECTORY_SEPARATOR.$this->sitename.'.json');
    }

    public function removeCsvFile(){
        unlink(CSV_DIR.DIRECTORY_SEPARATOR.$this->sitename.'.csv');
    }
}