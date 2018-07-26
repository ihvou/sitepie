<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 7/5/18
 * Time: 8:33 PM
 */

namespace App\Web;

use App\FileHandler\MustacheHandler;

class BaseController
{
    protected function render($templateName, $renderData = []){
        $mustache = MustacheHandler::getWebMustacheObject();
        $tpl = $mustache->loadTemplate($templateName);
        echo $tpl->render($renderData);
    }
}