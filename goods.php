<?php
//zend by QQ:2172298892
function get_linked_goods($goods_id, $warehouse_id = 0, $area_id = 0)
{
	$where = '';
	$leftJoin = '';
	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . 'IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\') AS shop_price, ' . 'g.market_price, g.sales_volume, ' . 'IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)) as promote_price, ' . ' g.promote_start_date, g.promote_end_date ' . 'FROM ' . $GLOBALS['ecs']->table('link_goods') . ' lg ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = lg.link_goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . $leftJoin . 'WHERE lg.goods_id = \'' . $goods_id . '\' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . $where . 'LIMIT ' . $GLOBALS['_CFG']['related_goods_number'];
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$price_other = array('market_price' => $row['market_price'], 'org_price' => $row['org_price'], 'shop_price' => $row['shop_price'], 'promote_price' => $promote_price);
		$price_info = get_goods_one_attr_price($row['goods_id'], $warehouse_id, $area_id, $price_other);
		$row = (!empty($row) ? array_merge($row, $price_info) : $row);
		$promote_price = $row['promote_price'];
		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
	}

	return $arr;
}

function get_linked_articles($goods_id)
{
	$sql = 'SELECT a.article_id, a.title, a.file_url, a.open_type, a.add_time ' . 'FROM ' . $GLOBALS['ecs']->table('goods_article') . ' AS g, ' . $GLOBALS['ecs']->table('article') . ' AS a ' . 'WHERE g.article_id = a.article_id AND g.goods_id = \'' . $goods_id . '\' AND a.is_open = 1 ' . 'ORDER BY a.add_time DESC';
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['url'] = $row['open_type'] != 1 ? build_uri('article', array('aid' => $row['article_id']), $row['title']) : trim($row['file_url']);
		$row['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
		$row['short_title'] = 0 < $GLOBALS['_CFG']['article_title_length'] ? sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
		$arr[] = $row;
	}

	return $arr;
}

function get_user_rank_prices($goods_id, $shop_price)
{
	if (empty($shop_price)) {
		$shop_price = 0;
	}

	$sql = 'SELECT rank_id, IFNULL(mp.user_price, r.discount * ' . $shop_price . ' / 100) AS price, r.rank_name, r.discount ' . 'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = \'' . $goods_id . '\' AND mp.user_rank = r.rank_id ' . 'WHERE r.show_price = 1 OR r.rank_id = \'' . $_SESSION['user_rank'] . '\'' . $tag_where;
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr[$row['rank_id']] = array('rank_name' => htmlspecialchars($row['rank_name']), 'price' => price_format($row['price']));
	}

	return $arr;
}

function get_also_bought($goods_id)
{
	$sql = 'SELECT COUNT(b.goods_id ) AS num, g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price, g.promote_price, g.promote_start_date, g.promote_end_date ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS b ON b.order_id = a.order_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = b.goods_id ' . 'WHERE a.goods_id = \'' . $goods_id . '\' AND b.goods_id <> \'' . $goods_id . '\' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . 'GROUP BY b.goods_id ' . 'ORDER BY num DESC ' . 'LIMIT ' . $GLOBALS['_CFG']['bought_goods'];
	$res = $GLOBALS['db']->query($sql);
	$key = 0;
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr[$key]['goods_id'] = $row['goods_id'];
		$arr[$key]['goods_name'] = $row['goods_name'];
		$arr[$key]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$arr[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[$key]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
		$arr[$key]['shop_price'] = price_format($row['shop_price']);
		$arr[$key]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

		if (0 < $row['promote_price']) {
			$arr[$key]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			$arr[$key]['formated_promote_price'] = price_format($arr[$key]['promote_price']);
		}
		else {
			$arr[$key]['promote_price'] = 0;
		}

		$key++;
	}

	return $arr;
}

function get_goods_rank($goods_id)
{
	$period = intval($GLOBALS['_CFG']['top10_time']);

	if ($period == 1) {
		$ext = ' AND o.add_time > \'' . local_strtotime('-1 years') . '\'';
	}
	else if ($period == 2) {
		$ext = ' AND o.add_time > \'' . local_strtotime('-6 months') . '\'';
	}
	else if ($period == 3) {
		$ext = ' AND o.add_time > \'' . local_strtotime('-3 months') . '\'';
	}
	else if ($period == 4) {
		$ext = ' AND o.add_time > \'' . local_strtotime('-1 months') . '\'';
	}
	else {
		$ext = '';
	}

	$sql = 'SELECT IFNULL(SUM(g.goods_number), 0) ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' . $GLOBALS['ecs']->table('order_goods') . ' AS g ' . 'WHERE o.order_id = g.order_id ' . 'AND o.order_status = \'' . OS_CONFIRMED . '\' ' . 'AND o.shipping_status ' . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . ' AND o.pay_status ' . db_create_in(array(PS_PAYED, PS_PAYING)) . ' AND g.goods_id = \'' . $goods_id . '\'' . $ext;
	$sales_count = $GLOBALS['db']->getOne($sql);

	if (0 < $sales_count) {
		$sql = 'SELECT DISTINCT SUM(goods_number) AS num ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' . $GLOBALS['ecs']->table('order_goods') . ' AS g ' . 'WHERE o.order_id = g.order_id ' . 'AND o.order_status = \'' . OS_CONFIRMED . '\' ' . 'AND o.shipping_status ' . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . ' AND o.pay_status ' . db_create_in(array(PS_PAYED, PS_PAYING)) . $ext . ' GROUP BY g.goods_id HAVING num > ' . $sales_count;
		$res = $GLOBALS['db']->query($sql);
		$rank = $GLOBALS['db']->num_rows($res) + 1;

		if (10 < $rank) {
			$rank = 0;
		}
	}
	else {
		$rank = 0;
	}

	return $rank;
}

function get_attr_amount($goods_id, $attr)
{
	$sql = 'SELECT SUM(attr_price) FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE goods_id=\'' . $goods_id . '\' AND ' . db_create_in($attr, 'goods_attr_id');
	return $GLOBALS['db']->getOne($sql);
}

