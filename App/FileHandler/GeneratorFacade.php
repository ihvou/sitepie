<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 6/6/18
 * Time: 11:32 AM
 */

namespace App\FileHandler;


use App\Exceptions\CsvDataException;
use App\Exceptions\CsvException;
use App\Exceptions\ValidatorExcetion;

class GeneratorFacade
{
    private $siteName;
    private $template;
    private $logFileName;

    public function __construct($siteName, $template, $logFileName)
    {
        $this->logFileName = $logFileName;
        $this->template = $template;
        $this->siteName = $siteName;
    }

    private $errors = [];

    public function makeStaticSite(){

        \App\Logger\Logger::writeLog($this->logFileName,'Static generator job start!',false);

        try {
            $fileHandler = new \App\FileHandler\FileHandler($this->siteName,$this->template);
            $fileHandler->validate();

            $fileHandler->CsvToJson();
            \App\Logger\Logger::writeLog($this->logFileName,'Temporary json file saved',false);

            $render = new \App\FileHandler\Render(new \App\FileHandler\FileJsonHandler($this->siteName),$this->template);
            \App\Logger\Logger::writeLog($this->logFileName,'Saving HTML files...');
            $render->renderIndexPages();
            \App\Logger\Logger::writeLog($this->logFileName,'Index HTML files saved');
            $render->renderTagsPages();
            \App\Logger\Logger::writeLog($this->logFileName,'Tags HTML files saved');
            $render->renderPostsPages();
            \App\Logger\Logger::writeLog($this->logFileName,'Posts HTML files saved');
            $render->copyAssets();
            \App\Logger\Logger::writeLog($this->logFileName,'Assets files saved');

            $fileHandler->removeJsonFile();

        }catch (ValidatorExcetion $e){
            \App\Logger\Logger::writeLog($this->logFileName,$e->getMessage().' :: '.$e->getFile().' :: '.$e->getLine(),false);
            $this->errors[] = $e->getMessage();
            return false;
        }
        catch (CsvException $e){
            \App\Logger\Logger::writeLog($this->logFileName,$e->getMessage().' :: '.$e->getFile().' :: '.$e->getLine(),false);
            $this->errors[] = 'CSV Parse Error! Please, check your spreadsheet file';
            return false;
        }
        catch (CsvDataException $e){
            \App\Logger\Logger::writeLog($this->logFileName,$e->getMessage().' :: '.$e->getFile().' :: '.$e->getLine(),false);
            $this->errors[] = $e->getMessage();
            return false;
        }
        catch (\Exception $e){
            \App\Logger\Logger::writeLog($this->logFileName,$e->getMessage().' :: '.$e->getFile().' :: '.$e->getLine(),false);
            $this->errors[] = 'Generator Error! Please, try again later';
            return false;
        }

        \App\Logger\Logger::writeLog($this->logFileName,'Static generator job stop',false);
        return true;

    }

    public function getErrors(){
        return $this->errors;
    }
}