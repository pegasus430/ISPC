<?
// require_once 'Zend/Controller/Response/Abstract.php';

class Pms_Controller_Response extends Zend_Controller_Response_Abstract
{

	protected $containerId;
	
	public function sendResponse()
	{
		
		$layout = Zend_Layout::getMvcInstance();
		$view = $layout->getView();
		$sc = $view->getScriptPath('client');
		var_dump($sc);
		$front = Zend_Controller_Front::getInstance();
		
		$request = $front->getRequest();
		
		
		
		
		$data['data'] = $view->render($request->getControllerName().'/'.$request->getActionName().".html");
		$data['container'] = $this->containerId;
		
		echo Zend_Json::encode($data);
		exit;
		//echo "dfsadf";
	}
	
	public function outputBody()
    {
        foreach ($this->_body as $content) {
            echo $content;
        }
    }
	
	public function setContainer($containerId)
	{
		$this->containerId = $containerId;	
	}

}
?>