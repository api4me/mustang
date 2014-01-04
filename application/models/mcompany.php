<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename mcompany.php
* @touch date Thursday, January 02, 2014 AM11:54:19 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MCompany extends CI_Model {

/*{{{ load_by_store */
    public function load_by_store($sid) {
        $q = 'SELECT C.COMPANY_OID
            , C.COMPANY_CODE
            , C.COMPANY_NAME
            , C.COMPANY_SRT_NAME
            , C.COMPANY_LOGO
            , C.COMPANY_DESCR
            , C.COMPANY_TEL
            , C.COMPANY_FAX
            , C.COMPANY_PSTL_CODE
            , C.COMPANY_ADDR1
            , C.COMPANY_ADDR2
            , C.COMPANY_ADDR3
            , C.COMPANY_ADDR4
            , C.CTCT_PERS
            , C.CTCT_TEL
            , C.CTCT_MOBILE
            , C.CTCT_EMAIL
            , C.UPD_DATE
        FROM COMPANY C
        WHERE C.COMPANY_OID IN (SELECT COMPANY_OID FROM STORE S WHERE S.STORE_OID=?)';
        $query = $this->db->query($q, $sid);

        return $query->row();
    }
/*}}}*/

}
