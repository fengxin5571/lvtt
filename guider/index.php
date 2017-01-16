<?php 
/*
 * 导游入驻管理后台
 * 
 */
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . '/includes/lib_order.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$smarty->assign('menus', $_SESSION['menus']);
if ($_REQUEST['act'] == '') {//导游后台首页
    assign_template();//获取全局参数值
    $guide_id = intval($_SESSION['guide_id']);
    if($guide_id){
        $sql = 'SELECT g.*,m.*,u.user_name  FROM '.$ecs->table("guide_shop_information"). ' AS g LEFT JOIN '.$ecs->table("merchants_steps_fields"). 'AS m ON m.user_id=g.user_id '.
        'LEFT JOIN '.$ecs->table('users').' AS u ON g.user_id =u.user_id '.
        'WHERE g.guide_id='.$guide_id;
        $guide_info=$db->getRow($sql);
        $guide_info['last_login'] = local_date('Y-m-d H:i:s', $guide_info['last_login']);
    }
    $sql = 'SELECT * FROM ' . $ecs->table('article') . 'WHERE cat_id =14 ';
    $articles = $db->getAll($sql);
    $smarty->assign('articles', $articles);
    $smarty->assign("guider_info", $guide_info);
    $smarty->display('index.dwt');
}
else if ($_REQUEST['act'] == 'clear_cache') {//清楚缓存
    if (file_exists(ROOT_PATH . 'mobile/api/script/clear_cache.php')) {
        require_once ROOT_PATH . 'mobile/api/script/clear_cache.php';
    }

    clear_all_files('', SELLER_PATH);
    sys_msg($_LANG['caches_cleared']);
}
?>