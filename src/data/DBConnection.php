<?php

namespace losthost\swanctlModel\data;

class DBConnection extends \losthost\DB\DBObject {

    const TABLE_NAME = 'swanctl_connections';
    
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                user bigint UNSIGNED NOT NULL,
                login varchar(30),
                password varchar(20) NOT NULL,
                is_enabled tinyint(1) NOT NULL,
                valid_till DATETIME,
                description varchar(1024) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE INDEX `LOGIN` (`login`)
            ) COMMENT = 'v1.0.0'
            END;
    
}
