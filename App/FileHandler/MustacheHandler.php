<?php

namespace App\FileHandler;


class MustacheHandler
{
    public static function getRenderMustacheObject($templateName){
        $options =  array('extension' => '.html');
        $viewPath = INPUT_TEMPLATES_DIR.DIRECTORY_SEPARATOR.$templateName;
        return self::getMustacheObject($options,$viewPath);
    }

    public static function getWebMustacheObject(){
        $options =  array('extension' => '.html');
        $viewPath = WEB_VIEW_DIR;
        return self::getMustacheObject($options,$viewPath);
    }

    private static function getMustacheObject($options,$viewpath){
        $tmplObj = new \Mustache_Engine(array(
            'loader' => new \Mustache_Loader_FilesystemLoader($viewpath, $options),
        ));
        return $tmplObj;
    }
}