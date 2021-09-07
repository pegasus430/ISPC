<?php
Doctrine_Manager::getInstance()->bindComponent('SapvEvaluationIpos2', 'MDAT');

class SapvEvaluationIpos2 extends BaseSapvEvaluationIpos2
{
    function get_sapv_evaluation_ipos2($form_id,$ipid)
    {
        $sp = Doctrine_Query::create()
        ->select('*')
        ->from('SapvEvaluationIpos2')
        ->where("ipid ='" . $ipid . "'")
        ->andWhere("form_id ='" . $form_id . "'")
        ->andWhere('isdelete = 0');
        $sparr = $sp->fetchArray();
    
        return $sparr;
    }
}

?>