<?php
/**
 * Created by PhpStorm.
 * User: Zaid
 * Date: 2/26/2019
 * Time: 11:05 PM
 */
require_once 'library/constant.php';
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';
require_once 'function/f_reference.php';

$constant = new Class_constant();
$fn_general = new Class_general();
$fn_login = new Class_login();
$fn_reference = new Class_reference();
$api_name = 'api_work_category';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 2700 */
try {
    Class_db::getInstance()->db_connect();
    $request_method = $_SERVER['REQUEST_METHOD'];
    //$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);

    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:2701) [' . __LINE__ . '] - Parameter Authorization empty');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);

    if ('GET' === $request_method) {
        $workcategoryId = filter_input(INPUT_GET, 'workcategoryId');
        $form_data['result'] = $fn_reference->get_work_category($workcategoryId);
        $form_data['success'] = true;
    }
    else if ('POST' === $request_method) {
        $workcategoryDesc = filter_input(INPUT_POST, 'workcategoryDesc');
        $worktypeId = filter_input(INPUT_POST, 'worktypeId');
        $workcategoryStatus = filter_input(INPUT_POST, 'workcategoryStatus');

        $params = array(
            'workcategoryDesc'=>$workcategoryDesc,
            'worktypeId'=>$worktypeId,
            'workcategoryStatus'=>$workcategoryStatus
        );

        $result = $fn_reference->add_work_category($params);
        $fn_general->updateVersion(5);
        $fn_general->save_audit('20', $jwt_data->userId, 'Work Category = ' . $workcategoryDesc);

        $form_data['errmsg'] = $constant::SUC_WORK_CATEGORY_ADD;
        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('PUT' === $request_method) {
        $workcategoryId = filter_input(INPUT_GET, 'workcategoryId');
        $put_data = file_get_contents("php://input");
        parse_str($put_data, $put_vars);
        $action = $put_vars['action'];

        if ($action === 'update') {
            $fn_reference->update_work_category($workcategoryId, $put_vars);
            $fn_general->updateVersion(5);
            $fn_general->save_audit('21', $jwt_data->userId, 'Work Category = ' . $put_vars['workcategoryDesc']);
            $form_data['errmsg'] = $constant::SUC_WORK_CATEGORY_EDIT;
        }
        else if ($action === 'deactivate') {
            $workcategoryDesc = $fn_reference->deactivate_work_category($workcategoryId);
            $fn_general->updateVersion(5);
            $fn_general->save_audit('22', $jwt_data->userId, 'Work Category = ' . $workcategoryDesc);
            $form_data['errmsg'] = $constant::SUC_WORK_CATEGORY_DEACTIVATE;
        }
        else if ($action === 'activate') {
            $workcategoryDesc = $fn_reference->activate_work_category($workcategoryId);
            $fn_general->updateVersion(5);
            $fn_general->save_audit('23', $jwt_data->userId, 'Work Category = ' . $workcategoryDesc);
            $form_data['errmsg'] = $constant::SUC_WORK_CATEGORY_ACTIVATE;
        } else {
            throw new Exception('(ErrCode:2702) [' . __LINE__ . '] - Parameter action invalid ('.$action.')');
        }

        $form_data['success'] = true;
    }
    else if ('DELETE' === $request_method) {
        $workcategoryId = filter_input(INPUT_GET, 'workcategoryId');

        $workcategoryDesc = $fn_reference->delete_work_category($workcategoryId);
        $fn_general->updateVersion(5);
        $fn_general->save_audit('24', $jwt_data->userId, 'Work Category = ' . $workcategoryDesc);

        $form_data['errmsg'] = $constant::SUC_WORK_CATEGORY_DELETE;
        $form_data['success'] = true;
    } else {
        throw new Exception('(ErrCode:2700) [' . __LINE__ . '] - Wrong Request Method');
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