<?php

namespace ree_jp\mysql_logger\sql;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use poggit\libasynql\SqlThread;

class BulkRepo extends Repository
{
    private string $query = "";

    public function addBlockLog(BlockPlaceEvent|BlockBreakEvent $ev, string $action): void
    {
        $p = $ev->getPlayer();
        $pos = $ev->getBlock()->getPosition();
        $time = date(self::DATE_FORMAT);
        $string = "( $action, {$p->getXuid()}, {$pos->getFloorX()}, {$pos->getFloorY()}, {$pos->getFloorZ()}, {$pos->getWorld()->getFolderName()}," .
            "{$ev->getItem()->getName()}, {$ev->getBlock()->getName()}, $this->serverId, $time)";

        if (!empty($this->query)) {
            $this->query .= ", ";
        }
        $this->query .= $string;
    }

    public function enQueue(): void
    {
        if (empty($this->query)) {
            return;
        }
        $this->sendSql();
    }

    public function sendSql(): void
    {
        if (empty($this->query)) {
            return;
        }
        $this->db->executeImplRaw(["INSERT INTO BLOCK_LOG VALUES " . $this->query . ";"], [[]], [SqlThread::MODE_INSERT],
            function (): void {

            },
            null);
        $this->query = "";
    }
}