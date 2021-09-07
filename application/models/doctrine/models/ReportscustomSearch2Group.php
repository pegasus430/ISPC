<?php

	Doctrine_Manager::getInstance()->bindComponent('ReportscustomSearch2Group', 'MDAT');

	class ReportscustomSearch2Group extends BaseReportscustomSearch2Group {
		
	    public function get_groups_search_details($ids)
	    {
	        if (is_array($ids)) {
	            $groups = $ids;
	        } else {
	            $groups = array(
	                $ids
	            );
	        }
	    
	        $search_criterias = ReportscustomSearch::get_search_criterias();
	        
	        $report = Doctrine_Query::create()->select('*')
	        ->from('ReportscustomSearch2Group')
	        ->where('isdelete = 0')
	        ->andWhereIn('group_id', $groups);
	        $reportarray = $report->fetchArray();
	    
	        if ($reportarray) {
	    
	            foreach ($reportarray as $k => $val) {
	                $group_details[$val['group_id']][$val['search_id']] = $val;
	                $group_details[$val['group_id']][$val['search_id']]['system'] = $search_criterias[$val['search_id']];
	            }
	            return $group_details;
	        } else {
	            return false;
	        }
	    }
	    
	}

?>