<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/20/2019
 * Time: 2:08 AM
 */
require_once 'library/constant.php';
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';
require_once 'function/f_user.php';
require_once 'function/f_employee.php';

$constant = new Class_constant();
$fn_general = new Class_general();
$fn_login = new Class_login();
$fn_user = new Class_user();
$fn_employee = new Class_employee();
$api_name = 'api_employee';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 3300 */
try {
    Class_db::getInstance()->db_connect();
    $request_method = $_SERVER['REQUEST_METHOD'];
    //$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);

    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:3301) [' . __LINE__ . '] - Parameter Authorization empty');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);

    if ('POST' === $request_method) {
        $action = filter_input(INPUT_POST, 'action');
        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;

        if ($action === 'get_by_mykad') {
            $groupId = filter_input(INPUT_POST, 'groupId');
            $mykadNo = filter_input(INPUT_POST, 'userMykadNo');
            $result = $fn_employee->check_by_ic($mykadNo, $groupId);
        }
        else if ($action === 'add_employee_existing') {
            $groupId = filter_input(INPUT_POST, 'groupId');
            $userId = filter_input(INPUT_POST, 'userId');
            $roles = filter_input(INPUT_POST, 'roles');
            $fn_employee->add_employee_existing($groupId, $userId, $roles);
            $fn_general->save_audit('40', $jwt_data->userId, 'user_id = ' . $userId . ', roles = ' . $roles);
            $form_data['errmsg'] = $constant::SUC_EMPLOYEE_ADD_EXISTING;
        }
        else if ($action === 'add_employee_new') {
            $groupId = filter_input(INPUT_POST, 'groupId');
            $roles = filter_input(INPUT_POST, 'roles');
            $userName = filter_input(INPUT_POST, 'userName');
            $userMykadNo = filter_input(INPUT_POST, 'userMykadNo');
            $userPassword = filter_input(INPUT_POST, 'userPassword');
            $userFirstName = filter_input(INPUT_POST, 'userFirstName');
            $userLastName = filter_input(INPUT_POST, 'userLastName');
            $userContactNo = filter_input(INPUT_POST, 'userContactNo');
            $userEmail = filter_input(INPUT_POST, 'userEmail');

            $param = array(
                'userName'=>$userName,
                'userMykadNo'=>$userMykadNo,
                'userPassword'=>$userPassword,
                'userFirstName'=>$userFirstName,
                'userLastName'=>$userLastName,
                'userContactNo'=>$userContactNo,
                'userEmail'=>$userEmail,
                'userType'=>'1'
            );
            $userId = $fn_user->add_user($param);
            $fn_employee->add_employee_new($groupId, $userId, $roles);
            $fn_general->save_audit('41', $jwt_data->userId, 'user_id = ' . $userId . ', roles = ' . $roles);
            $form_data['errmsg'] = $constant::SUC_EMPLOYEE_ADD_NEW;
        } else {
            throw new Exception('(ErrCode:3302) [' . __LINE__ . '] - Parameter action ('.$action.') invalid');
        }

        Class_db::getInstance()->db_commit();
        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('GET' === $request_method) {
        $groupId = filter_input(INPUT_GET, 'groupId');
        $userId = filter_input(INPUT_GET, 'userId');

        if (!is_null($groupId) && !is_null($userId)) {
            $result = $fn_employee->get_employee($groupId, $userId);
        }

        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('PUT' === $request_method) {
        $userId = filter_input(INPUT_GET, 'userId');
        $put_data = file_get_contents("php://input");
        parse_str($put_data, $put_vars);
        $action = $put_vars['action'];

        if ($action === 'update') {
            $groupId = $put_vars['groupId'];
            $rolesStr = $put_vars['roles'];
            $fn_employee->update_employee($groupId, $userId, $rolesStr);
            $fn_general->save_audit('42', $jwt_data->userId, 'user_id = ' . $userId . ', roles = ' . $rolesStr);
            $form_data['errmsg'] = $constant::SUC_EMPLOYEE_EDIT;
        } else {
            throw new Exception('(ErrCode:3302) [' . __LINE__ . '] - Parameter action invalid (' . $action . ')');
        }

        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('DELETE' === $request_method) {
        $userId = filter_input(INPUT_GET, 'userId');
        $groupId = filter_input(INPUT_GET, 'groupId');

        $fn_employee->delete_employee($groupId, $userId);
        $fn_general->save_audit('43', $jwt_data->userId, 'user_id = ' . $userId . ', group_id = ' . $groupId);

        $form_data['errmsg'] = $constant::SUC_EMPLOYEE_DELETE;
        $form_data['success'] = true;
    } else {
        throw new Exception('(ErrCode:3300) [' . __LINE__ . '] - Wrong Request Method');
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
        $form_data['errmsg'] = $constant::ERR_DEFAULT;
    }
    $fn_general->log_error($api_name, __LINE__, $ex->getMessage());
}

echo json_encode($form_data);