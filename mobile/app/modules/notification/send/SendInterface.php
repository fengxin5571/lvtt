<?php
//zend by QQ:2172298892
namespace app\modules\notification\send;

interface SendInterface
{
	public function __construct($config);

	public function push($to, $title, $content, $data = array());

	public function getError();
}


?>
