<?php

	Doctrine_Manager::getInstance()->bindComponent('ReportPermission', 'MDAT');

	class ReportPermission extends BaseReportPermission {

		public function getAllowedReports($clientid)
		{
			$perm = Doctrine_Query::create()
				->select('*')
				->from('ReportPermission')
				->where('clientid =' . $clientid . '');
			$report_perm = $perm->fetchArray();

			return $report_perm[0]['report_id'];
		}

	}

?>