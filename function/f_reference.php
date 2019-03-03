<?php
/**
 * Created by PhpStorm.
 * User: Zaid
 * Date: 2/26/2019
 * Time: 11:08 PM
 */
require_once 'library/constant.php';
require_once 'function/f_general.php';

/* Error code range - 0500 */
class Class_reference {

    private $fn_general;

    function __construct()
    {
        $this->fn_general = new Class_general();
    }

    private function get_exception($codes, $function, $line, $msg)
    {
        if ($msg != '') {
            $pos = strpos($msg, '-');
            if ($pos !== false) {
                $msg = substr($msg, $pos + 2);
            }
            return "(ErrCode:" . $codes . ") [" . __CLASS__ . ":" . $function . ":" . $line . "] - " . $msg;
        } else {
            return "(ErrCode:" . $codes . ") [" . __CLASS__ . ":" . $function . ":" . $line . "]";
        }
    }

    /**
     * @param $property
     * @return mixed
     * @throws Exception
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new Exception($this->get_exception('0001', __FUNCTION__, __LINE__, 'Get Property not exist [' . $property . ']'));
        }
    }

    /**
     * @param $property
     * @param $value
     * @throws Exception
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            throw new Exception($this->get_exception('0002', __FUNCTION__, __LINE__, 'Get Property not exist [' . $property . ']'));
        }
    }

    /**
     * @param $property
     * @return bool
     * @throws Exception
     */
    public function __isset($property)
    {
        if (property_exists($this, $property)) {
            return isset($this->$property);
        } else {
            throw new Exception($this->get_exception('0003', __FUNCTION__, __LINE__, 'Get Property not exist [' . $property . ']'));
        }
    }

    /**
     * @param $property
     * @throws Exception
     */
    public function __unset($property)
    {
        if (property_exists($this, $property)) {
            unset($this->$property);
        } else {
            throw new Exception($this->get_exception('0004', __FUNCTION__, __LINE__, 'Get Property not exist [' . $property . ']'));
        }
    }

    /**
     * @param string $problemtypeId
     * @return array
     * @throws Exception
     */
    public function get_problem_type ($problemtypeId=null) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_problem_type()');

