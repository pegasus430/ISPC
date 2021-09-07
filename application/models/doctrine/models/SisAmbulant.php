<?php
Doctrine_Manager::getInstance()->bindComponent('SisAmbulant', 'MDAT');

class SisAmbulant extends BaseSisAmbulant
{

    public function get_patient_sis_ambulant($ipid,$clientid,$from_id = false)
    {
        $actions_sql = Doctrine_Query::create()
        ->select('*')
        ->from('SisAmbulant')
        ->where("ipid= ?", $ipid)
        ->andWhere("clientid= ?", $clientid);

        $actions_sql->andWhere('isdelete = 0');
        //ISPC-2658, elena, 08.09.2020
        //ISPC: SH_Travebogen_West
        if(!empty($from_id )){
        	$actions_sql->andWhere("id = ?", $from_id);
        }else{
            //exactly last form if exists
            $actions_sql->orderBy('change_date desc');
            $actions_sql->limit(1);

        }

        $actionsarray = $actions_sql->fetchArray();
        
        if($actionsarray)
        {
            return $actionsarray;
        }
    }
}

?>