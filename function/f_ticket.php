<?php
/**
 * Created by PhpStorm.
 * User: Zaid
 * Date: 2/23/2019
 * Time: 9:47 AM
 */
require_once 'library/constant.php';
require_once 'function/f_general.php';

/* Error code range - 0600 */
class Class_ticket {

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
     * @param $userId
     * @return mixed
     * @throws Exception
     */
    public function create_new_ticket ($userId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering create_new_ticket()');

            if (empty($userId)) {
                throw new Exception('(ErrCode:0602) [' . __LINE__ . '] - Parameter userId empty');
            }

            return Class_db::getInstance()->db_insert('icn_ticket', array('ticket_created_by'=>$userId, 'ticket_status'=>'5'));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0601', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $imageDetails
     * @param $userId
     * @return mixed
     * @throws Exception
     */
    public function upload_ticket_image ($imageDetails, $userId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering create_new_ticket()');

            if (empty($imageDetails)) {
                throw new Exception('(ErrCode:0603) [' . __LINE__ . '] - Array imageDetails empty');
            }
            if (empty($userId)) {
                throw new Exception('(ErrCode:0602) [' . __LINE__ . '] - Parameter userId empty');
            }

            if (!array_key_exists('ticketId', $imageDetails) || empty($imageDetails['ticketId'])) {
                throw new Exception('(ErrCode:0604) [' . __LINE__ . '] - Parameter ticketId empty');
            }
            if (!array_key_exists('uploadName', $imageDetails)) {
                throw new Exception('(ErrCode:0605) [' . __LINE__ . '] - Parameter uploadName empty');
            }
            if (!array_key_exists('uploadUplName', $imageDetails) || empty($imageDetails['uploadUplName'])) {
                throw new Exception('(ErrCode:0606) [' . __LINE__ . '] - Parameter uploadUplName empty');
            }
            if (!array_key_exists('uploadFilesize', $imageDetails) || empty($imageDetails['uploadFilesize'])) {
                throw new Exception('(ErrCode:0607) [' . __LINE__ . '] - Parameter uploadFilesize empty');
            }
            if (!array_key_exists('uploadBlobType', $imageDetails) || empty($imageDetails['uploadBlobType'])) {
                throw new Exception('(ErrCode:0608) [' . __LINE__ . '] - Parameter uploadBlobType empty');
            }
            if (!array_key_exists('uploadBlobData', $imageDetails) || empty($imageDetails['uploadBlobData'])) {
                throw new Exception('(ErrCode:0609) [' . __LINE__ . '] - Parameter uploadBlobData empty');
            }

            $ticketId = $imageDetails['ticketId'];
            $uploadName = $imageDetails['uploadName'];
            $uploadUplName = $imageDetails['uploadUplName'];
            $uploadFilesize = $imageDetails['uploadFilesize'];
            $uploadBlobType = $imageDetails['uploadBlobType'];
            $uploadBlobData = $imageDetails['uploadBlobData'];

            $uploadId = $this->fn_general->uploadDocument(array('name'=>$uploadName, 'filename'=>$uploadUplName, 'size'=>$uploadFilesize, 'type'=>$uploadBlobType, 'data'=>$uploadBlobData), '2', $userId);
            Class_db::getInstance()->db_insert('icn_ticket_image', array('ticket_id'=>$ticketId, 'upload_id'=>$uploadId));
            $documentUrl = $this->fn_general->getDocument($uploadId);

            return array('uploadId'=>$uploadId, 'documentUrl'=>$documentUrl);
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0601', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $ticketId
     * @param $put_vars
     * @param $userId
     * @throws Exception
     */
    public function update_ticket ($ticketId, $put_vars, $userId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering submit_new_ticket()');

            if (empty($ticketId)) {
                throw new Exception('(ErrCode:0611) [' . __LINE__ . '] - Parameter ticketId empty');
            }
            if (empty($put_vars)) {
                throw new Exception('(ErrCode:0610) [' . __LINE__ . '] - Array put_vars empty');
            }
            if (empty($userId)) {
                throw new Exception('(ErrCode:0602) [' . __LINE__ . '] - Parameter userId empty');
            }

            if (!isset($put_vars['problemtypeId']) || empty($put_vars['problemtypeId'])) {
                throw new Exception('(ErrCode:0612) [' . __LINE__ . '] - Parameter problemtypeId empty');
            }
            if (!isset($put_vars['workcategoryId']) || empty($put_vars['workcategoryId'])) {
                throw new Exception('(ErrCode:0613) [' . __LINE__ . '] - Parameter workcategoryId empty');
            }
            if (!isset($put_vars['ticketLongitude']) || empty($put_vars['ticketLongitude'])) {
                throw new Exception('(ErrCode:0614) [' . __LINE__ . '] - Parameter ticketLongitude empty');
            }
            if (!isset($put_vars['ticketLatitude']) || empty($put_vars['ticketLatitude'])) {
                throw new Exception('(ErrCode:0615) [' . __LINE__ . '] - Parameter ticketLatitude empty');
            }
            if (!isset($put_vars['ticketComplaint']) || empty($put_vars['ticketComplaint'])) {
                throw new Exception('(ErrCode:0616) [' . __LINE__ . '] - Parameter ticketComplaint empty');
            }

            $problemtypeId = $put_vars['problemtypeId'];
            $workcategoryId = $put_vars['workcategoryId'];
            $ticketLongitude = $put_vars['ticketLongitude'];
            $ticketLatitude = $put_vars['ticketLatitude'];
            $ticketComplaint = $put_vars['ticketComplaint'];

            Class_db::getInstance()->db_update('icn_ticket', array('problemtype_id'=>$problemtypeId, 'workcategory_id'=>$workcategoryId, 'ticket_longitude'=>$ticketLongitude, 'ticket_latitude'=>$ticketLatitude,
                'ticket_complaint'=>$ticketComplaint), array('ticket_id'=>$ticketId));
        }
        catch(Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0601', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function generate_ticket_no () {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering generate_ticket_no()');

            $ticket = Class_db::getInstance()->db_select_single('icn_ticket', array('ticket_no'=>'is not NULL'), 'ticket_no DESC');
            if (empty($ticket)) {
                return 'CP000001';
            }
            $ticketNoOld = $ticket['ticket_no'];
            $newCnt = intval(substr($ticketNoOld, 3)) + 1000001;

            return 'CP'.substr(strval($newCnt), 1);
        }
        catch(Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0601', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $ticketId
     * @param $ticketNo
     * @param $taskId
     * @throws Exception
     */
    public function submit_ticket ($ticketId, $ticketNo, $taskId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering submit_ticket()');

            if (empty($ticketId)) {
                throw new Exception('(ErrCode:0611) [' . __LINE__ . '] - Parameter ticketId empty');
            }
            if (empty($ticketNo)) {
                throw new Exception('(ErrCode:0617) [' . __LINE__ . '] - Parameter ticketNo empty');
            }
            if (empty($taskId)) {
                throw new Exception('(ErrCode:0618) [' . __LINE__ . '] - Parameter taskId empty');
            }

            $transactionId = Class_db::getInstance()->db_select_col('wfl_task', array('task_id'=>$taskId), 'transaction_id', null, 1);
            Class_db::getInstance()->db_update('icn_ticket', array('ticket_no'=>$ticketNo, 'transaction_id'=>$transactionId, 'ticket_status'=>'11'), array('ticket_id'=>$ticketId));
        }
        catch(Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0601', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function get_ticket_list () {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_ticket_list()');

            $result = array();
            $tickets = Class_db::getInstance()->db_select('icn_ticket', array('ticket_status'=>'<>5'));
            foreach ($tickets as $ticket) {
                $row_result['ticketId'] = $ticket['ticket_id'];
                $row_result['ticketNo'] = $this->fn_general->clear_null($ticket['ticket_no']);
                $row_result['problemtypeId'] = $ticket['problemtype_id'];
                $row_result['workcategoryId'] = $ticket['workcategory_id'];
                $row_result['transactionId'] = $ticket['transaction_id'];
                $row_result['ticketTimeSubmit'] = str_replace('-', '/', $ticket['ticket_time_submit']);
                $row_result['ticketStatus'] = $ticket['ticket_status'];
                array_push($result, $row_result);
            }

            return $result;
        }
        catch(Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0601', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function get_ticket_list_mobile () {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_ticket_list_mobile()');

            $result = array();
            $tickets = Class_db::getInstance()->db_select('dt_ticket', array('ticket_status'=>'<>5'));
            foreach ($tickets as $ticket) {
                $row_result['ticketId'] = $ticket['ticket_id'];
                $row_result['ticketNo'] = $this->fn_general->clear_null($ticket['ticket_no']);
                $row_result['problemtypeDesc'] = $ticket['problemtype_desc'];
                $row_result['worktypeDesc'] = $ticket['worktype_desc'];
                $row_result['workcategoryDesc'] = $ticket['workcategory_desc'];
                $row_result['ticketComplaint'] = $this->fn_general->clear_null($ticket['ticket_complaint']);
                $row_result['transactionId'] = $ticket['transaction_id'];
                $row_result['ticketTimeSubmit'] = str_replace('-', '/', $ticket['ticket_time_submit']);
                $row_result['statusDesc'] = $ticket['status_desc'];
                array_push($result, $row_result);
            }

            return $result;
        }
        catch(Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0601', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $ticketId
     * @return mixed
     * @throws Exception
     */
    public function get_ticket ($ticketId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_ticket()');

            if (empty($ticketId)) {
                throw new Exception('(ErrCode:0611) [' . __LINE__ . '] - Parameter ticketId empty');
            }

            $ticket = Class_db::getInstance()->db_select_single('icn_ticket', array('ticket_id'=>$ticketId), null, 1);
            $result['ticketId'] = $ticket['ticket_id'];
            $result['ticketNo'] = $this->fn_general->clear_null($ticket['ticket_no']);
            $result['problemtypeId'] = $ticket['problemtype_id'];
            $result['workcategoryId'] = $ticket['workcategory_id'];
            $result['ticketLongitude'] = $ticket['ticket_longitude'];
            $result['ticketLatitude'] = $ticket['ticket_latitude'];
            $result['ticketComplaint'] = $this->fn_general->clear_null($ticket['ticket_complaint']);
            $result['ticketTimeSubmit'] = str_replace('-', '/', $ticket['ticket_time_submit']);
            $result['transactionId'] = $ticket['transaction_id'];
            $result['ticketStatus'] = $ticket['ticket_status'];

            $user = Class_db::getInstance()->db_select_single('sys_user', array('user_id'=>$ticket['ticket_created_by']));
            $result['createdBy'] = $user['user_first_name'].' '.$user['user_last_name'];

            $result['ticketImages'] = array();
            $ticketImages = Class_db::getInstance()->db_select('icn_ticket_image', array('ticket_id'=>$ticketId));
            foreach ($ticketImages as $ticketImage) {
                array_push($result['ticketImages'], $this->fn_general->getDocument($ticketImage['upload_id']));
            }

            return $result;
        }
        catch(Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0601', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function get_tickets_by_status () {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_tickets_by_status()');

            $result = array();
            $ticketData = Class_db::getInstance()->db_select('vw_ticket_by_status');
            foreach ($ticketData as $data) {
                $row_result['ticketStatus'] = $data['ticket_status'];
                $row_result['total'] = $data['total'];
                array_push($result, $row_result);
            }

            return $result;
        }
        catch(Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0601', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}

