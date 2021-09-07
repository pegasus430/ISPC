<?php
Doctrine_Manager::getInstance()->bindComponent('SapvEvaluationIpos1', 'MDAT');

class SapvEvaluationIpos1 extends BaseSapvEvaluationIpos1
{
    function get_sapv_evaluation_ipos1($form_id,$ipid)
    {
        $sp = Doctrine_Query::create()
        ->select('*')
        ->from('SapvEvaluationIpos1')
        ->where("ipid ='" . $ipid . "'")
        ->andWhere("form_id ='" . $form_id . "'")
        ->andWhere('isdelete = 0');
        $sparr = $sp->fetchArray();
    
        return $sparr;
    }
}

?>