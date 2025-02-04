<?php
namespace Controllers;

use Framework\Controller;
use Framework\RedisConnection;

class AdminRedis extends Controller {
    public function info() {
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

            return $this->view('admin/redis/info', ['kpis' => $kpis]);
        } catch (\Exception $e) {
            return $this->view('admin/redis/info', ['error' => $e->getMessage()]);
        }
    }

    public function explore() {
        try {
            $redisConnection = RedisConnection::instance();
            $redis = $redisConnection->getRedis();

            if (!$redis) {
                throw new \Exception('Unable to connect to Redis.');
            }

            // Get search filter
            $filter = $this->request->get('filter', '*');
            
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

            return $this->view('admin/redis/explore', [
                'keys' => $keysData,
                'filter' => $filter
            ]);
        } catch (\Exception $e) {
            return $this->view('admin/redis/explore', ['error' => $e->getMessage()]);
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

    public function deleteKey() {
        try {
            if (!$this->request->isPost()) {
                throw new \Exception('Method not allowed');
            }

            $key = $this->request->post('key');
            if (!$key) {
                throw new \Exception('Key is required');
            }

            $redis = RedisConnection::instance()->getRedis();
            $redis->del($key);

            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}
