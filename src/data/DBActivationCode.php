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
class DBActivationCode extends \losthost\DB\DBObject {

    const TABLE_NAME = 'swanctl_activation_codes';
    
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                id bigint NOT NULL AUTO_INCREMENT,
                code varchar(30) NOT NULL,
                valid_till datetime NOT NULL,
                period_days int UNSIGNED NOT NULL,
                max_activations int UNSIGNED NOT NULL,
                remain_activations int UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                UNIQUE INDEX CODE(code)
            ) COMMENT = 'v1.0.0'
            END;
    
}
