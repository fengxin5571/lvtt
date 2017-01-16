<?php
//zend by QQ:2172298892
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$modules['04_order']['11_order_detection'] = 'order.php?act=order_detection';
$modules['04_order']['11_add_order'] = 'mc_order.php';
$modules['02_cat_and_goods']['sale_notice'] = 'sale_notice.php?act=list';
$modules['04_order']['11_back_cause'] = 'order.php?act=back_cause_list';
$modules['04_order']['12_back_apply'] = 'order.php?act=return_list';
$modules['02_cat_and_goods']['discuss_circle'] = 'discuss_circle.php?act=list';
$modules['01_system']['user_keywords_list'] = 'keywords_manage.php?act=list';
$modules['17_merchants']['01_merchants_steps_list'] = 'merchants_steps.php?act=list';
$modules['17_merchants']['02_guides_users_list'] = 'guides_users_list.php?act=list';
$modules['17_merchants']['02_merchants_users_list'] = 'merchants_users_list.php?act=list';
$modules['17_merchants']['03_merchants_commission'] = 'merchants_commission.php?act=list';
$modules['17_merchants']['03_users_merchants_priv'] = 'merchants_privilege.php?act=allot';
$modules['17_merchants']['04_create_seller_grade'] = 'merchants_users_list.php?act=create_seller_grade';
$modules['17_merchants']['05_comment_seller_rank'] = 'comment_seller.php?act=list';
$modules['17_merchants']['09_seller_domain'] = 'seller_domain.php?act=list';
$modules['17_merchants']['10_seller_grade'] = 'seller_grade.php?act=list';
$modules['17_merchants']['11_seller_apply'] = 'seller_apply.php?act=list';
$modules['17_merchants']['16_seller_users_real'] = 'user_real.php?act=list&user_type=1';
$modules['17_merchants']['12_seller_account'] = 'merchants_account.php?act=list';

$modules['19_merchants_store']['01_merchants_basic_info'] = 'index.php?act=merchants_first';
$modules['19_merchants_store']['02_merchants_ad'] = 'seller_shop_slide.php?act=list';
$modules['19_merchants_store']['03_merchants_shop_top'] = 'index.php?act=shop_top';
$modules['19_merchants_store']['04_merchants_basic_nav'] = 'merchants_navigator.php?act=list';
$modules['19_merchants_store']['07_merchants_window'] = 'merchants_window.php?act=list';
$modules['19_merchants_store']['08_merchants_template'] = 'merchants_template.php?act=list';
$modules['08_members']['12_user_address_list'] = 'user_address_log.php?act=list';
$modules['20_ectouch']['01_oauth_admin'] = '../mobile/index.php?r=oauth/admin';
$modules['20_ectouch']['02_touch_nav_admin'] = 'touch_navigator.php?act=list';
$modules['20_ectouch']['03_touch_ads'] = 'touch_ads.php?act=list';
$modules['20_ectouch']['04_touch_ad_position'] = 'touch_ad_position.php?act=list';
$modules['21_cloud']['01_cloud_services'] = 'index.php?act=cloud_services';
$modules['21_cloud']['02_platform_recommend'] = 'index.php?act=platform_recommend';
$modules['21_cloud']['03_best_recommend'] = 'index.php?act=best_recommend';
$modules['02_cat_and_goods']['01_goods_list'] = 'goods.php?act=list';

if ($GLOBALS['_CFG']['review_goods'] == 1) {
	$modules['02_cat_and_goods']['01_review_status'] = 'goods.php?act=review_status';
}

