<?php
Doctrine_Manager::getInstance()->bindComponent('UserOffline', 'SYSDAT');

class UserOffline extends BaseUserOffline
{

    /**
     * @deprecated
     */
    public function get_offline_user($userid, $clientid)
    {
        $usr_res = Doctrine_Query::create()
        ->select('*')
        ->from('UserOffline')
        ->where('userid = ?', $userid)
        ->andWhere('clientid = ?', $clientid)
        ->fetchArray();
        
        if ($usr_res) {
            return $usr_res;
        } else {
            return false;
        }
    }
    
    // userid is the `userid` field from User not the UserOffline field `id`
    /**
     * @deprecated
     */
    public function check_username_taken($username = false, $userid = false)
    {
        if ($username && $userid) {
            $usr_res = Doctrine_Query::create()
            ->select('*')
            ->from('UserOffline')
            ->where('username = ?', $username)
            ->andWhere('userid != ?', $userid) // exclude false positives for same account
            ->fetchArray();
            
            if (empty($usr_res)) {
                // no user exists with same inserted username
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    
    public static function fetch_user($userid = 0, $clientid = 0)
    {
        return Doctrine_Query::create()
        ->select('*')
        ->from('UserOffline')
        ->where('userid = ?', $userid)
        ->andWhere('clientid = ?', $clientid)
        ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
    }
    
    /**
     * do your own lowercase, trim($username) ... this is just assert
     * 
     * username must be unique for all users.. 
     * I update by userid, NOT primaryKey
     * 
     * @param string $username
     * @return boolean
     */
    public function assertUsernameUnique($username = '', $userid = 0)
    {
        if (empty($username)) {
            return false;
        }

        $usr_res = Doctrine_Query::create()
            ->select('id')
            ->from('UserOffline')
            ->where('username = ?', $username)
        ;
        
        if ( ! empty($username)) {
            $usr_res->andWhere('userid != ?' , $userid);
        }
        
        $usr_arr = $usr_res->fetchArray();
        
        return (empty($usr_arr) ? true : false);
    }
    
}

?>