<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dish extends Ma_Controller {

    private $cid;

/*{{{ construct */
    public function __construct() {
        parent::__construct();
        $this->cid = $this->lsession->get('company')->COMPANY_OID;
    }
/*}}}*/
/*{{{ index */
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/article
	 *	- or -  
	 * 		http://example.com/index.php/article/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/article/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index($start = 0) {
        $this->load->library('twig');
        $out = array();
        $out['title'] = '菜品管理';

        $search = array();
        if ($this->input->post()) {
            $search['STORE_OID'] = $this->input->get_post('store-oid');
            $search['FLC_OID'] = $this->input->get_post('flc-oid');
            $search['SLC_OID'] = $this->input->get_post('slc-oid');
            $search['DISH_NAME'] = $this->input->get_post('dish-name');
            $search['DISH_CODE'] = $this->input->get_post('dish-code');
            $this->lsession->set('second_search', $search);
        } else {
            if ($tmp = $this->lsession->get('second_search')) {
                $search = $tmp;
            }
        }
        $this->config->load("pagination");
        $search['start'] = $start;
        $search["per_page"] = $this->config->item("per_page"); 
        $out['search'] = $search;

        $param = array();
        $this->load->model('mstore');
        $param['store'] = $this->lcommon->insert_blank($this->mstore->load_for_kv($this->cid));
        $param['first'] = $this->lcommon->insert_blank(array());
        $param['second'] = $this->lcommon->insert_blank(array());
        $out['param'] = $param;

        // The data of search
        $this->load->model('mdishes');
        if($data = $this->mdishes->load_all_by_company($search, $this->cid)) {
            $out["dish"] = $data["data"];

            // Pagaination
            $this->load->library("pagination");
            $this->pagination->uri_segment = 3;
            $this->pagination->total_rows = $data["num"];
            $this->pagination->base_url = site_url() . "/second/index";
            $out["pagination"] = $this->pagination->create_links();
        }

        $this->twig->display('dish_index.html', $out);
	}
/*}}} */
/*{{{ edit */
    public function edit($id = 0) {
        $this->load->library('twig');
        $out = array();
        $out['title'] = '菜品管理';
        
        $param = array();
        $this->load->model('mstore');
        $param['store'] = $this->lcommon->insert_blank($this->mstore->load_for_kv($this->cid));
        $param['first'] = $this->lcommon->insert_blank(array());
        $param['second'] = $this->lcommon->insert_blank(array());
        $param['prom'] = $this->lcommon->insert_blank($this->lcommon->option('yesno'));
        $param['new'] = $this->lcommon->insert_blank($this->lcommon->option('yesno'));
        $out['param'] = $param;

        $this->load->model('mdishes');
        if ($id) {
            $out['dish'] = $this->mdishes->load($id, $this->cid);
        }
        $this->twig->display('dish_edit.html', $out);

        return true;
    }
