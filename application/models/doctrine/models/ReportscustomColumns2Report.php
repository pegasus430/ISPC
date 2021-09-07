<?php
Doctrine_Manager::getInstance()->bindComponent('ReportscustomColumns2Report', 'MDAT');

class ReportscustomColumns2Report extends BaseReportscustomColumns2Report
{

    public function get_report_columns($report_id,$ordered = false)
    {
        $report = Doctrine_Query::create()->select('*')
            ->from('ReportscustomColumns2Report')
            ->where('report_id = ' . $report_id)
            ->andWhere('isdelete = 0 ');
        if($ordered){
            $report->orderBy("order_number ASC");
        }
        $reportarray = $report->fetchArray();
        
        if ($reportarray) {
            foreach ($reportarray as $k => $val) {
                $columns2report[$val['report_id']][] = $val;
            }
            
            return $columns2report;
        } else {
            return false;
        }
    }

    
    public function get_report_columns_details($report_id)
    {
        $report = Doctrine_Query::create()
            ->select('cr.*,rcc.*')
            ->from('ReportscustomColumns2Report cr')
            ->where('report_id = ' . $report_id)
            ->andWhere('isdelete = 0 ')
            ->leftJoin('cr.ReportscustomColumns rcc');
        $reportarray = $report->fetchArray();
        
        if ($reportarray) {
            foreach ($reportarray as $k => $val) {
                $columns2report[$val['report_id']][] = $val;
            }
            
            return $columns2report;
        } else {
            return false;
        }
    }

}

?>