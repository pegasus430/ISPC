<?php

class GraphController extends Zend_Controller_Action {
	function init() {
		$this->getHelper('viewRenderer')->setNoRender();
		$this->getHelper('layout')->disableLayout();

		require_once(APPLICATION_PATH.'/../library/Pms/JpGraph/jpgraph.php');
		require_once(APPLICATION_PATH.'/../library/Pms/JpGraph/jpgraph_bar.php');
		require_once(APPLICATION_PATH.'/../library/Pms/JpGraph/jpgraph_pie.php');
		require_once(APPLICATION_PATH.'/../library/Pms/JpGraph/jpgraph_pie3d.php');
	}

	public function piegraphAction($imagedata, $pdf = false)
	{


		if(empty($imagedata)) {
			$imagedata = $_REQUEST;
			$gdata = unserialize(base64_decode(urldecode($imagedata['gdata'])));
		} else {
			$gdata = $imagedata['gdata'];
		}
			
		Pms_Graph::piechart($gdata,$imagedata['gtitle'],$pdf);
			

	}
}