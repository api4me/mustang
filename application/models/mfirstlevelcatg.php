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
        $q = 'SELECT FLC.FLC_OID, FLC.FLC_CODE, FLC.FLC_NAME, FLC.FLC_DESCR, FLC.DISP_SEQ, SFL.STORE_OID, FLC.UPD_DATE 
            FROM FIRST_LEVEL_CATG FLC
            JOIN STORE_FIRST_LEVEL SFL ON SFL.FLC_OID=FLC.FLC_OID AND SFL.STORE_OID=?
            WHERE FLC.FLC_STATUS<>?';
        $query = $this->db->query($q, array($sid, MA_STATUS_D));

        return $query->result();
    }
/*}}}*/
/*{{{ load_for_kv */
    public function load_for_kv($cid) {
        $this->db->select('FLC_OID, FLC_NAME');
        $this->db->where('FLC_STATUS <>', 'd');
        $this->db->where('COMPANY_OID', $cid);
        $query = $this->db->get('FIRST_LEVEL_CATG');

        $out = array();
        $tmp = $query->result();
        foreach ($tmp as $val) {
            $out[$val->FLC_OID] = $val->FLC_NAME;
        }

        return $out;
    }
/*}}}*/

/*{{{ load_all_by_company */
    private function _where($param) {
        if (@$param['STORE_OID']) {
             $this->db->where('SFL.STORE_OID', $param['STORE_OID']);
             $this->db->join('STORE_FIRST_LEVEL SFL', 'SFL.FLC_OID=FLC.FLC_OID');
        }
        if (@$param['FLC_NAME']) {
            $this->db->like('FLC.FLC_NAME', $param['FLC_NAME']);
        }
        if (@$param['FLC_CODE']) {
            $this->db->like('FLC.FLC_CODE', $param['FLC_CODE']);
        }
        if (@$param['COMPANY_OID']) {
            $this->db->where('FLC.COMPANY_OID', $param['COMPANY_OID']);
        }
        $this->db->where('FLC.FLC_STATUS <>', 'd'); 
    }
    public function load_all_by_company($param, $cid) {
        $param['COMPANY_OID'] = $cid;
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $this->db->from('FIRST_LEVEL_CATG FLC');
        $query = $this->db->get();

        if ($num = $query->row(0)->num) {
            $this->db->select('FLC.*, U.USER_NAME');
            $this->_where($param);
            $this->db->from('FIRST_LEVEL_CATG FLC');
            $this->db->join('USER_PROFILE U', 'U.USER_OID=FLC.CRE_BY', 'left');
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
        $this->db->from('FIRST_LEVEL_CATG FLC');
        $this->db->where('FLC.COMPANY_OID', $cid);
        $this->db->where('FLC.FLC_STATUS <>', MA_STATUS_D);
        $this->db->where('FLC.FLC_OID', $id);
        $query = $this->db->get();

        return $query->row();
    }
/*}}}*/
/*{{{ load_vs_store */
    public function load_vs_store($id) {
        $this->db->select('STORE_OID');
        $this->db->where('FLC_OID', $id);
        $query = $this->db->get('STORE_FIRST_LEVEL');

        $out = array();
        foreach ($query->result() as $val) {
            $out[] = $val->STORE_OID;
        }
        
        return $out;
    }
/*}}}*/
/*{{{ not_exists */
    public function not_exists($str, $id, $cid) {
        $q = 'SELECT FLC.FLC_OID 
            FROM FIRST_LEVEL_CATG FLC 
            WHERE FLC.COMPANY_OID=? AND FLC.FLC_CODE=? AND FLC.FLC_STATUS<>?';
        $query = $this->db->query($q, array($cid, $str, MA_STATUS_D));
        if ($tmp = $query->row()) {
            if ($tmp->FLC_OID != $id) {
                return false;
            }
        }

        return true;
    }
/*}}}*/
/*{{{ save */
    public function save($param, $id) {
        if (!$id) {
            $this->db->trans_start();
            if (!$id = $this->lcommon->sequence('FIRST_LEVEL_CATG_SEQ')) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->set('FLC_OID', $id);
            $this->db->set('CRE_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('CRE_DATE', 'now()', false);
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if (!$this->db->insert('FIRST_LEVEL_CATG', $param)) {
                $this->db->trans_rollback();

                return false;
            }
            $this->db->trans_complete();

            return $id;
        } else {
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if ($this->db->update('FIRST_LEVEL_CATG', $param, array('FLC_OID'=>$id))) {
                return $id;
            }
        }

        return false;
    }
/*}}}*/
/*{{{ savestore */
    public function savestore($store, $cid, $id) {
        $this->db->trans_start();

        $this->db->delete('STORE_FIRST_LEVEL', array(
            'FLC_OID'=> $id, 
        ));
        foreach ($store as $val) {
            $param = array(
                'STORE_OID' => $val,
                'FLC_OID' => $id,
                'COMPANY_OID' => $cid,
            );
            if (!$this->db->insert('STORE_FIRST_LEVEL', $param)) {
                $this->db->trans_rollback();

                return false;
            }
        }

        $this->db->trans_complete();

        return true;
    }
/*}}}*/
/*{{{ del */
    public function del($id) {
        $this->db->trans_start();
        // STORE_FIRST_LEVEL
        $this->db->where_in('FLC_OID', $id);
        if (!$this->db->delete('STORE_FIRST_LEVEL')) {
            $this->db->trans_rollback();
            return false;
        }
        // SECOND_LEVEL_DISHES
        $this->db->where_in('FLC_OID', $id);
        if (!$this->db->delete('SECOND_LEVEL_DISHES')) {
            $this->db->trans_rollback();
            return false;
        }
        // SECOND LEVEL CATEGORY
        $this->db->set('SLC_STATUS', MA_STATUS_D);
        $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
        $this->db->set('UPD_DATE', 'now()', false);
        $this->db->where_in('FLC_OID', $id);
        if (!$this->db->update('SECOND_LEVEL_CATG')) {
            $this->db->trans_rollback();
            return false;
        }
        // First LEVEL CATEGORY
        $this->db->set('FLC_STATUS', MA_STATUS_D);
        $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
        $this->db->set('UPD_DATE', 'now()', false);
        $this->db->where_in('FLC_OID', $id);
        if (!$this->db->update('FIRST_LEVEL_CATG')) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_complete();

        return true;
    }
/*}}}*/

}
