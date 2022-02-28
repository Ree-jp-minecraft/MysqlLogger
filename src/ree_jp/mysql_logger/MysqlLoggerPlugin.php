<?php

namespace ree_jp\mysql_logger;

use pocketmine\plugin\PluginBase;
use ree_jp\mysql_logger\sql\BulkRepo;
use ree_jp\mysql_logger\sql\FileRepo;

class MysqlLoggerPlugin extends PluginBase
{

    public function onEnable(): void
    {
        $repo = match ($this->getConfig()->get("type")) {
            "bulk" => new BulkRepo($this, $this->getConfig()),
            "file" => new FileRepo($this, $this->getConfig())
        };
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($repo), $this);
    }

    public function onDisable(): void
    {
        parent::onDisable();
    }
}
