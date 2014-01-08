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
        $q = 'SELECT S.SLC_OID, S.SLC_CODE, S.SLC_NAME, S.SLC_DESCR, S.DISP_SEQ, S.SLC_OID, S.UPD_DATE, S.FLC_OID FROM SECOND_LEVEL_CATG S
        INNER JOIN FIRST_LEVEL_CATG F ON F.FLC_OID=S.FLC_OID AND F.STORE_OID=?';
        $query = $this->db->query($q, $sid);

        return $query->result();
    }
/*}}}*/
/*{{{ load_for_kv */
    public function load_for_kv($fid) {
        $this->db->select('SLC_OID, SLC_NAME');
        $this->db->where('SLC_STATUS <>', 'd');
        $this->db->where('FLC_OID', $fid);
        $query = $this->db->get('SECOND_LEVEL_CATG');

        $out = array();
        $tmp = $query->result();
        foreach ($tmp as $val) {
            $out[$val->SLC_OID] = $val->SLC_NAME;
        }

        return $out;
    }
/*}}}*/

/*{{{ load_all_by_company */
    private function _where($param) {
        if (@$param['STORE_OID']) {
            $this->db->where('FLC.STORE_OID', $param['STORE_OID']);
        }
        if (@$param['FLC_OID']) {
            $this->db->where('FLC.FLC_OID', $param['FLC_OID']);
        }
        if (@$param['SLC_NAME']) {
            $this->db->like('SLC.SLC_NAME', $param['SLC_NAME']);
        }
        if (@$param['SLC_CODE']) {
            $this->db->like('SLC.SLC_CODE', $param['SLC_CODE']);
        }
        if (@$param['COMPANY_OID']) {
            $this->db->where('S.COMPANY_OID', $param['COMPANY_OID']);
        }
        $this->db->where('SLC.SLC_STATUS <>', 'd'); 
    }
    public function load_all_by_company($param, $cid) {
        $param['COMPANY_OID'] = $cid;
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $this->db->from('SECOND_LEVEL_CATG SLC');
        $this->db->join('FIRST_LEVEL_CATG FLC', 'FLC.FLC_OID=SLC.FLC_OID AND FLC.FLC_STATUS<>\'d\'');
        $this->db->join('STORE S', 'S.STORE_OID=FLC.STORE_OID AND S.STORE_STATUS<>\'d\'');
        $query = $this->db->get();

        if ($num = $query->row(0)->num) {
            $this->db->select('SLC.*, FLC.FLC_NAME, S.STORE_NAME, U.USER_NAME');
            $this->_where($param);
            $this->db->from('SECOND_LEVEL_CATG SLC');
            $this->db->join('FIRST_LEVEL_CATG FLC', 'FLC.FLC_OID=SLC.FLC_OID AND FLC.FLC_STATUS<>\'d\'');
            $this->db->join('STORE S', 'S.STORE_OID=FLC.STORE_OID AND S.STORE_STATUS<>\'d\'');
            $this->db->join('USER_PROFILE U', 'U.USER_OID=SLC.CRE_BY', 'left');
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
        $this->db->from('SECOND_LEVEL_CATG SLC');
        $this->db->join('FIRST_LEVEL_CATG FLC', 'FLC.FLC_OID=SLC.FLC_OID AND FLC.FLC_STATUS<>\'d\'');
        $this->db->join('STORE S', "S.STORE_OID=FLC.STORE_OID AND S.COMPANY_OID={$cid} AND S.STORE_STATUS<>'d'");
        $this->db->where('SLC.SLC_STATUS <>', MA_STATUS_D);
        $this->db->where('SLC.SLC_OID', $id);
        $query = $this->db->get();

        return $query->row();
    }
/*}}}*/
/*{{{ not_exists */
    public function not_exists($str, $id, $cid) {
        $q = 'SELECT SLC.SLC_OID 
            FROM SECOND_LEVEL_CATG SLC 
            INNER JOIN FIRST_LEVEL_CATG FLC ON FLC.FLC_OID=SLC.FLC_OID AND FLC.FLC_STATUS<>? 
            INNER JOIN STORE S ON S.STORE_OID=FLC.STORE_OID AND S.COMPANY_OID=? AND S.STORE_STATUS<>?
            WHERE SLC.SLC_CODE=? AND SLC.SLC_STATUS<>?';
        $query = $this->db->query($q, array(MA_STATUS_D, $cid, MA_STATUS_D, $str, MA_STATUS_D));
        if ($tmp = $query->row()) {
            if ($tmp->SLC_OID != $id) {
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
            if (!$id = $this->lcommon->sequence('SECOND_LEVEL_CATG_SEQ')) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->set('SLC_OID', $id);
            $this->db->set('CRE_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('CRE_DATE', 'now()', false);
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if (!$this->db->insert('SECOND_LEVEL_CATG', $param)) {
                $this->db->trans_rollback();

                return false;
            }
            $this->db->trans_complete();

            return $id;
        } else {
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if ($this->db->update('SECOND_LEVEL_CATG', $param, array('SLC_OID'=>$id))) {
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
