<?php
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';
require_once 'function/f_user.php';

$fn_general = new Class_general();
$fn_login = new Class_login();
$fn_user = new Class_user();
$api_name = 'api_user';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 2100 */ 
try {   
    Class_db::getInstance()->db_connect();
    $request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD'); 
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);   
    
    if ('PUT' === $request_method) {  
        $userId = filter_input(INPUT_GET, 'userId'); 
        $put_data = file_get_contents("php://input");
        parse_str($put_data, $put_vars);
        $action = $put_vars['action'];
        
        if (empty($userId)) {
            throw new Exception('(ErrCode:2102) [' . __LINE__ . '] - Parameter userId empty');
        }        
        if (empty($action)) {
            throw new Exception('(ErrCode:2103) [' . __LINE__ . '] - Parameter action empty');
        } 
        
        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;
        
        if ($action === 'profile') {
            $fn_user->update_profile($userId, $put_vars);  
            $fn_general->save_audit('5', $userId);
        } 
        else if ($action === 'password') {
            $fn_user->change_password($userId, $put_vars);  
            $fn_general->save_audit('6', $userId);
        }
        
        Class_db::getInstance()->db_commit();        
        Class_db::getInstance()->db_close();        
        $form_data['success'] = true;      
    } else {
        throw new Exception('(ErrCode:2100) [' . __LINE__ . '] - Wrong Request Method');   
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
        $form_data['errmsg'] = 'Error occured. Please contact Administrator!';
    }
    $fn_general->log_error($api_name, __LINE__, $ex->getMessage());
}

echo json_encode($form_data);