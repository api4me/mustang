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
        $q = 'SELECT S.STORE_OID, S.STORE_CODE, C.COMPANY_OID, C.COMPANY_CODE, TE.IS_UPLOAD_BEHAVIORAL
            FROM TRML_EQUIP TE
            INNER JOIN STORE S ON TE.STORE_OID=S.STORE_OID AND S.STORE_STATUS<>?
            INNER JOIN COMPANY C ON C.COMPANY_OID=S.COMPANY_OID AND C.COMPANY_STATUS<>?
            WHERE TE.SERL_NBR=? AND TE.IS_ENABLED=?
            ';
        $query = $this->db->query($q, array(MA_ENABLE_Y, MA_ENABLE_Y, strval($serial), MA_ENABLE_Y));

        return $query->row();
    }
/*}}}*/

/*{{{ load_all */
    private function _where($param) {
        if (@$param['STORE_OID']) {
            $this->db->where('STORE.STORE_OID', $param['STORE_OID']);
        }
        if (@$param['SERL_NBR']) {
            $this->db->like('SERL_NBR', $param['SERL_NBR']);
        }
    }
    public function load_all_by_company($param, $cid) {
        $param['COMPANY_OID'] = $cid;
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $this->db->from('TRML_EQUIP');
        $this->db->join('STORE', "STORE.STORE_OID=TRML_EQUIP.STORE_OID AND STORE.COMPANY_OID={$cid} AND STORE.STORE_STATUS<>'d'");
        $query = $this->db->get();

        if ($num = $query->row(0)->num) {
            $this->db->select();
            $this->_where($param);
            $this->db->from('TRML_EQUIP');
            $this->db->join('STORE', "STORE.STORE_OID=TRML_EQUIP.STORE_OID AND STORE.COMPANY_OID={$cid} AND STORE.STORE_STATUS<>'d'");
            $this->db->limit($param['per_page'], $param['start']);
            $query = $this->db->get();
            $data = $query->result();

            return array(
                'num' => $num,
                'data' => $data,
            );
        }

        return false;
    }
/*}}}*/
/*{{{ load */
    public function load($id, $cid) {
        $this->db->from('TRML_EQUIP');
        $this->db->join('STORE', "STORE.STORE_OID=TRML_EQUIP.STORE_OID AND STORE.COMPANY_OID={$cid} AND STORE.STORE_STATUS<>'d'");
        $this->db->where('TRML_EQUIP.SERL_NBR', $id);
        $query = $this->db->get();

        return $query->row();
    }
/*}}}*/
/*{{{ not_exists */
    public function not_exists($str, $id, $cid) {
        $q = 'SELECT SERL_NBR 
            FROM TRML_EQUIP 
            WHERE SERL_NBR=? ';
        $query = $this->db->query($q, array($str));
        if ($tmp = $query->row()) {
            return false;
        }

        return true;
    }
/*}}}*/
/*{{{ save */
    public function save($param, $id) {
        $q = 'REPLACE INTO TRML_EQUIP(`SERL_NBR`, `IS_ENABLED`, `STORE_OID`, `IS_UPLOAD_BEHAVIORAL`) VALUES(?, ?, ?, ?)';

        return $this->db->query($q, array($param['SERL_NBR'], $param['IS_ENABLED'], $param['STORE_OID'], $param['IS_UPLOAD_BEHAVIORAL']));
    }
/*}}}*/
/*{{{ del */
    public function del($id) {
        $this->db->where_in('SERL_NBR', $id);
        if (!$this->db->delete('TRML_EQUIP')) {
            return false;
        }

        return true;
    }
/*}}}*/

}
