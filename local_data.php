<?php
require_once 'library/constant.php';
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';
require_once 'function/f_reference.php';

$constant = new Class_constant();
$fn_general = new Class_general();
$fn_login = new Class_login();
$fn_reference = new Class_reference();
$api_name = 'api_local_data';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 2300 */ 
try {   
    Class_db::getInstance()->db_connect();
    $request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD'); 
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);
    
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:2301) [' . __LINE__ . '] - Parameter Authorization empty');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);
    
    if ('GET' === $request_method) { 
        if (!isset($headers['Name']) || empty($headers['Name'])) {
            throw new Exception('(ErrCode:2302) [' . __LINE__ . '] - Parameter Name empty');
        }
        $name = $headers['Name'];    
            
        $result = array();
        if ($name === 'icon_status') {
            $result = $fn_reference->get_status();
        }        
        else if ($name === 'icon_state') {
            $arr_dataLocal = Class_db::getInstance()->db_select('ref_state');        
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('stateId'=>'', 'stateDesc'=>'', 'countryId'=>'', 'stateStatus'=>'');
                $row_result['stateId'] = $dataLocal['state_id'];
                $row_result['stateDesc'] = $dataLocal['state_desc'];
                $row_result['countryId'] = $dataLocal['country_id'];
                $row_result['stateStatus'] = $dataLocal['state_status'];
                array_push($result, $row_result);
            }  
        }
        else if ($name === 'icon_city') {
            $arr_dataLocal = Class_db::getInstance()->db_select('ref_city');
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('cityId' => '', 'citiDesc' => '', 'stateId' => '', 'cityStatus' => '');
                $row_result['cityId'] = $dataLocal['city_id'];
                $row_result['cityDesc'] = $dataLocal['city_desc'];
                $row_result['stateId'] = $dataLocal['state_id'];
                $row_result['cityStatus'] = $dataLocal['city_status'];
                array_push($result, $row_result);
            }
        }
        else if ($name === 'icon_problemtype') {
            $result = $fn_reference->get_problem_type();
        }
        else if ($name === 'icon_worktype') {
            $arr_dataLocal = Class_db::getInstance()->db_select('icn_worktype');
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('worktypeId'=>'', 'worktypeDesc'=>'', 'worktypeStatus'=>'');
                $row_result['worktypeId'] = $dataLocal['worktype_id'];
                $row_result['worktypeDesc'] = $dataLocal['worktype_desc'];
                $row_result['worktypeStatus'] = $dataLocal['worktype_status'];
                array_push($result, $row_result);
            }
        }
        else if ($name === 'icon_workcategory') {
            $arr_dataLocal = Class_db::getInstance()->db_select('icn_workcategory');
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('workcategoryId'=>'', 'workcategoryDesc'=>'', 'worktypeId'=>'', 'workcategoryStatus'=>'');
                $row_result['workcategoryId'] = $dataLocal['workcategory_id'];
                $row_result['workcategoryDesc'] = $dataLocal['workcategory_desc'];
                $row_result['worktypeId'] = $dataLocal['worktype_id'];
                $row_result['workcategoryStatus'] = $dataLocal['workcategory_status'];
                array_push($result, $row_result);
            }
        } else {
            throw new Exception('(ErrCode:2303) [' . __LINE__ . '] - Parameter name invalid ('.$name.')');
        }
                
        $form_data['result'] = $result;
        $form_data['success'] = true; 
        //$fn_general->log_debug($api_name, __LINE__, 'Result = '.print_r($result, true));
    } else {
        throw new Exception('(ErrCode:2300) [' . __LINE__ . '] - Wrong Request Method');   
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
