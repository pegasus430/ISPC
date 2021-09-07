<?php

class ClientbackupController extends Zend_Controller_Action
{

	public function init()
	{
	}

	public function clientbackupAction()
	{
		$client = Doctrine::getTable('Client')->findAll();
		$clientarray = $client->toArray();
		while($clientarray)
		{

		}
	}
}

?>