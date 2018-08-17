<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 6/11/18
 * Time: 10:01 PM
 */

namespace App\FileHandler;

use App\Exceptions\CsvDataException;

class CsvDataValidator
{

    private $headerCount = 0;
    private $postsCount = 0;
    private $indexAdCount = 0;
    private $postPageAdCount = 0;

    private $types = ['header','post','ad','footer','custom_data','body'];

    private $adSubtypes = ['index_page_ad','post_page_ad','popunder_script'];

    private $requiredFields = [
        'header' =>
            [
                'main' => ['title','main_image','description','tags','date']
            ],
        'post' => ['title','date'],//['title','tags','date'],
        'ad' => ['media'],
        'body' => ['media'],
        'custom_data' => ['media']
        ];

    public function validate($data){

        foreach ($data as $i => $item) {

            $i++;
            $itemArray = (array)$item;

            //check status
            if((isset($itemArray['status']) && strtolower($itemArray['status']) == 'on') == false){
                continue;
            }

            if(!in_array($itemArray['item_type'],$this->types)){
                throw new CsvDataException('Data error. Row #'.($i+1).' Incorrect type. Valid types - '.implode(',',$this->types));
            }
            if($itemArray['item_type'] == 'header' && $itemArray['sub_type'] == 'main'){
                $this->headerCount++;
                $this->checkHeader($itemArray, $i);
            }
            if($itemArray['item_type'] == 'post'){
                $this->postsCount++;
                $this->checkPost($itemArray, $i);
            }
            if($itemArray['item_type'] == 'ad'){
                $this->checkAd($itemArray, $i);
                if($itemArray['sub_type'] == 'index_page_ad'){
                    $this->indexAdCount++;
                }
                if($itemArray['sub_type'] == 'post_page_ad'){
                    $this->postPageAdCount++;
                }
            }
        }
        if($this->headerCount !== 1){
            throw new CsvDataException('Incorrect Number of rows with type = "header"');
        }
        if(!$this->postsCount){
            throw new CsvDataException('Incorrect data - there is no rows with type = "post"');
        }
        if(!$this->postPageAdCount){
            throw new CsvDataException('Incorrect data - there is no ad rows with subtype = "post_page_ad"');
        }
        if(!$this->indexAdCount) {
            throw new CsvDataException('Incorrect data - there is no ad rows with subtype = "index_page_ad"');
        }
        return true;
    }

    private function checkHeader($headerData, $rowNumber){
        foreach ($this->requiredFields['header']['main'] as $headerField) {
            if((!isset($headerData[$headerField]) || !trim($headerData[$headerField])) && $headerData['sub_type'] == 'main'){
                throw new CsvDataException('Empty data: Row #'.($rowNumber+1).': "header" row column "'.$headerField.'"');
            }
        }
    }

    private function checkPost($postData,$rowNumber){
        foreach ($this->requiredFields['post'] as $postField) {
            if(!isset($postData[$postField]) || !trim($postData[$postField])){
                throw new CsvDataException('Empty data: Row #'.($rowNumber+1).' column "'.$postField.'"');
            }
        }
    }

    private function checkAd($adData,$rowNumber){
        foreach ($this->requiredFields['ad'] as $adField) {
            if(!isset($adData[$adField]) || !trim($adData[$adField])){
                throw new CsvDataException('Empty data: Row #'.($rowNumber+1).' column "'.$adField.'"');
            }
            if(!in_array($adData['sub_type'],$this->adSubtypes)){
                throw new CsvDataException('Incorrect subtype: Row #'.($rowNumber+1).' column "'.$adField.'". Valid types - '.implode(',',$this->adSubtypes));
            }
        }
    }

}