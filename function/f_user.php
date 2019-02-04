<?php
require_once 'f_general.php';
require_once 'f_email.php';

/* Error code range - 0200 */ 
class Class_user {
     
    private $fn_general;
    private $fn_email;
    
    function __construct()
    {
        $this->fn_general = new Class_general();
        $this->fn_email = new Class_email();
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
    
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new Exception($this->get_exception('0001', __FUNCTION__, __LINE__, 'Get Property not exist ['.$property.']'));
        }
    }

    public function __set( $property, $value ) {
        if (property_exists($this, $property)) {
            $this->$property = $value;        
        } else {
            throw new Exception($this->get_exception('0002', __FUNCTION__, __LINE__, 'Get Property not exist ['.$property.']'));
        }
    }
    
    public function __isset( $property ) {
        if (property_exists($this, $property)) {
            return isset($this->$property);
        } else {
            throw new Exception($this->get_exception('0003', __FUNCTION__, __LINE__, 'Get Property not exist ['.$property.']'));
        }
    }
    
    public function __unset( $property ) {
        if (property_exists($this, $property)) {
            unset($this->$property);
        } else {
            throw new Exception($this->get_exception('0004', __FUNCTION__, __LINE__, 'Get Property not exist ['.$property.']'));
        } 
    }
               
    public function register_user ($userDetails=array(), $type=0) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering register_user()');
            if (empty($userDetails)) {
                throw new Exception('(ErrCode:0202) [' . __LINE__ . '] - Array userDetails empty');   
            }     
            if (empty($type)) {
                throw new Exception('(ErrCode:0203) [' . __LINE__ . '] - Parameter type empty');   
            }     
            if (!array_key_exists('userFirstName', $userDetails)) {
                throw new Exception('(ErrCode:0204) [' . __LINE__ . '] - Index userFirstName in array userDetails empty');  
            }  
            if (!array_key_exists('userLastName', $userDetails)) {
                throw new Exception('(ErrCode:0205) [' . __LINE__ . '] - Index userLastName in array userDetails empty');  
            } 
            if (!array_key_exists('userEmail', $userDetails)) {
                throw new Exception('(ErrCode:0206) [' . __LINE__ . '] - Index userEmail in array userDetails empty');  
            } 
            if (!array_key_exists('userMykadNo', $userDetails)) {
                throw new Exception('(ErrCode:0207) [' . __LINE__ . '] - Index userMykadNo in array userDetails empty');  
            } 
            if (!array_key_exists('userProfileContactNo', $userDetails)) {
                throw new Exception('(ErrCode:0208) [' . __LINE__ . '] - Index userProfileContactNo in array userDetails empty');  
            } 
            if (!array_key_exists('userPassword', $userDetails)) {
                throw new Exception('(ErrCode:0209) [' . __LINE__ . '] - Index userPassword in array userDetails empty');  
            }            
            
            $userFirstName = $userDetails['userFirstName'];
            $userLastName = $userDetails['userLastName'];
            $userEmail = $userDetails['userEmail'];
            $userMykadNo = $userDetails['userMykadNo'];
            $userProfileContactNo = $userDetails['userProfileContactNo'];
            $userPassword = $userDetails['userPassword'];
            
            if (Class_db::getInstance()->db_count('sys_user', array('user_email'=>$userEmail)) > 0) {
                throw new Exception('(ErrCode:0210) [' . __LINE__ . '] - Email already exist. Please use different email.', 31);                     
            }
            
            if ($type === 2) {
                $userId = Class_db::getInstance()->db_insert('sys_user', array('user_email'=>$userEmail, 'user_type'=>strval($type), 'user_password'=>md5($userPassword), 'user_first_name'=>$userFirstName, 
                    'user_last_name'=>$userLastName, 'user_mykad_no'=>$userMykadNo, 'group_id'=>'2', 'user_status'=>'3'));
                $userActivationKey = $this->fn_general->generateRandomString().$userId;
                Class_db::getInstance()->db_update('sys_user', array('user_activation_key'=>$userActivationKey), array('user_id'=>$userId));
                Class_db::getInstance()->db_insert('sys_user_profile', array('user_id'=>$userId, 'user_profile_contact_no'=>$userProfileContactNo));
                Class_db::getInstance()->db_insert('sys_user_role', array('user_id'=>$userId, 'role_id'=>'2'));
                $arr_checkpoint = Class_db::getInstance()->db_select('wfl_checkpoint', array('role_id'=>'2', 'checkpoint_type'=>'<>5'));
                foreach ($arr_checkpoint as $checkpoint) {
                    $checkpointId = $checkpoint['checkpoint_id'];
                    $groupId = $checkpoint['group_id'];
                    if ($groupId === '2' || is_null($groupId)) {
                        Class_db::getInstance()->db_insert('wfl_checkpoint_user', array('user_id'=>$userId, 'checkpoint_id'=>$checkpointId));
                    }
                }
            } else {
                throw new Exception('(ErrCode:0211) [' . __LINE__ . '] - Parameter type invalid ('.$type.')');  
            }
            
            return array('userId'=>$userId, 'activationKey'=>$userActivationKey);
        }
        catch(Exception $ex) {   
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0201', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
    public function activate_user ($activationInput='') {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering activate_user()');
            if (empty($activationInput)) {
                throw new Exception('(ErrCode:0212) [' . __LINE__ . '] - Parameter activationInput empty');   
            }    
            if (strlen($activationInput) < 21) { 
                throw new Exception('(ErrCode:0213) [' . __LINE__ . '] - Wrong activation key. Please click the activation link given from your email.', 31);    
            }
            
            $userId = substr($activationInput, 20);
            
            if (Class_db::getInstance()->db_count('sys_user', array('user_id'=>$userId, 'user_activation_key'=>$activationInput)) === 0) {
                throw new Exception('(ErrCode:0214) [' . __LINE__ . '] - Wrong activation key. Please click the activation link given from your email.', 31);                     
            }
            if (Class_db::getInstance()->db_count('sys_user', array('user_id'=>$userId, 'user_activation_key'=>$activationInput, 'user_status'=>'1')) === 1) {
                throw new Exception('(ErrCode:0215) [' . __LINE__ . '] - Your account already activated. Please login with email as user ID and your registered password.', 31);                     
            }
                        
            Class_db::getInstance()->db_update('sys_user', array('user_status'=>'1', 'user_time_activate'=>'Now()'), array('user_id'=>$userId));
            return $userId;
        }
        catch(Exception $ex) {   
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0201', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
    public function forgot_password ($userName='') {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering forgot_password()');
            if (empty($userName)) {
                throw new Exception('(ErrCode:0216) [' . __LINE__ . '] - Parameter userName empty');   
            } 
            
            $sys_user = Class_db::getInstance()->db_select_single('sys_user', array('user_name'=>$userName));
            if (empty($sys_user)) {
                throw new Exception('(ErrCode:0217) [' . __LINE__ . '] - User ID not exist', 31);
            }
            
            $userId = $sys_user['user_id'];
            $temporaryPassword = $this->fn_general->generateRandomString(15);
            Class_db::getInstance()->db_update('sys_user', array('user_password'=>md5($temporaryPassword)), array('user_id'=>$userId));
            
            $emailParam = array('userName'=>$userName, 'tempPassword'=>$temporaryPassword); 
            $this->fn_email->setup_email($userId, 1, $emailParam);
            
            return $userId;
        }
        catch(Exception $ex) {   
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0201', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
    public function update_profile ($userId, $put_vars) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering update_profile()');
            
            if (empty($userId)) {
                throw new Exception('(ErrCode:0218) [' . __LINE__ . '] - Parameter userId empty');   
            } 
            if (!isset($put_vars['userEmail']) || empty($put_vars['userEmail'])) {
                throw new Exception('(ErrCode:0219) [' . __LINE__ . '] - Parameter userEmail empty');
            }
            if (!isset($put_vars['userFirstName']) || empty($put_vars['userFirstName'])) {
                throw new Exception('(ErrCode:0220) [' . __LINE__ . '] - Parameter userFirstName empty');
            }
            if (!isset($put_vars['userLastName']) || empty($put_vars['userLastName'])) {
                throw new Exception('(ErrCode:0221) [' . __LINE__ . '] - Parameter userLastName empty');
            }
            if (!isset($put_vars['userMykadNo'])) {
                throw new Exception('(ErrCode:0222) [' . __LINE__ . '] - Parameter userMykadNo empty');
            }
            if (!isset($put_vars['profileContactNo'])) {
                throw new Exception('(ErrCode:0223) [' . __LINE__ . '] - Parameter profileContactNo empty');
            }
            
            $userEmail = $put_vars['userEmail'];
            $userFirstName = $put_vars['userFirstName'];
            $userLastName = $put_vars['userLastName'];
            $userMykadNo = $put_vars['userMykadNo'];
            $profileContactNo = $put_vars['profileContactNo'];
            
            if (Class_db::getInstance()->db_count('sys_user', array('user_email'=>$userEmail, 'user_id'=>'<>'.$userId)) > 0) {
                throw new Exception('(ErrCode:0224) [' . __LINE__ . '] - Emel ('.$userEmail.') telah sedia daftar', 31);
            }
            
            Class_db::getInstance()->db_update('sys_user', array('user_email'=>$userEmail, 'user_first_name'=>$userFirstName, 'user_last_name'=>$userLastName, 'user_mykad_no'=>$userMykadNo), array('user_id'=>$userId));
            Class_db::getInstance()->db_update('sys_profile', array('user_profile_contact_no'=>$profileContactNo), array('user_id'=>$userId, 'user_profile_status'=>'1'));
        }
        catch(Exception $ex) {   
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0201', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
    public function change_password ($userId, $put_vars) {
        try {
            $this->fn_general->log_debug(__FUNCTION__, __LINE__, 'Entering change_password()');
            
            if (empty($userId)) {
                throw new Exception('(ErrCode:0218) [' . __LINE__ . '] - Parameter userId empty');   
            } 
            if (!isset($put_vars['oldPassword']) || empty($put_vars['oldPassword'])) {
                throw new Exception('(ErrCode:0225) [' . __LINE__ . '] - Parameter oldPassword empty');
            }
            if (!isset($put_vars['newPassword']) || empty($put_vars['newPassword'])) {
                throw new Exception('(ErrCode:0226) [' . __LINE__ . '] - Parameter newPassword empty');
            }
            
            $oldPassword = $put_vars['oldPassword'];
            $newPassword = $put_vars['newPassword'];
            
            if (Class_db::getInstance()->db_count('sys_user', array('user_password'=>md5($oldPassword), 'user_id'=>$userId)) == 0) {
                throw new Exception('(ErrCode:0227) [' . __LINE__ . '] - Current Password is incorrect', 31);                     
            }
                        
            Class_db::getInstance()->db_update('sys_user', array('user_password'=>md5($newPassword)), array('user_id'=>$userId));
        }
        catch(Exception $ex) {   
            $this->fn_general->log_error(__FUNCTION__, __LINE__, $ex->getMessage());
            throw new Exception($this->get_exception('0201', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
}