function get_package_goods_list($goods_id)
{
	$now = gmtime();
	$sql = "SELECT pg.goods_id, ga.act_id, ga.act_name, ga.act_desc, ga.goods_name, ga.start_time,\r\n                   ga.end_time, ga.is_finished, ga.ext_info\r\n            FROM " . $GLOBALS['ecs']->table('goods_activity') . ' AS ga, ' . $GLOBALS['ecs']->table('package_goods') . " AS pg\r\n            WHERE pg.package_id = ga.act_id\r\n            AND ga.start_time <= '" . $now . "'\r\n            AND ga.end_time >= '" . $now . "'\r\n            AND pg.goods_id = " . $goods_id . "\r\n            GROUP BY ga.act_id\r\n            ORDER BY ga.act_id ";
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $tempkey => $value) {
		$subtotal = 0;
		$row = unserialize($value['ext_info']);
		unset($value['ext_info']);

		if ($row) {
			foreach ($row as $key => $val) {
				$res[$tempkey][$key] = $val;
			}
		}

		$sql = 'SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, p.goods_attr, g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . "') AS rank_price\r\n                FROM " . $GLOBALS['ecs']->table('package_goods') . " AS pg\r\n                    LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g\r\n                        ON g.goods_id = pg.goods_id\r\n                    LEFT JOIN " . $GLOBALS['ecs']->table('products') . " AS p\r\n                        ON p.product_id = pg.product_id\r\n                    LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp\r\n                        ON mp.goods_id = g.goods_id AND mp.user_rank = '" . $_SESSION['user_rank'] . "'\r\n                WHERE pg.package_id = " . $value['act_id'] . "\r\n                ORDER BY pg.package_id, pg.goods_id";
		$goods_res = $GLOBALS['db']->getAll($sql);

		foreach ($goods_res as $key => $val) {
			$goods_id_array[] = $val['goods_id'];
			$goods_res[$key]['goods_thumb'] = get_image_path($val['goods_id'], $val['goods_thumb'], true);
			$goods_res[$key]['market_price'] = price_format($val['market_price']);
			$goods_res[$key]['rank_price'] = price_format($val['rank_price']);
			$subtotal += $val['rank_price'] * $val['goods_number'];
		}

		$sql = "SELECT ga.goods_attr_id, ga.attr_value\r\n                FROM " . $GLOBALS['ecs']->table('goods_attr') . ' AS ga, ' . $GLOBALS['ecs']->table('attribute') . " AS a\r\n                WHERE a.attr_id = ga.attr_id\r\n                AND a.attr_type = 1\r\n                AND " . db_create_in($goods_id_array, 'goods_id');
		$result_goods_attr = $GLOBALS['db']->getAll($sql);
		$_goods_attr = array();

		foreach ($result_goods_attr as $value) {
			$_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
		}

		$format = '[%s]';

		foreach ($goods_res as $key => $val) {
			if ($val['goods_attr'] != '') {
				$goods_attr_array = explode('|', $val['goods_attr']);
				$goods_attr = array();

				foreach ($goods_attr_array as $_attr) {
					$goods_attr[] = $_goods_attr[$_attr];
				}

				$goods_res[$key]['goods_attr_str'] = sprintf($format, implode('，', $goods_attr));
			}
		}

		$res[$tempkey]['goods_list'] = $goods_res;
		$res[$tempkey]['subtotal'] = price_format($subtotal);
		$res[$tempkey]['saving'] = price_format($subtotal - $res[$tempkey]['package_price']);
		$res[$tempkey]['package_price'] = price_format($res[$tempkey]['package_price']);
	}

	return $res;
}

function get_goods_related_cat($cat_id)
{
	$sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE cat_id = \'' . $cat_id . '\'';
	$res = $GLOBALS['db']->getOne($sql, true);
	$sql = 'SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = \'' . $res . '\'';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $row) {
		$res[$key]['cat_id'] = $row['cat_id'];
		$res[$key]['cat_name'] = $row['cat_name'];
		$res[$key]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
	}

	return $res;
}

function get_goods_similar_brand($cat_id = 0)
{
	$res['brand_id'] = '';
	$sql = 'SELECT user_id, brand_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE cat_id = \'' . $cat_id . '\'';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $row) {
		if (0 < $row['user_id']) {
			$sql = 'SELECT lb.brand_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' AS msb ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('link_brand') . ' AS lb ON msb.bid = lb.bid' . ' WHERE msb.bid = \'' . $row['brand_id'] . '\' LIMIT 1';
			$shop_brand = $GLOBALS['db']->getRow($sql);
			$res[$key]['brand_id'] = $shop_brand['brand_id'];
		}

		if ($res[$key]['brand_id']) {
			$res['brand_id'] .= $res[$key]['brand_id'] . ',';
		}
	}

	$brand = '';

	if ($res['brand_id']) {
		$brand_id = substr($res['brand_id'], 0, -1);
		$brand_id = explode(',', $brand_id);
		$brand = array_unique($brand_id);
		$brand = implode(',', $brand);
		$sql = 'SELECT brand_id, brand_name FROM ' . $GLOBALS['ecs']->table('brand') . ' WHERE brand_id IN(' . $brand . ')';
		$brand = $GLOBALS['db']->getAll($sql);

		foreach ($brand as $key => $row) {
			$brand[$key]['url'] = build_uri('brand', array('bid' => $row['brand_id']), $row['brand_name']);
		}
	}

	return $brand;
}

function get_goods_attr_ajax($goods_id, $goods_attr, $goods_attr_id)
{
	$arr = array();
	$arr['attr_id'] = '';

	if ($goods_attr) {
		$goods_attr = implode(',', $goods_attr);

		if ($goods_attr_id) {
			$goods_attr_id = implode(',', $goods_attr_id);
			$where = ' AND ga.goods_attr_id IN(' . $goods_attr_id . ')';
		}
		else {
			$where = '';
		}

		$sql = 'SELECT ga.goods_attr_id, ga.attr_id, ga.attr_value  FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON ga.attr_id = a.attr_id ' . ' WHERE ga.attr_id IN(' . $goods_attr . ') AND ga.goods_id = \'' . $goods_id . '\' ' . $where . ' AND a.attr_type > 0 ORDER BY a.sort_order, ga.attr_sort, ga.goods_attr_id';
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $key => $row) {
			$arr[$row['attr_id']][$row['goods_attr_id']] = $row;
			$arr['attr_id'] .= $row['attr_id'] . ',';
		}

		if ($arr['attr_id']) {
			$arr['attr_id'] = substr($arr['attr_id'], 0, -1);
			$arr['attr_id'] = explode(',', $arr['attr_id']);
		}
		else {
			$arr['attr_id'] = array();
		}
	}

	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require dirname(__FILE__) . '/includes/phpqrcode/phpqrcode.php';
require ROOT_PATH . '/includes/lib_area.php';
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
	$region_id = $_COOKIE['region_id'];
}

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
$smarty->assign('affiliate', $affiliate);
$factor = intval($_CFG['comment_factor']);
$smarty->assign('factor', $factor);
get_request_filter();
$goods_id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$uachar = '/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i';
if ((($ua == '') || preg_match($uachar, $ua)) && !strpos(strtolower($_SERVER['REQUEST_URI']), 'wap')) {
	$Loaction = 'mobile/index.php?r=goods&id=' . $goods_id;

	if (!empty($Loaction)) {
		ecs_header('Location: ' . $Loaction . "\n");
		exit();
	}
}

