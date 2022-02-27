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
        $log["action"] = $action;
        $log["xuid"] = $ev->getPlayer()->getXuid();

        $pos = $ev->getBlock()->getPosition();
        $log["x"] = $pos->getFloorX();
        $log["y"] = $pos->getFloorY();
        $log["z"] = $pos->getFloorZ();
        $log["world"] = $pos->getWorld()->getFolderName();

        $log["item"] = json_encode($ev->getItem());
        $log["block"] = json_encode($ev->getBlock());
        $this->repo->addBlockLog($log);
    }
}