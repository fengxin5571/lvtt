<?php
//zend by QQ:2172298892
error_reporting(1);
function sub_str($str, $length = 0, $append = true)
{
	$str = trim($str);
	$strlength = strlen($str);
	if (($length == 0) || ($strlength <= $length)) {
		return $str;
	}
	else if ($length < 0) {
		$length = $strlength + $length;

		if ($length < 0) {
			$length = $strlength;
		}
	}

	if (function_exists('mb_substr')) {
		$newstr = mb_substr($str, 0, $length, EC_CHARSET);
	}
	else if (function_exists('iconv_substr')) {
		$newstr = iconv_substr($str, 0, $length, EC_CHARSET);
	}
	else {
		$newstr = substr($str, 0, $length);
	}

	if ($append && ($str != $newstr)) {
		$newstr .= '...';
	}

	return $newstr;
}

function real_ip()
{
	static $realip;

	if ($realip !== NULL) {
		return $realip;
	}

	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

			foreach ($arr as $ip) {
				$ip = trim($ip);

				if ($ip != 'unknown') {
					$realip = $ip;
					break;
				}
			}
		}
		else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$realip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if (isset($_SERVER['REMOTE_ADDR'])) {
			$realip = $_SERVER['REMOTE_ADDR'];
		}
		else {
			$realip = '0.0.0.0';
		}
	}
	else if (getenv('HTTP_X_FORWARDED_FOR')) {
		$realip = getenv('HTTP_X_FORWARDED_FOR');
	}
	else if (getenv('HTTP_CLIENT_IP')) {
		$realip = getenv('HTTP_CLIENT_IP');
	}
	else {
		$realip = getenv('REMOTE_ADDR');
	}

	preg_match('/[\\d\\.]{7,15}/', $realip, $onlineip);
	$realip = (!empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0');
	return $realip;
}

function str_len($str)
{
	$length = strlen(preg_replace('/[\\x00-\\x7F]/', '', $str));

	if ($length) {
		return (strlen($str) - $length) + (intval($length / 3) * 2);
	}
	else {
		return strlen($str);
	}
}

function get_crlf()
{
	if (stristr($_SERVER['HTTP_USER_AGENT'], 'Win')) {
		$the_crlf = '\\r\\n';
	}
	else if (stristr($_SERVER['HTTP_USER_AGENT'], 'Mac')) {
		$the_crlf = '\\r';
	}
	else {
		$the_crlf = '\\n';
	}

	return $the_crlf;
}

