<?php
Doctrine_Manager::getInstance()->bindComponent('ReportscustomGroups', 'MDAT');

class ReportscustomGroups extends BaseReportscustomGroups
{

    public function get_groups_details($ids)
    {
        if (is_array($ids)) {
            $groups = $ids;
        } else {
            $groups = array(
                $ids
            );
        }
        
        $report = Doctrine_Query::create()->select('*')
            ->from('ReportscustomGroups')
            ->where('isdelete = 0')
            ->andWhereIn('id', $groups);
        $reportarray = $report->fetchArray();
        
        if ($reportarray) {
            
            foreach ($reportarray as $k => $val) {
                $group_details[$val['id']] = $val;
            }
            return $group_details;
        } else {
            return false;
        }
    }
}

?>