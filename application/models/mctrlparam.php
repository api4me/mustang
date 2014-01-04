<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename mtrmlequip.php
* @touch date Thursday, January 02, 2014 AM11:54:19 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MCtrlparam extends CI_Model {

/*{{{ load_by_store */
    public function load_by_store($sid) {
        $q = 'SELECT CP.PARAM_OID
            , CP.PARAM_CODE
            , CP.PARAM_DESCR
            , CP.STRING_VALUE
            , CP.UPD_DATE
            FROM CTRL_PARAM CP
            WHERE CP.COMPANY_OID IN (
                SELECT S.COMPANY_OID FROM STORE S
                WHERE S.STORE_OID=?
            )';
        $query = $this->db->query($q, $sid);

        return $query->result();
    }
/*}}}*/

}
