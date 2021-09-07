<?php
Doctrine_Manager::getInstance()->bindComponent('MemberStatuses', 'SYSDAT');

class MemberStatuses extends BaseMemberStatuses
{

    public function get_client_member_statuses($clientid = false,$id=false)
    {
        // get new grund ids
        $type_q = Doctrine_Query::create()->select("*")
            ->from('MemberStatuses')
            ->where('isdelete=0');
        if ($clientid) {
            $type_q->andWhere("clientid = " . $clientid);
        }
        if ($id) {
            $type_q->andWhere("id = " . $id);
        }

        $type_array = $type_q->fetchArray();
        
        if ($type_array) {
            
            foreach ($type_array as $k => $tar) {
                $status_array[$tar['id']] = $tar;
            }
            
            return $status_array;
        } else {
            return false;
        }
    }
}

?>