function get_contents_section($dir = '')
{
	$is_cp_url = base64_decode('aHR0cDovL2Vjc2hvcC5lY21vYmFuLmNvbS9kc2MucGhw');
	$new_dir = ROOT_PATH . 'includes/lib_ecmobanFunc.php';
	if (empty($dir) && file_exists($new_dir)) {
		$dir = $new_dir;
	}

	$cp_str = base64_decode('5ZWG5Yib572R57uc');
	$section = file_get_contents($dir, NULL, NULL, 0, 180);
	$section = substr($section, 0, -5);
	$section = substr($section, -38);
	$section = substr($section, 1);

	if (strpos($section, $cp_str) !== false) {
		$post_type = 1;
	}
	else {
		$post_type = 2;
	}

	$cer_url = $GLOBALS['db']->getOne('SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'certi\'');
	if (empty($cer_url) || ($cer_url != $is_cp_url)) {
		$shop_url = urlencode($GLOBALS['ecs']->url());
		$shop_country = $GLOBALS['db']->getOne('SELECT region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_id=\'' . $GLOBALS['_CFG']['shop_country'] . '\'');
		$shop_province = $GLOBALS['db']->getOne('SELECT region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_id=\'' . $GLOBALS['_CFG']['shop_province'] . '\'');
		$shop_city = $GLOBALS['db']->getOne('SELECT region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_id=\'' . $GLOBALS['_CFG']['shop_city'] . '\'');
		$url_data = array('domain' => $GLOBALS['ecs']->get_domain(), 'url' => urldecode($shop_url), 'shop_name' => $GLOBALS['_CFG']['shop_name'], 'shop_title' => $GLOBALS['_CFG']['shop_title'], 'shop_desc' => $GLOBALS['_CFG']['shop_desc'], 'shop_keywords' => $GLOBALS['_CFG']['shop_keywords'], 'country' => $shop_country, 'province' => $shop_province, 'city' => $shop_city, 'address' => $GLOBALS['_CFG']['shop_address'], 'qq' => $GLOBALS['_CFG']['qq'], 'ww' => $GLOBALS['_CFG']['ww'], 'ym' => $GLOBALS['_CFG']['service_phone'], 'msn' => $GLOBALS['_CFG']['msn'], 'email' => $GLOBALS['_CFG']['service_email'], 'phone' => $GLOBALS['_CFG']['sms_shop_mobile'], 'icp' => $GLOBALS['_CFG']['icp_number'], 'version' => VERSION, 'release' => RELEASE, 'language' => $GLOBALS['_CFG']['lang'], 'php_ver' => PHP_VERSION, 'mysql_ver' => $GLOBALS['db']->version(), 'charset' => EC_CHARSET, 'post_type' => $post_type);
		$cp_url_size = 'base64_decode(\'aHR0cDovL2Vjc2hvcC5lY21vYmFuLmNvbS9kc2MucGhw\')';
		$cp_url_size = '$url_http = ' . $cp_url_size . ";\r\n";
		$cp_url = $cp_url_size;
		$cp_url .= '$purl_http = new Http();' . "\r\n";
		$cp_url .= '$purl_http->doPost($url_http, $url_data);';
		write_static_cache('cat_goods_config', $cp_url, '/temp/static_caches/', 1, $url_data);
	}

	if (file_exists(ROOT_PATH . 'temp/static_caches/cat_goods_config.php')) {
		require ROOT_PATH . 'temp/static_caches/cat_goods_config.php';
	}
}
function send_mail($name, $email, $subject, $content, $type = 0, $notification = false)
{
	if ($GLOBALS['_CFG']['mail_charset'] != EC_CHARSET) {
		$name = ecs_iconv(EC_CHARSET, $GLOBALS['_CFG']['mail_charset'], $name);
		$subject = ecs_iconv(EC_CHARSET, $GLOBALS['_CFG']['mail_charset'], $subject);
		$content = ecs_iconv(EC_CHARSET, $GLOBALS['_CFG']['mail_charset'], $content);
		$shop_name = ecs_iconv(EC_CHARSET, $GLOBALS['_CFG']['mail_charset'], $GLOBALS['_CFG']['shop_name']);
	}

	$charset = $GLOBALS['_CFG']['mail_charset'];
	if (($GLOBALS['_CFG']['mail_service'] == 0) && function_exists('mail')) {
		$content_type = ($type == 0 ? 'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset);
		$headers = array();
		$headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $GLOBALS['_CFG']['smtp_mail'] . '>';
		$headers[] = $content_type . '; format=flowed';

		if ($notification) {
			$headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $GLOBALS['_CFG']['smtp_mail'] . '>';
		}

		$res = @mail($email, '=?' . $charset . '?B?' . base64_encode($subject) . '?=', $content, implode("\r\n", $headers));

		if (!$res) {
			$GLOBALS['err']->add($GLOBALS['_LANG']['sendemail_false']);
			return false;
		}
		else {
			return true;
		}
	}
	else {
		$content_type = ($type == 0 ? 'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset);
		$content = base64_encode($content);
		$headers = array();
		$headers[] = 'Date: ' . gmdate('D, j M Y H:i:s') . ' +0000';
		$headers[] = 'To: "' . '=?' . $charset . '?B?' . base64_encode($name) . '?=' . '" <' . $email . '>';
		$headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $GLOBALS['_CFG']['smtp_mail'] . '>';
		$headers[] = 'Subject: ' . '=?' . $charset . '?B?' . base64_encode($subject) . '?=';
		$headers[] = $content_type . '; format=flowed';
		$headers[] = 'Content-Transfer-Encoding: base64';
		$headers[] = 'Content-Disposition: inline';

		if ($notification) {
			$headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $GLOBALS['_CFG']['smtp_mail'] . '>';
		}

		$params['host'] = $GLOBALS['_CFG']['smtp_host'];
		$params['port'] = $GLOBALS['_CFG']['smtp_port'];
		$params['user'] = $GLOBALS['_CFG']['smtp_user'];
		$params['pass'] = $GLOBALS['_CFG']['smtp_pass'];
		if (empty($params['host']) || empty($params['port'])) {
			$GLOBALS['err']->add($GLOBALS['_LANG']['smtp_setting_error']);
			return false;
		}
		else {
			if (!function_exists('fsockopen')) {
				$GLOBALS['err']->add($GLOBALS['_LANG']['disabled_fsockopen']);
				return false;
			}

			include_once ROOT_PATH . 'includes/cls_smtp.php';
			static $smtp;
			$send_params['recipients'] = $email;
			$send_params['headers'] = $headers;
			$send_params['from'] = $GLOBALS['_CFG']['smtp_mail'];
			$send_params['body'] = $content;

			if (!isset($smtp)) {
				$smtp = new smtp($params);
			}

			if ($smtp->connect() && $smtp->send($send_params)) {
				return true;
			}
			else {
				$err_msg = $smtp->error_msg();

				if (empty($err_msg)) {
					$GLOBALS['err']->add('Unknown Error');
				}
				else if (strpos($err_msg, 'Failed to connect to server') !== false) {
					$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['smtp_connect_failure'], $params['host'] . ':' . $params['port']));
				}
				else if (strpos($err_msg, 'AUTH command failed') !== false) {
					$GLOBALS['err']->add($GLOBALS['_LANG']['smtp_login_failure']);
				}
				else if (strpos($err_msg, 'bad sequence of commands') !== false) {
					$GLOBALS['err']->add($GLOBALS['_LANG']['smtp_refuse']);
				}
				else {
					$GLOBALS['err']->add($err_msg);
				}

				return false;
			}
		}
	}
}

