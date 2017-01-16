<?php
//zend  QQ:2172298892
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$modules['04_order']['11_order_detection'] = 'order.php?act=order_detection';
$modules['04_order']['11_add_order'] = 'mc_order.php';
$modules['08_members']['11_users_add'] = 'mc_user.php';
$modules['02_cat_and_goods']['sale_notice'] = 'sale_notice.php?act=list';
$modules['02_cat_and_goods']['notice_logs'] = 'notice_logs.php?act=list';
$modules['04_order']['11_back_cause'] = 'order.php?act=back_cause_list';
$modules['04_order']['12_back_apply'] = 'order.php?act=return_list';
$modules['02_cat_and_goods']['discuss_circle'] = 'discuss_circle.php?act=list';
$modules['11_system']['user_keywords_list'] = 'keywords_manage.php?act=list';
$modules['17_merchants']['01_merchants_steps_list'] = 'merchants_steps.php?act=list';
$modules['17_merchants']['02_merchants_users_list'] = 'merchants_users_list.php?act=list';
$modules['17_merchants']['03_merchants_commission'] = 'merchants_commission.php?act=list';
$modules['17_merchants']['03_users_merchants_priv'] = 'merchants_privilege.php?act=allot';
$modules['17_merchants']['04_create_seller_grade'] = 'merchants_users_list.php?act=create_seller_grade';
$modules['17_merchants']['09_seller_domain'] = 'seller_domain.php?act=list';

if (!isset($_REQUEST['act_type'])) {
	$modules['17_merchants']['10_account_manage'] = 'merchants_account.php?act=account_manage&act_type=account';
}
else {
	$modules['17_merchants']['10_account_manage'] = 'merchants_account.php?act=account_manage&act_type=' . $_REQUEST['act_type'];
}

$modules['11_system']['09_warehouse_management'] = 'warehouse.php?act=list';
$modules['11_system']['09_region_area_management'] = 'region_area.php?act=list';
$modules['19_merchants_store']['01_merchants_basic_info'] = 'index.php?act=merchants_first';

if ($templates_mode != 1) {
	$modules['19_merchants_store']['02_merchants_ad'] = 'seller_shop_slide.php?act=list';
	$modules['19_merchants_store']['03_merchants_shop_top'] = 'index.php?act=shop_top';
}

$modules['19_merchants_store']['04_merchants_basic_nav'] = 'merchants_navigator.php?act=list';

if ($templates_mode != 1) {
	$modules['19_merchants_store']['05_merchants_shop_bg'] = 'seller_shop_bg.php?act=first';
	$modules['19_merchants_store']['06_merchants_custom'] = 'merchants_custom.php?act=list';
	$modules['19_merchants_store']['07_merchants_window'] = 'merchants_window.php?act=list';
	$modules['19_merchants_store']['08_merchants_template'] = 'merchants_template.php?act=list';
}

$modules['19_merchants_store']['09_merchants_upgrade'] = 'merchants_upgrade.php?act=list';

if ($templates_mode == 1) {
	$modules['19_merchants_store']['10_visual_editing'] = 'visual_editing.php?act=first';
}

$modules['18_batch_manage']['warehouse_batch'] = 'goods_warehouse_batch.php?act=add';
$modules['18_batch_manage']['area_batch'] = 'goods_area_batch.php?act=add';
$modules['18_batch_manage']['area_attr_batch'] = 'goods_area_attr_batch.php?act=add';
$modules['02_cat_and_goods']['07_merchants_brand'] = 'merchants_brand.php?act=list';
$modules['02_cat_and_goods']['03_store_category_list'] = 'category_store.php?act=list';
$modules['08_members']['12_user_address_list'] = 'user_address_log.php?act=list';
$modules['04_order']['13_goods_inventory_logs'] = 'goods_inventory_logs.php?act=list';
$modules['20_ectouch']['01_oauth_admin'] = '../mobile/index.php?r=oauth/admin';
$modules['20_ectouch']['02_touch_nav_admin'] = 'touch_navigator.php?act=list';
$modules['20_ectouch']['03_touch_ads'] = 'touch_ads.php?act=list';
$modules['20_ectouch']['04_touch_ad_position'] = 'touch_ad_position.php?act=list';
$modules['21_cloud']['01_cloud_services'] = 'index.php?act=cloud_services';
$modules['02_cat_and_goods']['01_goods_list'] = 'goods.php?act=list';
$modules['02_cat_and_goods']['02_goods_add'] = 'goods.php?act=add';
$modules['02_cat_and_goods']['03_category_list'] = 'category.php?act=list';
$modules['02_cat_and_goods']['05_comment_manage'] = 'comment_manage.php?act=list';
$modules['02_cat_and_goods']['06_goods_brand_list'] = 'brand.php?act=list';
$modules['02_cat_and_goods']['08_goods_type'] = 'goods_type.php?act=manage';
$modules['02_cat_and_goods']['11_goods_trash'] = 'goods.php?act=trash';
$modules['02_cat_and_goods']['12_batch_pic'] = 'picture_batch.php';
if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'add')) {
	$modules['02_cat_and_goods']['13_batch_add'] = 'goods_batch.php?act=add';
}
else {
	if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'upload')) {
		$modules['02_cat_and_goods']['13_batch_add'] = 'goods_batch.php?act=upload';
	}
}

