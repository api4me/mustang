<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename msecondlevelcatg.php
* @touch date Thursday, January 02, 2014 AM11:54:19 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MSecondlevelcatg extends CI_Model {

/*{{{ load_by_store */
    public function load_by_store($sid) {
        $q = 'SELECT S.SLC_OID, S.SLC_CODE, S.SLC_NAME, S.SLC_DESCR, S.UPD_DATE FROM SECOND_LEVEL_CATG S
        INNER JOIN FIRST_LEVEL_CATG F ON F.FLC_OID=S.FLC_OID AND F.STORE_OID=?';
        $query = $this->db->query($q, $sid);

        return $query->result();
    }
/*}}}*/

}
