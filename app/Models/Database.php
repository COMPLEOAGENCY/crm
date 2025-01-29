<?php
// Path: src/app/Models/Database.php
namespace Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;
use Classes\Logger;
use Framework\DebugBar;
// use DebugBar\DataCollector\DataCollectorInterface; // Import the missing class
/**
 * Classe Database gérant les interactions avec la base de données.
 * Utilise le pattern Singleton pour garantir une unique instance de cette classe.
 *
 * @property Database $instance L'instance singleton de la classe Database.
 * @property Capsule $DB L'instance de Illuminate\Database\Capsule\Manager.
 */
class Database
{
    /**
     * Instance unique de la classe Database.
     *
     * @var Database|null
     */    
    private static $instance;
    /**
     * Instance du gestionnaire de base de données Capsule.
     *
     * @var Capsule
     */    
    public $DB;
    /**
     * Constructeur privé pour la mise en place de la base de données.
     * Initialise la connexion et configure les écouteurs d'événements.
     */
    public function __construct()
    {
        $this->initializeDatabase();
        $this->configureLogging();
        $this->DB->setAsGlobal();
        $this->DB->bootEloquent();
    }

    private function initializeDatabase()
    {
        $this->DB = new Capsule();
        $this->DB->addConnection([
            "driver" => $_ENV["DB_DRIVER"],
            "host" => $_ENV["DB_HOST"],
            "port" => $_ENV["DB_PORT"],
            "database" => $_ENV["DB_NAME"],
            "username" => $_ENV["DB_USER"],
            "password" => $_ENV["DB_PASSWORD"],
            "charset" => $_ENV["DB_CHARSET"],
            "collation" => $_ENV["DB_COLLATION"],
            'options' => [\PDO::ATTR_PERSISTENT => false], // Désactivation des connexions persistantes
        ]);        
        
    }


            private function configureLogging()
            {
                if (isset($_ENV["LOG_SQL"]) || isset($_ENV["LOG_SLOW_SQL_DURATION"])) {
                    if ($_ENV["LOG_SQL"] == 1 || $_ENV["LOG_SLOW_SQL_DURATION"]>0) {
                        $dispatcher = new Dispatcher();
                        $dispatcher->listen(QueryExecuted::class, function ($query) {
                            $logSql = isset($_ENV["LOG_SQL"]) && $_ENV["LOG_SQL"] == 1;
                            $logSlowSql = isset($_ENV["LOG_SLOW_SQL_DURATION"]) && $query->time > $_ENV["LOG_SLOW_SQL_DURATION"];                            
                            if ($logSql || $logSlowSql) {
                                Logger::debug("SQL", [
                                    "query" => $this->formatQuery($query->sql, $query->bindings),
                                    "duration" => $query->time
                                ]);
                            }
                        });
                        $this->DB->setEventDispatcher($dispatcher);
                        $this->DB->getConnection()->enableQueryLog();
                    }
                }
                if (DebugBar::isSet()) {
                    $debugbar = DebugBar::instance()->getDebugBar();
                    $debugbar->addCollector(new \DebugBar\DataCollector\PDO\PDOCollector($this->DB->getConnection()->getPdo()));
                }                
            }


    private function formatQuery($sql, $bindings)
    {
        return preg_replace_callback('/\?/', function ($match) use (&$bindings) {
            $value = array_shift($bindings);
            return is_numeric($value) ? $value : "'" . addslashes($value) . "'";
        }, $sql);
    }

