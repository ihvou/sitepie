<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 6/11/18
 * Time: 11:45 PM
 */

namespace App\FileHandler;


class ZipHandler
{

    private $siteName;
    private $zip;

    public function __construct($siteName)
    {
        $this->siteName = $siteName;
        $this->zip = new \ZipArchive();
    }

    public function makeZip(){

        $archiveFilePath = HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.ZIP_FILE_NAME;
        if (!$this->zip->open($archiveFilePath, \ZipArchive::CREATE)) {
            return false;
        }

        $options = ['add_path' => './','remove_all_path'=>true];
        $this->zip->addGlob(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR."*.html",GLOB_BRACE,$options);

        $this->zip->addEmptyDir('assets');
        $this->addAssetsRecursively('assets');
        if (!$this->zip->status == \ZipArchive::ER_OK){
           return false;
        }
        $this->zip->close();
        return true;
    }

    private function addAssetsRecursively($path){
        $list = glob(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.'*');
        if(empty($list)){
            return;
        }
        foreach ($list as $item){
            $lastPart = array_pop(explode(DIRECTORY_SEPARATOR,$item));
            if(!is_dir($item)){
                $this->zip->addFile($item,$path.DIRECTORY_SEPARATOR.$lastPart);
            }else{

                $this->zip->addEmptyDir($path.DIRECTORY_SEPARATOR.$lastPart);
                $this->addAssetsRecursively($path.DIRECTORY_SEPARATOR.$lastPart);
            }
        }
        return;
    }

}