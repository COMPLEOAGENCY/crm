<?php
namespace Controllers;

use Framework\Controller;
use Framework\RedisConnection;

class AdminRedis extends Controller {
    public function info($params = []) {
        try {
            // Get Redis connection instance
            $redisConnection = RedisConnection::instance();
            $redis = $redisConnection->getRedis();

            if (!$redis) {
                throw new \Exception('Unable to connect to Redis.');
            }

            // Get Redis info for different sections
            $serverInfo = $redis->info('server');
            $memoryInfo = $redis->info('memory');
            $statsInfo = $redis->info('stats');
            $clientsInfo = $redis->info('clients');
            $persistenceInfo = $redis->info('persistence');

            // Calculate memory usage percentage
            $usedMemory = $memoryInfo['used_memory'] ?? 0;
            $totalMemory = $memoryInfo['total_system_memory'] ?? 1;
            $memoryUsagePercentage = ($usedMemory / $totalMemory) * 100;

            // Important KPIs with descriptions
            $kpis = [
                [
                    'key' => 'Redis Version',
                    'value' => $serverInfo['redis_version'] ?? 'N/A',
                    'description' => 'Version du serveur Redis.'
                ],
                [
                    'key' => 'Uptime',
                    'value' => $serverInfo['uptime_in_days'] ?? 'N/A',
                    'description' => 'Temps de fonctionnement en jours.'
                ],
                [
                    'key' => 'Clients Connectés',
                    'value' => $clientsInfo['connected_clients'] ?? 'N/A',
                    'description' => 'Nombre de clients connectés.'
                ],
                [
                    'key' => 'Utilisation Mémoire',
                    'value' => number_format($memoryUsagePercentage, 2) . '%',
                    'description' => 'Pourcentage de mémoire utilisée.'
                ],
                [
                    'key' => 'Commandes Traitées',
                    'value' => $statsInfo['total_commands_processed'] ?? 'N/A',
                    'description' => 'Nombre total de commandes traitées.'
                ],
                [
                    'key' => 'Hits Cache',
                    'value' => $statsInfo['keyspace_hits'] ?? 'N/A',
                    'description' => 'Nombre de succès dans le cache.'
                ],
                [
                    'key' => 'Miss Cache',
                    'value' => $statsInfo['keyspace_misses'] ?? 'N/A',
                    'description' => 'Nombre d\'échecs dans le cache.'
                ],
                [
                    'key' => 'Mémoire Utilisée',
                    'value' => $memoryInfo['used_memory_human'] ?? 'N/A',
                    'description' => 'Mémoire totale utilisée.'
                ]
            ];

            $params['kpis'] = $kpis;
            return $this->view('admin.redis.info', $params);
        } catch (\Exception $e) {
            return $this->view('admin.redis.info', ['error' => $e->getMessage()]);
        }
    }

    public function explore($params = []) {
        try {
            $redisConnection = RedisConnection::instance();
            $redis = $redisConnection->getRedis();

            if (!$redis) {
                throw new \Exception('Unable to connect to Redis.');
            }

            // Get search filter from params or use default
            $filter = $params['filter'] ?? '*';
            
            // Get all keys matching the pattern
            $keys = $redis->keys($filter);
            $keysData = [];

            foreach ($keys as $key) {
                $type = $redis->type($key);
                $ttl = $redis->ttl($key);
                
                $keysData[] = [
                    'key' => $key,
                    'type' => $this->getRedisTypeName($type),
                    'ttl' => $ttl,
                    'size' => $this->getKeySize($redis, $key, $type)
                ];
            }

            $params['keys'] = $keysData;
            $params['filter'] = $filter;
            return $this->view('admin.redis.explore', $params);
        } catch (\Exception $e) {
            return $this->view('admin.redis.explore', ['error' => $e->getMessage()]);
        }
    }

    private function getRedisTypeName($type) {
        $types = [
            \Redis::REDIS_NOT_FOUND => 'none',
            \Redis::REDIS_STRING => 'string',
            \Redis::REDIS_SET => 'set',
            \Redis::REDIS_LIST => 'list',
            \Redis::REDIS_ZSET => 'zset',
            \Redis::REDIS_HASH => 'hash',
            \Redis::REDIS_STREAM => 'stream'
        ];
        
        return $types[$type] ?? 'unknown';
    }

    private function getKeySize($redis, $key, $type) {
        switch ($type) {
            case \Redis::REDIS_STRING:
                return strlen($redis->get($key));
            case \Redis::REDIS_LIST:
                return $redis->lLen($key);
            case \Redis::REDIS_SET:
                return $redis->sCard($key);
            case \Redis::REDIS_ZSET:
                return $redis->zCard($key);
            case \Redis::REDIS_HASH:
                return $redis->hLen($key);
            default:
                return 0;
        }
    }

    public function deleteKey($params = []) {
        try {
            if (!isset($params['key'])) {
                throw new \Exception('Key is required');
            }

            $redis = RedisConnection::instance()->getRedis();
            $redis->del($params['key']);

            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function deleteKeys($params = []) {
        try {
            if (!isset($params['keys']) || !is_array($params['keys'])) {
                throw new \Exception('Keys array is required');
            }

            $redis = RedisConnection::instance()->getRedis();
            foreach ($params['keys'] as $key) {
                $redis->del($key);
            }

            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getValue($params = []) {
        try {
            if (!isset($params['key'])) {
                throw new \Exception('Key is required');
            }

            $redis = RedisConnection::instance()->getRedis();
            $key = $params['key'];
            $type = $redis->type($key);
            $value = $this->getKeyValue($redis, $key, $type);

            return json_encode(['success' => true, 'value' => $value]);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    private function getKeyValue($redis, $key, $type) {
        switch ($type) {
            case \Redis::REDIS_STRING:
                return $redis->get($key);
            case \Redis::REDIS_LIST:
                return $redis->lRange($key, 0, -1);
            case \Redis::REDIS_SET:
                return $redis->sMembers($key);
            case \Redis::REDIS_ZSET:
                return $redis->zRange($key, 0, -1, true);
            case \Redis::REDIS_HASH:
                return $redis->hGetAll($key);
            default:
                return null;
        }
    }
}
