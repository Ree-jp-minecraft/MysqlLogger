<?php

namespace ree_jp\mysql_logger;

use Closure;
use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;
use ree_jp\mysql_logger\sql\BulkRepo;
use ree_jp\mysql_logger\sql\FileRepo;
use ree_jp\mysql_logger\sql\Repository;

class MysqlLoggerPlugin extends PluginBase
{
    private static Repository $repo;

    public function onEnable(): void
    {
        self::$repo = match ($this->getConfig()->get("type")) {
            "bulk" => new BulkRepo($this, $this->getConfig()),
            "file" => new FileRepo($this, $this->getConfig())
        };
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(self::$repo), $this);
    }

    static function getLog(Position $pos, Closure $func, ?Closure $failure): void
    {
        self::$repo->getLog($pos, function (array $rows) use ($func): void {
            $logs = [];
            foreach ($rows as $row) {
                $logs[] = new BlockLog($row["action"], strval($row["xuid"]), $row["x"], $row["y"], $row["z"], $row["world"],
                    $row["item"], $row["block"], $row["server_id"], $row["time"]);
            }
            $func($logs);
        }, $failure);
    }

    public function onDisable(): void
    {
        self::$repo->close();
    }
}
