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
     * @return array
     * @throws Exception
     */
    public function get_problem_type () {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_problem_type()');

            $result = array();
            $arr_dataLocal = Class_db::getInstance()->db_select('icn_problemtype');
            foreach ($arr_dataLocal as $dataLocal) {
                $row_result = array('problemtypeId' => '', 'problemtypeDesc' => '', 'problemtypeStatus' => '');
                $row_result['problemtypeId'] = $dataLocal['problemtype_id'];
                $row_result['problemtypeDesc'] = $dataLocal['problemtype_desc'];
                $row_result['problemtypeStatus'] = $dataLocal['problemtype_status'];
                array_push($result, $row_result);
            }
            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0501', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}