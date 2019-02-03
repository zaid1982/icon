<?php

class Class_sql {
     
    function __construct()
    {
        // 1010 - 1019
    }
    
    private function get_exception($codes, $function, $line, $msg) {
        if ($msg != '') {            
            $pos = strpos($msg,'-');
            if ($pos !== false)   
                $msg = substr($msg, $pos+2); 
            return "(ErrCode:".$codes.") [".__CLASS__.":".$function.":".$line."] - ".$msg;
        } else
            return "(ErrCode:".$codes.") [".__CLASS__.":".$function.":".$line."]";
    }
    
    public function get_sql ($title) {
        try {     
            if ($title == 'vw_roles') { 
                $sql = "SELECT
                        ref_role.role_id AS roleId, 
                        ref_role.role_desc AS roleDesc
                    FROM sys_user_role
                    INNER JOIN ref_role ON sys_user_role.role_id = ref_role.role_id AND role_status = 1";
            } else if ($title === 'vw_menu') { 
                $sql = "SELECT 
                        sys_nav.nav_id,
                        sys_nav.nav_desc,
                        sys_nav.nav_icon,
                        sys_nav.nav_page,
                        sys_nav_second.nav_second_id,
                        sys_nav_second.nav_second_desc,
                        sys_nav_second.nav_second_page
                    FROM
                        (SELECT
                                nav_id, nav_second_id, MIN(nav_role_turn) AS turn
                        FROM sys_nav_role
                        WHERE role_id IN ([roles])
                        GROUP BY nav_id, nav_second_id) AS nav_role
                    LEFT JOIN sys_nav ON sys_nav.nav_id = nav_role.nav_id
                    LEFT JOIN sys_nav_second ON sys_nav_second.nav_second_id = nav_role.nav_second_id
                    WHERE nav_status = 1  AND (ISNULL(sys_nav_second.nav_second_id) OR nav_second_status = 1)
                    ORDER BY nav_role.turn";
            } else if ($title === 'vw_company_spdp') { 
                $sql = "SELECT 
                    profile.profile_email,
                    profile.profile_contactNo,
                    profile.profile_faxNo,
                    address.address_line1,
                    address.address_line2,
                    address.address_line3,
                    address.address_postcode,
                    address.city_id,	
                    ref_city.state_id,
                    pengguna.jenisPerniagaan_id,
                    sijil.* 
                FROM sijil
                LEFT JOIN wf_transaction ON wf_transaction.wfTrans_id = sijil.wfTrans_id
                LEFT JOIN pengguna ON pengguna.pengguna_id = sijil.pengguna_id
                LEFT JOIN profile ON profile.profile_id = pengguna.profile_id
                LEFT JOIN address ON address.address_id = profile.address_id
                LEFT JOIN ref_city ON ref_city.city_id = address.city_id";
            } else if ($title === 'dt_sem_company') { 
                $sql = "SELECT
                    sem_company.*,
                    sys_address.address_desc,
                    sys_address.address_postcode,
                    sys_address.state_id,
                    sys_address.city_id
                FROM sem_company
                LEFT JOIN sys_address ON sys_address.address_id = sem_company.address_id";
            } else if ($title === 'vw_company_by_state') { 
                $sql = "SELECT
                    sys_address.state_id, COUNT(*) AS total
                FROM sem_company
                LEFT JOIN sys_address ON sys_address.address_id = sem_company.address_id
                GROUP BY sys_address.state_id";
            } else if ($title === 'vw_company_stats') { 
                $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(IF(company_status = 1, 1, 0)) AS total_active,
                    SUM(IF(company_created_by IS NULL, 1, 0)) AS total_spdp
                FROM sem_company";
            } else if ($title === 'dt_sem_seminar') { 
                $sql = "SELECT 
                    sem_seminar.*,
                    gr.total_peserta AS total_peserta
                FROM sem_seminar
                LEFT JOIN (
                    SELECT seminar_id, COUNT(*) AS total_peserta FROM sem_participant GROUP BY seminar_id
                ) gr ON gr.seminar_id = sem_seminar.seminar_id";
            } else if ($title === 'vg_sem_seminar_company') {     
                $sql = "SELECT 
                    sem_seminar_company.*,
                    gr.total_participant AS total_participant
                FROM sem_seminar_company
                LEFT JOIN (
                    SELECT company_id, seminar_id, COUNT(*) AS total_participant FROM sem_participant GROUP BY company_id, seminar_id
                ) gr ON gr.company_id = sem_seminar_company.company_id AND gr.seminar_id = sem_seminar_company.seminar_id";
            } else if ($title === 'vw_seminar_company_stats') { 
                $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(IF(company_created_by IS NULL, 1, 0)) AS total_spdp
                FROM sem_seminar_company
                LEFT JOIN sem_company ON sem_company.company_id = sem_seminar_company.company_id";
            } else if ($title === 'vw_seminar_invitation_email') {
                $sql = "SELECT 
                    sem_seminar_company.*,
                    sem_company.company_email,
                    ref_state.state_desc,
                    seminar_date,
                    seminar_time_start,
                    seminar_time_end,
                    sys_location.location_desc,
                    sys_location.location_longitude,
                    sys_location.location_latitude,
                    CONCAT(upload_folder, '/', upload_filename, '.', upload_extension) AS upload_location
                FROM sem_seminar_company
                LEFT JOIN sem_company ON sem_company.company_id = sem_seminar_company.company_id
                LEFT JOIN sem_seminar ON sem_seminar.seminar_id = sem_seminar_company.seminar_id
                LEFT JOIN ref_state ON ref_state.state_id = sem_seminar.state_id
                LEFT JOIN sys_location ON sys_location.location_id = sem_seminar.location_id
                LEFT JOIN sys_upload ON sys_upload.upload_id = sem_seminar.upload_id";
            } else if ($title === 'vw_certificate') {
                $sql = "SELECT
                    sem_certificate.*,
                    sem_participant.seminar_id,
                    sem_participant.company_id,
                    sem_participant.participant_idno,
                    sem_participant.participant_email,
                    sem_participant.participant_phone
                FROM sem_certificate
                LEFT JOIN sem_participant ON sem_participant.certificate_id = sem_certificate.certificate_id";
            } else if ($title === 'vw_company_cert') {
                $sql = "SELECT
                    company_id
                FROM sem_participant
                GROUP BY company_id";
            } else if ($title === 'vw_certificate_email') {
                $sql = "SELECT
                    sem_participant.*,
                    ref_state.state_desc,
                    seminar_date,
                    seminar_time_start,
                    seminar_time_end,
                    sys_location.location_desc,
                    sem_certificate.certificate_name,
                    sem_company.company_name,
                    CONCAT(pdf_folder, '/', pdf_filename) AS pdf_location,
                    sem_certificate.certificate_id
                FROM sem_participant
                LEFT JOIN sem_certificate ON sem_certificate.certificate_id = sem_participant.certificate_id
                LEFT JOIN sem_seminar ON sem_seminar.seminar_id = sem_participant.seminar_id
                LEFT JOIN ref_state ON ref_state.state_id = sem_seminar.state_id
                LEFT JOIN sys_location ON sys_location.location_id = sem_seminar.location_id
                LEFT JOIN sem_company ON sem_company.company_id = sem_participant.company_id
                LEFT JOIN sys_pdf ON sys_pdf.pdf_id = sem_certificate.pdf_id";
            } else if ($title === 'vw_cert_by_seminar') {
                $sql = "SELECT
                    sem_participant.seminar_id AS seminar_id, 
                    DATE_FORMAT(seminar_date, '%e %b %Y') AS dates,
                    COUNT(*) AS total
                FROM sem_participant
                LEFT JOIN sem_seminar ON sem_seminar.seminar_id = sem_participant.seminar_id
                WHERE participant_status IN (15,16)
                GROUP BY seminar_id, seminar_date
                ORDER BY dates DESC LIMIT 5";
            } else if ($title === 'vw_participant_by_seminar') {
                $sql = "SELECT
                    sem_participant.seminar_id AS seminar_id, 
                    DATE_FORMAT(seminar_date, '%e %b %Y') AS dates,
                    COUNT(*) AS total
                FROM sem_participant
                LEFT JOIN sem_seminar ON sem_seminar.seminar_id = sem_participant.seminar_id
                GROUP BY seminar_id, seminar_date
                ORDER BY dates DESC LIMIT 5";
            } else {
                throw new Exception($this->get_exception('0098', __FUNCTION__, __LINE__, 'Sql not exist : '.$title)); 
            }
            return $sql;
        }
        catch(Exception $e) {
            if ($e->getCode() == 30) { $errCode = 32; } else { $errCode = $e->getCode(); }
            throw new Exception($this->get_exception('0099', __FUNCTION__, __LINE__, $e->getMessage()), $errCode);
        }
    }
    
}

?>
