<?php
//dezend by  QQ:2172298892
class AlibabaAliqinFcFlowGradeRequest
{
	private $apiParas = array();

	public function getApiMethodName()
	{
		return 'alibaba.aliqin.fc.flow.grade';
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function check()
	{
	}

	public function putOtherTextParam($key, $value)
	{
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}


?>
