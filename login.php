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
    $request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD'); 
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);
    
    //$headers = apache_request_headers();
    //$fn_general->log_debug($api_name, __LINE__, $headers['Authorization']);
    
    if ('POST' === $request_method) {
        $action = filter_input(INPUT_POST, 'action');
        $username = filter_input(INPUT_POST, 'username');
        
        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;        
        
        if (is_null($action)) {
            $password = filter_input(INPUT_POST, 'password');  

            if (is_null($username) || $username === '') { 
                throw new Exception('(ErrCode:2001) [' . __LINE__ . '] - Sila pastikan Pengguna ID diisi', 31);         
            } 
            if (is_null($password) || $password === '') { 
                throw new Exception('(ErrCode:2002) [' . __LINE__ . '] - Sila pastikan Kata Laluan diisi', 31);         
            }      

            $sys_user = Class_db::getInstance()->db_select_single('sys_user', array('user_email'=>$username));
            if (empty($sys_user)) {
                throw new Exception('(ErrCode:2003) [' . __LINE__ . '] - Pengguna ID tidak wujud dalam sistem', 31);
            } 
            if ($sys_user['user_password'] !== md5($password)) {
                throw new Exception('(ErrCode:2004) [' . __LINE__ . '] - Kata Laluan tidak tepat', 31);
            } 
            if ($sys_user['user_status'] !== '1') {
                throw new Exception('(ErrCode:2005) [' . __LINE__ . '] - Pengguna ID tidak aktif. Sila hubungi pihak Admin.', 31);
            }

            $userId = $sys_user['user_id'];
            $groupId = $sys_user['group_id'];

            $token = $fn_login->create_jwt($userId, $username);        
            $arr_roles = Class_db::getInstance()->db_select('vw_roles', array('sys_user_role.user_id'=>$userId));
            $sys_group = Class_db::getInstance()->db_select_single('sys_group', array('group_id'=>$groupId), null, 1);

            $result['token'] = $token;
            $result['userId'] = $userId;
            $result['userFirstName'] = $sys_user['user_first_name'];
            $result['userLastName'] = $sys_user['user_last_name'];
            $result['userType'] = $sys_user['user_type'];
            $result['isFirstTime'] = is_null($sys_user['user_time_activate']) ? 'Yes' : 'No';
            $result['roles'] = $arr_roles;
            $result['group']['groupId'] = $sys_group['group_id'];
            $result['group']['groupName'] = $sys_group['group_name'];
            $result['group']['groupType'] = $sys_group['group_type'];
            $result['group']['groupRegNo'] = $fn_general->clear_null($sys_group['group_reg_no']);
            $result['group']['groupStatus'] = $sys_group['group_status'];
            $result['menu'] = $fn_login->get_menu_list($arr_roles);        

            $fn_general->save_audit('1', $userId);   
        }     
        else if ($action === 'forgot_password') {         
            $userId = $fn_user->forgot_password($username);
            $fn_general->save_audit('3', $userId); 
        } else {
            throw new Exception('(ErrCode:2006) [' . __LINE__ . '] - Parameter action ('.$action.') invalid'); 
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
        $form_data['errmsg'] = 'Berlaku kesilapan pada sistem. Sila hubungi pihak Admin!';
    }
    $fn_general->log_error($api_name, __LINE__, $ex->getMessage());
}

echo json_encode($form_data);
