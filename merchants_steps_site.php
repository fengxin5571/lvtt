<?php
//dezend by  QQ:2172298892
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

$user_id = $_SESSION['user_id'];
$did = ($_REQUEST['deg']== 'guide'?1:0);
if ($user_id <= 0) {
	show_message($_LANG['steps_UserLogin'], $_LANG['UserLogin'], 'user.php');
	exit();
}

$sql = 'select steps_site from ' . $ecs->table('merchants_steps_fields') . ' where user_id = \'' . $user_id . '\' AND identity ='.$did;
$steps_site = $db->getOne($sql);

if (empty($steps_site)) {
	$steps_site = 'merchants_steps.php?deg='.$_REQUEST['deg'];
}

ecs_header('Location: ' . $steps_site . "\n");
exit();

?>
