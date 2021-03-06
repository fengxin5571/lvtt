<?php
//zend by QQ:2172298892
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
require ROOT_PATH . '/includes/cls_json.php';
require ROOT_PATH . '/includes/lib_clips.php';
include_once ROOT_PATH . 'includes/lib_transaction.php';
require_once ROOT_PATH . 'includes/lib_ecmoban.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
assign_template();
$area_info = get_area_info($province_id);
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
$act = (!empty($_REQUEST['act']) ? trim($_REQUEST['act']) : '');
$tpl = (!empty($_REQUEST['tpl']) ? trim($_REQUEST['tpl']) : 1);
$smarty->assign('tpl', $tpl);

if ($act == 'get_content') {
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$json = new JSON();
	$data_type = (!empty($_POST['data_type']) ? trim($_POST['data_type']) : '');

	switch ($data_type) {
	case 'cart_list':
		$cart_info = insert_cart_info(2);
		$result['cart_num'] = $cart_info['number'];
		$GLOBALS['smarty']->assign('cart_info', $cart_info);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_cart_info.lbi');
		break;

	case 'mpbtn_total':
		$user_info = get_user_default($_SESSION['user_id']);
		$bonus = get_user_bouns_new_list($_SESSION['user_id'], $page, 0, 'bouns_available_gotoPage', 0, 7);

		if ($user_info) {
			foreach ($bonus['available_list'] as $key => $val) {
				$bonus['available_list'][$key]['use_startdate'] = substr($val['use_startdate'], 0, 10);
				$bonus['available_list'][$key]['use_enddate'] = substr($val['use_enddate'], 0, 10);
			}

			$user_info['bouns_list'] = $bonus['available_list'];
			$user_info['bouns_num'] = count($bonus['available_list']);
		}

		$GLOBALS['smarty']->assign('user_info', $user_info);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_total_info.lbi');
		break;

	case 'mpbtn_history':
		$history_info = insert_history_info(10);
		$GLOBALS['smarty']->assign('history_info', $history_info);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_histroy_info.lbi');
		break;

	case 'mpbtn_collection':
		$operat = (!empty($_POST['type']) ? trim($_POST['type']) : '');
		$collect_id = (!empty($_POST['collection_id']) ? intval($_POST['collection_id']) : 0);
		if (!empty($operat) && ($operat == 'del') && (0 < $collect_id)) {
			$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' WHERE rec_id=\'' . $collect_id . '\' AND user_id =\'' . $_SESSION['user_id'] . '\'');
		}

		$collection_goods = get_collection_goods($_SESSION['user_id'], 10, 1, 'collection_goods_gotoPage');
		$smarty->assign('goods_list', $collection_goods['goods_list']);
		$collection_store = get_collection_store($_SESSION['user_id'], 5, 1, 'collection_store_gotoPage');
		$smarty->assign('store_list', $collection_store['store_list']);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_collection_info.lbi');
		break;

	case 'mpbtn_order':
		$order_list = get_user_orders($_SESSION['user_id'], 5, 1, 0, '', '', 0, 5);
		$smarty->assign('order_list', $order_list['order_list']);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_order_info.lbi');
		break;

	case 'mpbtn_coupons':
		$goods_id = intval($_GET['goods_id']);
		$user_id = $_SESSION['user_id'];
		$sql = 'SELECT user_id FROM ' . $ecs->table('goods') . ' WHERE goods_id=' . $goods_id;
		$ru_id = $db->getOne($sql);
		$goods_coupons = get_goods_coupons_list($goods_id);

		if ($user_id) {
			$user_coupons = get_user_coupons_list($user_id);
		}
		else {
			$user_coupons = array();
		}

		foreach ($user_coupons as $k => $v) {
			$user_coupons[$k]['cou_start_time'] = local_date('Y-m-d', $v['cou_start_time']);
			$user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
			$user_coupons[$k]['cou_type'] = $v['cou_type'] == 3 ? '全场券' : ($v['cou_type'] == 4 ? '会员券' : ($v['cou_type'] == 2 ? '购物券' : ($v['cou_type'] == 1 ? '注册券' : '未知')));
			if (((strpos(',' . $v['cou_goods'] . ',', ',' . $goods_id . ',') !== false) || ($v['cou_goods'] == 0)) && ($v['ru_id'] == $ru_id)) {
				$user_coupons[$k]['keyong'] = 1;
			}

			if ($v['is_use'] == 1) {
				unset($user_coupons[$k]);
			}

			$arr[] = $user_coupons[$k]['cou_id'];
		}

		$arr = array_filter($arr);

		foreach ($goods_coupons as $k => $v) {
			if (!in_array($v['cou_id'], $arr)) {
				$v['cou_start_time'] = local_date('Y-m-d', $v['cou_start_time']);
				$v['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
				$kelingqu_coupons[] = $v;
			}
		}

		$smarty->assign('user_coupons', array_slice($user_coupons, 0, 4));
		$smarty->assign('kelingqu_coupons', array_slice($kelingqu_coupons, 0, 4));
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_yhq_info.lbi');
		exit($json->encode($result));
		break;

	case 'mpbtn_yhq':
		$goods_coupons = get_coupons_type_info2('3,4');
		$user_id = $_SESSION['user_id'];

		if ($user_id) {
			$user_coupons = get_user_coupons_list($user_id);
		}
		else {
			$user_coupons = array();
		}

		foreach ($user_coupons as $k => $v) {
			$user_coupons[$k]['cou_start_time'] = local_date('Y-m-d', $v['cou_start_time']);
			$user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
			$user_coupons[$k]['cou_type'] = $v['cou_type'] == 3 ? '全场券' : ($v['cou_type'] == 4 ? '会员券' : ($v['cou_type'] == 2 ? '购物券' : ($v['cou_type'] == 1 ? '注册券' : '未知')));

			if ($v['is_use'] == 1) {
				unset($user_coupons[$k]);
			}

			$arr[] = $user_coupons[$k]['cou_id'];
		}

		$arr = array_filter($arr);

		foreach ($goods_coupons as $k => $v) {
			if (!in_array($v['cou_id'], $arr)) {
				$v['cou_start_time'] = local_date('Y-m-d', $v['cou_start_time']);
				$v['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
				$kelingqu_coupons[] = $v;
			}
		}

		$smarty->assign('user_coupons', array_slice($user_coupons, 0, 4));
		$smarty->assign('kelingqu_coupons', array_slice($kelingqu_coupons, 0, 4));
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_yhq_info.lbi');
		exit($json->encode($result));
		break;

	default:
		break;
	}

	exit($json->encode($result));
}
else if ($act == 'get_login_dialog') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$back_act = (!empty($_POST['back_act']) ? trim($_POST['back_act']) : '');
	$json = new JSON();
	if (intval($_CFG['captcha']) && (0 < gd_version())) {
		$smarty->assign('enabled_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	$website_dir = ROOT_PATH . 'includes/website/config/';
	$website_list = get_dir_file_list($website_dir, 1, '_');
	$smarty->assign('website_list', $website_list);
	$smarty->assign('back_act', $back_act);
	$smarty->assign('user_lang', $_LANG);
	$result['content'] = $GLOBALS['smarty']->fetch('library/login_dialog_body.lbi');
	exit($json->encode($result));
}
else if ($act == 'get_store_list') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$goods_id = (!empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : '');
	$spec_arr = (isset($_REQUEST['spec_arr']) ? $_REQUEST['spec_arr'] : '');
	$json = new JSON();
	$smarty->assign('provinces', get_regions(1, 1));
	$smarty->assign('goods_id', $goods_id);
	$sql = 'SELECT o.id,o.stores_name,s.goods_id,o.stores_address,o.stores_traffic_line,o.ru_id ,p.region_name as province ,s.goods_number ,' . 'c.region_name as city ,d.region_name as district FROM ' . $ecs->table('offline_store') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('store_goods') . ' AS s ON o.id = s.store_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS p ON p.region_id = o.province ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS c ON c.region_id = o.city ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS d ON d.region_id = o.district ' . 'WHERE o.is_confirm=1 AND s.goods_id =\'' . $goods_id . '\'  GROUP BY o.id';
	$store_list = $db->getAll($sql);
	$is_spec = explode(',', $spec_arr);

	if (!empty($store_list)) {
		foreach ($store_list as $k => $v) {
			if (is_spec($is_spec) == true) {
				$products = get_warehouse_id_attr_number($v['goods_id'], $spec_arr, $v['ru_id'], 0, 0, '', $v['id']);
				$v['goods_number'] = $products['product_number'];

				if ($products['product_number'] == 0) {
					unset($store_list[$k]);
				}
			}
		}
	}

	$smarty->assign('store_list', $store_list);
	$result['content'] = $GLOBALS['smarty']->fetch('library/store_list_body.lbi');
	exit($json->encode($result));
}
else {
	if (($act == 'get_cat_top_list') && ($tpl == 1)) {
		$region_id = (isset($_REQUEST['region_id']) ? intval($_REQUEST['region_id']) : 0);
		$area_id = (isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0);
		$cat_id = (!empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0);
		$prent_id = (!empty($_REQUEST['prent_id']) ? intval($_REQUEST['prent_id']) : 0);
		$rome_key = intval($_REQUEST['rome_key']) + 1;
		$result = array('error' => 0, 'content' => '');
		$json = new JSON();

		if (0 < $cat_id) {
			if ($categories_child = read_static_cache('cat_top_cache' . $prent_id)) {
				$one_cate_child = $categories_child[$cat_id];
			}
			else {
				$categories_child = get_parent_cat_tree($prent_id);
				$one_cate_child = $categories_child[$cat_id];
			}

			if ($one_cate_child) {
				$one_cate_child['brands'] = get_brands($cat_id, 'brand', 10);

				foreach ($one_cate_child['cat_id'] as $k => $v) {
					$child_children = get_children($v['id']);
					$childcate_goods_list = get_category_recommend_goods('best', $child_children, 0, 0, 0, '', $region_id, $area_id, 10);

					if ($childcate_goods_list) {
						$one_cate_child['cat_id'][$k]['goods_list'] = $childcate_goods_list;
					}
				}

				for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
					$cat_top_floor_ad .= '\'cat_top_floor_ad' . $i . ',';
				}

				$floor_ad_arr = array('ad_arr' => $cat_top_floor_ad, 'id' => $cat_id);
				$cat_top_floor_ad = insert_get_adv_child($floor_ad_arr);
				$smarty->assign('rome_number', $rome_key);
				$smarty->assign('cat_top_floor_ad', $cat_top_floor_ad);
				$smarty->assign('one_cate_child', $one_cate_child);
				$result['content'] = html_entity_decode($smarty->fetch('library/load_category_top.lbi'));
			}
			else {
				$result['error'] = 1;
			}
		}

		$result['cat_id'] = $cat_id;
		$result['rome_key'] = $rome_key;
		exit($json->encode($result));
	}
	else if ($act == 'get_index_goods_cat') {
		$rome_key = intval($_REQUEST['rome_key']);
		$result = array('error' => 0, 'content' => '', 'maxindex' => 0);
		$json = new JSON();

		if (!read_static_cache('index_goods_cat_cache')) {
			$sql = 'SELECT filename,region,theme,id, number, type, sort_order FROM ' . $GLOBALS['ecs']->table('template') . ' WHERE filename = \'index\' AND type =1 AND theme=\'' . $GLOBALS['_CFG']['template'] . '\' AND remarks=\'\' order by sort_order asc';
			$template = $GLOBALS['db']->getAll($sql);
			write_static_cache('index_goods_cat_cache', $template);
		}
		else {
			$template = read_static_cache('index_goods_cat_cache');
		}

		if ($rome_key <= count($template) - 1) {
			$row = $template[$rome_key];
			$brand_ids = insert_get_floor_content($row);
			$brands_theme2 = get_floor_brand($brand_ids);
			$brands_theme2 = get_brands_theme2($brands_theme2);
			$smarty->assign('brands_theme2', $brands_theme2);

			if (!read_static_cache('index_goods_cat' . $rome_key)) {
				$goods_cat = assign_cat_goods($row['id'], $row['number'], 'web', '', 'cat', $region_id, $area_info['region_id'], $rome_key);
				write_static_cache('index_goods_cat' . $rome_key, $goods_cat);
			}
			else {
				$goods_cat = read_static_cache('index_goods_cat' . $rome_key);
			}

			if ($goods_cat) {
				$get_adv = insert_get_adv(array('logo_name' => $goods_cat['floor_banner']));
				$smarty->assign('get_adv', $get_adv);
				$cat_goods_banner = '';
				$cat_goods_hot = '';

				for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
					$cat_goods_banner .= '\'cat_goods_banner' . $i . ',';
					$cat_goods_hot .= '\'cat_goods_hot' . $i . ',';
				}

				$cat_goods_banner = insert_get_adv_child(array('ad_arr' => $cat_goods_banner, 'id' => $goods_cat['id'], 'warehouse_id' => $region_id, 'area_id' => $area_info['region_id']));
				$cat_goods_hot = insert_get_adv_child(array('ad_arr' => $cat_goods_hot, 'id' => $goods_cat['id'], 'warehouse_id' => $region_id, 'area_id' => $area_info['region_id']));
				$smarty->assign('cat_goods_banner', $cat_goods_banner);
				$smarty->assign('cat_goods_hot', $cat_goods_hot);
				$smarty->assign('goods_cat', $goods_cat);
				$result['content'] = html_entity_decode($smarty->fetch('library/load_cat_goods.lbi'));
			}
			else {
				$result['error'] = 1;
			}

			if ($rome_key == (count($template) - 1)) {
				$result['maxindex'] = 1;
			}
		}
		else {
			$result['error'] = 1;
		}

		exit($json->encode($result));
	}
	else if ($act == 'getCategotyParentTree') {
		$cat_id = (isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0);
		$result = array('error' => 0, 'content' => '');
		$json = new JSON();
		$file = 'parent_style_brands' . $cat_id;
		$brands = read_static_cache($file);

		if ($brands === false) {
			$brands = get_brands($cat_id);
			write_static_cache($file, $brands);
		}

		$smarty->assign('brands', $brands);
		$result['cat_id'] = $cat_id;
		$result['brands_content'] = $GLOBALS['smarty']->fetch('library/category_parent_brands.lbi');
		exit($json->encode($result));
	}
	else {
		if (($act == 'get_cat_top_list') && ($tpl == 2)) {
			$region_id = (isset($_REQUEST['region_id']) ? intval($_REQUEST['region_id']) : 0);
			$area_id = (isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0);
			$cat_id = (!empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0);
			$prent_id = (!empty($_REQUEST['prent_id']) ? intval($_REQUEST['prent_id']) : 0);
			$rome_key = intval($_REQUEST['rome_key']) + 1;
			$result = array('error' => 0, 'content' => '');
			$json = new JSON();

			if (0 < $cat_id) {
				if ($categories_child = read_static_cache('cat_top_cache' . $prent_id)) {
					$one_cate_child = $categories_child[$cat_id];
				}
				else {
					$categories_child = get_parent_cat_tree($prent_id);
					$one_cate_child = $categories_child[$cat_id];
				}

				if ($one_cate_child) {
					$one_cate_child['brands'] = get_brands($cat_id, 'brand', 10);

					foreach ($one_cate_child['cat_id'] as $k => $v) {
						$child_children = get_children($v['id']);
						$childcate_goods_list = get_category_recommend_goods('best', $child_children, 0, 0, 0, '', $region_id, $area_id, 10);

						if ($childcate_goods_list) {
							$one_cate_child['cat_id'][$k]['goods_list'] = $childcate_goods_list;
						}
					}

					$childcate_goods_hot = get_category_recommend_goods('hot', get_children($cat_id), 0, 0, 0, '', $region_id, $area_id, 1);
					$one_cate_child['goods_hot'] = $childcate_goods_hot;

					for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
						$top_style_elec_left .= '\'top_style_elec_left' . $i . ',';
					}

					$floor_ad_arr = array('ad_arr' => $top_style_elec_left, 'id' => $cat_id);
					$top_style_elec_left = insert_get_adv_child($floor_ad_arr);
					$top_style_elec_row = '\'top_style_elec_row,';
					$floor_ad_arr = array('ad_arr' => $top_style_elec_row, 'id' => $cat_id);
					$top_style_elec_row = insert_get_adv_child($floor_ad_arr);
					$class_num = array('on', '', 'last');
					$smarty->assign('class_num', $class_num);
					$smarty->assign('rome_number', $rome_key);
					$smarty->assign('top_style_elec_left', $top_style_elec_left);
					$smarty->assign('top_style_elec_row', $top_style_elec_row);
					$smarty->assign('one_cate_child', $one_cate_child);
					$result['content'] = html_entity_decode($smarty->fetch('library/load_category_top.lbi'));
				}
				else {
					$result['error'] = 1;
				}
			}

			$result['cat_id'] = $cat_id;
			$result['rome_key'] = $rome_key;
			exit($json->encode($result));
		}
		else {
			if (($act == 'get_cat_top_list') && ($tpl == 3)) {
				$region_id = (isset($_REQUEST['region_id']) ? intval($_REQUEST['region_id']) : 0);
				$area_id = (isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0);
				$cat_id = (!empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0);
				$prent_id = (!empty($_REQUEST['prent_id']) ? intval($_REQUEST['prent_id']) : 0);
				$rome_key = intval($_REQUEST['rome_key']) + 1;
				$result = array('error' => 0, 'content' => '');
				$json = new JSON();

				if (0 < $cat_id) {
					if ($categories_child = read_static_cache('cat_top_cache' . $prent_id)) {
						$one_cate_child = $categories_child[$cat_id];
					}
					else {
						$categories_child = get_parent_cat_tree($prent_id);
						$one_cate_child = $categories_child[$cat_id];
					}

					if ($one_cate_child) {
						$one_cate_child['brands'] = get_brands($cat_id, 'brand', 10);

						foreach ($one_cate_child['cat_id'] as $k => $v) {
							$child_children = get_children($v['id']);
							$childcate_goods_list = get_category_recommend_goods('best', $child_children, 0, 0, 0, '', $region_id, $area_id, 10);

							if ($childcate_goods_list) {
								$one_cate_child['cat_id'][$k]['goods_list'] = $childcate_goods_list;
							}
						}

						$childcate_goods_hot = get_category_recommend_goods('hot', get_children($cat_id), 0, 0, 0, '', $region_id, $area_id, 4);
						$one_cate_child['goods_hot'] = $childcate_goods_hot;

						for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
							$top_style_food_left .= '\'top_style_food_left' . $i . ',';
						}

						$floor_ad_arr = array('ad_arr' => $top_style_food_left, 'id' => $cat_id);
						$top_style_food_left = insert_get_adv_child($floor_ad_arr);
						$top_style_food_row = '\'top_style_food_row,';
						$floor_ad_arr = array('ad_arr' => $top_style_food_row, 'id' => $cat_id);
						$top_style_food_row = insert_get_adv_child($floor_ad_arr);
						$class_num = array('on', '', 'last');
						$smarty->assign('class_num', $class_num);
						$smarty->assign('rome_number', $rome_key);
						$smarty->assign('top_style_food_left', $top_style_food_left);
						$smarty->assign('top_style_food_row', $top_style_food_row);
						$smarty->assign('one_cate_child', $one_cate_child);
						$result['content'] = html_entity_decode($smarty->fetch('library/load_category_top.lbi'));
					}
					else {
						$result['error'] = 1;
					}
				}

				$result['cat_id'] = $cat_id;
				$result['rome_key'] = $rome_key;
				exit($json->encode($result));
			}
			else if ($act == 'changeShow') {
				$type = (empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']));
				$tpl = (empty($_REQUEST['tpl']) ? 0 : intval($_REQUEST['tpl']));
				$region_id = (isset($_REQUEST['region_id']) ? intval($_REQUEST['region_id']) : 0);
				$area_id = (isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0);
				$cat_id = (!empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0);
				$prent_id = (!empty($_REQUEST['prent_id']) ? intval($_REQUEST['prent_id']) : 0);
				$rome_key = intval($_REQUEST['rome_key']) + 1;
				$smarty->assign('type', $type);
				$smarty->assign('tpl', $tpl);
				$result = array('error' => 0, 'content' => '');
				$json = new JSON();

				if ($type == 1) {
					$child_children = get_children($cat_id);
					$havealook = get_category_recommend_goods('rand', $child_children, 0, 0, 0, '', $region_id, $area_id, 6);
					$smarty->assign('havealook', $havealook);
					$result['page'] = $GLOBALS['smarty']->fetch('library/have_a_look.lbi');
				}
				else if ($type == 3) {
					if (0 < $cat_id) {
						if ($tpl == 2) {
							$child_children = get_children($cat_id);
							$goods_list = get_category_recommend_goods('rand', $child_children, 0, 0, 0, '', $region_id, $area_id, 8);
							$smarty->assign('goods_list', $goods_list);
							$result['page'] = html_entity_decode($smarty->fetch('library/cat_goods_change.lbi'));
						}
						else if ($tpl == 3) {
							$child_children = get_children($cat_id);
							$goods_list = get_category_recommend_goods('rand', $child_children, 0, 0, 0, '', $region_id, $area_id, 6);
							$smarty->assign('goods_list', $goods_list);
							$result['page'] = html_entity_decode($smarty->fetch('library/cat_goods_change.lbi'));
						}
					}
					else {
						$result['error'] = 1;
					}
				}

				exit($json->encode($result));
			}
			else if ($act == 'floor_cat_content') {
				$result = array('error' => 0, 'content' => '');
				$json = new JSON();
				$cat_id = (empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']));
				$floor_num = (empty($_REQUEST['floor_num']) ? 0 : intval($_REQUEST['floor_num']));
				$warehouse_id = (empty($_REQUEST['warehouse_id']) ? 0 : intval($_REQUEST['warehouse_id']));
				$area_id = (empty($_REQUEST['area_id']) ? 0 : intval($_REQUEST['area_id']));
				$result['cat_id'] = $cat_id;
				$file = 'floor_cat_content_' . $cat_id;
				$goods_list = read_static_cache($file);

				if ($goods_list === false) {
					$goods_list = get_floor_ajax_goods($cat_id, $floor_num, $warehouse_id, $area_id);
					write_static_cache($file, $goods_list);
				}

				$smarty->assign('goods_list', $goods_list);
				$result['content'] = $GLOBALS['smarty']->fetch('library/floor_cat_content.lbi');
				exit($json->encode($result));
			}
			else if ($act == 'cart_info') {
				$result = array('error' => 0, 'content' => '');
				$json = new JSON();
				$result['content'] = insert_cart_info(4);
				exit($json->encode($result));
			}
		}
	}
}

?>
