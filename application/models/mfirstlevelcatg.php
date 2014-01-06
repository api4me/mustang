<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename mfirstlevelcatg.php
* @touch date Thursday, January 02, 2014 AM11:54:19 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MFirstlevelcatg extends CI_Model {

/*{{{ load_by_store */
    public function load_by_store($sid) {
        $q = 'SELECT FLC_OID, FLC_CODE, FLC_NAME, FLC_DESCR, DISP_SEQ, STORE_OID, UPD_DATE FROM FIRST_LEVEL_CATG WHERE STORE_OID=?';
        $query = $this->db->query($q, $sid);

        return $query->result();
    }
/*}}}*/

}
