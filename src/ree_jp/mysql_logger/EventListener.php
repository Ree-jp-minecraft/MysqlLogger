<?php

namespace ree_jp\mysql_logger;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;

class EventListener implements Listener
{
    public function __construct(private Repository $repo)
    {
    }

    /**
     * @priority MONITOR
     */
    public function onBreak(BlockBreakEvent $ev): void
    {
        if ($ev->isCancelled()) return;
        $this->addBlockLog($ev, "break");
    }

    public function onPlace(BlockPlaceEvent $ev): void
    {
        if ($ev->isCancelled()) return;
        $this->addBlockLog($ev, "place");
    }

    private function addBlockLog(BlockBreakEvent|BlockPlaceEvent $ev, string $action): void
    {
        $log[2] = $action;
        $log[3] = $ev->getPlayer()->getXuid();

        $pos = $ev->getBlock()->getPosition();
        $log[4] = $pos->getFloorX();
        $log[5] = $pos->getFloorY();
        $log[6] = $pos->getFloorZ();
        $log[7] = $pos->getWorld()->getFolderName();

        $log[8] = json_encode($ev->getItem());
        $log[9] = json_encode($ev->getBlock());
        $this->repo->addBlockLog($log);
    }
}