<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/8/2019
 * Time: 3:21 AM
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
$api_name = 'api_area';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 2900 */
try {
    Class_db::getInstance()->db_connect();
    $request_method = $_SERVER['REQUEST_METHOD'];
    //$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);

    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:2901) [' . __LINE__ . '] - Parameter Authorization empty');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);

    if ('POST' === $request_method) {
        $areaDesc = filter_input(INPUT_POST, 'areaDesc');
        $cityId = filter_input(INPUT_POST, 'cityId');
        $areaStatus = filter_input(INPUT_POST, 'areaStatus');

        $params = array(
            'areaDesc'=>$areaDesc,
            'cityId'=>$cityId,
            'areaStatus'=>$areaStatus
        );

        $result = $fn_reference->add_area($params);
        $fn_general->updateVersion(7);
        $fn_general->save_audit('30', $jwt_data->userId, 'Area = ' . $areaDesc);

        $form_data['errmsg'] = $constant::SUC_AREA_ADD;
        $form_data['result'] = $result;
        $form_data['success'] = true;
    } else {
        throw new Exception('(ErrCode:2900) [' . __LINE__ . '] - Wrong Request Method');
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