<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Param extends Ma_Controller {

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
        $out['title'] = '系统参数配置';

        $search = array();
        if ($this->input->post()) {
            $search['PARAM_CODE'] = $this->input->get_post('param-code');
            $this->lsession->set('param_search', $search);
        } else {
            if ($tmp = $this->lsession->get('param_search')) {
                $search = $tmp;
            }
        }
        $this->config->load("pagination");
        $search['start'] = $start;
        $search["per_page"] = $this->config->item("per_page"); 
        $out['search'] = $search;

        // The data of search
        $this->load->model('mctrlparam');
        if($data = $this->mctrlparam->load_all_by_company($search, $this->cid)) {
            $out["param"] = $data["data"];

            // Pagaination
            $this->load->library("pagination");
            $this->pagination->uri_segment = 3;
            $this->pagination->total_rows = $data["num"];
            $this->pagination->base_url = site_url() . "/param/index";
            $out["pagination"] = $this->pagination->create_links();
        }

        $this->twig->display('param_index.html', $out);
	}
/*}}} */
/*{{{ edit */
    public function edit($id = 0) {
        $this->load->library('twig');
        $out = array();
        $out['title'] = '参数管理';

        $this->load->model('mctrlparam');
        if ($id) {
            $out['param'] = $this->mctrlparam->load($id, $this->cid);
        }
        $this->twig->display('param_edit.html', $out);

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
            array('field' => 'param-code', 'label' => '参数编码', 'rules' => 'trim|required|callback__check_code'),
            array('field' => 'string-value', 'label' => '值', 'rules' => 'trim|required'),
        );
        $this->load->library('form_validation');
        $this->form_validation->set_rules($rules);
        if (!$this->form_validation->run()) {
            $out['msg'] = $this->form_validation->error_string();
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array();
        $param['PARAM_CODE'] = $this->input->post('param-code');
        $param['STRING_VALUE'] = $this->input->post('string-value');
        $param['PARAM_DESCR'] = $this->input->post('param-descr');
        $param['COMPANY_OID'] = $this->cid;
        $this->load->model('mctrlparam');
        if ($tmp = $this->mctrlparam->save($param, $id)) {
            $out['status'] = 0;
            $out['msg'] = '保存成功';
            $out['id'] = $tmp;
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
        $this->load->model('mctrlparam');
        $id = $this->input->get_post('id');
        if (!$this->mctrlparam->not_exists($str, $id, $this->cid)) {
            $this->form_validation->set_message(__FUNCTION__, '编码 已经存在，请换一个。');

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
        $this->load->model('mctrlparam');
        if ($this->mctrlparam->del($ids)) {
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
