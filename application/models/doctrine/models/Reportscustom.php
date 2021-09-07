<?php

	Doctrine_Manager::getInstance()->bindComponent('Reportscustom', 'MDAT');

	class Reportscustom extends BaseReportscustom {

		public function getallreports()
		{
			$report = Doctrine_Query::create()
				->select('*')
				->from('Reportscustom')
				->where('isdelete = 0')
				->andWhere('issaved = 0');
			$reportarray = $report->fetchArray();

			return $reportarray;
		}

		public function get_report($id)
		{
			$report = Doctrine_Query::create()
				->select('*')
				->from('Reportscustom')
				->where('id=' . $id)
			     ->andWhere('isdelete = 0');
			$reportarray = $report->fetchArray();
            if($reportarray){
                
			    $report_details=$reportarray[0];
            }
			
			return $report_details;
		}

	}

?>