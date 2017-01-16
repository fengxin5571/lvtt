<?php
//zend by QQ:2172298892

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

define('ECS_ADMIN', true);
error_reporting(30719);

if (__FILE__ == '') {
	exit('Fatal error code: 0');
}

@ini_set('memory_limit', '1024M');
@ini_set('session.cache_expire', 180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies', 1);
@ini_set('session.auto_start', 0);
@ini_set('display_errors', 1);

if (DIRECTORY_SEPARATOR == '\\') {
	@ini_set('include_path', '.;' . ROOT_PATH);
}
else {
	@ini_set('include_path', '.:' . ROOT_PATH);
}

if (file_exists('../data/config.php')) {
	include '../data/config.php';
}
else {
	include '../includes/config.php';
}

if (!defined('GUIDER_PATH')) {
	define('GUIDER_PATH', 'guider');
}

define('ROOT_PATH', str_replace(GUIDER_PATH . '/includes/init.php', '', str_replace('\\', '/', __FILE__)));
if (defined('DEBUG_MODE') == false) {
	define('DEBUG_MODE', 0);
}

if (('5.1' <= PHP_VERSION) && !empty($timezone)) {
	date_default_timezone_set($timezone);
}

if (isset($_SERVER['PHP_SELF'])) {
	define('PHP_SELF', $_SERVER['PHP_SELF']);
}
else {
	define('PHP_SELF', $_SERVER['SCRIPT_NAME']);
}

require ROOT_PATH . 'includes/Http.class.php';
require ROOT_PATH . 'includes/inc_constant.php';
require ROOT_PATH . 'includes/cls_ecshop.php';
require ROOT_PATH . 'includes/cls_error.php';
require ROOT_PATH . 'includes/lib_time.php';
require ROOT_PATH . 'includes/lib_base.php';
require ROOT_PATH . 'includes/lib_common.php';
require ROOT_PATH . 'includes/cls_pinyin.php';
require ROOT_PATH . 'includes/lib_scws.php';
require ROOT_PATH . 'includes/lib_main.php';
require ROOT_PATH . GUIDER_PATH . '/includes/lib_main.php';
require ROOT_PATH . GUIDER_PATH . '/includes/cls_exchange.php';
require ROOT_PATH . 'includes/lib_ecmoban.php';
require ROOT_PATH . 'includes/lib_ecmobanFunc.php';
require ROOT_PATH . 'includes/lib_publicfunc.php';
require ROOT_PATH . 'data/sms_config.php';

if (!get_magic_quotes_gpc()) {
	if (!empty($_GET)) {
		$_GET = addslashes_deep($_GET);
	}

	if (!empty($_POST)) {
		$_POST = addslashes_deep($_POST);
	}

	$_COOKIE = addslashes_deep($_COOKIE);
	$_REQUEST = addslashes_deep($_REQUEST);
}
if (strpos(PHP_SELF, '.php/') !== false) {
	ecs_header('Location:' . substr(PHP_SELF, 0, strpos(PHP_SELF, '.php/') + 4) . "\n");
	exit();
}

$ecs = new ECS($db_name, $prefix);
define('DATA_DIR', $ecs->data_dir());
define('IMAGE_DIR', $ecs->image_dir());
require ROOT_PATH . 'includes/cls_mysql.php';
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;
$err = new ecs_error('message.htm');
require ROOT_PATH . 'includes/cls_session.php';
$sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'), 'ECSCP_GUIDER_ID');

if (!isset($_REQUEST['act'])) {
	$_REQUEST['act'] = '';
}
else {
	if ((($_REQUEST['act'] == 'login') || ($_REQUEST['act'] == 'logout') || ($_REQUEST['act'] == 'signin')) && (strpos(PHP_SELF, '/privilege.php') === false)) {
	    $_REQUEST['act'] = '';
	}
	else {
		if ((($_REQUEST['act'] == 'forget_pwd') || ($_REQUEST['act'] == 'reset_pwd') || ($_REQUEST['act'] == 'get_pwd')) && (strpos(PHP_SELF, '/get_password.php') === false)) {
			$_REQUEST['act'] = '';
		}
	}
}

$sel_config = get_shop_config_val('open_memcached');


if ($sel_config['open_memcached'] == 1) {
	require ROOT_PATH . 'includes/cls_cache.php';
	require ROOT_PATH . 'data/cache_config.php';
	$cache = new cls_cache($cache_config);
}

$_CFG = load_config();

$_CFG['editing_tools'] = 'seller_ueditor';

if ($_REQUEST['act'] == 'captcha') {
	require ROOT_PATH . '/includes/cls_captcha_verify.php';
	$code_config = array('imageW' => '120', 'imageH' => '36', 'fontSize' => '18', 'length' => '4', 'useNoise' => false);
	$code_config['seKey'] = 'admin_login';
	$img = new Verify($code_config);
	$img->entry();
	exit();
}

require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/common_merchants.php';
require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/log_action.php';

if (file_exists(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/' . basename(PHP_SELF))) {
	include ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/' . basename(PHP_SELF);
}

if (!file_exists('../temp/caches')) {
	@mkdir('../temp/caches', 511);
	@chmod('../temp/caches', 511);
}

if (!file_exists('../temp/compiled/' . GUIDER_PATH)) {
	@mkdir('../temp/compiled/' . GUIDER_PATH, 511);
	@chmod('../temp/compiled/' . GUIDER_PATH, 511);
}

clearstatcache();

if (!isset($_CFG['dsc_version'])) {
	$_CFG['dsc_version'] = 'v1.8';
}

if ((preg_replace('/(?:\\.|\\s+)[a-z]*$/i', '', $_CFG['dsc_version']) != preg_replace('/(?:\\.|\\s+)[a-z]*$/i', '', VERSION)) && file_exists('../upgrade/index.php')) {
	ecs_header("Location: ../upgrade/index.php\n");
	exit();
}

require ROOT_PATH . 'includes/cls_template.php';
$smarty = new cls_template();
$smarty->template_dir = ROOT_PATH . GUIDER_PATH . '/templates';
$smarty->compile_dir = ROOT_PATH . 'temp/compiled/' . GUIDER_PATH;

if ((DEBUG_MODE & 2) == 2) {
	$smarty->force_compile = true;
}

$smarty->assign('lang', $_LANG);
$smarty->assign('help_open', $_CFG['help_open']);

if (isset($_CFG['enable_order_check'])) {
	$smarty->assign('enable_order_check', $_CFG['enable_order_check']);
}
else {
	$smarty->assign('enable_order_check', 0);
}

if (isset($_GET['ent_id']) && isset($_GET['ent_ac']) && isset($_GET['ent_sign']) && isset($_GET['ent_email'])) {
	$ent_id = trim($_GET['ent_id']);
	$ent_ac = trim($_GET['ent_ac']);
	$ent_sign = trim($_GET['ent_sign']);
	$ent_email = trim($_GET['ent_email']);
	$certificate_id = trim($_CFG['certificate_id']);
	$domain_url = $ecs->url();
	$token = $_GET['token'];

	if ($token == md5(md5($_CFG['token']) . $domain_url . GUIDER_PATH)) {
		require ROOT_PATH . 'includes/cls_transport.php';
		$t = new transport('-1', 5);
		$apiget = 'act=ent_sign&ent_id= ' . $ent_id . ' & certificate_id=' . $certificate_id;
		$t->request('http://cloud.ecshop.com/api.php', $apiget);
		$db->query('UPDATE ' . $ecs->table('shop_config') . ' SET value = "' . $ent_id . '" WHERE code = "ent_id"');
		$db->query('UPDATE ' . $ecs->table('shop_config') . ' SET value = "' . $ent_ac . '" WHERE code = "ent_ac"');
		$db->query('UPDATE ' . $ecs->table('shop_config') . ' SET value = "' . $ent_sign . '" WHERE code = "ent_sign"');
		$db->query('UPDATE ' . $ecs->table('shop_config') . ' SET value = "' . $ent_email . '" WHERE code = "ent_email"');
		clear_cache_files();
		ecs_header("Location: ./index.php\n");
	}
}

if ((!isset($_SESSION['guide_id']) || (intval($_SESSION['guide_id']) <= 0)) && ($_REQUEST['act'] != 'login') && ($_REQUEST['act'] != 'signin') && ($_REQUEST['act'] != 'check_user_name') && ($_REQUEST['act'] != 'check_user_password') && ($_REQUEST['act'] != 'forget_pwd') && ($_REQUEST['act'] != 'reset_pwd') && ($_REQUEST['act'] != 'check_order')) {
    if (!empty($_COOKIE['ECSCP']['guide_id']) && !empty($_COOKIE['ECSCP']['guide_pass'])) {
		$sql = 'SELECT guide_id,user_id,login_name,password,guide_name,steps_audit,guide_audit,last_login ' . ' FROM ' . $ecs->table('guide_shop_information') . ' WHERE guide_id = \'' . intval($_COOKIE['ECSCP']['guide_id']) . '\'';
		$row = $db->GetRow($sql);
        
		if (!$row) {
			setcookie($_COOKIE['ECSCP']['guide_id'], '', 1);
			setcookie($_COOKIE['ECSCP']['guide_pass'], '', 1);

			if (!empty($_REQUEST['is_ajax'])) {
				make_json_error($_LANG['priv_error']);
			}
			else {
				ecs_header("Location: privilege.php?act=login\n");
			}

			exit();
		}
		else if (md5($row['password'] . $_CFG['hash_code']) == $_COOKIE['ECSCP']['guide_pass']) {
			!isset($row['last_time']) && $row['last_time'] = '';
			set_admin_session($row['user_id'], $row['user_name'], $row['action_list'], $row['last_time']);
			$db->query('UPDATE ' . $ecs->table('guide_shop_information') . ' SET last_login = \'' . gmtime() . '\''. ' WHERE guide_id = \'' . $_SESSION['guide_id'] . '\'');
		}
		else {
			setcookie($_COOKIE['ECSCP']['guide_id'], '', 1);
			setcookie($_COOKIE['ECSCP']['guide_pass'], '', 1);

			if (!empty($_REQUEST['is_ajax'])) {
				make_json_error($_LANG['priv_error']);
			}
			else {
				ecs_header("Location: privilege.php?act=login\n");
			}

			exit();
		}
	}
	else {
		if (!empty($_REQUEST['is_ajax'])) {
			make_json_error($_LANG['priv_error']);
		}
		else {
			ecs_header("Location: privilege.php?act=login\n");
		}

		exit();
	}
}

$smarty->assign('token', $_CFG['token']);
if (($_REQUEST['act'] != 'login') && ($_REQUEST['act'] != 'signin') && ($_REQUEST['act'] != 'forget_pwd') && ($_REQUEST['act'] != 'reset_pwd') && ($_REQUEST['act'] != 'check_order')) {
	$admin_path = preg_replace('/:\\d+/', '', $ecs->guider_url()) . GUIDER_PATH;
	
	if (!empty($_SERVER['HTTP_REFERER']) && (strpos(preg_replace('/:\\d+/', '', $_SERVER['HTTP_REFERER']), $admin_path) === false)) {
		if (!empty($_REQUEST['is_ajax'])) {
			make_json_error($_LANG['priv_error']);
		}
		else {
			ecs_header("Location: privilege.php?act=login\n");
		}

		exit();
	}
}

if (isset($_SESSION['guide_name'])) {
	$admin_sql = 'select guide_id from ' . $GLOBALS['ecs']->table('guide_shop_information') . ' where guide_name = \'' . $_SESSION['guide_name'] . '\'';
	$uid = $GLOBALS['db']->getOne($admin_sql);
	$uname = '';
	if ((0 < $_SESSION['guide_id']) && ($_SESSION['guide_id'] != $uid)) {
		$admin_sql = 'select guide_name from ' . $GLOBALS['ecs']->table('guide_shop_information') . ' where guide_id = \'' . $_SESSION['guide_id'] . '\'';
		$uname = $GLOBALS['db']->getOne($admin_sql);
		$_SESSION['guide_name'] = $uname;
	}
}

if (($_REQUEST['act'] == 'phpinfo') && function_exists('phpinfo')) {
	phpinfo();
	exit();
}

header('content-type: text/html; charset=' . EC_CHARSET);
header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

if ((DEBUG_MODE & 1) == 1) {
	error_reporting(30719);
}
else {
	error_reporting(30719 ^ 8);
}

if ((DEBUG_MODE & 4) == 4) {
	include ROOT_PATH . 'includes/lib.debug.php';
}


include_once 'includes/inc_priv.php';
include_once 'includes/inc_menu.php';
set_guider_menu();//获取导游菜单
get_menu_name();
get_user_menu_pro();
unset($modules);
unset($purview);


$smarty->assign('site_url', str_replace(array('http://', 'https://'), '', $ecs->get_domain()));
/*
 * 获取导游菜单
 * 
 */
function set_guider_menu()
{
    global $modules;
    global $purview;
    global $_LANG;

    foreach ($modules as $key => $value) {
        ksort($modules[$key]);
    }
    
    ksort($modules);
    $action_list = explode(',', $_SESSION['seller_action_list']);
    $action_menu = array();

    foreach ($purview as $key => $val) {
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                if (in_array($v, $action_list)) {
                    $action_menu[$key] = $v;
                }
            }
        }
        else if (in_array($val, $action_list)) {
            $action_menu[$key] = $val;
        }
    }

    foreach ($modules as $key => $val) {
        foreach ($val as $k => $v) {
            if (!array_key_exists($k, $action_menu)) {
                unset($modules[$key][$k]);
            }
        }

        if (empty($modules[$key])) {
            unset($modules[$key]);
        }
    }

    $menu = array();
    $i = 0;

    foreach ($modules as $key => $val) {
        $menu[$i] = array(
            'action'   => $key,
            'label'    => get_menu_url(reset($val), $_LANG[$key]),
            'url'      => get_menu_url(reset($val)),
            'children' => array()
        );

        foreach ($val as $k => $v) {
            $menu[$i]['children'][] = array('action' => $k, 'label' => get_menu_url($v, $_LANG[$k]), 'url' => get_menu_url($v), 'status' => get_user_menu_status($k));
        }

        $i++;
    }

    $GLOBALS['smarty']->assign('guider_menu', $menu);
}
function get_menu_url($url = '', $name = '')
{
    if ($url) {
        $url_arr = explode('?', $url);
        if (!$url_arr[0] || !is_file($url_arr[0])) {
            $url = '';
            if ($name && $url) {
                $name = '<span style="text-decoration: line-through; color:#ccc; ">' . $name . '</span>';
            }
        }
    }

    if ($name) {
        return $name;
    }
    else {
        return $url;
    }
}
function get_menu_name()
{
    global $modules;
    global $purview;
    @$url = basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'];

    if ($url) {
        $url = str_replace('&uselastfilter=1', '', $url);
        $menu_arr = get_menu_arr($url, $modules);

        if ($menu_arr) {
            $GLOBALS['smarty']->assign('menu_select', $menu_arr);
            return $menu_arr;
        }
    }

    return false;
}

