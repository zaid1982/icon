<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/10/2019
 * Time: 12:09 AM
 */
require_once 'library/constant.php';
require_once 'function/f_general.php';

/* Error code range - 0700 */
class Class_workorder {

    private $fn_general;

    function __construct()
    {
        $this->fn_general = new Class_general();
    }

    private function get_exception($codes, $function, $line, $msg) {
        if ($msg != '') {
            $pos = strpos($msg,'-');
            if ($pos !== false) {
                $msg = substr($msg, $pos+2);
            }
            return "(ErrCode:".$codes.") [".__CLASS__.":".$function.":".$line."] - ".$msg;
        } else {
            return "(ErrCode:".$codes.") [".__CLASS__.":".$function.":".$line."]";
        }
    }

    /**
     * @param $property
     * @return mixed
     * @throws Exception
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new Exception($this->get_exception('0001', __FUNCTION__, __LINE__, 'Get Property not exist ['.$property.']'));
        }
    }

    /**
     * @param $property
     * @param $value
     * @throws Exception
     */
    public function __set($property, $value ) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            throw new Exception($this->get_exception('0002', __FUNCTION__, __LINE__, 'Get Property not exist ['.$property.']'));
        }
    }

    /**
     * @param $property
     * @return bool
     * @throws Exception
     */
    public function __isset($property ) {
        if (property_exists($this, $property)) {
            return isset($this->$property);
        } else {
            throw new Exception($this->get_exception('0003', __FUNCTION__, __LINE__, 'Get Property not exist ['.$property.']'));
        }
    }

    /**
     * @param $property
     * @throws Exception
     */
    public function __unset($property ) {
        if (property_exists($this, $property)) {
            unset($this->$property);
        } else {
            throw new Exception($this->get_exception('0004', __FUNCTION__, __LINE__, 'Get Property not exist ['.$property.']'));
        }
    }

    /**
     * @param $ticketId
     * @param $userId
     * @return mixed
     * @throws Exception
     */
    public function create_new_workorder ($ticketId, $userId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering create_new_workorder()');

            if (empty($userId)) {
                throw new Exception('(ErrCode:0702) [' . __LINE__ . '] - Parameter userId empty');
            }
            if (empty($ticketId)) {
                throw new Exception('(ErrCode:0703) [' . __LINE__ . '] - Parameter ticketId empty');
            }

            $ticket = Class_db::getInstance()->db_select_single('icn_ticket', array('ticket_id'=>$ticketId), null, 1);
            $problemtypeId = $ticket['problemtype_id'];
            $workcategoryId = $ticket['workcategory_id'];
            $timeComplaint = $ticket['ticket_time_submit'];
            $worktypeId = Class_db::getInstance()->db_select_col('icn_workcategory', array('workcategory_id'=>$workcategoryId), 'worktype_id', null, 1);

            if (Class_db::getInstance()->db_count('icn_workorder', array('ticket_id'=>$ticketId)) > 0) {
                throw new Exception('(ErrCode:0704) [' . __LINE__ . '] - '.$constant::ERR_WORKORDER_SIMILAR, 31);
            }

            $workorderId = Class_db::getInstance()->db_insert('icn_workorder', array('ticket_id'=>$ticketId, 'problemtype_id'=>$problemtypeId, 'workcategory_id'=>$workcategoryId, 'workorder_time_complaint'=>$timeComplaint,
                'worktype_id'=>$worktypeId, 'workorder_created_by'=>$userId, 'workorder_status'=>'5'));
            Class_db::getInstance()->db_update('icn_workorder', array('workorder_no'=>'draft-'.$workorderId), array('workorder_id'=>$workorderId));

            return $workorderId;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $workorderId
     * @return mixed
     * @throws Exception
     */
    public function get_workorder ($workorderId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_workorder()');

            if (empty($workorderId)) {
                throw new Exception('(ErrCode:0705) [' . __LINE__ . '] - Parameter workorderId empty');
            }

            $workorder = Class_db::getInstance()->db_select_single('vw_workorder', array('workorder_id'=>$workorderId), null, 1);
            $result['workorderId'] = $workorder['workorder_id'];
            $result['workorderNo'] = $this->fn_general->clear_null($workorder['workorder_no']);
            $result['problemtypeId'] = $workorder['problemtype_id'];
            $result['worktypeId'] = $this->fn_general->clear_null($workorder['worktype_id']);
            $result['workcategoryId'] = $workorder['workcategory_id'];
            $result['siteId'] = $this->fn_general->clear_null($workorder['site_id']);
            $result['areaId'] = $this->fn_general->clear_null($workorder['area_id']);
            $result['cityId'] = $this->fn_general->clear_null($workorder['city_id']);
            $result['workorderBlock'] = $this->fn_general->clear_null($workorder['workorder_block']);
            $result['workorderLevel'] = $this->fn_general->clear_null($workorder['workorder_level']);
            $result['workorderUnit'] = $this->fn_general->clear_null($workorder['workorder_unit']);
            $result['contractorId'] = $this->fn_general->clear_null($workorder['contractor_id']);
            $result['workorderSiteType'] = $this->fn_general->clear_null($workorder['workorder_site_type']);
            $result['workorderTimeComplaint'] = str_replace('-', '/', $workorder['workorder_time_complaint']);
            $result['workorderDesc'] = $this->fn_general->clear_null($workorder['workorder_desc']);
            $result['workorderLocationDesc'] = $this->fn_general->clear_null($workorder['workorder_location_desc']);
            $result['workorderStatus'] = $workorder['workorder_status'];
            $result['requesterName'] = $workorder['requester_name'];
            $result['requesterPhone'] = $workorder['requester_phone'];

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $workorderId
     * @param $put_vars
     * @throws Exception
     */
    public function save_workorder ($workorderId, $put_vars) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering save_workorder()');

            if (empty($workorderId)) {
                throw new Exception('(ErrCode:0705) [' . __LINE__ . '] - Parameter workorderId empty');
            }
            if (empty($put_vars)) {
                throw new Exception('(ErrCode:0706) [' . __LINE__ . '] - Array put_vars empty');
            }

            if (!isset($put_vars['siteId'])) {
                throw new Exception('(ErrCode:0707) [' . __LINE__ . '] - Parameter siteId not exist');
            }
            if (!isset($put_vars['areaId'])) {
                throw new Exception('(ErrCode:0708) [' . __LINE__ . '] - Parameter areaId not exist');
            }
            if (!isset($put_vars['cityId'])) {
                throw new Exception('(ErrCode:0709) [' . __LINE__ . '] - Parameter cityId not exist');
            }
            if (!isset($put_vars['contractorId'])) {
                throw new Exception('(ErrCode:0710) [' . __LINE__ . '] - Parameter contractorId not exist');
            }
            if (!isset($put_vars['worktypeId'])) {
                throw new Exception('(ErrCode:0711) [' . __LINE__ . '] - Parameter worktypeId not exist');
            }
            if (!isset($put_vars['workorderSiteType'])) {
                throw new Exception('(ErrCode:0712) [' . __LINE__ . '] - Parameter workorderSiteType not exist');
            }
            if (!isset($put_vars['workcategoryId'])) {
                throw new Exception('(ErrCode:0713) [' . __LINE__ . '] - Parameter workcategoryId not exist');
            }
            if (!isset($put_vars['workorderDesc'])) {
                throw new Exception('(ErrCode:0714) [' . __LINE__ . '] - Parameter workorderDesc not exist');
            }
            if (!isset($put_vars['workorderBlock'])) {
                throw new Exception('(ErrCode:0715) [' . __LINE__ . '] - Parameter workorderBlock not exist');
            }
            if (!isset($put_vars['workorderLevel'])) {
                throw new Exception('(ErrCode:0716) [' . __LINE__ . '] - Parameter workorderLevel not exist');
            }
            if (!isset($put_vars['workorderUnit'])) {
                throw new Exception('(ErrCode:0717) [' . __LINE__ . '] - Parameter workorderUnit not exist');
            }
            if (!isset($put_vars['workorderLocationDesc'])) {
                throw new Exception('(ErrCode:0718) [' . __LINE__ . '] - Parameter workorderLocationDesc not exist');
            }
            if (Class_db::getInstance()->db_count('icn_workorder', array('workorder_id'=>$workorderId, 'workorder_status'=>'5')) == 0) {
                throw new Exception('(ErrCode:0719) [' . __LINE__ . '] - '.$constant::ERR_WORKORDER_SUBMITTED, 31);
            }

            $arrUpdate = array(
                'site_id' => $put_vars['siteId'],
                'area_id' => $put_vars['areaId'],
                'city_id' => $put_vars['cityId'],
                'contractor_id' => $put_vars['contractorId'],
                'worktype_id' => $put_vars['worktypeId'],
                'workorder_site_type' => $put_vars['workorderSiteType'],
                'workcategory_id' => $put_vars['workcategoryId'],
                'workorder_desc' => $put_vars['workorderDesc'],
                'workorder_block' => $put_vars['workorderBlock'],
                'workorder_level' => $put_vars['workorderLevel'],
                'workorder_unit' => $put_vars['workorderUnit'],
                'workorder_location_desc' => $put_vars['workorderLocationDesc']
            );
            Class_db::getInstance()->db_update('icn_workorder', $arrUpdate, array('workorder_id'=>$workorderId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $workorderId
     * @param $statusCheck
     * @param $checkpointCheck
     * @return mixed
     * @throws Exception
     */
    public function get_task_info ($workorderId, $statusCheck, $checkpointCheck) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_task_id()');

            if (empty($workorderId)) {
                throw new Exception('(ErrCode:0705) [' . __LINE__ . '] - Parameter workorderId empty');
            }
            if (empty($statusCheck)) {
                throw new Exception('(ErrCode:0720) [' . __LINE__ . '] - Parameter statusCheck empty');
            }
            if (empty($checkpointCheck)) {
                throw new Exception('(ErrCode:0721) [' . __LINE__ . '] - Parameter checkpointCheck empty');
            }

            $workorder = Class_db::getInstance()->db_select_single('icn_workorder', array('workorder_id'=>$workorderId, 'workorder_status'=>'('.$statusCheck.')'), null, 1);
            $transactionId = Class_db::getInstance()->db_select_col('icn_ticket', array('ticket_id'=>$workorder['ticket_id']), 'transaction_id', null, 1);
            $taskId = Class_db::getInstance()->db_select_col('wfl_task', array('transaction_id'=>$transactionId, 'checkpoint_id'=>$checkpointCheck, 'task_current'=>'1'), 'task_id', null, 1);
            $groupId = Class_db::getInstance()->db_select_col('icn_contractor', array('contractor_id'=>$workorder['contractor_id']), 'group_id', null, 1);
            return array('taskId'=>$taskId, 'groupId'=>$groupId);
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $workorderId
     * @param $taskId
     * @throws Exception
     */
    public function submit_workorder ($workorderId, $taskId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering submit_workorder()');

            if (empty($workorderId)) {
                throw new Exception('(ErrCode:0705) [' . __LINE__ . '] - Parameter workorderId empty');
            }
            if (empty($taskId)) {
                throw new Exception('(ErrCode:0722) [' . __LINE__ . '] - Parameter taskId empty');
            }

            $transactionId = Class_db::getInstance()->db_select_col('wfl_task', array('task_id'=>$taskId), 'transaction_id', null, 1);
            Class_db::getInstance()->db_update('icn_workorder', array('transaction_id'=>$transactionId, 'workorder_time_submit'=>'Now()', 'workorder_status'=>'12'), array('workorder_id'=>$workorderId));
            Class_db::getInstance()->db_update('icn_ticket', array('ticket_status'=>'12'), array('transaction_id'=>$transactionId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function get_workorder_list () {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_workorder()');

            $result = array();
            $workorders = Class_db::getInstance()->db_select('icn_workorder', array('workorder_status'=>'<>5'));
            foreach ($workorders as $workorder) {
                $row_result['workorderId'] = $workorder['workorder_id'];
                $row_result['workorderNo'] = $this->fn_general->clear_null($workorder['workorder_no']);
                $row_result['ticketId'] = $workorder['ticket_id'];
                $row_result['problemtypeId'] = $workorder['problemtype_id'];
                $row_result['worktypeId'] = $this->fn_general->clear_null($workorder['worktype_id']);
                $row_result['workcategoryId'] = $workorder['workcategory_id'];
                $row_result['contractorId'] = $this->fn_general->clear_null($workorder['contractor_id']);
                $row_result['siteId'] = $this->fn_general->clear_null($workorder['site_id']);
                $row_result['workorderTimeSubmit'] = str_replace('-', '/', $workorder['workorder_time_submit']);
                $row_result['workorderStatus'] = $workorder['workorder_status'];
                array_push($result, $row_result);
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function get_workorder_by_status () {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_workorder_by_status()');

            $result = array();
            $workorderData = Class_db::getInstance()->db_select('vw_workorder_by_status');
            foreach ($workorderData as $data) {
                $row_result['workorderStatus'] = $data['workorder_status'];
                $row_result['total'] = $data['total'];
                array_push($result, $row_result);
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $groupIds
     * @return array
     * @throws Exception
     */
    public function get_workorder_pending_list ($groupIds) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_workorder()');

            if (empty($groupIds)) {
                throw new Exception('(ErrCode:0723) [' . __LINE__ . '] - Parameter workorderId groupIds');
            }

            $result = array();
            $workorders = Class_db::getInstance()->db_select('vw_workorder_pending', array('task_current'=>'1', 'wfl_task.checkpoint_id'=>'(3,5)', 'wfl_task.group_id'=>'('.$groupIds.')'));
            foreach ($workorders as $workorder) {
                $row_result['taskId'] = $workorder['task_id'];
                $row_result['checkpointId'] = $workorder['checkpoint_id'];
                $row_result['checkpointDesc'] = $workorder['checkpoint_desc'];
                $row_result['taskTimeCreated'] = $workorder['task_time_created'];
                $row_result['groupId'] = $workorder['group_id'];
                $row_result['workorderId'] = $workorder['workorder_id'];
                $row_result['workorderNo'] = $this->fn_general->clear_null($workorder['workorder_no']);
                $row_result['ticketId'] = $workorder['ticket_id'];
                $row_result['problemtypeId'] = $workorder['problemtype_id'];
                $row_result['worktypeId'] = $this->fn_general->clear_null($workorder['worktype_id']);
                $row_result['workcategoryId'] = $workorder['workcategory_id'];
                $row_result['contractorId'] = $this->fn_general->clear_null($workorder['contractor_id']);
                $row_result['siteId'] = $this->fn_general->clear_null($workorder['site_id']);
                $row_result['workorderTimeSubmit'] = str_replace('-', '/', $workorder['workorder_time_submit']);
                $row_result['workorderStatus'] = $workorder['workorder_status'];
                array_push($result, $row_result);
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $groupIds
     * @return array
     * @throws Exception
     */
    public function get_workorder_pending_by_worktype ($groupIds) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_workorder_pending_by_worktype()');

            if (empty($groupIds)) {
                throw new Exception('(ErrCode:0723) [' . __LINE__ . '] - Parameter workorderId groupIds');
            }

            $result = array();
            $workorderData = Class_db::getInstance()->db_select('vw_workorder_pending_by_worktype', array(), null, null, null, array('group_ids'=>'('.$groupIds.')'));
            foreach ($workorderData as $data) {
                $row_result['worktypeId'] = $data['worktype_id'];
                $row_result['total'] = $data['total'];
                array_push($result, $row_result);
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}