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

class MTrmlequip extends CI_Model {

/*{{{ load_by_serial */
    public function load_by_serial($serial) {
        $q = 'SELECT S.STORE_OID, S.STORE_CODE 
            FROM STORE S 
            INNER JOIN TRML_EQUIP TE 
            ON TE.STORE_OID=S.STORE_OID AND TE.SERL_NBR=? AND IS_ENABLED=1';
        $query = $this->db->query($q, $serial);

        return $query->row();
    }
/*}}}*/

}
