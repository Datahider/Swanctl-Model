<?php declare (strict_types=1);

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\swanctlModel;

/**
 * Description of Connection
 *
 * @author drweb
 */
class Connection {
    
    public function create(data\DBUser $user, ?\DateInterval $grace_period, string $description, bool $is_enabled=true, ?string $login=null, ?string $password=null, string $prefix='u') {
        
        $connection = new data\DBConnection();
        $connection->user = $user->id;
        $connection->valid_till = $grace_period === null ? date_create() : date_create()->add($grace_period);
        $connection->description = $description;
        $connection->is_enabled = $is_enabled;
        $connection->login = $login;
        $connection->password = $password === null ? \losthost\passg\Pass::generate() : $password;
        
        $model = Model::getModel();
        $model->beginTransaction();
        $connection->write();
        if ($login === null) {
            $connection->login = "{$prefix}{$connection->id}";
            $connection->write();
        }
        $model->commit();
        
        return $connection;
    }
    
    public function get(int|string $id_or_login) {
    
        if (is_int($id_or_login)) {
            $connection = new data\DBConnection("id = ?", [$id_or_login]);
        } elseif (is_string($id_or_login)) {
            $connection = new data\DBConnection("login = ?", [$id_or_login]);
        }
        
        return $connection;
    }

    public function list(string $condition=null, mixed $param=null, string|null $order_by=null, ?string $sort=null, int $limit=25, int $start=0) {

        $sql = "SELECT id FROM [swanctl_connections] ";
        if (!empty($condition)) {
            $sql .= "WHERE $condition ";
        }
        if (!empty($order_by)) {
            $sql .= "ORDER BY $order_by $sort ";
        }
        
        if (!empty($start) && !empty($limit)) {
            $sql .= "LIMIT $start, $limit"; 
        } elseif (!empty($limit)) {
            $sql .= "LIMIT $limit";
        }
        
        $ids = new \losthost\DB\DBView($sql, $param);
        
        $result = [];
        while ($ids->next()) {
            $result[] = $this->get((int)$ids->id);
        }
        return $result;
    }
 
    public function prolong(data\DBConnection &$connection, string $code) {
        $model = Model::getModel();
        
        data\DBActivationCodeEvent::initDataStructure();
        $db_code = new data\DBActivationCode('code = ? AND remain_activations > 0 AND valid_till >= ?', [$code, $model->now(true)], false);
        
        $model->beginTransaction();
        try {
            $now = date_create();
            if ($now->getTimestamp() > $connection->valid_till->getTimestamp()) {
                $connection->valid_till = $now->add(date_interval_create_from_date_string("{$db_code->period_days} days"));
            } else {
                $connection->valid_till = $connection->valid_till->add(date_interval_create_from_date_string("{$db_code->period_days} days"));
            }
            $db_code->remain_activations = $db_code->remain_activations - 1;
            $db_event = new data\DBActivationCodeEvent();
            $db_event->code = $db_code->id;
            $db_event->event_datetime = $model->now();
            $db_event->event_type = 'activation';
            $db_event->event_data = $connection->id;
            $connection->write();
            $db_code->write();
            $db_event->write();
            $model->commit();
        } catch (\Exception $exc) {
            $model->rollBack();
            throw $exc;
        }
    }
}