$smarty->assign('category', $goods_id);

if (empty($_REQUEST['act'])) {
	$goods_date = array('goods_name');
	$goods_where = 'goods_id = \'' . $goods_id . '\' AND is_delete=0';
	$goods_name = get_table_date('goods', $goods_where, $goods_date);

	if (empty($goods_name)) {
		header("Location: index.php\n");
		exit();
	}
}

$pid = (isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0);
$user_id = (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

if (!empty($_SESSION['user_id'])) {
	$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
}
else {
	$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
}

$_SESSION['goods_id'] = $goods_id;
if (!empty($_REQUEST['act']) && ($_REQUEST['act'] == 'price')) {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'err_no' => 0, 'result' => '', 'qty' => 1);
	$attr_id = (isset($_REQUEST['attr']) ? explode(',', $_REQUEST['attr']) : array());
	$number = (isset($_REQUEST['number']) ? intval($_REQUEST['number']) : 1);
	$warehouse_id = (isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0);
	$area_id = (isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0);
	$onload = (isset($_REQUEST['onload']) ? trim($_REQUEST['onload']) : '');
	$goods_attr = (isset($_REQUEST['goods_attr']) ? explode(',', $_REQUEST['goods_attr']) : array());
	$attr_ajax = get_goods_attr_ajax($goods_id, $goods_attr, $attr_id);
	$goods = get_goods_info($goods_id, $warehouse_id, $area_id);

	if ($goods_id == 0) {
		$res['err_msg'] = $_LANG['err_change_attr'];
		$res['err_no'] = 1;
	}
	else {
		if ($number == 0) {
			$res['qty'] = $number = 1;
		}
		else {
			$res['qty'] = $number;
		}

		$products = get_warehouse_id_attr_number($goods_id, $_REQUEST['attr'], $goods['user_id'], $warehouse_id, $area_id);
		$attr_number = $products['product_number'];

		if ($goods['model_attr'] == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' and warehouse_id = \'' . $warehouse_id . '\'';
		}
		else if ($goods['model_attr'] == 2) {
			$table_products = 'products_area';
			$type_files = ' and area_id = \'' . $area_id . '\'';
		}
		else {
			$table_products = 'products';
			$type_files = '';
		}

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $goods_id . '\'' . $type_files . ' LIMIT 1';
		$prod = $GLOBALS['db']->getRow($sql);

		if ($goods['goods_type'] == 0) {
			$attr_number = $goods['goods_number'];
		}
		else {
			if (empty($prod)) {
				$attr_number = $goods['goods_number'];
			}

			if (!empty($prod) && ($GLOBALS['_CFG']['add_shop_price'] == 0)) {
				if (empty($attr_number)) {
					$attr_number = $goods['goods_number'];
				}
			}
		}

		if (empty($prod)) {
			$res['bar_code'] = $goods['bar_code'];
		}
		else {
			$res['bar_code'] = $products['bar_code'];
		}

		$attr_number = (!empty($attr_number) ? $attr_number : 0);
		$res['attr_number'] = $attr_number;
		$shop_price = get_final_price($goods_id, $number, true, $attr_id, $warehouse_id, $area_id);
		$res['shop_price'] = price_format($shop_price);
		$res['market_price'] = $goods['market_price'];
		$res['show_goods'] = 0;
		if ($goods_attr && ($GLOBALS['_CFG']['add_shop_price'] == 0)) {
			if (count($goods_attr) == count($attr_ajax['attr_id'])) {
				$res['show_goods'] = 1;
			}
		}

		$spec_price = get_final_price($goods_id, $number, true, $attr_id, $warehouse_id, $area_id, 1, 0, 0, $res['show_goods']);
		if (($GLOBALS['_CFG']['add_shop_price'] == 0) && empty($spec_price)) {
			$spec_price = $shop_price;
		}

		$res['spec_price'] = price_format($spec_price);
		$res['marketPrice_amount'] = price_format($goods['marketPrice'] + $spec_price);
		$res['result'] = price_format($shop_price * $number);

		if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
			$res['result_market'] = price_format($goods['marketPrice'] * $number);
		}
		else {
			$res['result_market'] = price_format(($goods['marketPrice'] * $number) + $spec_price);
		}

		if ($goods['stages']) {
			$stages = unserialize($goods['stages']);
			$total = floatval(strip_tags(str_replace('¥', '', $res['result'])));

			foreach ($stages as $K => $v) {
				$res['stages'][$v] = round(($total * ($goods['stages_rate'] / 100)) + ($total / $v), 2);
			}
		}
	}

	$goods_fittings = get_goods_fittings_info($goods_id, $warehouse_id, $area_id, '', 1);
	$fittings_list = get_goods_fittings(array($goods_id), $warehouse_id, $area_id);

	if ($fittings_list) {
		if (is_array($fittings_list)) {
			foreach ($fittings_list as $vo) {
				$fittings_index[$vo['group_id']] = $vo['group_id'];
			}
		}

		ksort($fittings_index);
		$merge_fittings = get_merge_fittings_array($fittings_index, $fittings_list);
		$fitts = get_fittings_array_list($merge_fittings, $goods_fittings);

		for ($i = 0; $i < count($fitts); $i++) {
			$fittings_interval = $fitts[$i]['fittings_interval'];
			$res['fittings_interval'][$i]['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . '-' . number_format($fittings_interval['fittings_max'], 2, '.', '');
			$res['fittings_interval'][$i]['market_minMax'] = price_format($fittings_interval['market_min']) . '-' . number_format($fittings_interval['market_max'], 2, '.', '');

			if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
				$res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
			}
			else {
				$res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . '-' . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
			}

			$res['fittings_interval'][$i]['groupId'] = $fittings_interval['groupId'];
		}
	}

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$area_list = get_goods_link_area_list($goods_id, $goods['user_id']);

		if ($area_list['goods_area']) {
			if (!in_array($area_id, $area_list['goods_area'])) {
				$res['err_no'] = 2;
			}
		}
		else {
			$res['err_no'] = 2;
		}
	}

	$res['onload'] = $onload;
	exit($json->encode($res));
}
else if ($_REQUEST['act'] == 'add_useful') {
	require_once dirname(__FILE__) . '/includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'content' => '', 'err_no' => 0);
	$id = (empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']));
	$type = (empty($_REQUEST['type']) ? 'comment' : $_REQUEST['type']);
	$goods_id = (empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']));
	$ip = real_ip();

	if (!empty($id)) {
		if (!isset($_SESSION['user_id']) || ($_SESSION['user_id'] == 0)) {
			$res['url'] = get_return_goods_url($goods_id);
			$res['err_no'] = 1;
		}
		else {
			$useful_user = '';
			$sql = 'SELECT useful_user, useful FROM ' . $ecs->table('comment') . ' WHERE comment_id=\'' . $id . '\'';
			$comment = $db->getRow($sql);

			if ($comment['useful_user']) {
				$useful_user = explode(',', $comment['useful_user']);

				if (in_array($_SESSION['user_id'], $useful_user)) {
					$res['err_no'] = 2;
					exit($json->encode($res));
				}
				else {
					array_push($useful_user, $_SESSION['user_id']);
					$useful_user = implode(',', $useful_user);
				}
			}
			else {
				$useful_user = array(0);
				array_push($useful_user, $_SESSION['user_id']);
				$useful_user = implode(',', $useful_user);
			}

			$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('comment') . ' WHERE comment_id=\'' . $id . '\'';
			$count = $db->getOne($sql);

			if ($count == 1) {
				$sql = 'UPDATE ' . $ecs->table('comment') . ' SET useful = useful + 1, useful_user = \'' . $useful_user . '\' WHERE comment_id=\'' . $id . '\'';

				if ($db->query($sql)) {
					$res = array('option' => 'true', 'id' => $id, 'type' => $type, 'useful' => $comment['useful'] + 1, 'err_no' => 0);
				}
				else {
					$res = array('error' => '', 'id' => $id, 'type' => $type, 'err_no' => 2);
				}
			}
			else {
				$res = array('option' => '', 'id' => $id, 'type' => $type, 'err_no' => 2);
			}
		}
	}

	exit($json->encode($res));
}
else if ($_REQUEST['act'] == 'comment_reply') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$result = array('err_msg' => '', 'err_no' => 0, 'content' => '');
	$comment_id = (isset($_REQUEST['comment_id']) ? intval($_REQUEST['comment_id']) : 0);
	$reply_content = (isset($_REQUEST['reply_content']) ? htmlspecialchars(trim($_REQUEST['reply_content'])) : 0);
	$goods_id = (isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0);
	$comment_user = (isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0);
	$libType = (isset($_REQUEST['libType']) ? intval($_REQUEST['libType']) : 0);
	$type = 0;
	$reply_page = 1;
	$add_time = gmtime();
	$real_ip = real_ip();
	$result['comment_id'] = $comment_id;
	$result['reply_content'] = $reply_content;
	if (!isset($_SESSION['user_id']) || ($_SESSION['user_id'] == 0)) {
		$result['err_no'] = 1;
	}
	else if ($comment_user == $_SESSION['user_id']) {
		$result['err_no'] = 2;
	}
	else {
		$comment_user_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value = \'' . $goods_id . '\' AND parent_id = \'' . $comment_id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\'');

		if (0 < $comment_user_count) {
			$result['err_no'] = 2;
		}
		else {
			$comment_user_name = $GLOBALS['db']->getOne('SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\'');
			$status = 1 - $GLOBALS['_CFG']['comment_check'];
			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('comment') . '(`id_value`,`content`,`comment_type`,`user_name`,`comment_rank`,`comment_server`,`comment_delivery`, `add_time`, `parent_id`, `user_id`, `ip_address`, `status`)' . 'VALUES(\'' . $goods_id . '\', \'' . $reply_content . '\', 2, \'' . $comment_user_name . '\', \'5\', \'5\', \'5\', \'' . $add_time . '\', \'' . $comment_id . '\', \'' . $_SESSION['user_id'] . '\', \'' . $real_ip . '\', \'' . $status . '\')';
			$GLOBALS['db']->query($sql);
			$result['message'] = $GLOBALS['_CFG']['comment_check'] ? $_LANG['cmt_submit_wait'] : $_LANG['cmt_submit_done'];
		}
	}

	if ($libType == 1) {
		$size = 10;
	}
	else {
		$size = 2;
	}

	if ($result['err_no'] != 1) {
		$reply = get_reply_list($goods_id, $comment_id, $type, $reply_page, $libType, $size);
		$smarty->assign('reply_pager', $reply['reply_pager']);
		$smarty->assign('reply_count', $reply['reply_count']);
		$smarty->assign('reply_list', $reply['reply_list']);

		if ($libType == 1) {
			$result['content'] = $smarty->fetch('library/comment_repay.lbi');
		}
		else {
			$result['content'] = $smarty->fetch('library/comment_reply.lbi');
		}
	}

	$result['url'] = get_return_goods_url($goods_id);
	exit($json->encode($result));
}

