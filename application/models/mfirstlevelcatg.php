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
/*{{{ load_for_kv */
    public function load_for_kv($sid) {
        $this->db->select('FLC_OID, FLC_NAME');
        $this->db->where('FLC_STATUS <>', 'd');
        $this->db->where('STORE_OID', $sid);
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
            $this->db->where('FLC.STORE_OID', $param['STORE_OID']);
        }
        if (@$param['FLC_NAME']) {
            $this->db->like('FLC.FLC_NAME', $param['FLC_NAME']);
        }
        if (@$param['FLC_CODE']) {
            $this->db->like('FLC.FLC_CODE', $param['FLC_CODE']);
        }
        if (@$param['COMPANY_OID']) {
            $this->db->where('S.COMPANY_OID', $param['COMPANY_OID']);
        }
        $this->db->where('FLC.FLC_STATUS <>', 'd'); 
    }
    public function load_all_by_company($param, $cid) {
        $param['COMPANY_OID'] = $cid;
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $this->db->from('FIRST_LEVEL_CATG FLC');
        $this->db->join('STORE S', 'S.STORE_OID=FLC.STORE_OID AND S.STORE_STATUS<>\'d\'');
        $query = $this->db->get();

        if ($num = $query->row(0)->num) {
            $this->db->select('FLC.*, S.STORE_NAME, U.USER_NAME');
            $this->_where($param);
            $this->db->from('FIRST_LEVEL_CATG FLC');
            $this->db->join('STORE S', 'S.STORE_OID=FLC.STORE_OID AND S.STORE_STATUS<>\'d\'');
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
        $this->db->join('STORE S', "S.STORE_OID=FLC.STORE_OID AND S.COMPANY_OID={$cid} AND S.STORE_STATUS<>'d'");
        $this->db->where('FLC.FLC_STATUS <>', MA_STATUS_D);
        $this->db->where('FLC.FLC_OID', $id);
        $query = $this->db->get();

        return $query->row();
    }
/*}}}*/
/*{{{ not_exists */
    public function not_exists($str, $id, $cid) {
        $q = 'SELECT FLC.FLC_OID 
            FROM FIRST_LEVEL_CATG FLC 
            INNER JOIN STORE S ON S.STORE_OID=FLC.STORE_OID AND S.COMPANY_OID=? 
            WHERE FLC.FLC_CODE=? AND FLC.FLC_STATUS<>?';
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