$modules['02_cat_and_goods']['03_category_list'] = 'category.php?act=list';
$modules['02_cat_and_goods']['05_comment_manage'] = 'comment_manage.php?act=list';
$modules['02_cat_and_goods']['06_goods_brand'] = 'brand.php?act=list';
$modules['02_cat_and_goods']['08_goods_type'] = 'goods_type.php?act=manage';
$modules['02_cat_and_goods']['15_batch_edit'] = 'goods_batch.php?act=select';
$modules['02_cat_and_goods']['gallery_album'] = 'gallery_album.php?act=list';
$modules['02_goods_storage']['01_goods_storage_put'] = 'goods_inventory_logs.php?act=list&step=put';
$modules['02_goods_storage']['02_goods_storage_out'] = 'goods_inventory_logs.php?act=list&step=out';
//$modules['03_promotion']['02_snatch_list'] = 'snatch.php?act=list';
//$modules['03_promotion']['04_bonustype_list'] = 'bonus.php?act=list';
//$modules['03_promotion']['08_group_buy'] = 'group_buy.php?act=list';
//$modules['03_promotion']['09_topic'] = 'topic.php?act=list';
//$modules['03_promotion']['10_auction'] = 'auction.php?act=list';
//$modules['03_promotion']['12_favourable'] = 'favourable.php?act=list';
//$modules['03_promotion']['13_wholesale'] = 'wholesale.php?act=list';
//$modules['03_promotion']['14_package_list'] = 'package.php?act=list';
//$modules['03_promotion']['15_exchange_goods'] = 'exchange_goods.php?act=list';
//$modules['03_promotion']['17_coupons'] = 'coupons.php?act=list';
//$modules['03_promotion']['gift_gard_list'] = 'gift_gard.php?act=list';
//$modules['03_promotion']['16_presale'] = 'presale.php?act=list';
$modules['04_order']['02_order_list'] = 'order.php?act=list';
$modules['04_order']['05_edit_order_print'] = 'order.php?act=templates';
$modules['04_order']['06_undispose_booking'] = 'goods_booking.php?act=list_all';
$modules['04_order']['08_add_order'] = 'order.php?act=add';
$modules['04_order']['09_delivery_order'] = 'order.php?act=delivery_list';
$modules['04_order']['10_back_order'] = 'order.php?act=back_list';
$modules['05_banner']['ad_position'] = 'ad_position.php?act=list';
$modules['05_banner']['ad_list'] = 'ads.php?act=list';
$modules['06_stats']['report_guest'] = 'guest_stats.php?act=list';
$modules['06_stats']['report_order'] = 'order_stats.php?act=list';
$modules['06_stats']['sale_list'] = 'sale_list.php?act=list';
$modules['06_stats']['report_users'] = 'users_order.php?act=order_num';
$modules['06_stats']['visit_buy_per'] = 'visit_sold.php?act=list';
$modules['07_content']['03_article_list'] = 'article.php?act=list';
$modules['07_content']['02_articlecat_list'] = 'articlecat.php?act=list';
$modules['07_content']['vote_list'] = 'vote.php?act=list';
$modules['07_content']['article_auto'] = 'article_auto.php?act=list';
$modules['08_members']['03_users_list'] = 'users.php?act=list';
$modules['08_members']['05_user_rank_list'] = 'user_rank.php?act=list';
$modules['08_members']['06_list_integrate'] = 'integrate.php?act=list';
$modules['08_members']['08_unreply_msg'] = 'user_msg.php?act=list_all';
$modules['08_members']['09_user_account'] = 'user_account.php?act=list';
$modules['08_members']['10_user_account_manage'] = 'user_account_manage.php?act=list';
$modules['08_members']['13_user_baitiao_info'] = 'user_baitiao_log.php?act=list';
$modules['08_members']['16_users_real'] = 'user_real.php?act=list';
$modules['10_priv_admin']['admin_logs'] = 'admin_logs.php?act=list';
$modules['10_priv_admin']['01_admin_list'] = 'privilege.php?act=list';
$modules['10_priv_admin']['02_admin_seller'] = 'privilege_seller.php?act=list';
$modules['10_priv_admin']['admin_role'] = 'role.php?act=list';
$modules['10_priv_admin']['agency_list'] = 'agency.php?act=list';
$modules['10_priv_admin']['suppliers_list'] = 'suppliers.php?act=list';
$modules['01_system']['01_shop_config'] = 'shop_config.php?act=list_edit';
$modules['01_system']['02_payment_list'] = 'payment.php?act=list';
$modules['01_system']['03_area_shipping'] = 'shipping.php?act=list';
$modules['01_system']['04_mail_settings'] = 'shop_config.php?act=mail_settings';
$modules['01_system']['07_cron_schcron'] = 'cron.php?act=list';
$modules['01_system']['08_friendlink_list'] = 'friend_link.php?act=list';
$modules['01_system']['09_partnerlink_list'] = 'friend_partner.php?act=list';
$modules['01_system']['sitemap'] = 'sitemap.php';
$modules['01_system']['check_file_priv'] = 'check_file_priv.php?act=check';
$modules['01_system']['captcha_manage'] = 'captcha_manage.php?act=main';
$modules['01_system']['ucenter_setup'] = 'integrate.php?act=setup&code=ucenter';
$modules['01_system']['navigator'] = 'navigator.php?act=list';
$modules['01_system']['021_reg_fields'] = 'reg_fields.php?act=list';
$modules['01_system']['api'] = 'oss_configure.php?act=list';
$modules['12_template']['02_template_select'] = 'template.php?act=list';
$modules['12_template']['03_template_setup'] = 'template.php?act=setup';
$modules['12_template']['04_template_library'] = 'template.php?act=library';
$modules['12_template']['05_edit_languages'] = 'edit_languages.php?act=list';
$modules['12_template']['06_template_backup'] = 'template.php?act=backup_setting';
$modules['12_template']['mail_template_manage'] = 'mail_template.php?act=list';
//$modules['13_backup']['02_db_manage'] = 'database.php?act=backup';
//$modules['13_backup']['03_db_optimize'] = 'database.php?act=optimize';
//$modules['13_backup']['04_sql_query'] = 'sql.php?act=main';
//$modules['13_backup']['convert'] = 'convert.php?act=main';
//$modules['13_backup']['05_table_prefix'] = 'table_prefix.php?act=edit';
//$modules['13_backup']['06_transfer_config'] = 'transfer_manage.php';
//$modules['13_backup']['07_transfer_choose'] = 'transfer_manage.php?act=choose';
$modules['15_rec']['affiliate'] = 'affiliate.php?act=list';
$modules['15_rec']['affiliate_ck'] = 'affiliate_ck.php?act=list';
$modules['16_email_manage']['email_list'] = 'email_list.php?act=list';
$modules['16_email_manage']['magazine_list'] = 'magazine_list.php?act=list';
$modules['16_email_manage']['attention_list'] = 'attention_list.php?act=list';
$modules['16_email_manage']['view_sendlist'] = 'view_sendlist.php?act=list';
//$modules['09_crowdfunding']['01_crowdfunding_list'] = 'zc_project.php?act=list';
//$modules['09_crowdfunding']['02_crowdfunding_cat'] = 'zc_category.php?act=list';
//$modules['09_crowdfunding']['03_project_initiator'] = 'zc_initiator.php?act=list';
//$modules['09_crowdfunding']['04_topic_list'] = 'zc_topic.php?act=list';
$menu_top['menuplatform'] = '05_banner,06_stats,07_content,08_members,10_priv_admin,01_system,12_template,16_email_manage,13_backup';
$menu_top['menushopping'] = '02_cat_and_goods,03_promotion,04_order,09_crowdfunding,15_rec,17_merchants,18_batch_manage,19_merchants_store,02_goods_storage,10_offline_store';
$menu_top['ectouch'] = '20_ectouch,22_wechat,23_drp';
//$menu_top['menuinformation'] = '21_cloud';
$modules['20_ectouch']['01_oauth_admin'] = '../mobile/index.php?r=oauth/admin';
$modules['20_ectouch']['02_touch_nav_admin'] = 'touch_navigator.php?act=list';
$modules['20_ectouch']['03_touch_ads'] = 'touch_ads.php?act=list';
$modules['20_ectouch']['04_touch_ad_position'] = 'touch_ad_position.php?act=list';

