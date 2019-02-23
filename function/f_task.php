<?php
/**
 * Created by PhpStorm.
 * User: Zaid
 * Date: 2/23/2019
 * Time: 1:35 PM
 */
require_once 'library/constant.php';
require_once 'function/f_general.php';

/* Error code range - 0400 */
class Class_task {

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
     * @param $flowId
     * @param $userId
     * @param $roleId
     * @param $groupId
     * @param $transactionNo
     * @return string
     * @throws Exception
     */
    public function create_new_task ($flowId, $userId, $roleId, $groupId, $transactionNo) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering create_new_task()');

            if (empty($flowId)) {
                throw new Exception('(ErrCode:0402) [' . __LINE__ . '] - Parameter flowId empty');
            }
            if (empty($userId)) {
                throw new Exception('(ErrCode:0403) [' . __LINE__ . '] - Parameter userId empty');
            }
            if (empty($roleId)) {
                throw new Exception('(ErrCode:0404) [' . __LINE__ . '] - Parameter roleId empty');
            }
            if (empty($groupId)) {
                throw new Exception('(ErrCode:0405) [' . __LINE__ . '] - Parameter groupId empty');
            }
            if (empty($transactionNo)) {
                throw new Exception('(ErrCode:0406) [' . __LINE__ . '] - Parameter transactionNo empty');
            }

            $checkpoint = Class_db::getInstance()->db_select_single('wfl_checkpoint', array('flow_id'=>$flowId, 'checkpoint_type'=>'1'), null, 1);
            $checkpoint_id = $checkpoint['checkpoint_id'];
            $checkpointRole = $checkpoint['role_id'];
            $checkpointGroup = $checkpoint['group_id'];

            if (!is_null($checkpointRole) && $checkpointRole != $roleId) {
                throw new Exception('(ErrCode:0407) [' . __LINE__ . '] - Role ID ('.$roleId.') is not allowed to perform this checkpoint ('.$checkpoint_id.')');
            }
            if (!is_null($checkpointGroup) && $checkpointGroup != $groupId) {
                throw new Exception('(ErrCode:0408) [' . __LINE__ . '] - Group ID ('.$groupId.') is not allowed to perform this checkpoint ('.$checkpoint_id.')');
            }
            if (Class_db::getInstance()->db_count('wfl_checkpoint_user', array('checkpoint_id'=>$checkpoint_id, 'user_id'=>$userId)) == 0) {
                throw new Exception('(ErrCode:0409) [' . __LINE__ . '] - User ID ('.$userId.') is not allowed to perform this checkpoint ('.$checkpoint_id.')');
            }

            $flowDueDay = Class_db::getInstance()->db_select_single('wfl_flow', array('flow_id'=>$flowId), 'flow_due_day', null, 1);
            $transactionId = Class_db::getInstance()->db_insert('wfl_transaction', array('transaction_no'=>$transactionNo, 'flow_id'=>$flowId, 'user_id'=>$userId, 'group_id'=>$groupId,
                'transaction_date_due'=>'|Curdate()+'.$flowDueDay, 'transaction_status'=>'5'));
            Class_db::getInstance()->db_insert('wfl_task', array('transaction_id'=>$transactionId, 'checkpoint_id'=>$checkpoint_id));
            return $transactionId;

        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}