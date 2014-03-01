<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename mcompany.php
* @touch date Thursday, January 02, 2014 AM11:54:19 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MCompany extends CI_Model {

/*{{{ load_by_store */
    public function load_by_store($sid) {
        $q = 'SELECT C.COMPANY_OID
            , C.COMPANY_CODE
            , C.COMPANY_NAME
            , C.COMPANY_SRT_NAME
            , C.COMPANY_LOGO
            , C.COMPANY_DESCR
            , C.COMPANY_TEL
            , C.COMPANY_FAX
            , C.COMPANY_PSTL_CODE
            , C.COMPANY_ADDR1
            , C.COMPANY_ADDR2
            , C.COMPANY_ADDR3
            , C.COMPANY_ADDR4
            , C.CTCT_PERS
            , C.CTCT_TEL
            , C.CTCT_MOBILE
            , C.CTCT_EMAIL
            , C.UPD_DATE
        FROM COMPANY C
        WHERE C.COMPANY_OID IN (SELECT COMPANY_OID FROM STORE S WHERE S.STORE_OID=?)';
        $query = $this->db->query($q, $sid);

        return $query->row();
    }
/*}}}*/
/*{{{ load_all */
    private function _where($param) {
        if (@$param['COMPANY_CODE']) {
            $this->db->like('COMPANY_CODE', $param['COMPANY_CODE']);
        }
        if (@$param['COMPANY_NAME']) {
            $this->db->like('COMPANY_NAME', $param['COMPANY_NAME']);
        }
    }
    public function load_all($param) {
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $query = $this->db->get('COMPANY');

        if ($num = $query->row(0)->num) {
            $this->db->select();
            $this->_where($param);
            $query = $this->db->get('COMPANY', $param['per_page'], $param['start']);
            $data = $query->result();

            return array(
                'num' => $num,
                'data' => $data,
            );
        }

        return false;
    }
/*}}}*/

/*{{{ load_for_choose */
    public function load_for_choose() {
        $query = $this->db->select('COMPANY_OID , COMPANY_NAME')->where('COMPANY_STATUS <>', MA_STATUS_D)->get('COMPANY');
        $out = array();
        if ($tmp = $query->result()) {
            foreach ($tmp as $val) {
                $out[$val->COMPANY_OID] = $val->COMPANY_NAME;
            }
        }

        return $out;
    }
/*}}}*/
/*{{{ load */
    public function load($id) {
        $this->db->where('COMPANY_OID', $id);
        $this->db->where('COMPANY_STATUS <>', MA_STATUS_D);
        // ADD logic param
        $query = $this->db->get('COMPANY');
        return $query->row();
    }
/*}}}*/
/*{{{ not_exists */
    public function not_exists($str, $id) {
        $this->db->where('COMPANY_CODE', $str);
        $this->db->where('COMPANY_STATUS <>', MA_STATUS_D);
        $query = $this->db->get('COMPANY');
        if ($tmp = $query->row()) {
            if ($tmp->COMPANY_OID != $id) {
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
            if (!$id = $this->lcommon->sequence('COMPANY_SEQ')) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->set('COMPANY_OID', $id);
            $this->db->set('CRE_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('CRE_DATE', 'now()', false);
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if (!$this->db->insert('COMPANY', $param)) {
                $this->db->trans_rollback();

                return false;
            }
            $this->db->trans_complete();

            return $id;
        } else {
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if ($this->db->update('COMPANY', $param, array('COMPANY_OID'=>$id))) {
                return $id;
            }
        }

        return false;
    }
/*}}}*/
/*{{{ del */
    public function del($id) {
        // Do not dele the relation table for prevent making a mistake
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
