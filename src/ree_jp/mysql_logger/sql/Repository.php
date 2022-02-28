<?php

namespace ree_jp\mysql_logger\sql;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

abstract class Repository
{
    const DATE_FORMAT = "Y-m-d H:i:s";

    protected DataConnector $db;
    protected string $serverId;
    private TaskHandler $task;

    public function __construct(PluginBase $owner, private Config $config)
    {
        $this->serverId = $config->get("server-id");
        $this->db = libasynql::create($owner, $config->get("database"), [
            "mysql" => "mysql.sql",
        ]);
        $this->db->executeInsert("mysql_logger.init.block_log");
        $this->db->executeInsert("mysql_logger.init.clear");
        $this->task = $owner->getScheduler()->scheduleRepeatingTask(new ClosureTask(
            function (): void {
                $this->enQueue();
            }
        ), 20 * $this->config->get("save-interval"));
        $this->sendSql();
    }

    abstract public function addBlockLog(BlockBreakEvent|BlockPlaceEvent $ev, string $action): void;

    abstract public function enQueue(): void;

    abstract public function sendSql(): void;

    public function close(): void
    {
        $this->task->cancel();
        $this->enQueue();
        $this->db->waitAll();
        $this->db->close();
    }
}