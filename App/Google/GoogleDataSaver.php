<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 6/5/18
 * Time: 4:53 PM
 */

namespace App\Google;


class GoogleDataSaver
{

    public function __construct()
    {

    }

    public function saveDataToCsv($siteName,$data){
        $fileName = TMP_CSV_DIR.DIRECTORY_SEPARATOR.$siteName.'.csv';
        if(file_exists($fileName)){
            unlink($fileName);
        }
        touch($fileName);

        $csv = new \ParseCsv\Csv();
        foreach ($data as $item) {
            $csv->save($fileName, array($item), true);
        }
    }

}