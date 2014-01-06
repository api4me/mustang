<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends Ma_Controller {

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
        $out['title'] = '用户管理';

        $search = array();
        if ($this->input->post()) {
            $search['LOGIN_ID'] = $this->input->get_post('login-id');
            $search['USER_TYPE'] = $this->input->get_post('user-type');
            $search['USER_STATUS'] = $this->input->get_post('user-status');
            $this->lsession->set('user_search', $search);
        } else {
            if ($tmp = $this->lsession->get('user_search')) {
                $search = $tmp;
            }
        }
        $this->config->load("pagination");
        $search['start'] = $start;
        $search["per_page"] = $this->config->item("per_page"); 
        $out['search'] = $search;

        $param = array();
        $param['enable'] = $this->lcommon->form_option('enable');
        $param['role'] = $this->lcommon->form_option('role');
        $out['param'] = $param;

        // The data of search
        $this->load->model('muser');
        if($data = $this->muser->load_all($search)) {
            $out["users"] = $data["data"];

            // Pagaination
            $this->load->library("pagination");
            $this->pagination->uri_segment = 3;
            $this->pagination->total_rows = $data["num"];
            $this->pagination->base_url = site_url() . "/user/index";
            $out["pagination"] = $this->pagination->create_links();
        }

        $this->twig->display('user_index.html', $out);
	}
/*}}}*/
/*{{{ edit */
    public function edit($id = 0) {
        $user = $this->lsession->get('user');
        if ($user->USER_TYPE != MA_USER_TYPE_SUPER) {
            $this->index();

            return false;
        }

        $this->load->library('twig');
        $out = array();
        $out['title'] = '用户管理';
        
        $param = array();
        $this->load->model('mcompany');
        $param['enable'] = $this->lcommon->form_option('enable');
        $param['role'] = $this->lcommon->form_option('role');
        $param['company'] = $this->lcommon->insert_blank($this->mcompany->load_for_choose());

        $out['param'] = $param;

        $this->load->model('muser');
        if ($id) {
            $out['user'] = $this->muser->load($id);
        }
        
        $this->twig->display('user_edit.html', $out);

        return true;
    }
/*}}}*/
/*{{{ save */
    public function save($id = 0) {
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

        // Validate
        $rules = array(
            array('field' => 'user-name', 'label' => '姓名', 'rules' => 'trim|required'),
            array('field' => 'login-id', 'label' => '登录号', 'rules' => 'trim|required'),
            array('field' => 'user-type', 'label' => '用户类型', 'rules' => 'trim|required'),
            array('field' => 'user-status', 'label' => '用户状态', 'rules' => 'trim|required'),
        );
        $this->load->library('form_validation');
        $this->form_validation->set_rules($rules);
        if (!$this->form_validation->run()) {
            $out['msg'] = $this->form_validation->error_string();
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array();
        $param['USER_CODE'] = $this->input->post('user-code');
        $param['USER_NAME'] = $this->input->post('user-name');
        $param['OFFTEL'] = $this->input->post('offtel');
        $param['MOBILE'] = $this->input->post('mobile');
        $param['EMAIL'] = $this->input->post('email');
        $param['LOGIN_ID'] = $this->input->post('login-id');
        if ($this->input->post('login-pwd')) {
            $param['LOGIN_PWD'] = $this->lcommon->encrypt_pwd($this->input->post('login-pwd'));
        }
        $param['USER_STATUS'] = $this->input->post('user-status');
        $param['USER_TYPE'] = $this->input->post('user-type');
        if ($param['USER_TYPE'] != MA_USER_TYPE_SUPER) {
            $param['COMPANY_OID'] = $this->input->post('company');
        }

        $this->load->model('muser');
        if ($cid = $this->muser->save($param, $id)) {
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

        $this->load->model('mcompany');
        if ($cid = $this->mcompany->del($id)) {
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
/*{{{ reset */
    public function reset() {
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

        $id = $this->input->post('id');
        $pwd = $this->input->post('pwd');
        if (!$id || !$pwd) {
            $out["status"] = 1;
            $out["msg"] = "系统忙，请稍后...";
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param['LOGIN_PWD'] = $this->lcommon->encrypt_pwd($pwd);
        $this->load->model('muser');
        if ($cid = $this->muser->save($param, $id)) {
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

}
