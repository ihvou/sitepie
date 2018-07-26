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

$router->get('/generator', '\App\Web\WebController@index');
$router->get('/oauth2callback', '\App\Web\WebController@OAuth2Callback');
$router->post('/parse_url', '\App\Web\WebController@parseUrl');
$router->get('/make', '\App\Web\WebController@make');
$router->get('/sites/([a-z0-9-]+)/admin', '\App\Web\AdminController@index');
$router->get('/sites/([a-z0-9-]+)/admin/download', '\App\Web\AdminController@download');
$router->get('/sites/([a-z0-9-]+)/admin/delete', '\App\Web\AdminController@delete');
$router->post('/sites/([a-z0-9-]+)/admin/binddomain', '\App\Web\AdminController@bindDomain');
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>Not Found</h1>';
});
$router->run();