<?php

if (PHP_SAPI == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) return false;
}

session_start();

require_once 'vendor/autoload.php';
date_default_timezone_set('Europe/Belgrade');

$router = new \Bramus\Router\Router();

$router->get('/generator', '\Web\WebController@index');
$router->get('/oauth2callback', '\Web\WebController@OAuth2Callback');
$router->post('/parse_url', '\Web\WebController@parseUrl');
$router->get('/make', '\Web\WebController@make');
$router->get('/sites/([a-z0-9-]+)/admin', '\Web\AdminController@index');
$router->get('/sites/([a-z0-9-]+)/admin/download', '\Web\AdminController@download');
$router->get('/sites/([a-z0-9-]+)/admin/delete', '\Web\AdminController@delete');
$router->post('/sites/([a-z0-9-]+)/admin/binddomain', '\Web\AdminController@bindDomain');
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>Not Found</h1>';
});
$router->run();