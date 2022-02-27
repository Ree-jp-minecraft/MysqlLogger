-- #!mysql
-- #{ mysql_logger
-- #    { init
-- #        { block_log
CREATE TABLE IF NOT EXISTS BLOCK_LOG
(
    server_id VARCHAR(9)  NOT NULL,
    action    VARCHAR(9)  NOT NULL,
    xuid      BIGINT      NOT NULL,
    x         BIGINT      NOT NULL,
    y         BIGINT      NOT NULL,
    z         BIGINT      NOT NULL,
    world     VARCHAR(99) NOT NULL,
    item      JSON        NOT NULL,
    block     JSON        NOT NULL,
    time      DATETIME    NOT NULL
);
-- #        }
-- #    }
-- #    { send
LOAD DATA LOCAL INFILE '/home/container/plugin_data/MysqlLogger/temp.csv' INTO TABLE BLOCK_LOG FIELDS TERMINATED BY ';';
-- #    }
-- #}
