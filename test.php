<?php
class Insurance
{
	function clsName()
	{
		echo get_class($this);
	}
}
$cl = new Insurance();
$cl->clsName();
//Insurance::clsName();


?>