<?php
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';

$fn_general = new Class_general();
$fn_login = new Class_login();
$api_name = 'api_local_data';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');

/* Error code range - 2300 */ 
try {   
    Class_db::getInstance()->db_connect();
    $request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD'); 
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);
    
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:2301) [' . __LINE__ . '] - Parameter Authorization emtpy');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);
    
    if ('GET' === $request_method) { 
        if (!isset($headers['Name']) || empty($headers['Name'])) {
            throw new Exception('(ErrCode:2302) [' . __LINE__ . '] - Parameter Name emtpy');
        }
        $name = $headers['Name'];    
            
        $result = array();
        if ($name === 'status') {
            $arr_dataLocal = Class_db::getInstance()->db_select('ref_status');        
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('statusId'=>'', 'statusDesc'=>'', 'statusColor'=>'', 'statusAction'=>'');
                $row_result['statusId'] = $dataLocal['status_id'];
                $row_result['statusDesc'] = $dataLocal['status_desc'];
                $row_result['statusColor'] = $fn_general->clear_null($dataLocal['status_color']);
                $row_result['statusAction'] = $fn_general->clear_null($dataLocal['status_action']);
                array_push($result, $row_result);
            }  
        }        
        else if ($name === 'state') {
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
        else if ($name === 'jenisPerniagaan') {
            $arr_dataLocal = Class_db::getInstance()->db_select('ref_jenis_perniagaan');        
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('jenisPerniagaanId'=>'', 'jenisPerniagaanDesc'=>'', 'jenisPerniagaanStatus'=>'');
                $row_result['jenisPerniagaanId'] = $dataLocal['jenis_perniagaan_id'];
                $row_result['jenisPerniagaanDesc'] = $dataLocal['jenis_perniagaan_desc'];
                $row_result['jenisPerniagaanStatus'] = $dataLocal['jenis_perniagaan_status'];
                array_push($result, $row_result);
            }  
        }
        else if ($name === 'city') {
            $arr_dataLocal = Class_db::getInstance()->db_select('ref_city');        
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('cityId'=>'', 'citiDesc'=>'', 'stateId'=>'', 'cityStatus'=>'');
                $row_result['cityId'] = $dataLocal['city_id'];
                $row_result['cityDesc'] = $dataLocal['city_desc'];
                $row_result['stateId'] = $dataLocal['state_id'];
                $row_result['cityStatus'] = $dataLocal['city_status'];
                array_push($result, $row_result);
            }  
        } 
        else if ($name === 'location') {
            $arr_dataLocal = Class_db::getInstance()->db_select('sys_location');        
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('locationId'=>'', 'locationDesc'=>'', 'locationLongitude'=>'', 'locationLatitude'=>'', 'locationStatus'=>'');
                $row_result['locationId'] = $dataLocal['location_id'];
                $row_result['locationDesc'] = $dataLocal['location_desc'];
                $row_result['locationLongitude'] = $dataLocal['location_longitude'];
                $row_result['locationLatitude'] = $dataLocal['location_latitude'];
                $row_result['locationStatus'] = $dataLocal['location_status'];
                array_push($result, $row_result);
            }  
        }
        else if ($name === 'company') {
            $arr_dataLocal = Class_db::getInstance()->db_select('dt_sem_company');        
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('companyId'=>'', 'companyName'=>'', 'companyNoDaftar'=>'', 'jenisPerniagaanId'=>'', 'stateId'=>'', 'companyEmail'=>'', 'statusId'=>'');
                $row_result['companyId'] = $dataLocal['company_id'];
                $row_result['companyName'] = $fn_general->clear_null($dataLocal['company_name']);
                $row_result['companyNoDaftar'] = $fn_general->clear_null($dataLocal['company_ssm_no']);
                $row_result['jenisPerniagaanId'] = $fn_general->clear_null($dataLocal['jenis_perniagaan_id']);
                $row_result['stateId'] = $fn_general->clear_null($dataLocal['state_id']);
                $row_result['companyEmail'] = $fn_general->clear_null($dataLocal['company_email']);
                $row_result['statusId'] = $fn_general->clear_null($dataLocal['company_status']);
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
        $form_data['errmsg'] = 'Berlaku kesilapan pada sistem. Sila hubungi pihak Admin!';
    }
    $fn_general->log_error($api_name, __LINE__, $ex->getMessage());
}

echo json_encode($form_data);
