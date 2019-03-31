<?php

class Class_sql
{

    function __construct()
    {
        // 1010 - 1019
    }

    private function get_exception($codes, $function, $line, $msg)
    {
        if ($msg != '') {
            $pos = strpos($msg, '-');
            if ($pos !== false)
                $msg = substr($msg, $pos + 2);
            return "(ErrCode:" . $codes . ") [" . __CLASS__ . ":" . $function . ":" . $line . "] - " . $msg;
        } else
            return "(ErrCode:" . $codes . ") [" . __CLASS__ . ":" . $function . ":" . $line . "]";
    }

    public function get_sql($title)
    {
        try {
            if ($title == 'vw_profile') {
                $sql = "SELECT
                    sys_user.*,
                    sys_user_profile.user_contact_no,
                    sys_user_profile.user_email,
                    sys_address.address_desc,
                    sys_address.address_postcode,
                    sys_address.address_city,
                    ref_state.state_desc
                FROM sys_user
                LEFT JOIN sys_user_profile ON sys_user_profile.user_id = sys_user.user_id
                LEFT JOIN sys_address ON sys_address.address_id = sys_user_profile.address_id
                LEFT JOIN ref_state ON ref_state.state_id = sys_address.state_id";
            } else if ($title == 'vw_roles') {
                $sql = "SELECT
                    ref_role.role_id AS roleId, 
                    ref_role.role_desc AS roleDesc, 
                    ref_role.role_type AS roleType
                FROM (SELECT DISTINCT(role_id) FROM sys_user_role WHERE user_id = [user_id] GROUP BY role_id) roles
                INNER JOIN ref_role ON roles.role_id = ref_role.role_id AND role_status = 1";
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
                WHERE nav_status = 1 AND (ISNULL(sys_nav_second.nav_second_id) OR nav_second_status = 1)
                ORDER BY nav_role.turn";
            } else if ($title === 'dt_ticket') {
                $sql = "SELECT
                    icn_ticket.*,
                    icn_problemtype.problemtype_desc,
                    icn_worktype.worktype_desc,
                    icn_workcategory.workcategory_desc,
                    ref_status.status_desc
                FROM icn_ticket
                LEFT JOIN icn_problemtype ON icn_problemtype.problemtype_id = icn_ticket.problemtype_id
                LEFT JOIN icn_workcategory ON icn_workcategory.workcategory_id = icn_ticket.workcategory_id
                LEFT JOIN icn_worktype ON icn_worktype.worktype_id = icn_workcategory.worktype_id
                LEFT JOIN ref_status ON ref_status.status_id = icn_ticket.ticket_status
                ";
            } else if ($title === 'vw_ticket_by_status') {
                $sql = "SELECT
                    ticket_status, COUNT(*) AS total
                FROM icn_ticket
                GROUP BY ticket_status";
            } else if ($title === 'vw_company_stats') {
                $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(IF(company_status = 1, 1, 0)) AS total_active,
                    SUM(IF(company_created_by IS NULL, 1, 0)) AS total_spdp
                FROM sem_company";
            } else if ($title === 'vw_ticket_worktype') {
                $sql = "SELECT icn_ticket.* 
                FROM icn_ticket 
                LEFT JOIN icn_workcategory ON icn_workcategory.workcategory_id = icn_ticket.workcategory_id";
            } else if ($title === 'vw_workorder_worktype') {
                $sql = "SELECT icn_workorder.* 
                FROM icn_workorder 
                LEFT JOIN icn_workcategory ON icn_workcategory.workcategory_id = icn_workorder.workcategory_id";
            } else if ($title === 'vw_workorder') {
                $sql = "SELECT 
                  icn_workorder.*,
                  CONCAT(sys_user.user_first_name, ' ', sys_user.user_last_name) AS requester_name,
                  sys_user_profile.user_contact_no AS requester_phone
                FROM icn_workorder 
                LEFT JOIN sys_user ON sys_user.user_id = icn_workorder.workorder_created_by
                LEFT JOIN sys_user_profile ON sys_user_profile.user_id = sys_user.user_id AND sys_user_profile.user_profile_status = 1";
            } else if ($title === 'vw_workorder_by_status') {
                $sql = "SELECT
                    workorder_status, COUNT(*) AS total
                FROM icn_workorder
                GROUP BY workorder_status";
            } else if ($title === 'vw_contractor') {
                $sql = "SELECT
                    icn_contractor.*,
                    sys_address.address_desc,
                    sys_address.address_postcode,
                    sys_address.address_city,
                    ref_state.state_id,
                    ref_state.state_desc,
                    CONCAT(sys_user.user_first_name, ' ', sys_user.user_last_name) AS created_by,
                    contractor_site.sites                    
                FROM icn_contractor
                LEFT JOIN sys_address ON sys_address.address_id = icn_contractor.address_id
                LEFT JOIN ref_state ON ref_state.state_id = sys_address.state_id
                LEFT JOIN sys_user ON sys_user.user_id = icn_contractor.contractor_created_by
                LEFT JOIN 
                    (SELECT contractor_id, GROUP_CONCAT(site_id) AS sites 
                    FROM icn_contractor_site 
                    GROUP BY contractor_id) contractor_site ON contractor_site.contractor_id = icn_contractor.contractor_id";
            } else if ($title === 'vw_contractor_user') {
                $sql = "SELECT
                    user_role.*,
                    CONCAT(sys_user.user_first_name, ' ', sys_user.user_last_name) AS user_fullname,
                    sys_user_profile.user_contact_no,
                    sys_user_profile.user_email,
                    sys_user.user_status
                FROM (SELECT user_id, group_id, GROUP_CONCAT(role_id) AS roles 
                    FROM sys_user_role 
                    WHERE role_id IN (5,6) AND group_id = [group_id]
                    GROUP BY user_id, group_id) user_role
                LEFT JOIN sys_user ON sys_user.user_id = user_role.user_id
                LEFT JOIN sys_user_profile ON sys_user_profile.user_id = user_role.user_id AND user_profile_status = 1";
            } else if ($title === 'vw_user_profile') {
                $sql = "SELECT 
                    sys_user.*,
                    sys_user_profile.user_contact_no,
                    sys_user_profile.user_email
                FROM sys_user 
                LEFT JOIN sys_user_profile ON sys_user_profile.user_id = sys_user.user_id AND user_profile_status = 1";
            } else if ($title === 'vw_check_assigned') {
                $sql = "SELECT 
                    wfl_task_assign.* 
                FROM wfl_task_assign  
                INNER JOIN wfl_transaction ON wfl_transaction.transaction_id = wfl_task_assign.transaction_id AND transaction_status = 4";
            } else {
                throw new Exception($this->get_exception('0098', __FUNCTION__, __LINE__, 'Sql not exist : ' . $title));
            }
            return $sql;
        } catch (Exception $e) {
            if ($e->getCode() == 30) {
                $errCode = 32;
            } else {
                $errCode = $e->getCode();
            }
            throw new Exception($this->get_exception('0099', __FUNCTION__, __LINE__, $e->getMessage()), $errCode);
        }
    }

}

?>
