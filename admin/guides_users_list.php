<?php 
/*
 * 后台导游类
 * 
 */
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('guide_shop_information'), $db, 'guide_id', 'gudie_name');


if ($_REQUEST['act'] == 'list') {//获取全部导游列表
    admin_priv('users_guides');
    $smarty->assign('ur_here', $_LANG['02_guides_users_list']);
    $smarty->assign('action_link', array('text' => $_LANG['01_guides_user_add'], 'href' => 'guides_users_list.php?act=add_guide'));  
    $users_list = steps_users_list();//获取导游列表
    $smarty->assign('users_list', $users_list['users_list']);
    $smarty->assign('filter', $users_list['filter']);
    $smarty->assign('record_count', $users_list['record_count']);
    $smarty->assign('page_count', $users_list['page_count']);
    $smarty->assign('full_page', 1);
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
    $store_list = get_common_store_list();
    $smarty->assign('store_list', $store_list);
    assign_query_info();
    $smarty->display('guides_users_list.dwt');
}
else if ($_REQUEST['act'] == 'query') { //aiax查询
    $users_list = steps_users_list();
    $smarty->assign('users_list', $users_list['users_list']);
    $smarty->assign('filter', $users_list['filter']);
    $smarty->assign('record_count', $users_list['record_count']);
    $smarty->assign('page_count', $users_list['page_count']);
    $store_list = get_common_store_list();
    $smarty->assign('store_list', $store_list);
    $sort_flag = sort_flag($users_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('guides_users_list.dwt'), '', array('filter' => $users_list['filter'], 'page_count' => $users_list['page_count']));
}
if (($_REQUEST['act'] == 'add_guide') || ($_REQUEST['act'] == 'edit_guide')) {//添加、编辑导游界面显示
    admin_priv('users_guides');
    $user_id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
   //$login_name = (isset($_REQUEST['login_name']) ? trim($_REQUEST['login_name']) : '');//导游登录名称
    $guideInfo_list = get_steps_user_shopinfo_list(1,$user_id);//获取导游身份的入驻流程步骤信息以及导游信息
    if(empty($guideInfo_list['guide_info']['login_name'])){
        $login_suffix =random(4,9999);
        $guideInfo_list['guide_info']['login_name']="lvtt_".$login_suffix;
    }
    $smarty->assign('guideInfo_list', $guideInfo_list);
    $smarty->assign('action_link', array('text' => $_LANG['02_guies_users_list'], 'href' => 'guides_users_list.php?act=list'));
    $country_list = get_regions_steps();
    $province_list = get_regions_steps(1, 1);
    $city_list = get_regions_steps(2, $consignee['province']);
    $district_list = get_regions_steps(3, $consignee['city']);
    $sql = ' SELECT region_id, region_name FROM ' . $ecs->table('region');
    $region = $db->getAll($sql);

    foreach ($region as $v) {
        $regions[$v['region_id']] = $v['region_name'];
    }

    $smarty->assign('regions', $regions);
    $sql = 'select steps_audit, guide_audit, guides_message, review_goods,is_show,is_IM from ' . $ecs->table('guide_shop_information') . ' where user_id = \'' . $user_id . '\'';
    $guides = $db->getRow($sql);
    $smarty->assign('guides', $guides);
    $sn = 0;
    $smarty->assign('country_list', $country_list);
    $smarty->assign('province_list', $province_list);
    $smarty->assign('city_list', $city_list);
    $smarty->assign('district_list', $district_list);
    $smarty->assign('consignee', $consignee);
    $smarty->assign('sn', $sn);
    $smarty->assign('user_id', $user_id);

    if ($_REQUEST['act'] == 'edit_guide') { //如果是编辑导游
        $sql = 'select user_id, user_name from '.$ecs->table('users') .'where user_id = '.$user_id;
        $user_info=$db->getRow($sql);
        $smarty->assign('user_info',$user_info);
        $smarty->assign('form_action', 'update_guide');
    }
    else {
        $sql = 'select user_id, user_name from' . $ecs->table('users') . ' where 1';//获取平台所有会员
        $user_list = $db->getAll($sql);
        $smarty->assign('user_list', $user_list);
        $smarty->assign('form_action', 'insert_guide');
    }

    assign_query_info();
    $smarty->display('guides_users_shopInfo.dwt');
}
/*处理添加和更新 */

