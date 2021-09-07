<?php
Doctrine_Manager::getInstance()->bindComponent('ReportscustomGroups2Report', 'MDAT');

class ReportscustomGroups2Report extends BaseReportscustomGroups2Report
{

    public function get_report_groups($report_id)
    {
        $report = Doctrine_Query::create()->select('*')
            ->from('ReportscustomGroups2Report')
            ->where('report_id = ' . $report_id)
            ->andWhere('isdelete = 0');
        $reportarray = $report->fetchArray();
        
        if ($reportarray) {
            foreach ($reportarray as $k => $val) {
                $groups2report[$val['report_id']][] = $val['group_id'];
            }
            
            return $groups2report;
        } else {
            return false;
        }
    }
}

?>