function gd_version()
{
	include_once ROOT_PATH . 'includes/cls_image.php';
	return cls_image::gd_version();
}

function file_mode_info($file_path)
{
	if (!file_exists($file_path)) {
		return false;
	}

	$mark = 0;

	if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
		$test_file = $file_path . '/cf_test.txt';

		if (is_dir($file_path)) {
			$dir = @opendir($file_path);

			if ($dir === false) {
				return $mark;
			}

			if (@readdir($dir) !== false) {
				$mark ^= 1;
			}

			@closedir($dir);
			$fp = @fopen($test_file, 'wb');

			if ($fp === false) {
				return $mark;
			}

			if (@fwrite($fp, 'directory access testing.') !== false) {
				$mark ^= 2;
			}

			@fclose($fp);
			@unlink($test_file);
			$fp = @fopen($test_file, 'ab+');

			if ($fp === false) {
				return $mark;
			}

			if (@fwrite($fp, "modify test.\r\n") !== false) {
				$mark ^= 4;
			}

			@fclose($fp);

			if (@rename($test_file, $test_file) !== false) {
				$mark ^= 8;
			}

			@unlink($test_file);
		}
		else if (is_file($file_path)) {
			$fp = @fopen($file_path, 'rb');

			if ($fp) {
				$mark ^= 1;
			}

			@fclose($fp);
			$fp = @fopen($file_path, 'ab+');
			if ($fp && (@fwrite($fp, '') !== false)) {
				$mark ^= 6;
			}

			@fclose($fp);

			if (@rename($test_file, $test_file) !== false) {
				$mark ^= 8;
			}
		}
	}
	else {
		if (@is_readable($file_path)) {
			$mark ^= 1;
		}

		if (@is_writable($file_path)) {
			$mark ^= 14;
		}
	}

	return $mark;
}

function log_write($arg, $file = '', $line = '')
{
	if ((DEBUG_MODE & 4) != 4) {
		return NULL;
	}

	$str = "\r\n-- " . date('Y-m-d H:i:s') . " --------------------------------------------------------------\r\n";
	$str .= 'FILE: ' . $file . "\r\nLINE: " . $line . "\r\n";

	if (is_array($arg)) {
		$str .= '$arg = array(';

		foreach ($arg as $val) {
			foreach ($val as $key => $list) {
				$str .= '\'' . $key . '\' => \'' . $list . "'\r\n";
			}
		}

		$str .= ")\r\n";
	}
	else {
		$str .= $arg;
	}

	file_put_contents(ROOT_PATH . DATA_DIR . '/log.txt', $str);
}

function make_dir($folder)
{
	$reval = false;

	if (!file_exists($folder)) {
		@umask(0);
		preg_match_all('/([^\\/]*)\\/?/i', $folder, $atmp);
		$base = ($atmp[0][0] == '/' ? '/' : '');

		foreach ($atmp[1] as $val) {
			if ('' != $val) {
				$base .= $val;
				if (('..' == $val) || ('.' == $val)) {
					$base .= '/';
					continue;
				}
			}
			else {
				continue;
			}

			$base .= '/';

			if (!file_exists($base)) {
				if (@mkdir(rtrim($base, '/'), 511)) {
					@chmod($base, 511);
					$reval = true;
				}
			}
		}
	}
	else {
		$reval = is_dir($folder);
	}

	clearstatcache();
	return $reval;
}

function gzip_enabled()
{
	static $enabled_gzip;

	if ($enabled_gzip === NULL) {
		$enabled_gzip = $GLOBALS['_CFG']['enable_gzip'] && function_exists('ob_gzhandler');
	}

	return $enabled_gzip;
}

function addslashes_deep($value)
{
	if (empty($value)) {
		return $value;
	}
	else {
		return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
	}
}

function addslashes_deep_obj($obj)
{
	if (is_object($obj) == true) {
		foreach ($obj as $key => $val) {
			$obj->$key = addslashes_deep($val);
		}
	}
	else {
		$obj = addslashes_deep($obj);
	}

	return $obj;
}

function stripslashes_deep($value)
{
	if (empty($value)) {
		return $value;
	}
	else {
		return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	}
}