/*}}}*/
/*{{{ save */
    public function save($id = 0) {
        $out = array();
        $this->output->set_content_type('application/json');
        if (!$this->input->is_ajax_request()) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        // Validate
        $rules = array(
            array('field' => 'dish-code', 'label' => '编码', 'rules' => 'trim|required|callback__check_code'),
            array('field' => 'dish-name', 'label' => '名称', 'rules' => 'trim|required'),
            array('field' => 'disp-seq', 'label' => '展示顺序', 'rules' => 'integer'),
            array('field' => 'store-oid', 'label' => '所属门店', 'rules' => 'trim|callback__select'),
            array('field' => 'flc-oid', 'label' => '所属一级分类', 'rules' => 'trim|callback__select'),
            array('field' => 'slc-oid', 'label' => '所属二级分类', 'rules' => 'trim|callback__select'),
            array('field' => 'orig-cost', 'label' => '原价', 'rules' => 'trim|required|callback__decimal'),
            array('field' => 'cur-cost', 'label' => '现价', 'rules' => 'trim|required|callback__decimal'),
        );
        $this->load->library('form_validation');
        $this->form_validation->set_rules($rules);
        if (!$this->form_validation->run()) {
            $out['msg'] = $this->form_validation->error_string();
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array();
        // For dishes table
        $param['dish'] = array(
            'DISH_CODE' => $this->input->post('dish-code'),
            'DISH_NAME' => $this->input->post('dish-name'),
            'DISH_DESCR' => $this->input->post('dish-descr'),
            'UNIT' => $this->input->post('unit'),
            'ORIG_COST' => $this->input->post('orig-cost'),
            'CUR_COST' => $this->input->post('cur-cost'),
            'IS_PROM' => $this->input->post('is-prom'),
            'IS_NEW_PRODUCTS' => $this->input->post('is-new-products'),
        );

        // For second level dishes table
        $param['sld'] = array(
            'SLC_OID' => $this->input->post('slc-oid'),
            'DISH_OID' => $id,
            'FLC_OID' => $this->input->post('flc-oid'),
            'DISP_SEQ' => $this->input->post('disp-seq'),
        );
        // For store dishes table
        $param['sd'] = array(
            'STORE_OID' => $this->input->post('store-oid'),
            'DISH_OID' => $id,
            'COMPANY_OID' => $this->cid,
            'STORE_DISH_PRICE' => $this->input->post('cur-cost'),
        );

        $this->load->model('mdishes');
        if ($cid = $this->mdishes->save($param, $id)) {
            $out['status'] = 0;
            $out['msg'] = '保存成功';
            $out['id'] = $cid;
            $this->output->set_output(json_encode($out));

            return true;
        }

        $out['status'] = 1;
        $out['msg'] = '保存失败';
        $this->output->set_output(json_encode($out));

        return false;
    }
/*}}}*/
/*{{{ _decimal */
    public function _decimal($str) {
        if (!is_numeric($str)) {
            $this->form_validation->set_message(__FUNCTION__, '请正确填写金额。');

            return false;
        }

        return true;
    }
/*}}}*/
/*{{{ _check_code */
    public function _check_code($str) {
        $this->load->model('mdishes');
        $id = $this->input->get_post('id');
        if (!$this->mdishes->not_exists($str, $id, $this->cid)) {
            $this->form_validation->set_message(__FUNCTION__, '编码 已经存在，请换一个。');

            return false;
        }

        return true;
    }
/*}}}*/
/*{{{ image */
    public function image($id) {
        $this->load->library('twig');
        $out = array();
        $out['title'] = '菜品图片管理';
        
        $this->load->model('mdishes');
        if ($id) {
            $out['dish'] = $this->mdishes->load($id, $this->cid);
            $out['image'] = array();
            if ($tmp = $this->mdishes->load_image($id, $this->cid)) {
                foreach($tmp as $val) {
                    $file = array();
                    list($file['raw_name'], $file['file_ext']) = explode('.', $val->PIC_URL);
                    if (substr($file['raw_name'], -2) != '_i') {
                        $thumbnail = $file['raw_name'] . '_i.' . $file['file_ext'];
                    } else {
                        $thumbnail = $val->PIC_URL;
                    }

                    $out['image'][] = array(
                        'ori' => $thumbnail,
                        'name' => $thumbnail,
                        'title' => $val->PIC_NAME,
                        'title' => $val->PIC_NAME,
                        'descr' => $val->PIC_DESCR,
                        'show' => base_url() . $thumbnail,
                        'default' => $val->IS_DFLT,
                        'disp' => $val->IS_DISP,
                    );
                }
            }
        } 
        $this->twig->display('dish_image.html', $out); 
        return true;
    }
/*}}}*/
/*{{{ saveimage */
    public function saveimage($id = 0) {
        $out = array();
        $this->output->set_content_type('application/json');
        if (!$this->input->is_ajax_request()) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }
        if (!$id || !is_numeric($id)) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        // Check id in the company
        $this->load->model('mdishes');
        if (!$this->mdishes->load($id, $this->cid)) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array(
            'PIC_NAME' => $this->input->get_post('title'),
            'PIC_DESCR' => $this->input->get_post('desc'),
            'PIC_URL' => $this->input->get_post('image'),
            'IS_DFLT' => $this->input->get_post('dflt'),
            'IS_DISP' => $this->input->get_post('disp'),
            'DISP_OID' => $id,
        );

        if ($this->mdishes->saveimage($param, $id, $this->cid)) {
            $out['status'] = 0;
            $out['msg'] = '保存成功';
            $out['id'] = $id;
            $this->output->set_output(json_encode($out));

            return true;
        }

        $out['status'] = 1;
        $out['msg'] = '保存失败';
        $this->output->set_output(json_encode($out));

        return false;


    }
/*}}}*/
/*{{{ del */
    public function del($id = 0) {
        $user = $this->lsession->get('user');
        if ($user->USER_TYPE != MA_USER_TYPE_SUPER) {
            $this->index();

            return false;
        }

        $out = array();
        $this->output->set_content_type('application/json');
        if (!$this->input->is_ajax_request()) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array();

        $this->load->model('mstore');
        if ($cid = $this->mstore->del($id)) {
            $out['status'] = 0;
            $out['msg'] = '删除成功';
            $out['id'] = $cid;
            $this->output->set_output(json_encode($out));

            return true;
        }

        $out['status'] = 1;
        $out['msg'] = '删除失败';
        $this->output->set_output(json_encode($out));

        return false;
    }
/*}}}*/
/*{{{ _select */
    public function _select($str) {
        if (!$str) {
            $this->form_validation->set_message(__FUNCTION__, '%s 须选择');
            return false;
        }

        return true;
    }
/*}}}*/

/*{{{ first */
    public function first($sid) {
        $out = array();
        $this->output->set_content_type('application/json');
        if (!$this->input->is_ajax_request()) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }
        if (!$sid) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        $this->load->model('mfirstlevelcatg');
        $out['status'] = 0;
        $out['data'] = $this->lcommon->insert_blank($this->mfirstlevelcatg->load_for_kv($sid));
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/
/*{{{ second */
    public function second($fid) {
        $out = array();
        $this->output->set_content_type('application/json');
        if (!$this->input->is_ajax_request()) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }
        if (!$fid) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        $this->load->model('msecondlevelcatg');
        $out['status'] = 0;
        $out['data'] = $this->lcommon->insert_blank($this->msecondlevelcatg->load_for_kv($fid));
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/

}
