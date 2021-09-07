<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('PatientCaseStatusLog', 'IDAT');

class PatientCaseStatusLog extends BasePatientCaseStatusLog
{

    public function getPatientCaseLogs($ipid, $case_id, $log_type){
        $logsQ = Doctrine_Query::create()
            ->select('*')
            ->from('PatientCaseStatusLog a')
            ->where('a.ipid=?', $ipid)
            ->andwhere('a.case_id=?',$case_id)
            ->andwhere('a.log_type=?',$log_type)
            ->andWhere("a.isdelete=false");

        $logs = $logsQ->fetchArray();
        return $logs;

    }

}