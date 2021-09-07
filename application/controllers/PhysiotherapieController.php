<?php
class PhysiotherapieController extends Zend_Controller_Action
{

	public function init ()
	{

	}

	public function physiotherapielistAction ()
	{

	}

	public function physiotherapieeditAction ()
	{

	}

	public function physiotherapieaddAction ()
	{

	}

	public function fetchlistAction()
	{
		
	}

	private function retainValues ( $values )
	{
		foreach ($values as $key => $val)
		{
			$this->view->$key = $val;
		}
	}

}
?>