$modules['02_cat_and_goods']['14_goods_export'] = 'goods_export.php?act=goods_export';
$modules['02_cat_and_goods']['15_batch_edit'] = 'goods_batch.php?act=select';
$modules['02_cat_and_goods']['16_goods_script'] = 'gen_goods_script.php?act=setup';
$modules['02_cat_and_goods']['17_tag_manage'] = 'tag_manage.php?act=list';
$modules['02_cat_and_goods']['50_virtual_card_list'] = 'goods.php?act=list&extension_code=virtual_card';
$modules['02_cat_and_goods']['51_virtual_card_add'] = 'goods.php?act=add&extension_code=virtual_card';
$modules['02_cat_and_goods']['52_virtual_card_change'] = 'virtual_card.php?act=change';
$modules['02_cat_and_goods']['goods_auto'] = 'goods_auto.php?act=list';
$modules['02_cat_and_goods']['comment_seller_rank'] = 'comment_seller.php?act=list';
$modules['11_system']['website'] = 'website.php?act=list';
$modules['02_cat_and_goods']['gallery_album'] = 'gallery_album.php?act=list';
$modules['03_promotion']['02_snatch_list'] = 'snatch.php?act=list';
$modules['03_promotion']['04_bonustype_list'] = 'bonus.php?act=list';
$modules['03_promotion']['08_group_buy'] = 'group_buy.php?act=list';
$modules['03_promotion']['09_topic'] = 'topic.php?act=list';
$modules['03_promotion']['10_auction'] = 'auction.php?act=list';
$modules['03_promotion']['12_favourable'] = 'favourable.php?act=list';
$modules['03_promotion']['13_wholesale'] = 'wholesale.php?act=list';
$modules['03_promotion']['14_package_list'] = 'package.php?act=list';
$modules['03_promotion']['15_exchange_goods'] = 'exchange_goods.php?act=list';
$modules['03_promotion']['17_coupons'] = 'coupons.php?act=list';
$modules['03_promotion']['gift_gard_list'] = 'gift_gard.php?act=list';
$modules['03_promotion']['take_list'] = 'gift_gard.php?act=take_list';
$modules['03_promotion']['16_presale'] = 'presale.php?act=list';
$modules['04_order']['02_order_list'] = 'order.php?act=list';
$modules['04_order']['03_order_query'] = 'order.php?act=order_query';
$modules['04_order']['04_merge_order'] = 'order.php?act=merge';
$modules['04_order']['05_edit_order_print'] = 'order.php?act=templates';
$modules['04_order']['06_undispose_booking'] = 'goods_booking.php?act=list_all';
$modules['04_order']['08_add_order'] = 'order.php?act=add';
$modules['04_order']['09_delivery_order'] = 'order.php?act=delivery_list';
$modules['04_order']['10_back_order'] = 'order.php?act=back_list';
$modules['05_banner']['ad_position'] = 'ad_position.php?act=list';
$modules['05_banner']['ad_list'] = 'ads.php?act=list';
$modules['06_stats']['flow_stats'] = 'flow_stats.php?act=view';
$modules['06_stats']['searchengine_stats'] = 'searchengine_stats.php?act=view';
$modules['06_stats']['z_clicks_stats'] = 'adsense.php?act=list';
$modules['06_stats']['report_guest'] = 'guest_stats.php?act=list';
$modules['06_stats']['report_order'] = 'order_stats.php?act=list';
$modules['06_stats']['report_sell'] = 'sale_general.php?act=list';
$modules['06_stats']['sale_list'] = 'sale_list.php?act=list';
$modules['06_stats']['sell_stats'] = 'sale_order.php?act=goods_num';
$modules['06_stats']['report_users'] = 'users_order.php?act=order_num';
$modules['06_stats']['visit_buy_per'] = 'visit_sold.php?act=list';
$modules['07_content']['03_article_list'] = 'article.php?act=list';
$modules['07_content']['02_articlecat_list'] = 'articlecat.php?act=list';
$modules['07_content']['vote_list'] = 'vote.php?act=list';
$modules['07_content']['article_auto'] = 'article_auto.php?act=list';
$modules['08_members']['03_users_list'] = 'users.php?act=list';
$modules['08_members']['04_users_add'] = 'users.php?act=add';
$modules['08_members']['05_user_rank_list'] = 'user_rank.php?act=list';
$modules['08_members']['06_list_integrate'] = 'integrate.php?act=list';
$modules['08_members']['08_unreply_msg'] = 'user_msg.php?act=list_all';
$modules['08_members']['09_user_account'] = 'user_account.php?act=list';
$modules['08_members']['10_user_account_manage'] = 'user_account_manage.php?act=list';
$modules['08_members']['13_user_baitiao_info'] = 'user_baitiao_log.php?act=list';
$modules['10_priv_admin']['admin_logs'] = 'admin_logs.php?act=list';
$modules['10_priv_admin']['02_admin_seller'] = 'privilege_seller.php?act=list';
$modules['10_priv_admin']['admin_role'] = 'role.php?act=list';
$modules['10_priv_admin']['agency_list'] = 'agency.php?act=list';
$modules['10_priv_admin']['suppliers_list'] = 'suppliers.php?act=list';
$modules['11_system']['01_shop_config'] = 'shop_config.php?act=list_edit';
$modules['11_system']['02_payment_list'] = 'payment.php?act=list';
$modules['11_system']['03_shipping_list'] = 'shipping.php?act=list';
$modules['11_system']['shipping_date_list'] = 'shipping.php?act=date_list';
$modules['11_system']['04_mail_settings'] = 'shop_config.php?act=mail_settings';
$modules['11_system']['05_area_list'] = 'area_manage.php?act=list';
$modules['11_system']['07_cron_schcron'] = 'cron.php?act=list';
$modules['11_system']['08_friendlink_list'] = 'friend_link.php?act=list';
$modules['11_system']['sitemap'] = 'sitemap.php';
$modules['11_system']['check_file_priv'] = 'check_file_priv.php?act=check';
$modules['11_system']['captcha_manage'] = 'captcha_manage.php?act=main';
$modules['11_system']['ucenter_setup'] = 'integrate.php?act=setup&code=ucenter';
$modules['11_system']['navigator'] = 'navigator.php?act=list';
$modules['11_system']['021_reg_fields'] = 'reg_fields.php?act=list';
$modules['11_system']['oss_configure'] = 'oss_configure.php?act=list';
$modules['12_template']['02_template_select'] = 'template.php?act=list';
$modules['12_template']['03_template_setup'] = 'template.php?act=setup';
$modules['12_template']['04_template_library'] = 'template.php?act=library';
$modules['12_template']['05_edit_languages'] = 'edit_languages.php?act=list';
$modules['12_template']['06_template_backup'] = 'template.php?act=backup_setting';
$modules['12_template']['mail_template_manage'] = 'mail_template.php?act=list';
$modules['13_backup']['02_db_manage'] = 'database.php?act=backup';
$modules['13_backup']['03_db_optimize'] = 'database.php?act=optimize';
$modules['13_backup']['04_sql_query'] = 'sql.php?act=main';
$modules['13_backup']['convert'] = 'convert.php?act=main';
$modules['15_rec']['affiliate'] = 'affiliate.php?act=list';
$modules['15_rec']['affiliate_ck'] = 'affiliate_ck.php?act=list';
$modules['16_email_manage']['email_list'] = 'email_list.php?act=list';
$modules['16_email_manage']['magazine_list'] = 'magazine_list.php?act=list';
$modules['16_email_manage']['attention_list'] = 'attention_list.php?act=list';
$modules['16_email_manage']['view_sendlist'] = 'view_sendlist.php?act=list';
$modules['10_offline_store']['12_offline_store'] = 'offline_store.php?act=list';
$modules['10_offline_store']['2_order_stats'] = 'offline_store.php?act=order_stats';

?>
