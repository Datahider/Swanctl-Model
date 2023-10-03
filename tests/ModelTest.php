<?php declare (strict_types=1);

namespace losthost\swanctlModel;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase {
    
    public function testCanGetModel() : void {
        $model = Model::getModel();
        $this->assertSame($model, Model::getModel());
    }
    
    public function testCanConnectToDatabase() : void {
        $model = Model::getModel();
        $model->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PREFIX);

        \losthost\DB\DB::dropAllTables(true, true); // Prepares test database for new tests

        $this->assertSame(DB_HOST. ' via TCP/IP', $model->connectionStatus());
    }
    
    public function testCanDealWithDatabase() : void {
        $model = Model::getModel();
        $magic = $model->query('SELECT "<<!MaGiC!>>"')->fetchColumn(0);
        $this->assertSame("<<!MaGiC!>>", $magic);
        
        $sth = $model->prepare("SELECT ?");
        $sth->execute(['magic string']);
        $this->assertSame('magic string', $sth->fetchColumn(0));
    }
    
    public function testCantInstantiateAnotherModel() : void {
        $this->expectExceptionCode(-10005);
        
        new Model();
    }
    
    public function testModelHasUser() : void {
        $model = Model::getModel();
        $this->assertSame(true, is_a($model->user, 'losthost\swanctlModel\User'));
    }
    
    public function testHasConnection() : void {
        $model = Model::getModel();
        $this->assertSame(true, is_a($model->user, 'losthost\swanctlModel\User'));
    }
    
    public function testHasActivationCode() : void {
        $model = Model::getModel();
        $this->assertSame(true, is_a($model->activation_code, 'losthost\swanctlModel\ActivationCode'));
    }
    
    public function testNow() {
        $model = Model::getModel();
        $this->assertLessThanOrEqual(1, abs(date_create_immutable()->getTimestamp()-$model->now()->getTimestamp()));
        
        $now_string = date_create_immutable()->format(\losthost\DB\DB::DATE_FORMAT);
        $this->assertEquals($now_string, $model->now(true));
    }
    /**
     * Tests of Model::$user
     */
    
    public function testCanCreateUser() : void {
        $model = Model::getModel();
        $user1 = $model->user->create('new_login', 'new_password');
        $this->assertSame(1, (int)$user1->id);
        $this->assertSame('new_login', $user1->login);
        $this->assertSame('new_password', $user1->password);
        $this->assertSame(false, $user1->isNew());
        
        $user2 = $model->user->create('second', 'secret');
        $this->assertSame(2, (int)$user2->id);
    }
    
    public function testCanAuthenticateUser() : void {
        $model = Model::getModel();
        $user = $model->user->authenticate('second', 'secret');
        $this->assertSame(2, (int)$user->id);
        $this->assertSame('second', $user->login);
        $this->assertSame('secret', $user->password);
    }
    
    public function testWrongLogin() : void {
        $model = Model::getModel();
        $this->expectExceptionCode(-10008);
        $model->user->authenticate('wrong', 'new_password');
    }
    
    public function testWrongPassword() : void {
        $model = Model::getModel();
        $this->expectExceptionCode(-10008);
        $model->user->authenticate('new_login', 'wrong');
    }
    
    public function testCanGetUserByLoginOrId() : void {
        $model = Model::getModel();
        $user1 = $model->user->get(1);
        $this->assertSame('new_login', $user1->login);
        
        $user2 = $model->user->get('second');
        $this->assertSame(2, (int)$user2->id);
    }
    
    public function testDeleteUser() : void {
        $model = Model::getModel();
        $user = $model->user->get(1);
        $user->delete();
        
        $this->expectExceptionCode(-10002);
        $model->user->get(1);
    }
    
    public function testCreateManyUsers() : void {
        $model = Model::getModel();
        foreach(range(3, 100) as $index) {
            $model->user->create("user$index", uniqid("pass_"));
        }
        
        $user = $model->user->get(10);
        $this->assertSame('user10', $user->login);
    }
    
    public function testListUsers() : void {
        $model = Model::getModel();
        $list = $model->user->list('login LIKE ?', 'user1%', 'id', 'ASC', 15);
        
        $this->assertSame(11, count($list));
        $this->assertSame(100, (int)$list[10]->id);
        
        $list_from_1 = $model->user->list('login LIKE ?', 'user1%', 'login', 'ASC', 10, 1);
        $this->assertSame(10, count($list_from_1));
        $this->assertSame(100, (int)$list_from_1[0]->id);
    }

    /**
     * Tests of Model::$connection
     */
    
    public function testCanCreateConnectionForUser() : void {
        $model = Model::getModel();
        $user = $model->user->get(2);

        $description = 'New test connection 1';
        $grace_period = date_interval_create_from_date_string("7 days");
        $connection = $model->connection->create($user, $grace_period, $description);
        $this->assertSame(1, (int)$connection->id);
        $this->assertSame($description, $connection->description);
        $this->assertSame(2, $connection->user);
        $this->assertSame(true, $connection->is_enabled);
        $this->assertSame('u1', $connection->login);
        $this->assertTrue(date_create()->add($grace_period)->getTimestamp() == $connection->valid_till->getTimestamp());
    }
    
    public function testCanGetConnection() : void {
        $model = Model::getModel();
        $connection = $model->connection->get(1);
        
        $expected_timestamp = date_create()->add(date_interval_create_from_date_string("7 days"))->getTimestamp();
        $this->assertSame("New test connection 1", $connection->description);
        $this->assertLessThanOrEqual($expected_timestamp+1, $connection->valid_till->getTimestamp());
        $this->assertGreaterThanOrEqual($expected_timestamp, $connection->valid_till->getTimestamp());
        
        $connection_by_login = $model->connection->get('u1');
        $this->assertEquals($connection, $connection_by_login);
    }
    
    public function testCanDeleteConnection() : void {
        $model = Model::getModel();
        $connection = $model->connection->get(1);
        $connection->delete();

        $this->expectExceptionCode(-10002);
        $model->connection->get(1);
    }
    
    public function testCreateManyConnections() : void {
        $model = Model::getModel();
        $user = $model->user->get(3);
        $pass = \losthost\passg\Pass::generate();
        foreach(range(2, 100) as $index) {
            $model->connection->create($user, null, "Connection ID: $index", true, null, $pass, 'vpn');
        }
        
        $connection = $model->connection->get(11);
        $this->assertSame("Connection ID: 11", $connection->description);
        $this->assertSame("vpn11", $connection->login);
        $this->assertSame($pass, $connection->password);
    }
    
    public function testListConnections() : void {
        $model = Model::getModel();
        $list = $model->connection->list('user = ?', 3, 'id', 'ASC', 10);
        
        $this->assertCount(10, $list);
        $this->assertSame(3, $list[1]->id);
        
        $list = $model->connection->list('login LIKE ?', 'vpn9%', 'login', 'DESC', 1, 2);
        
        $this->assertCount(1, $list);
        $this->assertSame('vpn97', $list[0]->login);
        
    }
    
    /**
     * Test of Model::$activation_code
     */
    public function testCanCreateActivationCode() : void {
        $model = Model::getModel();
        $date1 = date_create()->add(date_interval_create_from_date_string('10 days'));
        $date2 = date_create_immutable()->add(date_interval_create_from_date_string('1 second'));
        $code1 = $model->activation_code->create($date1, 10, 20);
        $code2 = $model->activation_code->create($date2, 10, 1);
        
        $lowercase = \losthost\passg\Pass::CLEAN_LOWERCASE;
        $digits = \losthost\passg\Pass::ALL_DIGITS;
        $all = \losthost\passg\Pass::CLEAN;
        
        $this->assertSame(14, strlen($code1->code));
        $this->assertMatchesRegularExpression("/^[{$lowercase}]{3}[{$digits}]{3}:[{$all}]{7}/", $code1->code);
        $this->assertSame(14, strlen($code2->code));
        $this->assertMatchesRegularExpression("/^[{$lowercase}]{3}[{$digits}]{3}:[{$all}]{7}/", $code2->code);
    }
    
    public function testCantCreateIncorrectActivationCode() {
        $this->expectExceptionCode(22003);
        $model = Model::getModel();
        $model->activation_code->create(date_create(), -1, -1);
    }
    
    public function testCanGetActivationCode() : void {
        $model = Model::getModel();
        
        // by id
        $activation_code_by_id = $model->activation_code->get(2);
        $this->assertSame(1, $activation_code_by_id->max_activations);
        $this->assertSame(1, $activation_code_by_id->remain_activations);
        
        // by code
        $activation_code_by_code = $model->activation_code->get($activation_code_by_id->code);
        $this->assertEquals($activation_code_by_id, $activation_code_by_code);
               
    }
    
    public function testCanActivateActivationCode() : void {
        $model = Model::getModel();
        $code = $model->activation_code->get(1);
        $connection = $model->connection->get(11);
        $initial_expiration = $connection->valid_till;
        
        $model->connection->prolong($connection, $code->code);
        $this->assertEquals($initial_expiration->add(date_interval_create_from_date_string("10 days")), $connection->valid_till);
    }
    
    public function testCanNotActivateWrongCode() : void {
        
        $this->expectExceptionCode(-10002);
        $model = Model::getModel();
        $connection = $model->connection->get(11);
        $model->connection->prolong($connection, "12345");
                
    }
    
    public function testCanNotActivateExpiredCode() : void {
        
        $this->expectExceptionCode(-10002);
        $model = Model::getModel();
        $connection = $model->connection->get(11);
        $code = $model->activation_code->get(2);
        
        sleep(2); // allow the code to expire
        $model->connection->prolong($connection, $code->code);
    }
    
    public function testExceptionWhileCodeActivation() {
        $this->expectExceptionCode(-10013);
        $model = Model::getModel();
        $connection = $model->connection->get(99);
        $code = $model->activation_code->get(1);
        
        $connection->delete();
        $model->connection->prolong($connection, $code->code);
    }
    
    public function testCanListActivationCodes() : void {
        
        $model = Model::getModel();
        
        $list = $model->activation_code->list('valid_till < ?', $model->now(true), 'valid_till', 'DESC', 10);
        
        $this->assertCount(1, $list);
        $this->assertInstanceOf(data\DBActivationCode::class, $list[0]);

        $list_from_1 = $model->activation_code->list('valid_till < ?', $model->now(true), 'valid_till', 'DESC', 10, 1);
        
        $this->assertCount(0, $list_from_1);
    }
}