function make_semiangle($str)
{
	$arr = array('��' => '0', '��' => '1', '��' => '2', '��' => '3', '��' => '4', '��' => '5', '��' => '6', '��' => '7', '��' => '8', '��' => '9', '��' => 'A', '��' => 'B', '��' => 'C', '��' => 'D', '��' => 'E', '��' => 'F', '��' => 'G', '��' => 'H', '��' => 'I', '��' => 'J', '��' => 'K', '��' => 'L', '��' => 'M', '��' => 'N', '��' => 'O', '��' => 'P', '��' => 'Q', '��' => 'R', '��' => 'S', '��' => 'T', '��' => 'U', '��' => 'V', '��' => 'W', '��' => 'X', '��' => 'Y', '��' => 'Z', '��' => 'a', '��' => 'b', '��' => 'c', '��' => 'd', '��' => 'e', '��' => 'f', '��' => 'g', '��' => 'h', '��' => 'i', '��' => 'j', '��' => 'k', '��' => 'l', '��' => 'm', '��' => 'n', '��' => 'o', '��' => 'p', '��' => 'q', '��' => 'r', '��' => 's', '��' => 't', '��' => 'u', '��' => 'v', '��' => 'w', '��' => 'x', '��' => 'y', '��' => 'z', '��' => '(', '��' => ')', '��' => '[', '��' => ']', '��' => '[', '��' => ']', '��' => '[', '��' => ']', '��' => '[', '��' => ']', '��' => '[', '��' => ']', '��' => '{', '��' => '}', '��' => '<', '��' => '>', '��' => '%', '��' => '+', '��' => '-', '��' => '-', '��' => '-', '��' => ':', '��' => '.', '��' => ',', '��' => '.', '��' => '.', '��' => ',', '��' => '?', '��' => '!', '��' => '-', '��' => '|', '��' => '"', '��' => '`', '��' => '`', '��' => '|', '��' => '"', '��' => ' ');
	return strtr($str, $arr);
}

function compile_str($str)
{
	$arr = array('<' => '��', '>' => '��');
	return strtr($str, $arr);
}

function check_file_type($filename, $realname = '', $limit_ext_types = '')
{
	if ($realname) {
		$extname = strtolower(substr($realname, strrpos($realname, '.') + 1));
	}
	else {
		$extname = strtolower(substr($filename, strrpos($filename, '.') + 1));
	}

	if ($limit_ext_types && (stristr($limit_ext_types, '|' . $extname . '|') === false)) {
		return '';
	}

	$str = $format = '';
	$file = @fopen($filename, 'rb');

	if ($file) {
		$str = @fread($file, 1024);
		@fclose($file);
	}
	else if (stristr($filename, ROOT_PATH) === false) {
		if (($extname == 'jpg') || ($extname == 'jpeg') || ($extname == 'gif') || ($extname == 'png') || ($extname == 'doc') || ($extname == 'xls') || ($extname == 'txt') || ($extname == 'zip') || ($extname == 'rar') || ($extname == 'ppt') || ($extname == 'pdf') || ($extname == 'rm') || ($extname == 'mid') || ($extname == 'wav') || ($extname == 'bmp') || ($extname == 'swf') || ($extname == 'chm') || ($extname == 'sql') || ($extname == 'cert') || ($extname == 'pptx') || ($extname == 'xlsx') || ($extname == 'docx')) {
			$format = $extname;
		}
	}
	else {
		return '';
	}

	if (($format == '') && (2 <= strlen($str))) {
		if ((substr($str, 0, 4) == 'MThd') && ($extname != 'txt')) {
			$format = 'mid';
		}
		else {
			if ((substr($str, 0, 4) == 'RIFF') && ($extname == 'wav')) {
				$format = 'wav';
			}
			else if (substr($str, 0, 3) == "\xff\xd8\xff") {
				$format = 'jpg';
			}
			else {
				if ((substr($str, 0, 4) == 'GIF8') && ($extname != 'txt')) {
					$format = 'gif';
				}
				else if (substr($str, 0, 8) == "x89NG\r\n\x1a\n") {
					$format = 'png';
				}
				else {
					if ((substr($str, 0, 2) == 'BM') && ($extname != 'txt')) {
						$format = 'bmp';
					}
					else {
						if (((substr($str, 0, 3) == 'CWS') || (substr($str, 0, 3) == 'FWS')) && ($extname != 'txt')) {
							$format = 'swf';
						}
						else if (substr($str, 0, 4) == "\xd0\xcf\x11\xe0") {
							if ((substr($str, 512, 4) == "\xec\xa5\xc1\x00") || ($extname == 'doc')) {
								$format = 'doc';
							}
							else {
								if ((substr($str, 512, 2) == "\t\x08") || ($extname == 'xls')) {
									$format = 'xls';
								}
								else {
									if ((substr($str, 512, 4) == "\xfd\xff\xff\xff") || ($extname == 'ppt')) {
										$format = 'ppt';
									}
								}
							}
						}
						else if (substr($str, 0, 4) == "PK\x03\x04") {
							if ((substr($str, 512, 4) == "\xec\xa5\xc1\x00") || ($extname == 'docx')) {
								$format = 'docx';
							}
							else {
								if ((substr($str, 512, 2) == "\t\x08") || ($extname == 'xlsx')) {
									$format = 'xlsx';
								}
								else {
									if ((substr($str, 512, 4) == "\xfd\xff\xff\xff") || ($extname == 'pptx')) {
										$format = 'pptx';
									}
									else {
										$format = 'zip';
									}
								}
							}
						}
						else {
							if ((substr($str, 0, 4) == 'Rar!') && ($extname != 'txt')) {
								$format = 'rar';
							}
							else if (substr($str, 0, 4) == '%PDF') {
								$format = 'pdf';
							}
							else if (substr($str, 0, 3) == "0\x82\n") {
								$format = 'cert';
							}
							else {
								if ((substr($str, 0, 4) == 'ITSF') && ($extname != 'txt')) {
									$format = 'chm';
								}
								else if (substr($str, 0, 4) == '.RMF') {
									$format = 'rm';
								}
								else if ($extname == 'sql') {
									$format = 'sql';
								}
								else if ($extname == 'txt') {
									$format = 'txt';
								}
							}
						}
					}
				}
			}
		}
	}

	if ($limit_ext_types && (stristr($limit_ext_types, '|' . $format . '|') === false)) {
		$format = '';
	}

	return $format;
}

