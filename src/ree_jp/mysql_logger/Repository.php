<?php

namespace ree_jp\template;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Repository
{
    private DataConnector $db;

    public function __construct(PluginBase $owner, private Config $config)
    {
        $this->db = libasynql::create($owner, $config->get("database"), [
            "mysql" => "mysql.sql",
        ]);
    }
}