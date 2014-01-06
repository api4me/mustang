<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename api.php
* @touch date Thursday, January 02, 2014 AM07:52:35 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/
class Api extends CI_Controller {

/*{{{ index */
    public function index() {
        $out = array();
        $out["data"] = "load...";
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));
    }
/*}}}*/
/*{{{ validate*/
    private function validate($serial, $store) {
        $this->load->model('mtrmlequip');
        if ($data = $this->mtrmlequip->load_by_serial($serial)) {
            if ($data->STORE_CODE == $store) {
                return $data->STORE_OID;
            }
        }

        $out = array();
        $out['status'] = 1;
        $out['msg'] = '暂时不能访问系统，请联系管理员。';

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return false;
    }
/*}}}*/
/*{{{ getMappedStoreInfo */
    public function getMappedStoreInfo() {
        $this->syncStoreInfo();
    }
/*}}}*/
/*{{{ syncDishesInfo */
    /**
     * 终端菜谱信息同步接口
     *
     */
    public function syncDishesInfo() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        $out['status'] = 0;
        $out['data'] = array();
        $this->load->model('mdishes');
        if ($data = $this->mdishes->load_by_store($store_id)) {
            $out['data'] = $data;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ updateSystemParamSetupInfo */
    /**
     * 系统参数设置更新接口
     *
     */
    public function updateSystemParamSetupInfo() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        $out['status'] = 0;
        $out['data'] = array();
        $this->load->model('mctrlparam');
        if ($data = $this->mctrlparam->load_by_store($store_id)) {
            $out['data'] = $data;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ syncFirstLevelCategoryInfo */
    /**
     * 一级分类信息同步接口
     *
     */
    public function syncFirstLevelCategoryInfo() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        $out['status'] = 0;
        $out['data'] = array();
        $this->load->model('mfirstlevelcatg');
        if ($data = $this->mfirstlevelcatg->load_by_store($store_id)) {
            $out['data'] = $data;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ syncSecondLevelCategoryInfo */
    /**
     * 二级分类信息同步接口
     *
     */
    public function syncSecondLevelCategoryInfo() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        $out['status'] = 0;
        $out['data'] = array();
        $this->load->model('msecondlevelcatg');
        if ($data = $this->msecondlevelcatg->load_by_store($store_id)) {
            $out['data'] = $data;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ syncCompanyInfo */
    /**
     * 公司信息同步接口
     *
     */
    public function syncCompanyInfo() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        $out['status'] = 0;
        $out['data'] = array();
        $this->load->model('mcompany');
        if ($data = $this->mcompany->load_by_store($store_id)) {
            $out['data'] = $data;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ syncStoreInfo */
    /**
     * 门店信息同步接口
     *
     */
    public function syncStoreInfo() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        $out['status'] = 0;
        $out['data'] = array();
        $this->load->model('mstore');
        if ($data = $this->mstore->load_by_store($store_id)) {
            $out['data'] = $data;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ syncDishesAndSecLvlCateInfo */
    /**
     * 菜品分类关联信息同步接口
     *
     */
    public function syncDishesAndSecLvlCateInfo() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        $out['status'] = 0;
        $out['data'] = array();
        $this->load->model('mdishes');
        if ($data = $this->mdishes->load_dishes_sec_by_store($store_id)) {
            $out['data'] = $data;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ syncStoreAndDishesInfo */
    /**
     * 门店菜品关联信息同步接口
     *
     */
    public function syncStoreAndDishesInfo() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        $out['status'] = 0;
        $out['data'] = array();
        $this->load->model('mdishes');
        if ($data = $this->mdishes->load_dishes_by_store($store_id)) {
            $out['data'] = $data;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ syncDishImageInfo */
    /**
     * 提供接口供终端同步门店菜品图片信息
     *
     */
    public function syncDishImageInfo() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        $out['status'] = 0;
        $out['data'] = array();
        $this->load->model('mdishes');
        if ($data = $this->mdishes->load_image_by_store($store_id)) {
            $out['data'] = $data;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ uploadUserBehaviorData */
    /**
      * 上传记录在iPad终端的用户行为数据。
      *
      */
    public function uploadUserBehaviorData() {
        $in = array(
            'serial' => $this->input->get_post('serialNumber'), 
            'store' => $this->input->get_post('storeCode'), 
            'data' => $this->input->get_post('data'),
        );

        if (!$store_id = $this->validate($in['serial'], $in['store'])) {
            return false;
        }

        $out = array();
        if (!$in['data'] = json_decode($in['data'], true)) {
            $out['status'] = 1;
            $out['msg'] = '系统开小差了。';
        }

        $out = array();
        $out['status'] = 0;
        $this->load->model('mbehavioraldata');
        if ($data = $this->mbehavioraldata->insert($in['data'])) {
            $out['msg'] = '上传成功。';
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/

}