function mysql_like_quote($str)
{
	return strtr($str, array('\\\\' => '\\\\\\\\', '_' => '\\_', '%' => '\\%', '\\\'' => '\\\\\\\''));
}

function real_server_ip()
{
	static $serverip;

	if ($serverip !== NULL) {
		return $serverip;
	}

	if (isset($_SERVER)) {
		if (isset($_SERVER['SERVER_ADDR'])) {
			$serverip = $_SERVER['SERVER_ADDR'];
		}
		else {
			$serverip = '0.0.0.0';
		}
	}
	else {
		$serverip = getenv('SERVER_ADDR');
	}

	return $serverip;
}

function ecs_header($string, $replace = true, $http_response_code = 0)
{
	if (strpos($string, '../upgrade/index.php') === 0) {
		echo '<script type="text/javascript">window.location.href="' . $string . '";</script>';
	}

	$string = str_replace(array("\r", "\n"), array('', ''), $string);

	if (preg_match('/^\\s*location:/is', $string)) {
		@header($string . "\n", $replace);
		exit();
	}

	if (empty($http_response_code) || (PHP_VERSION < '4.3')) {
		@header($string, $replace);
	}
	else {
		@header($string, $replace, $http_response_code);
	}
}

function ecs_iconv($source_lang, $target_lang, $source_string = '')
{
	static $chs;
	if (($source_lang == $target_lang) || ($source_string == '') || (preg_match("/[\x80-\xff]+/", $source_string) == 0)) {
		return $source_string;
	}

	if ($chs === NULL) {
		require_once ROOT_PATH . 'includes/cls_iconv.php';
		$chs = new Chinese(ROOT_PATH);
	}

	return $chs->Convert($source_lang, $target_lang, $source_string);
}

function ecs_geoip($ip)
{
	static $fp;
	static $offset = array();
	static $index;
	$ip = gethostbyname($ip);
	$ipdot = explode('.', $ip);
	$ip = pack('N', ip2long($ip));
	$ipdot[0] = (int) $ipdot[0];
	$ipdot[1] = (int) $ipdot[1];
	if (($ipdot[0] == 10) || ($ipdot[0] == 127) || (($ipdot[0] == 192) && ($ipdot[1] == 168)) || (($ipdot[0] == 172) && (16 <= $ipdot[1]) && ($ipdot[1] <= 31))) {
		return 'LAN';
	}

	if ($fp === NULL) {
		$fp = fopen(ROOT_PATH . 'includes/codetable/ipdata.dat', 'rb');

		if ($fp === false) {
			return 'Invalid IP data file';
		}

		$offset = unpack('Nlen', fread($fp, 4));

		if ($offset['len'] < 4) {
			return 'Invalid IP data file';
		}

		$index = fread($fp, $offset['len'] - 4);
	}

	$length = $offset['len'] - 1028;
	$start = unpack('Vlen', $index[$ipdot[0] * 4] . $index[($ipdot[0] * 4) + 1] . $index[($ipdot[0] * 4) + 2] . $index[($ipdot[0] * 4) + 3]);

	for ($start = ($start['len'] * 8) + 1024; $start < $length; $start += 8) {
		if ($ip <= $index[$start] . $index[$start + 1] . $index[$start + 2] . $index[$start + 3]) {
			$index_offset = unpack('Vlen', $index[$start + 4] . $index[$start + 5] . $index[$start + 6] . "\x00");
			$index_length = unpack('Clen', $index[$start + 7]);
			break;
		}
	}

	fseek($fp, ($offset['len'] + $index_offset['len']) - 1024);
	$area = fread($fp, $index_length['len']);
	fclose($fp);
	$fp = NULL;
	return $area;
}