else if (($_REQUEST['act'] == 'insert_guide') || ($_REQUEST['act'] == 'update_guide')) {
    admin_priv('users_guides');//查看是否有此权限
    $user_id = (isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0);//获取用户id
    $review_goods = (isset($_REQUEST['review_goods']) ? intval($_REQUEST['review_goods']) : 0);//是否审核其接团信息
    $guide_audit = (isset($_REQUEST['guide_audit']) ? intval($_REQUEST['guide_audit']) : 0);//导游信息审核
    $guides_message = (isset($_REQUEST['guides_message']) ? trim($_REQUEST['guides_message']) : '');//回复导游信息
    $login_name = (isset($_REQUEST['login_name']) ? trim($_REQUEST['login_name']) : '');//登录名称
    $password = (isset($_REQUEST['login_password']) ? trim($_REQUEST['login_password']) : '');//登录密码
    $is_show = (isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0);//是否显示
    $form = get_admin_steps_title_insert_form($user_id);//获取要添加或更新的字段名称（动态获取入驻步骤里的字段/导游身份）
    $parent = get_setps_form_insert_date($form['formName']);//将获取的字段名称转换成键值对数组
    $parent['identity']=1;
    if($parent['contactName']){
        $info['guide_name']=$parent['contactName'];
    }
    if(empty($password)){
        $password='123456';
    }
    if($guide_audit ==1){
        $info['login_name'] = $login_name;
        $info['password'] = md5($password);
        $is_show = 1;
    }else if($guide_audit ==2){
        $info['login_name'] = "";
        $info['password'] = '';
    }
    $info['guide_audit'] = $guide_audit;
    $info['review_goods'] = $review_goods;
    $info['guides_message'] = $guides_message;
    $info['is_show'] = $is_show;
    if ($_REQUEST['act'] == 'update_guide') {//如果是更新就更新反之新增
        
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields'), $parent, 'UPDATE', 'user_id = \'' . $user_id . '\' AND identity = 1');
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('guide_shop_information'), $info, 'UPDATE', 'user_id = \'' . $user_id . '\'');
    }else{
        $parent['user_id'] = $user_id;
        $parent['agreement'] = 1;
        
        $sql = 'select fid from ' . $ecs->table('merchants_steps_fields') . ' where user_id = \'' . $user_id . '\' AND identity = 1';
        $fid = $db->getOne($sql);
        if (0 < $fid) {//如果有值说明此会员已经入驻了,提示错误
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'guides_users_list.php?act=add_guide');
            $centent = $_LANG['insert_fail'];
            sys_msg($centent, 0, $link);
            exit();
        }
        else {
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields'), $parent, 'INSERT');
        }
        $guide_info=get_guide_septs_custom_userInfo('guide_shop_information',$user_id);//获取导游信息
        if($guide_info){//如果有值说明此会员已经入驻了,提示错误
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'guides_users_list.php?act=add_guide');
            sys_msg($_LANG['insert_fail'], 0, $link);
            exit();
        }
        
        $info['steps_audit'] = 1;
        $info['user_id'] =$user_id;
        
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('guide_shop_information'), $info, 'INSERT');
    }
    if($info['guide_audit'] == 1 && !empty($parent['contactEmail'])){//审核通过后发送导游登录账号密码到其邮箱
        $template = get_mail_template('send_guide_password');//获取邮件模板
        $email= $parent['contactEmail'];
        $smarty->assign('send_date', date('Y-m-d'));
        $smarty->assign("send_loginname", $info['login_name']);
        $smarty->assign("send_password", $password);
        $content = $smarty->fetch('str:' . $template['template_content']);
        send_mail($user_name, $email, $template['template_subject'], $content, $template['is_html']);
    }

    if ($_REQUEST['act'] == 'update_guide') {
        $centent = $_LANG['update_success'];
    }
    else {
        $centent = $_LANG['insert_success'];
    }
    $href = 'guides_users_list.php?act=list';
    $link[] = array('text' => $_LANG['go_back'], 'href' => $href);
    sys_msg($centent, 0, $link);
    
}
/*删除入驻导游*/
else if ($_REQUEST['act'] == 'remove') {
    admin_priv('users_guides');//查看是否有此权限
    $id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
    $form = get_admin_steps_title_insert_form($id);//获取要添加或更新的字段名称（动态获取入驻步骤里的字段/导游身份）
    $parent=explode(",",$form['formName']);
    $query=array();
    foreach($parent as $key=> $value){
        if(substr($value, -3) == 'Img'){
            $query[$key]=$value;
        }
    }
    if(is_array($query)){
        $query=implode(",", $query);
    }
    $sql = "select ".$query." from " . $ecs->table('merchants_steps_fields') . ' where user_id = \'' . $id . '\' AND identity = 1';
    $guide_img = $db->getRow($sql);
    foreach ($guide_img as $val){
        if($val){
            @unlink('../' . $val);
        }
    }
    $sql = 'delete from ' . $ecs->table('guide_shop_information') . ' where user_id = \'' . $id . '\'';
    $db->query($sql);
    $sql = 'delete from ' . $ecs->table('merchants_steps_fields') . ' where user_id = \'' . $id . '\' AND identity = 1';
    $db->query($sql);
    if ($GLOBALS['_CFG']['delete_seller'] && $id) {//删除导游时是否删除导游的所有信息
        
    }
    $link[] = array('text' => $_LANG['go_back'], 'href' => 'guides_users_list.php?act=list');
    sys_msg('删除成功', 0, $link);
}
else if ($_REQUEST['act'] == 'toggle_is_show') {//编辑是否显示
    check_authz_json('goods_manage');
    $guide_id = intval($_POST['id']);
    $is_show = intval($_POST['val']);

    if ($exc->edit('is_show = \'' . $is_show . '\'', $guide_id)) {
        clear_cache_files();
        make_json_result($is_show);
    }
}
else if ($_REQUEST['act'] == 'toggle_is_IM') {//是否IM在线客服
    check_authz_json('goods_manage');
    $guide_id = intval($_POST['id']);
    $is_IM = intval($_POST['val']);

    if ($exc->edit('is_IM = \'' . $is_IM . '\'', $guide_id)) {
        clear_cache_files();
        make_json_result($is_IM);
    }
}
else if ($_REQUEST['act'] == 'edit_sort_order') {//编辑排序
    check_authz_json('users_guides');
    $guide_id = intval($_POST['id']);
    $sort_order = intval($_POST['val']);

    if ($exc->edit('sort_order = \'' . $sort_order . '\'', $guide_id)) {
        clear_cache_files();
        make_json_result($sort_order);
    }
}
/*
 * 获取入驻导游列表
 * 
 * @param
 * @return $arr
 */
