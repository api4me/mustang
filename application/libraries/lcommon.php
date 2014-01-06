<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* @filename common_helper.php
* @touch date Wednesday, May 15, 2013 AM08:56:26 CST
* @author: Fred<fred.zhou@foxmail.com>
* @license: http://www.zend.com/license/3_0.txt PHP License 3.0"
* @version 1.0.0
*/

class LCommon {

/*{{{ variable */
    private $CI;
/*}}}*/
/*{{{ __construct */
    public function __construct() {
        $this->CI =& get_instance();
    }
/*}}}*/
/*{{{ option */
    public function option($type, $code = null) {
        static $data;
        if (!isset($data)) {
            $this->CI->config->load('options', true);
            $data = $this->CI->config->item('options');
        }

        if (isset($data[$type])) {
            if ($code) {
                return (isset($data[$type][$code])) ? $data[$type][$code] : false;
            } else {
                return $data[$type];
            }
        }

        return false;
    }
/*}}}*/
/*{{{ form_option */
    public function form_option($type, $blankline = true, $remove = false) {
        if ($data = $this->option($type)) {
            if (is_array($remove)) {
                foreach ($remove as $val) {
                    if (isset($data[$val])) {
                        unset($data[$val]);
                    }
                }
            }

            if ($blankline) {
                $data = $this->insert_blank($data);
            }

            return $data;
        }

        return array();
    }
/*}}}*/
/*{{{ insert_blank */
    public function insert_blank($data) {
        $tmp = array("0" => "--");
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $tmp[$key] = $val;
            }
        }

        return $tmp;
    }
/*}}}*/
/*{{{ md5 */
    public function md5($str, $salt = false) {
        if ($salt) {
            return md5($str . $this->CI->config->item('encryption_salt'));
        }

        return md5($str);
    }
/*}}}*/
/*{{{ encrypt_pwd */
    public function encrypt_pwd($str) {
        return $this->md5($this->md5($str), true);
    }
/*}}}*/
/*{{{ sequence */
    public function sequence($type) {
        if ($query = $this->CI->db->query('SELECT nextval(?) AS id;', $type)) {
            return $query->row()->id;
        }

        return false;
    }
/*}}}*/
/*{{{ validate func */
/*{{{ is_empty */
    public function is_empty($str) {
        if (isset($str) && !empty($str)) {
            return false;
        }

        return true;
    }
/*}}}*/
/*{{{ get_size */
    public function get_size($str) {
        if (isset($str) && !empty($str)) {
            return mb_strlen($str);
        }

        return 0;
    }
/*}}}*/
/*{{{ is_email */
    public function is_email($str) {
        return (preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? true : false;
    }
/*}}}*/
/*{{{ is_phone */
    public function is_phone($str) {
        if (preg_match("/^1[3458]{1}[0-9]{1}[0-9]{8}$/", $str)) { // Phone
           // || preg_match("/^0[1-9]{2,3}-[0-9]{7,8}$/", $str)) { // Tel

            return true;
        }

        return false;
    }
/*}}}*/
/*}}}*/

}
