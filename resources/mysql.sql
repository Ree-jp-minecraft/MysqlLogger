-- #!mysql
-- #{ mysql_logger
-- #    { init
-- #        { block_log
CREATE TABLE IF NOT EXISTS BLOCK_LOG
(
    action    VARCHAR(9)  NOT NULL,
    xuid      BIGINT      NOT NULL,
    x         BIGINT      NOT NULL,
    y         BIGINT      NOT NULL,
    z         BIGINT      NOT NULL,
    world     VARCHAR(99) NOT NULL,
    item      VARCHAR(99) NOT NULL,
    block     VARCHAR(99) NOT NULL,
    server_id VARCHAR(9)  NOT NULL,
    time      DATETIME    NOT NULL
);
-- #        }
-- #        { delete
-- #          :server_id string
-- #          :time string
DELETE
FROM BLOCK_LOG
WHERE server_id = :server_id
  AND time < :time;
-- #        }
-- #        { clear
DELETE
FROM BLOCK_LOG
WHERE action = ''
  AND xuid = 0
  AND x = 0
  AND y = 0
  AND z = 0
  AND world = ''
  AND item = ''
  AND block = ''
  AND server_id = '';
-- #        }
-- #    }
-- #    { get
-- #      :x int
-- #      :y int
-- #      :z int
-- #      :world string
-- #      :server_id string
SELECT *
FROM BLOCK_LOG
WHERE x = :x
  AND y = :y
  AND z = :z
  AND world = :world
  AND server_id = :server_id
ORDER BY time DESC;
-- #    }
-- #}