function steps_users_list()
{
    $result = get_filter();

    if ($result === false) {
        $filter['keywords'] = !isset($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
       
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'mis.guide_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
        $ex_where = ' WHERE 1 ';
        $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['guide_name']) ? trim($_REQUEST['guide_name']) : '';
        $store_where = '';
        $store_search_where = '';
        
        if ($filter['store_search'] != 0) {
                

                if ($filter['store_search'] == 1) {
                    $ex_where .= ' AND mis.user_id = \'' . $filter['merchant_id'] . '\' ';
                }
                else if ($filter['store_search'] == 2) {
                    $store_where .= ' AND mis.guide_name LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
                }
                

                if (1 < $filter['store_search']) {
                    $ex_where .= ' AND mis.user_id > 0 ' . $store_where . ' ';
                }
           
        }

        $ex_where .= (!empty($filter['user_name']) ? ' AND (u.user_name LIKE \'%' . mysql_like_quote($filter['user_name']) . '%\')' : '');
        $filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('guide_shop_information') . ' as mis ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' as u on mis.user_id = u.user_id ' . $ex_where);
        $filter = page_and_size($filter);
        $sql = 'SELECT mis.* ' . ' FROM ' . $GLOBALS['ecs']->table('guide_shop_information') . ' AS mis ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' as u on mis.user_id = u.user_id ' . $ex_where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
       
    }
    else {
        $sql = $result['sql'];
        
        $filter = $result['filter'];
    }
    
    $users_list = $GLOBALS['db']->getAll($sql);
    $count = count($users_list);
    for ($i = 0; $i < $count; $i++) {
        $users_list[$i]['guide_id'] = $users_list[$i]['guide_id'];
        $users_list[$i]['user_name'] = $GLOBALS['db']->getOne('select user_name from ' . $GLOBALS['ecs']->table('users') . ' where user_id = \'' . $users_list[$i]['user_id'] . '\'');

    }
    
    $arr = array('users_list' => $users_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    return $arr;
}
/*
 * 获取导游、供应商对应的入驻申请流程步骤
 * 
 * @param $identity         所属身份0 供应商 1导游
 *        $user_id          用户id
 *        
 *        
 * @return $arr       
 */
 
function get_steps_user_shopInfo_list($identity=0,$user_id = 0, $ec_shop_bid = 0)
{
    $sql = 'select * from ' . $GLOBALS['ecs']->table('merchants_steps_process') . ' where 1 and process_steps <> 1 AND is_show = 1 AND identity = '. $identity.' AND id <> 10 order by process_steps asc';
    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();

    foreach ($res as $key => $row) {
        $arr[$key]['sp_id'] = $row['id'];
        $arr[$key]['process_title'] = $row['process_title'];
        $arr[$key]['steps_title'] = get_user_steps_title($row['id'], $user_id);
        
    }
    $guide_info = get_guide_septs_custom_userinfo('guide_shop_information', $user_id);//获取导游信息
    $arr['guide_info']=$guide_info;
    return $arr;
}
/*
 * 获取对应流程步骤的详细流程标题描述信息
 * 
 * @param   $id             所属流程id
 *          $user_id        用户id
 *          
 *          
 * @return $arr
 */
function get_user_steps_title($id = 0, $user_id)
{
    include_once ROOT_PATH . '/includes/cls_image.php';
    $image = new cls_image($_CFG['bgcolor']);
    $sql = 'select tid, fields_titles, steps_style, titles_annotation from ' . $GLOBALS['ecs']->table('merchants_steps_title') . ' where fields_steps = \'' . $id . '\'';
    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();

    foreach ($res as $key => $row) {
        $arr[$key]['tid'] = $row['tid'];
        $arr[$key]['fields_titles'] = $row['fields_titles'];
        $arr[$key]['steps_style'] = $row['steps_style'];
        $arr[$key]['titles_annotation'] = $row['titles_annotation'];
        $sql = 'select * from ' . $GLOBALS['ecs']->table('merchants_steps_fields_centent') . ' where tid = \'' . $row['tid'] . '\'';
        $centent = $GLOBALS['db']->getRow($sql);
        $cententFields = get_fields_centent_info(1,$centent['id'], $centent['textFields'], $centent['fieldsDateType'], $centent['fieldsLength'], $centent['fieldsNotnull'], $centent['fieldsFormName'], $centent['fieldsCoding'], $centent['fieldsForm'], $centent['fields_sort'], $centent['will_choose'], 'root', $user_id);
        $arr[$key]['cententFields'] = get_array_sort($cententFields, 'fields_sort');  
        
    }

    return $arr;
}
/*
 * 获取指定内容的所有信息
 * 
 * @param  $table       数据库表名
 *         $user_id     用户id
 *         
 * @return $result
 */
function get_guide_septs_custom_userInfo($table = '', $user_id = 0)
{
    
    $sql = 'select * from ' . $GLOBALS['ecs']->table($table) . ' where user_id = \'' . $user_id . '\'' ;
    return $GLOBALS['db']->getRow($sql);
}

/*
 * 获取指定导游身份所有入驻流程中的字段
 *
 * @param  $user_id     用户id
 *
 * @return $after_arr
 */
function get_admin_steps_title_insert_form($user_id)
{
    $sql = 'select * from ' . $GLOBALS['ecs']->table('merchants_steps_process') . ' where 1 and process_steps <> 1 AND is_show = 1 AND identity = 1 AND id <> 10 order by process_steps asc';
    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();
    
    foreach ($res as $key => $row) {
        $arr[$key]['sp_id'] = $row['id'];
        $arr[$key]['process_title'] = $row['process_title'];
        $arr[$key]['steps_title'] = get_user_steps_title($row['id'], $user_id);
        if(is_array($arr[$key]['steps_title'][0]['cententFields'])){
            $cententFields = $arr[$key]['steps_title'][0]['cententFields'];
            for ($j = 1; $j <= count($cententFields); $j++) {
                    $after_arr['formName'] .= $cententFields[$j]['textFields'] . ',';
                }
        }
        
    }
    $after_arr['formName'] = substr($after_arr['formName'], 0, -1);
    return $after_arr;
}
/*
 * 获取随机数
 *
 * @param  $length     长度
 *         $mumeric    传入数值  不为0则 生产带字符串的随机码
 *
 * @return $hash
 */
function random($length = 6, $numeric = 0)
{
    (PHP_VERSION < '4.2.0') && mt_srand((double) microtime() * 1000000);

    if ($numeric) {
        $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
    }
    else {
        $hash = '';
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
    }

    return $hash;
}
?>