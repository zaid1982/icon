<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/20/2019
 * Time: 2:08 AM
 */
require_once 'library/constant.php';
require_once 'function/f_general.php';

/* Error code range - 0900 */
class Class_employee {

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
     * @param $mykadNo
     * @param $groupId
     * @return array
     * @throws Exception
     */
    public function checkByIc ($mykadNo, $groupId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering checkByIc()');

            if (empty($mykadNo)) {
                throw new Exception('(ErrCode:0802) [' . __LINE__ . '] - Parameter mykadNo empty');
            }
            if (empty($groupId)) {
                throw new Exception('(ErrCode:0803) [' . __LINE__ . '] - Parameter groupId empty');
            }

            $result = array();
            $userProfile = Class_db::getInstance()->db_select_single('vw_user_profile', array('user_mykad_no'=>$mykadNo));
            if (!empty($userProfile)) {
                if (Class_db::getInstance()->db_count('sys_user_group', array('group_id'=>$groupId, 'user_id'=>$userProfile['user_id'])) > 0) {
                    throw new Exception('(ErrCode:0804) [' . __LINE__ . '] - '.$constant::ERR_EMPLOYEE_CHECK_EXIST, 31);
                }

                $result['userId'] = $userProfile['user_id'];
                $result['userName'] = $userProfile['user_name'];
                $result['userFirstName'] = $userProfile['user_first_name'];
                $result['userLastName'] = $userProfile['user_last_name'];
                $result['userMykadNo'] = $userProfile['user_mykad_no'];
                $result['userStatus'] = $userProfile['user_status'];
                $result['userContactNo'] = $this->fn_general->clear_null($userProfile['user_contact_no']);
                $result['userEmail'] = $this->fn_general->clear_null($userProfile['user_email']);

                $resultRole = array();
                $roles = Class_db::getInstance()->db_select('sys_user_role', array('user_id'=>$userProfile['user_id'], 'role_id'=>'(5,6)'));
                foreach ($roles as $role) {
                    array_push($resultRole, $role['role_id']);
                }
                $result['roles'] = $resultRole;
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0801', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $userGroupId
     * @return array
     * @throws Exception
     */
    public function getEmployee ($userGroupId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering getEmployee()');

            if (empty($userGroupId)) {
                throw new Exception('(ErrCode:0804) [' . __LINE__ . '] - Parameter userGroupId empty');
            }

            $userId = Class_db::getInstance()->db_select_col('sys_user_group', array('user_group_id'=>$userGroupId), 'user_id', null, 1);

            $result = array();
            $userProfile = Class_db::getInstance()->db_select_single('vw_user_profile', array('sys_user.user_id'=>$userId), null, 1);
            if (!empty($userProfile)) {
                $result['userId'] = $userProfile['user_id'];
                $result['userName'] = $userProfile['user_name'];
                $result['userFirstName'] = $userProfile['user_first_name'];
                $result['userLastName'] = $userProfile['user_last_name'];
                $result['userMykadNo'] = $userProfile['user_mykad_no'];
                $result['userStatus'] = $userProfile['user_status'];
                $result['userContactNo'] = $this->fn_general->clear_null($userProfile['user_contact_no']);
                $result['userEmail'] = $this->fn_general->clear_null($userProfile['user_email']);

                $resultRole = array();
                $roles = Class_db::getInstance()->db_select('sys_user_role', array('user_id'=>$userId, 'role_id'=>'(5,6)'));
                foreach ($roles as $role) {
                    array_push($resultRole, $role['role_id']);
                }
                $result['roles'] = $resultRole;
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0801', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}