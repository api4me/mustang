<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename mstore.php
* @touch date Thursday, January 02, 2014 AM11:54:19 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MStore extends CI_Model {

/*{{{ load_by_store */
    public function load_by_store($sid) {
        $q = 'SELECT STORE_OID
            , STORE_CODE
            , STORE_NAME
            , STORE_SRT_NAME
            , STORE_DESCR
            , STORE_TEL
            , STORE_FAX
            , STORE_PSTL_CODE
            , STORE_ADDR1
            , STORE_ADDR2
            , STORE_ADDR3
            , STORE_ADDR4
            , CTCT_PERS
            , UPD_DATE
        FROM STORE
        WHERE STORE_OID=?';
        $query = $this->db->query($q, $sid);

        return $query->row();
    }
/*}}}*/

}
