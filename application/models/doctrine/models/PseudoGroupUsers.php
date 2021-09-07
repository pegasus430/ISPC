<?php
Doctrine_Manager::getInstance()->bindComponent('PseudoGroupUsers', 'SYSDAT');

class PseudoGroupUsers extends BasePseudoGroupUsers {

	public function get_users($id)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	        
		$drop = Doctrine_Query::create()
		->select('*')
		->from('PseudoGroupUsers')
		->where("pseudo_id='" .$id. "'")
		->andwhere("isdelete=0")
		->andWhere("clientid='" . $clientid . "'");
		$droparray = $drop->fetchArray();

		if($droparray)
		{
			return $droparray;
		}
		else
		{
			return false;
		}
	}

	public function get_usersgroup()
	{
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
		$drop = Doctrine_Query::create()
		->select('*')
		->from('PseudoGroupUsers')
		->where("isdelete=0")
		->andWhere("clientid=?", $clientid);
		$droparray = $drop->fetchArray();
	
		if($droparray)
		{
			return $droparray;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 
	 * Jul 5, 2017 @claudiu 
	 * 
	 * @param array $groups
	 * @return void|multitype:
	 */
	public function get_users_by_groups( $groups =  array() )
	{
		if (empty($groups) || ! is_array($groups)) {
			return;
		}
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		 
		$result = array();
		
		$q = $this->getTable()->createQuery()
		->select('*')
		->whereIn("pseudo_id" , $groups)
		->andwhere("isdelete = 0")
		->andWhere("clientid = ? ", $logininfo->clientid)
		->fetchArray();
	

		foreach($q as $row) {
			$result[$row['pseudo_id']][$row['id']] = $row;
			$result['all_user_id'][] = $row['user_id'];
			
		}
		
		return $result;
		
	}
	
	
	/**
	 * @author claudiu on 08.02.2018 
	 * used to list user's pseudogroup in his profile
	 * user can belong to one pseudogroup at a time (idealy, because it's done in js)
	 * 
	 * @param number $user_id
	 * @return array
	 */
	public static function get_user_pseudogroup( $user_id = 0 )
	{
	    if (empty($user_id)) {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $user_id = $logininfo->userid;
	    }
	    
	    $r = Doctrine_Query::create()->select('pgu.*, upg.*')
	    ->from('PseudoGroupUsers pgu')
	    ->leftJoin('pgu.UserPseudoGroup upg')
	    ->where("user_id = ? ", $user_id)
	    ->andWhere("isdelete = 0")
	    ->orderBy('id DESC') //this is for 'idealy'
	    ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
	    
	    return $r;
	    
	}
	
	public static function get_users_pseudogroup( $userids )
	{
		$r = array();
		if(empty($userids))
		{
			return $r;
		}
		
		if(!is_array($userids))
		{
			$usrids[] = $userids;
		}
		else 
		{
			$usrids = $userids;
		}
		
		$r = Doctrine_Query::create()->select('pgu.*, upg.*')
		->from('PseudoGroupUsers pgu')
		->leftJoin('pgu.UserPseudoGroup upg')
		->whereIn("pgu.user_id", $usrids)
		->andWhere("pgu.isdelete = 0")
		->fetchArray();
		//var_dump($r); exit;
		return $r;
		 
	}
	
	

}

?>