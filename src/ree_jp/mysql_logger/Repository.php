<?php

namespace ree_jp\mysql_logger;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlThread;

class Repository
{
    const DATE_FORMAT = "Y-m-d H:i:s";

    private DataConnector $db;
    private string $csvPath;
    private string $serverId;
    private TaskHandler $task;

    /**
     * @var array[]
     */
    private array $logs = [];

    public function __construct(PluginBase $owner, private Config $config)
    {
        $this->csvPath = $owner->getDataFolder();
        $this->serverId = $config->get("server-id");
        mkdir($this->csvPath . "processing/");
        $this->db = libasynql::create($owner, $config->get("database"), [
            "mysql" => "mysql.sql",
        ]);
        $this->db->executeInsert("mysql_logger.init.block_log");
        $this->task = $owner->getScheduler()->scheduleRepeatingTask(new ClosureTask(
            function (): void {
                $this->enQueue();
            }
        ), 20 * $this->config->get("save-interval"));
        $this->sendSql();
    }

    public function addBlockLog(array $log): void
    {
        $log[10] = $this->serverId;
        $log[11] = date(self::DATE_FORMAT);
        $this->logs[] = $log;
    }

    public function enQueue(): void
    {
        if (empty($this->logs)) {
            return;
        }

        $csv = fopen($this->csvPath . date(self::DATE_FORMAT), "w");
        foreach ($this->logs as $log) {
            fputcsv($csv, $log, ";");
        }
        fclose($csv);
        $this->logs = [];
        $this->sendSql();
    }

    public function sendSql(): void
    {
        foreach (glob($this->csvPath . "*.csv") as $filePath) {
            $afterPath = $this->csvPath . "processing/" . basename($filePath);
            rename($filePath, $afterPath);
            $this->db->executeImplRaw(["LOAD DATA LOCAL INFILE '$afterPath' INTO TABLE BLOCK_LOG FIELDS TERMINATED BY ';';"], [], [SqlThread::MODE_INSERT],
                function () use ($afterPath): void {
                    unlink($afterPath);
                }, null);
        }
    }

    public function close(): void
    {
        $this->task->cancel();
        $this->enQueue();
        $this->db->waitAll();
        $this->db->close();
    }
}