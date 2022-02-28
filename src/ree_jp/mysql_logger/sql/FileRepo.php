<?php

namespace ree_jp\mysql_logger\sql;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use poggit\libasynql\SqlThread;

class FileRepo extends Repository
{
    private string $path;
    /**
     * @var array[]
     */
    private array $logs = [];

    public function __construct(PluginBase $owner, Config $config)
    {
        $this->path = $owner->getDataFolder();
        if (!file_exists($this->path . "processing/")) {
            mkdir($this->path . "processing/");
        }
        parent::__construct($owner, $config);
        $this->db->executeInsert("mysql_logger.init.clear");
    }

    public function addBlockLog(BlockBreakEvent|BlockPlaceEvent $ev, string $action): void
    {
        $log[] = $action;
        $log[] = $ev->getPlayer()->getXuid();

        $pos = $ev->getBlock()->getPosition();
        $log[] = $pos->getFloorX();
        $log[] = $pos->getFloorY();
        $log[] = $pos->getFloorZ();
        $log[] = $pos->getWorld()->getFolderName();

        $log[] = $ev->getItem()->getName();
        $log[] = $ev->getBlock()->getName();
        $log[] = $this->serverId;
        $log[] = date(self::DATE_FORMAT);
        $this->logs[] = $log;
    }

    public function enQueue(): void
    {
        if (empty($this->logs)) {
            return;
        }

        $csv = fopen($this->path . time() . ".csv", "w");
        foreach ($this->logs as $log) {
            fputcsv($csv, $log, ";");
        }
        fclose($csv);
        $this->logs = [];
        $this->sendSql();
    }

    public function sendSql(): void
    {
        foreach (glob($this->path . "*.csv") as $filePath) {
            $afterPath = $this->path . "processing/" . basename($filePath);
            rename($filePath, $afterPath);
            $this->db->executeImplRaw(["LOAD DATA LOCAL INFILE '$afterPath' INTO TABLE BLOCK_LOG FIELDS TERMINATED BY ';';"],
                [[]], [SqlThread::MODE_GENERIC],
                function () use ($afterPath): void {
                    unlink($afterPath);
                },
                null);
        }
    }
}