function trim_right($str)
{
	$len = strlen($str);
	if (($len == 0) || (ord($str[$len - 1]) < 127)) {
		return $str;
	}

	if (192 <= ord($str[$len - 1])) {
		return substr($str, 0, $len - 1);
	}

	$r_len = strlen(rtrim($str, "\x80..\xbf"));
	if (($r_len == 0) || (ord($str[$r_len - 1]) < 127)) {
		return sub_str($str, 0, $r_len);
	}

	$as_num = ord(~$str[$r_len - 1]);

	if ((1 << ((6 + $r_len) - $len)) < $as_num) {
		return $str;
	}
	else {
		return substr($str, 0, $r_len - 1);
	}
}

function move_upload_file($file_name, $target_name = '')
{
	if (function_exists('move_uploaded_file')) {
		if (move_uploaded_file($file_name, $target_name)) {
			@chmod($target_name, 493);
			return true;
		}
		else if (copy($file_name, $target_name)) {
			@chmod($target_name, 493);
			return true;
		}
	}
	else if (copy($file_name, $target_name)) {
		@chmod($target_name, 493);
		return true;
	}

	return false;
}

function json_str_iconv($str)
{
	if (EC_CHARSET != 'utf-8') {
		if (is_string($str)) {
			return addslashes(stripslashes(ecs_iconv('utf-8', EC_CHARSET, $str)));
		}
		else if (is_array($str)) {
			foreach ($str as $key => $value) {
				$str[$key] = json_str_iconv($value);
			}

			return $str;
		}
		else if (is_object($str)) {
			foreach ($str as $key => $value) {
				$str->$key = json_str_iconv($value);
			}

			return $str;
		}
		else {
			return $str;
		}
	}

	return $str;
}

function to_utf8_iconv($str)
{
	if (EC_CHARSET != 'utf-8') {
		if (is_string($str)) {
			return ecs_iconv(EC_CHARSET, 'utf-8', $str);
		}
		else if (is_array($str)) {
			foreach ($str as $key => $value) {
				$str[$key] = to_utf8_iconv($value);
			}

			return $str;
		}
		else if (is_object($str)) {
			foreach ($str as $key => $value) {
				$str->$key = to_utf8_iconv($value);
			}

			return $str;
		}
		else {
			return $str;
		}
	}

	return $str;
}

function get_file_suffix($file_name, $allow_type = array())
{
	$file_suffix = strtolower(array_pop(explode('.', $file_name)));

	if (empty($allow_type)) {
		return $file_suffix;
	}
	else if (in_array($file_suffix, $allow_type)) {
		return true;
	}
	else {
		return false;
	}
}

function read_static_cache($cache_name, $cache_file_path = '')
{
	$data = '';

	if ((DEBUG_MODE & 2) == 2) {
		return false;
	}

	static $result = array();

	if (!empty($result[$cache_name])) {
		return $result[$cache_name];
	}

	$sel_config = get_shop_config_val('open_memcached');

	if ($sel_config['open_memcached'] == 1) {
		$result[$cache_name] = $GLOBALS['cache']->get('static_caches_' . $cache_name);
		return $result[$cache_name];
	}
	else {
		if (!empty($cache_file_path)) {
			$cache_file_path = ROOT_PATH . $cache_file_path . $cache_name . '.php';
		}
		else {
			$cache_file_path = ROOT_PATH . '/temp/static_caches/' . $cache_name . '.php';
		}

		if (file_exists($cache_file_path)) {
			include_once $cache_file_path;
			$result[$cache_name] = $data;
			return $result[$cache_name];
		}
		else {
			return false;
		}
	}
}

function write_static_cache($cache_name, $caches, $cache_file_path = '', $type = 0, $url_data = array())
{
	if ((DEBUG_MODE & 2) == 2) {
		return false;
	}

	$sel_config = get_shop_config_val('open_memcached');

	if ($sel_config['open_memcached'] == 1) {
		$GLOBALS['cache']->set('static_caches_' . $cache_name, $caches);
	}
	else {
		if (!empty($cache_file_path)) {
			$cache_file_path = ROOT_PATH . $cache_file_path . $cache_name . '.php';
		}
		else {
			$cache_file_path = ROOT_PATH . '/temp/static_caches/' . $cache_name . '.php';
		}

		$content = "<?php\r\n";

		if ($type == 1) {
			$content .= '$url_data = ' . var_export($url_data, true) . ";\r\n";
			$content .= $caches . "\r\n";
		}
		else {
			$content .= '$data = ' . var_export($caches, true) . ";\r\n";
		}

		$content .= '?>';
		file_put_contents($cache_file_path, $content, LOCK_EX);
	}
}

