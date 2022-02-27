<?php

namespace ree_jp\mysql_logger;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

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
        $this->csvPath = $owner->getDataFolder() . "temp.csv";
        $this->serverId = $config->get("server-id");
        $this->db = libasynql::create($owner, $config->get("database"), [
            "mysql" => "mysql.sql",
        ]);
        $this->db->executeInsert("mysql_logger.init.block_log");
        $this->task = $owner->getScheduler()->scheduleRepeatingTask(new ClosureTask(
            function (): void {
                $this->enQueue();
            }
        ), 20 * $this->config->get("save-interval"));
    }

    public function addBlockLog(array $log): void
    {
        $log["server_id"] = $this->serverId;
        $log["time"] = date(self::DATE_FORMAT);
        $this->logs[] = $log;
    }

    public function enQueue(): void
    {
        if (empty($this->logs)) {
            return;
        }

        $csv = fopen($this->csvPath, "w");
        foreach ($this->logs as $log) {
            fputcsv($csv, $log, ";");
        }
        fclose($csv);
        $this->sendSql();
    }

    public function sendSql(): void
    {
        if (file_exists($this->csvPath)) {
            $this->db->executeGeneric("mysql_logger.send", ["filePath" => $this->csvPath],
                function (): void {
                    unlink($this->csvPath);
                }
            );
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