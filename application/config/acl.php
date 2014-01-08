<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename acl.php
* @touch date Tuesday, May 14, 2013 AM01:55:52 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0'
* @version 1.0.0
*/


/*
|--------------------------------------------------------------------------
| Acl setting
|--------------------------------------------------------------------------
|
| Prevent user to access the controller or action of controller.
| The struct if below:
| $config['role'] = array(
|     'user type' => array(
|         'control1' => array('*'),
|         'control2' => array('action1', 'action2', ...)
| If prevent all of controller, please add the '*' in array of controller.
|
| Role: super, admin, leader, appraiser, waiter, buyer, sell, guest
|
*/
$config['role'] = array(
    's' => array(
    ),
    'a' => array(
        'user' => array('*'),
        'home' => array('add', 'edit', 'del', 'entry'),
    ),
);