if (!empty($_REQUEST['act']) && ($_REQUEST['act'] == 'in_warehouse')) {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'result' => '', 'qty' => 1);
	clear_cache_files();
	setcookie('region_id', $pid, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('regionId', $pid, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$area_region = 0;
	setcookie('area_region', $area_region, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$res['goods_id'] = $goods_id;
	$json = new JSON();
	exit($json->encode($res));
}

if (!empty($_REQUEST['act']) && ($_REQUEST['act'] == 'in_stock')) {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'result' => '', 'qty' => 1);
	clear_cache_files();
	$goods_id = (empty($_GET['id']) ? 0 : intval($_GET['id']));
	$province = (empty($_GET['province']) ? 1 : intval($_GET['province']));
	$city = (empty($_GET['city']) ? 52 : intval($_GET['city']));
	$district = (empty($_GET['district']) ? 500 : intval($_GET['district']));
	$d_null = (empty($_GET['d_null']) ? 0 : intval($_GET['d_null']));
	$user_id = (empty($_GET['user_id']) ? 0 : intval($_GET['user_id']));
	$user_address = get_user_address_region($user_id);
	$user_address = explode(',', $user_address['region_address']);
	setcookie('province', $province, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('city', $city, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('district', $district, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$regionId = 0;
	setcookie('regionId', $regionId, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('type_province', 0, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('type_city', 0, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('type_district', 0, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$res['d_null'] = $d_null;

	if ($d_null == 0) {
		if (in_array($district, $user_address)) {
			$res['isRegion'] = 1;
		}
		else {
			$res['message'] = '您尚未拥有此配送地区，请您填写配送地址';
			$res['isRegion'] = 88;
		}
	}
	else {
		setcookie('district', '', gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	$res['goods_id'] = $goods_id;
	$flow_warehouse = get_warehouse_goods_region($province);
	setcookie('flow_region', $flow_warehouse['region_id'], gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	exit($json->encode($res));
}

if (!empty($_REQUEST['act']) && ($_REQUEST['act'] == 'gotopage')) {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'result' => '');
	$goods_id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$page = (isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1);

	if (!empty($goods_id)) {
		$need_cache = $GLOBALS['smarty']->caching;
		$need_compile = $GLOBALS['smarty']->force_compile;
		$GLOBALS['smarty']->caching = false;
		$GLOBALS['smarty']->force_compile = true;
		$sql = 'SELECT u.user_name, og.goods_number, oi.add_time, IF(oi.order_status IN (2, 3, 4), 0, 1) AS order_status ' . 'FROM ' . $ecs->table('order_info') . ' AS oi LEFT JOIN ' . $ecs->table('users') . ' AS u ON oi.user_id = u.user_id, ' . $ecs->table('order_goods') . ' AS og ' . 'WHERE oi.order_id = og.order_id AND ' . gmtime() . ' - oi.add_time < 2592000 AND og.goods_id = ' . $goods_id . ' ORDER BY oi.add_time DESC LIMIT ' . ((1 < $page ? $page - 1 : 0) * 5) . ',5';
		$bought_notes = $db->getAll($sql);

		foreach ($bought_notes as $key => $val) {
			$bought_notes[$key]['add_time'] = local_date('Y-m-d G:i:s', $val['add_time']);
		}

		$sql = 'SELECT count(*) ' . 'FROM ' . $ecs->table('order_info') . ' AS oi LEFT JOIN ' . $ecs->table('users') . ' AS u ON oi.user_id = u.user_id, ' . $ecs->table('order_goods') . ' AS og ' . 'WHERE oi.order_id = og.order_id AND ' . gmtime() . ' - oi.add_time < 2592000 AND og.goods_id = ' . $goods_id;
		$count = $db->getOne($sql);
		$pager = array();
		$pager['page'] = $page;
		$pager['size'] = $size = 5;
		$pager['record_count'] = $count;
		$pager['page_count'] = $page_count = (0 < $count ? intval(ceil($count / $size)) : 1);
		$pager['page_first'] = 'javascript:gotoBuyPage(1,' . $goods_id . ')';
		$pager['page_prev'] = 1 < $page ? 'javascript:gotoBuyPage(' . ($page - 1) . ',' . $goods_id . ')' : 'javascript:;';
		$pager['page_next'] = $page < $page_count ? 'javascript:gotoBuyPage(' . ($page + 1) . ',' . $goods_id . ')' : 'javascript:;';
		$pager['page_last'] = $page < $page_count ? 'javascript:gotoBuyPage(' . $page_count . ',' . $goods_id . ')' : 'javascript:;';
		$smarty->assign('notes', $bought_notes);
		$smarty->assign('pager', $pager);
		$res['result'] = $GLOBALS['smarty']->fetch('library/bought_notes.lbi');
		$GLOBALS['smarty']->caching = $need_cache;
		$GLOBALS['smarty']->force_compile = $need_compile;
	}

	exit($json->encode($res));
}

if (!empty($_REQUEST['act']) && ($_REQUEST['act'] == 'guess_goods')) {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'result' => '');
	$page = (isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1);

	if (3 < $page) {
		$page = 1;
	}

	$need_cache = $GLOBALS['smarty']->caching;
	$need_compile = $GLOBALS['smarty']->force_compile;
	$GLOBALS['smarty']->caching = false;
	$GLOBALS['smarty']->force_compile = true;
	$guess_goods = get_guess_goods($user_id, 1, $page, 7);
	$smarty->assign('guess_goods', $guess_goods);
	$smarty->assign('pager', $pager);
	$res['page'] = $page;
	$res['result'] = $GLOBALS['smarty']->fetch('library/guess_goods_love.lbi');
	$GLOBALS['smarty']->caching = $need_cache;
	$GLOBALS['smarty']->force_compile = $need_compile;
	exit($json->encode($res));
}
else if ($_REQUEST['act'] == 'comment_reply') {
	require_once dirname(__FILE__) . '/includes/cls_json.php';
	$json = new JSON();
	$res = array('err_msg' => '', 'content' => '', 'err_no' => 0);
	$id = (empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']));
	$type = (empty($_REQUEST['type']) ? 'comment' : $_REQUEST['type']);
	$ip = real_ip();

	if (!empty($id)) {
		if (!isset($_SESSION['user_id']) || ($_SESSION['user_id'] == 0)) {
			$res['err_no'] = 1;
		}
		else {
			$useful_user = '';
			$sql = 'SELECT useful_user, useful FROM ' . $ecs->table('comment') . ' WHERE comment_id=\'' . $id . '\'';
			$comment = $db->getRow($sql);

			if ($comment['useful_user']) {
				$useful_user = explode(',', $comment['useful_user']);

				if (in_array($_SESSION['user_id'], $useful_user)) {
					$res['err_no'] = 2;
					exit($json->encode($res));
				}
				else {
					array_push($useful_user, $_SESSION['user_id']);
					$useful_user = implode(',', $useful_user);
				}
			}
			else {
				$useful_user = array(0);
				array_push($useful_user, $_SESSION['user_id']);
				$useful_user = implode(',', $useful_user);
			}

			$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('comment') . ' WHERE comment_id=\'' . $id . '\'';
			$count = $db->getOne($sql);

			if ($count == 1) {
				$sql = 'UPDATE ' . $ecs->table('comment') . ' SET useful = useful + 1, useful_user = \'' . $useful_user . '\' WHERE comment_id=\'' . $id . '\'';

				if ($db->query($sql)) {
					$res = array('option' => 'true', 'id' => $id, 'type' => $type, 'useful' => $comment['useful'] + 1, 'err_no' => 0);
				}
				else {
					$res = array('error' => '', 'id' => $id, 'type' => $type, 'err_no' => 2);
				}
			}
			else {
				$res = array('option' => '', 'id' => $id, 'type' => $type, 'err_no' => 2);
			}
		}
	}

	exit($json->encode($res));
}
else if ($_REQUEST['act'] == 'reply_comment') {
	require_once dirname(__FILE__) . '/includes/cls_json.php';
	$json = new JSON();
	$content = (empty($_REQUEST['comment_content']) ? '' : htmlspecialchars($_REQUEST['comment_content']));
	$user_name = $_SESSION['user_name'];
	$cid = (empty($_REQUEST['comment_id']) ? 0 : $_REQUEST['comment_id']);
	$sid = (empty($_REQUEST['single_id']) ? 0 : $_REQUEST['single_id']);
	$addtime = gmtime();
	$ip = real_ip();
	$is_ajax = (empty($_REQUEST['is_ajax']) ? 0 : htmlspecialchars($_REQUEST['is_ajax']));

	if (empty($cid)) {
		$err_msg = '此条评论可能已经被删除';
		$res = array('error' => $err_msg, 'option' => false);
		exit($json->encode($res));
	}

	if (empty($_SESSION['user_id'])) {
		if ($is_ajax == 1) {
			$res['is_user'] = '';
			exit($json->encode($res));
		}
	}

	if ($content == '') {
		$err_msg = '回复内容不能为空';
		$res = array('error' => $err_msg, 'option' => false);
		exit($json->encode($res));
	}

	$com_user = $db->getRow('SELECT user_name, id_value FROM ' . $ecs->table('comment') . ' WHERE comment_id=' . $cid);

	if ($com_user['user_name'] == $user_name) {
		if ($is_ajax == 1) {
			$err_msg = '不能对自己评论';
			$res = array('error' => $err_msg, 'option' => false);
			exit($json->encode($res));
		}
	}

	$sql = 'INSERT INTO ' . $ecs->table('comment') . "(comment_type, id_value, email, user_name, content, comment_rank, add_time, ip_address, status, parent_id, user_id, single_id)\r\n\tVALUES('0', '" . $com_user['id_value'] . '\', \'' . $_SESSION['email'] . '\', \'' . $user_name . '\', \'' . $content . '\', 5, \'' . $addtime . '\', \'' . $ip . '\', \'1\', \'' . $cid . '\', \'' . $_SESSION['user_id'] . '\', \'' . $sid . '\')';

	if ($db->query($sql)) {
		$res = array('error' => 0, 'option' => true);
		exit($json->encode($res));
	}
	else {
		$res = array('error' => '评论未成功，请检查网络。', 'option' => false);
		exit($json->encode($res));
	}
}

if (!empty($_REQUEST['act']) && ($_REQUEST['act'] == 'price_notice')) {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('msg' => '', 'status' => '');
	$goods_id = (isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0);
	$user_id = (isset($_REQUEST['user_id']) ? intval($_SESSION['user_id']) : 0);
	$email = (isset($_REQUEST['email']) ? trim($_REQUEST['email']) : '');
	$cellphone = (isset($_REQUEST['cellphone']) ? trim($_REQUEST['cellphone']) : '');
	$hopeDiscount = (isset($_REQUEST['hopeDiscount']) ? trim($_REQUEST['hopeDiscount']) : 0);
	$add_time = gmtime();
	if ($user_id && $email) {
		$sql = 'SELECT count(*) FROM ' . $ecs->table('sale_notice') . ' WHERE goods_id=\'' . $goods_id . '\' AND user_id=\'' . $user_id . '\'';
		$one = $db->getOne($sql);

		if ($one) {
			$sql = 'UPDATE ' . $ecs->table('sale_notice') . ' SET cellphone=\'' . $cellphone . '\',email=\'' . $email . '\',hopeDiscount=\'' . $hopeDiscount . '\',add_time=\'' . $add_time . '\' WHERE goods_id=\'' . $goods_id . '\' AND user_id=\'' . $user_id . '\'';
			$db->query($sql);
			$res['msg'] = '更新成功！';
		}
		else {
			$sql = 'INSERT INTO ' . $ecs->table('sale_notice') . ' (user_id,goods_id,cellphone,email,hopeDiscount,add_time)' . ' VALUES (\'' . $user_id . '\',\'' . $goods_id . '\',\'' . $cellphone . '\',\'' . $email . '\',\'' . $hopeDiscount . '\',\'' . $add_time . '\')';
			$db->query($sql);
			$res['msg'] = '提交成功！';
		}

		$res['status'] = 0;
	}
	else {
		$res['msg'] = '提交失败，稍后在提交一次';
		$res['status'] = 1;
	}

	exit($json->encode($res));
}

$goods = get_goods_info($goods_id, $region_id, $area_id);
$area = array('region_id' => $region_id, 'province_id' => $province_id, 'city_id' => $city_id, 'district_id' => $district_id, 'goods_id' => $goods_id, 'user_id' => $user_id, 'area_id' => $area_id, 'merchant_id' => $goods['user_id']);
$smarty->assign('area', $area);
$cache_id = $goods_id . '-' . $_SESSION['user_rank'] . '-' . $_CFG['lang'];
$cache_id = sprintf('%X', crc32($cache_id));
$cache_id = '';
$not = 'not';

if (!$smarty->is_cached('goods.dwt', $cache_id)) {
	$smarty->assign('image_width', $_CFG['image_width']);
	$smarty->assign('image_height', $_CFG['image_height']);
	$smarty->assign('helps', get_shop_help());
	$smarty->assign('id', $goods_id);
	$smarty->assign('type', 0);
	$smarty->assign('cfg', $_CFG);
	$promotion = get_promotion_info($goods_id, $goods['user_id']);
	$smarty->assign('promotion', $promotion);
	$promo_count = count($promotion) + count($goods['consumption']);
	$smarty->assign('promo_count', $promo_count);
	$promotion_info = get_promotion_info('', $goods['user_id']);
	$smarty->assign('promotion_info', $promotion_info);
	$user_id = (!empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);
	$start_date = $goods['xiangou_start_date'];
	$end_date = $goods['xiangou_end_date'];
	$nowTime = gmtime();
	if (($start_date < $nowTime) && ($nowTime < $end_date)) {
		$xiangou = 1;
	}
	else {
		$xiangou = 0;
	}

	$order_goods = get_for_purchasing_goods($start_date, $end_date, $goods_id, $user_id);
	$smarty->assign('xiangou', $xiangou);
	$smarty->assign('orderG_number', $order_goods['goods_number']);
	$shop_info = get_merchants_shop_info('merchants_steps_fields', $goods['user_id']);
	$adress = get_license_comp_adress($shop_info['license_comp_adress']);
	$smarty->assign('shop_info', $shop_info);
	$smarty->assign('adress', $adress);

	if ($goods === false) {
		ecs_header("Location: ./\n");
		exit();
	}
	else {
		$goods['goods_extends'] = get_goods_extends($goods_id);

		if (0 < $goods['brand_id']) {
			$brand_act = '';
			$brand = get_goods_brand($goods['brand_id'], $goods['user_id']);

			if ($brand) {
				$sql = 'SELECT lb.brand_id, (SELECT brand_name FROM ' . $GLOBALS['ecs']->table('brand') . ' AS b WHERE b.brand_id = lb.brand_id LIMIT 1) AS brand_name FROM ' . $GLOBALS['ecs']->table('link_brand') . ' AS lb where lb.bid = \'' . $brand['brand_id'] . '\' LIMIT 1';
				$res = $GLOBALS['db']->getRow($sql);
				$goods['brand_id'] = $res['brand_id'];
				$goods['goods_brand'] = $res['brand_name'];
			}

			$goods['goods_brand_url'] = build_uri('brand', array('bid' => $goods['brand_id'], 'mbid' => $brand['brand_id']), $goods['goods_brand']);
		}

		$shop_price = $goods['shop_price'];
		$linked_goods = get_linked_goods($goods_id, $region_id, $area_info['region_id']);
		$goods['goods_style_name'] = add_style($goods['goods_name'], $goods['goods_name_style']);

		if (0 < $goods['bonus_type_id']) {
			$time = gmtime();
			$sql = 'SELECT type_money FROM ' . $ecs->table('bonus_type') . ' WHERE type_id = \'' . $goods['bonus_type_id'] . '\' ' . ' AND send_type = \'' . SEND_BY_GOODS . '\' ' . ' AND send_start_date <= \'' . $time . '\'' . ' AND send_end_date >= \'' . $time . '\'';
			$goods['bonus_money'] = floatval($db->getOne($sql));

			if (0 < $goods['bonus_money']) {
				$goods['bonus_money'] = price_format($goods['bonus_money']);
			}
		}

		$sql = 'SELECT rec_id FROM ' . $ecs->table('collect_store') . ' WHERE user_id = \'' . $user_id . '\' AND ru_id = \'' . $goods['user_id'] . '\' ';
		$rec_id = $db->getOne($sql);

		if (0 < $rec_id) {
			$goods['error'] = '1';
		}
		else {
			$goods['error'] = '2';
		}

		if ($_CFG['two_code']) {
			$logo = (empty($_CFG['two_code_logo']) ? $goods['goods_img'] : str_replace('../', '', $_CFG['two_code_logo']));
			$size = '200x200';
			$url = $ecs->url();
			$two_code_links = trim($_CFG['two_code_links']);
			$two_code_links = (empty($two_code_links) ? $url : $two_code_links);
			$data = $two_code_links . 'mobile/index.php?r=goods&id=' . $goods['goods_id'];
			$errorCorrectionLevel = 'H';
			$matrixPointSize = 4;
			$filename = 'images/weixin_img/weixin_code_' . $goods['goods_id'] . '.png';
			QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize);
			$QR = imagecreatefrompng($filename);

			if ($logo !== false) {
				$logo = imagecreatefromstring(file_get_contents($logo));
				$QR_width = imagesx($QR);
				$QR_height = imagesy($QR);
				$logo_width = imagesx($logo);
				$logo_height = imagesy($logo);
				$logo_qr_width = $QR_width / 5;
				$scale = $logo_width / $logo_qr_width;
				$logo_qr_height = $logo_height / $scale;
				$from_width = ($QR_width - $logo_qr_width) / 2;
				imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
			}

			imagepng($QR, $filename);
			imagedestroy($QR);
			$smarty->assign('weixin_img_url', $filename);
			$smarty->assign('weixin_img_text', trim($_CFG['two_code_mouse']));
			$smarty->assign('two_code', trim($_CFG['two_code']));
		}

		$goods['store_count'] = 0;
		$sql = 'SELECT COUNT(*) FROM' . $ecs->table('offline_store') . ' AS o LEFT JOIN' . $ecs->table('store_goods') . ' AS s ON o.id = s.store_id WHERE s.goods_id = \'' . $goods_id . '\' AND o.is_confirm=1';
		$store_goods = $db->getOne($sql);
		$sql = 'SELECT COUNT(*) FROM' . $ecs->table('offline_store') . ' AS o LEFT JOIN' . $ecs->table('store_products') . ' AS s ON o.id = s.store_id WHERE s.goods_id = \'' . $goods_id . '\' AND o.is_confirm=1';
		$store_products = $db->getOne($sql);
		if ((0 < $store_goods) || (0 < $store_products)) {
			$goods['store_count'] = 1;
		}

		$smarty->assign('goods', $goods);
		$smarty->assign('goods_name', $goods['goods_name']);
		$smarty->assign('goods_id', $goods['goods_id']);
		$smarty->assign('promote_end_time', $goods['gmt_end_time']);
		$smarty->assign('keywords', htmlspecialchars($goods['keywords']));
		$smarty->assign('description', htmlspecialchars($goods['goods_brief']));
		$catlist = array();

		foreach (get_parent_cats($goods['cat_id']) as $k => $v) {
			$catlist[] = $v['cat_id'];
		}

		assign_template('c', $catlist);
		$prev_gid = $db->getOne('SELECT goods_id FROM ' . $ecs->table('goods') . ' WHERE cat_id=' . $goods['cat_id'] . ' AND goods_id > ' . $goods['goods_id'] . ' AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 LIMIT 1');

		if (!empty($prev_gid)) {
			$prev_good['url'] = build_uri('goods', array('gid' => $prev_gid), $goods['goods_name']);
			$smarty->assign('prev_good', $prev_good);
		}

		$next_gid = $db->getOne('SELECT max(goods_id) FROM ' . $ecs->table('goods') . ' WHERE cat_id=' . $goods['cat_id'] . ' AND goods_id < ' . $goods['goods_id'] . ' AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0');

		if (!empty($next_gid)) {
			$next_good['url'] = build_uri('goods', array('gid' => $next_gid), $goods['goods_name']);
			$smarty->assign('next_good', $next_good);
		}

		$position = assign_ur_here($goods['cat_id'], $goods['goods_name'], array(), '', $goods['user_id']);
		$smarty->assign('page_title', $position['title']);
		$smarty->assign('ur_here', $position['ur_here']);
		$categories_pro = get_category_tree_leve_one();
		$smarty->assign('categories_pro', $categories_pro);
		$properties = get_goods_properties($goods_id, $region_id, $area_info['region_id']);
		$smarty->assign('best_goods', get_recommend_goods('best', '', $region_id, $area_info['region_id'], $goods['user_id'], 1));
		$smarty->assign('new_goods', get_recommend_goods('new', '', $region_id, $area_info['region_id'], $goods['user_id'], 1));
		$smarty->assign('hot_goods', get_recommend_goods('hot', '', $region_id, $area_info['region_id'], $goods['user_id'], 1));
		$smarty->assign('properties', $properties['pro']);
		$smarty->assign('specification', $properties['spe']);
		$smarty->assign('attribute_linked', get_same_attribute_goods($properties));
		$smarty->assign('related_goods', $linked_goods);
		$smarty->assign('goods_article_list', get_linked_articles($goods_id));
		$smarty->assign('rank_prices', get_user_rank_prices($goods_id, $shop_price));
		$smarty->assign('pictures', get_goods_gallery($goods_id));
		$smarty->assign('bought_goods', get_also_bought($goods_id));
		$smarty->assign('goods_rank', get_goods_rank($goods_id));
		$smarty->assign('guess_goods', get_guess_goods($user_id, 1, $page = 1, 7, $region_id, $area_info['region_id']));

		if ($goods['user_id']) {
			$goods_store_cat = get_child_tree_pro(0, 0, 'merchants_category', 0, $goods['user_id']);

			if ($goods_store_cat) {
				$goods_store_cat = array_values($goods_store_cat);
			}

			$smarty->assign('goods_store_cat', $goods_store_cat);
		}

		$comboTabIndex = get_cfg_group_goods();
		$smarty->assign('comboTab', $comboTabIndex);
		$fittings_list = get_goods_fittings(array($goods_id), $region_id, $area_info['region_id']);

		if (is_array($fittings_list)) {
			foreach ($fittings_list as $vo) {
				$fittings_index[$vo['group_id']] = $vo['group_id'];
			}
		}

		ksort($fittings_index);
		$smarty->assign('fittings_tab_index', $fittings_index);
		$smarty->assign('fittings', $fittings_list);
		$tag_array = get_tags($goods_id);
		$smarty->assign('tags', $tag_array);
		$package_goods_list = get_package_goods_list($goods['goods_id']);
		$smarty->assign('package_goods_list', $package_goods_list);
		assign_dynamic('goods');
		$volume_price_list = get_volume_price_list($goods['goods_id'], '1');
		$smarty->assign('volume_price_list', $volume_price_list);
		$discuss_list = get_discuss_all_list($goods_id, 0, 1, 10);
		$smarty->assign('discuss_list', $discuss_list);
		$smarty->assign('all_count', $discuss_list['record_count']);
		$goods_brand = get_goods_similar_brand($goods['cat_id']);
		$smarty->assign('goods_brand', $goods_brand);
		$goods_related_cat = get_goods_related_cat($goods['cat_id']);
		$smarty->assign('goods_related_cat', $goods_related_cat);
	}
}

if (!empty($_COOKIE['ECS']['history'])) {
	$history = explode(',', $_COOKIE['ECS']['history']);
	array_unshift($history, $goods_id);
	$history = array_unique($history);

	while ($_CFG['history_number'] < count($history)) {
		array_pop($history);
	}

	setcookie('ECS[history]', implode(',', $history), gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
}
else {
	setcookie('ECS[history]', $goods_id, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
}

$smarty->assign('goods_id', $goods_id);
$smarty->assign('region_id', $region_id);
$smarty->assign('user_id', $user_id);
$smarty->assign('area_id', $area_info['region_id']);
$db->query('UPDATE ' . $ecs->table('goods') . ' SET click_count = click_count + 1 WHERE goods_id = \'' . $_REQUEST['id'] . '\'');

if (!empty($_COOKIE['ECS']['list_history'])) {
	$list_history = explode(',', $_COOKIE['ECS']['list_history']);
	array_unshift($list_history, $goods_id);
	$list_history = array_unique($list_history);

	while (100000 < count($list_history)) {
		array_pop($list_history);
	}

	setcookie('ECS[list_history]', implode(',', $list_history), gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
}
else {
	setcookie('ECS[list_history]', $goods_id, gmtime() + (3600 * 24 * 30), $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
}

$date = array('shipping_code');
$where = 'shipping_id = \'' . $goods['default_shipping'] . '\'';
$shipping_code = get_table_date('shipping', $where, $date, 2);
$cart_num = get_goods_cart_num($goods_id, $region_id);
$smarty->assign('cart_num', $cart_num);
$smarty->assign('area_htmlType', 'goods');
$comment_all = get_comments_percent($goods_id);

if (0 < $goods['user_id']) {
	$merchants_goods_comment = get_merchants_goods_comment($goods['user_id']);
	$smarty->assign('merch_cmt', $merchants_goods_comment);
}

$smarty->assign('comment_all', $comment_all);

if ($GLOBALS['_CFG']['customer_service'] == 0) {
	$goods['user_id'] = 0;
}

$basic_info = get_shop_info_content($goods['user_id']);
$shop_information = get_shop_name($goods['user_id']);

if ($goods['user_id'] == 0) {
	if ($db->getOne('SELECT kf_im_switch FROM ' . $ecs->table('seller_shopinfo') . 'WHERE ru_id = 0')) {
		$shop_information['is_dsc'] = true;
	}
	else {
		$shop_information['is_dsc'] = false;
	}
}
else {
	$shop_information['is_dsc'] = false;
}

$smarty->assign('shop_information', $shop_information);
$smarty->assign('kf_appkey', $basic_info['kf_appkey']);
$smarty->assign('im_user_id', 'dsc' . $_SESSION['user_id']);
$basic_date = array('region_name');
$basic_info['province'] = get_table_date('region', 'region_id = \'' . $basic_info['province'] . '\'', $basic_date, 2);
$basic_info['city'] = get_table_date('region', 'region_id= \'' . $basic_info['city'] . '\'', $basic_date, 2) . '市';
$smarty->assign('basic_info', $basic_info);
$_SESSION['goods_equal'] = '';
$db->query('delete from ' . $ecs->table('cart_combo') . ' WHERE (parent_id = 0 and goods_id = \'' . $goods_id . '\' or parent_id = \'' . $goods_id . '\') and ' . $sess_id);

if ($rank = get_rank_info()) {
	$smarty->assign('rank_name', $rank['rank_name']);
}

$smarty->assign('info', get_user_default($_SESSION['user_id']));
$cart_info = insert_cart_info(1);
$smarty->assign('cart_info', $cart_info);
$goods_area = 1;

if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
	$area_list = get_goods_link_area_list($goods_id, $goods['user_id']);

	if ($area_list['goods_area']) {
		if (in_array($area_info['region_id'], $area_list['goods_area'])) {
			$goods_area = 1;
		}
		else {
			$goods_area = 0;
		}
	}
	else {
		$goods_area = 0;
	}
}

$region = array(1, $province_id, $city_id, $district_id);
$shippingFee = goodsshippingfee($goods_id, $region_id, $region);
$smarty->assign('shippingFee', $shippingFee);
$smarty->assign('goods_area', $goods_area);
$GLOBALS['smarty']->assign('shop_price_type', $goods['model_price']);
$smarty->assign('freight_model', $GLOBALS['_CFG']['freight_model']);
$smarty->assign('one_step_buy', $GLOBALS['_CFG']['one_step_buy']);
$smarty->assign('now_time', gmtime());

if ($goods['stages']) {
	foreach ($goods['stages'] as $k => $v) {
		$stages_arr[$v]['stages_one_price'] = round((($goods['shop_price'] + $shippingFee['shipping_fee']) * ($goods['stages_rate'] / 100)) + (($goods['shop_price'] + $shippingFee['shipping_fee']) / $v), 2);
	}

	$smarty->assign('stages', $stages_arr);
}

$goods_coupons = get_goods_coupons_list($goods_id);
$smarty->assign('goods_coupons', $goods_coupons);
$regular = get_regular_members();
$smarty->assign('regular', $regular);
$smarty->display('goods.dwt', $cache_id);

?>
