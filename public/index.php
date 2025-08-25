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

require __DIR__ . '/../config/settings.php';
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

    // Campagnes clients (MVC)
    $App->all("/admin/clientcampaign/list")
        ->setAction("AdminController@clientcampaignList");
    $App->all("/admin/clientcampaign/list/")
        ->setAction("AdminController@clientcampaignList");

    // Route de test pour le BalanceService
    $App->get("/admin/test/balance/{userid}")
        ->setAction("TestController@testBalance")
        ->where('userid', '[0-9]+');

    // Vérifications (MVC)
    $App->all("/admin/verification/list")
        ->setAction("AdminController@verificationList");

    // Détail d'une vérification (par ID)
    $App->all("/admin/verification/{id}")
        ->setAction("AdminController@verificationAdd")
        ->where('id', '[0-9]+');

    $App->all("/admin/verification/")
    ->setAction("AdminController@verificationAdd");

    $App->all("/loginuser/")
        ->setAction("AuthController@login");
    
    $App->get(".*/logout.*")
        ->setAction("AuthController@logout");

    $App->all("/apiv2/docs")
        ->setAction("Api\\ApiDocController@renderDocs");

    $App->all("/webhook/receive")
        ->setAction("Webhook@receive");

    // Routes pour l'intégration du chat avec N8N
    $App->post("/api/v2/chat/message")
        ->setAction("Api\\AIChatApiController@storeUserMessage");
        
    $App->get("/api/v2/chat/analyze")
        ->setAction("Api\\AIChatApiController@getMessagesToAnalyze");
        
    $App->post("/api/v2/chat/response")
        ->setAction("Api\\AIChatApiController@storeAIResponse");

    // Route template vide
    $App->get("/admin/blanck")
        ->setAction("AdminController@blanck");

    // Routes Redis Admin
    $App->all("/admin/redis/info")
        ->setAction("AdminRedis@info");

    $App->all("/admin/redis/explore")
        ->setAction("AdminRedis@explore");

    $App->all("/admin/redis/delete-key")
        ->setAction("AdminRedis@deleteKey");

    $App->all("/admin/redis/delete-keys")
        ->setAction("AdminRedis@deleteKeys");

    $App->all("/admin/redis/get-value")
        ->setAction("AdminRedis@getValue");

    // Route Chat AI
    $App->get("/admin/ai/chat")
        ->setAction("Ai\\AiChatController@index");

// Dans index.php

// Routes pour APIv2
$App->get("/apiv2/{resource}[/]?")
    ->setAction("Api\\ApiV2Controller@handleRequest")
    ->where('resource', '[a-zA-Z]+');

$App->get("/apiv2/{resource}/{id}[/]?")
    ->setAction("Api\\ApiV2Controller@handleRequest")
    ->where('resource', '[a-zA-Z]+')
    ->where('id', '[0-9]+');

$App->post("/apiv2/{resource}[/]?")
    ->setAction("Api\\ApiV2Controller@handleRequest")
    ->where('resource', '[a-zA-Z]+');

$App->post("/apiv2/{resource}/{id}[/]?")
    ->setAction("Api\\ApiV2Controller@handleRequest")
    ->where('resource', '[a-zA-Z]+')
    ->where('id', '[0-9]+');

// Routes pour l'API de Chat
$App->get("/api/chat/conversations")
    ->setAction("Api\\ChatApiController@getConversations");

$App->get("/api/chat/conversations/{id}")
    ->setAction("Api\\ChatApiController@getConversation")
    ->where('id', '[a-zA-Z0-9-]+');

$App->post("/api/chat/conversations")
    ->setAction("Api\\ChatApiController@createConversation");

$App->post("/api/chat/conversations/{id}/messages")
    ->setAction("Api\\ChatApiController@sendMessage")
    ->where('id', '[a-zA-Z0-9-]+');

$App->get("/api/chat/conversations/{id}/messages")
    ->setAction("Api\\ChatApiController@getMessages")
    ->where('id', '[a-zA-Z0-9-]+');

$App->put("/api/chat/messages/{id}/read")
    ->setAction("Api\\ChatApiController@markMessageAsRead")
    ->where('id', '[0-9]+');

$App->post("/api/chat/conversations/{id}/participants")
    ->setAction("Api\\ChatApiController@addParticipant")
    ->where('id', '[a-zA-Z0-9-]+');

$App->delete("/api/chat/conversations/{id}/participants/{participantId}")
    ->setAction("Api\\ChatApiController@removeParticipant")
    ->where('id', '[a-zA-Z0-9-]+')
    ->where('participantId', '[0-9]+');

// Routes pour les webhooks du chat (à implémenter plus tard)
$App->post("/webhook/chat")
    ->setAction("Webhook@receive");

// Route Validation Leads (API v2)
$App->all("/api/v2/validation/run")
    ->setAction("Api\\LeadValidationController@run");

// Chargement des leads pending (API v2)
$App->all("/api/v2/validation/pending")
    ->setAction("Api\\LeadValidationController@loadPendingLeads");

$Response   = $App->run();        


} catch (\Throwable $e ){
    echo '<pre>';
    print_r($e);
    echo '</pre>';
}