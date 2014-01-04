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

/*{{{ load_all */
    private function _where($param) {
        if (isset($param["phone"]) && $param["phone"]) {
            $this->db->where("phone", $param["phone"]);
        }
        if (isset($param["status"]) && $param["status"] == "invalid") {
            $this->db->where("status", "invalid");
        } else {
            // Where new add
            $this->db->where("status", "add");
        }
    }
    public function load_all($param) {
        // For search
        $this->_where($param);
        $this->db->select("COUNT(1) AS num");
        $query = $this->db->get("##prebook");

        if ($num = $query->row(0)->num) {
            $this->db->select();
            $this->_where($param);
            $query = $this->db->get("##prebook", $param["per_page"], $param["start"]);
            $data = $query->result();

            return array(
                "num" => $num,
                "data" => $data,
            );
        }

        return false;
    }
/*}}}*/
/*{{{ load */
    public function load($id) {
        $this->db->where("id", $id);
        $query = $this->db->get("##prebook");
        return $query->row();
    }
/*}}}*/
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
            WHERE D.DISH_OID IN (
                SELECT SD.DISH_OID
                FROM STORE_DISHES SD
                WHERE SD.STORE_OID=?
            )';
        $query = $this->db->query($q, array($id));
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
            , SD.STORE_COST
            FROM STORE_DISHES SD
            WHERE SD.STORE_OID=?
        ';
        $query = $this->db->query($q, array($id));
        return $query->result();
    }
/*}}}*/
/*{{{ save */
    public function save($param, $id) {
        if (!$id) {
            $this->db->set("created", "now()", false);
            $this->db->set("updated", "now()", false);
            if ($this->db->insert("##prebook", $param)) {
                return $this->db->insert_id();
            }
        } else {
            $this->db->set("updated", "now()", false);
            return $this->db->update("##prebook", $param, array("id"=>$id));
        }

        return false;
    }
/*}}}*/

}
