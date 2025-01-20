<?php
// Path: src/app/Classes/Logger.php
namespace Classes;

use Monolog\Processor\ProcessorInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Logs;
use Monolog\LogRecord;
use Monolog\Level;
use Throwable;

/**
 * Class Logger singleton
 *
 * @property self $instance The instance of singleton Logger
 * @property DynamicProcessor $dynamicProcessor Monolog logger to manage errors
 * @property Logs $error The logger instance to manage errors logs
 * @property Logs $app The logger instance to manage app logs
 */
class Logger {
    private static $instance;
    private $dynamicProcessor;
    private $error;
    private $app;
    private $httpError; // Nouvelle propriété pour les logs d'erreurs HTTP
        
    /**
     * Constructor of Logger class
     *
     * @return void
     */
    public function __construct() {
        /* Create error logger instance */
        $this->error = new Logs("error");
        $this->error->pushHandler(new RotatingFileHandler(APPFOLDER."logs/error.log", 3, Level::Critical));
        /* Attach processor to error logger instance */
        $this->dynamicProcessor = new DynamicProcessor();
        $this->error->pushProcessor($this->dynamicProcessor);

        /* Create app logger instance */
        $this->app = new Logs("app");
        $this->app->pushHandler(new RotatingFileHandler(APPFOLDER."logs/app.log", 3, Level::Debug));

        // Initialiser le logger d'erreurs HTTP
        $this->httpError = new Logs("httpError");
        // $this->httpError->pushHandler(new RotatingFileHandler(APPFOLDER."logs/httpError.log", 3, Level::Error));        
        $this->httpError->pushHandler(new StreamHandler(APPFOLDER."logs/httpError.log", Level::Error));
    }
    
    /**
     * Get instance of Logger singleton class
     *
     * @return self
     */
    public static function instance(): self {
        if(!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    /**
     * Add new debug log into logs/app.log
     *
     * @param string $message The debug log message
     * @param array $context (optional) The debug log context
     * @return void
     */
    public static function debug(string $message, array $context = array()): void {
        self::instance()->app->debug($message, $context);
    }
    
    /**
     * Add new critical log into logs/error.log
     *
     * @param string $message The critical log message
     * @param array $context (optional) The critical log context
     * @param Throwable|null $error (optional) The critical log error
     * @return void
     */
    public static function critical(string $message, array $context = array(), Throwable|null $error = null): void {
        /* Update error in dynamic processor */
        if(isset($error)) {
            self::instance()->dynamicProcessor->setError($error);
        }
        self::instance()->error->critical($message, $context);
        self::instance()->dynamicProcessor->resetError();
    }
    
    // Méthode pour ajouter des logs d'erreurs HTTP
    public static function httpError(int $statusCode, string $message, array $context = array()): void {
        $context['statusCode'] = $statusCode; // Ajoute le code d'état HTTP au contexte
        self::instance()->httpError->error($message, $context);
    }

}

/**
 * Class DynamicProcessor 
 *
 * This class is used to add error information to a log record.
 * It's designed to be used as a processor in the Monolog library.
 * 
 * @property Throwable $error The current error to be logged
 */
class DynamicProcessor implements ProcessorInterface {
    private $error;
        
    /**
     * Set the new error to be logged.
     *
     * @param Throwable $error The instance exception
     * @return void
     */
    public function setError(Throwable $error): void {
        $this->error = $error;
    }

    /**
     * Remove the current log error.
     *
     * @return void
     */
    public function resetError(): void {
        $this->error = null;
    }

    /**
     * This method is invoked for each log record. If an error has been set,
     * it adds the error information to the 'extra' field of the log record.
     *
     * @param LogRecord $record
     * @return LogRecord
     */
    public function __invoke(LogRecord $record): LogRecord {
        if($this->error) {
            $record["extra"]["error"] = array(
                "Message" => $this->error->getMessage(),
                "Line" => $this->error->getLine(),
                "File" => $this->error->getFile(),
                "Code" => $this->error->getCode(),
                "Trace" => $this->error->getTrace()
            );
        }

        return $record;
    }
}