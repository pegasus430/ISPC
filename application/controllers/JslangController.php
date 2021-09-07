<?php 

class JslangController extends Zend_Controller_Action
{
	/**
	 * this fn was added so it can use the new translation folder 
	 * Aug 14, 2017 @claudiu 
	 *
	 */
	public function jstranslateAction()
	{
		// returns all the complete translation data
		$Translator = Zend_Registry::get('Zend_Translate');
		$langvalue = $Translator->getMessages();
		
		$jsstring = 'var jsTranslate={';

		$comma = '';

		foreach($langvalue as $key=>$val)
		{
			if (is_array($val)) {
				$val = json_encode($val);
				$jsstring .= $comma.'"'.$key.'":' . $val ;
			} else {
				$jsstring .= $comma.'"'.$key.'":"'.str_replace('"','\"',$val).'"';
			}
			$comma=",\n";
			
		}
		
		ob_end_clean();
		ob_start();
		
		echo $jsstring .= '};';
		
		exit;
	}

	
	public function jstranslateAction_OLD()
	{
		$langvalue = include("../application/language/".LANG.".php");
		ob_end_clean();
		$jsstring = 'var jsTranslate={';
	
		foreach($langvalue as $key=>$val)
		{
			if (is_array($val)) {
				$val = json_encode($val);
				$jsstring .= $comma.'"'.$key.'":' . $val ;
			} else {
				$jsstring .= $comma.'"'.$key.'":"'.str_replace('"','\"',$val).'"';
			}
			$comma=",\n";
				
		}
		echo $jsstring .= '};';
	
		exit;
	}
}
?>