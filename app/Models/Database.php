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
            // Filtrer les paramètres pour gérer les `IS NULL`, `IS NOT NULL` et les opérateurs logiques
            foreach ($sqlParameters as $key => $parameter) {
                // Gestion des opérateurs logiques OR
                if (is_array($parameter) && isset($parameter[0]) && $parameter[0] === 'OR' && isset($parameter[1]) && is_array($parameter[1])) {
                    // Créer une clause OR avec les sous-conditions
                    $query->where(function($q) use ($parameter) {
                        $orConditions = $parameter[1];
                        foreach ($orConditions as $i => $cond) {
                            if (isset($cond[0], $cond[1], $cond[2])) {
                                $method = $i === 0 ? 'where' : 'orWhere';
                                $q->$method($cond[0], $cond[1], $cond[2]);
                            }
                        }
                    });
                    unset($sqlParameters[$key]);
                }
                // Gestion des formats sous forme de tableaux avec `IS` et `IS NOT`
                elseif (is_array($parameter) && isset($parameter[1]) && isset($parameter[2])) {
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
            if (!empty($sqlParameters)) {
                $query->where($sqlParameters);
            }
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
        
        // Essayer d'abord une mise à jour si l'ID est défini
        if (!empty($sqlParameters[$tableIndex])) {
            $result = @$this->DB::table($table)->where($whereParameters)->update($sqlParameters);
            
            if ($result > 0) {
                // La mise à jour a affecté des lignes, c'est un succès
                $r = $sqlParameters[$tableIndex];
            } else {
                // Aucune ligne n'a été modifiée, vérifions si l'enregistrement existe
                $exists = !empty($whereParameters) && $this->DB::table($table)->where($whereParameters)->exists();
                
                if ($exists) {
                    // L'enregistrement existe mais aucune modification n'était nécessaire
                    $r = $sqlParameters[$tableIndex];
                }
                // Sinon, $r reste false et on passera à l'insertion
            }
        }
        
        // Si l'ID n'est pas défini ou si la mise à jour a échoué (enregistrement inexistant)
        if ($r === false) {
            // Si l'ID est défini mais que l'enregistrement n'existe pas, on peut l'utiliser pour l'insertion
            if (empty($sqlParameters[$tableIndex])) {
                $sqlParameters[$tableIndex] = null;
            }
            
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
