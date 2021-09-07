<?php

	Doctrine_Manager::getInstance()->bindComponent('ReportscustomColumns', 'MDAT');

	class ReportscustomColumns extends BaseReportscustomColumns {
	    
	    public function get_columns($type = false)
	    {
	        $report = Doctrine_Query::create()
	        ->select('*')
	        ->from('ReportscustomColumns')
	        ->where('isdelete = 0');
	        
	        if($type){
	            $report->andWhere('search_type = "'.$type.'" ');
	        }
	        $reportarray = $report->fetchArray();
	    
	        return $reportarray;
	    }
	    


	    public function report_columns($report_id)
	    {
	        $report = Doctrine_Query::create()
	        ->select('c.*,c2r.*')
	        ->from('ReportscustomColumns c INDEXBY c.id')
	        ->leftJoin('c.ReportscustomColumns2Report as c2r ON c2r.column_id = c.id  AND c2r.report_id = "'.$report_id.'" AND c2r.isdelete = 0   INDEXBY  c2r.id')
	        ->where('c.isdelete = 0 ')
	        ->andWhere('c2r.id IS NOT NULL ')
	        ->orderBy('c2r.order_number ASC');
	        $reportarray = $report->fetchArray();
	    
	        
	        foreach($reportarray as $k => $col){
	            $columns_data[$report_id][$col['id']]['column'] =  $col['column_name'];
	            $columns_data[$report_id][$col['id']]['column_type'] =  $col['type'];
	            $columns_data[$report_id][$col['id']]['search_type'] =  $col['search_type'];
	            $columns_data[$report_id][$col['id']]['allow_average'] =  $col['allow_average'];
	            $columns_data[$report_id][$col['id']]['allow_median'] =  $col['allow_median'];
	            
	            $columns_data[$report_id][$col['id']]['report_show_average'] =  $col['ReportscustomColumns2Report']['show_average'];
	            $columns_data[$report_id][$col['id']]['report_show_median'] =  $col['ReportscustomColumns2Report']['show_median'];
	            /* foreach($col['ReportscustomColumns2Report'] as $c2r_id =>$c2r_data){
    	            $columns_data[$report_id][$col['id']]['report_show_average'] =  $c2r_data['show_average'];
    	            $columns_data[$report_id][$col['id']]['report_show_median'] =  $c2r_data['show_median'];
	            } */
	        }
	        
	        if ($reportarray) {
	            return $columns_data;
	        } else {
	            return false;
	        }
	    }
	    
	    
	}

?>