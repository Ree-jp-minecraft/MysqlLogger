<?php

namespace ree_jp\mysql_logger;

class BlockLog
{
    const ACTION_BREAK = "break";
    const ACTION_PLACE = "place";

    public function __construct(public string $action, public string $xuid, public int $x, public int $y, public int $z, public string $world,
                                public string $item, public string $block, public string $server_id, public string $time)
    {
    }
}