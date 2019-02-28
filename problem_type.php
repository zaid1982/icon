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
$api_name = 'api_problem_type';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 2500 */
try {
    Class_db::getInstance()->db_connect();
    $request_method = $_SERVER['REQUEST_METHOD'];
    //$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);

    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:2401) [' . __LINE__ . '] - Parameter Authorization empty');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);

    if ('GET' === $request_method) {
        $problemtypeId = filter_input(INPUT_GET, 'problemtypeId');
        $form_data['result'] = $fn_reference->get_problem_type($problemtypeId);
        $form_data['success'] = true;
    }
    else if ('POST' === $request_method) {
        $problemtypeDesc = filter_input(INPUT_POST, 'problemtypeDesc');
        $problemtypeStatus = filter_input(INPUT_POST, 'problemtypeStatus');

        $params = array(
            'problemtypeDesc'=>$problemtypeDesc,
            'problemtypeStatus'=>$problemtypeStatus
        );

        $result = $fn_reference->add_problem_type($params);
        $fn_general->updateVersion(3);
        $fn_general->save_audit('10', $jwt_data->userId, 'Problem Type = ' . $problemtypeDesc);

        $form_data['errmsg'] = $constant::SUC_PROBLEM_TYPE_ADD;
        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('PUT' === $request_method) {
        $problemtypeId = filter_input(INPUT_GET, 'problemtypeId');
        $put_data = file_get_contents("php://input");
        parse_str($put_data, $put_vars);

        $fn_reference->update_problem_type($problemtypeId, $put_vars);
        $fn_general->updateVersion(3);
        $fn_general->save_audit('12', $jwt_data->userId, 'Problem Type = ' . $put_vars['problemtypeDesc']);

        $form_data['errmsg'] = $constant::SUC_PROBLEM_TYPE_EDIT;
        $form_data['success'] = true;
    }
    else if ('DELETE' === $request_method) {

    } else {
        throw new Exception('(ErrCode:2400) [' . __LINE__ . '] - Wrong Request Method');
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