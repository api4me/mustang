<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Store extends Ma_Controller {

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
        $out['title'] = '门店管理';

        $search = array();
        if ($this->input->post()) {
            $search['STORE_CODE'] = $this->input->get_post('store-code');
            $search['STORE_NAME'] = $this->input->get_post('store-name');
            $this->lsession->set('store_search', $search);
        } else {
            if ($tmp = $this->lsession->get('store_search')) {
                $search = $tmp;
            }
        }
        $this->config->load("pagination");
        $search['start'] = $start;
        $search["per_page"] = $this->config->item("per_page"); 
        $out['search'] = $search;

        // The data of search
        $this->load->model('mstore');
        if($data = $this->mstore->load_all_by_company($search, $this->cid)) {
            $out["store"] = $data["data"];

            // Pagaination
            $this->load->library("pagination");
            $this->pagination->uri_segment = 3;
            $this->pagination->total_rows = $data["num"];
            $this->pagination->base_url = site_url() . "/store/index";
            $out["pagination"] = $this->pagination->create_links();
        }

        $this->twig->display('store_index.html', $out);
	}
/*}}} */
/*{{{ edit */
    public function edit($id = 0) {

        $this->load->library('twig');
        $out = array();
        $out['title'] = '用户管理';
        
        $param = array();
        $this->load->model('mstore');
        if ($id) {
            $out['store'] = $this->mstore->load($id, $this->cid);
        }
        $this->twig->display('store_edit.html', $out);

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
            array('field' => 'store-code', 'label' => '编码', 'rules' => 'trim|required|callback__check_code'),
            array('field' => 'store-name', 'label' => '名称', 'rules' => 'trim|required'),
        );
        $this->load->library('form_validation');
        $this->form_validation->set_rules($rules);
        if (!$this->form_validation->run()) {
            $out['msg'] = $this->form_validation->error_string();
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array();
        $param['STORE_CODE'] = $this->input->post('store-code');
        $param['STORE_NAME'] = $this->input->post('store-name');
        $param['STORE_SRT_NAME'] = $this->input->post('store-srt-name');
        $param['STORE_DESCR'] = $this->input->post('store-descr');
        $param['STORE_TEL'] = $this->input->post('store-tel');
        $param['STORE_FAX'] = $this->input->post('store-fax');
        $param['STORE_PSTL_CODE'] = $this->input->post('store-pstl-code');
        $param['STORE_ADDR1'] = $this->input->post('store-addr1');
        $param['STORE_ADDR2'] = $this->input->post('store-addr2');
        $param['STORE_ADDR3'] = $this->input->post('store-addr3');
        $param['STORE_ADDR4'] = $this->input->post('store-addr4');
        $param['CTCT_PERS'] = $this->input->post('ctct-pers');
        $param['COMPANY_OID'] = $this->cid;
        $this->load->model('mstore');
        if ($cid = $this->mstore->save($param, $id)) {
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
/*{{{ _check_code */
    public function _check_code($str) {
        $this->load->model('mstore');
        $id = $this->input->get_post('id');
        if (!$this->mstore->not_exists($str, $id, $this->cid)) {
            $this->form_validation->set_message(__FUNCTION__, '编码 已经存在，请换一个。');

            return false;
        }

        return true;
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
            $this->output->set_output(json_encode($out));

            return true;
        }

        $out['status'] = 1;
        $out['msg'] = '删除失败';
        $this->output->set_output(json_encode($out));

        return false;
    }
/*}}}*/

}
