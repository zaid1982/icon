<?php
require_once 'f_general.php';
require_once 'src/BeforeValidException.php';
require_once 'src/ExpiredException.php';
require_once 'src/SignatureInvalidException.php';
require_once 'src/JWT.php';

use \Firebase\JWT\JWT;

/* Error code range - 0100 */ 
class Class_login {
     
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
     * @param string $userId
     * @param string $username
     * @return string
     * @throws Exception
     */
    public function create_jwt ($userId='', $username='') {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering create_jwt()');
            if ($userId === '') {
                throw new Exception('(ErrCode:0102) [' . __LINE__ . '] - Parameter userId empty');   
            }
            if ($username === '') {
                throw new Exception('(ErrCode:0103) [' . __LINE__ . '] - Parameter username empty');   
            }
            
            $key = "inventory_sample1";
            $token = array('iss'=>'inventory_sample1/jwt', 'userId'=>$userId, 'username'=>$username, 'iat'=>time(), 'exp'=>time()+10);
            $jwt = JWT::encode($token, $key);              
            return $jwt;
        } catch(Exception $ex) {  
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage()); 
            throw new Exception($this->get_exception('0101', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param string $jwt
     * @return object
     * @throws Exception
     */
    public function check_jwt ($jwt='') {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering check_jwt()');
            if ($jwt === '') {
                throw new Exception('(ErrCode:0104) [' . __LINE__ . '] - Parameter jwt empty');   
            }
            
            $key = "inventory_sample1";
            JWT::$leeway = 86400; // $leeway in seconds
            $data = JWT::decode(substr($jwt, 7), $key, array('HS256'));
            
            if (Class_db::getInstance()->db_count('sys_user', array('user_id'=>$data->userId)) == 0) {
                throw new Exception('(ErrCode:0105) [' . __LINE__ . '] - Token not valid');   
            }
            return $data;
        } catch(Exception $ex) {   
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0101', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param array $arr_roles
     * @return array
     * @throws Exception
     */
    public function get_menu_list ($arr_roles=array()) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering get_menu_list()');
            if (empty($arr_roles)) {
                throw new Exception('(ErrCode:0107) [' . __LINE__ . '] - Array arr_roles empty');  
            }
            
            $role_list = array();
            foreach ($arr_roles as $roles) {
                array_push($role_list, $roles['roleId']);
            }
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Roles = '.$role_list[0]);
            $role_str = implode(',', $role_list);            
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, $role_str);
            
            $menu_return = [];
            $nav_index = 0;
            $menu_list = Class_db::getInstance()->db_select('vw_menu', null, null, null, 1, array('roles'=>$role_str));
            foreach ($menu_list as $menu) {                
                //$this->fn_general->log_debug(__FUNCTION__, __LINE__, '$nav_page = '.$menu['nav_page']);
                //$this->fn_general->log_debug(__FUNCTION__, __LINE__, '$nav_index = '.$nav_index);
                if (is_null($menu['nav_second_id'])) {
                    array_push($menu_return, array('navId'=>$menu['nav_id'], 'navDesc'=>$menu['nav_desc'], 'navIcon'=>$menu['nav_icon'], 'navPage'=> $this->fn_general->clear_null($menu['nav_page']), 'navSecond'=>array()));
                    $nav_index++;
                } else {
                    array_push($menu_return[$nav_index-1]['navSecond'], array('navSecondId'=>$menu['nav_second_id'], 'navSecondDesc'=>$menu['nav_second_desc'], 'navSecondPage'=>$menu['nav_second_page']));
                }
            }
            return $menu_return;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0101', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    /**
     * @param $username
     * @param $password
     * @param $roleId
     * @return array
     * @throws Exception
     */
    public function check_login ($username, $password, $roleId) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering check_login()');
            if (is_null($username) || $username === '') { 
                throw new Exception('(ErrCode:0108) [' . __LINE__ . '] - User ID is empty', 31);         
            } 
            if (is_null($password) || $password === '') { 
                throw new Exception('(ErrCode:0109) [' . __LINE__ . '] - Password is empty', 31);         
            }
            if (is_null($roleId) || $roleId === '') {
                throw new Exception('(ErrCode:0110) [' . __LINE__ . '] - Role ID is empty');
            }

            $profile = Class_db::getInstance()->db_select_single('vw_profile', array('user_name'=>$username));
            if (empty($profile)) {
                throw new Exception('(ErrCode:0111) [' . __LINE__ . '] - User ID is not exist', 31);
            } 
            if ($profile['user_password'] !== md5($password)) {
                throw new Exception('(ErrCode:0112) [' . __LINE__ . '] - Password is incorrect', 31);
            } 
            if ($profile['user_status'] !== '1') {
                throw new Exception('(ErrCode:0113) [' . __LINE__ . '] - User ID is not active. Please contact Administrator to activate.', 31);
            }

            $userId = $profile['user_id'];
            $result = array();

            if (Class_db::getInstance()->db_count('sys_user_role', array('user_id'=>$userId, 'role_id'=>$roleId)) == 0) {
                throw new Exception('(ErrCode:0114) [' . __LINE__ . '] - User ID not exist', 31);
            }
            $arr_roles = Class_db::getInstance()->db_select('vw_roles', array('sys_user_role.user_id'=>$userId));

            $token = $this->create_jwt($userId, $username);

            $result['token'] = $token;
            $result['userId'] = $userId;
            $result['userName'] = $username;
            $result['userFirstName'] = $profile['user_first_name'];
            $result['userLastName'] = $profile['user_last_name'];
            $result['userType'] = $profile['user_type'];
            $result['userMykadNo'] = $this->fn_general->clear_null($profile['user_mykad_no']);
            $result['userEmail'] = $profile['user_email'];
            $result['userContactNo'] = $profile['user_contact_no'];
            $result['isFirstTime'] = is_null($profile['user_time_activate']) ? 'Yes' : 'No';
            $result['address']['addressDesc'] = $this->fn_general->clear_null($profile['address_desc']);
            $result['address']['addressPostcode'] = $this->fn_general->clear_null($profile['address_postcode']);            
            $result['address']['addressCity'] = $this->fn_general->clear_null($profile['address_city']);          
            $result['address']['addressState'] = $this->fn_general->clear_null($profile['state_desc']);
            $result['roles'] = $arr_roles;

            if ($roleId == '5' || $roleId == '6') {
                $groupId = Class_db::getInstance()->db_select_col('sys_user_group', array('user_id'=>$userId), null, 1);
                $sys_group = Class_db::getInstance()->db_select_single('sys_group', array('group_id'=>$groupId), null, 1);
                $result['group']['groupId'] = $sys_group['group_id'];
                $result['group']['groupName'] = $sys_group['group_name'];
                $result['group']['groupType'] = $sys_group['group_type'];
                $result['group']['groupRegNo'] = $this->fn_general->clear_null($sys_group['group_reg_no']);
                $result['group']['groupStatus'] = $sys_group['group_status'];
            } else {
                $result['group'] = '';
            }
            //$result['menu'] = $fn_login->get_menu_list($arr_roles);        
            
            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0101', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }

    public function check_login_web ($username, $password) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering check_login()');
            if (is_null($username) || $username === '') {
                throw new Exception('(ErrCode:0108) [' . __LINE__ . '] - User ID is empty', 31);
            }
            if (is_null($password) || $password === '') {
                throw new Exception('(ErrCode:0109) [' . __LINE__ . '] - Password is empty', 31);
            }

            $profile = Class_db::getInstance()->db_select_single('vw_profile', array('user_name'=>$username));
            if (empty($profile)) {
                throw new Exception('(ErrCode:0111) [' . __LINE__ . '] - User ID is not exist', 31);
            }
            if ($profile['user_password'] !== md5($password)) {
                throw new Exception('(ErrCode:0112) [' . __LINE__ . '] - Password is incorrect', 31);
            }
            if ($profile['user_status'] !== '1') {
                throw new Exception('(ErrCode:0113) [' . __LINE__ . '] - User ID is not active. Please contact Administrator to activate.', 31);
            }

            $userId = $profile['user_id'];
            $result = array();

            $arr_roles = Class_db::getInstance()->db_select('vw_roles', array('sys_user_role.user_id'=>$userId));

            $token = $this->create_jwt($userId, $username);

            $result['token'] = $token;
            $result['userId'] = $userId;
            $result['userName'] = $username;
            $result['userFirstName'] = $profile['user_first_name'];
            $result['userLastName'] = $profile['user_last_name'];
            $result['userType'] = $profile['user_type'];
            $result['userMykadNo'] = $this->fn_general->clear_null($profile['user_mykad_no']);
            $result['userEmail'] = $profile['user_email'];
            $result['userContactNo'] = $profile['user_contact_no'];
            $result['isFirstTime'] = is_null($profile['user_time_activate']) ? 'Yes' : 'No';
            $result['address']['addressDesc'] = $this->fn_general->clear_null($profile['address_desc']);
            $result['address']['addressPostcode'] = $this->fn_general->clear_null($profile['address_postcode']);
            $result['address']['addressCity'] = $this->fn_general->clear_null($profile['address_city']);
            $result['address']['addressState'] = $this->fn_general->clear_null($profile['state_desc']);
            $result['roles'] = $arr_roles;

            $result['menu'] = $this->get_menu_list($arr_roles);

            return $result;
        } catch (Exception $ex) {
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0101', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
}