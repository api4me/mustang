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

class MDishes extends CI_Model {

/*{{{ load_by_store */
    public function load_by_store($id) {
        $q = 'SELECT D.DISH_OID
            , D.DISH_CODE
            , D.DISH_NAME
            , D.DISH_DESCR
            , D.UNIT
            , D.ORIG_COST
            , D.CUR_COST
            , D.IS_PROM
            , D.IS_NEW_PRODUCTS
            , D.REMARKS
            , D.UPD_DATE
            FROM DISHES D
            WHERE D.DISH_STATUS<>? AND D.DISH_OID IN (
                SELECT SD.DISH_OID
                FROM STORE_DISHES SD
                WHERE SD.STORE_OID=?
            )';
        $query = $this->db->query($q, array(MA_STATUS_D, $id));
        return $query->result();
    }
/*}}}*/
/*{{{ load_dishes_sec_by_store */
    public function load_dishes_sec_by_store($id) {
        $q = 'SELECT SLD.DISH_OID
            , SLD.SLC_OID
            , SLD.FLC_OID
            , SLD.DISP_SEQ
            FROM SECOND_LEVEL_DISHES SLD
            INNER JOIN DISHES D ON SLD.DISH_OID=D.DISH_OID
            INNER JOIN STORE_DISHES SD ON SD.DISH_OID=D.DISH_OID AND SD.STORE_OID=?
        ';
        $query = $this->db->query($q, array($id));
        return $query->result();
    }
/*}}}*/
/*{{{ load_dishes_by_store */
    public function load_dishes_by_store($id) {
        $q = 'SELECT SD.DISH_OID
            , SD.STORE_OID
            , SD.COMPANY_OID
            , SD.STORE_DISH_PRICE
            FROM STORE_DISHES SD
            WHERE SD.STORE_OID=?
        ';
        $query = $this->db->query($q, array($id));
        return $query->result();
    }
/*}}}*/
/*{{{ load_image_by_store */
    public function load_image_by_store($id) {
        $q = 'SELECT DP.PIC_OID
            , DP.PIC_NAME
            , DP.PIC_DESCR
            , DP.PIC_URL
            , DP.IS_DFLT
            , DP.IS_DISP
            , DP.DISH_OID
            FROM DISH_PICTURE DP
            INNER JOIN STORE_DISHES SD ON SD.DISH_OID=DP.DISH_OID AND SD.DISH_OID=?
        ';
        $query = $this->db->query($q, array($id));
        return $query->result();
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
        if (@$param['SLC_OID']) {
            $this->db->where('SLC.SLC_OID', $param['SLC_OID']);
        }
        if (@$param['DISH_NAME']) {
            $this->db->like('D.DISH_NAME', $param['DISH_NAME']);
        }
        if (@$param['DISH_CODE']) {
            $this->db->like('D.DISH_CODE', $param['DISH_CODE']);
        }
        if (@$param['COMPANY_OID']) {
            $this->db->where('S.COMPANY_OID', $param['COMPANY_OID']);
        }
        $this->db->where('D.DISH_STATUS <>', 'd'); 
    }
    public function load_all_by_company($param, $cid) {
        $param['COMPANY_OID'] = $cid;
        // For search
        $this->_where($param);
        $this->db->select('COUNT(1) AS num');
        $this->db->from('DISHES D');
        $this->db->join('SECOND_LEVEL_DISHES SLD', 'SLD.DISH_OID=D.DISH_OID');
        $this->db->join('SECOND_LEVEL_CATG SLC', 'SLD.SLC_OID=SLC.SLC_OID AND SLC.SLC_STATUS<>\'d\'');
        $this->db->join('FIRST_LEVEL_CATG FLC', 'FLC.FLC_OID=SLC.FLC_OID AND FLC.FLC_STATUS<>\'d\'');
        $this->db->join('STORE S', 'S.STORE_OID=FLC.STORE_OID AND S.STORE_STATUS<>\'d\'');
        $query = $this->db->get();

        if ($num = $query->row(0)->num) {
            $this->db->select('D.*, SLC.SLC_NAME, FLC.FLC_NAME, S.STORE_NAME');
            $this->_where($param);
            $this->db->from('DISHES D');
            $this->db->join('SECOND_LEVEL_DISHES SLD', 'SLD.DISH_OID=D.DISH_OID');
            $this->db->join('SECOND_LEVEL_CATG SLC', 'SLD.SLC_OID=SLC.SLC_OID AND SLC.SLC_STATUS<>\'d\'');
            $this->db->join('FIRST_LEVEL_CATG FLC', 'FLC.FLC_OID=SLC.FLC_OID AND FLC.FLC_STATUS<>\'d\'');
            $this->db->join('STORE S', 'S.STORE_OID=FLC.STORE_OID AND S.STORE_STATUS<>\'d\'');
            $this->db->order_by('SLD.DISP_SEQ, D.DISH_OID');
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
        $q = 'SELECT D.*, SLC.SLC_OID, FLC.FLC_OID, S.STORE_OID, SLD.DISP_SEQ
            FROM DISHES D
            INNER JOIN SECOND_LEVEL_DISHES SLD ON SLD.DISH_OID=D.DISH_OID
            INNER JOIN SECOND_LEVEL_CATG SLC ON SLD.SLC_OID=SLC.SLC_OID AND SLC.SLC_STATUS<>?
            INNER JOIN FIRST_LEVEL_CATG FLC ON FLC.FLC_OID=SLC.FLC_OID AND FLC.FLC_STATUS<>?
            INNER JOIN STORE S ON S.STORE_OID=FLC.STORE_OID AND S.COMPANY_OID=? AND S.STORE_STATUS<>?
            WHERE D.DISH_OID=? AND D.DISH_STATUS <>?
        ';
        $query = $this->db->query($q, array(MA_STATUS_D, MA_STATUS_D, $cid, MA_STATUS_D, $id, MA_STATUS_D));

        return $query->row();
    }
/*}}}*/
/*{{{ not_exists */
    public function not_exists($str, $id, $cid) {
        $q = 'SELECT D.DISH_OID 
            FROM DISHES D 
            INNER JOIN STORE_DISHES SD ON SD.DISH_OID=D.DISH_OID AND SD.COMPANY_OID=? 
            WHERE D.DISH_CODE=? AND D.DISH_STATUS<>?';
        $query = $this->db->query($q, array($cid, $str, MA_STATUS_D));
        if ($tmp = $query->row()) {
            if ($tmp->DISH_OID != $id) {
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
            if (!$id = $this->lcommon->sequence('DISHES_SEQ')) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->set('DISH_OID', $id);
            $this->db->set('CRE_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('CRE_DATE', 'now()', false);
            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if (!$this->db->insert('DISHES', $param['dish'])) {
                $this->db->trans_rollback();

                return false;
            }

            // Insert dish vs second level
            $param['sld']['DISH_OID'] = $id;
            if (!$this->db->insert('SECOND_LEVEL_DISHES', $param['sld'])) {
                $this->db->trans_rollback();

                return false;
            }

            // Insert store vs dish 
            if (!$this->db->insert('STORE_DISHES', $param['sd'])) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->trans_complete();

            return $id;
        } else {
            $this->db->trans_start();

            $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
            $this->db->set('UPD_DATE', 'now()', false);
            if (!$this->db->update('DISHES', $param['dish'], array('DISH_OID'=>$id))) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->delete('SECOND_LEVEL_DISHES', array(
                'SLC_OID'=> $param['sld']['SLC_OID'], 
                'DISH_OID'=> $param['sld']['DISH_OID'], 
            ));
            if (!$this->db->insert('SECOND_LEVEL_DISHES', $param['sld'])) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->delete('STORE_DISHES', array(
                'STORE_OID'=> $param['sd']['STORE_OID'], 
                'DISH_OID'=> $param['sd']['DISH_OID'], 
            ));
            if (!$this->db->insert('STORE_DISHES', $param['sd'])) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->trans_complete();

            return $id;
        }

        return false;
    }
/*}}}*/
/*{{{ load_image */
    public function load_image($id, $cid) {
        $q = 'SELECT DP.PIC_OID
            , DP.PIC_NAME
            , DP.PIC_DESCR
            , DP.PIC_URL
            , DP.IS_DFLT
            , DP.IS_DISP
            , DP.DISH_OID
            FROM DISH_PICTURE DP
            INNER JOIN STORE_DISHES SD ON SD.DISH_OID=DP.DISH_OID AND SD.COMPANY_OID=?
            WHERE DP.DISH_OID=?
        ';
        $query = $this->db->query($q, array($cid, $id));
        return $query->result();
    }
/*}}}*/
/*{{{ saveimage */
    public function saveimage($param, $id, $cid) {
        $ori_images = array();
        $ori_image_ids = array();
        if ($tmp = $this->load_image($id, $cid)) {
            foreach ($tmp as $val) {
                $ori_images[$val->PIC_URL] = $val->PIC_URL;
                $ori_image_ids[] = $val->PIC_OID;
            }
        }

        $this->db->trans_start();
        if ($ori_image_ids) {
            $this->db->where_in('PIC_OID', $ori_image_ids);
            if (!$this->db->delete('DISH_PICTURE')) {
                $this->db->trans_rollback();

                return false;
            }
        }

        $this->load->library('limage');
        $default = false;
        if (is_array($param['PIC_URL'])) {
            foreach ($param['PIC_URL'] as $key=>$val) {
                // Move image
                if (!list($ori, $cur) = explode('~', $val)) {
                    $this->db->trans_rollback();

                    return false;
                }
                if ($ori == $cur) {
                    $file = array();
                    list($file['raw_name'], $file['file_ext']) = explode('.', $cur);
                    if (substr($file['raw_name'], -2) == '_i') {
                        $cur = substr($file['raw_name'], 0, -2) . '.' . $file['file_ext'];
                    }

                    unset($ori_images[$cur]);
                } else {
                    $tmp = $this->limage->move($cur);
                    $cur = $tmp['url'];
                }

                if (!$pid = $this->lcommon->sequence('DISH_PICTURE_SEQ')) {
                    $this->db->trans_rollback();

                    return false;
                }

                // First checked is default
                $default = ($param['IS_DFLT'][$key] && !$default)? true: false;
                $data = array(
                    'PIC_OID' => $pid,
                    'PIC_NAME' => $param['PIC_NAME'][$key],
                    'PIC_DESCR' => $param['PIC_DESCR'][$key],
                    'PIC_URL' => $cur,
                    'IS_DFLT' => ($default && $param['IS_DFLT'][$key]) ? MA_ENABLE_Y : MA_ENABLE_N,
                    'IS_DISP' => $param['IS_DISP'][$key]? MA_ENABLE_Y : MA_ENABLE_N,
                    'DISH_OID' => $id,
                );
                if (!$this->db->insert('DISH_PICTURE', $data)) {
                    $this->db->trans_rollback();

                    return false;
                }
            }
        }

        $this->db->trans_complete();

        // Delete image
        if ($ori_images) {
            foreach ($ori_images as $val) {
                $this->limage->del($val);
            }
        }

        return true;
    }
/*}}}*/
/*{{{ del */
    public function del($id) {
        $this->db->trans_start();
        // SECOND_LEVEL_DISHES
        $this->db->where_in('DISH_OID', $id);
        if (!$this->db->delete('SECOND_LEVEL_DISHES')) {
            $this->db->trans_rollback();
            return false;
        }
        // DISH_PICTURE
        $this->db->where_in('DISH_OID', $id);
        $query = $this->db->get('DISH_PICTURE');
        $images = $query->result();

        $this->db->where_in('DISH_OID', $id);
        if (!$this->db->delete('DISH_PICTURE')) {
            $this->db->trans_rollback();
            return false;
        }
        // STORE_DISHES
        $this->db->set('DISH_STATUS', MA_STATUS_D);
        $this->db->set('UPD_BY', $this->lsession->get('user')->USER_OID);
        $this->db->set('UPD_DATE', 'now()', false);
        $this->db->where_in('DISH_OID', $id);
        if (!$this->db->update('DISHES')) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_complete();

        // Delete pic
        if ($images) {
            foreach ($images as $key => $val) {
                $this->load->library('limage');
                $this->limage->del($val->PIC_URL);
            }
        }
        return true;
    }
/*}}}*/

}
