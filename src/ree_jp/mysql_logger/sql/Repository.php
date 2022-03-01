<?php

namespace ree_jp\mysql_logger\sql;

use Closure;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use pocketmine\world\Position;
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
        $this->db->waitAll();
        $this->db->executeGeneric("mysql_logger.init.delete", ["server_id" => $this->serverId,
            "time" => date(self::DATE_FORMAT, strtotime($this->config->get("delete-day") . "ago"))]);
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

    public function getLog(Position $pos, Closure $func, ?Closure $failure): void
    {
        $this->db->executeSelect("mysql_logger.get", ["x" => $pos->getFloorX(), "y" => $pos->getFloorY(), "z" => $pos->getFloorZ(),
            "world" => $pos->getWorld()->getFolderName(), "server_id" => $this->serverId], $func, $failure);
    }

    public function close(): void
    {
        $this->task->cancel();
        $this->enQueue();
        $this->db->waitAll();
        $this->db->close();
    }
}