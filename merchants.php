<?php
//dezend by  QQ:2172298892
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
$article_id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : $_CFG['marticle_id']);
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));
$action = (isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default');
if($action == 'default') {// 默认为入驻专题介绍页面
    if (!$smarty->is_cached('merchants.dwt')) {
    	assign_template();
    	$position = assign_ur_here();
    	$smarty->assign('page_title', $position['title']);
    	$smarty->assign('ur_here', $position['ur_here']);
    	$categories_pro = get_category_tree_leve_one();
    	$smarty->assign('categories_pro', $categories_pro);
    	$marticle = explode(',', $_CFG['marticle']);
    	$article_menu1 = get_merchants_article_menu($marticle[0]);
    	$article_menu2 = get_merchants_article_menu($marticle[1]);
    	$article_info = get_merchants_article_info($article_id);
    
    	for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
    		$ad_arr .= '\'merch' . $i . ',';
    	}
    
    	$smarty->assign('adarr', $ad_arr);
    	$smarty->assign('article', $article_info);
    	$smarty->assign('article_menu1', $article_menu1);
    	$smarty->assign('article_menu2', $article_menu2);
    	$smarty->assign('article_id', $article_id);
    	$smarty->assign('marticle', $marticle[0]);
    	$smarty->assign('helps', get_shop_help());
    	assign_dynamic('merchants');
    	$smarty->display('merchants.dwt');
    }
}
else if ($action == 'merchants_identity'){//入驻身份选择界面
    assign_template();
    $position = assign_ur_here();
    $smarty->assign('helps', get_shop_help());
    $smarty->assign('page_title', $position['title']);
    $smarty->display('merchants_identity.dwt');
}


?>
