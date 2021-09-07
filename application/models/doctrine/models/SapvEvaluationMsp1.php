<?php
Doctrine_Manager::getInstance()->bindComponent('SapvEvaluationMsp1', 'MDAT');

class SapvEvaluationMsp1 extends BaseSapvEvaluationMsp1
{
    function get_sapv_evaluation_msp1($form_id,$ipid)
    {
        $sp = Doctrine_Query::create()
        ->select("*,IF(change_date != '0000-00-00 00:00:00', change_date , create_date) as last_change_date")//TODO-4181 Ancuta 09.06.2021
        ->from('SapvEvaluationMsp1')
        ->where("ipid ='" . $ipid . "'")
        ->andWhere("form_id ='" . $form_id . "'")
        ->andWhere('isdelete = 0')
        ->orderBy('last_change_date DESC')//TODO-4181 Ancuta 09.06.2021
        ->limit(1);//TODO-4181 Ancuta 09.06.2021
        $sparr = $sp->fetchArray();
    
        return $sparr;
    }
    
}

?>