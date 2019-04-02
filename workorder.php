<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/10/2019
 * Time: 12:08 AM
 */
require_once 'library/constant.php';
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';
require_once 'function/f_workorder.php';
require_once 'function/f_task.php';

$constant = new Class_constant();
$fn_general = new Class_general();
$fn_login = new Class_login();
$fn_workorder = new Class_workorder();
$fn_task = new Class_task();
$api_name = 'api_workorder';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 3100 */
try {
    Class_db::getInstance()->db_connect();
    $request_method = $_SERVER['REQUEST_METHOD'];
    //$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);

    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:3101) [' . __LINE__ . '] - Parameter Authorization empty');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);

    if ('POST' === $request_method) {
        $action = filter_input(INPUT_POST, 'action');
        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;

        if ($action === 'create_id_temp') {
            $ticketId = filter_input(INPUT_POST, 'ticketId');
            $result = $fn_workorder->create_new_workorder($ticketId, $jwt_data->userId);
            $fn_general->save_audit('32', $jwt_data->userId, 'workorder_id = ' . $result);
        } else {
            throw new Exception('(ErrCode:3102) [' . __LINE__ . '] - Parameter action (' . $action . ') invalid');
        }

        Class_db::getInstance()->db_commit();
        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('GET' === $request_method) {
        $workorderId = filter_input(INPUT_GET, 'workorderId');

        if (isset($headers['Reportid'])) {
            $reportId = $headers['Reportid'];
            if ($reportId === '1') {
                $result = $fn_workorder->get_workorder_by_status();
            }
            else if ($reportId === 'get_pending_tasks') {
                $groupIds = $fn_task->get_checkpoint_groups($jwt_data->userId, '5', '(3,5)');
                if (empty($groupIds)) {
                    throw new Exception('(ErrCode:3106) [' . __LINE__ . '] - '.$constant::ERR_WORKORDER_NOGROUP);
                }
                $result = $fn_workorder->get_workorder_pending_list(implode(',', $groupIds));
            } else {
                throw new Exception('(ErrCode:3105) [' . __LINE__ . '] - Parameter Reportid ('.$reportId.') invalid');
            }
        } else if (!is_null($workorderId)) {
            $result = $fn_workorder->get_workorder($workorderId);
        } else {
            $result = $fn_workorder->get_workorder_list();
        }

        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('PUT' === $request_method) {
        $workorderId = filter_input(INPUT_GET, 'workorderId');
        $put_data = file_get_contents("php://input");
        parse_str($put_data, $put_vars);
        $action = $put_vars['action'];

        if (empty($workorderId)) {
            throw new Exception('(ErrCode:3104) [' . __LINE__ . '] - Parameter workorderId empty');
        }

        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;

        if ($action === 'save_workorder' || $action === 'save_workorder2') {
            $fn_workorder->save_workorder($workorderId, $put_vars);
            $fn_general->save_audit('34', $jwt_data->userId, 'workorder_id = ' . $workorderId);
            if ($action === 'save_workorder') {
                $form_data['errmsg'] = $constant::SUC_WORKORDER_SAVE;
            }
        }
        else if ($action === 'submit_workorder') {
            $taskInfo = $fn_workorder->get_task_info($workorderId, '5', '2');
            $taskIdNew = $fn_task->submit_task($taskInfo['taskId'], $jwt_data->userId, '9', '', '', '', $taskInfo['groupId']);
            $fn_workorder->submit_workorder($workorderId, $taskIdNew);
            $fn_general->save_audit('35', $jwt_data->userId, 'workorder_id = ' . $workorderId);
            $form_data['errmsg'] = $constant::SUC_WORKORDER_SUBMIT;
        } else {
            throw new Exception('(ErrCode:3103) [' . __LINE__ . '] - Parameter action (' . $action . ') invalid');
        }

        Class_db::getInstance()->db_commit();
        $form_data['result'] = $result;
        $form_data['success'] = true;
    } else {
        throw new Exception('(ErrCode:3100) [' . __LINE__ . '] - Wrong Request Method');
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