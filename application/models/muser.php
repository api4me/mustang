<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename ../models/user.php
* @touch date Wednesday, May 15, 2013 PM12:21:50 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0'
* @version 1.0.0
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MUser extends CI_Model {

/*{{{ login */
    /**
     * @param param['name']: user name
     * @param param['pwd']: password of user, the md5 value
     */
    public function login($param) {
        $this->db->from('USER_PROFILE');
        $this->db->where('LOGIN_ID', $param['username']);
        $this->db->where('USER_STATUS', 'Y');
        $this->db->where('LOGIN_PWD', $param['pwd']);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            if ($out = $query->row()) {
                $this->lsession->set('user', $out);
                // Set login time
                $param = array();
                $this->db->set('PRE_LOGIN_DATE', 'LOGIN_DATE', false);
                $this->db->set('LOGIN_DATE', 'now()', false);
                $this->db->set('FAILED_ATTEMPT', 0);
                $this->save($param, $out->LOGIN_ID);
            }
            return $out;
        }

        return false;
    }
/*}}}*/
/*{{{ load_all */
    private function _where($param) {
        $user = $this->lsession->get('user');
        $this->db->where('USER_OID <>', $user->USER_OID);
        if (@$param['LOGIN_ID']) {
            $this->db->like('LOGIN_ID', $param['LOGIN_ID']);
            $this->db->like('USER_NAME', $param['LOGIN_ID']);
        }
        if (@$param['USER_TYPE']) {
            $this->db->where('USER_TYPE', $param['USER_TYPE']);
        }
        if (@$param['USER_STATUS']) {
            $this->db->where('USER_STATUS', $param['USER_STATUS']);
        }
    }
    public function load_all($param) {
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $query = $this->db->get('USER_PROFILE');

        if ($num = $query->row()->num) {
            $this->db->select();
            $this->_where($param);
            $query = $this->db->get('USER_PROFILE', $param['per_page'], $param['start']);
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
    public function load($id) {
        $this->db->where('USER_OID', $id);
        $this->db->where('USER_STATUS <>', MA_STATUS_D);
        $query = $this->db->get('USER_PROFILE');
        return $query->row();
    }
/*}}}*/
/*{{{ load_for_deal */
    public function load_for_deal($param) {
        $this->db->select('id, username');
        $key = '(username=\'' . $this->db->escape_str($param['key']) 
            . '\' OR phone=\'' . $this->db->escape_str($param['key'])
            . '\')';
        $this->db->where($key);
        $this->db->where('role', 'buyer');
        $this->db->where('enable <>', 'D');
        $query = $this->db->get('##user');
        return $query->row();
    }
/*}}}*/
/*{{{ save */
    public function save($param, $id) {
        if (!$id) {
            $this->db->trans_start();
            if (!$id = $this->lcommon->sequence('USER_PROFILE_SEQ')) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->set('USER_OID', $id);
            $this->db->set('CRE_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('CRE_DATE', 'now()', false);
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if (!$this->db->insert('USER_PROFILE', $param)) {
                $this->db->trans_rollback();

                return false;
            }
            $this->db->trans_complete();

            return $id;
        } else {
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if ($this->db->update('USER_PROFILE', $param, array('USER_OID'=>$id))) {
                return $id;
            }
        }

        return false;
    }
/*}}}*/
/*{{{ change_enable */
    public function change_enable($param, $id) {
        if (!$id) {
            $this->db->set('updated', 'now()', false);
            return $this->db->update('##user', $param, array('id'=>$id));
        }

        return false;
    }
/*}}}*/
/*{{{ get_data_by_phone */
    public function get_data_by_phone($phone) {
        $this->db->where('phone', $phone);
        $this->db->where('enable <>', 'D');
        $query = $this->db->get('##user');
        return $query->row();
    }
/*}}}*/
/*{{{ exists_username */
    public function exists_username($str, $id = 0) {
        if ($id) {
            $this->db->where('id <>', $id);
        }
        $this->db->where('username', $str);
        $this->db->where('enable <>', 'D');
        $query = $this->db->get('##user');
        return ($query->row()) ? true : false;
    }
/*}}}*/
/*{{{ exists_phone */
    public function exists_phone($str, $id = 0) {
        if ($id) {
            $this->db->where('id <>', $id);
        }
        $this->db->where('phone', $str);
        $this->db->where('enable <>', 'D');
        $query = $this->db->get('##user');
        return ($query->row()) ? true : false;
    }
/*}}}*/
/*{{{ exists_email */
    public function exists_email($str, $id = 0) {
        if ($id) {
            $this->db->where('id <>', $id);
        }
        $this->db->where('email', $str);
        $this->db->where('enable <>', 'D');
        $query = $this->db->get('##user');
        return ($query->row()) ? true : false;
    }
/*}}}*/

}
