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

            if (Class_db::getInstance()->db_count('icn_workorder', array('ticket_id'=>$ticketId)) > 0) {
                throw new Exception('(ErrCode:0704) [' . __LINE__ . '] - '.$constant::ERR_WORKORDER_SIMILAR, 31);
            }

            $workorderId = Class_db::getInstance()->db_insert('icn_workorder', array('ticket_id'=>$ticketId, 'problemtype_id'=>$problemtypeId, 'workcategory_id'=>$workcategoryId, 'workorder_time_complaint'=>$timeComplaint,
                'workorder_created_by'=>$userId, 'workorder_status'=>'5'));
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
            $result['worktypeId'] = $workorder['worktype_id'];
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
            $result['workorderDesc'] = $workorder['workorder_desc'];
            $result['workorderLocationDesc'] = $workorder['workorder_location_desc'];
            $result['workorderStatus'] = $workorder['workorder_status'];
            $result['requesterName'] = $workorder['requester_name'];
            $result['requesterPhone'] = $workorder['requester_phone'];

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}