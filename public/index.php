<?php
// Path: src/public/index.php
use Framework\Framework as Framework;
use Framework\HttpRequest ;
use Illuminate\Http\Response;
use Classes\Logger;
use Framework\DebugBar;



ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
require  './../vendor/autoload.php';
if(isset($_GET['debug'])){
    $debugbar = DebugBar::instance()->getDebugBar();
    $debugbar['time']->startMeasure('Execution', 'Total App execution time');  
}

require './../config/settings.php';
set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno)
{
    if (error_reporting() == 0) {
        return;
    }

    if (strpos($message, 'Failed to open stream') !== false || (strpos($message, 'Failed opening') !== false || strpos($message, 'require') !== false)) {
        return; // Ignore spécifiquement les erreurs d'include/require manqués
    }   
    
    // Lancer une exception uniquement pour les erreurs fatales
    if ($severity == E_ERROR || $severity == E_CORE_ERROR || $severity == E_COMPILE_ERROR || $severity == E_RECOVERABLE_ERROR || $severity == E_USER_ERROR) {
        throw new ErrorException($message, 0, $severity, $filename, $lineno);
    }

    // Journaliser toutes les autres erreurs, considérées comme non fatales
    if (!(error_reporting() & $severity)) {
        return; // Ne pas traiter les erreurs qui ne sont pas actuellement signalées
    }

    @Logger::debug($severity, [
        'message' => $message,
        'filename' => $filename,
        'lineno' => $lineno
    ]);
}

try{
    // echo APPFOLDER;exit;
    Framework::setAppFolder(APPFOLDER);
    $App = new Framework();
    // DebugBar uniquement en mode debug
    if (isset($_GET['debug'])) {
        $App->use("/.*", \Middlewares\DebugBarMiddleware::class);
    }    
    $App->use("/.*", \Middlewares\SessionMiddleware::class);
    $App->use("/admin/.*", \Middlewares\AuthMiddleware::class);    
    $App->use("/loginuser/", \Middlewares\AuthMiddleware::class);    
    $App->use("/logout/", \Middlewares\AuthMiddleware::class);    

    $App->use("/.*", \Middlewares\CacheMiddleware::class);
    $App->use("/.*", \Middlewares\CacheObserverMiddleware::class);    




    $App->all("/admin/useradd/{userid}")
        ->setAction("AdminController@useradd")
        ->where('userid', '[a-z]*');

    $App->all("/admin/useradd/")
        ->setAction("AdminController@useradd");

    $App->all("/admin/userlist/")
        ->setAction("AdminController@userlist");            

    $App->all("/loginuser/")
        ->setAction("AuthController@login");
    
    $App->get(".*/logout.*")
        ->setAction("AuthController@logout");

    $App->all("/apiv2/docs")
        ->setAction("Api\\ApiDocController@renderDocs");

    $App->all("/webhook/receive")
        ->setAction("Webhook@receive");

// Dans index.php

// Routes pour APIv2
$App->get("/apiv2/{resource}")
    ->setAction("Api\\ApiV2Controller@handleRequest")
    ->where('resource', '[a-zA-Z]+');

$App->get("/apiv2/{resource}/{id}")
    ->setAction("Api\\ApiV2Controller@handleRequest")
    ->where('resource', '[a-zA-Z]+')
    ->where('id', '[0-9]+');

$App->post("/apiv2/{resource}")
    ->setAction("Api\\ApiV2Controller@handleRequest")
    ->where('resource', '[a-zA-Z]+');

    $Response   = $App->run();        


} catch (\Throwable $e ){
    echo '<pre>';
    print_r($e);
    echo '</pre>';
}