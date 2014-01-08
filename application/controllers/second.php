<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Second extends Ma_Controller {

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
        $out['title'] = '二级分类管理';

        $search = array();
        if ($this->input->post()) {
            $search['STORE_OID'] = $this->input->get_post('store-oid');
            $search['FLC_OID'] = $this->input->get_post('flc-oid');
            $search['SLC_NAME'] = $this->input->get_post('slc-name');
            $search['SLC_CODE'] = $this->input->get_post('slc-code');
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
        $param['store'] = $this->lcommon->insert_blank($this->mstore->load_for_kv());
        $param['first'] = $this->lcommon->insert_blank(array());
        $out['param'] = $param;

        // The data of search
        $this->load->model('msecondlevelcatg');
        if($data = $this->msecondlevelcatg->load_all_by_company($search, $this->cid)) {
            $out["second"] = $data["data"];

            // Pagaination
            $this->load->library("pagination");
            $this->pagination->uri_segment = 3;
            $this->pagination->total_rows = $data["num"];
            $this->pagination->base_url = site_url() . "/second/index";
            $out["pagination"] = $this->pagination->create_links();
        }

        $this->twig->display('second_index.html', $out);
	}
/*}}} */
/*{{{ edit */
    public function edit($id = 0) {
        $this->load->library('twig');
        $out = array();
        $out['title'] = '一级分类管理';
        
        $param = array();
        $this->load->model('mstore');
        $param['store'] = $this->lcommon->insert_blank($this->mstore->load_for_kv());
        $param['first'] = $this->lcommon->insert_blank(array());
        $out['param'] = $param;

        $this->load->model('msecondlevelcatg');
        if ($id) {
            $out['second'] = $this->msecondlevelcatg->load($id, $this->cid);
        }
        $this->twig->display('second_edit.html', $out);

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
            array('field' => 'slc-code', 'label' => '编码', 'rules' => 'trim|required|callback__check_code'),
            array('field' => 'slc-name', 'label' => '名称', 'rules' => 'trim|required'),
            array('field' => 'disp-seq', 'label' => '展示顺序', 'rules' => 'integer'),
            array('field' => 'store-oid', 'label' => '所属门店', 'rules' => 'trim|callback__select'),
            array('field' => 'flc-oid', 'label' => '所属一级分类', 'rules' => 'trim|callback__select'),
        );
        $this->load->library('form_validation');
        $this->form_validation->set_rules($rules);
        if (!$this->form_validation->run()) {
            $out['msg'] = $this->form_validation->error_string();
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array();
        $param['SLC_CODE'] = $this->input->post('slc-code');
        $param['SLC_NAME'] = $this->input->post('slc-name');
        $param['SLC_DESCR'] = $this->input->post('slc-descr');
        $param['DISP_SEQ'] = $this->input->post('disp-seq');
        $param['FLC_OID'] = $this->input->post('flc-oid');
        $this->load->model('msecondlevelcatg');
        if ($cid = $this->msecondlevelcatg->save($param, $id)) {
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
        $this->load->model('msecondlevelcatg');
        $id = $this->input->get_post('id');
        if (!$this->msecondlevelcatg->not_exists($str, $id, $this->cid)) {
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

}
