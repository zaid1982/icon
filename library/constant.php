<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2/18/2019
 * Time: 10:39 PM
 */

class Class_constant {

    const URL = '//localhost:8081/icon/';

    const ERR_DEFAULT = 'Error on system. Please contact Administrator!';
    const ERR_LOGIN_NOT_EXIST = 'User ID not exist';
    const ERR_LOGIN_WRONG_PASSWORD = 'Password is incorrect';
    const ERR_LOGIN_NOT_ACTIVE = 'User ID is not active. Please contact Administrator to activate.';
    const ERR_FORGOT_PASSWORD_NOT_EXIST = 'User ID not exist';
    const ERR_CHANGE_PASSWORD_WRONG_CURRENT = 'Current Password is incorrect';
    const ERR_PROBLEM_TYPE_SIMILAR = 'Problem Type already exist';
    const ERR_PROBLEM_TYPE_DEACTIVATE = 'Problem Type already inactive';
    const ERR_PROBLEM_TYPE_ACTIVATE = 'Problem Type already active';
    const ERR_PROBLEM_TYPE_DELETE = 'Problem Type cannot be deleted because it\'s been selected by complainer from Ticket';
    const ERR_WORK_TYPE_SIMILAR = 'Work Type already exist';
    const ERR_WORK_TYPE_DEACTIVATE = 'Work Type already inactive';
    const ERR_WORK_TYPE_ACTIVATE = 'Work Type already active';
    const ERR_WORK_TYPE_DELETE = 'Work Type cannot be deleted because it\'s been selected by complainer from Ticket';
    const ERR_WORK_CATEGORY_SIMILAR = 'Work Category already exist';
    const ERR_WORK_CATEGORY_DEACTIVATE = 'Work Category already inactive';
    const ERR_WORK_CATEGORY_ACTIVATE = 'Work Category already active';
    const ERR_WORK_CATEGORY_DELETE = 'Work Category cannot be deleted because it\'s been selected by complainer from Ticket';
    const ERR_SITE_SIMILAR = 'Site already exist';
    const ERR_SITE_DEACTIVATE = 'Site already inactive';
    const ERR_SITE_ACTIVATE = 'Site already active';
    const ERR_SITE_DELETE = 'Site cannot be deleted because it\'s been selected in Work Order or Vendor';
    const ERR_AREA_SIMILAR = 'Area already exist';
    const ERR_CITY_SIMILAR = 'City already exist';
    const ERR_WORKORDER_SIMILAR = 'Workorder already created. Please refresh or go back to main page.';
    const ERR_WORKORDER_SUBMITTED = 'Workorder already submitted. Please go back to main page and refresh the list.';
    const ERR_CONTRACTOR_SUBMITTED = 'Contractor already submitted. Please go back to main page and refresh the list.';
    const ERR_CONTRACTOR_NOSITE = 'Please make sure at least 1 site selected';
    const ERR_CONTRACTOR_NOSUPERVISOR = 'Please register at least 1 employee\'s supervisor';
    const ERR_CONTRACTOR_DEACTIVATE = 'Contractor already inactive';
    const ERR_CONTRACTOR_ACTIVATE = 'Contractor already active';
    const ERR_CONTRACTOR_SITE_SIMILAR = 'Site already exist';
    const ERR_EMPLOYEE_CHECK_EXIST = 'MyKad No. / Passport No. already exist in the contractor\'s employee list.';
    const ERR_EMPLOYEE_DELETE_ALREADY = 'This user already been removed before. Please refresh the employee\'s contractor list';
    const ERR_ROLE_DELETE_HAVE_TASK = 'This user cannot be removed from this roles since there are still task assigned. Please delegate the task first.';
    const ERR_ROLE_DELETE_ALONE = 'There is no other user are assigned to this role. Please assign this role to new user before remove this user form this role.';
    const ERR_USER_ADD_SIMILAR_MYKAD = 'MyKad No. / Passport No. already exist. Please use different no.';
    const ERR_USER_ADD_SIMILAR_USERNAME = 'User ID already exist. Please use different user ID.';

    const SUC_FORGOT_PASSWORD = 'Your password successfully reset. Please login with temporary password sent to your email.';
    const SUC_CHANGE_PASSWORD = 'Your password successfully changed';
    const SUC_PROBLEM_TYPE_ADD = 'Problem Type successfully added';
    const SUC_PROBLEM_TYPE_EDIT = 'Problem Type successfully updated';
    const SUC_PROBLEM_TYPE_DEACTIVATE = 'Problem Type successfully deactivated';
    const SUC_PROBLEM_TYPE_ACTIVATE = 'Problem Type successfully activated';
    const SUC_PROBLEM_TYPE_DELETE = 'Problem Type successfully deleted';
    const SUC_WORK_TYPE_ADD = 'Work Type successfully added';
    const SUC_WORK_TYPE_EDIT = 'Work Type successfully updated';
    const SUC_WORK_TYPE_DEACTIVATE = 'Work Type successfully deactivated';
    const SUC_WORK_TYPE_ACTIVATE = 'Work Type successfully activated';
    const SUC_WORK_TYPE_DELETE = 'Work Type successfully deleted';
    const SUC_WORK_CATEGORY_ADD = 'Work Category successfully added';
    const SUC_WORK_CATEGORY_EDIT = 'Work Category successfully updated';
    const SUC_WORK_CATEGORY_DEACTIVATE = 'Work Category successfully deactivated';
    const SUC_WORK_CATEGORY_ACTIVATE = 'Work Category successfully activated';
    const SUC_WORK_CATEGORY_DELETE = 'Work Category successfully deleted';
    const SUC_SITE_ADD = 'Site successfully added';
    const SUC_SITE_EDIT = 'Site successfully updated';
    const SUC_SITE_DEACTIVATE = 'Site successfully deactivated';
    const SUC_SITE_ACTIVATE = 'Site successfully activated';
    const SUC_SITE_DELETE = 'Site successfully deleted';
    const SUC_AREA_ADD = 'Area successfully added';
    const SUC_AREA_CITY = 'City successfully added';
    const SUC_WORKORDER_SAVE = 'Work order successfully updated';
    const SUC_WORKORDER_SUBMIT = 'Work order successfully submitted to contractor\'s supervisor';
    const SUC_CONTRACTOR_SAVE = 'Contractor successfully updated';
    const SUC_CONTRACTOR_SITE_ADD = 'Site successfully added';
    const SUC_CONTRACTOR_SITE_DELETE = 'Site successfully deleted';
    const SUC_CONTRACTOR_SUBMIT = 'Contractor successfully created';
    const SUC_CONTRACTOR_DEACTIVATE = 'Contractor successfully deactivated';
    const SUC_CONTRACTOR_ACTIVATE = 'Contractor successfully activated';
    const SUC_EMPLOYEE_ADD_EXISTING = 'User successfully added as contractor\'s employee';
    const SUC_EMPLOYEE_ADD_NEW = 'New contractor\'s employee successfully created';
    const SUC_EMPLOYEE_EDIT = 'Contractor\'s employee successfully updated';
    const SUC_EMPLOYEE_DELETE = 'User successfully deleted from employee list';

}