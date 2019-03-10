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
                $row_result['contractorName'] = $contractor['contractor_name'];
                $row_result['contractorRegNo'] = $this->fn_general->clear_null($contractor['contractor_reg_no']);
                $row_result['contractorContactNo'] = $this->fn_general->clear_null($contractor['contractor_contact_no']);
                $row_result['contractorFaxNo'] = $this->fn_general->clear_null($contractor['contractor_fax_no']);
                $row_result['contractorEmail'] = $this->fn_general->clear_null($contractor['contractor_email']);
                $row_result['groupId'] = $this->fn_general->clear_null($contractor['group_id']);
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
}