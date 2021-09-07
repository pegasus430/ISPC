<?php

	Doctrine_Manager::getInstance()->bindComponent('ReportscustomSearch', 'MDAT');

	class ReportscustomSearch extends BaseReportscustomSearch {

		public function get_search_criterias()
		{
			$report = Doctrine_Query::create()
				->select('*')
				->from('ReportscustomSearch')
				->where('isdelete = 0')
			     ->orderBy('type');
			$reportarray = $report->fetchArray();

			if($reportarray){
			    
    			foreach($reportarray as $k=>$sdata){
    			    $search_details[$sdata['id']] = $sdata;
    			}
    			
			} else {
			    $search_details = array();
			}
			
   			return $search_details ;
		}
	}

?>