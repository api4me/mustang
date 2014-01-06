<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename mprebook.php
* @touch date Thursday, January 02, 2014 AM10:25:40 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MBehavioraldata extends CI_Model {

/*{{{ save */
    public function insert($param) {
        $this->db->trans_start();
        foreach ($param as $val) {
            if (!$id = $this->lcommon->sequence('STORE_SEQ')) {
                $this->db->trans_rollback();

                return false;
            }
            $val['DB_OID'] = $id;
            if (!$this->db->insert("BEHAVIORAL_DATA", $val)) {
                $this->db->trans_rollback();

                return false;
            }
        }
        $this->db->trans_complete();

        return true;
    }
/*}}}*/

}
