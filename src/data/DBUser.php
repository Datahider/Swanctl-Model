<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\swanctlModel\data;

/**
 * Description of newPHPClass
 *
 * @author drweb
 */
class DBUser extends \losthost\DB\DBObject {
    
    const TABLE_NAME = 'swanctl_users';
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                login varchar(50) NOT NULL,
                password varchar(50) NOT NULL,
                integration_id bigint(20),
                PRIMARY KEY (id),
                UNIQUE INDEX INTEGRATION_ID(integration_id)
            ) COMMENT = 'v1.0.0'
            END;
    
}
