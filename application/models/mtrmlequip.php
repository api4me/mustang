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

/*{{{ load_all */
    private function _where($param) {
        if (@$param['STORE_CODE']) {
            $this->db->like('STORE_CODE', $param['STORE_CODE']);
        }
        if (@$param['STORE_OID']) {
            $this->db->like('STORE_OID', $param['STORE_OID']);
        }
        if (@$param['SERL_NBR']) {
            $this->db->where('SERL_NBR', $param['SERL_NBR']);
        }
    }
    public function load_all_by_company($param, $cid) {
        $param['COMPANY_OID'] = $cid;
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $query = $this->db->get('SERL_NBR');

        if ($num = $query->row(0)->num) {
            $this->db->select();
            $this->_where($param);
            $query = $this->db->get('SERL_NBR', $param['per_page'], $param['start']);
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
        $this->db->where('STORE_OID', $id);
        $this->db->where('COMPANY_OID', $cid);
        $this->db->where('STORE_STATUS <>', MA_STATUS_D);
        // ADD logic param
        $query = $this->db->get('STORE');
        return $query->row();
    }
/*}}}*/
/*{{{ save */
    public function save($param, $id) {
        if (!$id) {
            $this->db->trans_start();
            if (!$id = $this->lcommon->sequence('STORE_SEQ')) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->set('STORE_OID', $id);
            $this->db->set('CRE_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('CRE_DATE', 'now()', false);
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if (!$this->db->insert('STORE', $param)) {
                $this->db->trans_rollback();

                return false;
            }
            $this->db->trans_complete();

            return $id;
        } else {
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if ($this->db->update('STORE', $param, array('STORE_OID'=>$id))) {
                return $id;
            }
        }

        return false;
    }
/*}}}*/
/*{{{ del */
    public function del($id) {
        // TODO delete relation table
        $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
        $this->db->set('UPD_DATE', 'now()', false);
        $param['COMPANY_STATUS'] = MA_STATUS_D;
        if ($this->db->update('COMPANY', $param, array('COMPANY_OID'=>$id))) {
            return true;
        }

        return false;
    }
/*}}}*/

}
