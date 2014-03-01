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

class MCtrlparam extends CI_Model {

/*{{{ load_by_company */
    public function load_by_company($code) {
        $q = 'SELECT CP.PARAM_OID
            , CP.PARAM_CODE
            , CP.PARAM_DESCR
            , CP.STRING_VALUE
            , CP.UPD_DATE
            FROM CTRL_PARAM CP
            INNER JOIN COMPANY C ON C.COMPANY_OID=CP.COMPANY_OID AND C.COMPANY_STATUS<>?
            WHERE C.COMPANY_CODE=?
            ';
        $query = $this->db->query($q, array(MA_STATUS_D, $code));

        return $query->result();
    }
/*}}}*/

/*{{{ load_all */
    private function _where($param) {
        if (@$param['PARAM_CODE']) {
            $this->db->like('PARAM_CODE', $param['PARAM_CODE']);
        }
        if (@$param['COMPANY_OID']) {
            $this->db->where('COMPANY_OID', $param['COMPANY_OID']);
        }
    }
    public function load_all_by_company($param, $cid) {
        $param['COMPANY_OID'] = $cid;
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $this->db->from('CTRL_PARAM');
        $query = $this->db->get();

        if ($num = $query->row(0)->num) {
            $this->db->select();
            $this->_where($param);
            $this->db->from('CTRL_PARAM');
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
        $this->db->from('CTRL_PARAM');
        $this->db->where('PARAM_OID', $id);
        $this->db->where('COMPANY_OID', $cid);
        $query = $this->db->get();

        return $query->row();
    }
/*}}}*/
/*{{{ not_exists */
    public function not_exists($str, $id, $cid) {
        $this->db->where('PARAM_CODE', $str);
        $this->db->where('COMPANY_OID', $cid);
        $query = $this->db->get('CTRL_PARAM');
        if ($tmp = $query->row()) {
            if ($tmp->PARAM_OID != $id) {
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
            if (!$id = $this->lcommon->sequence('CTRL_PARAM_SEQ')) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->set('PARAM_OID', $id);
            $this->db->set('CRE_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('CRE_DATE', 'now()', false);
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if (!$this->db->insert('CTRL_PARAM', $param)) {
                $this->db->trans_rollback();

                return false;
            }
            $this->db->trans_complete();

            return $id;
        } else {
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if ($this->db->update('CTRL_PARAM', $param, array('PARAM_OID'=>$id))) {
                return $id;
            }
        }

        return false;
    }
/*}}}*/
/*{{{ del */
    public function del($id) {
        $this->db->where_in('PARAM_OID', $id);
        if (!$this->db->delete('CTRL_PARAM')) {
            return false;
        }

        return true;
    }
/*}}}*/

}
