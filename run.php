<?php

set_time_limit(0);
date_default_timezone_set('Europe/Belgrade');

require_once 'vendor/autoload.php';

$logFileName = 'console_'.date('Y-m-d');

if($argc < 3){
    \App\Logger\Logger::writeLog($logFileName,'Need to pass 2 parameters! Exit!');
    exit(0);
}

$siteName = $argv[1];
$templateName = $argv[2];

\App\Logger\Logger::writeLog($logFileName,'Static generator job start!');

try {
    $fileHandler = new \App\FileHandler\FileHandler($siteName, $templateName);
    $validateResult = $fileHandler->validate();

    $fileHandler->CsvToJson();
    \App\Logger\Logger::writeLog($logFileName,'Temporary json file saved');

    $render = new \App\FileHandler\Render(new \App\FileHandler\FileJsonHandler($siteName),$templateName);
    \App\Logger\Logger::writeLog($logFileName,'Saving HTML files...');
    $render->renderIndexPages();
    \App\Logger\Logger::writeLog($logFileName,'Index HTML files saved');
    $render->renderTagsPages();
    \App\Logger\Logger::writeLog($logFileName,'Tags HTML files saved');
    $render->renderPostsPages();
    \App\Logger\Logger::writeLog($logFileName,'Posts HTML files saved');
    $render->renderPostsPages();
    \App\Logger\Logger::writeLog($logFileName,'Posts HTML files saved');

    $fileHandler->removeJsonFile();

}catch (\Exception $e){
    \App\Logger\Logger::writeLog($logFileName,$e->getMessage());
    exit(0);
}

\App\Logger\Logger::writeLog($logFileName,'Static generator job stop');

