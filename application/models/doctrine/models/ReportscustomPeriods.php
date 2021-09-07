<?php

	Doctrine_Manager::getInstance()->bindComponent('ReportscustomPeriods', 'MDAT');

	class ReportscustomPeriods extends BaseReportscustomPeriods {
	    
	    
	    public function get_report_period($report_id)
	    {
	        $report_period_q = Doctrine_Query::create()
	        ->select("*,IF(start_date != '0000-00-00 00:00:00',DATE_FORMAT(start_date,'%d\.%m\.%Y'),'') as start_date, IF(end_date != '0000-00-00 00:00:00',DATE_FORMAT(end_date,'%d\.%m\.%Y'),'') as end_date")
	        ->from('ReportscustomPeriods')
	        ->where('report_id=' . $report_id)
	        ->andWhere('isdelete = 0');
	        $r_period_array = $report_period_q->fetchArray();
	        
	        if($r_period_array){
	            
    	        foreach($r_period_array as $k=>$per){
    	            
    	            $report_period[$report_id] = $per;
    	            $report_period[$report_id]['months'] = unserialize($per['months']);
    	            $report_period[$report_id]['quarters'] = unserialize($per['quarters']);
    	            $report_period[$report_id]['years'] = unserialize($per['years']);
    	        }
    	        
    	        return $report_period;
	        } else{
    	        return false;
	        }
	    }
 

	}

?>