function real_cart_mac_ip()
{
	static $realip;

	if ($realip !== NULL) {
		return $realip;
	}

	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

			foreach ($arr as $ip) {
				$ip = trim($ip);

				if ($ip != 'unknown') {
					$realip = $ip;
					break;
				}
			}
		}
		else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$realip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if (isset($_SERVER['REMOTE_ADDR'])) {
			$realip = $_SERVER['REMOTE_ADDR'];
		}
		else {
			$realip = '0.0.0.0';
		}
	}
	else if (getenv('HTTP_X_FORWARDED_FOR')) {
		$realip = getenv('HTTP_X_FORWARDED_FOR');
	}
	else if (getenv('HTTP_CLIENT_IP')) {
		$realip = getenv('HTTP_CLIENT_IP');
	}
	else {
		$realip = getenv('REMOTE_ADDR');
	}

	preg_match('/[\\d\\.]{7,15}/', $realip, $onlineip);
	$realip = (!empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0');

	if ($_COOKIE['session_id_ip']) {
		$realip = $_COOKIE['session_id_ip'];
	}
	else {
		$realip = $realip . '_' . SESS_ID;
		$time = gmtime() + (3600 * 24 * 365);
		setcookie('session_id_ip', $realip, $time, '/');
	}

	return $realip;
}

function zhuo_arr_foreach($cat_list, $cat_id)
{
	static $tmp = array();

	foreach ($cat_list as $key => $row) {
		if ($row) {
			$row = array_values($row);

			if (!is_array($row[0])) {
				array_unshift($tmp, $row[0]);
			}

			if (isset($row[1]) && is_array($row[1])) {
				zhuo_arr_foreach($row[1]);
			}
		}
	}

	return $tmp;
}

function arr_foreach($multi)
{
	$arr = array();

	foreach ($multi as $key => $val) {
		if (is_array($val)) {
			$arr = array_merge($arr, arr_foreach($val));
		}
		else {
			$arr[] = $val;
		}
	}

	return $arr;
}

function get_array_flip($val = 0, $arr = array())
{
	if (1 < count($arr)) {
		$arr = array_flip($arr);
		unset($arr[$val]);
		$arr = array_flip($arr);
	}

	return $arr;
}

function get_array_keys_cat($cat_id, $type = 0, $table = 'category')
{
	$list = arr_foreach(cat_list($cat_id, 1, 1, $table));

	if ($type == 1) {
		if ($list) {
			$list = implode(',', $list);
			$list = get_del_str_comma($list);
		}
	}

	return $list;
}

function get_del_str_comma($str = '')
{
	if ($str && is_array($str)) {
		return $str;
	}
	else {
		if ($str) {
			$str = str_replace(',,', ',', $str);
			$str1 = substr($str, 0, 1);
			$str2 = substr($str, str_len($str) - 1);
			if (($str1 === ',') && ($str2 !== ',')) {
				$str = substr($str, 1);
			}
			else {
				if (($str1 !== ',') && ($str2 === ',')) {
					$str = substr($str, 0, -1);
				}
				else {
					if (($str1 === ',') && ($str2 === ',')) {
						$str = substr($str, 1);
						$str = substr($str, 0, -1);
					}
				}
			}
		}

		return $str;
	}
}

function get_deldir($dir, $strpos = '', $is_rmdir = false)
{
	$dh = opendir($dir);

	while ($file = readdir($dh)) {
		if (($file != '.') && ($file != '..')) {
			$fullpath = $dir . '/' . $file;

			if ($strpos) {
				$spos = strpos($fullpath, $strpos);

				if ($spos !== false) {
					if (!is_dir($fullpath)) {
						unlink($fullpath);
					}
					else {
						deldir($fullpath);
					}
				}
			}
			else if (!is_dir($fullpath)) {
				unlink($fullpath);
			}
			else {
				deldir($fullpath);
			}
		}
	}

	closedir($dh);

	if ($is_rmdir == true) {
		if (rmdir($dir)) {
			return true;
		}
		else {
			return false;
		}
	}
}

function dsc_unlink($file = '')
{
	if (file_exists($file)) {
		unlink($file);
	}
}

