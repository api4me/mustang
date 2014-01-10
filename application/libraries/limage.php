<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename LImage.php
* @touch date Wednesday, May 15, 2013 AM08:56:26 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0'
* @version 1.0.0
*/

class LImage {

/*{{{ variable */
    private $CI;
    private $folder;
/*}}}*/
/*{{{ __construct */
    public function __construct() {
        $this->CI =& get_instance();
        $this->folder = array(
            'tmp' => FCPATH . 'assets/upload/t',
            'tmp-show' => base_url() . 'assets/upload/t',
            'real' => FCPATH . 'assets/upload',
            'real-abs-show' => 'assets/upload',
        );
    }
/*}}}*/
/*{{{ upload */
    public function upload() {
        $out = array();
        // Fix IE issue, fileupload jquery plug is not send 'HTTP_X_REQUESTED_WITH'
        /*
        if (!$this->CI->input->is_ajax_request()) {
            $out['status'] = 1;
            $out['msg'] = '系统忙，请稍后...';
            echo json_encode($out);

            return false;
        }
        */
        // File upload
        $config = array();
        $config['upload_path'] = $this->folder['tmp'];
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = 2*1024*1024;
        $config['max_width'] = '1200';
        $config['max_height'] = '800';
        $config['remove_spaces'] = true;
        $config['encrypt_name'] = true;

        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->CI->load->library('upload', $config);
        if (!$this->CI->upload->do_upload('upload')) {
            $out['status'] = 1;
            $out['msg'] = $this->CI->upload->display_errors();
            return $out;
        }
        $upload = $this->CI->upload->data();

        // Thumb
        $config = array();
        $config['source_image'] = $upload['full_path'];
        $config['create_thumb'] = true;
        $config['maintain_ratio'] = true;
        $config['width'] = 150;
        $config['height'] = 100;
        $config['master_dim'] = (($upload['image_width']/$upload['image_height']) > ($config['width']/$config['height'])) ? 'width' : 'height';
        $config['thumb_marker'] = '_i';
        $this->CI->load->library('image_lib', $config);
        $this->CI->image_lib->resize();

        $out['status'] = 0;
        $out['name'] = $upload['raw_name'] . '_i' . $upload['file_ext'];
        $out['title'] = substr($upload['orig_name'], 0, -strlen($upload['file_ext']));
        $out['show'] = sprintf('%s/%s', $this->folder['tmp-show'], $out['name']) ;
        $out['msg'] = '上传成功';

        return $out;
    }
/*}}}*/
/*{{{ move */
    /**
     * Move file to it's folder
     * @param name string the file of name, name can original or thumbnail.
     *
     */
    public function move($name) {
        $name = array_pop(explode('/', $name));
        // Original and thumbnail
        if ($pos = strrpos($name, '.')) {
            if (substr($name, $pos-2, 2) == '_i') {
                $name = substr($name, 0, $pos - 2) . substr($name, $pos);
            }
        }
        $tmp = explode('.', $name);
        if (count($tmp) > 1) {
            $tmp[count($tmp) - 2] .= '_i';
        }
        $thumbnail = implode('.', $tmp);
        $file = array($name, $thumbnail);
        foreach ($file as $f) {
            $from = sprintf('%s/%s', $this->folder['tmp'], $f);
            if (!file_exists($from)) {
                log_message('error', sprintf('File(%s) is not exists in image temp fold.', $f));
                continue;
            }
            $to = sprintf('%s/%s', $this->folder['real'], $f);
            if (!rename($from, $to)) {
                log_message('error', sprintf('File(%s) can not be move.', $f));
            }
        }

        $out = array();
        $out['status'] = 0;
        $out['url'] = sprintf('%s/%s', $this->folder['real-abs-show'], $name);
        $out['msg'] = '文件保存成功';

        return $out;
    }
/*}}}*/
/*{{{ del */
    public function del($name) {
        $file = array();
        list($file['raw_name'], $file['file_ext']) = explode('.', $name);
        if (substr($file['raw_name'], -2) == '_i') {
            $file['thumb_name'] = $file['raw_name'] . '.' . $file['file_ext'];
            $file['file_name'] = substr($file['raw_name'], 0, -2) . '.' . $file['file_ext'];
        } else {
            $file['thumb_name'] = $file['raw_name'] . '_i.' . $file['file_ext'];
            $file['file_name'] = $file['raw_name'] . '.' . $file['file_ext'];
        }

        if (strpos($file['thumb_name'], $this->folder['real']) !== false) {
            @unlink($file['thumb_name']);
            @unlink($file['file_name']);
        } else {
            @unlink($this->folder['real'] . $file['thumb_name']);
            @unlink($this->folder['real'] . $file['file_name']);
        }

        $out = array();
        $out['status'] = 0;
        $out['msg'] = '删除成功';

        return $out;
    }
/*}}}*/

}
