<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/10/2019
 * Time: 9:44 PM
 */
require_once 'library/constant.php';
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';
require_once 'function/f_contractor.php';

$constant = new Class_constant();
$fn_general = new Class_general();
$fn_login = new Class_login();
$fn_contractor = new Class_contractor();
$api_name = 'api_contractor';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 3200 */
try {
    Class_db::getInstance()->db_connect();
    $request_method = $_SERVER['REQUEST_METHOD'];
    //$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);

    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:3201) [' . __LINE__ . '] - Parameter Authorization empty');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);

    if ('GET' === $request_method) {
        $contractorId = filter_input(INPUT_GET, 'contractorId');

        if (!is_null($contractorId)) {
            $result = $fn_contractor->get_contractor($contractorId);
        } else {
            $result = $fn_contractor->get_contractor_list();
        }

        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('POST' === $request_method) {
        $action = filter_input(INPUT_POST, 'action');
        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;

        if ($action === 'create_draft') {
            $result = $fn_contractor->create_draft($jwt_data->userId);
            $fn_general->updateVersion(9);
            $fn_general->save_audit('36', $jwt_data->userId, 'contractor_id = ' . $result);
        }
        else if ($action === 'add_contractor_site') {
            $contractorId = filter_input(INPUT_POST, 'contractorId');
            $siteId = filter_input(INPUT_POST, 'siteId');

            $result = $fn_contractor->add_contractor_site($contractorId, $siteId);
            $fn_general->updateVersion(9);
            $fn_general->save_audit('36', $jwt_data->userId, 'contractor_id = ' . $contractorId . ', site_id = ' . $siteId);
            $form_data['errmsg'] = $constant::SUC_CONTRACTOR_SITE_ADD;
        } else {
            throw new Exception('(ErrCode:3202) [' . __LINE__ . '] - Parameter action (' . $action . ') invalid');
        }

        Class_db::getInstance()->db_commit();
        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('PUT' === $request_method) {
        $contractorId = filter_input(INPUT_GET, 'contractorId');
        $put_data = file_get_contents("php://input");
        parse_str($put_data, $put_vars);
        $action = $put_vars['action'];

        if (empty($contractorId)) {
            throw new Exception('(ErrCode:3203) [' . __LINE__ . '] - Parameter contractorId empty');
        }

        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;

        if ($action === 'save_contractor' || $action === 'save_contractor2') {
            $fn_contractor->save_contractor($contractorId, $put_vars);
            $fn_general->updateVersion(9);
            $fn_general->save_audit('37', $jwt_data->userId, 'contractor_id = ' . $contractorId);
            if ($action === 'save_contractor') {
                $form_data['errmsg'] = $constant::SUC_CONTRACTOR_SAVE;
            }
        }
        else if ($action === 'submit_contractor') {
            $fn_contractor->submit_contractor($contractorId);
            $fn_general->updateVersion(9);
            $fn_general->save_audit('44', $jwt_data->userId, 'contractor_id = ' . $contractorId);
            $form_data['errmsg'] = $constant::SUC_CONTRACTOR_SUBMIT;
        } else {
            throw new Exception('(ErrCode:3103) [' . __LINE__ . '] - Parameter action (' . $action . ') invalid');
        }

        Class_db::getInstance()->db_commit();
        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('DELETE' === $request_method) {
        $contractorId = filter_input(INPUT_GET, 'contractorId');
        $contractorSiteId = filter_input(INPUT_GET, 'contractorSiteId');

        if (!is_null($contractorId)) {

        }
        else if (!is_null($contractorSiteId)) {
            $fn_contractor->delete_contractor_site($contractorSiteId);
            $fn_general->updateVersion(9);
            $fn_general->save_audit('39', $jwt_data->userId, 'contractor_site_id = ' . $contractorSiteId);
            $form_data['errmsg'] = $constant::SUC_CONTRACTOR_SITE_DELETE;
        }
        $form_data['result'] = $result;
        $form_data['success'] = true;
    } else {
        throw new Exception('(ErrCode:3200) [' . __LINE__ . '] - Wrong Request Method');
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