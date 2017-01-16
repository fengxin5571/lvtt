<?php
//zend by QQ:2172298892
class ECS
{
	public $db_name = '';
	public $prefix = 'ecs_';

	public function ECS($db_name, $prefix)
	{
		$this->db_name = $db_name;
		$this->prefix = $prefix;
	}

	public function table($str)
	{
		return '`' . $this->db_name . '`.`' . $this->prefix . $str . '`';
	}

	public function compile_password($pass)
	{
		return md5($pass);
	}

	public function get_domain()
	{
		$protocol = $this->http();

		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
		}
		else if (isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		}
		else {
			if (isset($_SERVER['SERVER_PORT'])) {
				$port = ':' . $_SERVER['SERVER_PORT'];
				if (((':80' == $port) && ('http://' == $protocol)) || ((':443' == $port) && ('https://' == $protocol))) {
					$port = '';
				}
			}
			else {
				$port = '';
			}

			if (isset($_SERVER['SERVER_NAME'])) {
				$host = $_SERVER['SERVER_NAME'] . $port;
			}
			else if (isset($_SERVER['SERVER_ADDR'])) {
				$host = $_SERVER['SERVER_ADDR'] . $port;
			}
		}

		return $protocol . $host;
	}

	public function url()
	{
		$curr = (strpos(PHP_SELF, ADMIN_PATH . '/') !== false ? preg_replace('/(.*)(' . ADMIN_PATH . ')(\\/?)(.)*/i', '\\1', dirname(PHP_SELF)) : dirname(PHP_SELF));
		$root = str_replace('\\', '/', $curr);

		if (substr($root, -1) != '/') {
			$root .= '/';
		}

		return $this->get_domain() . $root;
	}

	public function seller_url()
	{
		$curr = (strpos(PHP_SELF, SELLER_PATH . '/') !== false ? preg_replace('/(.*)(' . SELLER_PATH . ')(\\/?)(.)*/i', '\\1', dirname(PHP_SELF)) : dirname(PHP_SELF));
		$root = str_replace('\\', '/', $curr);

		if (substr($root, -1) != '/') {
			$root .= '/';
		}

		return $this->get_domain() . $root;
	}
	
   public  function guider_url(){
       $curr = (strpos(PHP_SELF, GUIDER_PATH . '/') !== false ? preg_replace('/(.*)(' . GUIDER_PATH . ')(\\/?)(.)*/i', '\\1', dirname(PHP_SELF)) : dirname(PHP_SELF));
       $root = str_replace('\\', '/', $curr);
       
       if (substr($root, -1) != '/') {
           $root .= '/';
       }
       
       return $this->get_domain() . $root;
   }
   
	public function stores_url()
	{
		$curr = (strpos(PHP_SELF, STORES_PATH . '/') !== false ? preg_replace('/(.*)(' . STORES_PATH . ')(\\/?)(.)*/i', '\\1', dirname(PHP_SELF)) : dirname(PHP_SELF));
		$root = str_replace('\\', '/', $curr);

		if (substr($root, -1) != '/') {
			$root .= '/';
		}

		return $this->get_domain() . $root;
	}

	public function http()
	{
		return isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off') ? 'https://' : 'http://';
	}

	public function data_dir($sid = 0)
	{
		if (empty($sid)) {
			$s = 'data';
		}
		else {
			$s = 'user_files/';
			$s .= ceil($sid / 3000) . '/';
			$s .= $sid % 3000;
		}

		return $s;
	}

	public function image_dir($sid = 0)
	{
		if (empty($sid)) {
			$s = 'images';
		}
		else {
			$s = 'user_files/';
			$s .= ceil($sid / 3000) . '/';
			$s .= ($sid % 3000) . '/';
			$s .= 'images';
		}

		return $s;
	}

	public function ali_yu($msg, $sms_type = 0)
	{
		include ROOT_PATH . 'plugins/aliyunyu/TopSdk.php';
		$c = new TopClient();
		$c->appkey = $GLOBALS['_CFG']['ali_appkey'];
		$c->secretKey = $GLOBALS['_CFG']['ali_secretkey'];
		$c->format = 'json';
		$req = new AlibabaAliqinFcSmsNumSendRequest();

		if ($sms_type == 1) {
			$arr = array();

			foreach ($msg as $key => $row) {
				$phones = $row['mobile_phone'];
				$req->setSmsType($row['SmsType']);
				$req->setSmsFreeSignName($row['SignName']);
				$req->setSmsParam($row['smsParams']);
		        $req->setRecNum(''.$phones);
				$req->setSmsTemplateCode($row['SmsCdoe']);
				$arr[$key]['resp'] = $c->execute($req);
			}

			return $arr;
		}
		else {
			$phones = $msg['mobile_phone'];
			$req->setSmsType($msg['SmsType']);
			$req->setSmsFreeSignName($msg['SignName']);
			$req->setSmsParam($msg['smsParams']);
		    $req->setRecNum(''.$phones);
			$req->setSmsTemplateCode($msg['SmsCdoe']);
			$resp = $c->execute($req);
			return $resp;
		}
	}

	public function page_array($page_size = 1, $page = 1, $array = array(), $order = 0)
	{
		$arr = array();
		$pagedata = array();

		if ($array) {
			global $countpage;
			$start = ($page - 1) * $page_size;

			if ($order == 1) {
				$array = array_reverse($array);
			}

			$totals = count($array);
			$countpage = ceil($totals / $page_size);
			$pagedata = array_slice($array, $start, $page_size);
			$filter = array('page' => $page, 'page_size' => $page_size, 'record_count' => $totals, 'page_count' => $countpage);
			$arr = array('list' => $pagedata, 'filter' => $filter, 'page_count' => $countpage, 'record_count' => $totals);
		}

		return $arr;
	}

	public function get_explode_filter($str_arr, $type = 0)
	{
		switch ($type) {
		case 1:
			$str = 1;
			break;

		default:
			$str = $this->return_intval($str_arr);
			break;
		}

		return $str;
	}

	public function return_intval($str)
	{
		$new_str = '';

		if ($str) {
			$str = explode(',', $str);

			foreach ($str as $key => $row) {
				$row = intval($row);

				if ($row) {
					$new_str .= $row . ',';
				}
			}
		}

		$new_str = substr($new_str, 0, -1);
		return $new_str;
	}

	public function preg_is_letter($str)
	{
		$preg = '[^A-Za-z]+';

		if (preg_match('/' . $preg . '/', $str)) {
			return false;
		}
		else {
			return true;
		}
	}

	public function get_select_find_in_set($is_db = 0, $select_id, $select_array = array(), $where = '', $table = '', $id = '', $replace = '')
	{
		if ($replace) {
			$replace = 'REPLACE (' . $id . ',\'' . $replace . '\',\',\')';
		}
		else {
			$replace = $id;
		}

		if ($select_array) {
			$select = explode(',', $select_array);
		}
		else {
			$select = '*';
		}

		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE find_in_set(\'' . $select_id . '\', ' . $replace . ') OR find_in_set(\'' . $select_id . '\', ' . $replace . ') ' . $where;

		if ($is_db == 1) {
			return $GLOBALS['db']->getAll($sql);
		}
		else if ($is_db == 2) {
			return $GLOBALS['db']->getRow($sql);
		}
		else {
			return $GLOBALS['db']->getOne($sql, true);
		}
	}

	public function get_del_find_in_set($select_id, $where = '', $table = '', $id = '', $replace = '')
	{
		if ($replace) {
			$replace = 'REPLACE (' . $id . ',\'' . $replace . '\',\',\')';
		}
		else {
			$replace = $id;
		}

		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE find_in_set(\'' . $select_id . '\', ' . $replace . ') OR find_in_set(\'' . $select_id . '\', ' . $replace . ') ' . $where;
		$GLOBALS['db']->query($sql);
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

define('APPNAME', 'ECMOBAN_DSC');
define('VERSION', 'v1.9.3');
define('RELEASE', '20161215');

?>
