<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 7/5/18
 * Time: 8:58 PM
 */

namespace App\ServerHandler;

use App\Helper\Helper;

class ServerHandler
{

    private $siteName;
    private $htpassword;

    public function __construct($siteName)
    {
        $this->siteName = $siteName;
    }

    public function isSecureFilesExists(){
        return file_exists(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.'access'.DIRECTORY_SEPARATOR.'.htaccess')
            && file_exists(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.'access'.DIRECTORY_SEPARATOR.'.htpasswd');
    }

    public function generateSecureFiles(){
        mkdir(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.'access');
        $this->makeAccessFile();
        $this->makePasswordFile();
    }

    public function getPassword(){
        return $this->htpassword;
    }

    public function getSiteName(){
        return $this->siteName;
    }

    private function makeAccessFile()
    {
        $content =
            'Authtype Basic'.PHP_EOL.
            'AuthName "Please fill login and password fields"'.PHP_EOL.
            'AuthUserFile '.HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.'access'.DIRECTORY_SEPARATOR.'.htpasswd'.PHP_EOL.
            'Require valid-user';
        file_put_contents(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.'access'.DIRECTORY_SEPARATOR.'.htaccess',$content);
    }

    public function makeSitesFile($domain,$currentUrl){
        $cleanDomain = str_replace(['http://','https://'],'',$domain);
        $content =
            '<VirtualHost *:80>'.PHP_EOL.
            'ServerName '.$cleanDomain.PHP_EOL.
            'ServerAdmin webmaster@localhost'.PHP_EOL.
            'DocumentRoot '.HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.PHP_EOL.
            'SetEnv APPLICATION_ENV dev'.PHP_EOL.
            '</VirtualHost>'.PHP_EOL.
            '<VirtualHost *:80>'.PHP_EOL.
            'ServerName '.$currentUrl.PHP_EOL.
            'ServerAdmin webmaster@localhost'.PHP_EOL.
            'Redirect / '. $domain.'/'.PHP_EOL.
            'DocumentRoot '.HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.PHP_EOL.
            'SetEnv APPLICATION_ENV dev'.PHP_EOL.
            '</VirtualHost>';
        file_put_contents(APACHE_SITES_DIR.DIRECTORY_SEPARATOR.$this->siteName.'.conf',$content);
    }

    public function enableSite(){
        shell_exec('/usr/bin/sudo /usr/sbin/a2ensite '.$this->siteName.' &');
    }

    public function reload(){
        shell_exec('/usr/bin/sudo /usr/sbin/service apache2 reload &');
    }

    private function makePasswordFile()
    {
        $this->htpassword = Helper::generatePassword(10);
        $hash = base64_encode(sha1($this->htpassword, true));
        $contents = $this->siteName . ':{SHA}' . $hash;
        file_put_contents(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.'access'.DIRECTORY_SEPARATOR.'.htpasswd', $contents);
    }

    public function getCreditionals(){
        if(!file_exists(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.'access'.DIRECTORY_SEPARATOR.'.htpasswd')){
            return [false,false];
        }
        $credString = file_get_contents(HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->siteName.DIRECTORY_SEPARATOR.'access'.DIRECTORY_SEPARATOR.'.htpasswd');

        return explode(':',str_replace('{SHA}','',$credString));
    }

    public function getHash($string){
        return base64_encode(sha1($string, true));
    }
}