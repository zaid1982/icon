<?php

class Class_general {
     
    private $log_dir = '';
    
    function __construct()
    {
        $config = parse_ini_file('library/config.ini');
        $this->log_dir = $config['log_dir'];
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
           
    public function log_debug ($function, $line, $msg) {
        $debugMsg = date("Y/m/d h:i:sa")." [".__CLASS__.":".$function.":".$line."] - ".$msg."\r\n";
        error_log($debugMsg, 3, $this->log_dir.'/debug/debug_'.date("Ymd").'.log');
    }
    
    public function log_error ($function, $line, $msg) {
        $debugMsg = date("Y/m/d h:i:sa")." [".__CLASS__.":".$function.":".$line."] - ".$msg."\r\n";
        error_log($debugMsg, 3, $this->log_dir.'/debug/debug_'.date("Ymd").'.log');
        error_log($debugMsg, 3, $this->log_dir.'/error/error_'.date("Ymd").'.log');
    }
    
    public function clear_null ($param, $replaced='') {
        try {
            if (is_null($param)) {
                return $replaced;
            }
            return $param;
        } catch(Exception $ex) {
            $this->log_error(__FUNCTION__, __LINE__, $ex->getMessage());            
            throw new Exception($this->get_exception('0051', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
    public function save_audit ($audit_action_id='', $user_id='', $remark='') {
        try {
            $this->log_debug(__FUNCTION__, __LINE__, 'Entering save_audit()');
            if ($audit_action_id === '') {
                throw new Exception('(ErrCode:0052) [' . __LINE__ . '] - Parameter audit_action_id empty');   
            }
            
            $place = '';
            $ipaddress = '';
            
            if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']!='') {
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            } else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']!='') {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if(isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED']!='') {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            } else if(isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR']!='') {
                $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            } else if(isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED']!='') {
                $ipaddress = $_SERVER['HTTP_FORWARDED'];
            } else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']!='') {
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            } else {
                $ipaddress = 'UNKNOWN';
            }
            
            if (!in_array($ipaddress, array('', 'UNKNOWN', '::1'), true)) {
                $details = json_decode(file_get_contents("http://ipinfo.io/$ipaddress/json"));
                if (isset($details->city)) {
                    $place = $details->city;
                }
            }
            return Class_db::getInstance()->db_insert('sys_audit', array('audit_action_id'=>$audit_action_id, 'user_id'=>$user_id, 'audit_ip'=>$ipaddress, 'audit_place'=>$place, 'audit_remark'=>$remark));
        } catch(Exception $ex) {
            $this->log_error(__FUNCTION__, __LINE__, $ex->getMessage());            
            throw new Exception($this->get_exception('0051', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
    public function generateRandomString ($length = 20) {
        try {
            $this->log_debug(__FUNCTION__, __LINE__, 'Entering generateRandomString()');
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        } catch(Exception $ex) {
            $this->log_error(__FUNCTION__, __LINE__, $ex->getMessage());            
            throw new Exception($this->get_exception('0051', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
    public function updateVersion ($versionId='') {
        try {
            $this->log_debug(__FUNCTION__, __LINE__, 'Entering updateVersion()');
            if ($versionId === '') {
                throw new Exception('(ErrCode:0053) [' . __LINE__ . '] - Parameter versionId empty');   
            }            
            Class_db::getInstance()->db_update('sys_version', array('version_no'=>'++'), array('version_id'=>$versionId));
        } catch(Exception $ex) {
            $this->log_error(__FUNCTION__, __LINE__, $ex->getMessage());            
            throw new Exception($this->get_exception('0051', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
    public function folderExist($folder) {
        $path = realpath($folder);
        return ($path !== false AND is_dir($path)) ? $path : false;
    }
    
    public function uploadDocument ($uploadDetails='', $documentId='' , $userId='') {
        try {
            $this->log_debug(__FUNCTION__, __LINE__, 'Entering uploadDocument()');
            
            if (empty($uploadDetails)) {
                throw new Exception('(ErrCode:0054) [' . __LINE__ . '] - Array uploadDetails empty');   
            }
            if (empty($documentId)) {
                throw new Exception('(ErrCode:0055) [' . __LINE__ . '] - Parameter documentId empty');   
            } 
            if (empty($userId)) {
                throw new Exception('(ErrCode:0056) [' . __LINE__ . '] - Parameter userId empty');   
            } 
            
            if (!array_key_exists('name', $uploadDetails) || empty($uploadDetails['name'])) {
                throw new Exception('(ErrCode:0057) [' . __LINE__ . '] - Parameter upload name empty');  
            }
            if (!array_key_exists('size', $uploadDetails) || empty($uploadDetails['size'])) {
                throw new Exception('(ErrCode:0058) [' . __LINE__ . '] - Parameter upload size empty');  
            }
            if (!array_key_exists('type', $uploadDetails) || empty($uploadDetails['type'])) {
                throw new Exception('(ErrCode:0059) [' . __LINE__ . '] - Parameter upload type empty');  
            }
            if (!array_key_exists('data', $uploadDetails) || empty($uploadDetails['data'])) {
                throw new Exception('(ErrCode:0060) [' . __LINE__ . '] - Parameter upload data empty');  
            }
            
            $uploadUplname = $uploadDetails['name']; 
            $uploadFilesize = $uploadDetails['size']; 
            $uploadBlobType = $uploadDetails['type']; 
            $uploadBlobData = $uploadDetails['data'];             
            $pos = strrpos($uploadUplname,'.');
            $uploadExtension = $pos !== false ? substr($uploadUplname, $pos+1) : ' - '; 
            
            $uploadId = Class_db::getInstance()->db_insert('sys_upload', array('document_id'=>$documentId, 'upload_uplname'=>$uploadUplname, 'upload_filesize'=>$uploadFilesize, 'upload_blob_type'=>$uploadBlobType,
                'upload_blob_data'=>$uploadBlobData, 'upload_extension'=>$uploadExtension, 'upload_created_by'=>$userId));
            $uploadFilename = 'f_'.(10000 + intval($uploadId));
            $uploadFolder = 'upload/'.$documentId.'/'.(floor(intval($uploadId)/1000));
            if (!$this->folderExist($uploadFolder)) {
                mkdir ($uploadFolder,0777, true);   
            }               
            file_put_contents($uploadFolder.'/'.$uploadFilename.'.'.$uploadExtension, base64_decode($uploadBlobData));
            Class_db::getInstance()->db_update('sys_upload', array('upload_filename'=>$uploadFilename, 'upload_folder'=>$uploadFolder), array('upload_id'=>$uploadId));
                        
            return $uploadId;            
        } catch(Exception $ex) {
            $this->log_error(__FUNCTION__, __LINE__, $ex->getMessage());            
            throw new Exception($this->get_exception('0051', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
    
    public function getDocument ($uploadId='') {
        try {
            $this->log_debug(__FUNCTION__, __LINE__, 'Entering getDocument()');            
            if (empty($uploadId)) {
                throw new Exception('(ErrCode:0061) [' . __LINE__ . '] - Parameter uploadId empty');   
            }
            
            $sysUpload = Class_db::getInstance()->db_select_single('sys_upload', array('upload_id'=>$uploadId), null, 1);
            $document = Class_db::getInstance()->db_select_single('ref_document', array('document_id'=>$sysUpload['document_id']), null, 1);
            
            return 
                array(
                    'documentDesc'=>$document['document_desc'], 
                    'documentFilename'=>$sysUpload['upload_uplname'],
                    'documentSrc'=>'//localhost/spdp/seminar/api/'.$sysUpload['upload_folder'].'/'.$sysUpload['upload_filename'].'.'.$sysUpload['upload_extension']
                );            
        } catch(Exception $ex) {
            $this->log_error(__FUNCTION__, __LINE__, $ex->getMessage());            
            throw new Exception($this->get_exception('0051', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
        
    public function convertMysqlDate ($date='') {
        try {
            $this->log_debug(__FUNCTION__, __LINE__, 'Entering convertMysqlDate()');            
            if (empty($date)) {
                throw new Exception('(ErrCode:0062) [' . __LINE__ . '] - Parameter date empty');   
            }
            
            $newDate = '';
            $arrMonth = array('', 'Januari', 'Februari', 'Mac', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember');
            $dateSplit = explode('-', $date);
            if (sizeof($dateSplit) === 3) {                
                $newDate = intval($dateSplit[2]).' '.$arrMonth[intval($dateSplit[1])].' '.$dateSplit[0];
            }
            return $newDate;
        } catch(Exception $ex) {
            $this->log_error(__FUNCTION__, __LINE__, $ex->getMessage());            
            throw new Exception($this->get_exception('0051', __FUNCTION__, __LINE__, $ex->getMessage()), $ex->getCode());
        }
    }
}