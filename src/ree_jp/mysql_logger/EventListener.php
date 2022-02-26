<?php

namespace ree_jp\template;

use pocketmine\event\Listener;

class EventListener implements Listener
{
    public function __construct(private Repository $repo)
    {
    }
}