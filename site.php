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
$api_name = 'api_site';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 2800 */
try {
    Class_db::getInstance()->db_connect();
    $request_method = $_SERVER['REQUEST_METHOD'];
    //$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);

    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:2801) [' . __LINE__ . '] - Parameter Authorization empty');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);

    if ('GET' === $request_method) {
        $siteId = filter_input(INPUT_GET, 'siteId');
        $form_data['result'] = $fn_reference->get_site($siteId);
        $form_data['success'] = true;
    }
    else if ('POST' === $request_method) {
        $siteDesc = filter_input(INPUT_POST, 'siteDesc');
        $areaId = filter_input(INPUT_POST, 'areaId');
        $siteStatus = filter_input(INPUT_POST, 'siteStatus');

        $params = array(
            'siteDesc'=>$siteDesc,
            'areaId'=>$areaId,
            'siteStatus'=>$siteStatus
        );

        $result = $fn_reference->add_site($params);
        $fn_general->updateVersion(8);
        $fn_general->save_audit('25', $jwt_data->userId, 'Site = ' . $siteDesc);

        $form_data['errmsg'] = $constant::SUC_SITE_ADD;
        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('PUT' === $request_method) {
        $siteId = filter_input(INPUT_GET, 'siteId');
        $put_data = file_get_contents("php://input");
        parse_str($put_data, $put_vars);
        $action = $put_vars['action'];

        if ($action === 'update') {
            $fn_reference->update_site($siteId, $put_vars);
            $fn_general->updateVersion(8);
            $fn_general->save_audit('26', $jwt_data->userId, 'Site = ' . $put_vars['siteDesc']);
            $form_data['errmsg'] = $constant::SUC_SITE_EDIT;
        }
        else if ($action === 'deactivate') {
            $siteDesc = $fn_reference->deactivate_site($siteId);
            $fn_general->updateVersion(8);
            $fn_general->save_audit('27', $jwt_data->userId, 'Site = ' . $siteDesc);
            $form_data['errmsg'] = $constant::SUC_SITE_DEACTIVATE;
        }
        else if ($action === 'activate') {
            $siteDesc = $fn_reference->activate_site($siteId);
            $fn_general->updateVersion(8);
            $fn_general->save_audit('28', $jwt_data->userId, 'Site = ' . $siteDesc);
            $form_data['errmsg'] = $constant::SUC_SITE_ACTIVATE;
        } else {
            throw new Exception('(ErrCode:2802) [' . __LINE__ . '] - Parameter action invalid ('.$action.')');
        }

        $form_data['success'] = true;
    }
    else if ('DELETE' === $request_method) {
        $siteId = filter_input(INPUT_GET, 'siteId');

        $siteDesc = $fn_reference->delete_site($siteId);
        $fn_general->updateVersion(8);
        $fn_general->save_audit('29', $jwt_data->userId, 'Site = ' . $siteDesc);

        $form_data['errmsg'] = $constant::SUC_SITE_DELETE;
        $form_data['success'] = true;
    } else {
        throw new Exception('(ErrCode:2800) [' . __LINE__ . '] - Wrong Request Method');
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