<?php
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';
require_once 'function/f_user.php';

$fn_general = new Class_general();
$fn_login = new Class_login();
$fn_user = new Class_user();
$api_name = 'api_login';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 2000 */ 
try {   
    Class_db::getInstance()->db_connect();
    //$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $request_method = $_SERVER['REQUEST_METHOD'];
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);
    
    if ('POST' === $request_method) {
        $action = filter_input(INPUT_POST, 'action');
        
        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;        
        
        if ($action === 'login') {  
            $username = filter_input(INPUT_POST, 'username');
            $password = filter_input(INPUT_POST, 'password');
            $roleId = filter_input(INPUT_POST, 'roleId');

            $result = $fn_login->check_login($username, $password, $roleId);
            $fn_general->save_audit('1', $result['userId']);
        }     
        else if ($action === 'forgot_password') {      
            $username = filter_input(INPUT_POST, 'username');   
            $userId = $fn_user->forgot_password($username);
            $fn_general->save_audit('4', $userId); 
        } else {
            throw new Exception('(ErrCode:2001) [' . __LINE__ . '] - Parameter action ('.$action.') invalid'); 
        }
        
        Class_db::getInstance()->db_commit(); 
        $form_data['result'] = $result;
        $form_data['success'] = true;
        $fn_general->log_debug($api_name, __LINE__, 'Result = '.print_r($result, true));
    } else {
        throw new Exception('(ErrCode:2000) [' . __LINE__ . '] - Wrong Request Method');   
    }       
    Class_db::getInstance()->db_close();
} catch (Exception $ex) {
    if ($is_transaction) {
        Class_db::getInstance()->db_rollback();
    }
    Class_db::getInstance()->db_close();
    $form_data['error'] = substr($ex->getMessage(), strpos($ex->getMessage(), '] - ') + 4);
    if ($ex->getCode() === 31) {
        $form_data['errmsg'] = substr($ex->getMessage(), strpos($ex->getMessage(), '] - ') + 4);
    } else {
        $form_data['errmsg'] = 'Error on system. Please contact Administrator!';
    }
    $fn_general->log_error($api_name, __LINE__, $ex->getMessage());
}

echo json_encode($form_data);
