<?php

namespace ree_jp\template;

use pocketmine\plugin\PluginBase;

class MysqlLoggerPlugin extends PluginBase
{

    public function onEnable(): void
    {
        $repo = new Repository($this, $this->getConfig());
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($repo), $this);
    }

    public function onDisable(): void
    {
        parent::onDisable();
    }
}
