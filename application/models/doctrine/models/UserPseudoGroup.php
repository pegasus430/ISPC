<?php
Doctrine_Manager::getInstance()->bindComponent('UserPseudoGroup', 'SYSDAT');

class UserPseudoGroup extends BaseUserPseudoGroup
{

    public function get_service($id)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        
        $drop = Doctrine_Query::create()->select('*')
            ->from('UserPseudoGroup')
            ->where("id = ?", $id)
            ->andwhere("isdelete = 0")
            ->andWhere("clientid = ?", $clientid);
        $droparray = $drop->fetchArray();
        
        if ($droparray) {
            return $droparray;
        } else {
            return false;
        }
    }

    public function get_userpseudo($clientid = 0)
    {
        if (empty($clientid)) {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
        }
        
        $drop = Doctrine_Query::create()->select('*')
            ->from('UserPseudoGroup')
            ->Where("clientid = ?", $clientid)
            ->andWhere("isdelete = 0")
            ->orderBy("servicesname ASC");
        $droparray = $drop->fetchArray();
        
        if ($droparray) {
            return $droparray;
        } else {
            return false;
        }
    }

    public function get_userpseudo_with_make_visits($clientid = 0)
    {
        if (empty($clientid)) {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
        }
        
        $drop = Doctrine_Query::create()->select('id, clientid, servicesname, phone, mobile, fax, email, makes_visits')
            ->from('UserPseudoGroup')
            ->where("isdelete = ?", 0)
            ->andWhere("makes_visits != '0'")
            ->andWhere("clientid = ?", $clientid)
            ->orderBy("servicesname ASC");
        $droparray = $drop->fetchArray();
        
        if ($droparray) {
            $ret_arr = array();
            foreach ($droparray as $arr) {
                $ret_arr[$arr['id']] = $arr;
            }
            return $ret_arr;
        } else {
            return false;
        }
    }

    public function get_pseudogroups_for_todo($clientid = 0)
    {
        if (empty($clientid)) {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
        }
        /*
         * at this moment, only makes_visits=tours should be excluded,
         * since they have no users, and they are designed to be used just in the tourenplanung
         */
        
        return $this->getTable()
            ->createQuery('upg indexBy id')
            ->select('*')
            ->Where("clientid = ?", $clientid)
            ->andWhere("makes_visits != 'tours'")
            ->andWhere("isdelete=0")
            ->orderBy("servicesname ASC")
            ->fetchArray();
    }
}

?>