<?

 // require_once 'Zend/View.php';

class Pms_Template{


	public static function createTemplate($input,$templatepath = 'templates/patientinfo.html')
	{
		$view = new Zend_View();
		$view->setScriptPath(APPLICATION_PATH."/views/scripts/");
		
		foreach($input as $key=>$value)
		{
			$view->{$key} = $value;
		}
		
		return $view->render($templatepath);
		
	}

}
?>