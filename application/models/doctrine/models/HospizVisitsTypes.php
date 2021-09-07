<?php
Doctrine_Manager::getInstance()->bindComponent('HospizVisitsTypes', 'MDAT');

class HospizVisitsTypes extends BaseHospizVisitsTypes
{

    public function get_client_hospiz_visits_types($clientid = false,$id=false,$only_billable=false)
    {
        // get new grund ids
        $type_q = Doctrine_Query::create()->select("*")
            ->from('HospizVisitsTypes')
            ->where('isdelete=0');
        if ($clientid) {
            $type_q->andWhere("clientid = ?", $clientid);
        }
        if ($id) {
            $type_q->andWhere("id = ?", $id);
        }
        
        if ($only_billable) {
            $type_q->andWhere("billable = 1");
        }

        $type_array = $type_q->fetchArray();
        
        if ($type_array) {
            
            foreach ($type_array as $k => $tar) {
                $grund_type[$tar['id']] = $tar;
            }
            
            return $grund_type;
        } else {
            return false;
        }
    }
}

?>