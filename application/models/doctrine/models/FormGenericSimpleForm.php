<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('FormGenericSimpleForm', 'MDAT');

class FormGenericSimpleForm extends BaseFormGenericSimpleForm{

    const DISCHARGEPLANNING_CLINIC = 'DischargePlanningClinic';


    public function get_list_patient_dischargeplanning($ipid, $onlylastPlanning = false)
    {

        $sql = Doctrine_Query::create()
            ->select('*')
            ->from('FormGenericSimpleForm')
            ->where('ipid=?', $ipid)
            ->andWhere('formname=?', self::DISCHARGEPLANNING_CLINIC)
            ->andWhere('isdelete=0');

        $discharge_planning = $sql->fetchArray();

        if(count($discharge_planning)== 0)
            return false;

        $erg = array();
        $date = date('01.01.1970');
        $index = -1;

        foreach ($discharge_planning as $key => $plan){
            $vals = $plan['json1'];
            $erg_dec = json_decode($vals,1);
            $erg[] = $erg_dec;
            $discharge_date = $erg_dec['entlassplanung']['date'];
            if(isset($discharge_date) && $discharge_date > $date){
                $date = $discharge_date;
                $index = $key;
            }

        }

        if(!$onlylastPlanning || count($erg) == 1)
            return $erg;

        if($index >=0){
            $new_erg = array();
            $new_erg[] = $erg[$index];
            return $new_erg;
        }


        return false;

    }


}
?>