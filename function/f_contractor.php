<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/10/2019
 * Time: 9:44 PM
 */
require_once 'library/constant.php';
require_once 'function/f_general.php';

/* Error code range - 0800 */
class Class_contractor {

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
     * @return array
     * @throws Exception
     */
    public function get_contractor_list () {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_workorder()');

            $result = array();
            $contractors = Class_db::getInstance()->db_select('vw_contractor');
            foreach ($contractors as $contractor) {
                $row_result['contractorId'] = $contractor['contractor_id'];
                $row_result['contractorName'] = $this->fn_general->clear_null($contractor['contractor_name']);
                $row_result['contractorRegNo'] = $this->fn_general->clear_null($contractor['contractor_reg_no']);
                $row_result['contractorContactNo'] = $this->fn_general->clear_null($contractor['contractor_contact_no']);
                $row_result['contractorFaxNo'] = $this->fn_general->clear_null($contractor['contractor_fax_no']);
                $row_result['contractorEmail'] = $this->fn_general->clear_null($contractor['contractor_email']);
                $row_result['groupId'] = $contractor['group_id'];
                $row_result['contractorTimeCreated'] = str_replace('-', '/', $contractor['contractor_time_created']);
                $row_result['contractorStatus'] = $contractor['contractor_status'];
                $row_result['sites'] = $this->fn_general->clear_null($contractor['sites']);
                $row_result['address']['addressId'] = $this->fn_general->clear_null($contractor['address_id']);
                $row_result['address']['addressDesc'] = $this->fn_general->clear_null($contractor['address_desc']);
                $row_result['address']['addressPostcode'] = $this->fn_general->clear_null($contractor['address_postcode']);
                $row_result['address']['addressCity'] = $this->fn_general->clear_null($contractor['address_city']);
                $row_result['address']['stateDesc'] = $this->fn_general->clear_null($contractor['state_desc']);
                array_push($result, $row_result);
            }

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0801', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $userId
     * @return mixed
     * @throws Exception
     */
    public function create_draft ($userId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering create_draft()');

            if (empty($userId)) {
                throw new Exception('(ErrCode:0802) [' . __LINE__ . '] - Parameter userId empty');
            }

            $addressId = Class_db::getInstance()->db_insert('sys_address', array());
            $groupId = Class_db::getInstance()->db_insert('sys_group', array('group_type'=>'3', 'group_status'=>'5'));
            return Class_db::getInstance()->db_insert('icn_contractor', array('contractor_created_by'=>$userId, 'address_id'=>$addressId, 'group_id'=>$groupId, 'contractor_status'=>'5'));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0801', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $contractorId
     * @return mixed
     * @throws Exception
     */
    public function get_contractor ($contractorId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_contractor()');

            if (empty($contractorId)) {
                throw new Exception('(ErrCode:0803) [' . __LINE__ . '] - Parameter contractorId empty');
            }

            $contractor = Class_db::getInstance()->db_select_single('vw_contractor', array('icn_contractor.contractor_id'=>$contractorId), null, 1);
            $result['contractorId'] = $contractor['contractor_id'];
            $result['contractorName'] = $this->fn_general->clear_null($contractor['contractor_name']);
            $result['contractorRegNo'] = $this->fn_general->clear_null($contractor['contractor_reg_no']);
            $result['contractorContactNo'] = $this->fn_general->clear_null($contractor['contractor_contact_no']);
            $result['contractorFaxNo'] = $this->fn_general->clear_null($contractor['contractor_fax_no']);
            $result['contractorEmail'] = $this->fn_general->clear_null($contractor['contractor_email']);
            $result['groupId'] = $contractor['group_id'];
            $result['contractorCreatedBy'] = $contractor['created_by'];
            $result['contractorTimeCreated'] = str_replace('-', '/', $contractor['contractor_time_created']);
            $result['contractorStatus'] = $contractor['contractor_status'];
            $result['address']['addressId'] = $this->fn_general->clear_null($contractor['address_id']);
            $result['address']['addressDesc'] = $this->fn_general->clear_null($contractor['address_desc']);
            $result['address']['addressPostcode'] = $this->fn_general->clear_null($contractor['address_postcode']);
            $result['address']['addressCity'] = $this->fn_general->clear_null($contractor['address_city']);
            $result['address']['stateId'] = $this->fn_general->clear_null($contractor['state_id']);
            $result['address']['stateDesc'] = $this->fn_general->clear_null($contractor['state_desc']);

            $resultSites = array();
            $contractorSites = Class_db::getInstance()->db_select('icn_contractor_site', array('contractor_id'=>$contractorId));
            foreach ($contractorSites as $contractorSite) {
                $row_result['contractorSiteId'] = $contractorSite['contractor_site_id'];
                $row_result['siteId'] = $contractorSite['site_id'];
                array_push($resultSites, $row_result);
            }
            $result['sites'] = $resultSites;

            $resultUser = array();
            $contractorUsers = Class_db::getInstance()->db_select('vw_contractor_user', array(), null, null, null, array('group_id'=>$contractor['group_id']));
            foreach ($contractorUsers as $contractorUser) {
                $row_result = array();
                $row_result['userId'] = $contractorUser['user_id'];
                $row_result['userFullname'] = $contractorUser['user_fullname'];
                $row_result['userContactNo'] = $this->fn_general->clear_null($contractorUser['user_contact_no']);
                $row_result['userEmail'] = $this->fn_general->clear_null($contractorUser['user_email']);
                $row_result['roles'] = $this->fn_general->clear_null($contractorUser['roles']);
                $row_result['userStatus'] = $contractorUser['user_status'];
                array_push($resultUser, $row_result);
            }
            $result['employees'] = $resultUser;

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0801', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $contractorId
     * @param $put_vars
     * @throws Exception
     */
    public function save_contractor ($contractorId, $put_vars) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering save_contractor()');

            if (empty($contractorId)) {
                throw new Exception('(ErrCode:0803) [' . __LINE__ . '] - Parameter contractorId empty');
            }
            if (empty($put_vars)) {
                throw new Exception('(ErrCode:0804) [' . __LINE__ . '] - Array put_vars empty');
            }

            if (!isset($put_vars['contractorName'])) {
                throw new Exception('(ErrCode:0805) [' . __LINE__ . '] - Parameter contractorName not exist');
            }
            if (!isset($put_vars['contractorRegNo'])) {
                throw new Exception('(ErrCode:0806) [' . __LINE__ . '] - Parameter contractorRegNo not exist');
            }
            if (!isset($put_vars['addressDesc'])) {
                throw new Exception('(ErrCode:0807) [' . __LINE__ . '] - Parameter addressDesc not exist');
            }
            if (!isset($put_vars['addressPostcode'])) {
                throw new Exception('(ErrCode:0808) [' . __LINE__ . '] - Parameter addressPostcode not exist');
            }
            if (!isset($put_vars['addressCity'])) {
                throw new Exception('(ErrCode:0809) [' . __LINE__ . '] - Parameter addressCity not exist');
            }
            if (!isset($put_vars['stateId'])) {
                throw new Exception('(ErrCode:0810) [' . __LINE__ . '] - Parameter stateId not exist');
            }
            if (!isset($put_vars['contractorContactNo'])) {
                throw new Exception('(ErrCode:0811) [' . __LINE__ . '] - Parameter contractorContactNo not exist');
            }
            if (!isset($put_vars['contractorFaxNo'])) {
                throw new Exception('(ErrCode:0812) [' . __LINE__ . '] - Parameter contractorFaxNo not exist');
            }
            if (!isset($put_vars['contractorEmail'])) {
                throw new Exception('(ErrCode:0813) [' . __LINE__ . '] - Parameter contractorEmail not exist');
            }

            $contractor = Class_db::getInstance()->db_select_single('icn_contractor', array('contractor_id'=>$contractorId), null, 1);

            $arrUpdateAddress = array(
                'address_desc' => $put_vars['addressDesc'],
                'address_postcode' => $put_vars['addressPostcode'],
                'address_city' => $put_vars['addressCity'],
                'state_id' => $put_vars['stateId']
            );
            Class_db::getInstance()->db_update('sys_address', $arrUpdateAddress, array('address_id'=>$contractor['address_id']));

            $arrUpdate = array(
                'contractor_name' => $put_vars['contractorName'],
                'contractor_reg_no' => $put_vars['contractorRegNo'],
                'contractor_contact_no' => $put_vars['contractorContactNo'],
                'contractor_fax_no' => $put_vars['contractorFaxNo'],
                'contractor_email' => $put_vars['contractorEmail']
            );
            Class_db::getInstance()->db_update('icn_contractor', $arrUpdate, array('contractor_id'=>$contractorId));

            $arrUpdateGroup = array(
                'group_name' => $put_vars['contractorName'],
                'group_reg_no' => $put_vars['contractorRegNo']
            );
            Class_db::getInstance()->db_update('sys_group', $arrUpdateGroup, array('group_id'=>$contractor['group_id']));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $contractorId
     * @param $siteId
     * @return
     * @throws Exception
     */
    public function add_contractor_site ($contractorId, $siteId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering add_contractor_site()');

            if (empty($contractorId)) {
                throw new Exception('(ErrCode:0803) [' . __LINE__ . '] - Parameter contractorId empty');
            }
            if (empty($siteId)) {
                throw new Exception('(ErrCode:0810) [' . __LINE__ . '] - Parameter siteId empty');
            }
            if (Class_db::getInstance()->db_count('icn_contractor_site', array('contractor_id'=>$contractorId, 'site_id'=>$siteId)) > 0) {
                throw new Exception('(ErrCode:0531) [' . __LINE__ . '] - '.$constant::ERR_CONTRACTOR_SITE_SIMILAR, 31);
            }

            return Class_db::getInstance()->db_insert('icn_contractor_site', array('contractor_id'=>$contractorId, 'site_id'=>$siteId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $contractorSiteId
     * @throws Exception
     */
    public function delete_contractor_site ($contractorSiteId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering delete_contractor_site()');

            if (empty($contractorSiteId)) {
                throw new Exception('(ErrCode:0814) [' . __LINE__ . '] - Parameter contractorSiteId empty');
            }

            Class_db::getInstance()->db_delete('icn_contractor_site', array('contractor_site_id'=>$contractorSiteId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $contractorId
     * @throws Exception
     */
    public function submit_contractor ($contractorId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering submit_contractor()');

            if (empty($contractorId)) {
                throw new Exception('(ErrCode:0803) [' . __LINE__ . '] - Parameter contractorId empty');
            }

            $contractor = Class_db::getInstance()->db_select_single('icn_contractor', array('contractor_id'=>$contractorId), null, 1);
            $groupId = $contractor['group_id'];
            if ($contractor['contractor_status'] != '5') {
                throw new Exception('(ErrCode:0815) [' . __LINE__ . '] - '.$constant::ERR_CONTRACTOR_SUBMITTED, 31);
            }
            if (Class_db::getInstance()->db_count('icn_contractor_site', array('contractor_id'=>$contractorId)) == 0) {
                throw new Exception('(ErrCode:0816) [' . __LINE__ . '] - '.$constant::ERR_CONTRACTOR_NOSITE, 31);
            }
            if (Class_db::getInstance()->db_count('sys_user_role', array('group_id'=>$groupId, 'role_id'=>'5')) == 0) {
                throw new Exception('(ErrCode:0817) [' . __LINE__ . '] - '.$constant::ERR_CONTRACTOR_NOSUPERVISOR, 31);
            }

            Class_db::getInstance()->db_update('icn_contractor', array('contractor_status'=>'1', 'contractor_time_registered'=>'Now()'), array('contractor_id'=>$contractorId));
            Class_db::getInstance()->db_update('sys_group', array('group_status'=>'1'), array('group_id'=>$groupId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $contractorId
     * @throws Exception
     */
    public function deactivate_contractor ($contractorId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering deactivate_contractor()');

            if (empty($contractorId)) {
                throw new Exception('(ErrCode:0803) [' . __LINE__ . '] - Parameter contractorId empty');
            }
            if (Class_db::getInstance()->db_count('icn_contractor', array('contractor_id'=>$contractorId, 'contractor_status'=>'2')) > 0) {
                throw new Exception('(ErrCode:0818) [' . __LINE__ . '] - '.$constant::ERR_CONTRACTOR_DEACTIVATE, 31);
            }
            if (Class_db::getInstance()->db_count('icn_contractor', array('contractor_id'=>$contractorId, 'contractor_status'=>'1')) == 0) {
                throw new Exception('(ErrCode:0820) [' . __LINE__ . '] - Current contractor status should be active');
            }

            Class_db::getInstance()->db_update('icn_contractor', array('contractor_status'=>'2'), array('contractor_id'=>$contractorId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $contractorId
     * @throws Exception
     */
    public function activate_contractor ($contractorId) {
        $constant = new Class_constant();
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering activate_contractor()');

            if (empty($contractorId)) {
                throw new Exception('(ErrCode:0803) [' . __LINE__ . '] - Parameter contractorId empty');
            }
            if (Class_db::getInstance()->db_count('icn_contractor', array('contractor_id'=>$contractorId, 'contractor_status'=>'1')) > 0) {
                throw new Exception('(ErrCode:0819) [' . __LINE__ . '] - '.$constant::ERR_CONTRACTOR_ACTIVATE, 31);
            }
            if (Class_db::getInstance()->db_count('icn_contractor', array('contractor_id'=>$contractorId, 'contractor_status'=>'2')) == 0) {
                throw new Exception('(ErrCode:0821) [' . __LINE__ . '] - Current contractor status should be not active');
            }

            Class_db::getInstance()->db_update('icn_contractor', array('contractor_status'=>'1'), array('contractor_id'=>$contractorId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    public function delete_contractor ($contractorId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering delete_contractor()');

            if (empty($contractorId)) {
                throw new Exception('(ErrCode:0803) [' . __LINE__ . '] - Parameter contractorId empty');
            }
            if (Class_db::getInstance()->db_count('icn_contractor', array('contractor_id'=>$contractorId, 'contractor_status'=>'5')) == 0) {
                throw new Exception('(ErrCode:0822) [' . __LINE__ . '] - Current contractor status should be draft');
            }

            $groupId = Class_db::getInstance()->db_select_col('icn_contractor', array('contractor_id'=>$contractorId), 'group_id', null, 1);

            Class_db::getInstance()->db_delete('icn_contractor_site', array('contractor_id'=>$contractorId));
            Class_db::getInstance()->db_delete('icn_contractor', array('contractor_id'=>$contractorId));
            Class_db::getInstance()->db_delete('sys_user_group', array('group_id'=>$groupId));
            Class_db::getInstance()->db_delete('sys_user_role', array('group_id'=>$groupId));
            Class_db::getInstance()->db_delete('sys_group', array('group_id'=>$groupId));
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0701', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}