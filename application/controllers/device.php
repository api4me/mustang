<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Device extends Ma_Controller {

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
        $out['title'] = '设备管理';

        $search = array();
        if ($this->input->post()) {
            $search['STORE_CODE'] = $this->input->get_post('store-code');
            $search['STORE_OID'] = $this->input->get_post('store-oid');
            $search['SERL_NBR'] = $this->input->get_post('serl-nbr');
            $this->lsession->set('device_search', $search);
        } else {
            if ($tmp = $this->lsession->get('device_search')) {
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
        $this->load->model('mtrmlequip');
        if($data = $this->mtrmlequip->load_all_by_company($search, $this->cid)) {
            $out["device"] = $data["data"];

            // Pagaination
            $this->load->library("pagination");
            $this->pagination->uri_segment = 3;
            $this->pagination->total_rows = $data["num"];
            $this->pagination->base_url = site_url() . "/device/index";
            $out["pagination"] = $this->pagination->create_links();
        }

        $this->twig->display('device_index.html', $out);
	}
/*}}} */
/*{{{ edit */
    public function edit($id = 0) {
        $this->load->library('twig');
        $out = array();
        $out['title'] = '设备管理';

        $param = array();
        $this->load->model('mstore');
        $param['store'] = $this->lcommon->insert_blank($this->mstore->load_for_kv($this->cid));
        $param['enable'] = $this->lcommon->form_option('enable');
        $param['yesno'] = $this->lcommon->form_option('yesno');
        $out['param'] = $param;
        
        $param = array();
        $this->load->model('mtrmlequip');
        if ($id) {
            $out['device'] = $this->mtrmlequip->load($id, $this->cid);
        }
        $this->twig->display('device_edit.html', $out);

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
            array('field' => 'serl-nbr', 'label' => '序列号', 'rules' => 'trim|required'),
            array('field' => 'store-oid', 'label' => '所属门店', 'rules' => 'trim|required'),
        );
        $this->load->library('form_validation');
        $this->form_validation->set_rules($rules);
        if (!$this->form_validation->run()) {
            $out['msg'] = $this->form_validation->error_string();
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array();
        $param['SERL_NBR'] = $this->input->post('serl-nbr');
        $param['IS_ENABLED'] = $this->input->post('is-enabled');
        $param['STORE_OID'] = $this->input->post('store-oid');
        $param['IS_UPLOAD_BEHAVIORAL'] = $this->input->post('is-upload-behavioral');
        $this->load->model('mtrmlequip');
        if ($this->mtrmlequip->save($param, $id)) {
            $out['status'] = 0;
            $out['msg'] = '保存成功';
            $out['id'] = $param['SERL_NBR'];
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

}
