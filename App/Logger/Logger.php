<?php
namespace App\Logger;

class Logger{

    private $logger;

    private $echo;

    private static $instance;

    private function __construct($fileName,$echo = true)
    {
        $this->logger = new \Monolog\Logger('site_generator');
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler(LOGS_DIR.DIRECTORY_SEPARATOR.$fileName.'.log', \Monolog\Logger::DEBUG));
        $this->echo = $echo;
    }

    private static function init($fileName,$echo = true)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($fileName,$echo);
        }
        return self::$instance;
    }

    public static function writeLog($fileName,$message,$echo = true)
    {
        $instance = self::init($fileName,$echo);
        $instance->logger->info($message);
        if($instance->echo){
            echo $message."\n";
        }
    }
}