if (file_exists(MOBILE_WECHAT)) {
	$modules['22_wechat']['01_wechat_admin'] = '../mobile/index.php?r=wechat/admin/modify';
	$modules['22_wechat']['02_mass_message'] = '../mobile/index.php?r=wechat/admin/mass_message';
	$modules['22_wechat']['03_auto_reply'] = '../mobile/index.php?r=wechat/admin/reply_subscribe';
	$modules['22_wechat']['04_menu'] = '../mobile/index.php?r=wechat/admin/menu_list';
	$modules['22_wechat']['05_fans'] = '../mobile/index.php?r=wechat/admin/subscribe_list';
	$modules['22_wechat']['06_media'] = '../mobile/index.php?r=wechat/admin/article';
	$modules['22_wechat']['07_qrcode'] = '../mobile/index.php?r=wechat/admin/qrcode_list';
	$modules['22_wechat']['08_share'] = '../mobile/index.php?r=wechat/admin/share_list';
	$modules['22_wechat']['09_extend'] = '../mobile/index.php?r=wechat/extend';
	$modules['22_wechat']['11_template'] = '../mobile/index.php?r=wechat/admin/template';
}

if (file_exists(MOBILE_DRP)) {
	$modules['23_drp']['01_drp_config'] = '../mobile/index.php?r=drp/admin/config';
	$modules['23_drp']['02_drp_shop'] = '../mobile/index.php?r=drp/admin/shop';
	$modules['23_drp']['03_drp_list'] = '../mobile/index.php?r=drp/admin/drplist';
	$modules['23_drp']['04_drp_order_list'] = '../mobile/index.php?r=drp/admin/drporderlist';
	$modules['23_drp']['05_drp_set_config'] = '../mobile/index.php?r=drp/admin/drpsetconfig';
}

$modules['10_offline_store']['12_offline_store'] = 'offline_store.php?act=list';
$modules['10_offline_store']['2_order_stats'] = 'offline_store.php?act=order_stats';

?>