function get_menu_arr($url = '', $list = array())
{
    static $menu_arr = array();
    static $menu_key;

    foreach ($list as $key => $val) {
        if (is_array($val)) {
            $menu_key = $key;
            get_menu_arr($url, $val);
        }
        else if ($val == $url) {
            $menu_arr['action'] = $menu_key;
            $menu_arr['current'] = $key;
        }
    }

    return $menu_arr;
}

function get_user_menu_pro()
{
    $user_menu_pro = array();
    $user_menu_arr = get_user_menu_list();

    if ($user_menu_arr) {
        foreach ($user_menu_arr as $key => $val) {
            $user_menu_pro[$key] = get_menu_info($val);
        }

        $GLOBALS['smarty']->assign('user_menu_pro', $user_menu_pro);
        return $user_menu_pro;
    }

    return false;
}

function get_user_menu_list()
{
    $adminru = get_admin_ru_id();

    if (0 < $adminru['ru_id']) {
        $sql = ' SELECT user_menu FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = \'' . $adminru['ru_id'] . '\' ';
        $user_menu_str = $GLOBALS['db']->getOne($sql);

        if ($user_menu_str) {
            $user_menu_arr = explode(',', $user_menu_str);
            return $user_menu_arr;
        }
    }

    return false;
}

function get_user_menu_status($action = '')
{
    $user_menu_arr = get_user_menu_list();
    if ($user_menu_arr && in_array($action, $user_menu_arr)) {
        return 1;
    }
    else {
        return 0;
    }
}

