<?php

	Doctrine_Manager::getInstance()->bindComponent('Reportsnew', 'MDAT');

	class Reportsnew extends BaseReportsnew {

		public function getallreports()
		{
			$report = Doctrine_Query::create()
				->select('*')
				->from('Reportsnew')
				->where('isdelete = 0');
			$reportarray = $report->fetchArray();

			return $reportarray;
		}

		public function getAllowedReports($ids)
		{
			if(empty($ids))
			{
				$ids = "'X'";
			}
			$report = Doctrine_Query::create()
				->select('*')
				->from('Reportsnew')
				->where('id IN (' . $ids . ')')
				->andWhere('isdelete = 0');
			$reportarray = $report->fetchArray();

			return $reportarray;
		}

		public function getreport($id)
		{
			$report = Doctrine_Query::create()
				->select('*')
				->from('Reportsnew')
				->where('id=' . $id);
			$reportarray = $report->fetchArray();

			return $reportarray;
		}

	}

?>