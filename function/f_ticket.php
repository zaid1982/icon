<?php
/**
 * Created by PhpStorm.
 * User: Zaid
 * Date: 2/23/2019
 * Time: 9:47 AM
 */
require_once 'library/constant.php';
require_once 'function/f_general.php';

/* Error code range - 0500 */
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
                throw new Exception('(ErrCode:0502) [' . __LINE__ . '] - Parameter userId empty');
            }

            return Class_db::getInstance()->db_insert('icn_ticket', array('ticket_created_by'=>$userId, 'ticket_status'=>'5'));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
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
                throw new Exception('(ErrCode:0503) [' . __LINE__ . '] - Array imageDetails empty');
            }
            if (empty($userId)) {
                throw new Exception('(ErrCode:0502) [' . __LINE__ . '] - Parameter userId empty');
            }

            if (!array_key_exists('ticketId', $imageDetails) || empty($imageDetails['ticketId'])) {
                throw new Exception('(ErrCode:0504) [' . __LINE__ . '] - Parameter ticketId empty');
            }
            if (!array_key_exists('uploadName', $imageDetails)) {
                throw new Exception('(ErrCode:0505) [' . __LINE__ . '] - Parameter uploadName empty');
            }
            if (!array_key_exists('uploadUplName', $imageDetails) || empty($imageDetails['uploadUplName'])) {
                throw new Exception('(ErrCode:0506) [' . __LINE__ . '] - Parameter uploadUplName empty');
            }
            if (!array_key_exists('uploadFilesize', $imageDetails) || empty($imageDetails['uploadFilesize'])) {
                throw new Exception('(ErrCode:0507) [' . __LINE__ . '] - Parameter uploadFilesize empty');
            }
            if (!array_key_exists('uploadBlobType', $imageDetails) || empty($imageDetails['uploadBlobType'])) {
                throw new Exception('(ErrCode:0508) [' . __LINE__ . '] - Parameter uploadBlobType empty');
            }
            if (!array_key_exists('uploadBlobData', $imageDetails) || empty($imageDetails['uploadBlobData'])) {
                throw new Exception('(ErrCode:0509) [' . __LINE__ . '] - Parameter uploadBlobData empty');
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
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
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
                throw new Exception('(ErrCode:0511) [' . __LINE__ . '] - Parameter ticketId empty');
            }
            if (empty($put_vars)) {
                throw new Exception('(ErrCode:0510) [' . __LINE__ . '] - Array put_vars empty');
            }
            if (empty($userId)) {
                throw new Exception('(ErrCode:0502) [' . __LINE__ . '] - Parameter userId empty');
            }

            if (!isset($put_vars['problemtypeId']) || empty($put_vars['problemtypeId'])) {
                throw new Exception('(ErrCode:0512) [' . __LINE__ . '] - Parameter problemtypeId empty');
            }
            if (!isset($put_vars['workcategoryId']) || empty($put_vars['workcategoryId'])) {
                throw new Exception('(ErrCode:0513) [' . __LINE__ . '] - Parameter workcategoryId empty');
            }
            if (!isset($put_vars['ticketLongitude']) || empty($put_vars['ticketLongitude'])) {
                throw new Exception('(ErrCode:0514) [' . __LINE__ . '] - Parameter ticketLongitude empty');
            }
            if (!isset($put_vars['ticketLatitude']) || empty($put_vars['ticketLatitude'])) {
                throw new Exception('(ErrCode:0515) [' . __LINE__ . '] - Parameter ticketLatitude empty');
            }
            if (!isset($put_vars['ticketComplaint']) || empty($put_vars['ticketComplaint'])) {
                throw new Exception('(ErrCode:0516) [' . __LINE__ . '] - Parameter ticketComplaint empty');
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
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
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
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
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
                throw new Exception('(ErrCode:0511) [' . __LINE__ . '] - Parameter ticketId empty');
            }
            if (empty($ticketNo)) {
                throw new Exception('(ErrCode:0517) [' . __LINE__ . '] - Parameter ticketNo empty');
            }
            if (empty($taskId)) {
                throw new Exception('(ErrCode:0518) [' . __LINE__ . '] - Parameter taskId empty');
            }

            $transactionId = Class_db::getInstance()->db_select_col('wfl_task', array('task_id'=>$taskId), 'transaction_id', null, 1);
            Class_db::getInstance()->db_update('icn_ticket', array('ticket_no'=>$ticketNo, 'transaction_id'=>$transactionId, 'ticket_status'=>'11'), array('ticket_id'=>$ticketId));
        }
        catch(Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}

