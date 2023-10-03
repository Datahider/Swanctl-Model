<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\swanctlModel;

/**
 * Description of User
 *
 * @author drweb
 */
class User {
    
    public function create(string $login, string $password) {
        $user = new data\DBUser();
        $user->login = $login;
        $user->password = $password;
        $user->write();
        return $user;
    }
    
    public function authenticate(string $login, string $password) {
        try {
            $user = new data\DBUser('login = ? AND password = ?', [$login, $password]);
        } catch (\Exception $e) {
            throw new \Exception('Login failed', -10008);
        }
        return $user;
    }
    
    public function get(int|string $id_or_login) {
        if (is_int($id_or_login)) {
            $user = new data\DBUser('id = ?', $id_or_login);
        } else {
            $user = new data\DBUser('login = ?', $id_or_login);
        }
        return $user;
    }
    
    public function list(string $condition=null, mixed $param=null, string|null $order_by=null, ?string $sort=null, int $limit=25, int $start=0) {

        $sql = "SELECT id FROM [swanctl_users] ";
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
