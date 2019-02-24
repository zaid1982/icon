<?php
/**
 * Created by PhpStorm.
 * User: Zaid
 * Date: 2/23/2019
 * Time: 9:52 AM
 */
require_once 'library/constant.php';
require_once 'function/db.php';
require_once 'function/f_general.php';
require_once 'function/f_login.php';
require_once 'function/f_ticket.php';
require_once 'function/f_task.php';

$constant = new Class_constant();
$fn_general = new Class_general();
$fn_login = new Class_login();
$fn_ticket = new Class_ticket();
$fn_task = new Class_task();
$api_name = 'api_ticket';
$is_transaction = false;
$form_data = array('success'=>false, 'result'=>'', 'error'=>'', 'errmsg'=>'');
$result = '';

/* Error code range - 2400 */
try {
    Class_db::getInstance()->db_connect();
    $request_method = $_SERVER['REQUEST_METHOD'];
    //$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $fn_general->log_debug($api_name, __LINE__, 'Request method = '.$request_method);

    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception('(ErrCode:2401) [' . __LINE__ . '] - Parameter Authorization empty');
    }
    $jwt_data = $fn_login->check_jwt($headers['Authorization']);

    if ('POST' === $request_method) {
        $action = filter_input(INPUT_POST, 'action');
        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;

        if ($action === 'create_id_temp') {
            $result = $fn_ticket->create_new_ticket($jwt_data->userId);
            $fn_general->save_audit('7', $jwt_data->userId, 'ticket_id = ' . $result);
        }
        else if ($action === 'upload_image') {
            $ticketId = filter_input(INPUT_POST, 'ticketId');
            $uploadName = filter_input(INPUT_POST, 'title');
            $uploadUplname = filter_input(INPUT_POST, 'filename');
            $uploadFilesize = filter_input(INPUT_POST, 'filesize');
            $uploadBlobType = filter_input(INPUT_POST, 'blobType');
            $uploadBlobData = filter_input(INPUT_POST, 'blobData');

            $imageDetails = array(
                'ticketId'=>$ticketId,
                'uploadName'=>$uploadName,
                'uploadUplName'=>$uploadUplname,
                'uploadFilesize'=>$uploadFilesize,
                'uploadBlobType'=>$uploadBlobType,
                'uploadBlobData'=>$uploadBlobData
            );

            $result = $fn_ticket->upload_ticket_image($imageDetails, $jwt_data->userId);
            $fn_general->save_audit('8', $jwt_data->userId, 'upload_id = ' . $result['uploadId']);
        } else {
            throw new Exception('(ErrCode:2402) [' . __LINE__ . '] - Parameter action ('.$action.') invalid');
        }

        Class_db::getInstance()->db_commit();
        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('PUT' === $request_method) {
        $ticketId = filter_input(INPUT_GET, 'ticketId');
        $put_data = file_get_contents("php://input");
        parse_str($put_data, $put_vars);
        $action = $put_vars['action'];

        if (empty($ticketId)) {
            throw new Exception('(ErrCode:2403) [' . __LINE__ . '] - Parameter userId empty');
        }

        Class_db::getInstance()->db_beginTransaction();
        $is_transaction = true;

        if ($action === 'submit_new_ticket') {
            $ticketNo = $fn_ticket->generate_ticket_no();
            $taskId = $fn_task->create_new_task('1', $jwt_data->userId, '3', '3', $ticketNo);
            $fn_ticket->update_ticket($ticketId, $put_vars, $jwt_data->userId);
            $fn_task->submit_task($taskId, $jwt_data->userId, $status='9');
            $fn_ticket->submit_ticket($ticketId, $ticketNo, $taskId);
            $fn_general->save_audit('9', $jwt_data->userId, 'ticket_no = '.$ticketNo);
            $form_data['errmsg'] = 'Your ticket has been successfully submitted. Your ticket number is '.$ticketNo.' for future reference.';
        } else {
            throw new Exception('(ErrCode:2402) [' . __LINE__ . '] - Parameter action (' . $action . ') invalid');
        }

        Class_db::getInstance()->db_commit();
        $form_data['result'] = $result;
        $form_data['success'] = true;
    }
    else if ('GET' === $request_method) {
        $ticketId = filter_input(INPUT_GET, 'ticketId');
        $result = array();

        if (!is_null($ticketId)) {

        }
        else {
            if (isset($headers['Simple'])) {
                $tickets = Class_db::getInstance()->db_select('icn_ticket', array('ticket_status'=>'<>5'));
                foreach ($tickets as $ticket) {
                    $row_result['ticketId'] = $ticket['ticket_id'];
                    $row_result['ticketNo'] = $fn_general->clear_null($ticket['ticket_no']);
                    $row_result['problemtypeId'] = $ticket['problemtype_id'];
                    $row_result['workcategoryId'] = $ticket['workcategory_id'];
                    $row_result['transactionId'] = $ticket['transaction_id'];
                    $row_result['ticketStatus'] = $ticket['ticket_status'];
                    $row_result['ticketTimeSubmit'] = str_replace('-', '/', $ticket['ticket_time_submit']);
                    array_push($result, $row_result);
                }
            }
            else {
                $tickets = Class_db::getInstance()->db_select('dt_ticket');
                foreach ($tickets as $ticket) {
                    $row_result['ticketId'] = $ticket['ticket_id'];
                    $row_result['ticketNo'] = $fn_general->clear_null($ticket['ticket_no']);
                    $row_result['ticketComplaint'] = $fn_general->clear_null($ticket['ticket_complaint']);
                    $row_result['ticketStatus'] = $ticket['ticket_status'];
                    array_push($result, $row_result);
                }
            }
        }

        $form_data['result'] = $result;
        $form_data['success'] = true;
    } else {
        throw new Exception('(ErrCode:2400) [' . __LINE__ . '] - Wrong Request Method');
    }
    Class_db::getInstance()->db_close();
} catch (Exception $ex) {
    if ($is_transaction) {
        Class_db::getInstance()->db_rollback();
    }
    Class_db::getInstance()->db_close();
    $form_data['error'] = substr($ex->getMessage(), strpos($ex->getMessage(), '] - ') + 4);
    if ($ex->getCode() === 31) {
        $form_data['errmsg'] = substr($ex->getMessage(), strpos($ex->getMessage(), '] - ') + 4);
    } else {
        $form_data['errmsg'] = $constant::ERR_DEFAULT;
    }
    $fn_general->log_error($api_name, __LINE__, $ex->getMessage());
}

echo json_encode($form_data);