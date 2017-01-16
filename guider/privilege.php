<?php 
/*
 * 导游管理员信息以及权限管理程序
 * 
 */
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$smarty->assign('menus', $_SESSION['menus']);

if (empty($_REQUEST['act'])) {
    $_REQUEST['act'] = 'login';
}
else {
    $_REQUEST['act'] = trim($_REQUEST['act']);
}
$exc = new exchange($ecs->table('guide_shop_information'), $db, 'guide_id', 'guide_name');
$php_self = get_php_self(1);
$smarty->assign('php_self', $php_self);

if ($_REQUEST['act'] == 'logout') {//导游登出
    setcookie('ECSCP[guide_id]', '', 1);
    setcookie('ECSCP[guide_pass]', '', 1);
    $sess->destroy_session();
    $_REQUEST['act'] = 'login';
}

if ($_REQUEST['act'] == 'login') {// 导游登录
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    if ((intval($_CFG['captcha']) & CAPTCHA_ADMIN) && (0 < gd_version())) {
        $smarty->assign('gd_version', gd_version());
        $smarty->assign('random', mt_rand());
    }

    $smarty->display('login.dwt');
}
else if ($_REQUEST['act'] == 'signin') {//处理登录
    if ((0 < gd_version()) && (intval($_CFG['captcha']) & CAPTCHA_ADMIN)) {
        require ROOT_PATH . '/includes/cls_captcha_verify.php';
        $captcha = (isset($_POST['captcha']) ? trim($_POST['captcha']) : '');
        $verify = new Verify();
        $captcha_code = $verify->check($captcha, 'admin_login');
    
        if (!$captcha_code) {
            sys_msg($_LANG['captcha_error'], 1);
        }
    }
    
    $_POST['username'] = isset($_POST['login_name']) ? trim($_POST['login_name']) : '';
    $_POST['password'] = isset($_POST['password']) ? trim($_POST['password']) : '';
    $sql = 'SELECT `ec_salt` FROM ' . $ecs->table('admin_user') . 'WHERE user_name = \'' . $_POST['login_name'] . '\'';
    $ec_salt = $db->getOne($sql);//获取加密因子

	if (!empty($ec_salt)) {
		$sql = 'SELECT user_id,ru_id, user_name, password, last_login, action_list, last_login,suppliers_id,ec_salt' . ' FROM ' . $ecs->table('admin_user') . ' WHERE user_name = \'' . $_POST['username'] . '\' AND password = \'' . md5(md5($_POST['password']) . $ec_salt) . '\'';
	}
	else {
		$sql = 'SELECT user_id,ru_id, user_name, password, last_login, action_list, last_login,suppliers_id,ec_salt' . ' FROM ' . $ecs->table('admin_user') . ' WHERE user_name = \'' . $_POST['username'] . '\' AND password = \'' . md5($_POST['password']) . '\'';
	}

	$row = $db->getRow($sql);
	if ($row) {
	    if ($row['ru_id']) {//如果是商户账号登录提示错误
	        sys_msg('导游后台，商户禁止入内', 1);
	    }else{//如果是平台管理员账号登录提出错误
	        sys_msg('导游后台，平台禁止入内', 1);
	    }

	}
	else {
	    $sql ='SELECT guide_id,user_id,login_name,password,guide_name,steps_audit,guide_audit,last_login '.' FROM '.$ecs->table('guide_shop_information').' WHERE login_name = \'' . $_POST['username'] .'\' AND password = \''.md5($_POST['password']). '\'';
	    $row = $db->getRow($sql);
	    if($row){
    	    set_admin_session($row['guide_id'], $row['guide_name'], $row['last_login']);//设置session
    	    
    	    $db->query('UPDATE ' . $ecs->table('guide_shop_information') . ' SET last_login=\'' . gmtime() . '\''  .' WHERE guide_id=\'' . $_SESSION['guide_id'] . '\'');
    	     
    	    if (isset($_POST['remember'])) {// 记住密码
    	        $time = gmtime() + (3600 * 24 * 365);
    	        setcookie('ECSCP[guide_id]', $row['guide_id'], $time);
    	        setcookie('ECSCP[guide_pass]', md5($row['password'] . $_CFG['hash_code']), $time);
    	    }
    	    
    	    clear_cart();
    	    
    	    ecs_header("Location: ./index.php\n");
    	    exit();
	    }
	    sys_msg($_LANG['login_faild'], 1);
	}
}
else{
    if ( $_REQUEST['act'] == 'update_self') {
       $guide_id = (!empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
       $login_name = (!empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '');
       $guide_email = (!empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '');
       $password = (!empty($_POST['new_password']) ? md5(trim($_POST['new_password'])): '');
       $user_id  = (!empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0);
       if (!empty($login_name)) {//判断此用户名是否已经存在
           $is_only = $exc->num('login_name', $login_name, $guide_id);
           if ($is_only == 1) {
               sys_msg(sprintf($_LANG['guide_name_exist'], stripslashes($admin_name)), 1);
           }
       }
       $pwd_modified = false;
       
       if (!empty($_POST['new_password'])) {
           $sql = 'SELECT password FROM ' . $ecs->table('guide_shop_information') . ' WHERE guide_id = \'' . $guide_id . '\'';
           $old_password = $db->getOne($sql);
           $old_ec_password = md5($_POST['old_password']);
           if ($old_password != $old_ec_password) {
               $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
               sys_msg($_LANG['pwd_error'], 0, $link);
           }
       
           if ($_POST['new_password'] != $_POST['pwd_confirm']) {
               $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
               sys_msg($_LANG['js_languages']['password_error'], 0, $link);
           }
           else {
               $pwd_modified = true;
           }
       }
       if ($pwd_modified) {//如果为真则修改导游登录密码
           $parent=array(
               'login_name'=>$login_name,
               'password'  =>$password,
           );
           $email=array('contactEmail'=>$guide_email);
           $db->autoExecute($ecs->table("guide_shop_information"), $parent,'UPDATE','guide_id='.$guide_id);
           $db->autoExecute($ecs->table("merchants_steps_fields"), $email,'UPDATE','user_id='.$user_id.' AND identity=1 ');
       }
       else {//反之只修改导游用户名和邮箱
           $parent=array(
               'login_name'=>$login_name,
           );
           $email=array('contactEmail'=>$guide_email);
           $db->autoExecute($ecs->table("guide_shop_information"), $parent,'UPDATE','guide_id='.$guide_id);
           $db->autoExecute($ecs->table("merchants_steps_fields"), $email,'UPDATE','user_id='.$user_id.' AND identity=1 ');
       }
       if ($pwd_modified && ($_REQUEST['act'] == 'update_self')) {
           $sess->delete_spec_admin_session($_SESSION['guide_id']);
           $msg = $_LANG['edit_password_succeed'];
       }
       else {
           $msg = $_LANG['edit_profile_succeed'];
       }
       $link[] = array('text' => strpos($g_link, 'list') ? $_LANG['back_admin_list'] : $_LANG['modif_info'], 'href' => $g_link);
       sys_msg($msg . '<script>parent.document.getElementById(\'header-frame\').contentWindow.document.location.reload();</script>', 0, $link);
    }
    else if ($_REQUEST['act'] == 'check_user_name') {//检查导游登录名
        $result = array('error' => 0, 'message' => '', 'content' => '');
        $user_name = (empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']));
        $user_password = (empty($_REQUEST['user_password']) ? '' : trim($_REQUEST['user_password']));
    
        if ($user_name) {
            $sql = ' SELECT guide_id FROM ' . $GLOBALS['ecs']->table('guide_shop_information') . ' WHERE login_name = \'' . $user_name . '\' LIMIT 1';
    
            if ($GLOBALS['db']->getOne($sql)) {
                $result['error'] = 1;
            }
        }
    
        exit(json_encode($result));
    }
    else if ($_REQUEST['act'] == 'check_user_password') {//检查导游密码
        $result = array('error' => 0, 'message' => '', 'content' => '');
        $user_name = (empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']));
        $user_password = (empty($_REQUEST['user_password']) ? '' : trim($_REQUEST['user_password']));
        if($user_password){
            $sql = ' SELECT guide_id FROM ' . $GLOBALS['ecs']->table('guide_shop_information') . ' WHERE password = \'' . md5($user_password) . '\' LIMIT 1';
            
            if ($GLOBALS['db']->getOne($sql)) {
                $result['error'] = 1;
            }
        }
    
        
    
        exit(json_encode($result));
    }
    else if ($_REQUEST['act'] == 'modif') {//个人设置
        $sql = 'SELECT code FROM ' . $ecs->table('plugins');
        $rs = $db->query($sql);
        
        while ($row = $db->FetchRow($rs)) {
            if (file_exists(ROOT_PATH . 'plugins/' . $row['code'] . '/languages/common_' . $_CFG['lang'] . '.php')) {
                include_once ROOT_PATH . 'plugins/' . $row['code'] . '/languages/common_' . $_CFG['lang'] . '.php';
            }
        
            if (file_exists(ROOT_PATH . 'plugins/' . $row['code'] . '/languages/inc_menu.php')) {
                include_once ROOT_PATH . 'plugins/' . $row['code'] . '/languages/inc_menu.php';
            }
        }
        $guider=get_guide_info($_SESSION['guide_id']);
        $smarty->assign('guider',$guider);
        $smarty->assign('lang', $_LANG);
        $smarty->assign('ur_here', $_LANG['guider_modif_info']);
        $smarty->assign('form_act', 'update_self');
        assign_query_info();
        $smarty->display('privilege_info.dwt');
    }
}

/*
 * 清空游客身份记录的购物车
 * 
 */
function clear_cart()
{
    $sql = 'SELECT DISTINCT session_id ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c, ' . $GLOBALS['ecs']->table('sessions') . ' AS s ' . 'WHERE c.session_id = s.sesskey ';
    $valid_sess = $GLOBALS['db']->getCol($sql);
    $sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE session_id NOT ' . db_create_in($valid_sess);
    $GLOBALS['db']->query($sql);
    $sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart_combo') . ' WHERE session_id NOT ' . db_create_in($valid_sess);
    $GLOBALS['db']->query($sql);
}
?>