    /**
     * Get instance of Database singleton class
     *
     * @return self
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construit une requête SQL basée sur les paramètres donnés.
     *
     * @param string $table
     * @param int|null $limit
     * @param array|null $sqlParameters
     * @param array|null $jsonParameters
     * @param string|null $groupBy
     * @param string|null $orderBy
     * @param string $direction
     * @return \Illuminate\Database\Query\Builder
     */
    public function buildQuery(
        string $table,
        $limit = null,
        array $sqlParameters = null,
        array $jsonParameters = null,
        $groupBy = null,
        $orderBy = null,
        $direction = 'asc',
        $raw = null
    ): \Illuminate\Database\Query\Builder {
        $query = $this->DB::table($table);

        if (!empty($sqlParameters)) {
            // Filtrer les paramètres pour gérer les `IS NULL` et `IS NOT NULL`
            foreach ($sqlParameters as $key => $parameter) {
                // Gestion des formats sous forme de tableaux avec `IS` et `IS NOT`
                if (is_array($parameter) && isset($parameter[1]) && isset($parameter[2])) {
                    if (strtoupper($parameter[1]) === 'IS' && $parameter[2] === null) {
                        $query->whereNull($parameter[0]);
                        unset($sqlParameters[$key]);
                    } elseif (strtoupper($parameter[1]) === 'IS NOT' && $parameter[2] === null) {
                        $query->whereNotNull($parameter[0]);
                        unset($sqlParameters[$key]);
                    }
                }
                // Gestion des formats `['key' => 'IS NULL']` et `['key' => 'IS NOT NULL']`
                elseif (is_string($parameter) && strtoupper($parameter) === 'IS NULL') {
                    $query->whereNull($key);
                    unset($sqlParameters[$key]);
                } elseif (is_string($parameter) && strtoupper($parameter) === 'IS NOT NULL') {
                    $query->whereNotNull($key);
                    unset($sqlParameters[$key]);
                }
            }

            // Appliquer les autres conditions SQL restantes
            $query->where($sqlParameters);
        }

        if (!empty($jsonParameters)) {
            foreach ($jsonParameters as $jsonName => $jsonArray) {
                $query->whereIn($jsonName, $jsonArray);
            }
        }

        if (!empty($raw)) {
            foreach ($raw as $rawsql) {
                $query->addSelect($this->DB::raw($rawsql));
            }
        }

        if (!empty($groupBy)) {
            $query->groupBy($groupBy);
        }

        if (!empty($orderBy)) {
            $query->orderBy($orderBy, $direction);
        }

        return $limit ? $query->limit($limit) : $query;
    }

    /**
     * Fournit un itérateur pour parcourir les résultats d'une requête.
     *
     * @param string $table Nom de la table.
     * @param int|null $limit Limite du nombre de résultats.
     * @param array|null $sqlParameters Paramètres SQL pour la clause WHERE.
     * @param array|null $jsonParameters Paramètres JSON pour la clause WHERE IN.
     *
     * @return \Generator Itérateur sur les enregistrements de la base de données.
     */    
    public function fetchIterator(
        string $table,
        $limit = null,
        array $sqlParameters = null,
        array $jsonParameters = null,
        $groupBy = null,
        $orderBy = null,
        $direction = 'asc',
        $raw = null
    ): \Generator {
        foreach ($this->buildQuery($table, $limit, $sqlParameters, $jsonParameters, $groupBy, $orderBy, $direction,$raw)->cursor() as $record) {
            yield $record;
        }
    }


    /**
     * Récupère les enregistrements d'une table sous forme de tableau.
     *
     * @param string $table
     * @param int|null $limit
     * @param array|null $sqlParameters
     * @param array|null $jsonParameters
     * @param string|null $groupBy
     * @param string|null $orderBy
     * @param string $direction
     * @return array
     */
    public function fetch(
        string $table,
        $limit = null,
        array $sqlParameters = null,
        array $jsonParameters = null,
        $groupBy = null,
        $orderBy = null,
        $direction = 'asc',
        $raw = null
    ): array {
        $results = [];
        foreach ($this->fetchIterator($table, $limit, $sqlParameters, $jsonParameters, $groupBy, $orderBy, $direction,$raw) as $record) {
            $results[] = $record;
        }
        return $results;
    }

    /**
     * Met à jour ou insère un enregistrement dans une table.
     *
     * @param string $table Nom de la table.
     * @param string $tableIndex Clé primaire ou unique de la table.
     * @param array $whereParameters Paramètres pour la clause WHERE.
     * @param array $sqlParameters Paramètres SQL pour l'insertion ou la mise à jour.
     *
     * @return mixed Renvoie false en cas d'échec, sinon renvoie l'identifiant de l'enregistrement.
     */    
    public function updateOrInsert(string $table, string $tableIndex, array $whereParameters, array $sqlParameters)
    {
        $r = false;
    
        // Essayer de mettre à jour l'enregistrement existant
        if (!empty($sqlParameters[$tableIndex])) {
            $result = @$this->DB::table($table)->where($whereParameters)->update($sqlParameters);
            if ($result > 0) {
                $r = $sqlParameters[$tableIndex];
            }
        }
    
        // Si la mise à jour échoue ou si le paramètre de l'index de la table est vide, essayer d'insérer un nouvel enregistrement
        if (empty($sqlParameters[$tableIndex]) || $r === false) {
            $sqlParameters[$tableIndex] = null;
            $result = @$this->DB::table($table)->insertGetId($sqlParameters, $tableIndex);
            if ($result > 0) {
                $r = $result;
            }
        }
    
        return $r;
    }
    
    /**
     * Supprime des enregistrements d'une table selon les critères donnés.
     *
     * @param string $table Nom de la table.
     * @param array $sqlParameters Paramètres pour la clause WHERE de la suppression.
     *
     * @return int Nombre d'enregistrements affectés par la suppression.
     */
    public function delete(string $table, array $sqlParameters)
    {
        return $this->DB::table($table)->where($sqlParameters)->delete();
    }
}
