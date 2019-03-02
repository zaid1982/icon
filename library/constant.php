<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2/18/2019
 * Time: 10:39 PM
 */

class Class_constant {

    const URL = '//localhost/icon/';

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

}