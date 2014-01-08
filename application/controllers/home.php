<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends Ma_Controller {

/*{{{ index */
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index() {
        if (!$user = $this->lsession->get('user')) {
            redirect('/login');
            return false;
        }

        if ($user->USER_TYPE == MA_USER_TYPE_ADMIN) {
            redirect('/dish');
            return true;
        }

        $this->load->library('twig');
        $out = array();
        $out['title'] = '选择公司';

        $this->load->model('mcompany');
        $out['company'] = $this->mcompany->load_for_choose();
        $this->twig->display('home_index.html', $out);

        return true;
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
        $out['title'] = '公司管理';

        $this->load->model('mcompany');
        if ($id) {
            $out['company'] = $this->mcompany->load($id);
        }
        $this->twig->display('home_edit.html', $out);

        return true;
    }
/*}}}*/
/*{{{ entry */
    public function entry($id = 0) {
        $user = $this->lsession->get('user');
        if ($user->USER_TYPE != MA_USER_TYPE_SUPER) {
            $this->index();

            return false;
        }

        $this->load->model('mcompany');
        if ($id) {
            if ($company = $this->mcompany->load($id)) {
                $this->lsession->set('company', $company);
                redirect('/user');
                return true;
            }
        }

        $this->index();
        return false;
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
            array('field' => 'company-code', 'label' => '编码', 'rules' => 'trim|required|callback__check_code'),
            array('field' => 'company-name', 'label' => '名称', 'rules' => 'trim|required'),
        );
        $this->load->library('form_validation');
        $this->form_validation->set_rules($rules);
        if (!$this->form_validation->run()) {
            $out['msg'] = $this->form_validation->error_string();
            $this->output->set_output(json_encode($out));

            return false;
        }

        $param = array();
        $param['COMPANY_CODE'] = $this->input->post('company-code');
        $param['COMPANY_NAME'] = $this->input->post('company-name');
        $param['COMPANY_SRT_NAME'] = $this->input->post('company-srt-name');
        $param['COMPANY_LOGO'] = $this->input->post('company-logo');
        $param['COMPANY_DESCR'] = $this->input->post('company-descr');
        $param['COMPANY_TEL'] = $this->input->post('company-tel');
        $param['COMPANY_FAX'] = $this->input->post('company-fax');
        $param['COMPANY_PSTL_CODE'] = $this->input->post('company-pstl-code');
        $param['COMPANY_ADDR1'] = $this->input->post('company-addr1');
        $param['COMPANY_ADDR2'] = $this->input->post('company-addr2');
        $param['COMPANY_ADDR3'] = $this->input->post('company-addr3');
        $param['COMPANY_ADDR4'] = $this->input->post('company-addr4');
        $param['CTCT_PERS'] = $this->input->post('ctct-pers');
        $param['CTCT_TEL'] = $this->input->post('ctct-tel');
        $param['CTCT_MOBILE'] = $this->input->post('ctct-mobile');
        $param['CTCT_EMAIL'] = $this->input->post('ctct-email');

        $this->load->model('mcompany');
        if ($cid = $this->mcompany->save($param, $id)) {
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
        $this->load->model('mcompany');
        $id = $this->input->get_post('id');
        if (!$this->mcompany->not_exists($str, $id)) {
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

}
