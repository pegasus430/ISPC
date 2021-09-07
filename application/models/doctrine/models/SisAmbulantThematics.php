<?php
Doctrine_Manager::getInstance()->bindComponent('SisAmbulantThematics', 'MDAT');

class SisAmbulantThematics extends BaseSisAmbulantThematics
{
    public function get_patient_sis_ambulant_thematics($ipid,$clientid,$form_id)
    {
        $actions_sql = Doctrine_Query::create()
        ->select('*')
        ->from('SisAmbulantThematics')
        ->where("ipid= '" . $ipid . "' ")
        ->andWhere("clientid='" . $clientid . "' ")
        ->andWhere("form_id = '" . $form_id . "' ")
        ->andWhere('isdelete = 0');
        $actionsarray = $actions_sql->fetchArray();
    
        if($actionsarray)
        {
            return $actionsarray;
        }
    }
}

?>