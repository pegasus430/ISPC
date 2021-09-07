<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 
class Net_Ldap{

    public function __construct(){

        $ldap_conf=Zend_Registry::get('ldap');

        $this->loginmode=false;
        if($ldap_conf){

            $this->connect_server   = $ldap_conf['connect']['server'];
            $this->connect_user     = $ldap_conf['connect']['user'];
            $this->connect_pass     = $ldap_conf['connect']['pass'];
            $this->connect_tree     = $ldap_conf['connect']['tree'];

            $this->login_by         = $ldap_conf['login']['by'];
            $this->login_tree       = $ldap_conf['login']['tree'];
            $this->login_usercolumn = $ldap_conf['login']['usercolumn'];

            $this->search_tree      = $ldap_conf['search']['tree'];
            $this->search_by        = $ldap_conf['search']['by'];

            if(isset($ldap_conf['search']['by2'])) {
                $this->search_by2 = $ldap_conf['search']['by2'];
            }

            $this->search_label        = $ldap_conf['search']['label'];

            $this->search_accountname  = $ldap_conf['search']['accountname'];

            $this->loginmode=$ldap_conf['login']['enable'];
            $this->searchmode=$ldap_conf['search']['enable'];

            $this->debug= $ldap_conf['debug'];

            $this->login_override_users=array();
            if($ldap_conf['login']['override_users'] && count($ldap_conf['login']['override_users'])>0) {
                foreach ($ldap_conf['login']['override_users'] as $user) {
                    $this->override_users[] = $user;
                }
            }
        }
    }

    private function errlog($msg){
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../public/log/ldap.log');
        $log = new Zend_Log($writer);
        if ($log){
            $log->crit("LDAP: ".$msg);
        }
    }

    public function get_ldap_searchmode(){
        return $this->searchmode;
    }

    /**
     * Check if this installation provides login with ldap.
     * Users configured in override_users can login without ldap
     * @param $username
     * @return bool
     */
    public function need_ldap_login($username){
        if($this->loginmode){
            if(strlen($username)>0 && strlen($username<100)){
                if(!(is_array($this->override_users) && in_array($username, $this->override_users) )) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns true if password matches ldap-password
     *
     * @param $username
     * @param $password
     * @return bool
     */
    public function do_ldap_login($username, $password)
    {

        if($this->login_usercolumn!=="username"){
            $user = Doctrine::getTable('User')->findOneBy('username',$username);
            $username=$user->ldapid;
        }

        if ($this->loginmode == 1) {
            if ($this->connect()) {
                //get dn
                $result = ldap_search($this->ldap_connection, $this->search_tree, "(".$this->login_by."=" . $username . ")");
                if (!$result) {
                    $this->errlog("Error in search query: " . ldap_error($this->ldap_connection));
                    $dn = '';
                } else {
                    $first = ldap_first_entry($this->ldap_connection, $result);
                    $dn = ldap_get_dn($this->ldap_connection, $first);
                }
                ldap_close($this->ldap_connection);

                if (!is_string($dn) || strlen($dn) < 3) {
                    return false;
                }

                $ldapconn = ldap_connect($this->connect_server);
                if ($ldapconn) {
                    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
                    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, FALSE);
                    $ldapbind = ldap_bind($ldapconn, $dn, $password);
                    if ($ldapbind) {
                        ldap_close($ldapconn);
                        return true;
                    }
                    ldap_close($ldapconn);
                }
            }
        }
        return false;
    }

    /**
     * Returns array with some userinfo from ldap
     * @param $search
     * @param $more unused
     * @return array
     */
    public function get_userinfo($search, $more=1){
        if($this->get_ldap_searchmode()==1){
            if($this->connect()){
                $return=array();

                $appendix="*";
                $search1=$search;
                $search2="";
                if(is_array($search)){
                    $search1=$search[0];
                    $search2=$search[1];
                }

                if(!$this->debug) {
                    $q1="(" . $this->search_by . "=" . $search1 . $appendix . ")";
                    if(strlen($search2)) {
                        $q2="(" . $this->search_by2 . "=" . $search2 . $appendix . ")";
                        $qj="(&" . $q1 . $q2 . ")";
                        $result = ldap_search($this->ldap_connection, $this->search_tree, $qj);
                    }else{
                        $result = ldap_search($this->ldap_connection, $this->search_tree, "(" . $this->search_by . "=" . $search1 . $appendix . ")");
                    }
                    if (!$result) {
                        $this->errlog("Error in search query: " . ldap_error($this->ldap_connection));
                        $ldap_return = array(array());
                    } else {
                        $ldap_return = ldap_get_entries($this->ldap_connection, $result);
                    }

                    ldap_close($this->ldap_connection);
                }else {
                    //debug dummy data
                    $ldap_return = array(
                        array(
                            'samaccountname' => 'testuser',
                            'sn' => 'Nachname',
                            'uid'=>'x1',
                            'givenname' => 'Firstname',
                            'title' => 'Testdaten'

                        ),
                        array(
                            'samaccountname' => 'testuser2',
                            'sn' => 'Nachname',
                            'uid'=>'x2',
                            'givenname' => 'Firstname',
                            'title' => 'Testdaten'

                        ),
                    );
                }

                if(is_array($ldap_return)){
                    $returns=array();
                    $max=100;
                    foreach($ldap_return as $user){
                        $return['search_by']=$user[$this->search_by][0];
                        $return['login_by']=$user[$this->login_by][0];
                        $return['accountname']=$user[$this->search_accountname][0];
                        $return['last_name']=$user['sn'][0];
                        $return['first_name']=$user['givenname'][0];
                        $return['displayname']=$user['displayname'][0];
                        $return['mail']=$user['mail'][0];
                        $return['title']=$user['title'][0];
                        if($return['search_by']!==null) {
                            $returns[] = $return;
                        }
                        if($max-- <= 0){
                            continue;
                        }
                    }

                    return ($returns);
                }

            }
            return array();
        }
        return array();
    }

    private function connect(){
        if($this->debug){
            return true;
        }
        if(!function_exists('ldap_connect')){
            $this->errlog('No LDAP-Support installed. Try sudo apt-get install php7.2-ldap, then reload apache.');
        }

        $ldapconn =ldap_connect($this->connect_server);
        if($ldapconn) {
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, FALSE);
            $ldapbind = ldap_bind($ldapconn, $this->connect_user, $this->connect_pass);
            if($ldapbind){
                $this->ldap_connection = $ldapconn;
                return true;
            }else{
                $this->errlog("Error trying to bind: ".ldap_error($ldapconn));
            }
        }else{
            $this->errlog("Could not connect to LDAP server.");
        }
        return false;
    }

}