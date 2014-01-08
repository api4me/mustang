<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename mstore.php
* @touch date Thursday, January 02, 2014 AM11:54:19 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MStore extends CI_Model {

/*{{{ load_by_store */
    public function load_by_store($sid) {
        $q = 'SELECT STORE_OID
            , STORE_CODE
            , STORE_NAME
            , STORE_SRT_NAME
            , STORE_DESCR
            , STORE_TEL
            , STORE_FAX
            , STORE_PSTL_CODE
            , STORE_ADDR1
            , STORE_ADDR2
            , STORE_ADDR3
            , STORE_ADDR4
            , CTCT_PERS
            , UPD_DATE
        FROM STORE
        WHERE STORE_OID=? AND STORE_STATUS<>?';
        $query = $this->db->query($q, array($sid, MA_STATUS_D));

        return $query->row();
    }
/*}}}*/
/*{{{ load_by_code */
    public function load_by_code($code) {
        $q = 'SELECT STORE_OID
            , STORE_CODE
            , STORE_NAME
            , STORE_SRT_NAME
            , STORE_DESCR
            , STORE_TEL
            , STORE_FAX
            , STORE_PSTL_CODE
            , STORE_ADDR1
            , STORE_ADDR2
            , STORE_ADDR3
            , STORE_ADDR4
            , CTCT_PERS
            , UPD_DATE
        FROM STORE
        WHERE STORE_CODE=? AND STORE_STATUS<>?';
        $query = $this->db->query($q, array($code, MA_STATUS_D));

        return $query->row();
    }
/*}}}*/
/*{{{ load_for_kv */
    public function load_for_kv() {
        $this->db->select('STORE_OID, STORE_NAME');
        $this->db->where('STORE_STATUS <>', 'd');
        $query = $this->db->get('STORE');

        $out = array();
        $tmp = $query->result();
        foreach ($tmp as $val) {
            $out[$val->STORE_OID] = $val->STORE_NAME;
        }

        return $out;
    }
/*}}}*/

/*{{{ load_all_by_company */
    private function _where($param) {
        if (@$param['STORE_CODE']) {
            $this->db->like('STORE_CODE', $param['STORE_CODE']);
        }
        if (@$param['STORE_NAME']) {
            $this->db->like('STORE_NAME', $param['STORE_NAME']);
        }
        if (@$param['COMPANY_OID']) {
            $this->db->where('COMPANY_OID', $param['COMPANY_OID']);
        }
        $this->db->where('STORE_STATUS <>', 'd'); 
    }
    public function load_all_by_company($param, $cid) {
        $param['COMPANY_OID'] = $cid;
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $query = $this->db->get('STORE');

        if ($num = $query->row(0)->num) {
            $this->db->select();
            $this->_where($param);
            $query = $this->db->get('STORE', $param['per_page'], $param['start']);
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
/*{{{ not_exists */
    public function not_exists($str, $id, $cid) {
        $this->db->where('STORE_CODE', $str);
        $this->db->where('COMPANY_OID', $cid);
        $query = $this->db->get('STORE');
        if ($tmp = $query->row()) {
            if ($tmp->STORE_OID != $id) {
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
