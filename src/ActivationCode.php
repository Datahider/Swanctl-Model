<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\swanctlModel;

/**
 * Description of ActivationCode
 *
 * @author drweb
 */
class ActivationCode {
    
    public function create(\DateTime|\DateTimeImmutable $valid_till, int $period_days, int $max_activations) {
        
        $model = Model::getModel();
        
        $code = new data\DBActivationCode();
        $code->code = sprintf(
                "%s%03d:%s", 
                \losthost\passg\Pass::generate(3, \losthost\passg\Pass::CLEAN_LOWERCASE), 
                $period_days,
                \losthost\passg\Pass::generate(7));
        
        $code->valid_till = $valid_till;
        $code->period_days = $period_days;
        $code->max_activations = $max_activations;
        $code->remain_activations = $max_activations;

        data\DBActivationCodeEvent::initDataStructure();
        $model->beginTransaction();
        try {
            $code->write();
            $code_event = new data\DBActivationCodeEvent();
            $code_event->code = $code->id;
            $code_event->event_datetime = date_create();
            $code_event->event_type = 'creation';
            $code_event->event_data = $code->max_activations;
            $code_event->write();
            $model->commit();
        } catch (\Exception $e) {
            $model->rollBack();
            throw $e;
        }
        
        return $code;
    }
    
    public function get(int|string $id_or_code) {
        if (is_int($id_or_code)) {
            return new data\DBActivationCode('id = ?', $id_or_code);
        } else {
            return new data\DBActivationCode('code = ?', $id_or_code);
        }
    }
    
    public function list(string $condition=null, mixed $param=null, string|null $order_by=null, ?string $sort=null, int $limit=25, int $start=0) {        
    
        $sql = "SELECT id FROM [swanctl_activation_codes] ";
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

}
