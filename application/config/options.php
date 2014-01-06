<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename options.php
* @touch date Sunday, January 05, 2014 AM05:07:10 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
if (! defined('BASEPATH')) exit('No direct script access allowed');

// Enable
$config['enable'] = array(
    MA_ENABLE_Y => '可用',
    MA_ENABLE_N => '停用',
);

// User type
$config['role'] = array(
    MA_USER_TYPE_SUPER => '超级管理员',
    MA_USER_TYPE_ADMIN => '普通管理员',
);

