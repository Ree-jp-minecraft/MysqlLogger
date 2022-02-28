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
        $log[] = $action;
        $log[] = $ev->getPlayer()->getXuid();

        $pos = $ev->getBlock()->getPosition();
        $log[] = $pos->getFloorX();
        $log[] = $pos->getFloorY();
        $log[] = $pos->getFloorZ();
        $log[] = $pos->getWorld()->getFolderName();

        $log[] = $ev->getItem()->getName();
        $log[] = $ev->getBlock()->getName();
        $this->repo->addBlockLog($log);
    }
}