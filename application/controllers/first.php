<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class First extends Ma_Controller {

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
        $out['title'] = '一级分类管理';

        $search = array();
        if ($this->input->post()) {
            $search['STORE_OID'] = $this->input->get_post('store-oid');
            $search['FLC_NAME'] = $this->input->get_post('flc-name');
            $search['FLC_CODE'] = $this->input->get_post('flc-code');
            $this->lsession->set('first_search', $search);
        } else {
            if ($tmp = $this->lsession->get('first_search')) {
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
        $out['param'] = $param;

        // The data of search
        $this->load->model('mfirstlevelcatg');
        if($data = $this->mfirstlevelcatg->load_all_by_company($search, $this->cid)) {
            $out["first"] = $data["data"];

            // Pagaination
            $this->load->library("pagination");
            $this->pagination->uri_segment = 3;
            $this->pagination->total_rows = $data["num"];
            $this->pagination->base_url = site_url() . "/first/index";
            $out["pagination"] = $this->pagination->create_links();
        }

        $this->twig->display('first_index.html', $out);
	}
/*}}} */
/*{{{ edit */
    public function edit($id = 0) {
        $this->load->library('twig');
        $out = array();
        $out['title'] = '一级分类管理';
        
        $param = array();
        $this->load->model('mstore');
        $out['param'] = $param;

        $this->load->model('mfirstlevelcatg');
        if ($id) {
            $out['first'] = $this->mfirstlevelcatg->load($id, $this->cid);
        }
        $this->twig->display('first_edit.html', $out);

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
            array('field' => 'flc-code', 'label' => '编码', 'rules' => 'trim|required|callback__check_code'),
            array('field' => 'flc-name', 'label' => '名称', 'rules' => 'trim|required'),
            array('field' => 'disp-seq', 'label' => '展示顺序', 'rules' => 'integer'),
        );
        $this->load->library('form_validation');
        $this->form_validation->set_rules($rules);
        if (!$this->form_validation->run()) {
            $out['msg'] = $this->form_validation->error_string();
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array();
        $param['FLC_CODE'] = $this->input->post('flc-code');
        $param['FLC_NAME'] = $this->input->post('flc-name');
        $param['FLC_DESCR'] = $this->input->post('flc-descr');
        $param['COMPANY_OID'] = $this->cid;
        $param['DISP_SEQ'] = $this->input->post('disp-seq');
        if (!$param['DISP_SEQ']) {
            $param['DISP_SEQ'] = 0;
        }
        $this->load->model('mfirstlevelcatg');
        if ($cid = $this->mfirstlevelcatg->save($param, $id)) {
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
/*{{{ savestore */
    public function savestore() {
        $out = array();
        $this->output->set_content_type('application/json');
        if (!$this->input->is_ajax_request()) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        $id = intval($this->input->get_post('id'));
        $store = $this->input->get_post('store');
        if (!$id) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }
        if ($store) {
            $store = array_filter($store, function($val){
                return intval($val);
            });
        }
        $this->load->model('mfirstlevelcatg');
        if ($this->mfirstlevelcatg->savestore($store, $this->cid, $id)) {
            $out['status'] = 0;
            $out['msg'] = '保存成功';
            $this->output->set_output(json_encode($out));

            return true;
        }

        $out['status'] = 1;
        $out['msg'] = '保存失败';
        $this->output->set_output(json_encode($out));

        return false;
    }
/*}}}*/
/*{{{ _check_code */
    public function _check_code($str) {
        $this->load->model('mfirstlevelcatg');
        $id = $this->input->get_post('id');
        if (!$this->mfirstlevelcatg->not_exists($str, $id, $this->cid)) {
            $this->form_validation->set_message(__FUNCTION__, '编码 已经存在，请换一个。');

            return false;
        }

        return true;
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
/*{{{ del */
    public function del() {
        $out = array();
        $this->output->set_content_type('application/json');
        if (!$this->input->is_ajax_request()) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        $ids = $this->input->get_post('ids');
        $this->load->model('mfirstlevelcatg');
        if ($this->mfirstlevelcatg->del($ids)) {
            $out['status'] = 0;
            $out['msg'] = '删除成功';
            $this->output->set_output(json_encode($out));

            return true;
        }

        $out['status'] = 1;
        $out['msg'] = '删除失败';
        $this->output->set_output(json_encode($out));

        return false;
    }
/*}}}*/

/*{{{ store */
    public function store($id) {
        $out = array();
        $this->output->set_content_type('application/json');
        if (!$this->input->is_ajax_request()) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }
        if (!$id) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        $this->load->model('mfirstlevelcatg');
        $out['status'] = 0;
        $out['data'] = $this->mfirstlevelcatg->load_vs_store($id);
        $this->output->set_output(json_encode($out));

        return true;
    }
/*}}}*/

}
