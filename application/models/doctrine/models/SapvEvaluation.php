<?php
Doctrine_Manager::getInstance()->bindComponent('SapvEvaluation', 'MDAT');

class SapvEvaluation extends BaseSapvEvaluation
{
    function get_sapv_evaluation($ipid,$admissionid = false)
    {
        $sp = Doctrine_Query::create()
        ->select('*')
        ->from('SapvEvaluation')
        ->where("ipid ='" . $ipid . "'");
        
        if($admissionid){
            $sp->andWhere("admissionid ='" . $admissionid. "'");
        }
        
        $sp->andWhere('isdelete = 0');
        $sp->orderBy('id ASC');//TODO-4181 Ancuta 09.06.2021
        //echo $sp->getSqlQuery(); exit;
        $sparr = $sp->fetchArray();

        return $sparr;
    }
    
}

?>