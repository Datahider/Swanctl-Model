<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\swanctlModel\data;

/**
 * Description of DBActivationCode
 *
 * @author drweb
 */
class DBActivationCodeEvent extends \losthost\DB\DBObject {

    const TABLE_NAME = 'swanctl_activation_codes_log';
    
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                code bigint UNSIGNED NOT NULL,
                event_datetime datetime NOT NULL,
                event_type varchar(20) NOT NULL,
                event_data varchar(20),
                PRIMARY KEY (id)
            ) COMMENT = 'v1.0.0'
            END;
    
}