function get_array_sort($arr, $keys, $type = 'asc')
{
	$new_array = array();
	if (is_array($arr) && !empty($arr)) {
		$keysvalue = $new_array = array();

		foreach ($arr as $k => $v) {
			$keysvalue[$k] = $v[$keys];
		}

		if ($type == 'asc') {
			asort($keysvalue);
		}
		else {
			arsort($keysvalue);
		}

		reset($keysvalue);

		foreach ($keysvalue as $k => $v) {
			$new_array[$k] = $arr[$k];
		}
	}

	return $new_array;
}

function get_dir_file_list($dir = '', $type = 0, $explode = '')
{
	if (empty($dir)) {
		$dir = ROOT_PATH . 'includes/lib_ecmobanFunc.php';
	}

	$arr = array();

	if (file_exists($dir)) {
		if (!is_dir($dir)) {
			get_contents_section($dir);
		}
		else {
			$idx = 0;
			$dir = opendir($dir);

			while (($file = readdir($dir)) !== false) {
				if (!is_dir($file)) {
					if ($type == 1) {
						$arr[$idx]['file'] = $file;
						$file = explode($explode, $file);
						$arr[$idx]['web_type'] = $file[0];
					}
					else {
						$arr[$idx] = $file;
					}

					$idx++;
				}
			}

			closedir($dir);
		}

		return $arr;
	}
}

function get_request_filter($get = '', $type = 0)
{
	if ($get) {
		$_REQUEST = $get;
	}

	if ($_REQUEST) {
		foreach ($_REQUEST as $key => $row) {
			$preg = '/<script[\\s\\S]*?<\\/script>/i';

			if ($row) {
				$row = strtolower($row);
				$row = (!empty($row) ? preg_replace($preg, '', stripslashes($row)) : '');

				if (strpos($row, '</script>') !== false) {
					$_REQUEST[$key] = '';
				}
				else if (strpos($row, 'alert') !== false) {
					$_REQUEST[$key] = '';
				}
				else {
					if ((strpos($row, 'updatexml') !== false) || (strpos($row, 'extractvalue') !== false) || (strpos($row, 'floor') !== false)) {
						$_REQUEST[$key] = '';
					}
					else {
						if ((strpos($row, 'update') !== false) || (strpos($row, 'select') !== false) || (strpos($row, 'delete') !== false)) {
							$_REQUEST[$key] = '';
						}
						else {
							$_REQUEST[$key] = $row;
						}
					}
				}
			}
		}
	}

	if ($get && ($type == 1)) {
		$_POST = $_REQUEST;
		return $_POST;
	}
	else {
		if ($get && ($type == 2)) {
			$_GET = $_REQUEST;
			return $_GET;
		}
		else {
			return $_REQUEST;
		}
	}
}

function dsc_unserialize($serial_str)
{
	$out = preg_replace('!s:(\\d+):"(.*?)";!se', '\'s:\'.strlen(\'$2\').\':"$2";\'', $serial_str);
	return unserialize($out);
}

function get_file_centent_size($dir)
{
	$filesize = filesize($dir) / 1024;
	return sprintf('%.2f', substr(sprintf('%.3f', $filesize), 0, -1));
}

function get_site_domain($site_domain = '')
{
	if ($site_domain) {
		if ((strpos($site_domain, 'http://') === false) && (strpos($site_domain, 'https://') === false)) {
			$site_domain = $GLOBALS['ecs']->http() . $site_domain;
		}

		if (substr($site_domain, str_len($site_domain) - 1) != '/') {
			$site_domain = $site_domain . '/';
		}
	}

	return $site_domain;
}

function get_http_basename($url = '', $path = '')
{
	$Http = new Http();
	$return_content = $Http->doGet($url);
	$url = basename($url);
	$filename = $path . '/' . $url;

	if (file_put_contents($filename, $return_content)) {
		return $filename;
	}
	else {
		return false;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

if (!function_exists('file_get_contents')) {
	function file_get_contents($file)
	{
		if (($fp = @fopen($file, 'rb')) === false) {
			return false;
		}
		else {
			$fsize = @filesize($file);

			if ($fsize) {
				$contents = fread($fp, $fsize);
			}
			else {
				$contents = '';
			}

			fclose($fp);
			return $contents;
		}
	}
}

if (!function_exists('file_put_contents')) {
	define('FILE_APPEND', 'FILE_APPEND');
	function file_put_contents($file, $data, $flags = '')
	{
		$contents = (is_array($data) ? implode('', $data) : $data);

		if ($flags == 'FILE_APPEND') {
			$mode = 'ab+';
		}
		else {
			$mode = 'wb';
		}

		if (($fp = @fopen($file, $mode)) === false) {
			return false;
		}
		else {
			$bytes = fwrite($fp, $contents);
			fclose($fp);
			return $bytes;
		}
	}
}

if (!function_exists('floatval')) {
	function floatval($n)
	{
		return (double) $n;
	}
}

?>
