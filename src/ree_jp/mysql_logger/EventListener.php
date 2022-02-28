<?php

namespace ree_jp\mysql_logger;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use ree_jp\mysql_logger\sql\Repository;

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
        $this->repo->addBlockLog($ev, "break");
    }

    /**
     * @priority MONITOR
     */
    public function onPlace(BlockPlaceEvent $ev): void
    {
        if ($ev->isCancelled()) return;
        $this->repo->addBlockLog($ev, "place");
    }
}
