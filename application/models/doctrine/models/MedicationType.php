<?php
Doctrine_Manager::getInstance()->bindComponent('MedicationType', 'SYSDAT');

class MedicationType extends BaseMedicationType
{

    public function client_medication_types($client, $allow_extra = false)
    {
        if (empty($client)) {
            return false;
        }
        
        // ISPC-2612 Ancuta 01.07.2020
        $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('MedicationType', $client);
        // --
        
        $query = Doctrine_Query::create()->select('*')
            ->from('MedicationType')
            ->where('clientid =  ?', $client)
            ->andWhere('isdelete = 0');
            if($client_is_follower){
                $query->andWhere('connection_id is NOT null');
                $query->andWhere('master_id is NOT null');
            }
        if (! $allow_extra) { // ISPC-2247 
            $query->andWhere('extra = 0');
        }
        $q_res = $query->fetchArray();
        
        if ($q_res) {
            return $q_res;
        } else {
            return false;
        }
    }
}

?>