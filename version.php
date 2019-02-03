<?php
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';

$fn_general = new Class_general();
$fn_login = new Class_login();
$api_name = 'api_version';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');

/* Error code range - 2200 */ 
try {   
    Class_db::getInstance()->db_connect();
    $request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD'); 
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);
    
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:2201) [' . __LINE__ . '] - Parameter Authorization emtpy');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);
    
    if ('GET' === $request_method) {                    
        $result = array();
        $arr_version = Class_db::getInstance()->db_select('sys_version');        
        foreach ($arr_version as $version) {
            $result[$version['version_name']] = $version['version_no'];
        }  
                
        $form_data['result'] = $result;
        $form_data['success'] = true; 
        $fn_general->log_debug($api_name, __LINE__, 'Result = '.print_r($result, true));
    } else {
        throw new Exception('(ErrCode:2200) [' . __LINE__ . '] - Wrong Request Method');   
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
        $form_data['errmsg'] = 'Berlaku kesilapan pada sistem. Sila hubungi pihak Admin!';
    }
    $fn_general->log_error($api_name, __LINE__, $ex->getMessage());
}

echo json_encode($form_data);