function get_menu_info($action = '')
{
    global $modules;
    global $_LANG;

    foreach ($modules as $key => $val) {
        foreach ($val as $k => $v) {
            if ($k == $action) {
                $user_info = array('action' => $k, 'label' => $_LANG[$k], 'url' => $v);
                return $user_info;
            }
        }
    }

    return false;
}
/*
 * 获取导游详细信息
 * @param      $guide_id //导游id
 * 
 * @return     $arr
 * 
 */
function get_guide_info($guide_id){
    $sql = 'SELECT g.*,m.*,u.user_name  FROM '.$GLOBALS['ecs']->table("guide_shop_information"). ' AS g LEFT JOIN '.$GLOBALS['ecs']->table("merchants_steps_fields"). 'AS m ON m.user_id=g.user_id '.
        'LEFT JOIN '.$GLOBALS['ecs']->table('users').' AS u ON g.user_id =u.user_id '.
        'WHERE g.guide_id='.$guide_id;
    $arr=$GLOBALS['db']->getRow($sql);
    foreach ($arr as $key=>$val){
        $key =='last_login' ?$arr[$key]=local_date('Y-m-d H:i:s', $val):$arr;
        $key =='add_time'   ?$arr[$key]=local_date('Y-m-d H:i:s', $val):$arr;
    }
    return $arr;
}
?>