            $result = array();
            if (is_null($problemtypeId)) {
                $arr_dataLocal = Class_db::getInstance()->db_select('icn_problemtype');
                foreach ($arr_dataLocal as $dataLocal) {
                    $row_result['problemtypeId'] = $dataLocal['problemtype_id'];
                    $row_result['problemtypeDesc'] = $dataLocal['problemtype_desc'];
                    $row_result['problemtypeStatus'] = $dataLocal['problemtype_status'];
                    array_push($result, $row_result);
                }
            } else {
                $dataLocal = Class_db::getInstance()->db_select_single('icn_problemtype', array('problemtype_id'=>$problemtypeId), null, 1);
                $result['problemtypeId'] = $dataLocal['problemtype_id'];
                $result['problemtypeDesc'] = $dataLocal['problemtype_desc'];
                $result['problemtypeStatus'] = $dataLocal['problemtype_status'];
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function add_problem_type ($params) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering add_problem_type()');

            if (empty($params)) {
                throw new Exception('(ErrCode:0502) [' . __LINE__ . '] - Array params empty');
            }
            if (!array_key_exists('problemtypeDesc', $params) || empty($params['problemtypeDesc'])) {
                throw new Exception('(ErrCode:0503) [' . __LINE__ . '] - Parameter problemtypeDesc empty');
            }
            if (!array_key_exists('problemtypeStatus', $params) || empty($params['problemtypeStatus'])) {
                throw new Exception('(ErrCode:0504) [' . __LINE__ . '] - Parameter problemtypeStatus empty');
            }

            $problemtypeDesc = $params['problemtypeDesc'];
            $problemtypeStatus = $params['problemtypeStatus'];

            if (Class_db::getInstance()->db_count('icn_problemtype', array('problemtype_desc'=>$problemtypeDesc)) > 0) {
                throw new Exception('(ErrCode:0505) [' . __LINE__ . '] - '.$constant::ERR_PROBLEM_TYPE_SIMILAR, 31);
            }

            return Class_db::getInstance()->db_insert('icn_problemtype', array('problemtype_desc'=>$problemtypeDesc, 'problemtype_status'=>$problemtypeStatus));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $problemtypeId
     * @param $put_vars
     * @throws Exception
     */
    public function update_problem_type ($problemtypeId, $put_vars) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering update_problem_type()');

            if (empty($problemtypeId)) {
                throw new Exception('(ErrCode:0506) [' . __LINE__ . '] - Parameter problemtypeId empty');
            }
            if (empty($put_vars)) {
                throw new Exception('(ErrCode:0507) [' . __LINE__ . '] - Array put_vars empty');
            }

            if (!isset($put_vars['problemtypeDesc']) || empty($put_vars['problemtypeDesc'])) {
                throw new Exception('(ErrCode:0503) [' . __LINE__ . '] - Parameter problemtypeDesc empty');
            }
            if (!isset($put_vars['problemtypeStatus']) || empty($put_vars['problemtypeStatus'])) {
                throw new Exception('(ErrCode:0504) [' . __LINE__ . '] - Parameter problemtypeStatus empty');
            }

            $problemtypeDesc = $put_vars['problemtypeDesc'];
            $problemtypeStatus = $put_vars['problemtypeStatus'];

            if (Class_db::getInstance()->db_count('icn_problemtype', array('problemtype_desc'=>$problemtypeDesc, 'problemtype_id'=>'<>'.$problemtypeId)) > 0) {
                throw new Exception('(ErrCode:0505) [' . __LINE__ . '] - '.$constant::ERR_PROBLEM_TYPE_SIMILAR, 31);
            }

            Class_db::getInstance()->db_update('icn_problemtype', array('problemtype_desc'=>$problemtypeDesc, 'problemtype_status'=>$problemtypeStatus), array('problemtype_id'=>$problemtypeId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $problemtypeId
     * @return mixed
     * @throws Exception
     */
    public function deactivate_problem_type ($problemtypeId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering deactivate_problem_type()');

            if (empty($problemtypeId)) {
                throw new Exception('(ErrCode:0506) [' . __LINE__ . '] - Parameter problemtypeId empty');
            }
            if (Class_db::getInstance()->db_count('icn_problemtype', array('problemtype_id'=>$problemtypeId, 'problemtype_status'=>'2')) > 0) {
                throw new Exception('(ErrCode:0508) [' . __LINE__ . '] - '.$constant::ERR_PROBLEM_TYPE_DEACTIVATE, 31);
            }

            Class_db::getInstance()->db_update('icn_problemtype', array('problemtype_status'=>'2'), array('problemtype_id'=>$problemtypeId));
            return Class_db::getInstance()->db_select_col('icn_problemtype', array('problemtype_id'=>$problemtypeId), 'problemtype_desc', null, 1);
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $problemtypeId
     * @return mixed
     * @throws Exception
     */
    public function activate_problem_type ($problemtypeId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering activate_problem_type()');

            if (empty($problemtypeId)) {
                throw new Exception('(ErrCode:0506) [' . __LINE__ . '] - Parameter problemtypeId empty');
            }
            if (Class_db::getInstance()->db_count('icn_problemtype', array('problemtype_id'=>$problemtypeId, 'problemtype_status'=>'1')) > 0) {
                throw new Exception('(ErrCode:0509) [' . __LINE__ . '] - '.$constant::ERR_PROBLEM_TYPE_ACTIVATE, 31);
            }

            Class_db::getInstance()->db_update('icn_problemtype', array('problemtype_status'=>'1'), array('problemtype_id'=>$problemtypeId));
            return Class_db::getInstance()->db_select_col('icn_problemtype', array('problemtype_id'=>$problemtypeId), 'problemtype_desc', null, 1);
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $problemtypeId
     * @return mixed
     * @throws Exception
     */
    public function delete_problem_type ($problemtypeId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering delete_problem_type()');

            if (empty($problemtypeId)) {
                throw new Exception('(ErrCode:0506) [' . __LINE__ . '] - Parameter problemtypeId empty');
            }
            if (Class_db::getInstance()->db_count('icn_problemtype', array('problemtype_id'=>$problemtypeId)) == 0) {
                throw new Exception('(ErrCode:0510) [' . __LINE__ . '] - Problem Type data not exist');
            }
            if (Class_db::getInstance()->db_count('icn_ticket', array('problemtype_id'=>$problemtypeId)) > 0 ||
                    Class_db::getInstance()->db_count('icn_workorder', array('problemtype_id'=>$problemtypeId)) > 0) {
                throw new Exception('(ErrCode:0511) [' . __LINE__ . '] - '.$constant::ERR_PROBLEM_TYPE_DELETE, 31);
            }

            $problemtypeDesc = Class_db::getInstance()->db_select_col('icn_problemtype', array('problemtype_id'=>$problemtypeId), 'problemtype_desc', null, 1);
            Class_db::getInstance()->db_delete('icn_problemtype', array('problemtype_id'=>$problemtypeId));

            return $problemtypeDesc;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function get_status () {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_status()');

            $result = array();
            $arr_dataLocal = Class_db::getInstance()->db_select('ref_status');
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result['statusId'] = $dataLocal['status_id'];
                $row_result['statusDesc'] = $dataLocal['status_desc'];
                $row_result['statusColor'] = $this->fn_general->clear_null($dataLocal['status_color']);
                $row_result['statusColorCode'] = $this->fn_general->clear_null($dataLocal['status_color_code']);
                $row_result['statusAction'] = $this->fn_general->clear_null($dataLocal['status_action']);
                array_push($result, $row_result);
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param null $worktypeId
     * @return array
     * @throws Exception
     */
    public function get_work_type ($worktypeId=null) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_work_type()');

            $result = array();
            if (is_null($worktypeId)) {
                $arr_dataLocal = Class_db::getInstance()->db_select('icn_worktype');
                foreach ($arr_dataLocal as $dataLocal) {
                    $row_result['worktypeId'] = $dataLocal['worktype_id'];
                    $row_result['worktypeDesc'] = $dataLocal['worktype_desc'];
                    $row_result['worktypeStatus'] = $dataLocal['worktype_status'];
                    array_push($result, $row_result);
                }
            } else {
                $dataLocal = Class_db::getInstance()->db_select_single('icn_worktype', array('worktype_id'=>$worktypeId), null, 1);
                $result['worktypeId'] = $dataLocal['worktype_id'];
                $result['worktypeDesc'] = $dataLocal['worktype_desc'];
                $result['worktypeStatus'] = $dataLocal['worktype_status'];
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function add_work_type ($params) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering add_work_type()');

            if (empty($params)) {
                throw new Exception('(ErrCode:0502) [' . __LINE__ . '] - Array params empty');
            }
            if (!array_key_exists('worktypeDesc', $params) || empty($params['worktypeDesc'])) {
                throw new Exception('(ErrCode:0512) [' . __LINE__ . '] - Parameter worktypeDesc empty');
            }
            if (!array_key_exists('worktypeStatus', $params) || empty($params['worktypeStatus'])) {
                throw new Exception('(ErrCode:0513) [' . __LINE__ . '] - Parameter worktypeStatus empty');
            }

            $worktypeDesc = $params['worktypeDesc'];
            $worktypeStatus = $params['worktypeStatus'];

            if (Class_db::getInstance()->db_count('icn_worktype', array('worktype_desc'=>$worktypeDesc)) > 0) {
                throw new Exception('(ErrCode:0514) [' . __LINE__ . '] - '.$constant::ERR_WORK_TYPE_SIMILAR, 31);
            }

            return Class_db::getInstance()->db_insert('icn_worktype', array('worktype_desc'=>$worktypeDesc, 'worktype_status'=>$worktypeStatus));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $worktypeId
     * @param $put_vars
     * @throws Exception
     */
    public function update_work_type ($worktypeId, $put_vars) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering update_work_type()');

            if (empty($worktypeId)) {
                throw new Exception('(ErrCode:0515) [' . __LINE__ . '] - Parameter worktypeId empty');
            }
            if (empty($put_vars)) {
                throw new Exception('(ErrCode:0507) [' . __LINE__ . '] - Array put_vars empty');
            }

            if (!isset($put_vars['worktypeDesc']) || empty($put_vars['worktypeDesc'])) {
                throw new Exception('(ErrCode:0512) [' . __LINE__ . '] - Parameter worktypeDesc empty');
            }
            if (!isset($put_vars['worktypeStatus']) || empty($put_vars['worktypeStatus'])) {
                throw new Exception('(ErrCode:0513) [' . __LINE__ . '] - Parameter worktypeStatus empty');
            }

            $worktypeDesc = $put_vars['worktypeDesc'];
            $worktypeStatus = $put_vars['worktypeStatus'];

            if (Class_db::getInstance()->db_count('icn_worktype', array('worktype_desc'=>$worktypeDesc, 'worktype_id'=>'<>'.$worktypeId)) > 0) {
                throw new Exception('(ErrCode:0514) [' . __LINE__ . '] - '.$constant::ERR_WORK_TYPE_SIMILAR, 31);
            }

            Class_db::getInstance()->db_update('icn_worktype', array('worktype_desc'=>$worktypeDesc, 'worktype_status'=>$worktypeStatus), array('worktype_id'=>$worktypeId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $worktypeId
     * @return mixed
     * @throws Exception
     */
    public function deactivate_work_type ($worktypeId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering deactivate_work_type()');

            if (empty($worktypeId)) {
                throw new Exception('(ErrCode:0515) [' . __LINE__ . '] - Parameter worktypeId empty');
            }
            if (Class_db::getInstance()->db_count('icn_worktype', array('worktype_id'=>$worktypeId, 'worktype_status'=>'2')) > 0) {
                throw new Exception('(ErrCode:0516) [' . __LINE__ . '] - '.$constant::ERR_WORK_TYPE_DEACTIVATE, 31);
            }

            Class_db::getInstance()->db_update('icn_worktype', array('worktype_status'=>'2'), array('worktype_id'=>$worktypeId));
            return Class_db::getInstance()->db_select_col('icn_worktype', array('worktype_id'=>$worktypeId), 'worktype_desc', null, 1);
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $worktypeId
     * @return mixed
     * @throws Exception
     */
    public function activate_work_type ($worktypeId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering activate_work_type()');

            if (empty($worktypeId)) {
                throw new Exception('(ErrCode:0515) [' . __LINE__ . '] - Parameter worktypeId empty');
            }
            if (Class_db::getInstance()->db_count('icn_worktype', array('worktype_id'=>$worktypeId, 'worktype_status'=>'1')) > 0) {
                throw new Exception('(ErrCode:0517) [' . __LINE__ . '] - '.$constant::ERR_WORK_TYPE_ACTIVATE, 31);
            }

            Class_db::getInstance()->db_update('icn_worktype', array('worktype_status'=>'1'), array('worktype_id'=>$worktypeId));
            return Class_db::getInstance()->db_select_col('icn_worktype', array('worktype_id'=>$worktypeId), 'worktype_desc', null, 1);
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $worktypeId
     * @return mixed
     * @throws Exception
     */
    public function delete_work_type ($worktypeId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering delete_work_type()');

            if (empty($worktypeId)) {
                throw new Exception('(ErrCode:0515) [' . __LINE__ . '] - Parameter worktypeId empty');
            }
            if (Class_db::getInstance()->db_count('icn_worktype', array('worktype_id'=>$worktypeId)) == 0) {
                throw new Exception('(ErrCode:0518) [' . __LINE__ . '] - Problem Type data not exist');
            }
            if (Class_db::getInstance()->db_count('vw_ticket_worktype', array('icn_workcategory.worktype_id'=>$worktypeId)) > 0 ||
                Class_db::getInstance()->db_count('vw_workorder_worktype', array('icn_workcategory.worktype_id'=>$worktypeId)) > 0) {
                throw new Exception('(ErrCode:0519) [' . __LINE__ . '] - '.$constant::ERR_WORK_TYPE_DELETE, 31);
            }

            $worktypeDesc = Class_db::getInstance()->db_select_col('icn_worktype', array('worktype_id'=>$worktypeId), 'worktype_desc', null, 1);
            Class_db::getInstance()->db_delete('icn_worktype', array('worktype_id'=>$worktypeId));

            return $worktypeDesc;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param null $workcategoryId
     * @return array
     * @throws Exception
     */
    public function get_work_category ($workcategoryId=null) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_work_category()');

            $result = array();
            if (is_null($workcategoryId)) {
                $arr_dataLocal = Class_db::getInstance()->db_select('icn_workcategory');
                foreach ($arr_dataLocal as $dataLocal) {
                    $row_result['workcategoryId'] = $dataLocal['workcategory_id'];
                    $row_result['workcategoryDesc'] = $dataLocal['workcategory_desc'];
                    $row_result['worktypeId'] = $dataLocal['worktype_id'];
                    $row_result['workcategoryStatus'] = $dataLocal['workcategory_status'];
                    array_push($result, $row_result);
                }
            } else {
                $dataLocal = Class_db::getInstance()->db_select_single('icn_workcategory', array('workcategory_id'=>$workcategoryId), null, 1);
                $result['workcategoryId'] = $dataLocal['workcategory_id'];
                $result['workcategoryDesc'] = $dataLocal['workcategory_desc'];
                $result['worktypeId'] = $dataLocal['worktype_id'];
                $result['workcategoryStatus'] = $dataLocal['workcategory_status'];
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function add_work_category ($params) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering add_work_category()');

            if (empty($params)) {
                throw new Exception('(ErrCode:0502) [' . __LINE__ . '] - Array params empty');
            }
            if (!array_key_exists('workcategoryDesc', $params) || empty($params['workcategoryDesc'])) {
                throw new Exception('(ErrCode:0520) [' . __LINE__ . '] - Parameter workcategoryDesc empty');
            }
            if (!array_key_exists('worktypeId', $params) || empty($params['worktypeId'])) {
                throw new Exception('(ErrCode:0515) [' . __LINE__ . '] - Parameter worktypeId empty');
            }
            if (!array_key_exists('workcategoryStatus', $params) || empty($params['workcategoryStatus'])) {
                throw new Exception('(ErrCode:0521) [' . __LINE__ . '] - Parameter workcategoryStatus empty');
            }

            $workcategoryDesc = $params['workcategoryDesc'];
            $worktypeId = $params['worktypeId'];
            $workcategoryStatus = $params['workcategoryStatus'];

            if (Class_db::getInstance()->db_count('icn_workcategory', array('workcategory_desc'=>$workcategoryDesc)) > 0) {
                throw new Exception('(ErrCode:0522) [' . __LINE__ . '] - '.$constant::ERR_WORK_CATEGORY_SIMILAR, 31);
            }

            return Class_db::getInstance()->db_insert('icn_workcategory', array('workcategory_desc'=>$workcategoryDesc, 'worktype_id'=>$worktypeId, 'workcategory_status'=>$workcategoryStatus));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $workcategoryId
     * @param $put_vars
     * @throws Exception
     */
    public function update_work_category ($workcategoryId, $put_vars) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering update_work_category()');

            if (empty($workcategoryId)) {
                throw new Exception('(ErrCode:0523) [' . __LINE__ . '] - Parameter workcategoryId empty');
            }
            if (empty($put_vars)) {
                throw new Exception('(ErrCode:0507) [' . __LINE__ . '] - Array put_vars empty');
            }

            if (!isset($put_vars['workcategoryDesc']) || empty($put_vars['workcategoryDesc'])) {
                throw new Exception('(ErrCode:0520) [' . __LINE__ . '] - Parameter workcategoryDesc empty');
            }
            if (!isset($put_vars['worktypeId']) || empty($put_vars['worktypeId'])) {
                throw new Exception('(ErrCode:0515) [' . __LINE__ . '] - Parameter worktypeId empty');
            }
            if (!isset($put_vars['workcategoryStatus']) || empty($put_vars['workcategoryStatus'])) {
                throw new Exception('(ErrCode:0521) [' . __LINE__ . '] - Parameter workcategoryStatus empty');
            }

            $workcategoryDesc = $put_vars['workcategoryDesc'];
            $worktypeId = $put_vars['worktypeId'];
            $workcategoryStatus = $put_vars['workcategoryStatus'];

            if (Class_db::getInstance()->db_count('icn_workcategory', array('workcategory_desc'=>$workcategoryDesc, 'worktype_id'=>$worktypeId, 'workcategory_id'=>'<>'.$workcategoryId)) > 0) {
                throw new Exception('(ErrCode:0522) [' . __LINE__ . '] - '.$constant::ERR_WORK_CATEGORY_SIMILAR, 31);
            }

            Class_db::getInstance()->db_update('icn_workcategory', array('workcategory_desc'=>$workcategoryDesc, 'worktype_id'=>$worktypeId, 'workcategory_status'=>$workcategoryStatus), array('workcategory_id'=>$workcategoryId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $workcategoryId
     * @return mixed
     * @throws Exception
     */
    public function deactivate_work_category ($workcategoryId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering deactivate_work_category()');

            if (empty($workcategoryId)) {
                throw new Exception('(ErrCode:0523) [' . __LINE__ . '] - Parameter workcategoryId empty');
            }
            if (Class_db::getInstance()->db_count('icn_workcategory', array('workcategory_id'=>$workcategoryId, 'workcategory_status'=>'2')) > 0) {
                throw new Exception('(ErrCode:0524) [' . __LINE__ . '] - '.$constant::ERR_WORK_CATEGORY_DEACTIVATE, 31);
            }

            Class_db::getInstance()->db_update('icn_workcategory', array('workcategory_status'=>'2'), array('workcategory_id'=>$workcategoryId));
            return Class_db::getInstance()->db_select_col('icn_workcategory', array('workcategory_id'=>$workcategoryId), 'workcategory_desc', null, 1);
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $workcategoryId
     * @return mixed
     * @throws Exception
     */
    public function activate_work_category ($workcategoryId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering activate_work_category()');

            if (empty($workcategoryId)) {
                throw new Exception('(ErrCode:0523) [' . __LINE__ . '] - Parameter workcategoryId empty');
            }
            if (Class_db::getInstance()->db_count('icn_workcategory', array('workcategory_id'=>$workcategoryId, 'workcategory_status'=>'1')) > 0) {
                throw new Exception('(ErrCode:0525) [' . __LINE__ . '] - '.$constant::ERR_WORK_CATEGORY_ACTIVATE, 31);
            }

            Class_db::getInstance()->db_update('icn_workcategory', array('workcategory_status'=>'1'), array('workcategory_id'=>$workcategoryId));
            return Class_db::getInstance()->db_select_col('icn_workcategory', array('workcategory_id'=>$workcategoryId), 'workcategory_desc', null, 1);
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $workcategoryId
     * @return mixed
     * @throws Exception
     */
    public function delete_work_category ($workcategoryId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering delete_work_category()');

            if (empty($workcategoryId)) {
                throw new Exception('(ErrCode:0523) [' . __LINE__ . '] - Parameter workcategoryId empty');
            }
            if (Class_db::getInstance()->db_count('icn_workcategory', array('workcategory_id'=>$workcategoryId)) == 0) {
                throw new Exception('(ErrCode:0526) [' . __LINE__ . '] - Problem Type data not exist');
            }
            if (Class_db::getInstance()->db_count('icn_ticket', array('workcategory_id'=>$workcategoryId)) > 0 ||
                Class_db::getInstance()->db_count('icn_workorder', array('workcategory_id'=>$workcategoryId)) > 0) {
                throw new Exception('(ErrCode:0527) [' . __LINE__ . '] - '.$constant::ERR_WORK_CATEGORY_DELETE, 31);
            }

            $workcategoryDesc = Class_db::getInstance()->db_select_col('icn_workcategory', array('workcategory_id'=>$workcategoryId), 'workcategory_desc', null, 1);
            Class_db::getInstance()->db_delete('icn_workcategory', array('workcategory_id'=>$workcategoryId));

            return $workcategoryDesc;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}