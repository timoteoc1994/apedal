<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ShowAllRedisData extends Command
{
    protected $signature = 'redis:show-all';
    protected $description = 'Mostrar todos los datos almacenados en Redis';

    public function handle()
    {
        $this->info("=== TODOS LOS DATOS EN REDIS ===\n");

        // Obtener TODAS las claves
        $allKeys = Redis::keys('*');

        if (empty($allKeys)) {
            $this->warn("No hay datos en Redis");
            return;
        }

        $this->info("Total de claves encontradas: " . count($allKeys));
        $this->info("--------------------------------\n");

        // Agrupar por tipo
        $recyclerKeys = [];
        $otherKeys = [];

        foreach ($allKeys as $key) {
            if (strpos($key, 'recycler:') === 0) {
                $recyclerKeys[] = $key;
            } else {
                $otherKeys[] = $key;
            }
        }

        // Mostrar claves de recicladores
        if (!empty($recyclerKeys)) {
            $this->info("📍 DATOS DE RECICLADORES:");
            $this->info("------------------------");

            foreach ($recyclerKeys as $key) {
                $this->showKeyData($key);
            }
        }

        // Mostrar otras claves
        if (!empty($otherKeys)) {
            $this->info("\n📦 OTROS DATOS:");
            $this->info("---------------");

            foreach ($otherKeys as $key) {
                $this->showKeyData($key);
            }
        }
    }

    private function showKeyData($key)
    {
        $type = Redis::type($key);

        $this->info("\n🔑 Clave: {$key}");
        $this->info("   Tipo: {$type}");

        switch ($type) {
            case 'string':
                $value = Redis::get($key);
                $ttl = Redis::ttl($key);

                // Intentar decodificar JSON
                $jsonData = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->info("   Valor (JSON):");
                    $this->line(json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                } else {
                    $this->info("   Valor: {$value}");
                }

                if ($ttl > 0) {
                    $this->info("   TTL: {$ttl} segundos");
                } elseif ($ttl == -1) {
                    $this->info("   TTL: Sin expiración");
                }
                break;

            case 'hash':
                $values = Redis::hgetall($key);
                $this->info("   Valores:");
                foreach ($values as $field => $value) {
                    $this->line("     {$field}: {$value}");
                }
                break;

            case 'list':
                $values = Redis::lrange($key, 0, -1);
                $this->info("   Valores (" . count($values) . " elementos):");
                foreach ($values as $index => $value) {
                    $this->line("     [{$index}]: {$value}");
                }
                break;

            case 'set':
                $values = Redis::smembers($key);
                $this->info("   Miembros (" . count($values) . "):");
                foreach ($values as $value) {
                    $this->line("     - {$value}");
                }
                break;

            case 'zset':
                $values = Redis::zrange($key, 0, -1, ['WITHSCORES' => true]);
                $this->info("   Miembros ordenados:");
                foreach ($values as $member => $score) {
                    $this->line("     {$member}: {$score}");
                }
                break;
        }

        $this->line("   --------------------------------");
    }
}
