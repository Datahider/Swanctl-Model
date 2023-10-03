<?php declare (strict_types=1);

namespace losthost\swanctlModel;

/**
 * Description of Model
 *
 * @author drweb
 */
class Model {
    static Model $model;
    
    public User $user;
    public Connection $connection;
    public ActivationCode $activation_code;
    
    static public function getModel() : Model {
        if (!isset(self::$model)) {
            self::$model = new Model();
        } 
        return self::$model;
    }
    
    public function __construct() {
        if (isset(self::$model)) {
            throw new \Exception("Use getModel() to get the Model.", -10005);
        }
        
        $this->user = new User();
        $this->connection = new Connection();
        $this->activation_code = new ActivationCode();
    }
    
    public function connect($host, $user, $pass, $database, $table_prefix) {
        \losthost\DB\DB::connect($host, $user, $pass, $database, $table_prefix);
    }
    
    public function connectionStatus() {
        return \losthost\DB\DB::$pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS);
    }
    
    public function query($sql) {
        return \losthost\DB\DB::$pdo->query($sql);
    }
    
    public function prepare($sql) {
        return \losthost\DB\DB::$pdo->prepare($sql);
    }
    
    public function beginTransaction() {
        \losthost\DB\DB::$pdo->beginTransaction();
    }
    
    public function commit() {
        \losthost\DB\DB::$pdo->commit();
    }
    
    public function rollBack() {
        \losthost\DB\DB::$pdo->rollBack();
    }
    
    public function now(bool $as_string=false) {
        $result = date_create_immutable();
        if ($as_string) {
            return $result->format(\losthost\DB\DB::DATE_FORMAT);
        } else {
            return $result;
        }
    }
}
