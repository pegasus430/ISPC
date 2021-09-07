<?php
	class Pms_CommonData {

		public static function getCountries()
		{
			$cust = Doctrine_Query::create()
				->select('*')
				->from('Countries')
				->orderBy('country_name ASC');

			$cntr = $cust->execute();
			$countryarray = $cntr->toArray();
			$countries = array("" => "Select Country");

			foreach($countryarray as $country)
			{
				$countries[$country[country_id]] = $country[country_name];
			}
			return $countries;
		}

		public static function getRegions()
		{

			$cust = Doctrine_Query::create()
			->select('id, dn')
			->from('KbvKeytabs')
			->where("kbv_oid='1.2.276.0.76.3.1.1.5.4.1'")
			->orderBy('sn ASC');
			
			$cntr = $cust->execute();
			$countryarray = $cntr->toArray();
			
			$countries = array("" => "Select Region");
			
			foreach($countryarray as $country)
			{
				$countries[$country[id]] = $country[dn];
			}
			return $countries;
			/* ZF1
			$cust = Doctrine_Query::create()
				->select('*')
				->from('KbvKeytabs')
				->where("kbv_oid='1.2.276.0.76.3.1.1.5.4.1'")
				->orderBy('sn ASC');

			$cntr = $cust->execute();
			$countryarray = $cntr->toArray();

			$countries = array("" => "Select Region");

			foreach($countryarray as $country)
			{
				$countries[$country[id]] = $country[dn];
			}
			return $countries;
			*/
		}

		public static function getDistrict()
		{
			$cust = Doctrine_Query::create()
			->select('id, dn')
			->from('KbvKeytabs')
			->where("sn='S_KBV_BEZIRKSSTELLE'")
			->orderBy('dn ASC');
			
			$cntr = $cust->execute();
			$countryarray = $cntr->toArray();
			
			$district = array("" => "Select District");
			
			foreach($countryarray as $country)
			{
				$district[$country[id]] = $country[dn];
			}
			return $district;
			/* ZF1
			$cust = Doctrine_Query::create()
				->select('*')
				->from('KbvKeytabs')
				->where("sn='S_KBV_BEZIRKSSTELLE'")
				->orderBy('dn ASC');

			$cntr = $cust->execute();
			$countryarray = $cntr->toArray();

			$district = array("" => "Select District");

			foreach($countryarray as $country)
			{
				$district[$country[id]] = $country[dn];
			}
			return $district;
			*/
		}

		public static function getMonths($backward = 1, $forward = 1)
		{
			$Tr = new Zend_View_Helper_Translate();
			$montharray = array();
			$curyear = date("Y");
			for($i = $curyear - $backward; $i <= $curyear + $forward; $i++)
			{
				$montharray[$i] = array(
					$i . "_01" => $Tr->translate('January'),
					$i . "_02" => $Tr->translate('February'),
					$i . "_03" => $Tr->translate('March'),
					$i . "_04" => $Tr->translate('April'),
					$i . "_05" => $Tr->translate('May'),
					$i . "_06" => $Tr->translate('June'),
					$i . "_07" => $Tr->translate('July'),
					$i . "_08" => $Tr->translate('August'),
					$i . "_09" => $Tr->translate('September'),
					$i . "_10" => $Tr->translate('Octomber'),
					$i . "_11" => $Tr->translate('November'),
					$i . "_12" => $Tr->translate('December')
				);
			}

			return $montharray;
		}

		public static function getGender()
		{
			$Tr = new Zend_View_Helper_Translate();
			$genderarray = array();
			$selectgender = $Tr->translate('gender_select');
			$divers = $Tr->translate('divers');
			$male = $Tr->translate('male');
			$female = $Tr->translate('female');

			$genderarray = array("" => $selectgender, "0" => $divers, "1" => $male, "2" => $female);
			return $genderarray;
		}

		/*
		 * //ISPC-2442 @Lore 09.10.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
		 *  field "gender" in table "member"  is int(1)
		 */
		public static function getGenderMember()
		{
		    $Tr = new Zend_View_Helper_Translate();
		    $genderarray = array();
		    $selectgender = $Tr->translate('gender_select');
		    $divers = $Tr->translate('divers');
		    $male = $Tr->translate('male');
		    $female = $Tr->translate('female');
		    
		    $genderarray = array("0" => $selectgender, "3" => $divers, "1" => $male, "2" => $female);
		    return $genderarray;
		}
		
		public static function getSalutation()
		{
			$Tr = new Zend_View_Helper_Translate();
			$blank = $Tr->translate('select_salutation');
			$mr = $Tr->translate('mr');
			$mrs = $Tr->translate('mrs');
			$miss = $Tr->translate('miss');
			$titlearray = array();
			$titlearray = array('' => $blank, $mr => $mr, $mrs => $mrs, $miss => $miss);
			return $titlearray;
		}

		public static function getHours()
		{
			$hrs = array();
			for($i = 0; $i < 24; $i++)
			{
				if($i < 10)
				{
					$app = "0";
				}
				else
				{
					$app = "";
				}
				$hrs[$app . $i] = $app . $i;
			}
			return $hrs;
		}

		public static function getMinutes()
		{
			$minutes = array();
			$app = "0";
			for($i = 0; $i < 60; $i++)
			{
				if($i >= 10)
				{
					$app = "";
				}
				$minutes[$app . $i] = $app . $i;
			}

			return $minutes;
		}

		public static function getIpid($pids)
		{
			if(empty($pids)){
				return;
			}
			
			$pt = Doctrine_Query::create()
				->select('id, ipid')
				->from('PatientMaster')
				->where('id = ?', $pids)
				->fetchArray();
			
			if ( !empty($pt[0]['ipid']) ) {
				return $pt[0]['ipid'];
			}
			
			/* ZF1
			$pt = Doctrine::getTable('PatientMaster')->find($pids);

			if($pt)
			{
				$ptarray = $pt->toArray();

				$ipid = $ptarray['ipid'];
				return $ipid;
			}
			*/
		}

		//get Epid From Ipid
		public static function getEpid($ipds)
		{
			$pt = Doctrine_Query::create()
				->select('id, epid')
				->from('EpidIpidMapping')
				->where('ipid = ?', $ipds)
				->fetchArray();
			
			if ( !empty($pt[0]['epid']) ) {
				return $pt[0]['epid'];
			}
			/* ZF1
			$pt = Doctrine::getTable('EpidIpidMapping')->findBy("ipid", $ipds);
			if($pt)
			{
				$ptarray = $pt->toArray();
				$epid = $ptarray[0]['epid'];
				return($epid);
			}
			*/
		}

		public static function getEpidcharsandNum($ipds)
		{
			$pt = Doctrine_Query::create()
			->select('id, ipid, epid, epid_num, epid_chars')
			->from('EpidIpidMapping')
			->where('ipid = ?', $ipds)
			->fetchArray();
				
			if ( !empty($pt[0]['epid']) ) {
				$epid['num'] = $pt[0]['epid_num'];
				$epid['char'] = $pt[0]['epid_chars'];
				$epid['epid'] = $pt[0]['epid'];
				$epid['ipid'] = $pt[0]['ipid'];
				return($epid);
			}
			/* ZF1
			$pt = Doctrine::getTable('EpidIpidMapping')->findBy("ipid", $ipds);
			$epid = array();
			if($pt)
			{
				$ptarray = $pt->toArray();
				$epid['num'] = $ptarray[0]['epid_num'];
				$epid['char'] = $ptarray[0]['epid_chars'];
				return($epid);
			}
			*/
		}

		//get epid from patient_id
		public static function getEpidFromId($id)
		{
			$pt = Doctrine_Query::create()
			->select('e.epid')
			->from('EpidIpidMapping e')
			->leftJoin('e.PatientMaster p')
			->where('p.id = ?', intval($id))
			->fetchArray();
			
			if ( !empty($pt[0]['epid']) ) {
				return $pt[0]['epid'];
			}

			/* ZF1
			$ipds = Pms_CommonData::getIpid($id);

			$pt = Doctrine::getTable('EpidIpidMapping')->findBy("ipid", $ipds);

			if($pt)
			{
				$ptarray = $pt->toArray();
				$epid = $ptarray[0]['epid'];
				return($epid);
			}
			*/
		}

		//getUserData(user_id) returns only NOT-isdeleted user(*)
		public static function getUserData($uid)
		{
			$pt = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where("id = ?", $uid)
				->andWhere("isdelete=0");
			$ptexec = $pt->execute();
			if($ptexec)
			{
				$usarray = $ptexec->toArray();

				return($usarray);
			}
		}

		//getUserDataById(user_id) returns user(*) - even if isdeleted
		public static function getUserDataById($uid)
		{
			$pt = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where("id = ?", $uid );
			$ptexec = $pt->execute();
			if($ptexec)
			{
				$usarray = $ptexec->toArray();

				return($usarray);
			}
		}

		public static function getClientData($cid)
		{

			$pt = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1
					,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2
					,AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode
					,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city
					,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname
					,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
					,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid
					,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone
					,AES_DECRYPT(institutskennzeichen,'" . Zend_Registry::get('salt') . "') as institutskennzeichen
					,AES_DECRYPT(betriebsstattennummer,'" . Zend_Registry::get('salt') . "') as betriebsstattennummer
					,AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax
					,AES_DECRYPT(lbg_sapv_provider,'" . Zend_Registry::get('salt') . "') as lbg_sapv_provider
					,AES_DECRYPT(lbg_postcode,'" . Zend_Registry::get('salt') . "') as lbg_postcode
					,AES_DECRYPT(lbg_city,'" . Zend_Registry::get('salt') . "') as lbg_city
					,AES_DECRYPT(lbg_street,'" . Zend_Registry::get('salt') . "') as lbg_street
					,AES_DECRYPT(lbg_institutskennzeichen,'" . Zend_Registry::get('salt') . "') as lbg_institutskennzeichen")
				->from('Client')
				->where("id = ?" , $cid);
			$usarray = $pt->fetchArray();
			if($usarray)
			{
				return($usarray);
			}
		}
		
		public static function getClientDataFp($cid)
		{

			$pt = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1
					,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2
					,AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode
					,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city
					,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname
					,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
					,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid
					,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone
					,AES_DECRYPT(institutskennzeichen,'" . Zend_Registry::get('salt') . "') as institutskennzeichen
					,AES_DECRYPT(betriebsstattennummer,'" . Zend_Registry::get('salt') . "') as betriebsstattennummer
					,AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax
					,AES_DECRYPT(lbg_sapv_provider,'" . Zend_Registry::get('salt') . "') as lbg_sapv_provider
					,AES_DECRYPT(lbg_postcode,'" . Zend_Registry::get('salt') . "') as lbg_postcode
					,AES_DECRYPT(lbg_city,'" . Zend_Registry::get('salt') . "') as lbg_city
					,AES_DECRYPT(lbg_street,'" . Zend_Registry::get('salt') . "') as lbg_street
					,AES_DECRYPT(lbg_institutskennzeichen,'" . Zend_Registry::get('salt') . "') as lbg_institutskennzeichen_dec
					,AES_DECRYPT(fileupoadpass,'" . Zend_Registry::get('salt') . "') as fileupoadpass
				    
				    ")
				->from('Client')
				->where("id = ? " , $cid);
			$usarray = $pt->fetchArray();
			if($usarray)
			{
				return($usarray);
			}
		}

		public static function getAllClientsDD()
		{

			$pt = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
		   	,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1
			,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2
			,AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode
			,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city
			,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname
			,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
			,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid
			,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
				->from('Client')
				->where('isdelete=0')
			    ->orderBy("h__0 ASC");
			$ptarray = $pt->fetchArray();

			$clarr = array(0 => 'Select');

			if($ptarray)
			{
				foreach($ptarray as $key => $val)
				{
					$clarr[$val['id']] = $val['client_name'];
				}
			}

			return $clarr;
		}

		public static function getFormId($formname)
		{
			$pt = Doctrine::getTable('TriggerForms')->findBy("formname", $formname);

			if($pt)
			{
				$usarray = $pt->toArray();

				return $usarray[0]['id'];
			}
		}

		public static function getIpidFromEpid($epids)
		{
			$pt = Doctrine_Query::create()
			->select('id, ipid')
			->from('EpidIpidMapping')
			->where('epid = ?', $epids)
			->fetchArray();
				
			if ( !empty($pt[0]['ipid']) ) {
				return $pt[0]['ipid'];
			}
			/* ZF1
			$pt = Doctrine::getTable('EpidIpidMapping')->findBy("epid", $epids);

			if($pt)
			{
				$ptarray = $pt->toArray();
				$ipid = $ptarray[0]['ipid'];
				return $ipid;
			}
			*/
		}

		public static function array_addslashes($array)
		{
			if(!is_array($array))
			{
				return array();
			}

			foreach($array as $key => $val)
			{
				if(is_array($val))
				{
					$array[$key] = Pms_CommonData::array_addslashes($val);
				}
				else
				{
					$array[$key] = addslashes($val);
				}
			}

			return $array;
		}

		public function array_add($a1, $a2)
		{
			$aRes = $a1;
			foreach(array_slice(func_get_args(), 1) as $aRay)
			{
				foreach(array_intersect_key($aRay, $aRes) as $key => $val)
				{
					$aRes[$key] += $val;
				}
				$aRes += $aRay;
			}
			return $aRes;
		}

		public function array_max_key($array)
		{
			foreach($array as $key => $val)
			{
				if($val == max($array))
					return $key;
			}
		}

		/**
		 * @deprecated by @cla... if you need this fn, you are expert || should learn
		 */
		public static function array_stripslashes($array)
		{
			if(!is_array($array))
			{
				return array();
			}
			foreach($array as $key => $val)
			{
				if(is_array($val))
				{
					$array[$key] = Pms_CommonData::array_stripslashes($val);
				}
				else
				{
					$array[$key] = stripslashes($val);
				}
			}

			return $array;
		}

		public function getGenderById($gid)
		{
			switch($gid)
			{
			    case '0' : $sex = "Divers"; //ISPC-2442 @Lore   30.09.2019
					break;
				case '1' : $sex = "Male";
					break;
				case '2' : $sex = "Female";
					break;
			}
			return $sex;
		}

		public static function aesEncrypt($value)
		{
			$manager = Doctrine_Manager::getInstance();
			$manager->setCurrentConnection('SYSDAT');
			$conn = $manager->getCurrentConnection();

			$query = $conn->prepare("select AES_ENCRYPT( :value ,'" . Zend_Registry::get('salt') . "')");
			$query->bindValue(":value", $value);
			//echo "select AES_ENCRYPT('".$value."','".Zend_Registry::get('salt')."')";
			//exit;

			$query->execute();
			$encrypt = $query->fetchAll();

			return $encrypt[0][0];
		}
		
		/**
		 * encrypt multiple data in one single sql query
		 * does NOT work for multidimensional array !
		 * 
		 * ex: $ecrypted_post = Pms_CommonData::aesEncryptMultiple($post);
		 * $ecrypted_post will have the same keys
		 * 
		 * @param array $array_values
		 * @return array()
		 */
		public static function aesEncryptMultiple( $array_values = array() )
		{
			$result = array();
			
			if (empty($array_values) || ! is_array($array_values)) {
				return $result;
			}
			
			$sql_txt =  array();
			
			$params = array();
			$params['salt'] = Zend_Registry::get('salt');
			
			$array_values_original =  $array_values;
			
			$multi_revert = false;
			if (count($array_values) != count($array_values, COUNT_RECURSIVE)) {
			    $array_values = self::array_flatten($array_values);
			    $multi_revert = true;
			}
			foreach ($array_values as $k => $v ) {
				$myk = preg_replace("/[^\w]+/", "", $k);		
				$sql_txt[] = "AES_ENCRYPT( :plain{$myk} , :salt) as encrypted{$myk}";
				$params[ "plain{$myk}" ] = $v;
			}	

			$manager = Doctrine_Manager::getInstance();
			$manager->setCurrentConnection('SYSDAT');
			$conn = $manager->getCurrentConnection();
			$query = $conn->prepare("SELECT ". implode(", ",$sql_txt) );
			$query->execute($params);		
			$encrypt = $query->fetchAll(Doctrine_core::FETCH_ASSOC);
			
			if ( ! empty($encrypt[0])) {
				
				$encrypted =  $encrypt[0] ;
				
				foreach ($array_values as $k => $v ) {
					$myk = preg_replace("/[^\w]+/", "", $k);
					$result[ $k ] = $encrypted[ "encrypted{$myk}"];
				}
			}
			
			if ($multi_revert) {
			    $result = self::array_unflatten($result);
			}
			
			return $result;
		}
		

		public static function array_flatten($array = array(), $prefix = '') 
		{
		    $result = array();
		    
		    foreach ($array as $key => $value) {    
		        if (is_array($value)) {
		            
		            $result = array_merge($result, self::array_flatten($value, $prefix . $key . '.'));
		            
		        } else {
		            
		            $result = array_merge($result, array($prefix . $key => $value));		            
		        }
		    }
		    
		    return $result;
		}
		
		
		
		public static function array_unflatten( $collection )
		{
		    $collection = (array) $collection;
		    $output = array();
		    foreach ( $collection as $key => $value )
		    {
		        self::array_set( $output, $key, $value );
		        if ( is_array( $value ) && ! strpos( $key, '.' ) )
		        {
		            $nested = self::array_unflatten( $value );
		
		            $output[$key] = $nested;
		        }
		    }
		    return $output;
		}
		
		public static function array_set( &$array, $key, $value )
		{
		    if ( is_null( $key ) )
		        return $array = $value;
		    $keys = explode( '.', $key );
		    while ( count( $keys ) > 1 )
		    {
		        $key = array_shift( $keys );
		        // If the key doesn't exist at this depth, we will just create an empty array
		        // to hold the next value, allowing us to create the arrays to hold final
		        // values at the correct depth. Then we'll keep digging into the array.
		        if ( ! isset( $array[$key] ) || ! is_array( $array[$key] ) )
		        {
		            $array[$key] = array();
		        }
		        $array =& $array[$key];
		    }
		    $array[array_shift( $keys )] = $value;
		    return $array;
		}
		
		

		public static function aesDecrypt($value)
		{
			$manager = Doctrine_Manager::getInstance();
			$manager->setCurrentConnection('SYSDAT');
			$conn = $manager->getCurrentConnection();

			$query = $conn->prepare('select AES_DECRYPT(:value,"' . Zend_Registry::get('salt') . '")');
			$query->bindValue(":value", $value);

			$query->execute();
			$encrypt = $query->fetchAll();

			return stripslashes($encrypt[0][0]);
		}

		public static function aesDecryptMultiple( array $array_values = array() )
		{
			$result = array();
				
			if (empty($array_values) || ! is_array($array_values)) {
				return $result;
			}
				
			$sql_txt =  array();
				
			$params = array();
			$params['salt'] = Zend_Registry::get('salt');
				
			foreach ($array_values as $k => $v ) {
				$myk = preg_replace("/[^\w]+/", "", $k);
				$sql_txt[] = "AES_DECRYPT( :encrypted{$myk} , :salt) as plain{$myk}";
				$params[ "encrypted{$myk}" ] = $v;
			}
		
			$manager = Doctrine_Manager::getInstance();
			$manager->setCurrentConnection('SYSDAT');
			$conn = $manager->getCurrentConnection();
			$query = $conn->prepare("SELECT ". implode(", ",$sql_txt) );
			$query->execute($params);
			$encrypt = $query->fetchAll(Doctrine_core::FETCH_ASSOC);
				
			if ( ! empty($encrypt[0])) {
		
				$encrypted =  $encrypt[0] ;
		
				foreach ($array_values as $k => $v ) {
					$myk = preg_replace("/[^\w]+/", "", $k);
					$result[ $k ] = $encrypted[ "plain{$myk}"];
				}
			}
				
			return $result;
		
		}
		
		
		public function msgFolder($uid)
		{
			$folderlist = Doctrine_Query::create()
				->select('*,AES_DECRYPT(folder_name,"' . Zend_Registry::get('salt') . '") as folder_name')
				->from("MessageFolder")
				->where("userid = ?" , $uid);
			$folderarray = $folderlist->fetchArray();
			return $folderarray;
		}

		//return just the count of new messages
		public function getNewmsg($uid, $fdid = false)
		{
			$msglist_q = Doctrine_Query::create()
			->select("count(*)")
			->from("Messages m")
			->leftJoin("m.MessagesDeleted m2 ON m.id = m2.messages_id AND m2.messages_id IS NOT NULL AND m2.recipient = ?", $uid)
			->where(' m2.messages_id IS NULL ')
			->andWhere("m.read_msg = 0 and m.delete_msg=0 ");
			if($fdid !== false){
				$msglist_q->andWhere("m.folder_id = ?",$fdid);
			}
			$msglist_q->andWhere("m.recipient = ?",$uid);
			$msglist = $msglist_q->fetchArray();
			
			if (!empty($msglist[0]['count'])){
				$mesg = $msglist[0]['count'];
			} else{
				$mesg = 0;
			}
			
			return $mesg;
		}

		public function getClientuser($cid)
		{
			$clntarray = Doctrine_Query::create()
			->select('id, userlimit')
			->from('Client')
			->where("id = ?" , $cid )
			->fetchArray();
			
			$usrarray = Doctrine_Query::create()
			->select("count(*) as count")
			->from("User")
			->where("isdelete = 0 and clientid = ?" , $cid)
			->fetchArray();
			
			if($clntarray[0]['userlimit'] <= $usrarray[0]['count'])
			{
				return false;
			}
			else
			{
				return true;
			}
			
			/* ZF1
			$clnt = Doctrine_Query::create()
				->select('*')
				->from('Client')
				->where("id = '" . $cid . "'");
			$clntarray = $clnt->fetchArray();

			$userlist = Doctrine_Query::create()
				->select("count(*)")
				->from("User")
				->where("isdelete =0 and clientid = " . $cid);
//			$userlist->getSqlQuery();
			$usrarray = $userlist->fetchArray();

			$usr = $usrarray[0]['count'];
			if($clntarray[0]['userlimit'] <= $usrarray[0]['count'])
			{
				return false;
			}
			else
			{
				return true;
			}
			*/
		}

		//returns all, ignoring isdeleted and isactive
		public function getClientUsers($cid)
		{
			$userlist = Doctrine_Query::create()
				->select("*")
				->from("User")
				->where("clientid = ?" , $cid)
				->orWhere("clientid = 0"); //including super admins
			$usrarray = $userlist->fetchArray();

			return $usrarray;
		}

		public function get_client_users($cid)
		{
			$userlist = Doctrine_Query::create()
				->select("*")
				->from("User")
				->where("clientid = ? or clientid = 0" , $cid) //including super admins
				->andWhere('isdelete = "0"')
				->andWhere('isactive="0"');
			$usrarray = $userlist->fetchArray();

			return $usrarray;
		}

		//returns a string of ipid's, comma-delimited (default '0')
		public static function getclientIpid($cid)
		{
			$ipidarray = Doctrine_Query::create()
			->select('id, ipid')
			->from('EpidIpidMapping')
			->where('clientid = ?' , $cid)
			->fetchArray();
			
			$comma = ",";
			$ipidval = "'0'";
			foreach($ipidarray as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['ipid'] . "'";
				$comma = ",";
			}
			
			return $ipidval;
			
			/* ZF1
			$ipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where('clientid = ' . $cid);
			$ipidarray = $ipid->fetchArray();

			$comma = ",";
			$ipidval = "'0'";
			foreach($ipidarray as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['ipid'] . "'";
				$comma = ",";
			}

			return $ipidval;
			*/
		}

		//returns a string of epid's, comma-delimited (default '0')
		public static function getclientEpid($cid)
		{
			$ipidarray = Doctrine_Query::create()
			->select('id, epid')
			->from('EpidIpidMapping')
			->where('clientid = ?' , $cid)
			->fetchArray();
			
			$comma = ",";
			$ipidval = "'0'";
			foreach($ipidarray as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['epid'] . "'";
				$comma = ",";
			}
			
			return $ipidval;
			/* ZF1
			$ipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where('clientid = ' . $cid);
			$ipidarray = $ipid->fetchArray();

			$comma = ",";
			$ipidval = "'0'";
			foreach($ipidarray as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['epid'] . "'";
				$comma = ",";
			}

			return $ipidval;
			*/
		}

		//return boolean - ipid belongs to client_id
		public static function getPatientClient($pid, $cid)
		{
			$ipid = Pms_CommonData::getIpid($pid);
			$ipid = !empty($ipid) ? $ipid : 0;
			
			$cldata = Doctrine_Query::create()
			->select('id')
			->from('EpidIpidMapping')
			->where("ipid = ? " , $ipid)
			->andWhere("clientid = ?" , $cid);
			$clarr = $cldata->fetchArray();
			
		
			if(count($clarr) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
			
			/* ZF1
			$ipid = Pms_CommonData::getIpid($pid);

			$cldata = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where("ipid = '" . $ipid . "'")
				->andWhere("clientid = '" . $cid . "'");
			$clarr = $cldata->fetchArray();

			if($clarr)
			{
				if(count($clarr) > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			*/
		}

		//return boolean - user_id belongs to client_id
		public static function getUserClient($uid, $cid)
		{		
			$cldata = Doctrine_Query::create()
			->select('id')
			->from('User')
			->where("id = ?" , $uid)
			->andWhere("clientid = ?" , $cid )
			->fetchArray();

			if(count($cldata) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}

			/* ZF1
			$ipid = Pms_CommonData::getIpid($pid);

			$cldata = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where("id = '" . $uid . "'")
				->andWhere("clientid = '" . $cid . "'");
			$clarr = $cldata->fetchArray();

			if($clarr)
			{
				if(count($clarr) > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			*/
		}

		public static function getSapvCheckBox($short = false,$invoice_extra = false)
		{
			$Tr = new Zend_View_Helper_Translate();
			$beratung = $Tr->translate('beratung');
			$korrdination = $Tr->translate('korrdination');
			$teilversorgung = $Tr->translate('teilversorgung');
			$vollversorgung = $Tr->translate('vollversorgung');
			if($short)
			{
				$beratung = "BE";
				$korrdination = "KO";
				$teilversorgung = "TV";
				$vollversorgung = "VV";
			}

			
			if($invoice_extra){
				
				$beh = $Tr->translate('shortcut_name_BEH');//"Beratung im stationaren Hospiz (50%)",
				$koh = $Tr->translate('shortcut_name_KOH');//"Koordination im stationaren Hospiz (50%)",
				$tvh = $Tr->translate('shortcut_name_TVH');//Additive Teilversorgung im stationaren Hospiz (50%)",
				$bb = $Tr->translate('shortcut_name_BB');//"Berechnung nach Betreuungstag 100,00",

				if($short)
				{
					$beh = 'BEH';//"Beratung im stationaren Hospiz (50%)",
					$koh = 'KOH';//"Koordination im stationaren Hospiz (50%)",
					$tvh = 'TVH';//Additive Teilversorgung im stationaren Hospiz (50%)",
					$bb ='BB';//"Berechnung nach Betreuungstag 100,00",
				}
				
				
				$verordnetarray = array('0'=>'Keine', '1' => $beratung, '2' => $korrdination, '3' => $teilversorgung, '4' => $vollversorgung, '5'=>$beh,'6'=>$koh,'7'=>$tvh,'8'=>$bb);
				
			} else {
				$verordnetarray = array('1' => $beratung, '2' => $korrdination, '3' => $teilversorgung, "4" => $vollversorgung);
			}
			return $verordnetarray;
		}
		
		public static function get_bra_options_checkboxes($short = false)
		{
			$bra_options_array = array(  
										'PCT01' => "PCT01", 
										'PCT02' => "PCT02", 
										'PCT03a' => "PCT03a", 
										'PCT03b' => "PCT03b", 
										'PCT04a' => "PCT04a", 
										'PCT04b' => "PCT04b", 
										'PCT05' => "PCT05", 
										'PCT06' => "PCT06"
										);
			return $bra_options_array;
		}

		public function getIdfromIpid($ipid)
		{

			$patarr = Doctrine_Query::create()
			->select("id")
			->from('PatientMaster')
			->where("ipid = ?" , $ipid)
			->fetchArray();
			
			if(!empty($patarr[0]['id']))
			{
				return $patarr[0]['id'];
			}
			/* ZF1
			$pt = Doctrine_Query::create()
				->select("*")
				->from('PatientMaster')
				->where("ipid='" . $ipid . "'");
			$patarr = $pt->fetchArray();

			if($patarr)
			{
				$id = $patarr[0]['id'];
				return $id;
			}
			*/
		}

		public static function hideInfo($var, $visible = 0)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			if($logininfo->usertype == 'SA' && $visible != 1)
			{
				$var = Zend_Registry::get('hidemagic');
			}
			return $var;
			
			/* ZF1
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			if($logininfo->usertype == 'SA' && $visible != 1)
			{
				$var = $hidemagic;
			}
			return $var;
			*/
		}

		public function getDateformat($dates)
		{
			if($dates != "")
			{
				$dtarr = array();
				$dtarr = explode(".", $dates);
				$finaldate = $dtarr[2] . "-" . $dtarr[1] . "-" . $dtarr[0];
				return($finaldate);
			}

			return "";
		}
		
		public static function generateDateRangeArray($first, $last, $step = '+1 day', $format = 'd.m.Y')
		{
			$dates = array();
			$current = strtotime($first);
			$last = strtotime($last);
			while($current <= $last)
			{
				$dates[] = date($format, $current);
				$current = strtotime($step, $current);
			}
			return $dates;
		}

		/**
		 * who 'invented' this SHIT has @cla full appreciation
		 * @deprecated 
		 */
		public function arraytocommastring($arr)
		{

			$comma = "";
			for($i = 0; $i < count($arr); $i++)
			{
				$cmstr .= $comma . $arr[$i];
				$comma = ",";
			}
			return $cmstr;
		}

		public function getPdfsAll()
		{
			//last added id: 75
			$pdfarray = array(
				'Arztbrief' => array(
					1 => 'Freitext',
					2 => 'Status',
					3 => 'Info-Brief',
					4 => 'Faxvorlage',
					5 => 'Verlängerung',
					6 => 'DANK',
					7 => 'Todesmitteilung',
					12 => 'Einwiesung Klinikum Fürth',
					14 => 'Briefvorlage Tod des Patienten',
					15 => 'Kostenübernahme',
					17 => 'Faxvorlage Hausarzt',
					27 => 'Faxmitteilung Bremen',
					29 => 'Freitext Tübingen',
					30 => 'Unna Tagesklinik',
					39 => 'Unterschrift Leistungsnachweis',
					40 => 'Briefvorlage Tod des Patienten(PT Munchen West)',
					41 => 'Erstverordnung',
					42 => 'Folgeverordnung',
					46 => 'Schreiben Bremen',
				),
				'Medikation' => array(
					8 => 'Medikamente Wochenplan',
					9 => 'Medikamenten Liste',
					10 => 'Medikation / Dosierung Plan',
					55 => 'Behandlungspflege Plan',
					57 => 'Medikamentenplan',
				    
					63 => 'NEU - Medikationsplan',
					64 => 'NEU - Medikationsplan Patient',
				),
				'Wurzburg' => array(
					11 => 'Einverständniserklärung',
					18 => 'Behandlungsvertrag',
				),
				'Bayern' => array(
					13 => 'Notfallplan',
					31 => 'Besuchformular Artz - Brief',
				),
				'Rechnung' => array(
					16 => 'Rechnung',
					37 => 'Bayern Rechnung',
				),
				'Baden Würt' => array(
					20 => 'SGB V Verordungen',
					21 => 'Muster 63 (p)',
					22 => 'SGB V Abrechnung',
					23 => 'Einverständniserklärung',
					26 => 'Entlassungsplanung',
					19 => 'SAPV Leistungsnachweis',
					35 => 'Medipump',
					38 => 'SGB XI Rechnungen',
					49 => 'Muster 4',
					52 => 'Muster 63 side2',
					56 => 'Muster 13',
				),
				'Rechnung ND' => array(
					24 => 'Rechnung Patienten',
					25 => 'Rechnung Benutzer',
				),
				'Bremen' => array(
					28 => 'Eingangsassessment',
					53 => 'Rechnung SAPV Hospiz',
					65 => 'Eingangsassessment KINDER',
				),
				'Nordrhein/Niedersachsen' => array(
					32 => 'Besuchformular Artz - Brief',
					58 => 'Nordrhein Aufklärung',
					70 => 'MDK bogen NORD',
				),
				'Sachsen-Anhalt' => array(
					33 => 'Besuchformular Artz - Brief',
				),
				'WL' => array(
					34 => 'Besuchformular Artz - Brief',
				),
				'Hessen' => array(
					36 => 'Invoice',
					43 => 'Assessment - Aufnahme',
					44 => 'Assessment - Beratung',
					45 => 'Assessment - Ende',
					67 => 'Assessment II - Aufnahme',
					68 => 'Assessment II - Beratung',
				),
				'Rheinland Pfalz' => array(
					47 => 'Assessment',
					48 => 'Abrechnung',
				),
				'Berlin' => array(
					50 => 'Erhebungsbogen B',
					51 => 'Erhebungsbogen C',
				),
				'Schleswig-Holstein' => array(
					'54' => 'Deckblatt',
				),
				'Members' => array(
					'59' => 'Avery70x35',
					'60' => 'Avery105x48',
				),
				'Algemein'=> array(
					'61' => 'Ethisches Assessment',
					'62' => 'Pflegeverordnung',
				    '66' => 'Muster 2b',
				    '69' => 'Stammblatt (LMU) ',
					'71' => 'Verordnung häuslicher Krankenpflege pag1',
					'72' => 'Verordnung häuslicher Krankenpflege pag2',
					'74' => 'Krankenbeförderung',
					'80' => 'Verordnung häuslicher Krankenpflege 2020 pag1', //ISPC-2777 Dragos 25.01.2021
				),
			    //ISPC-2424
			    'Rechnungsausgangsjournal'=> array(
			        '73' => 'InvoiceJournal_sh',
			    ),
              'Klinik'=> array( //Maria:: Migration CISPC to ISPC 22.07.2020
                    '75' => 'Entlassplanung',
                )
			    
			);


			return $pdfarray;
		}

		public function getUserPdfs()
		{
			//last added id: 1
			$pdfarray = array(
				'Rechnung' => array(
					1 => ' internen Rechnungen'
				),
			);

			return $pdfarray;
		}

		/**
		 * !!! if it fails to perform it will stop script execution with an exit;
		 * 
		 * update 18.05.2018
		 * + exit if empty path
		 * + recursive create path 
		 * 
		 * update 14.03.2018 
		 * 
		 * 29059430400 = total MAX possible unique folders from md5, if md5 has no collisions
		 * 2403116 files created as of jan.2018
		 * 
		 * + added a _patient_file_folder_is_unique, to check first in table patient_file that folder not exists
		 * this is NOT 100% ok, cause you still have the 'race-condition' for php+db... this folder name is not reserved by me in the table
		 * correct would be a do( mkdir then a transaction on innodb) until(ok); 
		 * 
		 * + allways prefix
		 * 
		 *  TODO: unify uniqfolder(), uniqfolder_v2(), tempfolder_for_ftp() under one single , and add 3rd param table where to check for unique name
		 */
		public static function uniqfolder($path = '', $prefix = '')
		{
		    
		    if (empty($path)) {
		        exit;
		    }
		    
		    $prefix = empty($prefix) ? date("dmY_") : $prefix;
		    
		    $max_retry = 3;
		    $i = 0;
		    $dir_created =  false;
		    
		    do {
		        $i++;
		        
		        $dir = $prefix . substr(md5(rand(1, 9999) . microtime()), 0, 10);
			    
			    $dir_is_unique = self::_patient_file_folder_is_unique($dir);
			    
		        if ($dir_is_unique && ! file_exists($path . '/' . $dir)) {
		            
	                if (mkdir($path . '/' . $dir, 0700, true)) { 
	                    $dir_created =  true;
	                }
		        } else {
		            //dir not unique in db or it was allready on hdd(a file or dir with this name)
	            }	
	            
		    } while ($i < $max_retry && ! $dir_created);
		    
		    if ($dir_created && is_dir($path . '/' . $dir)) {
		        
		        return $dir;
		        
		    } else {
		        
		        exit;
		        
		    }
		    
		}
		
		/**
		 * !! this fn return the full path, unlike uniqfolder() that returns just the dir it created
		 * !! this fn does not check like uniqfolder() in db
		 *
		 * update  14.03.2018
		 * + allways prefix
		 * 
		 * TODO: self::uniqfolder() was modified
		 * unify uniqfolder(), uniqfolder_v2(), tempfolder_for_ftp() under one single , and add 3rd param table where to check for unique name
		*/
		public static function uniqfolder_v2( $path, $prefix = '')
		{
			$dir = $path;
			 
			if (empty($prefix) || $prefix == 'date') {$prefix = date("dmY_");}
			$template = "-t {$prefix}XXXXXXXXXX";
			if (($dir) && (is_dir($dir))) { $tmpdir = "--tmpdir={$dir}"; }
			else { $tmpdir = '--tmpdir=' . sys_get_temp_dir(); }
			return exec("mktemp -d {$tmpdir} {$template}");
		}
		

		public function getPdfBackground($client, $type)
		{

			$ff = Doctrine_Query::create()
			->select("*")
			->from("PdfBackgrounds")
			->where("client = ?" , intval($client) )
			->andWhere('pdf_type = ?' , $type )
			->limit(1);
			$bg = $ff->fetchArray();
			
			return $bg[0];
			/* ZF1 
			$ff = Doctrine_Query::create()
				->select("*")
				->from("PdfBackgrounds")
				->where("client='" . intval($client) . "'")
				->andWhere('pdf_type = "' . $type . '"')
				->limit(1);
			$bg = $ff->fetchArray();

			return $bg[0];
			*/
		}

		public function getHospizMenus()
		{
			$menus['top'] = array(28);
			$menus['leftparent'] = array(11, 12, 38, 99);
			$menus['leftchild'] = array(100, 28, 84, 29, 87);
			$menus['patient'] = array(1, 2, 51);
			$menus['modules'] = array(2, 19, 22, 37);
			$menus['overviewboxes'] = array(3, 4, 5);

			$menus['patientishospizonly'] = array(51);

			return $menus;
		}

		public function getPatientVoidPermissions()
		{
			$perms['patient'] = array(5, 7, 8, 21, 22, 25, 26, 37, 38, 39, 40, 41, 50, 27, 18, 24, 41, 23, 22, 37, 28, 26, 40, 21, 25, 38, 39, 45, 29, 13, 48); //"2" is for Patient

			return $perms;
		}

		public function getStandbyDisabledMenus()
		{
			$menus['disabled_standby'] = array('13');

			return $menus;
		}
		


		/**
		 * BEWARE 
		 * length from A->B != B->A
		 * this fn uses client settings route_calculation
		 * this fn uses driving_time_limit
		 * 
		 * @cla on 24.09.2018
		 * 
		 * @cla on 28.02.2019
		 * changed to first fetch distance by using ISPC's own install of Openstreetmap
		 * if our server failes, we continue with Googleapis - this ELSE is now disabled, read below
		 * 
		 * 
		 * @param string $start_address
		 * @param string $destination_address
		 * @return Ambigous <boolean, string>
		 */
		public static function getRouteLength($start_address = null, $destination_address = null)
		{
		    
		    $logininfo    = new Zend_Session_Namespace('Login_Info');
		    $clientid     = $logininfo->clientid;
		    $client_details_array = Pms_CommonData::getClientData($clientid);
			$driving_time_limit = Pms_CommonData::driving_time_limit();
		    
		    $calculate_route = false;
		    
		    if ( ! empty($client_details_array)) {
		        
		        $client_details = $client_details_array[0];
		        	
		        if ($client_details['route_calculation'] == "1") {
		            $calculate_route = true;
		        }
		    }
		    	
		    if ( ! $calculate_route) {
		        
		        $length = false;
		        $duration = false;
		        
		    } elseif (empty($start_address) || empty($destination_address) || $destination_address == $start_address) {
		            //error, distance and time is 0
		            $length = '0 km';
		            $duration = '0';
		            
	        } else {
	            
	            /*
	             * this are user for osm 
	             */
	            $start_address .= ", Germany";
	            $destination_address .= ", Germany";
	                
	            /*
	             * this are needed for googleapis
	             */
		        $startRawurlencode = rawurlencode($start_address);
		        $destinationRawurlencode = rawurlencode($destination_address);
		        
		        
		        $md5hash = md5($startRawurlencode . $destinationRawurlencode);
		        $gdt = GoogleapisDistancematrixTable::getInstance()->findOrCreateOneBy('md5hash', $md5hash);
		     
		        
		        if (empty($gdt->result) 
		            || empty($gdt->fetch_date) 
		            || strtotime($gdt->fetch_date) < strtotime('-30 days')) 
		        {
		            /*
		             * missing result .. 
		             * this is the first time we calculate bethwen this 2 points
		             * or osm or googleapis failed
		             * or too old... maybe a new road was built so we check again 		
		             */
		            
		            $osmHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Openstreetmap');
		            $resultArray = $osmHelper->routeLength($start_address, $destination_address);
		            
		            if ($resultArray['success']) 
		            {    
		                $resultArray['__isOSM'] = true;
		                $resultArray['__isGoogleApis'] = false;
		                
		                $gdt->result = Zend_Json::encode($resultArray);
		                $gdt->fetch_date = new Doctrine_Expression('CURRENT_TIMESTAMP()');
		                $gdt->save();
		                
		                
		            }
		            /**
		             * unComment this next ELSE to fallback to googleapis - if the osm server(osrm) failes to fetch route
		             */
		            /*
		            else {
		                
		                $resultArray = self::_googleapisDistancematrix($startRawurlencode, $destinationRawurlencode);
		                
		                if ( ! empty($resultArray) && $resultArray['status'] == 'OK' && $resultArray['rows'][0]['elements'][0]['status'] == 'OK') 
		                {    
		                    $resultArray['__isOSM'] = false;
		                    $resultArray['__isGoogleApis'] = true;
		                    
		                    $gdt->result = Zend_Json::encode($resultArray);
		                    $gdt->fetch_date = new Doctrine_Expression('CURRENT_TIMESTAMP()');
		                    $gdt->save();
		                }
		            }
		            */
		            
		        } else {
		            //use the allready fetched values from
		            //$gdt->result
		        }
		        
		        
		        if (empty($gdt->result)) {
		            
	                $length = false;
	                $duration = false;
	                
		        } else {
		            
		            $resultArray = Zend_Json::decode($gdt->result);
		            
		            if ($resultArray['__isOSM']) {
		                $length = $resultArray['distance'];
		                $real_duration = $resultArray['duration'];
		            } else {
		                //fallback for googleapis
		                //(if distance is below 100m google displays text in meters not km)
		                $length = $resultArray['rows'][0]['elements'][0]['distance']['value'];
		                $real_duration = $resultArray['rows'][0]['elements'][0]['duration']['value'];
		            }
		            
		            //calculate km from meters value
		            $length = number_format($length * 0.001, '2') . ' km';
		            
		            $real_duration = round($real_duration / 60); //in minutes only... google api returns seconds

		            $duration = $real_duration > $driving_time_limit ? $driving_time_limit : $real_duration;
		        }
		    }
		     
	        return ['length' => $length, 'duration' => $duration, 'real_duration' => $real_duration  , "__debug" =>$resultArray];       
        }
		
        
        private static function _googleapisDistancematrix($startRawurlencode = '', $destinationRawurlencode = '') 
        {

            $apiKey = '';
            
            if (Zend_Registry::isRegistered('googleapis')) {
                $googleapis_cfg = Zend_Registry::get('googleapis');
                $apiKey = $googleapis_cfg['distancematrix']['apiKey'];
            }
            
            $route_url = 'https://maps.googleapis.com/maps/api/distancematrix/json?'
                . 'units=metric'
                    . '&region=de' // this is Germany for addresss
                    . '&origins=' . $startRawurlencode
                    . '&destinations=' . $destinationRawurlencode
                    . '&key=' . $apiKey;
            
            $adapter = new Zend_Http_Client_Adapter_Curl();
            $adapter->setConfig(array(
                'curloptions' => array(
                    CURLOPT_FOLLOWLOCATION  => true,
                    CURLOPT_RETURNTRANSFER  => true,
            
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
            
                    // 	                    	            CURLOPT_TIMEOUT => 11,
                    CURLINFO_CONNECT_TIME => 5,
                    CURLOPT_CONNECTTIMEOUT => 5,
                )
            ));
            
            
            $_httpService =  new Zend_Http_Client(null, [
                'timeout'     => 5,// Default = 10
                'useragent'   => 'ispc-login.de distancematrix'// Default = Zend_Http_Client
            ]);
            
            $_httpService->setAdapter($adapter);
            
            $_httpService->setUri(Zend_Uri_Http::fromString($route_url))
            ->setCookieJar(false)
            ->setMethod('GET')
            ->request('GET');
            
            $result = $_httpService->getLastResponse()->getBody();
            
            $resultArray = null;
            
            if ( ! empty($result)) {
                
                $resultArray = Zend_Json::decode($result);
                
                if ($resultArray['status'] == 'OK' && $resultArray['rows'][0]['elements'][0]['status'] == 'OK') {
            
                    //all OK
                    /*
                     * read
                     * https://developers.google.com/maps/documentation/javascript/distancematrix
                     * for Status Codes
                     * i've only used Response Status Codes = OK , and Element Status Codes = OK
                     * do more filtering if you need
                     */
            
                } elseif ($resultArray['status'] == 'OK' && $resultArray['rows'][0]['elements'][0]['status'] == "NOT_FOUND") {
            
                    //NOT_FOUND — The origin and/or destination of this pairing could not be geocoded.
            
                } elseif ($resultArray['status'] == 'OK' && $resultArray['rows'][0]['elements'][0]['status'] == "ZERO_RESULTS") {
            
                    //ZERO_RESULTS — No route could be found between the origin and destination.
            
                } elseif (in_array($resultArray['status'], ['MAX_ELEMENTS_EXCEEDED', 'OVER_QUERY_LIMIT', 'REQUEST_DENIED'])) {
            
                    GoogleapisDistancematrixTable::_log_error("fatal-error with the GoogleapisDistancematrix" . PHP_EOL . "Status: {$resultArray['status']} - {$resultArray['error_message']}" . PHP_EOL . "apiKey:{$apiKey}" . PHP_EOL . "url:{$route_url}" . PHP_EOL);
            
                } elseif (in_array($resultArray['status'], ['INVALID_REQUEST', 'MAX_DIMENSIONS_EXCEEDED', 'UNKNOWN_ERROR'])) {
                     
                    GoogleapisDistancematrixTable::_log_error("semi-error with the GoogleapisDistancematrix" . PHP_EOL . "Status: {$resultArray['status']} - {$resultArray['error_message']}" . PHP_EOL . "apiKey:{$apiKey}" . PHP_EOL . "url:{$route_url}" . PHP_EOL);
                }
            
            }
            
            return $resultArray;
            
        }
        

		/**
		 * @deprecated, is here just for info
		 */
		public function getRouteLength_OLD($s_addr, $f_addr)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid	= $logininfo->clientid;
			$client_details_array = Pms_CommonData::getClientData($clientid);

			$calculate_route = false;
			if(!empty($client_details_array)){
				$client_details = $client_details_array[0];
				 
				if($client_details['route_calculation'] == "1"){
					$calculate_route = true;
				}
			}
			
			if(!$calculate_route)
			{
				$length = false;
				$real_duration = false;
			} 
			else
			{
				
				//			return false; //remove from production when everything is set
				$s_addr = str_replace(" ", '+', $s_addr . ',Germany');
				$f_addr = str_replace(" ", '+', $f_addr . ',Germany');
				$config = array(
					'adapter' => 'Zend_Http_Client_Adapter_Curl',
					'curloptions' => array(CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, CURLOPT_FOLLOWLOCATION => true),
				);
	
	//		$route_url = 'http://maps.google.com/maps?ie=UTF8&hl=en&ct=clnk&cd=1&saddr='.urlencode($s_addr).'&daddr='.urlencode($f_addr).'&f=d&output=kml&ge_fileext=.kml';
	//		$route_url = 'http://maps.googleapis.com/maps/api/directions/xml?origin='.urlencode('103 Seegefelder Straße, Berlin').'&destination='.urlencode('Bahnhofstraße 16, Schwarzenberg').'&sensor=false';
				$route_url = 'http://maps.googleapis.com/maps/api/directions/xml?origin=' . urlencode($s_addr) . '&destination=' . urlencode($f_addr) . '&sensor=false';
	//		Debug: Simulate Zend_Http_Client_Exception
	//		$route_url = 'http://x/maps/api/directions/xml?origin=' . urlencode($s_addr) . '&destination=' . urlencode($f_addr) . '&sensor=false';
	
	
				$client = new Zend_Http_Client($route_url, $config);
	
				try
				{
					$route = $client->request("GET");
				} catch(Zend_Http_Client_Exception $ex)
				{
					//Log exception, if logger available
					$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
					$log = new Zend_Log($writer);
	
					if($log)
					{
						$log->crit($ex);
					}
				} catch(Zend_Uri_Exception $exception)
				{
					
				}
	
				if($route && $route->getBody())
				{
					$xml = simplexml_load_string($route->getBody());
	
					//calculate km from meters value(if distance is below 100m google displays it in meters not km)
					$length = number_format(($xml->route->leg->distance->value * 0.001), '2') . ' km';
	
					$duration = round($xml->route->leg->duration->value / 60); //in minutes only... google api returns seconds
	
					if($duration > '90')
					{
						$real_duration = '90';
					}
					else
					{
						$real_duration = $duration;
					}
				}
				else
				{
					$length = false;
					$real_duration = false;
				}
			}
//		exit;
//
//
//		preg_match_all("'<name>Route</name><description>(.*?)</description>'si", $route, $length);
//		if(!empty($length[1][0])){
//			$duration = trim(substr($length[1][0], stripos($length[1][0],'(') + 1, (stripos($length[1][0],')') - stripos($length[1][0],'(') - 1)));
//			$s = array('hours','about','mins');
//			$r = array('* 60 +','','');
//			eval('$real_duration = '.trim(str_replace($s,$r,$duration)).';');
//			if(stripos($route,'&#160;km') !== false) { //means distance is in meters
//				$divide = 1;
//			} else {
//				$divide = 0.001;
//			}
//			$length =  trim(substr($length[1][0],19,(stripos($length[1][0],'&#') - 19)));
//			$length = $length * $divide;
//		} else {
//			$length = false;
//			$real_duration = false;
//		}

			$return['length'] = $length;
			$return['duration'] = $real_duration;

			return $return;
		}

		public static function isintersected($r1start, $r1end, $r2start, $r2end)
		{
			return ($r1start == $r2start) || ($r1start > $r2start ? $r1start <= $r2end : $r2start <= $r1end);
		}

		public function getDischargeLocationTypes()
		{
			$typesarray = array(
				'0' => 'Select location type',
				'1' => 'zu Hause',
				'2' => 'Krankenhaus',
				'3' => 'Hospiz',
				'4' => 'Altenheim, Pflegeheim',
				'5' => 'Palliativstation',
				'6' => 'bei Kontaktperson',
				'7' => 'Kurzzeitpflege',
				'8' => 'betreutes Wohnen'
			);
			return $typesarray;
		}

		public static function get_primary_voluntary_statuses()
		{
// 			$status_array = array(
// 				array(
// 					'id' => 'e',
// 					'status' => 'Ehrenamtlicher'
// 				),
// 				array(
// 					'id' => 'k',
// 					'status' => 'Koordinator'
// 				)
// 			);


			$drop = Doctrine_Query::create()
			->select('status_id, description')
			->from('VoluntaryworkersPrimaryStatuses');
			$statuses = $drop->fetchArray();
				
			$i=0;
			foreach($statuses as $k => $st){
				$status_array[$i]['id'] = $st['status_id'];
				$status_array[$i]['status'] = $st['description'];
				$i++;
			}
				
			return $status_array;
			
		    /* ZF1
		    $drop = Doctrine_Query::create()
		    ->select('*')
		    ->from('VoluntaryworkersPrimaryStatuses');
		    $statuses = $drop->fetchArray();
			
		    $i=0;
			foreach($statuses as $k => $st){
			    $status_array[$i]['id'] = $st['status_id'];
			    $status_array[$i]['status'] = $st['description'];
			    $i++;
			}
			
			return $status_array;
			*/
		}

		public function getVoluntaryWorkersStatuses()
		{
			$drop = Doctrine_Query::create()
			->select('status_id, description')
			->from('VoluntaryworkersStatusesDetails');
			$statuses = $drop->fetchArray();
			
			$i=0;
			foreach($statuses as $k => $st){
				$status_array[$i]['id'] = $st['status_id'];
				$status_array[$i]['status'] = $st['description'];
				$i++;
			}
			/* ZF1
		    $drop = Doctrine_Query::create()
		    ->select('*')
		    ->from('VoluntaryworkersStatusesDetails');
		    $statuses = $drop->fetchArray();
		    
		    $i=0;
		    foreach($statuses as $k => $st){
		        $status_array[$i]['id'] = $st['status_id'];
		        $status_array[$i]['status'] = $st['description'];
		        $i++;
		    }
		    */
// 			$status_array = array(
// 				array(
// 					'id' => 'n',
// 					'status' => 'keine Angabe'
// 				),
// 				array(
// 					'id' => 'p',
// 					'status' => 'Ehrenamtliche Pflegekraft'
// 				),
// 				array(
// 					'id' => 'h',
// 					'status' => 'Kinderhospizbegleiter'
// 				),
// 				array(
// 					'id' => 's',
// 					'status' => 'Sterbebegleitung'
// 				),
// 				array(
// 					'id' => 'sh',
// 					'status' => 'stationäres Hospiz'
// 				),
// 				array(
// 					'id' => 'pal',
// 					'status' => 'Palliativstation'
// 				),
// 				array(
// 					'id' => 'nac',
// 					'status' => 'Nachtwachen'
// 				),
// 				array(
// 					'id' => 'off',
// 					'status' => 'Öffentlichkeitsarbeit'
// 				),
// 				array(
// 					'id' => 'tel',
// 					'status' => 'Telefonberatung'
// 				),
// 				array(
// 					'id' => 'tra',
// 					'status' => 'Trauerbegleitung'
// 				)
// 			);
			return $status_array;
		}

		function calculate_median($arr)
		{
			sort($arr);
			$count = count($arr); //total numbers in array
			$middleval = floor(($count - 1) / 2); // find the middle value, or the lowest middle value
			if($count % 2)
			{ // odd number, middle is the median
				$median = $arr[$middleval];
			}
			else
			{ // even number, calculate avg of 2 medians
				$low = $arr[$middleval];
				$high = $arr[$middleval + 1];
				$median = (($low + $high) / 2);
			}
			return $median;
		}

		function calculate_average($arr)
		{
			$count = count($arr); //total numbers in array
			foreach($arr as $value)
			{
				$total = $total + $value; // total value of array numbers
			}
			$average = ($total / $count); // get average value
			return $average;
		}

		public function getPeriodDates($quarterarr, $yeararr, $montharr = array(), $date_format = 'd-m-Y')
		{

			if(!empty($quarterarr))
			{
				$montharr = array();
				foreach($quarterarr as $quart)
				{
					switch($quart)
					{ //switch through the quarter start months
						case '2':
							$montharr[] = "4";
							break;

						case '3':
							$montharr[] = "7";

							break;

						case '4':
							$montharr[] = "10";
							break;

						default:
							$montharr[] = "1";
							break;
					}
				}
			}

			foreach($yeararr as $year)
			{
				if(!empty($montharr))
				{
					foreach($montharr as $month)
					{
						if(!empty($quarterarr))
						{
							$nextmonth = strtotime("+3 month", strtotime("01-" . $month . "-" . $year)); // +3 cause we need the next quarter first month
						}
						else
						{
							$nextmonth = strtotime("+1 month", strtotime("01-" . $month . "-" . $year));
						}
						$periods['start'][] = date($date_format, strtotime("01-" . $month . "-" . $year));
						$periods['end'][] = date($date_format, strtotime('-1 day', $nextmonth)); // -1 day so now next quarter first day first month becomes curent month last day
					}
				}
				else
				{ //only years
					$periods['start'][] = date($date_format, strtotime("01-01-" . $year));
					$periods['end'][] = date($date_format, strtotime("31-12-" . $year));
				}
			}

			return $periods;
		}

		public static function br2nl($string = 0)
		{
			return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
			/* return preg_replace('#<br\s*?/?>#i', "\n", $string); */
		}

		
		/**
		 * be aware , this fn does NOT work for 3+ decimals and uses locale (LC_NUMERIC)
		 * Pms_CommonData::str2num(1.230); => 1.23
		 * Pms_CommonData::str2num('1.230'); => 1230
		 * Pms_CommonData::str2num(1.234); => 1234
		 * Pms_CommonData::str2num('1.234'); =>  1234
		 * @deprecated ! create new function str2float()
		 */
		public function str2num($str)
		{
			$first_try = substr($str, -3, 1);
			$second_try = substr($str, -2, 1);

			if(!is_numeric($first_try) || !is_numeric($second_try))
			{
				if(!is_numeric($first_try)) //3rd character is . or ,
				{
					$decimals = substr($str, -2, 2);
					$number = substr($str, 0, -3);
				}

				if(!is_numeric($second_try))
				{
//				$decimals = substr($str, -2, 1);
					$decimals = str_pad(substr($str, -1, 1), '2', '0', STR_PAD_RIGHT);
					$number = substr($str, 0, -2);
				}

				$clear_number = str_replace(',', '', str_replace('.', '', $number));

				//$final_number = (float) ($clear_number . '.' . $decimals);
				$final_number = (float) $clear_number . '.' . $decimals; // TODO-1418
			}
			else
			{
				$clear_number = str_replace(',', '', str_replace('.', '', $str));

				$final_number = (float) $clear_number;
			}

			return $final_number;
		}

		//added call to new function
		public static function getSubUsers($client, $userid = false)
		{
			$user_array = array(
				'1' => array(// clientid  PMS
					'370' => array(// userid
						'1' => array(
							'name' => 'Frau Edith Schmitz',
							'shortname' => 'Ed',
						),
						'2' => array(
							'name' => 'Frau Beate Schröder-Baum',
							'shortname' => 'BSB',
						),
						'3' => array(
							'name' => 'Frau Kerstin Höck-Hackländer',
							'shortname' => 'HL',
						),
						'4' => array(
							'name' => 'Frau Inka Stirl',
							'shortname' => 'IS',
						),
						'5' => array(
							'name' => 'Frau Gabriele Noeske',
							'shortname' => 'GN',
						),
						'6' => array(
							'name' => 'Frau Claudia Veit-Schurat',
							'shortname' => 'CV',
						)
					)
				),
				'64' => array(// Client Leverkusen$list
					'599' => array(// User Diakoniestation Opladen

						'1' => array(
							'name' => 'Herr Jürgen Hesse',
							'shortname' => 'He',
							'status' => 'old',
						),
						'2' => array(
							'name' => 'Frau Maria Reich',
							'shortname' => 'MR',
							'status' => 'old',
						),
						//xls:4
						'4' => array(
							'name' => 'Frau Beate Kortz',
							'shortname' => 'BK',
						),
						//xls:5
						'3' => array(
							'name' => 'Frau Sabrina Berndt',
							'shortname' => 'SB',
						),
						'5' => array(
							'name' => 'Herr Fazlji Mevzat',
							'shortname' => 'FM',
							'status' => 'old',
						),
						//xls:7
						'6' => array(
							'name' => 'Frau Gudrun Mietz',
							'shortname' => 'GM',
						),
						'7' => array(
							'name' => 'Frau Anne Zimmermann',
							'shortname' => 'AZ',
							'status' => 'old',
						),
						//xls:6
						'8' => array(
							'name' => 'Frau Sabine Ziesemer',
							'shortname' => 'SZ',
						)
					),
					'600' => array(//User Diakoniestation Schlebusch
						'1' => array(
							'name' => 'Frau Regina Hill',
							'shortname' => 'RH',
							'status' => 'old',
						),
						//xls:43
						'2' => array(
							'name' => 'Frau Monika Mintrop',
							'shortname' => 'MM',
						),
						//xls:44
						'3' => array(
							'name' => 'Frau Carmen Ohlig',
							'shortname' => 'CO',
						),
						//xls:45
						'4' => array(
							'name' => 'Frau Ulrike Diderich',
							'shortname' => 'UD',
						),
						//xls:46
						'5' => array(
							'name' => 'Frau Sigrun Haubenreisser',
							'shortname' => 'SH',
						)
					),
					'601' => array(// User Diakoniestation Leichlingen / Witzhelden:
						//xls:15
						'1' => array(
							'name' => 'Frau Edith Schmitz',
							'shortname' => 'Ed',
						),
						//xls:16
						'7' => array(
							'name' => 'Frau Stefanie Hasenburg',
							'shortname' => 'SH',
						),
						//xls:17
						'2' => array(
							'name' => 'Frau Beate Schröder-Baum',
							'shortname' => 'BS',
						),
						'8' => array(
							'name' => 'Frau Laura Steinhausen',
							'shortname' => 'LS',
							'status' => 'old',
						),
						//xls:18
						'3' => array(
							'name' => 'Frau Kerstin Höök-Hackländer ',
							'shortname' => 'HL',
						),
						//xls:19
						'4' => array(
							'name' => 'Frau Inka Stirl',
							'shortname' => 'IS',
						),
						//xls:20
						'5' => array(
							'name' => 'Frau Gabriele Noeske',
							'shortname' => 'GN',
						),
						//xls:21
						'6' => array(
							'name' => 'Frau Claudia Veit-Schurat',
							'shortname' => 'CV',
						),
						//xls:22
						'9' => array(
							'name' => 'Frau Lissy Peterhänsel',
							'shortname' => 'LP',
						),
						//xls:23
						'10' => array(
							'name' => 'Frau Angela Zillger',
							'shortname' => 'AZ',
						),
					),
					'602' => array(//Diakoniestation Burscheid
						'2' => array(
							'name' => 'Frau Beate Heß',
							'shortname' => 'BH',
							'status' => 'old',
						),
						'3' => array(
							'name' => 'Frau Karoline Müller',
							'shortname' => 'KM',
							'status' => 'old',
						),
						//xls:33 new
						'5' => array(
							'name' => 'Frau Barbara Müller',
							'shortname' => 'BM',
						),
						//xls:35 new
						'4' => array(
							'name' => 'Frau Denise Andersen',
							'shortname' => 'DA',
						),
						//xls:32
						'1' => array(
							'name' => 'Frau Sonja Cholewa',
							'shortname' => 'SC',
						),
						//xls:34 new
						'6' => array(
							'name' => 'Frau Susanne Vierkötter',
							'shortname' => 'SV',
						),
					),
					'842' => array(//PCT-Pflege :

						'1' => array(
							'name' => 'Frau Inga Hoffmann',
							'shortname' => 'IH',
							'status' => 'old',
						),
						//xls:53
						'2' => array(
							'name' => 'Herr Robert Tischner',
							'shortname' => 'RT',
						),
						//xls:55
						'3' => array(
							'name' => 'Frau Karin Proksch',
							'shortname' => 'KP',
						),
						'4' => array(
							'name' => 'Frau Silke Peters',
							'shortname' => 'SP',
							'status' => 'old',
						),
						//xls:56
						'5' => array(
							'name' => 'Frau Barbara Wasilewska',
							'shortname' => 'BW',
						),
						//xls:54 new
						'6' => array(
							'name' => 'Frau Judith Weingarten',
							'shortname' => 'JW',
						),
					)
				)
			);

			if($userid)
			{
				//old way
				//return $user_array[$client][$userid];
				//new way
				return PseudoUsers::get_pseudo_users($client, $userid);
			}
			else
			{
				return $user_array;
			}
		}

		public static function getSubUsers_15102013($client, $userid)
		{
			$user_array = array(
				'1' => array(// clientid  PMS
					'370' => array(// userid
						'1' => array(
							'name' => 'Frau Edith Schmitz',
							'shortname' => 'Ed',
						),
						'2' => array(
							'name' => 'Frau Beate Schröder-Baum',
							'shortname' => 'BSB',
						),
						'3' => array(
							'name' => 'Frau Kerstin Höck-Hackländer',
							'shortname' => 'HL',
						),
						'4' => array(
							'name' => 'Frau Inka Stirl',
							'shortname' => 'IS',
						),
						'5' => array(
							'name' => 'Frau Gabriele Noeske',
							'shortname' => 'GN',
						),
						'6' => array(
							'name' => 'Frau Claudia Veit-Schurat',
							'shortname' => 'CV',
						)
					)
				),
				'64' => array(// Client Leverkusen$list
					'599' => array(// User Diakoniestation Opladen
						'1' => array(
							'name' => 'Herr Jürgen Hesse',
							'shortname' => 'He',
						),
						'2' => array(
							'name' => 'Frau Maria Reich',
							'shortname' => 'MR',
						),
						'3' => array(
							'name' => 'Frau Sabrina Berndt',
							'shortname' => 'SB',
						)
					),
					'600' => array(//User Diakoniestation Schlebusch
						'1' => array(
							'name' => 'Frau Regina Hill',
							'shortname' => 'RH',
						),
						'2' => array(
							'name' => 'Frau Monika Mintrop',
							'shortname' => 'SM',
						),
						'3' => array(
							'name' => 'Frau Carmen Ohlig',
							'shortname' => 'CO',
						)
					),
					'601' => array(// User Diakoniestation Leichlingen / Witzhelden:
						'1' => array(
							'name' => 'Frau Edith Schmitz',
							'shortname' => 'Ed',
						),
						'2' => array(
							'name' => 'Frau Beate Schröder-Baum',
							'shortname' => 'BSB',
						),
						'3' => array(
							'name' => 'Frau Kerstin Höck-Hackländer',
							'shortname' => 'HL',
						),
						'4' => array(
							'name' => 'Frau Inka Stirl',
							'shortname' => 'IS',
						),
						'5' => array(
							'name' => 'Frau Gabriele Noeske',
							'shortname' => 'GN',
						),
						'6' => array(
							'name' => 'Frau Claudia Veit-Schurat',
							'shortname' => 'CV',
						)
					),
					'602' => array(//Diakoniestation Burscheid
						'1' => array(
							'name' => 'Frau Sonja Cholewa',
							'shortname' => 'SC',
						),
						'2' => array(
							'name' => 'Frau Beate Heß',
							'shortname' => 'BH',
						)
					)
				)
			);

			return $user_array[$client][$userid];
		}
        /**
         * 
         * @return multitype:multitype:string  multitype:multitype:string
         * 
         * 
         * Changed Hessen shortcuts  for ISPC-2341  By Ancuta
         * Changes on 07.03.2019 :: removed sapvbe and moved the sapvbe shortcuts in rest of the types 
         * 
         */
		public function get_prices_shortcuts()
		{
			$shortcuts = array(
				'admission' => array('E', 'EH'),
				'daily' => array('B0', 'B5', 'B10'),
				'visits' => array('P1', 'P2', 'P3', 'A1', 'A2'),
				'performance' => array('37b1', '37b2', '37b5', '37b6', '37b7', '37b8'),
				'bra_sapv' => array('pct01', 'pct02', 'pct03a', 'pct03b', 'pct04a', 'pct04b', 'pct05', 'pct06', 'hf01', 'hf02'),
				'bra_sapv_weg' => array('weg0', 'weg1', 'weg2', 'weg3'),
				//abk = Assessment, Beratung und Koordination
				//bk = Beratung und Koordination
				//aut = Additiv unterstützte Teilversorgung
				//vv = Vollständige Versorgung
				'bre_sapv' => array('abk', 'bk', 'aut', 'vv'),
				'bre_dta' => array('abk', 'bk', 'aut', 'vv'),
				'bre_hospiz' => array('visit', 'phone', 'assessment', 'v_p_limit'),
// 				'bayern_sapv' => array('BE', 'KO', 'TV', 'VV'),
				'bayern_sapv' => array('BE', 'KO', 'TV', 'VV','BEH','KOH','TVH','BB'),// New added for ISPC-2017 13.07.2017
				//hassen pricelists shortcuts
				'hessen' => array(
					'vdek' => array('pv1', 'pv2', 'pv3', 'ph1', 'ph2','pb1', 'pb2'),
					'privat' => array('pv1pp', 'pv2pp', 'pv3pp', 'ph1pp', 'ph2pp','pb1', 'pb2'),
					'primar' => array('pa1', 'pa2', 'pa3', 'pa4', 'pa5', 'pa6', 'pa7', 'pa8', 'pa9', 'pa10', 'pa11', 'pa12', 'pa13', 'pc1', 'pc2', 'pc3', 'pc4', 'pc5', 'pc6', 'pc7', 'pc8', 'pc9', 'pc10', 'pc11', 'pc12', 'pc13','pb1', 'pb2'),
// 					'sapvbe' => array('pb1', 'pb2')
				),
				//hassen pricelists shortcuts
				'hessen_dta' => array(
//					'vdek' => array('pv1', 'pv2', 'pv3', 'ph1', 'ph2'),
//					'privat' => array('pv1pp', 'pv2pp', 'pv3pp', 'ph1pp', 'ph2pp'),
					'primar' => array('a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9', 'a10', 'a11', 'a12', 'a13', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12', 'c13','b1', 'b2'),
					'primar_change' => array('c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12', 'c13'),
					'primar_team_change' => array('c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12', 'c13'),
					'sapvbe' => array('b1', 'b2')
				),
				'sgbxi' => array('pf1', 'pf2', 'pf3'),
				'bayern' => array(
					'BE' => array('BE1', 'BE2', 'BE3'),
					'KO' => array('KO1', 'KO2', 'KO3'),
					'TV' => array('TV1', 'TV2', 'TV3')
				),
				'rp' => array('rp_eb_1', 'rp_eb_2', 'rp_eb_3',
					'rp_doc_1', 'rp_doc_2', 'rp_doc_3', 'rp_doc_4',
					'rp_nur_1', 'rp_nur_2', 'rp_nur_3', 'rp_nur_4',
					'rp_pat_dead'
				),
				'rp_dta' => array('rp_eb_1', 'rp_eb_2', 'rp_eb_3',
					'rp_doc_1', 'rp_doc_2', 'rp_doc_3', 'rp_doc_4',
					'rp_nur_1', 'rp_nur_2', 'rp_nur_3', 'rp_nur_4',
					'rp_pat_dead'
				),
				'shanlage14' => array(
					'sh_overall_beko',
					'sh_overall_doc_nur_non_hospiz',
					'sh_overall_doc_nur_hospiz',
					'sh_overall_folgeko',
					'sh_overall_phones',
				),
				'shanlage14_report' => array(
					'sh_meeting_participation',
					'sh_assigned_user_actions',
					'sh_visit_actions'
				),
				'sh_internal' => array(
					'sh_house_call',
					'sh_meeting_participation',
					'sh_call_hours',
					'sh_koordination'
				),
			    
				'sh_shifts_internal' => array(
					'VVD',// sapv vv visit day or flatrate - no hospital
					'TVD',// sapv tv visit day or flatrate - no hospital
					'PH1',// if no VVD or TVD - first 2 phone calls(xt)  - no hospital
					'PH2',// if no VVD or TVD - second 2 phone calls(xt)  - no hospital
					'HOD',// sapv any, hospiz and visit day
					'HOP',// sapv any, hospiz and  phone call(xt)
                    'KO',//
				    'FOLGEKO'//
				),
			    
				'hospiz' => array(
					'hospiz_pv_pat', // price for privat patient 
					'hospiz_normal_pat' // price for normal patient
				),
			    
				'care_level' => array( //  Pflegestufe
					'care_level_keine',
					'care_level_0', 
					'care_level_1', 
					'care_level_2', 
					'care_level_3', 
					'care_level_3_5', 
					'care_level_4', 
					'care_level_5' 
				),
				'nr_anlage10' => array( //ISPC-2495 Carmen 10.12.2019 nordrhein price list
					"VV",
					"TV",
					"TVAA",
					"BE",
					"BEAA",
					"KO",
					"SA",
					"SAA",
					"SAK",
				),
			);

			return $shortcuts;
		}

		public function get_default_price_shortcuts()
		{
			
			$default_price_list['performance'] = array(
					"37b1" => '1260',
					"37b2" => '150',
					"37b5" => '200',
					"37b6" => '25',
					"37b7" => '200',
					"37b8" => '100',
			);
			
			$default_price_list['performancebylocation'] = array(
				//ISPC-2549 Carmen 17.02.2020
				"10"=>array(
					"37b1" => '1260',
					"37b2" => '150',
					"37b5" => '200',
					"37b6" => '25',
					"37b7" => '200',
					"37b8" => '100',
				),
				//--
				"5"=>array(
					"37b1" => '1260',
					"37b2" => '150',
					"37b5" => '200',
					"37b6" => '25',
					"37b7" => '200',
					"37b8" => '100',
				),		
				"3"=>array(
					"37b1" => '1260',
					"37b2" => '150',
					"37b5" => '200',
					"37b6" => '25',
					"37b7" => '200',
					"37b8" => '100',
				),		
				"2"=>array(
					"37b1" => '1260',
					"37b2" => '150',
					"37b5" => '200',
					"37b6" => '25',
					"37b7" => '200',
					"37b8" => '100',
				),		
				"0"=>array(
					"37b1" => '1260',
					"37b2" => '150',
					"37b5" => '200',
					"37b6" => '25',
					"37b7" => '200',
					"37b8" => '100',
				),		
			);

			$default_price_list['bra_sapv'] = array(
				'pct01' => '120',
				'pct02' => '330',
				'pct03a' => '140',
				'pct03b' => '290',
				'pct04a' => '240',
				'pct04b' => '290',
				'pct05' => '50',
				'pct06' => '0',
				'hf01' => '30',
				'hf02' => '20',
			);
			
			$default_price_list['bra_sapv_weg'] = array(
				'weg0' => array("doctor" => "0.00", "nurse" => "0.00"),
				'weg1' => array("doctor" => "0.00", "nurse" => "0.00"),
				'weg2' => array("doctor" => "0.00", "nurse" => "0.00"),
				'weg3' => array("doctor" => "0.00", "nurse" => "0.00")
			);
			

			$default_price_list['bre_sapv'] = array(
				'abk' => '11',
				'bk' => '22',
				'aut' => '33',
				'vv' => '44',
			);

			$default_price_list['bre_hospiz'] = array(
				'assessment' => '100.00',
				'visit' => '45.00',
				'phone' => '45.00',
				'v_p_limit' => '135.00',
			);

			$default_price_list['bayern_sapv'] = array(
				'BE' => '200',
				'KO' => '800',
				'TV' => '3250',
				'VV' => '3250',
				'BEH' => '0.00',
				'KOH' => '0.00',
				'TVH' => '0.00',
				'BB' => '0.00',
			);

			$default_price_list['hessen'] = array(
				'vdek' => array('pv1' => '1500', 'pv2' => '120', 'pv3' => '80', 'ph1' => '750', 'ph2' => '60'),
				'privat' => array('pv1pp' => '1500', 'pv2pp' => '120', 'pv3pp' => '80', 'ph1pp' => '750', 'ph2pp' => '60'),
				'primar' => array('pa1' => '1500', 'pa2' => '2350', 'pa3' => '3350', 'pa4' => '4550', 'pa5' => '5850', 'pa6' => '7000', 'pa7' => '8000', 'pa8' => '9150', 'pa9' => '10450', 'pa10' => '11550', 'pa11' => '13300', 'pa12' => '15900', 'pa13' => '2400', 'pc1' => '750', 'pc2' => '1175', 'pc3' => '1675', 'pc4' => '2275', 'pc5' => '2925', 'pc6' => '5250', 'pc7' => '6000', 'pc8' => '6862', 'pc9' => '7838', 'pc10' => '8663', 'pc11' => '9975', 'pc12' => '11925', 'pc13' => '1800'),
				'sapvbe' => array('pb1' => '170', 'pb2' => '75')
			);

			$default_price_list['hessen_dta'] = array(
				'primar' => array('a1' => '1542.15', 'a2' => '2416.04', 'a3' => '3444.14', 'a4' => '4677.86', 'a5' => '6014.39', 'a6' => '7196.70', 'a7' => '8224.80', 'a8' => '9407.12', 'a9' => '10743.65', 'a10' => '11874.56', 'a11' => '13673.73', 'a12' => '16346.79', 'a13' => '2467.44', 'c1' => '771.08', 'c2' => '1208.02', 'c3' => '1722.07', 'c4' => '2338.93', 'c5' => '3007.19', 'c6' => '5397.53', 'c7' => '6168.60', 'c8' => '7054.82', 'c9' => '8058.25', 'c10' => '8906.43', 'c11' => '10255.30', 'c12' => '12260.09', 'c13' => '1850.58'),
				'primar_change' => array('c1' => '', 'c2' => '', 'c3' => '', 'c4' => '', 'c5' => '', 'c6' => '', 'c7' => '', 'c8' => '', 'c9' => '', 'c10' => '', 'c11' => '', 'c12' => '', 'c13' => ''),
				'primar_team_change' => array('c1' => '', 'c2' => '', 'c3' => '', 'c4' => '', 'c5' => '', 'c6' => '', 'c7' => '', 'c8' => '', 'c9' => '', 'c10' => '', 'c11' => '', 'c12' => '', 'c13' => ''),
				'sapvbe' => array('b1' => '174.78', 'b2' => '77.11')
			);

			$default_price_list['sgbxi'] = array(
				'pf1' => '21',
				'pf2' => '21',
				'pf3' => '31'
			);

			$default_price_list['bayern'] = array(
				'BE' => array('BE1' => '0', 'BE2' => '0', 'BE3' => '0'),
				'KO' => array('KO1' => '0', 'KO2' => '0', 'KO3' => '0'),
				'TV' => array('TV1' => '0', 'TV2' => '0', 'TV3' => '0'),
			);

			$default_price_list['rp'] = array(
				'rp_eb_1' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_eb_2' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_eb_3' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_doc_1' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_doc_2' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_doc_3' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_doc_4' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_nur_1' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_nur_2' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_nur_3' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_nur_4' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
				'rp_pat_dead' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00')
			);

			
			$default_price_list['rp_dta'] = array(
				'rp_eb_1' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
				'rp_eb_2' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
				'rp_eb_3' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
				'rp_doc_1' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
				'rp_doc_2' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
				'rp_doc_3' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
					
				'rp_doc_4' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
					
				'rp_nur_1' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
					
				'rp_nur_2' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
					
				'rp_nur_3' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
					
				'rp_nur_4' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				),
				'rp_pat_dead' => 
					array(
						"2"=>array(
								"be"=>array('location_type' => '2','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '2','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '2','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '2','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"3"=>array(
								"be"=>array('location_type' => '3','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '3','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '3','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '3','sapv_type' => 'vv','price_dta' => '0.00')
						 ),	
						"5"=>array(
								"be"=>array('location_type' => '5','sapv_type' => 'be','price_dta' => '0.00'),
								"beko"=>array('location_type' => '5','sapv_type' => 'beko','price_dta' => '0.00'),
								"tv"=>array('location_type' => '5','sapv_type' => 'tv','price_dta' => '0.00'),
								"vv"=>array('location_type' => '5','sapv_type' => 'vv','price_dta' => '0.00')
						 )
				)
					
// 				'rp_eb_2' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_eb_3' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_doc_1' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_doc_2' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_doc_3' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_doc_4' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_nur_1' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_nur_2' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_nur_3' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_nur_4' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00'),
// 				'rp_pat_dead' => array('p_home' => '0.00', 'p_nurse' => '0.00', 'p_hospiz' => '0.00')
			);


			$default_price_list['sh'] = array(
				'sh_overall_beko' => '0.00',
				'sh_overall_doc_nur_non_hospiz' => '0.00',
				'sh_overall_doc_nur_hospiz' => '0.00',
				'sh_overall_folgeko' => '0.00',
				'sh_overall_phones' => '0.00',
			);

			$default_price_list['sh_report'] = array(
				'sh_meeting_participation' => array("doctor" => "50.00", "nurse" => "50.00"),
				'sh_assigned_user_actions' => array("doctor" => "0.00", "nurse" => "60.00"),
				'sh_visit_actions' => array("doctor" => "50.00", "nurse" => "0.00")
			);

			$default_price_list['sh_internal'] = array(
				'sh_house_call' => '0.00',
				'sh_meeting_participation' => '0.00',
				'sh_call_hours' => '0.00',
				'sh_koordination' => '0.00'
			);
			$default_price_list['hospiz'] = array(
				'hospiz_pv_pat' => '0.00',
				'hospiz_normal_pat' => '0.00',
			);

			$default_price_list['care_level'] = array( //  Pflegestufe
			    'care_level_keine'=> '0.00',
			    'care_level_0'=> '0.00',
			    'care_level_1'=> '0.00',
			    'care_level_2'=> '0.00',
			    'care_level_3'=> '0.00',
			    'care_level_3_5'=> '0.00',
			    'care_level_4'=> '0.00',
			    'care_level_5'=> '0.00'
			);
			$default_price_list['nr_anlage10'] = array( //ISPC-2495
				"VV" => "225.00",
				"TV" => "40.00",
				"BE" => "20.00",
				"KO" => "0.00",
				"SA" => "300.00",
				"SAA" => "150.00",
				"SAK" => "150.00",
				"TVAA" => "135.00",
				"BEAA" => "80.00",
			);
			
			
			
			$internal_price_user_groups = PriceShInternalUserShifts::internal_price_user_groups();
			$existing_shortcuts = Pms_CommonData::get_prices_shortcuts();

			foreach($internal_price_user_groups as $group_price)
			{    
			    foreach($existing_shortcuts['sh_shifts_internal'] as $sh){
			        
			        $default_price_list['sh_shifts_internal'][$sh][$group_price] = '00.00';
			    }
			}
			
			return $default_price_list;
		}

		public function get_he_dta_default_ids()
		{
			$default_ids_list['hessen_dta'] = array(
				'primar' => array('a1' => '10527003', 'a2' => '10527004', 'a3' => '10527007', 'a4' => '10527008', 'a5' => '10527009', 'a6' => '10527010', 'a7' => '10527011', 'a8' => '10527012', 'a9' => '10527013', 'a10' => '10527014', 'a11' => '10527015', 'a12' => '10527016', 'a13' => '10527017',
					'c1' => '3010427003', 'c2' => '3010427004', 'c3' => '3010427007', 'c4' => '3010427008', 'c5' => '3010427009', 'c6' => '3010427010', 'c7' => '3010427011', 'c8' => '3010427012', 'c9' => '3010427013', 'c10' => '3010427014', 'c11' => '3010427015', 'c12' => '3010427016', 'c13' => '3010427017'),
				'primar_change' => array('c1' => '6010527403', 'c2' => '6010527404', 'c3' => '6010527407', 'c4' => '6100527408', 'c5' => '6010527409', 'c6' => '6010527410', 'c7' => '6010527411', 'c8' => '6010527412', 'c9' => '6010527413', 'c10' => '6010527414', 'c11' => '6010527415', 'c12' => '6010527416', 'c13' => '6010527417'),
				'primar_team_change' => array('c1' => '6010627403', 'c2' => '6010627404', 'c3' => '6010627407', 'c4' => '6100627408', 'c5' => '6010627409', 'c6' => '6010627410', 'c7' => '6010627411', 'c8' => '6010627412', 'c9' => '6010627413', 'c10' => '6010627414', 'c11' => '6010627415', 'c12' => '6010627416', 'c13' => '6010627417'),
				'sapvbe' => array('b1' => '0010121002', 'b2' => '0010121003')
			);

			return $default_ids_list;
		}
		
		public function get_nd_dta_default_ids()
		{
			$default_ids_list['nd_dta'] = array(
				'admission' => array('E' => '0010622001', 'EH' => '0010622000'),
				'daily' => array('B' => '0010324104','B0' => '0010324104','B5' => '0010324104','B10' => '0010324104'),
				'visits' => array('P1' => '0030623001','P2' => '0030623002','P3' => '0030623003','A1' => '0020623006','A2' => '0020623007')
			);

			return $default_ids_list;
		}

		public function get_period_months($date1, $date2, $format = "Ym")
		{
			$time1 = strtotime(date('Y-m', strtotime($date1) . "-01")); //
			$time2 = strtotime($date2);
			$my = date('mY', $time2);

			while($time1 < $time2)
			{
				if(!in_array(date($format, $time1), $months))
				{
					$months[] = date($format, $time1);
				}
				$time1 = strtotime(' +1 month', $time1);
			}

			if(!in_array(date($format, $time2), $months))
			{
				$months[] = date($format, $time2);
			}


			return $months;
		}

		public function calculate_visit_duration($start_h, $end_h, $start_m, $end_m, $visit_date)
		{
			$start_ts = strtotime(date('Y-m-d', strtotime($visit_date)) . ' ' . str_pad($start_h, 2, "0", STR_PAD_LEFT) . ':' . str_pad($start_m, 2, "0", STR_PAD_LEFT));
			$end_ts = strtotime(date('Y-m-d', strtotime($visit_date)) . ' ' . str_pad($end_h, 2, "0", STR_PAD_LEFT) . ':' . str_pad($end_m, 2, "0", STR_PAD_LEFT));

			return round(($end_ts - $start_ts) / 60);
		}

		public function calculate_visit_durationbydates($start_visit_date, $end_visit_date)
		{
			$start_ts = strtotime(date('Y-m-d H:i:s', strtotime($start_visit_date)));
			$end_ts = strtotime(date('Y-m-d H:i:s', strtotime($end_visit_date)));

			return round(($end_ts - $start_ts) / 60);
		}

		public function contact_form_blocks()
		{
		    //ISPC-2454 // Maria:: Migration ISPC to CISPC 08.08.2020
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			//--
			$blocks = array(
				'time', // Time
				'drivetime', // Fahrtzeit / -strecke
				'drivetime_doc', // Fahrtzeit / Dokumentationszeit
				'symp', // Symptome
				'med', // Medikation
				'com', // Kommentare
				'com_ph', // Kommentare Apotheke
				'anam', // Anamnese
				'ebm', // Arzt EBM
				'goa', // Arzt GOA
				'sgbv', // Leistungseingabe
				'ecog', // ECOG
				'befund', // Korperlicher Befund
				'careinstructions', // Pflege Anweisung
				'visitplan', // Besuch war
				'internalcomment', // Interner Kommentar
				'classification', // Klassifizierung
				'ebmii', // EBM Hausbesuch
				'goaii', // GOA Hausbesuch
				'bra_sapv', // BRA - SAPV Team - Hausarzt Einsatz
				'additional_users', // Beteiligte Mitarbeiter
				'sgbxi', // Qualitätssicherungsbesuch (BW SGBV XI)
				'measures', // Maßnahmen
				'befund_txt', // Befund
				'free_visit', // Nicht berechnen in internen Rechnungen
				'symp_zapv', // Symptome ZAPV
				'symp_zapv_complex', // Symptome ZAPV
				'therapy', // Therapie
				'sgbxi_actions', // SGB XI Leistungen
				//'ebm_ber', // EBM(BER_) for client BER
				//'service_entry', // Leistungserfassung BER
				'vital_signs', // Vitalwerte
				'bowel_movement', // Stuhlgang or bowel movement
				'hospiz_imex',// Hospiz II  - Einfuhr & Ausfuhr
				'hospiz_medi',// Hospiz I  
			    'med_time_dosage', // Medikation New -ISPC1624
			    'ipos',//+ IPOS ISPC-1719 basisassessment from clinic system 
			    'lmu_visit', //+ Status  ISPC-1719 basisassessment from clinic system
			    'lmu_pmba_body',//+ Körperliche Untersuchung ISPC-1719 basisassessment from clinic system    
			    'lmu_pmba_pain',// + Schmerzanamnese     ISPC-1719 basisassessment from clinic system
			    'lmu_pmba_wishes',//+ Wünsche und Erwartungen des Patienten und seiner Angehörigen ISPC-1719 basisassessment from clinic system 
			    'lmu_pmba_aufklaerung',// + Aufklärungsstand/Krankheitsverarbeitung Patient/ ggf. Angehörige ISPC-1719 basisassessment from clinic system 
			    'todos', //+ To Do ISPC-1719 basisassessment from clinic system
			    'lmu_pmba_psysoz',// + Psychosoziale Anamnese ISPC-1719 basisassessment from clinic system  (15.06.2016)  
			    
			    'kvno_visit_type', //+ ISPC-1740 block Nordrhein
			    'bavaria_options', //+ ISPC-1703 Kontaktformular for BAVARIA
			    
					
			    'time_division', //+ ISPC-1784 Zeitaufteilung Kontaktformular this block shows the "minutes" of the visit which the user documented
			    'tracheostomy', //ISPC-1787
			    'clientsymptoms', // ISPC-1798
			    'ventilation', // ISPC-1798 - Beatmung
			    'invoice_condition', // ISPC-1798 - Beatmung
			    'treatment_plan', //ISPC - 2277 - Behandlungsplan
			    
			    //ispc-2291
			    'puncture',
			    'infusion',
			    'infusiontimes',
			    'adverseevents',
			    
			    //ISPC-2387 23.05.2019
			    'visitclasification',
 
			    //ISPC-2488 Lore 22.11.2019
			    'delegation',
			    
			    //ISPC-2671 Lore 07.09.2020
			    'patient_acp',
			    
			    //ISPC-2668 Lore 11.09.2020
			    'patient_ms',
			    
			    //ISPC-2666 Lore 16.09.2020
			    'patient_hi',
			    
			    //ISPC-2667 Lore 21.09.2020
			    'patient_ci',
			    
			    //ISPC-2669 Lore 23.09.2020
			    'patient_hc',
			    
			    //ISPC-2773 Lore 14.12.2020
			    'patient_familyinfo',
			    
			    //ISPC-2776 Lore 15.12.2020
			    'patient_childrendiseases',
			    
			    //ISPC-2788 Lore 08.01.2021
			    'patient_nutritioninfo',
			    
			    //ISPC-2787 Lore 11.01.2021
			    'patient_stimulatorsinfo',
			    
			    //ISPC-2790 Lore 12.01.2021
			    'patient_finalphase',
			    
			    //ISPC-2791 Lore 13.01.2021
			    'patient_excretioninfo',
			    
			    //ISPC-2792 Lore 15.01.2021
			    'patient_personalhygiene',
			    
			    //ISPC-2793 Lore 18.01.2021
			    'patient_comm_employ',
			    
			    //ISPC-2670 Lore 24.09.2020
			    'patient_evn',
			    
			    //ISPC-2673 Lore 25.09.2020
			    'resources',
			    
			    //ISPC-2487 Ancuta 27.11.2019
			    'coordinator_actions',
					
				//ISPC-2508 Carmen 22.01.2020
				'artificial_entries_exits',
					
				//ISPC-2663 Carmen 02.09.2020
				'talkwithsingleselection',

				//Maria:: Migration CISPC to ISPC 22.07.2020
                'care_process_clinic',//IM-4
                'time_documentation_clinic', //IM-16, Zeitdokumentation for ISPC clinic
                'treatment_plan_clinic', //IM-26 Behandlungsplan für ISPC clinic
                'versorger',//IM-25 Versorger Block
                'talkcontent', //IM-46 Conversation-Content
                'job_background_clinic', //IM-47 Jobbackground Patient
                'discharge_planning_clinic', //IM-48 Discharge Planning
                'screen_depression_clinic', //IM-51 Screening for Depression
                'medication_clinic', //IM-53 Medication-Block
                'genogram', //IM-55 Genogram
                'psychosocial_status', // IM-62 Psychosocial Status
                'talkwith', // IM-56 Talk with
                'lmu_sign',
                'palliativ_support', // IM-65 palliativ support
                'palliativ_assessment', //IM-66
                'clinic_soap', //IM-87
		        'pmd_rahmendaten',
                'clinic_diagnosis', //IM-91
                'clinic_shift', //IM-92
                'clinic_measure', //IM-93
                'actual_problems', //IM-105
                'report_recipient', // IM-104
                'documentation', // IM-137
                'pflegeba', // ISPC-2599 Basisassessment Pflege
                'coordinationtime', //ISPC-2626 Koordinationszeit
                'pcoc', // IM-147,
                'fileupload', //IM-2628 Fileupload
                'lmu_pmba2', //IM-2631 ELSA: körperliche Untersuchung //Maria:: Migration CISPC to ISPC 20.08.2020
				'beatmung', //ISPC-2697, elena, 05.11.2020
                'anamnese', //ISPC-2694, elena, 14.12.2020
                'reactions', //ISPC-2657,elena,11.01.2021
				'new_diagnosis', //ISPC-2775 Carmen 04.01.2021

				'talkback', //ISPC-2868,Elena,18.03.2021
			);			
			//ISPC-2454
			$custom_form_blocks = FormBlockCustomSettingsTable::findByClientid($clientid);
			
			foreach($custom_form_blocks as $vcfb)
			{
				array_push($blocks, $vcfb['block_abbrev']);
			}

			return $blocks;
		}

		/**
		 * TODO-3843 Ancuta 11.02.2021 
		 * @return string[]
		 */
		public function contact_form_blocks2recorddata()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			//--
			$blocks = array(
			    
			    'clinic_shift', //IM-92
			    'documentation', // IM-137
			    'time_documentation_clinic', //IM-16, Zeitdokumentation for ISPC clinic
			    'talkcontent', //IM-46 Conversation-Content
			    'bericht_fbe',
			    'talkwith', // IM-56 Talk with
                'talkback', //ISPC-2868,Elena,18.03.2021
                'assessment_basis',//ISPC-2886 Nico 15.04.2021
			);			
			//ISPC-2454
/* 			$custom_form_blocks = FormBlockCustomSettingsTable::findByClientid($clientid);
			
			foreach($custom_form_blocks as $vcfb)
			{
				array_push($blocks, $vcfb['block_abbrev']);
			} */

			return $blocks;
		}

        /**
         * TODO-4035 Nico 12.04.2021
         * This are the blocks that can write to a user defined shortcut
         * @return string[]
         */
        public function contact_form_blocks2shortcut()
        {
            $blocks = array(
                'bericht_fbe',
                'visite_summary',
                'bewusstsein',
                'pcoc',
                'assessment_basis',//ISPC-2886 Nico 15.04.2021
                'vorerkrankungen_therapien'//ISPC-2888

            );

            return $blocks;
        }

		public static function getUsersData($uids)
		{
			if(count($uids) == 0)
			{
				$uids[] = '9999999';
			}

			$pt = Doctrine_Query::create()
				->select('*')
				->from('User')
				->whereIn("id", $uids)
				->andWhere('isdelete=0');
			$usarray = $pt->fetchArray();

			if($usarray)
			{
				foreach($usarray as $k_user => $v_user)
				{
					$users_data[$v_user['id']] = $v_user;
				}


				return($users_data);
			}
		}

		public function get_dashboard_actions()
		{
			$dashboard_labels = array('asses', 'reasses', 'anlage', 'todo', 'team_events', 'custom_team_event', 'custom_doctor_event', 'anlage4awl', 'sgbxi', 'patient_birthday');

			return $dashboard_labels;
		}

		public static function getSgbvStatusRadio()
		{
			$Tr = new Zend_View_Helper_Translate();

			$notmentioned = $Tr->translate('notmentioned');
			$created = $Tr->translate('created');
			$prescribed = $Tr->translate('prescribed');
			$sent_to_healt_insurance = $Tr->translate('sent_to_healt_insurance');
			$approved = $Tr->translate('approved');
			$approved_limit = $Tr->translate('approved_limit');
			$denied = $Tr->translate('denied');

			$sgbv_status_array = array(
				'1' => $created,
				'2' => $prescribed,
				'3' => $sent_to_healt_insurance,
				'4' => $approved,
				'5' => $approved_limit,
				'6' => $denied,
				'10' => $notmentioned
			);
			return $sgbv_status_array;
		}

		public static function getSgbvFillingConditions()
		{
			$sgbv_conditions_array = array(
				'glucose', //'n',
				'ulcer_treatment_degree2', //'s_t_grad',
				'ulcer_treatment_degree34', //'s_t_grad',
				'injection_ready_preparation', //'u_v_z2',
				'injection_im_preparation', //'u_w_z2',
				'injection_sc_preparation', //'u_x_z2',
				'medication_ready_preparation', //'y_z_z2',
				'medication_administer_preparation', //'y_z1_z2_text',
				'supportive_association', //'z3'
				'wund_bandages_location', //'z5_z5a',
				'other'//'z6_z7_text',
			);
			return $sgbv_conditions_array;
		}

		public function get_sgbv_day_groups()
		{
			$sgbv_day_groups = array(
				'F' => array('start' => '06', 'end' => '10'),
				'V' => array('start' => '10', 'end' => '12'),
				'M' => array('start' => '12', 'end' => '14'),
				'N' => array('start' => '14', 'end' => '17'),
				'A' => array('start' => '17', 'end' => '20'),
				'Z' => array('start' => '20', 'end' => '06'),
			);

			return $sgbv_day_groups;
		}

		public function str_replace_assoc($replace, $subject)
		{
			if(is_array($replace))
			{
				return str_replace(array_keys($replace), array_values($replace), $subject);
			}
		}

		public function get_invoice_types()
		{
			$invoice_types = array(
				//BAYERN INVOICE TYPE
				'invoice/invoice' => 'by_invoice',
				'invoice/bayerninvoices' => 'bayern_invoice',
				//NIE INVOICE TYPES
				'invoice/healthinsuranceinvoices' => 'nie_patient_invoice', //patients invoices
				'invoice/clientusersinvoices' => 'nie_user_invoice', //users invoices
				//BW INVOICE TYPES
				'invoice/bwinvoices' => 'bw_sapv_invoice', //sapv
				'invoice/sgbvinvoices' => 'bw_sgbv_invoice', //sapv
				'invoice/breinvoices' => 'bre_sapv_invoice', //bre sapv
				'invoice/brehospizinvoices' => 'bre_hospiz_sapv_invoice', //bre sapv hospiz
				'invoice/medipumpsinvoices' => 'bw_mp_invoice', //medipumps
				'invoice/medipumpsinvoices' => 'bw_mp_invoice', //medipumps
				//Hessen
				'invoice/heinvoiceslist' => 'he_invoice', //Hessen invoice
				'invoice/sgbxiinvoices' => 'bw_sgbxi_invoice', //sapv
				'invoice/rpinvoice' => 'rp_invoice', //sapv
				'invoice/rpinvoice' => 'rpinvoice', //ISPC-2745 Carmen 24.11.2020 - to be able to attach template to RP invoice
			    'invoice/rpinvoiceslist' => 'rp_invoice', //sapv//ISPC-2263 Ancuta 14.05.2021
				'invoicenew/shinvoices' => 'sh_invoice', //SH invoice
				'invoicenew/bayerninvoices' => 'new_bayern_invoice', //BAY new Tagepauschale invoice
				'invoicenew/shinternalinvoices' => 'sh_internal_invoice', //SH INTERNAL  invoice
				'invoicenew/shshiftsinternalinvoices' => 'sh_shifts_internal_invoice', //SH INTERNAL  user SHIFTS (ISPC-2257)
				'invoicenew/brainvoices' => 'bra_invoice', //Bra invoices
				'invoicenew/membersinvoices' => 'members_invoice', //Bra invoices
				//TODO-1425
				'invoicenew/hospizinvoice' => 'hospiz_invoice', //Hospiz invoices
// 				'invoice/rpinvoiceslist' => 'rl_invoice', //Rp invoices
				'invoicenew/rlpinvoice' => 'rlp_invoice', //Rlp invoices
				'invoicenew/bwsapvinvoice' => 'bw_sapv_invoice_new', //Bw invoice new
				//add for ISPC-2532 by Carmen 14.02.2020 
				'invoicenew/bwmedipumpsinvoice' => 'bw_medipumps_invoice', //Bw medipumps new
				// ISPC-2214
				'invoicenew/brekinderinvoice' => 'bre_kinder_invoice', //bremen Kinder new billing :: ISPC-2214
				// ISPC-2233
				'internalinvoice/invoices' => 'internal_invoice', //internal_invoice :: ISPC-2233
				// ISPC-2286
				'invoicenew/nrinvoice' => 'nr_invoice', //Nordrhein - new billing for new contract :: ISPC-2286

				// ISPC-2461
				'invoicenew/demstepcareinvoice' => 'demstepcare_invoice', //Demstepcare 
			    // ISPC-2585 Ancuta 15.06.2020
				'invoicenew/demstepcareinternal' => 'demstepcare_internal_invoice', //INTERNAL Demstepcare 
			);

			return $invoice_types;
		}

		
        /**
         * Ancuta
         * 27.08.2019
         * TODO-2510 - separate external from internal
         * copy of fn get_invoice_types
         * @param string $only_internal
         * @param string $only_external
         * @return Ambigous <multitype:string , multitype:>
         */
		public function get_all_invoice_types($only_internal=false,$only_external=false)
		{
		    $internal_invoices_types = array(
		        'invoicenew/shinternalinvoices' => 'sh_internal_invoice', //SH INTERNAL  invoice
		        'invoicenew/shshiftsinternalinvoices' => 'sh_shifts_internal_invoice', //SH INTERNAL  user SHIFTS (ISPC-2257)
		        // ISPC-2233
		        //'internalinvoice/invoices' => 'internal_invoice', //internal_invoice :: ISPC-2233
		        
		        //ISPC-2585 Ancuta 15.06.2020
		        'invoicenew/demstepcareinternal' => 'demstepcare_internal_invoice', //INTERNAL Demstepcare
		        //--
		    );
		
		
		    $external_invoices_types = array(
		        //BAYERN INVOICE TYPE
		        'invoice/invoice' => 'by_invoice',
		        'invoice/bayerninvoices' => 'bayern_invoice',
		        //NIE INVOICE TYPES
		        'invoice/healthinsuranceinvoices' => 'nie_patient_invoice', //patients invoices
		        'invoice/clientusersinvoices' => 'nie_user_invoice', //users invoices
		        //BW INVOICE TYPES
		        'invoice/bwinvoices' => 'bw_sapv_invoice', //sapv
		        'invoice/sgbvinvoices' => 'bw_sgbv_invoice', //sapv
		        'invoice/breinvoices' => 'bre_sapv_invoice', //bre sapv
		        'invoice/brehospizinvoices' => 'bre_hospiz_sapv_invoice', //bre sapv hospiz
		        'invoice/medipumpsinvoices' => 'bw_mp_invoice', //medipumps
		        'invoice/medipumpsinvoices' => 'bw_mp_invoice', //medipumps
		        //Hessen
		        'invoice/heinvoiceslist' => 'he_invoice', //Hessen invoice
		        'invoice/sgbxiinvoices' => 'bw_sgbxi_invoice', //sapv
// 		        'invoice/rpinvoice' => 'rp_invoice', //sapv
		        'invoice/rpinvoiceslist' => 'rp_invoice', //sapv//ISPC-2263 Ancuta 14.05.2021
		        'invoicenew/shinvoices' => 'sh_invoice', //SH invoice
		        'invoicenew/bayerninvoices' => 'new_bayern_invoice', //BAY new Tagepauschale invoice
		        'invoicenew/brainvoices' => 'bra_invoice', //Bra invoices
		        'invoicenew/membersinvoices' => 'members_invoice', //Bra invoices
		        //TODO-1425
		        'invoicenew/hospizinvoice' => 'hospiz_invoice', //Hospiz invoices
		        // 				'invoice/rpinvoiceslist' => 'rl_invoice', //Rp invoices
		        'invoicenew/rlpinvoice' => 'rlp_invoice', //Rlp invoices
		        'invoicenew/bwsapvinvoice' => 'bw_sapv_invoice_new', //Bw invoice new
		        // ISPC-2214
		        'invoicenew/brekinderinvoice' => 'bre_kinder_invoice', //bremen Kinder new billing :: ISPC-2214
		
		        // ISPC-2286
		        'invoicenew/nrinvoice' => 'nr_invoice', //Nordrhein - new billing for new contract :: ISPC-2286

		        // ISPC-2461
		        'invoicenew/demstepcareinvoice' => 'demstepcare_invoice', //Demstepcare
		    );
		
		    if($only_internal === true){
		        $invoice_types  = $internal_invoices_types;
		    } else if($only_external === true){
		        $invoice_types  = $external_invoices_types;
		    } else {
		        $invoice_types  = array_merge($internal_invoices_types,$external_invoices_types);
		    }
		    	
		    return $invoice_types;
		}		
		
		
		public function anlage5_part2_diagnosis()
		{
			$diagnosis = array(
				'1' => 'Schmerz',
				'2' => 'Übelkeit / Erbrechen',
				'3' => 'Atemnot',
				'4' => 'Angst',
				'5' => 'Obstipation / Diarrhoe',
			);

			return $diagnosis;
		}

		public function get_sapv_verordnets()
		{
			$verordnets = array(
				'1' => 'Beratung',
				'2' => 'Koordination',
				'3' => 'Teilversorgung',
				'4' => 'Vollversorgung'
			);

			return $verordnets;
		}

		public function default_approved_visit_type()
		{
			$default = "p3";

			return $default;
		}

		public function get_user_groupid($userid)
		{
			$user_details = Pms_CommonData::getUsersData(array($userid));

			if($user_details)
			{
				return $user_details[$userid]['groupid'];
			}
			else
			{
				return false;
			}
		}

		public function get_days_number_between($start, $end)
		{
			$start = strtotime($start);
			$end = strtotime($end);
			$datediff = $start - $end;

			return floor($datediff / (60 * 60 * 24));
		}

		function str_split_unicode($str, $l = 0)
		{
			if($l > 0)
			{
				$ret = array();
				$len = mb_strlen($str, "UTF-8");
				for($i = 0; $i < $len; $i += $l)
				{
					$ret[] = mb_substr($str, $i, $l, "UTF-8");
				}
				return $ret;
			}
			return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
		}

		function str_row_split($str, $allowed_str_lenght)
		{

			$new_string = wordwrap($str, $allowed_str_lenght, "|#|", false);
			$new_string_array = explode('|#|', $new_string);

			return ($new_string_array);
		}

		public function has_html($string)
		{
			if(trim($string) != trim(strip_tags($string)))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function clear_data(&$item, $key, $excluded = false)
		{
			if(!in_array($key, $excluded) || $excluded === false)
			{
				if(Pms_CommonData::has_html($item))
				{
					$item = htmlspecialchars(strip_tags(trim($item)), ENT_QUOTES, 'UTF-8');
				}
				else
				{
					$item = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
				}
			}
		}

		public function clear_pdf_data($post, $html_keys = false)
		{
			foreach($post as $k_post => $v_post)
			{
				if(!in_array($k_post, $html_keys))
				{
					if(!is_array($v_post))
					{
						$post_data[$k_post] = htmlspecialchars($v_post);
					}
					else
					{
						$post_data[$k_post] = Pms_CommonData::clear_pdf_data($v_post, $html_keys);
					}
				}
				else
				{
					//preserve data of excluded keys
					$post_data[$k_post] = $v_post;
				}
			}

			return $post_data;
		}

/**
 * copy of clear_pdf_data
 * ISPC-2329 new medi design in the field "INDIKATION/ KOMMENTAR" the symbol "<" or ">" in pdf dont show
 * ISPC-2329 Loredana 17.01.2020 -> din nu stiu ce motiv pt Array-ul cu key=[0] din "$post['medications_array']" nu aplica htmlspecialchars la generatePdfNew
 * @param unknown $post
 * @param boolean $html_keys
 * @return string
 */		
		public function clear_pdf_data_medi($post, $html_keys = false)
		{
		    foreach($post as $k_post => $v_post)
		    {
		        if(!in_array($k_post, $html_keys))
		        {
		            if(!is_array($v_post))
		            {
		                $post_data[$k_post] = htmlspecialchars($v_post);
		            }
		            else
		            {
		                $post_data[$k_post] = Pms_CommonData::clear_pdf_data_medi($v_post, $html_keys);
		            }
		        }
		        else
		        {
		            //preserve data of excluded keys
		            // ISPC-2329 new medi design in the field "INDIKATION/ KOMMENTAR" the symbol "<" or ">" in pdf dont show
		            // ISPC-2329 Loredana 17.01.2020 -> din nu stiu ce motiv pt Array-ul cu key=[0] din "$post['medications_array']" nu aplica htmlspecialchars la generatePdfNew
		            if(isset($v_post['comments'])){
		                $only_comments = htmlspecialchars($v_post['comments']);
		                $v_post['comments'] = $only_comments;
		            }
		            //.
		            $post_data[$k_post] = $v_post;
		        }
		    }
		    
		    return $post_data;
		}


		public function terminal_import_csv_labels($col_limit = 18)
		{
			$Tr = new Zend_View_Helper_Translate();
			$col_limit = ($col_limit - 1);
			for($i = 0; $i <= $col_limit; $i++)
			{
				$csv_labels[$i] = $Tr->translate('terminal_label_row_' . $i);
			}

			return $csv_labels;
		}

		public function treatment_days_options()
		{
			$Tr = new Zend_View_Helper_Translate();

			$tr_options[''] = $Tr->translate('select_day_method');
			$tr_options['tr'] = $Tr->translate('tr_days');
			$tr_options['hp'] = $Tr->translate('hp_days');
			$tr_options['hz'] = $Tr->translate('hz_days');

			return $tr_options;
		}

		public static function patients_active($select = "*", $client = null, $periods = null, $ipids = null, $order_by = "p.last_name", $sort = "ASC", $search_sql = false, $limit = false, $page = '0', $include_standby = false)
		{
			$patientmaster = new PatientMaster();
			$sql_period_standby_text = "";
			$sql_period_standby = array();
			$all_period_days = array();	// TODO-2735 Ancuta 10.12.2019 :: Create an array with all requested period days 
			if($periods)
			{
				foreach($periods as $period)
				{
					if(empty($period['end']))
					{
						$period['end'] = date('Y-m-d', strtotime('+1 day'));
					}
					// TODO-2735 Ancuta 10.12.2019 :: Create an array with all requested period days 
					$all_period_days  = array_merge($all_period_days ,$patientmaster->getDaysInBetween($period['start'], $period['end']));
					//-- 
					
					$sql_period .= ' OR ((a.start BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (a.end BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (a.start <= "' . $period['start'] . '" AND (a.end = "0000-00-00" OR a.end >= "' . $period['end'] . '")))';
					
					//if it has any standbys in this period it is considered standby
// 					$sql_period_standby[] = ' ((ps.start BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (ps.end BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") AND (ps.start <= "' . $period['start'] . '" AND (ps.end = "0000-00-00" OR ps.end >= "' . $period['end'] . '")))';
					//only if the standby-duration is greater that the period we consider the patient as isstandby
					$sql_period_standby[] = ' (ps.start <= "' . $period['start'] . '" AND (ps.end = "0000-00-00" OR ps.end >= "' . $period['end'] . '"))';
					
				}

				$sql_period = ' (' . substr($sql_period, 3) . ') ';
				
				$sql_period_standby_text = " AND ( " . implode(" OR ", $sql_period_standby) . " )";
			}
			else
			{
				$sql_period = '1';
			}

			if($client && is_numeric($client))
			{
				$sql_client = 'e.clientid = "' . $client . '"';
			}
			else
			{
				$sql_client = '1';
			}

			if($ipids)
			{
				foreach($ipids as $ipid)
				{
					$sql_ipids .= '"' . $ipid . '",';
				}

				$sql_ipids = 'e.ipid IN (' . substr($sql_ipids, 0, -1) . ')';
			}
			else
			{
				$sql_ipids = '1';
			}
			
			if(!$include_standby)
			{	
			$q = Doctrine_Query::create()
				->select($select)
				->from('EpidIpidMapping e')
				->leftJoin('e.PatientMaster p')
				->leftJoin('e.PatientActive a')
// 				->leftJoin('e.PatientStandby ps ON e.ipid = ps.ipid '. $sql_period_standby_text)
				->where($sql_client)
				->andWhere($sql_period)
				->andWhere($sql_ipids)
				->andWhere('p.isdelete = 0')
// 				->andWhere('p.isstandby = 0')
// 				->andWhere('ps.ipid IS NULL')
				->andWhere('p.isstandbydelete = 0')
				->orderBy($order_by . ' ' . $sort);
			}
			else 
			{
				$q = Doctrine_Query::create()
				->select($select)
				->from('EpidIpidMapping e')
				->leftJoin('e.PatientMaster p')
				->leftJoin('e.PatientActive a')
				->where($sql_client)
				->andWhere($sql_period)
				->andWhere($sql_ipids)
				->andWhere('p.isdelete = 0')
				->andWhere('p.isstandbydelete = 0')
				->orderBy($order_by . ' ' . $sort);
			}
			if($search_sql)
			{
				$q->andWhere($search_sql);
			}

			//disable group by if counting
			if(strpos($select, "count") === false)
			{
				$q->groupBy('e.ipid');
			}

			if($limit)
			{
				if(is_numeric($limit) && $limit > '0')
				{
					$q->limit($limit);
					$q->offset($page * $limit);
				}
			}
// 			print_r($q->getSqlQuery());
// 			exit;

			$patients_Arr = $q->fetchArray();
			
			$patient_standby = array();
			foreach($patients_Arr as $k=>$pdata){
				$patient_ipids[] = $pdata['ipid'];
				if($pdata['PatientMaster']['isstandby'] == "1"){
					$patient_standby[] = $pdata['ipid']; 
				}
			}
			
			if(!$include_standby && isset($patient_ipids) && !empty($patient_ipids))
			{	
				
				$rea_q = Doctrine_Query::create()
				->select('*')
				->from('PatientReadmission')
				->whereIn('ipid',$patient_ipids);
				$read_details_all = $rea_q->fetchArray();
				
				if($read_details_all){
					$admission_days = array();
					$discharge_days = array();
					foreach($read_details_all  as $k=>$rdata){
						if($rdata['date_type'] == "1"){
							$admission_days[$rdata['ipid']][]= date('Y-m-d',strtotime($rdata['date']));
						} 
						if($rdata['date_type'] == "2"){
							$discharge_days[$rdata['ipid']][]= date('Y-m-d',strtotime($rdata['date']));
						} 
					}
				}
				
				 
				$act_q = Doctrine_Query::create()
				->select('*')
				->from('PatientActive a')
				->whereIn('a.ipid',$patient_ipids)
				->andWhere($sql_period);
				$patient_active_details_all = $act_q->fetchArray();
				
				foreach($patient_active_details_all  as $k=>$pdata){
					$patient_active_details[$pdata['ipid']][]= $pdata;
				}
				
				$patients_periods_active =  array();
				$patients_initial_periods_active =  array();
				foreach($patient_active_details as $kipid=>$act_data_arr){
					foreach($act_data_arr as $k=>$act_data){
						$all_s_start = date('Y-m-d', strtotime($act_data['start']));
						if($act_data['end'] != "0000-00-00"){
							$all_s_end = date('Y-m-d', strtotime($act_data['end']));
						} else{
							$all_s_end = date('Y-m-d');
						}
				
						if(empty($patients_periods_active[$act_data['ipid']]['days']))
						{
							$patients_periods_active[$act_data['ipid']]['days'] = array();
						}
				
						$temp_all_sapv_days[$act_data['ipid']] = $patientmaster->getDaysInBetween($all_s_start, $all_s_end);
						$patients_periods_active[$act_data['ipid']]['days'] = array_merge($patients_periods_active[$act_data['ipid']]['days'], $temp_all_sapv_days[$act_data['ipid']]);
						$patients_initial_periods_active[$act_data['ipid']]['days'] = array_merge($patients_periods_active[$act_data['ipid']]['days'], $temp_all_sapv_days[$act_data['ipid']]);
					}
				}
				
				$st_q= Doctrine_Query::create()
				->select('*')
				->from('PatientStandby ps')
				->whereIn('ps.ipid',$patient_ipids);
				$standby_q_arr  = $st_q->fetchArray();
				
				
				foreach($standby_q_arr as $k=>$pst_data){
					$patients_periods[$pst_data['ipid']][] = $pst_data;
					
					$all_s_start = date('Y-m-d', strtotime($pst_data['start']));
					if($pst_data['end'] != "0000-00-00"){
						$all_s_end = date('Y-m-d', strtotime($pst_data['end']));
					} else{
						$all_s_end = date('Y-m-d');
					}
					
					if(empty($patients_periods[$pst_data['ipid']]['days']))
					{
						$patients_periods[$pst_data['ipid']]['days'] = array();
					}
					
					$temp_all_sapv_days[$pst_data['ipid']] = $patientmaster->getDaysInBetween($all_s_start, $all_s_end);
					$patients_periods[$pst_data['ipid']]['days'] = array_merge($patients_periods[$pst_data['ipid']]['days'], $temp_all_sapv_days[$pst_data['ipid']]);
				}
				
			
				foreach($patient_ipids as $k=>$ipid){
					foreach($patients_periods[$ipid]['days'] as $std_key=>$std_day){
						if(!in_array($ipid,$patient_standby) && count($admission_days[$ipid]) == "1"){ // if patient is not curently standby - and has no other admission - then remove admission dates 
							if(in_array($std_day,$admission_days[$ipid]) || in_array($std_day[$ipid],$discharge_days[$ipid])){
								unset($patients_periods[$ipid]['days'][$std_key]);
							}
						}
					}
				}

				
				foreach($patient_ipids as $k=>$ipid){
					if(isset($patients_periods[$ipid]['days']) && !empty($patients_periods[$ipid]['days'])){
						
						foreach($patients_periods_active[$ipid]['days'] as $k=>$act_day){
							if(in_array($act_day,$patients_periods[$ipid]['days'])){
								unset($patients_periods_active[$ipid]['days'][$k]);
							}
						}
					}
				}
				
				
				//TODO-1617 ISPC:: Can not create invoice
				foreach($patient_ipids as $k=>$ipid){
				    foreach($patients_initial_periods_active[$ipid]['days'] as $k=>$day){
				        // TODO-2735 Ancuta 10.12.2019 - changed, if period requested - then only add if date in the requested period 
				        if($periods && !empty($all_period_days)){
        				    if( in_array($day,$all_period_days) && in_array($day,$admission_days[$ipid]) && in_array($day,$discharge_days[$ipid]) && ! in_array($day,$patients_periods_active[$ipid]['days'])){
        				        $patients_periods_active[$ipid]['days'][] = $day;
        				    }
				            
				        } else{
        				    if(in_array($day,$admission_days[$ipid]) && in_array($day,$discharge_days[$ipid]) && ! in_array($day,$patients_periods_active[$ipid]['days'])){
        				        $patients_periods_active[$ipid]['days'][] = $day;
        				    }
				        }
				    }
				}
				// ---
				foreach($patients_Arr as $k=>$pdata){
					if(empty($patients_periods_active[$pdata['ipid']]['days'])){
						unset($patients_Arr[$k]);
					}
				}
			}
			
// 			return $q->fetchArray();
			return $patients_Arr;
		}

		public static function patients_days($condition = array(), $select = '')
		{
			$select .= 'a.id AS aid,st.id AS stid, l.id AS lid, d.id AS did, e.*,p.*,a.*,st.*,l.*,s.*,d.*'; //add some magic to whatever we're selecting
			
			$periods_days = array();
			if($condition['periods'])
			{
				foreach($condition['periods'] as $period)
				{
					if(empty($period['end']))
					{
						$period['end'] = date('Y-m-d', strtotime('today midnight'));
					}

					$periods_stop[] = strtotime($period['end']);

					//save all period days into array, use to "cut" locations, etc
					$periods_days = array_merge($periods_days, Pms_CommonData::generateDateRangeArray($period['start'], $period['end']));

					$sql_active .= ' OR ((a.start BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (a.end BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (a.start <= "' . $period['start'] . '" AND (a.end = "0000-00-00" OR a.end >= "' . $period['end'] . '")))';
					$sql_locations .= ' OR ((date(l.valid_from) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (date(l.valid_till) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (date(l.valid_from) <= "' . $period['start'] . '" AND (date(l.valid_till) = "0000-00-00" OR date(l.valid_till) >= "' . $period['end'] . '")))';
					$sql_discharge .= ' OR (date(d.discharge_date) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '")';
					$sql_sapv .= ' OR (((date(s.verorddisabledate) = "0000-00-00") OR (s.verorddisabledate >= s.verordnungbis) ) AND ( date(s.verordnungam) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (date(s.verordnungbis) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (date(s.verordnungam) <= "' . $period['start'] . '" AND (date(s.verordnungbis) = "0000-00-00" OR date(s.verordnungbis) >= "' . $period['end'] . '"))';
					$sql_sapv .= ' OR (((date(s.verorddisabledate) != "0000-00-00") AND (s.verorddisabledate < s.verordnungbis) ) AND ( date(s.verordnungam) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (date(s.verorddisabledate) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (date(s.verordnungam) <= "' . $period['start'] . '" AND (date(s.verorddisabledate) = "0000-00-00" OR date(s.verorddisabledate) >= "' . $period['end'] . '"))))';
					$sql_standby_q .= ' OR ((st.start BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (st.end BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (st.start <= "' . $period['start'] . '" AND (st.end = "0000-00-00" OR st.end >= "' . $period['end'] . '")))';
				}

				$sql_active = ' AND (' . substr($sql_active, 3) . ') ';
				$sql_locations = ' AND l.isdelete = "0" AND (' . substr($sql_locations, 3) . ') ';
				$sql_discharge = ' AND d.isdelete = "0" AND (' . substr($sql_discharge, 3) . ') ';
				$sql_sapv = ' AND s.isdelete = "0" AND (' . substr($sql_sapv, 3) . ') ';
				
				
				if($condition['include_standby'])
				{
					$sql_standby_q = ' AND (' . substr($sql_standby_q, 3) . ') ';
				}
				else
				{
					$sql_standby_q = ' AND (' . substr($sql_standby_q, 4) . ') ';
				}
				
			}
			else
			{
				$sql_active = '';
				$sql_locations = '';
				$sql_discharge = '';
				$sql_sapv = '';
				
				$sql_standby_q = '';
			}

			// all "open" locations stop here or "today"
			$period_stop = max($periods_stop);
			if($period_stop > time())
			{
				$period_stop = strtotime('today midnight');
			}


			if($condition['client'] && is_numeric($condition['client']))
			{
				$sql_client = 'e.clientid = "' . $condition['client'] . '"';

				//grab client specific stuff
				$locations_type = Locations::getLocations($condition['client'], 3);
				$discharge_methods = DischargeMethod::getDischargeMethod($condition['client'], 3);
				$client_settings = ClientHospitalSettings::getClientSetting($condition['client']);
// 			var_dump($client_settings);
			}
			else
			{
				$sql_client = '1';
			}

			$death_methods = array('tod', 'verstorben', 'todna');

			if($condition['ipids'])
			{
				foreach($condition['ipids'] as $ipid)
				{
					$sql_ipids .= '"' . $ipid . '",';
				}

				$sql_ipids = 'e.ipid IN (' . substr($sql_ipids, 0, -1) . ')';
			}
			else
			{
				$sql_ipids = '1';
			}

			if($condition['include_standby'])
			{
				$sql_standby = '1';
			}
			else
			{
				$sql_standby = 'p.isstandby = 0';
			}

			$q = Doctrine_Query::create()
				->select($select)
// 				->select('*')
				->from('EpidIpidMapping e INDEXBY e.ipid')
				->leftJoin('e.PatientMaster p')
				->leftJoin('e.PatientActive a ON a.ipid = e.ipid' . $sql_active . ' INDEXBY a.id')
				->leftJoin('e.PatientStandby st ON st.ipid = e.ipid' . $sql_standby_q . ' INDEXBY st.id')
				->leftJoin('e.PatientLocation l ON l.ipid = e.ipid' . $sql_locations . ' INDEXBY l.id')
				->leftJoin('e.PatientDischarge d ON d.ipid = e.ipid' . $sql_discharge . ' INDEXBY d.id')
				->leftJoin('e.SapvVerordnung s ON s.ipid = e.ipid' . $sql_sapv . ' INDEXBY s.id')
				->where($sql_client)
				->andWhere($sql_ipids)
				->andWhere('a.ipid IS NOT NULL')
				->andWhere('p.isdelete = 0')
				->andWhere($sql_standby)
				->andWhere('p.isstandbydelete = 0')
				->orderBy('e.ipid ASC');
                if($condition['limit'] && is_numeric($condition['limit']))
    			{
    			   $q->limit($condition['limit']);
    			}
// 			echo $q->getDql();
// 			echo $q->getSqlQuery();  
// 			 exit;
			
			
			$patients = $q->fetchArray();

			
			if(!empty($patients))
			{
			    
			    // ISPC-1948 holiday location
			    //1 =  hospital
			    //7 = palliativstation
			    //11 = holliday
			    $hospital_peers = array('1','7','11');
			    // -- 
			    
			    
			    //location types
			    $types = Locations::getLocationTypes();
			    
				foreach($patients as $patient)
				{
					$standby_periods = array();
					$standby_days_full = array();
					$standby_days = array();
					$standby_admission_days = array();
					$standby_discharge_days = array();
						
					if(!empty($patient['PatientStandby']) && is_array($patient['PatientStandby']))
					{
						foreach($patient['PatientStandby'] as $standby_id => $standby_record)
						{
							$start_timestamp_st = strtotime($standby_record['start']);
							$end_timestamp = strtotime($standby_record['end']);
							$standby_periods[$standby_id] = array(
									'start' => date('d.m.Y', $start_timestamp_st),
									'end' => date('d.m.Y', ($standby_record['end'] != '0000-00-00' ? $end_timestamp : $period_stop))
							);
								
							if($standby_record['end'] != '0000-00-00')
							{
								$standby_discharge_days[$standby_id] = date('d.m.Y', $end_timestamp);
							}
							$standby_days_full = array_merge($standby_days_full, Pms_CommonData::generateDateRangeArray($standby_record['start'], $standby_periods[$standby_id]['end']));
								$standby_admission_days[] = date('d.m.Y', $start_timestamp_st);
						}
					}
					
					foreach($standby_days_full as $st_key=>$st_day){
// 						if(!in_array($st_day,$active_days)){
							$standby_days[] = $st_day;
// 						}
					}
							
					
					
					
					$active_periods = array();
					$active_days = array();
					$active_days_full = array();
					$admission_days = array();
					$discharge_days = array();
					if(!empty($patient['PatientActive']) && is_array($patient['PatientActive']))
					{
						foreach($patient['PatientActive'] as $active_id => $active_record)
						{
							$start_timestamp = strtotime($active_record['start']);
							$end_timestamp = strtotime($active_record['end']);
							$active_periods[$active_id] = array(
								'start' => date('d.m.Y', $start_timestamp),
								'end' => date('d.m.Y', ($active_record['end'] != '0000-00-00' ? $end_timestamp : $period_stop))
							);
							if($active_record['end'] != '0000-00-00')
							{
								$discharge_days[$active_id] = date('d.m.Y', $end_timestamp);
							}
// 							$active_days = array_merge($active_days, Pms_CommonData::generateDateRangeArray($active_record['start'], $active_periods[$active_id]['end']));
							$active_days_full = array_merge($active_days_full, Pms_CommonData::generateDateRangeArray($active_record['start'], $active_periods[$active_id]['end']));

//							if(count($active_days)>'300')
//							{
//								//write log
//								$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
//								$log = new Zend_Log($writer);
//								$log->info("Huge data patient possible year 1970! \n".serialize($active_record)."\n\n");
//							}
							$admission_days[] = date('d.m.Y', $start_timestamp);
						}
//						//write log
//						$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
//						$log = new Zend_Log($writer);
//						$log->info("\n\n ===================================================================================================================== \n\n");
					}

					
					$hospital_admission_days = array();
					$hospital_first_days = array();
					$hospital_days = array();
					$hospital_discharge_days = array();


					$hospiz_admission_days = array();
					$hospiz_first_days = array();
					$hospiz_days = array();
					$hospiz_discharge_days = array();

					$locations = array();
					$hospital_locations = array();
					$hospiz_locations = array();

					$discharge_location_starts = array();
					$location_starts_not_hs = array();
					$location_starts_not_hz = array();
					
					$location_order = array();

					if(!empty($patient['PatientLocation']) && is_array($patient['PatientLocation']))
					{
						foreach($patient['PatientLocation'] as $location_id => $location_record)
						{
							$location_days = array();

							$location_start_timestamp = strtotime($location_record['valid_from']);
							$location_end_timestamp = strtotime($location_record['valid_till']);

							//ISPC-2100 Carmen 28.10.2020
							if(substr($location_record['location_id'], 0, 4) == '8888')
							{
								$location_type = '6';
							}
							else 
							{
								$location_type = $locations_type[$location_record['location_id']];
							}
							//--

							$location_start = date('d.m.Y', $location_start_timestamp);
							$location_end = date('d.m.Y', ($location_record['valid_till'] != '0000-00-00 00:00:00' ? $location_end_timestamp : $period_stop));
							$location_days = array_merge($location_days, Pms_CommonData::generateDateRangeArray($location_start, $location_end));

							$locations[$location_id] = array(
								'type' => $location_type,
								'type_description' => $types[$location_type],
								'period' => array(
									'start' => $location_start,
									'end' => $location_end,
								),
								'days' => $location_days,
								'hospdoc' => $location_record['hospdoc'],
								'reason' => $location_record['reason'],
								'reason_txt' => $location_record['reason_txt'],
								'transport' => $location_record['transport'],
								'location_id' => $location_record['location_id'],
								'discharge_location' => $location_record['discharge_location'],
								'valid_till' => $location_record['valid_till']
							);
							
							foreach($location_days as $k=>$ld){
							    $days2_location[$ld][] = $location_id;
							}
							
							
							if($location_record['discharge_location'] == '1'){
    							$discharge_location_starts[] = $location_start; 
							}
							
							if( ! in_array($location_type,$hospital_peers) ){            //ISPC-1948 Lore 21.08.2020
							        $location_starts_not_hs[] = $location_start; 
							}
							
							if($location_type != '2'){
    							$location_starts_not_hz[] = $location_start; 
							}
							
  							$location_order[strtotime($location_start).$location_id] = $location_id; 
							
							
							if( in_array ( $location_type,$hospital_peers) )             //ISPC-1948 Lore 21.08.2020
							{ // we have hospital or palliativestation
								$hospital_days = array_merge($hospital_days, $location_days);
								if(in_array($location_start, $admission_days))
								{
									$hospital_first_days[$location_id] = $location_start;
								}
								$hospital_admission_days[$location_id] = $location_start;
								if($location_record['valid_till'] != '0000-00-00 00:00:00')
								{
									$hospital_discharge_days[$location_id] = $location_end;
								}
								
								$hospital_locations[] =  $location_id;
							}

							if($location_type == '2')
							{ // we have hospiz
								$hospiz_days = array_merge($hospiz_days, $location_days);
								if(in_array($location_start, $admission_days))
								{
									$hospiz_first_days[$location_id] = $location_start;
								}
								$hospiz_admission_days[$location_id] = $location_start;
								if($location_record['valid_till'] != '0000-00-00 00:00:00')
								{
									$hospiz_discharge_days[$location_id] = $location_end;
								}
								
								$hospiz_locations[] =  $location_id;
							}
						}
						
						ksort($location_order);
					}
 
					$discharge_dead_days = array();
					if(!empty($patient['PatientDischarge']) && is_array($patient['PatientDischarge']))
					{
						foreach($patient['PatientDischarge'] as $discharge_id => $discharge_record)
						{
							if(in_array(strtolower($discharge_methods[$discharge_record['discharge_method']]), $death_methods))
							{ //we have discharge dead
								$discharge_dead_days[$discharge_id] = date('d.m.Y', strtotime($discharge_record['discharge_date']));
							}
						}
					}

					//group everything for the final array before applying client settings

					$final_patient[$patient['ipid']]['details'] = $patient['PatientMaster'];
					$final_patient[$patient['ipid']]['patient_active'] = $patient['PatientActive'];
					$final_patient[$patient['ipid']]['details']['epid'] = $patient['epid'];
					$final_patient[$patient['ipid']]['details']['epid_num'] = $patient['epid_num']; // ISPC-2193
					$final_patient[$patient['ipid']]['details']['clientid'] = $patient['clientid']; // ISPC-2193
					$final_patient[$patient['ipid']]['admission_days'] = $admission_days;
					if($discharge_days)
					{
					    $final_patient[$patient['ipid']]['discharge'] = $discharge_days;
					}
					
					if( is_array($patient['PatientDischarge']))
					{
					    $final_patient[$patient['ipid']]['discharge_details'] = $patient['PatientDischarge'];
					}
					
					foreach($standby_days as $std_key=>$std_day){
						if(in_array($std_day,$admission_days) || in_array($std_day,$discharge_days)){
							unset($standby_days[$std_key]);
						}
					}

					foreach($active_days_full as $st_key=>$st_day){
						if(!in_array($st_day,$standby_days)){
							$active_days[] = $st_day;
						}
					}
					
					
					$final_patient[$patient['ipid']]['active_periods'] = $active_periods;
					$final_patient[$patient['ipid']]['active_days'] = $active_days;
					$final_patient[$patient['ipid']]['real_active_days'] = array_unique(array_intersect($active_days, $periods_days)); //only active days in period
					
					$final_patient[$patient['ipid']]['standby_periods'] = $standby_periods;
					$final_patient[$patient['ipid']]['standby_days'] = $standby_days;
					$final_patient[$patient['ipid']]['real_standby_days'] = array_unique(array_intersect($standby_days, $periods_days)); //only active days in period

					
					$final_patient[$patient['ipid']]['locations'] = $locations;

					if($hospital_days)
					{
						$final_patient[$patient['ipid']]['hospital']['days'] = $hospital_days;
						$final_patient[$patient['ipid']]['hospital']['admission'] = $hospital_admission_days;
						$final_patient[$patient['ipid']]['hospital']['discharge'] = $hospital_discharge_days;
						$final_patient[$patient['ipid']]['hospital']['first_admission_days'] = $hospital_first_days;
					}

					if($hospiz_days)
					{
						$final_patient[$patient['ipid']]['hospiz']['days'] = $hospiz_days;
						$final_patient[$patient['ipid']]['hospiz']['admission'] = $hospiz_admission_days;
						$final_patient[$patient['ipid']]['hospiz']['discharge'] = $hospiz_discharge_days;
						$final_patient[$patient['ipid']]['hospiz']['first_admission_days'] = $hospiz_first_days;
					}


					if($discharge_dead_days)
					{
						$final_patient[$patient['ipid']]['discharge_dead'] = $discharge_dead_days;
					}

					
					//added 19.03.2018 to try and suppress
					//PHP Warning:  array_unique() expects parameter 1 to be array, null given
					if ( ! isset($final_patient[$patient['ipid']]['hospital']['days'])) {
					    $final_patient[$patient['ipid']]['hospital']['days'] = array();
					}
					if ( ! isset($final_patient[$patient['ipid']]['real_active_days'])) {
					    $final_patient[$patient['ipid']]['real_active_days'] = array();
					}
					//"cut" hospital & hospiz days
					$final_patient[$patient['ipid']]['hospital']['real_days'] = array_unique(array_intersect($final_patient[$patient['ipid']]['hospital']['days'], $final_patient[$patient['ipid']]['real_active_days'])); //only hospital days in period and active
					
					//added 19.03.2018 to try and suppress
					//PHP Notice:  Undefined index: hospiz
					//PHP Warning:  array_intersect(): Argument #1 is not an array
				    if ( ! isset($final_patient[$patient['ipid']]['hospiz']['days'])) {
					    $final_patient[$patient['ipid']]['hospiz']['days']= array();
					}
					if( ! isset($final_patient[$patient['ipid']]['real_active_days'])) {
					    $final_patient[$patient['ipid']]['real_active_days'] = array();
					}
					$final_patient[$patient['ipid']]['hospiz']['real_days'] = array_unique(array_intersect($final_patient[$patient['ipid']]['hospiz']['days'], $final_patient[$patient['ipid']]['real_active_days'])); //only hospital days in period and active
					
					
					//start applying client specific settings
					$hospital_add = array();
					$hospital_remove = array();
					$hospiz_add = array();
					$hospiz_remove = array();
					$treatment_add = array();
					$treatment_remove = array();

					if($client_settings)
					{


						//hospital first days - app admission in hospital
						if(!empty($hospital_first_days))
						{
							foreach($hospital_first_days as $location_id => $hospital_fd)
							{
								//check if the hospital FIRST admission day is NOT also a hospital/discharge day 
								if(!in_array($hospital_fd, $hospital_discharge_days) && !in_array($hospital_fd, $hospiz_discharge_days) )
								{
									if($client_settings['hosp_first_day'] == 'tr')
									{ //hospital admission is treatment day
										$treatment_add[] = $hospital_fd;
										$hospital_remove[] = $hospital_fd;
									}
									else
									{ //hospital admission is NOT treatment day
										$treatment_remove[] = $hospital_fd;
										$hospital_add[] = $hospital_fd;
									}
								}
							}
						}


						//hospital admission //  transfer to hospital
						if(!empty($hospital_admission_days))
						{
							foreach($hospital_admission_days as $location_id => $hospital_adm)
							{
								//check if the hospital admission day is NOT also a hospital/discharge day
								if(!in_array($hospital_adm, $hospital_discharge_days) && !in_array($hospital_adm, $hospiz_discharge_days) && !in_array($hospital_adm, $hospital_first_days))
								{
									if($client_settings['hosp_adm'] == 'tr')
									{ //hospital admission is treatment day
										$treatment_add[] = $hospital_adm;
   										$hospital_remove[] = $hospital_adm;
									}
									else
									{ //hospital admission is NOT treatment day
										$treatment_remove[] = $hospital_adm;
										$hospital_add[] = $hospital_adm;
									}
								}
							}
						}


						//hospital discharge
						if(!empty($hospital_discharge_days))
						{
							foreach($hospital_discharge_days as $location_id => $hospital_dis)
							{

								//check if the hospital discharge day is NOT also a hospital/hospiz admission day but not for the same location
								if(
									(!in_array($hospital_dis, $hospital_admission_days) || array_search($hospital_dis, $hospital_admission_days) == $location_id || !in_array($hospital_dis, $hospital_first_days) || array_search($hospital_dis, $hospital_first_days) == $location_id) 
								    && !in_array($hospital_dis, $hospiz_admission_days)
								    && !(in_array($hospital_dis, $hospital_admission_days) && array_search($hospital_dis, $hospital_admission_days) != $location_id)
								    )
								{
									if($client_settings['hosp_dis'] == 'tr')
									{ //hospital discharge is treatment day
										$treatment_add[] = $hospital_dis;
										$hospital_remove[] = $hospital_dis;
									}
									else
									{ //hospital discharge is NOT treatment day
										$treatment_remove[] = $hospital_dis;
										$hospital_add[] = $hospital_dis;
									}
									//hospital discharge/hospital admission
								}
								elseif(in_array($hospital_dis, $hospital_admission_days) && array_search($hospital_dis, $hospital_admission_days) != $location_id)
								{
									if($client_settings['hosp_dis_hosp_adm'] == 'tr')
									{ //hospital discharge/hospital admission is treatment day
										$treatment_add[] = $hospital_dis;
										$hospital_remove[] = $hospital_dis;
									}
									else
									{
										$treatment_remove[] = $hospital_dis;
										$hospital_add[] = $hospital_dis;
									}
									//hospital discharge/hospiz admission
								}
								elseif(in_array($hospital_dis, $hospiz_admission_days))
								{
									if($client_settings['hosp_dis_hospiz_adm'] == 'tr')
									{ //hospital discharge/hospiz admission is treatment day
										$treatment_add[] = $hospital_dis;
										$hospital_remove[] = $hospital_dis;
										$hospiz_remove[] = $hospital_dis;
									}
									elseif($client_settings['hosp_dis_hospiz_adm'] == 'hz')
									{ //hospital discharge/hospiz admission is hospiz
										$treatment_remove[] = $hospital_dis;
										$hospital_remove[] = $hospital_dis;
										$hospiz_add[] = $hospital_dis;
									}
									else
									{
										$treatment_remove[] = $hospital_dis;
										$hospital_add[] = $hospital_dis;
										$hospiz_remove[] = $hospital_dis;
									}
								}
							}
						}
// 					var_dump($hospiz_add);
						//hospital day
						if(!empty($hospital_days))
						{
							foreach($hospital_days as $hospital_day)
							{
								//check if hospital day is not patient discharge, hospital admission/discharge, hospiz admission/discharge
								if(!in_array($hospital_day, $hospital_admission_days) &&
									!in_array($hospital_day, $hospital_first_days) &&
									!in_array($hospital_day, $hospital_discharge_days) &&
									!in_array($hospital_day, $hospiz_admission_days) &&
									!in_array($hospital_day, $hospiz_discharge_days) &&
									!in_array($hospital_day, $hospiz_first_days) &&
									!in_array($hospital_day, $discharge_days) 
									
								)
								{

									if($client_settings['hosp_day'] == 'tr')
									{ //hospital day is treatment day
										if(!in_array($hospital_day, $treatment_remove))
										{
											$treatment_add[] = $hospital_day;
										}
										if(!in_array($hospital_day, $hospital_add))
										{
											$hospital_remove[] = $hospital_day;
										}
									}
									else
									{ //hospital day is NOT treatment day
										if(!in_array($hospital_day, $treatment_add))
										{
											$treatment_remove[] = $hospital_day;
										}
										if(!in_array($hospital_day, $hospital_remove))
										{
											$hospital_add[] = $hospital_day;
										}
									}
								}
								elseif(in_array($hospital_day, $discharge_days))
								{
									//var_dump(array_search($hospital_day, $treatment_remove));
									if(in_array($hospital_day, $discharge_dead_days))
									{ //discharge dead
										if($client_settings['hosp_pat_dead'] == 'tr')
										{ //hospital patient discharge dead day is treatment day
											$treatment_add[] = $hospital_day;
											$hospital_remove[] = $hospital_day;

											if(array_search($hospital_day, $hospital_add) !== false)
											{
												unset($hospital_add[array_search($hospital_day, $hospital_add)]);
											}

											if(array_search($hospital_day, $treatment_remove) !== false)
											{
												unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
											}
										}
										else
										{
										    // If it is considered hospital day - apply new conditions
										    if($client_settings['hosp_pat_dead_final'] == '0')   
										    {
										        if(in_array($hospital_day,$location_starts_not_hs)  && !in_array(end($location_order),$hospital_locations)) 
										        { // this means that another location starts on the same day and hospital is not the last location  

										            if(!in_array($hospital_day, $hospital_admission_days) && !in_array($hospital_day, $hospital_first_days)  && !in_array($hospital_day, $hospiz_admission_days) && !in_array($hospital_day, $discharge_location_starts) )
										            {
										                if($client_settings['hosp_dis'] == 'tr')
										                { //hospital discharge is treatment day
										                    
										                    $treatment_add[] = $hospital_day;
										                    $hospital_remove[] = $hospital_day;
										                    
										                    if(array_search($hospital_day, $hospital_add) !== false)
										                    {
										                        unset($hospital_add[array_search($hospital_day, $hospital_add)]);
										                    }
										                    
										                    if(array_search($hospital_day, $treatment_remove) !== false)
										                    {
										                        unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
										                    }
										                    
										                }
										                else
										                { //hospital discharge is NOT treatment day
										                    $treatment_remove[] = $hospital_day;
										                    $hospital_add[] = $hospital_day;
										                    if(array_search($hospital_day, $hospital_remove) !== false)
										                    {
										                        unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
										                    }
										                    if(array_search($hospital_day, $treatment_add) !== false)
										                    {
										                        unset($treatment_add[array_search($hospital_day, $treatment_add)]);
										                    }
										                    
										                }
										                //hospital discharge/hospital admission
										            }
										            elseif(in_array($hospital_day, $hospiz_admission_days) && !in_array($hospital_day, $hospiz_discharge_days) && !in_array($hospital_day, $discharge_location_starts))
										            {
										                if($client_settings['hosp_dis_hospiz_adm'] == 'tr')
										                { //hospital discharge/hospiz admission is treatment day
										                    
										                    $treatment_add[] = $hospital_day;
										                    $hospital_remove[] = $hospital_day;
										                    $hospiz_remove[] = $hospital_day;
										                    
										                    if(array_search($hospital_day, $hospital_add) !== false)
										                    {
										                        unset($hospital_add[array_search($hospital_day, $hospital_add)]);
										                    }
										                    
										                    if(array_search($hospital_day, $treatment_remove) !== false)
										                    {
										                        unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
										                    }
										                }
										                elseif($client_settings['hosp_dis_hospiz_adm'] == 'hz')
										                { //hospital discharge/hospiz admission is hospiz

										                    $treatment_remove[] = $hospital_day;
										                    $hospital_remove[] = $hospital_day;
										                    $hospiz_add[] = $hospital_day;
										                    
										                    if(array_search($hospital_day, $treatment_add) !== false)
										                    {
										                        unset($treatment_add[array_search($hospital_day, $treatment_add)]);
										                    }
										                    if(array_search($hospital_day, $hospital_remove) !== false)
										                    {
										                        unset($hospital_add[array_search($hospital_day, $hospital_remove)]);
										                    }
										                }
										                else
										                {
										                    $treatment_remove[] = $hospital_day;
										                    $hospital_add[] = $hospital_day;
										                    $hospiz_remove[] = $hospital_dis;;
										                    
										                    if(array_search($hospital_day, $hospital_remove) !== false)
										                    {
										                        unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
										                    }
										                    if(array_search($hospital_day, $treatment_add) !== false)
										                    {
										                        unset($treatment_add[array_search($hospital_day, $treatment_add)]);
										                    }
										                }
										            } 
										            elseif(in_array($hospital_day, $discharge_location_starts))
										            {
										                if($client_settings['hosp_pat_dead'] == 'tr')
										                { //hospital patient discharge dead day is treatment day
                                                            $treatment_add[] = $hospital_day;
                                                            $hospital_remove[] = $hospital_day;
										                
    										                if(array_search($hospital_day, $hospital_add) !== false)
    										                {
    										                    unset($hospital_add[array_search($hospital_day, $hospital_add)]);
    										                }
    										                
    										                if(array_search($hospital_day, $treatment_remove) !== false)
    										                {
    										                    unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
    										                }
										                } else {
										            
                											$treatment_remove[] = $hospital_day;
                											$hospital_add[] = $hospital_day;
                											if(array_search($hospital_day, $hospital_remove) !== false)
                											{
                												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
                											}
                											if(array_search($hospital_day, $treatment_add) !== false)
                											{
                												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
                											}
                                                        }
										            }
										        } else {
										            
        											$treatment_remove[] = $hospital_day;
        											$hospital_add[] = $hospital_day;
        											if(array_search($hospital_day, $hospital_remove) !== false)
        											{
        												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
        											}
        											if(array_search($hospital_day, $treatment_add) !== false)
        											{
        												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
        											}
										        }
										    } 
										    else 
										    {   // calculate as before TODO-618
    											$treatment_remove[] = $hospital_day;
    											$hospital_add[] = $hospital_day;
    											if(array_search($hospital_day, $hospital_remove) !== false)
    											{
    												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
    											}
    											if(array_search($hospital_day, $treatment_add) !== false)
    											{
    												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
    											}
										    }
										}
									}
									else
									{
										if($client_settings['hosp_pat_dis'] == 'tr')
										{ //hospital patient discharge day is treatment day
											$treatment_add[] = $hospital_day;
											$hospital_remove[] = $hospital_day;
											if(array_search($hospital_day, $hospital_add) !== false)
											{
												unset($hospital_add[array_search($hospital_day, $hospital_add)]);
											}

											if(array_search($hospital_day, $treatment_remove) !== false)
											{
												unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
											}
										}
										else
										{
										    if($client_settings['hosp_pat_dis_final'] == '0')
										    {
										        if(in_array($hospital_day,$location_starts_not_hs) && !in_array(end($location_order),$hospital_locations)){  // this means that another location starts on the same day and hospital is not the last location  
										           
										            if(!in_array($hospital_day, $hospital_admission_days)&& !in_array($hospital_day, $hospital_first_days)  && !in_array($hospital_day, $hospiz_admission_days) && !in_array($hospital_day, $discharge_location_starts) )
										            {
										                if($client_settings['hosp_dis'] == 'tr')
										                { //hospital discharge is treatment day
										            
										                    $treatment_add[] = $hospital_day;
										                    $hospital_remove[] = $hospital_day;
										            
										                    if(array_search($hospital_day, $hospital_add) !== false)
										                    {
                                                                unset($hospital_add[array_search($hospital_day, $hospital_add)]);
                                                            }
    										                if(array_search($hospital_day, $treatment_remove) !== false)
    										                {
    										                   unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
    										                }
										                }
										                else
										                { //hospital discharge is NOT treatment day
										                
                                                            $treatment_remove[] = $hospital_day;
										                    $hospital_add[] = $hospital_day;
										                    
										                    if(array_search($hospital_day, $hospital_remove) !== false)
										                    {
										                      unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
        										            }
        										            
		          								            if(array_search($hospital_day, $treatment_add) !== false)
					                                        {
                                                                unset($treatment_add[array_search($hospital_day, $treatment_add)]);
                                                            }
										                }
										                //hospital discharge/hospital admission
                                                    }
								                    elseif(in_array($hospital_day, $hospiz_admission_days) && !in_array($hospital_day, $hospiz_discharge_days)  && !in_array($hospital_day, $discharge_location_starts) )
								                    {
									                    if($client_settings['hosp_dis_hospiz_adm'] == 'tr')
									                    { //hospital discharge/hospiz admission is treatment day
										            
									                        $treatment_add[] = $hospital_day;
									                        $hospital_remove[] = $hospital_day;
									                        $hospiz_remove[] = $hospital_day;
										            
								                            if(array_search($hospital_day, $hospital_add) !== false)
								                            {
								                                unset($hospital_add[array_search($hospital_day, $hospital_add)]);
										                    }
										            
										                    if(array_search($hospital_day, $treatment_remove) !== false)
									                        {
                                                                unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
										                    }
    								                    }
    						                            elseif($client_settings['hosp_dis_hospiz_adm'] == 'hz')
    						                            { //hospital discharge/hospiz admission is hospiz
        						            
        						                            $treatment_remove[] = $hospital_day;
        						                            $hospital_remove[] = $hospital_day;
        						                            $hospiz_add[] = $hospital_day;
        						            
        						                            if(array_search($hospital_day, $treatment_add) !== false)
        						                            {
        						                                unset($treatment_add[array_search($hospital_day, $treatment_add)]);
        						                            }
        						                            
        						                            if(array_search($hospital_day, $hospital_remove) !== false)
        						                            {
        						                                unset($hospital_add[array_search($hospital_day, $hospital_remove)]);
        						                            }
                                                        }
                                                        else
    										            {
                                                            $treatment_remove[] = $hospital_day;
                                                            $hospital_add[] = $hospital_day;
                                                            $hospiz_remove[] = $hospital_dis;;
                                                            
                                                            if(array_search($hospital_day, $hospital_remove) !== false)
                                                            {
                                                                unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
                                                            }
                                                            if(array_search($hospital_day, $treatment_add) !== false)
                                                            {
                                                                unset($treatment_add[array_search($hospital_day, $treatment_add)]);
                                                            }
                                                        }
                                                    }
                                                    elseif(in_array($hospital_day, $discharge_location_starts))
                                                    {
                                                        
                                                        if($client_settings['hosp_pat_dis'] == 'tr')
                                                        { //hospital patient discharge dead day is treatment day
                                                            $treatment_add[] = $hospital_day;
                                                            $hospital_remove[] = $hospital_day;
                                                    
                                                            if(array_search($hospital_day, $hospital_add) !== false)
                                                            {
                                                                unset($hospital_add[array_search($hospital_day, $hospital_add)]);
                                                            }
                                                    
                                                            if(array_search($hospital_day, $treatment_remove) !== false)
                                                            {
                                                                unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
                                                            }
                                                        } else {
                                                    
                                                            $treatment_remove[] = $hospital_day;
                                                            $hospital_add[] = $hospital_day;
                                                            if(array_search($hospital_day, $hospital_remove) !== false)
                                                            {
                                                                unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
                                                            }
                                                            if(array_search($hospital_day, $treatment_add) !== false)
                                                            {
                                                                unset($treatment_add[array_search($hospital_day, $treatment_add)]);
                                                            }
                                                        }
                                                    }
                                                    
										        } 
										        else
										        {
        											$treatment_remove[] = $hospital_day;
        											$hospital_add[] = $hospital_day;
        
        											if(array_search($hospital_day, $hospital_remove) !== false)
        											{
        												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
        											}
        											if(array_search($hospital_day, $treatment_add) !== false)
        											{
        												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
        											}
										        }
										        
										    }   
										    else
										    {
    											$treatment_remove[] = $hospital_day;
    											$hospital_add[] = $hospital_day;
    
    											if(array_search($hospital_day, $hospital_remove) !== false)
    											{
    												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
    											}
    											if(array_search($hospital_day, $treatment_add) !== false)
    											{
    												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
    											}
										    }
										}
									}
								}
							}
						}

						//hospital first days - app admission in hospital
						if(!empty($hospiz_first_days))
						{
							foreach($hospiz_first_days as $location_id => $hospiz_fd)
							{
								//check if the hospital admission day is NOT also a hospital/discharge day
								if(!in_array($hospiz_fd, $hospiz_discharge_days) && !in_array($hospiz_fd, $hospital_discharge_days))
								{
									if($client_settings['hospiz_first_day'] == 'tr')
									{ //hospiz admission is treatment day
										$treatment_add[] = $hospiz_fd;
										$hospiz_remove[] = $hospiz_fd;
									}
									else
									{ //hospital admission is NOT treatment day
										$treatment_remove[] = $hospiz_fd;
										$hospiz_add[] = $hospiz_fd;
									}
								}
							}
						}

						//hospiz admission // hospiz transfer
						if(!empty($hospiz_admission_days))
						{
							foreach($hospiz_admission_days as $location_id => $hospiz_adm)
							{
								//check if the hospital admission day is NOT also a hospital/discharge day
								if(!in_array($hospiz_adm, $hospiz_discharge_days) && !in_array($hospiz_adm, $hospital_discharge_days) && !in_array($hospiz_adm, $hospiz_first_days))
								{
									if($client_settings['hospiz_adm'] == 'tr')
									{ //hospiz admission is treatment day
										$treatment_add[] = $hospiz_adm;
    									$hospiz_remove[] = $hospiz_adm;
									}
									else
									{ //hospital admission is NOT treatment day
										$treatment_remove[] = $hospiz_adm;
										$hospiz_add[] = $hospiz_adm;
									}
								}
							}
						}


						//hospiz discharge
						if(!empty($hospiz_discharge_days))
						{
							foreach($hospiz_discharge_days as $location_id => $hospiz_dis)
							{
								//check if the hospiz discharge day is NOT also a hospital/hospiz admission day but not for the same location
								if((!in_array($hospiz_dis, $hospiz_admission_days) || array_search($hospiz_dis, $hospiz_admission_days) == $location_id || !in_array($hospiz_dis, $hospiz_first_days) || array_search($hospiz_dis, $hospiz_first_days) == $location_id ) && !in_array($hospiz_dis, $hospital_admission_days))
								{
									if($client_settings['hospiz_dis'] == 'tr')
									{ //hospiz discharge is treatment day
										$treatment_add[] = $hospiz_dis;
										$hospiz_remove[] = $hospiz_dis;
									}
									else
									{ //hospiz discharge is NOT treatment day
										$treatment_remove[] = $hospiz_dis;
										$hospiz_add[] = $hospiz_dis;
									}
									//hospiz discharge/hospiz admission
								}
								elseif(in_array($hospiz_dis, $hospiz_admission_days) && array_search($hospiz_dis, $hospiz_admission_days) != $location_id)
								{
									if($client_settings['hospiz_dis_hospiz_adm'] == 'tr')
									{ //hospiz discharge/hospiz admission is treatment day
										$treatment_add[] = $hospiz_dis;
										$hospz_remove[] = $hospiz_dis;
									}
									else
									{
										$treatment_remove[] = $hospiz_dis;
										$hospiz_add[] = $hospiz_dis;
									}
									//hospiz discharge/hospital admission
								}
								elseif(in_array($hospiz_dis, $hospital_admission_days))
								{
									if($client_settings['hospiz_dis_hosp_adm'] == 'tr')
									{ //hospiz discharge/hospital admission is treatment day
										$treatment_add[] = $hospiz_dis;
										$hospital_remove[] = $hospiz_dis;
										$hospiz_remove[] = $hospiz_dis;
									}
									elseif($client_settings['hospiz_dis_hosp_adm'] == 'hp')
									{ //hospiz discharge/hospital admission is hospital
										$treatment_remove[] = $hospiz_dis;
										$hospital_add[] = $hospiz_dis;
										$hospiz_remove[] = $hospiz_dis;
									}
									else
									{
										$treatment_remove[] = $hospiz_dis;
										$hospital_remove[] = $hospiz_dis;
										$hospiz_add[] = $hospiz_dis;
									}
								}
							}
						}

						//hospiz day
						if(!empty($hospiz_days))
						{
							foreach($hospiz_days as $hospiz_day)
							{
								//check if hospiz day is not patient discharge, hospital admission/discharge, hospiz admission/discharge
								if(!in_array($hospiz_day, $hospiz_admission_days) &&
									!in_array($hospiz_day, $hospiz_first_days) &&
									!in_array($hospiz_day, $hospiz_discharge_days) &&
									!in_array($hospiz_day, $hospital_admission_days) &&
									!in_array($hospiz_day, $hospital_first_days) &&
									!in_array($hospiz_day, $hospital_discharge_days) &&
									!in_array($hospiz_day, $discharge_days)
								)
								{

									if($client_settings['hospiz_day'] == 'tr')
									{ //hospiz day is treatment day
										if(!in_array($hospiz_day, $treatment_remove))
										{
											$treatment_add[] = $hospiz_day;
										}
										if(!in_array($hospiz_day, $hospital_add))
										{
											$hospiz_remove[] = $hospiz_day;
										}
									}
									else
									{ //hospiz day is NOT treatment day
										if(!in_array($hospiz_day, $treatment_add))
										{
											$treatment_remove[] = $hospiz_day;
										}
										if(!in_array($hospiz_day, $hospital_remove))
										{
											$hospiz_add[] = $hospiz_day;
										}
									}
								}
								elseif(in_array($hospiz_day, $discharge_days))
								{
									if(in_array($hospiz_day, $discharge_dead_days))
									{ //discharge dead
										if($client_settings['hospiz_pat_dead'] == 'tr')
										{ //hospiz patient discharge dead day is treatment day
											$treatment_add[] = $hospiz_day;
											$hospiz_remove[] = $hospiz_day;

											if(array_search($hospiz_day, $hospiz_add) !== false)
											{
												unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
											}

											if(array_search($hospiz_day, $treatment_remove) !== false)
											{
												unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
											}
										}
										else
										{
										    if($client_settings['hospiz_pat_dead_final'] == '0')
										    {
    										    if(in_array($hospiz_day,$location_starts_not_hz)  && !in_array(end($location_order),$hospiz_locations))
    										    { // this means that another location starts on the same day and hospiz is not the last location
    										        
    										        //check if the hospiz discharge day is NOT also a hospital/hospiz admission day but not for the same location
    										        if( !in_array($hospiz_day, $hospiz_admission_days) &&  !in_array($hospiz_day, $hospiz_first_days) && !in_array($hospiz_day, $hospital_admission_days) && !in_array($hospiz_day, $discharge_location_starts))
    										        {
    										            if($client_settings['hospiz_dis'] == 'tr')
    										            { //hospiz discharge is treatment day
    										                
    										                $treatment_add[] = $hospiz_day;
    										                $hospiz_remove[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $treatment_remove) !== false)
    										                {
    										                    unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospiz_add) !== false)
    										                {
    										                    unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
    										                }
    										                
    										            }
    										            else
    										            { //hospiz discharge is NOT treatment day
    										                
    										                $treatment_remove[] = $hospiz_day;
    										                $hospiz_add[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $treatment_add) !== false)
    										                {
    										                    unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospiz_remove) !== false)
    										                {
    										                    unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    										                }
    										            }
    										            //hospiz discharge/hospiz admission
    										        }
 
    										        elseif(in_array($hospiz_day, $hospital_admission_days)  && !in_array($hospiz_day, $hospital_discharge_days) && !in_array($hospiz_day, $discharge_location_starts))
    										        {
    										            if($client_settings['hospiz_dis_hosp_adm'] == 'tr')
    										            { //hospiz discharge/hospital admission is treatment day

    										                $treatment_add[] = $hospiz_day;
    										                $hospital_remove[] = $hospiz_day;
    										                $hospiz_remove[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $treatment_remove) !== false)
    										                {
    										                    unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospital_add) !== false)
    										                {
    										                    unset($hospital_add[array_search($hospiz_day, $hospital_add)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospiz_add) !== false)
    										                {
    										                    unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
    										                }
    										                
    										            }
    										            elseif($client_settings['hospiz_dis_hosp_adm'] == 'hp')
    										            { //hospiz discharge/hospital admission is hospital

    										                $treatment_remove[] = $hospiz_day;
    										                $hospital_add[] = $hospiz_day;
    										                $hospiz_remove[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $treatment_add) !== false)
    										                {
    										                    unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospital_remove) !== false)
    										                {
    										                    unset($hospital_remove[array_search($hospiz_day, $hospital_remove)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospiz_add) !== false)
    										                {
    										                    unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
    										                }
    										                
    										            }
    										            else
    										            {

    										                $hospiz_add[] = $hospiz_day;
    										                $treatment_remove[] = $hospiz_day;
    										                $hospital_remove[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $hospiz_remove) !== false)
    										                {
    										                    unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $treatment_add) !== false)
    										                {
    										                    unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    										                }

    										                if(array_search($hospiz_day, $hospital_remove) !== false)
    										                {
    										                    unset($hospital_add[array_search($hospiz_day, $hospital_remove)]);
    										                }
    										                
    										            }
    										        }
    										        elseif( in_array($hospiz_day, $discharge_location_starts))
    										        {
    										            if($client_settings['hospiz_pat_dead'] == 'tr')
    										            { //hospiz patient discharge dead day is treatment day
        										            $treatment_add[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										            
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										            
        										            if(array_search($hospiz_day, $treatment_remove) !== false)
        										            {
        										                unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
        										            }
    										            }
    										            else
    										            {
    										                $treatment_remove[] = $hospiz_day;
    										                $hospiz_add[] = $hospiz_day;
    										                if(array_search($hospiz_day, $hospiz_remove) !== false)
    										                {
    										                    unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    										                }
    										            
    										                if(array_search($hospiz_day, $treatment_add) !== false)
    										                {
    										                    unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    										                }
    										            }
    										            
    										        }
    										        
    										    }
    										    else
    										    {
        											$treatment_remove[] = $hospiz_day;
        											$hospiz_add[] = $hospiz_day;
        											if(array_search($hospiz_day, $hospiz_remove) !== false)
        											{
        												unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        											}
        
        											if(array_search($hospiz_day, $treatment_add) !== false)
        											{
        												unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        											}
    										    }
										    
										    }
										    else
										    {
										        // calculate as before TODO-618
    											$treatment_remove[] = $hospiz_day;
    											$hospiz_add[] = $hospiz_day;
    											if(array_search($hospiz_day, $hospiz_remove) !== false)
    											{
    												unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    											}
    
    											if(array_search($hospiz_day, $treatment_add) !== false)
    											{
    												unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    											}
										    }
										}
									}
									else
									{
										if($client_settings['hospiz_pat_dis'] == 'tr')
										{ //hospiz patient discharge day is treatment day
											$treatment_add[] = $hospiz_day;
											$hospiz_remove[] = $hospiz_day;

											if(array_search($hospiz_day, $hospiz_add) !== false)
											{
												unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
											}

											if(array_search($hospiz_day, $hospiz_add) !== false)
											{
												unset($treatment_remove[array_search($hospiz_day, $hospiz_add)]);
											}
										}
										else
    									{
    									    if($client_settings['hospiz_pat_dis_final'] == '0')
    									    {
    										    if(in_array($hospiz_day,$location_starts_not_hz)  && !in_array(end($location_order),$hospiz_locations))
    										    { // this means that another location starts on the same day and hospiz is not the last location
    										    
        										    //check if the hospiz discharge day is NOT also a hospital/hospiz admission day but not for the same location
        										    if( !in_array($hospiz_day, $hospiz_admission_days) &&  !in_array($hospiz_day, $hospiz_first_days) && !in_array($hospiz_dis, $hospital_admission_days) && !in_array($hospiz_day, $discharge_location_starts))
        										    {
        										        if($client_settings['hospiz_dis'] == 'tr')
        										        { //hospiz discharge is treatment day
        										    
        										            $treatment_add[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $treatment_remove) !== false)
        										            {
        										                unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										    
        										        }
        										        else
        										        { //hospiz discharge is NOT treatment day
        										    
        										            $treatment_remove[] = $hospiz_day;
        										            $hospiz_add[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $treatment_add) !== false)
        										            {
        										                unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospiz_remove) !== false)
        										            {
        										                unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        										            }
        										        }
        										        //hospiz discharge/hospiz admission
        										    }
        										    elseif(in_array($hospiz_day, $hospital_admission_days) && !in_array($hospiz_day, $hospital_discharge_days)  && !in_array($hospiz_day, $discharge_location_starts))
        										    {
        										        
        										        if($client_settings['hospiz_dis_hosp_adm'] == 'tr')
        										        { //hospiz discharge/hospital admission is treatment day
        										    
        										            $treatment_add[] = $hospiz_day;
        										            $hospital_remove[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $treatment_remove) !== false)
        										            {
        										                unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospital_add) !== false)
        										            {
        										                unset($hospital_add[array_search($hospiz_day, $hospital_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										    
        										        }
        										        elseif($client_settings['hospiz_dis_hosp_adm'] == 'hp')
        										        { //hospiz discharge/hospital admission is hospital
        										    
        										            $treatment_remove[] = $hospiz_day;
        										            $hospital_add[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $treatment_add) !== false)
        										            {
        										                unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospital_remove) !== false)
        										            {
        										                unset($hospital_remove[array_search($hospiz_day, $hospital_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										    
        										        }
        										        else
        										        {
        										    
        										            $hospiz_add[] = $hospiz_day;
        										            $treatment_remove[] = $hospiz_day;
        										            $hospital_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $hospiz_remove) !== false)
        										            {
        										                unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $treatment_add) !== false)
        										            {
        										                unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospital_remove) !== false)
        										            {
        										                unset($hospital_add[array_search($hospiz_day, $hospital_remove)]);
        										            }
        										        }
        										    }
        										    elseif( in_array($hospiz_day, $discharge_location_starts))
        										    {
        										        if($client_settings['hospiz_pat_dis'] == 'tr')
        										        { //hospiz patient discharge dead day is treatment day
        										            $treatment_add[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $treatment_remove) !== false)
        										            {
        										                unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
        										            }
        										        }
        										        else
        										        {
        										            $treatment_remove[] = $hospiz_day;
        										            $hospiz_add[] = $hospiz_day;
        										            if(array_search($hospiz_day, $hospiz_remove) !== false)
        										            {
        										                unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $treatment_add) !== false)
        										            {
        										                unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        										            }
        										        }
        										    
        										    }
        										    
    										    }										    
    										    else
    										    {
        											$treatment_remove[] = $hospiz_day;
        											$hospiz_add[] = $hospiz_day;
        
        											if(array_search($hospiz_day, $hospiz_remove) !== false)
        											{
        												unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        											}
        
        											if(array_search($hospiz_day, $treatment_add) !== false)
        											{
        												unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        											}
    										    }
                                            }
                                            else
                                            {
    											$treatment_remove[] = $hospiz_day;
    											$hospiz_add[] = $hospiz_day;
    
    
    											if(array_search($hospiz_day, $hospiz_remove) !== false)
    											{
    												unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    											}
    
    											if(array_search($hospiz_day, $treatment_add) !== false)
    											{
    												unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    											}
                                            }	    
    									}
									}
								}
							}
						}
					}


					//we have the client settings arrays

					$hospital_add = array_unique($hospital_add);
					$hospital_remove = array_unique($hospital_remove);
					$hospiz_add = array_unique($hospiz_add);
					$hospiz_remove = array_unique($hospiz_remove);
					$treatment_add = array_unique($treatment_add);
					$treatment_remove = array_unique($treatment_remove);

					//cut "add" days to the active period

					$hospital_add = array_unique(@array_intersect($hospital_add, $final_patient[$patient['ipid']]['real_active_days']));
					$hospiz_add = array_unique(@array_intersect($hospiz_add, $final_patient[$patient['ipid']]['real_active_days']));
					$treatment_add = array_unique(@array_intersect($treatment_add, $final_patient[$patient['ipid']]['real_active_days']));


					//let's start appliying
					//first we add everything

					$final_patient[$patient['ipid']]['treatment_days'] = array_unique(array_merge($treatment_add, $final_patient[$patient['ipid']]['real_active_days']));
					
					if(!empty($final_patient[$patient['ipid']]['hospital']['real_days']))
					{
						$final_patient[$patient['ipid']]['hospital']['real_days_cs'] = array_unique(array_merge($hospital_add, $final_patient[$patient['ipid']]['hospital']['real_days']));
					}

					if(!empty($final_patient[$patient['ipid']]['hospiz']['real_days']))
					{
						$final_patient[$patient['ipid']]['hospiz']['real_days_cs'] = array_unique(array_merge($hospiz_add, $final_patient[$patient['ipid']]['hospiz']['real_days']));
					}

					//now let's substract and pray

					// remove standby details !!!!!!!!!!!!!!! ?????????????????????????????????
					$final_patient[$patient['ipid']]['treatment_days'] = array_unique(array_diff($final_patient[$patient['ipid']]['treatment_days'], $treatment_remove));
					$final_patient[$patient['ipid']]['hospital']['real_days_cs'] = array_unique(array_diff($final_patient[$patient['ipid']]['hospital']['real_days_cs'], $hospital_remove));
					$final_patient[$patient['ipid']]['hospiz']['real_days_cs'] = array_unique(array_diff($final_patient[$patient['ipid']]['hospiz']['real_days_cs'], $hospiz_remove));

					//count everything so the output can be used right away
					$final_patient[$patient['ipid']]['treatment_days_no'] = sizeof($final_patient[$patient['ipid']]['treatment_days']);
					$final_patient[$patient['ipid']]['real_active_days_no'] = sizeof($final_patient[$patient['ipid']]['real_active_days']);
					$final_patient[$patient['ipid']]['hospital']['real_days_cs_no'] = sizeof($final_patient[$patient['ipid']]['hospital']['real_days_cs']);
					$final_patient[$patient['ipid']]['hospiz']['real_days_cs_no'] = sizeof($final_patient[$patient['ipid']]['hospiz']['real_days_cs']);


// 				sort($final_patient[$patient['ipid']]['treatment_days']);
// 				sort($final_patient[$patient['ipid']]['hospital']['real_days_cs']);
// 				sort($final_patient[$patient['ipid']]['hospiz']['real_days_cs']);
				}
			}


//    			print_r($final_patient); exit;
// 			if($_REQUEST['spd'] =="1"){
//     			print_r($final_patient); exit;
// 			}
			return $final_patient;
		}
		
		
		
		public static function patients_days_before_standby($condition = array(), $select = '')
		{
			$select .= 'a.id AS aid, l.id AS lid, d.id AS did, e.*,p.*,a.*,l.*,s.*,d.*'; //add some magic to whatever we're selecting

			$periods_days = array();
			if($condition['periods'])
			{
				foreach($condition['periods'] as $period)
				{
					if(empty($period['end']))
					{
						$period['end'] = date('Y-m-d', strtotime('today midnight'));
					}

					$periods_stop[] = strtotime($period['end']);

					//save all period days into array, use to "cut" locations, etc
					$periods_days = array_merge($periods_days, Pms_CommonData::generateDateRangeArray($period['start'], $period['end']));

					$sql_active .= ' OR ((a.start BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (a.end BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (a.start <= "' . $period['start'] . '" AND (a.end = "0000-00-00" OR a.end >= "' . $period['end'] . '")))';
					$sql_locations .= ' OR ((date(l.valid_from) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (date(l.valid_till) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (date(l.valid_from) <= "' . $period['start'] . '" AND (date(l.valid_till) = "0000-00-00" OR date(l.valid_till) >= "' . $period['end'] . '")))';
					$sql_discharge .= ' OR (date(d.discharge_date) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '")';
					$sql_sapv .= ' OR (((date(s.verorddisabledate) = "0000-00-00") OR (s.verorddisabledate >= s.verordnungbis) ) AND ( date(s.verordnungam) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (date(s.verordnungbis) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (date(s.verordnungam) <= "' . $period['start'] . '" AND (date(s.verordnungbis) = "0000-00-00" OR date(s.verordnungbis) >= "' . $period['end'] . '"))';
					$sql_sapv .= ' OR (((date(s.verorddisabledate) != "0000-00-00") AND (s.verorddisabledate < s.verordnungbis) ) AND ( date(s.verordnungam) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (date(s.verorddisabledate) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (date(s.verordnungam) <= "' . $period['start'] . '" AND (date(s.verorddisabledate) = "0000-00-00" OR date(s.verorddisabledate) >= "' . $period['end'] . '"))))';
				}

				$sql_active = ' AND (' . substr($sql_active, 3) . ') ';
				$sql_locations = ' AND l.isdelete = "0" AND (' . substr($sql_locations, 3) . ') ';
				$sql_discharge = ' AND d.isdelete = "0" AND (' . substr($sql_discharge, 3) . ') ';
				$sql_sapv = ' AND s.isdelete = "0" AND (' . substr($sql_sapv, 3) . ') ';
			}
			else
			{
				$sql_active = '';
				$sql_locations = '';
				$sql_discharge = '';
				$sql_sapv = '';
			}

			// all "open" locations stop here or "today"
			$period_stop = max($periods_stop);
			if($period_stop > time())
			{
				$period_stop = strtotime('today midnight');
			}


			if($condition['client'] && is_numeric($condition['client']))
			{
				$sql_client = 'e.clientid = "' . $condition['client'] . '"';

				//grab client specific stuff
				$locations_type = Locations::getLocations($condition['client'], 3);
				$discharge_methods = DischargeMethod::getDischargeMethod($condition['client'], 3);
				$client_settings = ClientHospitalSettings::getClientSetting($condition['client']);
// 			var_dump($client_settings);
			}
			else
			{
				$sql_client = '1';
			}

			$death_methods = array('tod', 'verstorben', 'todna');

			if($condition['ipids'])
			{
				foreach($condition['ipids'] as $ipid)
				{
					$sql_ipids .= '"' . $ipid . '",';
				}

				$sql_ipids = 'e.ipid IN (' . substr($sql_ipids, 0, -1) . ')';
			}
			else
			{
				$sql_ipids = '1';
			}

			if($condition['include_standby'])
			{
				$sql_standby = '1';
			}
			else
			{
				$sql_standby = 'p.isstandby = 0';
			}

			$q = Doctrine_Query::create()
				->select($select)
				->from('EpidIpidMapping e INDEXBY e.ipid')
				->leftJoin('e.PatientMaster p')
				->leftJoin('e.PatientActive a ON a.ipid = e.ipid' . $sql_active . ' INDEXBY a.id')
				->leftJoin('e.PatientLocation l ON l.ipid = e.ipid' . $sql_locations . ' INDEXBY l.id')
				->leftJoin('e.PatientDischarge d ON d.ipid = e.ipid' . $sql_discharge . ' INDEXBY d.id')
				->leftJoin('e.SapvVerordnung s ON s.ipid = e.ipid' . $sql_sapv . ' INDEXBY s.id')
				->where($sql_client)
				->andWhere($sql_ipids)
				->andWhere('a.ipid IS NOT NULL')
				->andWhere('p.isdelete = 0')
				->andWhere($sql_standby)
				->andWhere('p.isstandbydelete = 0')
				->orderBy('e.ipid ASC');
			//echo $q->getDql(); exit;
			//echo $q->getSqlQuery(); exit;
			
			
//or standby  = 1 and dattes non in standby details
			
			
			$patients = $q->fetchArray();

			if(!empty($patients))
			{

				foreach($patients as $patient)
				{
					$active_periods = array();
					$active_days = array();
					$admission_days = array();
					$discharge_days = array();
					if(!empty($patient['PatientActive']) && is_array($patient['PatientActive']))
					{
						foreach($patient['PatientActive'] as $active_id => $active_record)
						{
							$start_timestamp = strtotime($active_record['start']);
							$end_timestamp = strtotime($active_record['end']);
							$active_periods[$active_id] = array(
								'start' => date('d.m.Y', $start_timestamp),
								'end' => date('d.m.Y', ($active_record['end'] != '0000-00-00' ? $end_timestamp : $period_stop))
							);
							if($active_record['end'] != '0000-00-00')
							{
								$discharge_days[$active_id] = date('d.m.Y', $end_timestamp);
							}
							$active_days = array_merge($active_days, Pms_CommonData::generateDateRangeArray($active_record['start'], $active_periods[$active_id]['end']));

//							if(count($active_days)>'300')
//							{
//								//write log
//								$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
//								$log = new Zend_Log($writer);
//								$log->info("Huge data patient possible year 1970! \n".serialize($active_record)."\n\n");
//							}
							$admission_days[] = date('d.m.Y', $start_timestamp);
						}
//						//write log
//						$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
//						$log = new Zend_Log($writer);
//						$log->info("\n\n ===================================================================================================================== \n\n");
					}

					$hospital_admission_days = array();
					$hospital_first_days = array();
					$hospital_days = array();
					$hospital_discharge_days = array();


					$hospiz_admission_days = array();
					$hospiz_first_days = array();
					$hospiz_days = array();
					$hospiz_discharge_days = array();

					$locations = array();
					$hospital_locations = array();
					$hospiz_locations = array();

					$discharge_location_starts = array();
					$location_starts_not_hs = array();
					$location_starts_not_hz = array();
					
					$location_order = array();

					if(!empty($patient['PatientLocation']) && is_array($patient['PatientLocation']))
					{
						foreach($patient['PatientLocation'] as $location_id => $location_record)
						{
							$location_days = array();

							$location_start_timestamp = strtotime($location_record['valid_from']);
							$location_end_timestamp = strtotime($location_record['valid_till']);

							$location_type = $locations_type[$location_record['location_id']];

							$location_start = date('d.m.Y', $location_start_timestamp);
							$location_end = date('d.m.Y', ($location_record['valid_till'] != '0000-00-00 00:00:00' ? $location_end_timestamp : $period_stop));
							$location_days = array_merge($location_days, Pms_CommonData::generateDateRangeArray($location_start, $location_end));

							$locations[$location_id] = array(
								'type' => $location_type,
								'period' => array(
									'start' => $location_start,
									'end' => $location_end,
								),
								'days' => $location_days,
								'hospdoc' => $location_record['hospdoc'],
								'reason' => $location_record['reason'],
								'reason_txt' => $location_record['reason_txt'],
								'transport' => $location_record['transport'],
								'location_id' => $location_record['location_id']
							);
							
							foreach($location_days as $k=>$ld){
							    $days2_location[$ld][] = $location_id;
							}
							
							
							if($location_record['discharge_location'] == '1'){
    							$discharge_location_starts[] = $location_start; 
							}
							
							if($location_type != '1' && $location_type != '7'){
							        $location_starts_not_hs[] = $location_start; 
							}
							
							if($location_type != '2'){
    							$location_starts_not_hz[] = $location_start; 
							}
							
  							$location_order[strtotime($location_start).$location_id] = $location_id; 
							
							
							if($location_type == '1' || $location_type == '7')
							{ // we have hospital or palliativestation
								$hospital_days = array_merge($hospital_days, $location_days);
								if(in_array($location_start, $admission_days))
								{
									$hospital_first_days[$location_id] = $location_start;
								}
								$hospital_admission_days[$location_id] = $location_start;
								if($location_record['valid_till'] != '0000-00-00 00:00:00')
								{
									$hospital_discharge_days[$location_id] = $location_end;
								}
								
								$hospital_locations[] =  $location_id;
							}

							if($location_type == '2')
							{ // we have hospiz
								$hospiz_days = array_merge($hospiz_days, $location_days);
								if(in_array($location_start, $admission_days))
								{
									$hospiz_first_days[$location_id] = $location_start;
								}
								$hospiz_admission_days[$location_id] = $location_start;
								if($location_record['valid_till'] != '0000-00-00 00:00:00')
								{
									$hospiz_discharge_days[$location_id] = $location_end;
								}
								
								$hospiz_locations[] =  $location_id;
							}
						}
						
						ksort($location_order);
					}
 
					$discharge_dead_days = array();
					if(!empty($patient['PatientDischarge']) && is_array($patient['PatientDischarge']))
					{
						foreach($patient['PatientDischarge'] as $discharge_id => $discharge_record)
						{
							if(in_array(strtolower($discharge_methods[$discharge_record['discharge_method']]), $death_methods))
							{ //we have discharge dead
								$discharge_dead_days[$discharge_id] = date('d.m.Y', strtotime($discharge_record['discharge_date']));
							}
						}
					}

					//group everything for the final array before applying client settings

					$final_patient[$patient['ipid']]['details'] = $patient['PatientMaster'];
					$final_patient[$patient['ipid']]['patient_active'] = $patient['PatientActive'];
					$final_patient[$patient['ipid']]['details']['epid'] = $patient['epid'];
					$final_patient[$patient['ipid']]['admission_days'] = $admission_days;
					if($discharge_days)
					{
					    $final_patient[$patient['ipid']]['discharge'] = $discharge_days;
					}
					
					if( is_array($patient['PatientDischarge']))
					{
					    $final_patient[$patient['ipid']]['discharge_details'] = $patient['PatientDischarge'];
					}
					
					
					$final_patient[$patient['ipid']]['active_periods'] = $active_periods;
					$final_patient[$patient['ipid']]['active_days'] = $active_days;
					$final_patient[$patient['ipid']]['real_active_days'] = array_unique(array_intersect($active_days, $periods_days)); //only active days in period
					$final_patient[$patient['ipid']]['locations'] = $locations;

					if($hospital_days)
					{
						$final_patient[$patient['ipid']]['hospital']['days'] = $hospital_days;
						$final_patient[$patient['ipid']]['hospital']['admission'] = $hospital_admission_days;
						$final_patient[$patient['ipid']]['hospital']['discharge'] = $hospital_discharge_days;
						$final_patient[$patient['ipid']]['hospital']['first_admission_days'] = $hospital_first_days;
					}

					if($hospiz_days)
					{
						$final_patient[$patient['ipid']]['hospiz']['days'] = $hospiz_days;
						$final_patient[$patient['ipid']]['hospiz']['admission'] = $hospiz_admission_days;
						$final_patient[$patient['ipid']]['hospiz']['discharge'] = $hospiz_discharge_days;
						$final_patient[$patient['ipid']]['hospiz']['first_admission_days'] = $hospiz_first_days;
					}


					if($discharge_dead_days)
					{
						$final_patient[$patient['ipid']]['discharge_dead'] = $discharge_dead_days;
					}

					//"cut" hospital & hospiz days
					$final_patient[$patient['ipid']]['hospital']['real_days'] = array_unique(array_intersect($final_patient[$patient['ipid']]['hospital']['days'], $final_patient[$patient['ipid']]['real_active_days'])); //only hospital days in period and active
					$final_patient[$patient['ipid']]['hospiz']['real_days'] = array_unique(array_intersect($final_patient[$patient['ipid']]['hospiz']['days'], $final_patient[$patient['ipid']]['real_active_days'])); //only hospital days in period and active
					//start applying client specific settings
					$hospital_add = array();
					$hospital_remove = array();
					$hospiz_add = array();
					$hospiz_remove = array();
					$treatment_add = array();
					$treatment_remove = array();

					if($client_settings)
					{


						//hospital first days - app admission in hospital
						if(!empty($hospital_first_days))
						{
							foreach($hospital_first_days as $location_id => $hospital_fd)
							{
								//check if the hospital FIRST admission day is NOT also a hospital/discharge day 
								if(!in_array($hospital_fd, $hospital_discharge_days) && !in_array($hospital_fd, $hospiz_discharge_days) )
								{
									if($client_settings['hosp_first_day'] == 'tr')
									{ //hospital admission is treatment day
										$treatment_add[] = $hospital_fd;
										$hospital_remove[] = $hospital_fd;
									}
									else
									{ //hospital admission is NOT treatment day
										$treatment_remove[] = $hospital_fd;
										$hospital_add[] = $hospital_fd;
									}
								}
							}
						}


						//hospital admission //  transfer to hospital
						if(!empty($hospital_admission_days))
						{
							foreach($hospital_admission_days as $location_id => $hospital_adm)
							{
								//check if the hospital admission day is NOT also a hospital/discharge day
								if(!in_array($hospital_adm, $hospital_discharge_days) && !in_array($hospital_adm, $hospiz_discharge_days) && !in_array($hospital_adm, $hospital_first_days))
								{
									if($client_settings['hosp_adm'] == 'tr')
									{ //hospital admission is treatment day
										$treatment_add[] = $hospital_adm;
   										$hospital_remove[] = $hospital_adm;
									}
									else
									{ //hospital admission is NOT treatment day
										$treatment_remove[] = $hospital_adm;
										$hospital_add[] = $hospital_adm;
									}
								}
							}
						}


						//hospital discharge
						if(!empty($hospital_discharge_days))
						{
							foreach($hospital_discharge_days as $location_id => $hospital_dis)
							{

								//check if the hospital discharge day is NOT also a hospital/hospiz admission day but not for the same location
								if(
									(!in_array($hospital_dis, $hospital_admission_days) || array_search($hospital_dis, $hospital_admission_days) == $location_id || !in_array($hospital_dis, $hospital_first_days) || array_search($hospital_dis, $hospital_first_days) == $location_id) 
								    && !in_array($hospital_dis, $hospiz_admission_days)
								    && !(in_array($hospital_dis, $hospital_admission_days) && array_search($hospital_dis, $hospital_admission_days) != $location_id)
								    )
								{
									if($client_settings['hosp_dis'] == 'tr')
									{ //hospital discharge is treatment day
										$treatment_add[] = $hospital_dis;
										$hospital_remove[] = $hospital_dis;
									}
									else
									{ //hospital discharge is NOT treatment day
										$treatment_remove[] = $hospital_dis;
										$hospital_add[] = $hospital_dis;
									}
									//hospital discharge/hospital admission
								}
								elseif(in_array($hospital_dis, $hospital_admission_days) && array_search($hospital_dis, $hospital_admission_days) != $location_id)
								{
									if($client_settings['hosp_dis_hosp_adm'] == 'tr')
									{ //hospital discharge/hospital admission is treatment day
										$treatment_add[] = $hospital_dis;
										$hospital_remove[] = $hospital_dis;
									}
									else
									{
										$treatment_remove[] = $hospital_dis;
										$hospital_add[] = $hospital_dis;
									}
									//hospital discharge/hospiz admission
								}
								elseif(in_array($hospital_dis, $hospiz_admission_days))
								{
									if($client_settings['hosp_dis_hospiz_adm'] == 'tr')
									{ //hospital discharge/hospiz admission is treatment day
										$treatment_add[] = $hospital_dis;
										$hospital_remove[] = $hospital_dis;
										$hospiz_remove[] = $hospital_dis;
									}
									elseif($client_settings['hosp_dis_hospiz_adm'] == 'hz')
									{ //hospital discharge/hospiz admission is hospiz
										$treatment_remove[] = $hospital_dis;
										$hospital_remove[] = $hospital_dis;
										$hospiz_add[] = $hospital_dis;
									}
									else
									{
										$treatment_remove[] = $hospital_dis;
										$hospital_add[] = $hospital_dis;
										$hospiz_remove[] = $hospital_dis;
									}
								}
							}
						}
// 					var_dump($hospiz_add);
						//hospital day
						if(!empty($hospital_days))
						{
							foreach($hospital_days as $hospital_day)
							{
								//check if hospital day is not patient discharge, hospital admission/discharge, hospiz admission/discharge
								if(!in_array($hospital_day, $hospital_admission_days) &&
									!in_array($hospital_day, $hospital_first_days) &&
									!in_array($hospital_day, $hospital_discharge_days) &&
									!in_array($hospital_day, $hospiz_admission_days) &&
									!in_array($hospital_day, $hospiz_discharge_days) &&
									!in_array($hospital_day, $hospiz_first_days) &&
									!in_array($hospital_day, $discharge_days)
								)
								{

									if($client_settings['hosp_day'] == 'tr')
									{ //hospital day is treatment day
										if(!in_array($hospital_day, $treatment_remove))
										{
											$treatment_add[] = $hospital_day;
										}
										if(!in_array($hospital_day, $hospital_add))
										{
											$hospital_remove[] = $hospital_day;
										}
									}
									else
									{ //hospital day is NOT treatment day
										if(!in_array($hospital_day, $treatment_add))
										{
											$treatment_remove[] = $hospital_day;
										}
										if(!in_array($hospital_day, $hospital_remove))
										{
											$hospital_add[] = $hospital_day;
										}
									}
								}
								elseif(in_array($hospital_day, $discharge_days))
								{
									//var_dump(array_search($hospital_day, $treatment_remove));
									if(in_array($hospital_day, $discharge_dead_days))
									{ //discharge dead
										if($client_settings['hosp_pat_dead'] == 'tr')
										{ //hospital patient discharge dead day is treatment day
											$treatment_add[] = $hospital_day;
											$hospital_remove[] = $hospital_day;

											if(array_search($hospital_day, $hospital_add) !== false)
											{
												unset($hospital_add[array_search($hospital_day, $hospital_add)]);
											}

											if(array_search($hospital_day, $treatment_remove) !== false)
											{
												unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
											}
										}
										else
										{
										    // If it is considered hospital day - apply new conditions
										    if($client_settings['hosp_pat_dead_final'] == '0')   
										    {
										        if(in_array($hospital_day,$location_starts_not_hs)  && !in_array(end($location_order),$hospital_locations)) 
										        { // this means that another location starts on the same day and hospital is not the last location  

										            if(!in_array($hospital_day, $hospital_admission_days) && !in_array($hospital_day, $hospital_first_days)  && !in_array($hospital_day, $hospiz_admission_days) && !in_array($hospital_day, $discharge_location_starts) )
										            {
										                if($client_settings['hosp_dis'] == 'tr')
										                { //hospital discharge is treatment day
										                    
										                    $treatment_add[] = $hospital_day;
										                    $hospital_remove[] = $hospital_day;
										                    
										                    if(array_search($hospital_day, $hospital_add) !== false)
										                    {
										                        unset($hospital_add[array_search($hospital_day, $hospital_add)]);
										                    }
										                    
										                    if(array_search($hospital_day, $treatment_remove) !== false)
										                    {
										                        unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
										                    }
										                    
										                }
										                else
										                { //hospital discharge is NOT treatment day
										                    $treatment_remove[] = $hospital_day;
										                    $hospital_add[] = $hospital_day;
										                    if(array_search($hospital_day, $hospital_remove) !== false)
										                    {
										                        unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
										                    }
										                    if(array_search($hospital_day, $treatment_add) !== false)
										                    {
										                        unset($treatment_add[array_search($hospital_day, $treatment_add)]);
										                    }
										                    
										                }
										                //hospital discharge/hospital admission
										            }
										            elseif(in_array($hospital_day, $hospiz_admission_days) && !in_array($hospital_day, $hospiz_discharge_days) && !in_array($hospital_day, $discharge_location_starts))
										            {
										                if($client_settings['hosp_dis_hospiz_adm'] == 'tr')
										                { //hospital discharge/hospiz admission is treatment day
										                    
										                    $treatment_add[] = $hospital_day;
										                    $hospital_remove[] = $hospital_day;
										                    $hospiz_remove[] = $hospital_day;
										                    
										                    if(array_search($hospital_day, $hospital_add) !== false)
										                    {
										                        unset($hospital_add[array_search($hospital_day, $hospital_add)]);
										                    }
										                    
										                    if(array_search($hospital_day, $treatment_remove) !== false)
										                    {
										                        unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
										                    }
										                }
										                elseif($client_settings['hosp_dis_hospiz_adm'] == 'hz')
										                { //hospital discharge/hospiz admission is hospiz

										                    $treatment_remove[] = $hospital_day;
										                    $hospital_remove[] = $hospital_day;
										                    $hospiz_add[] = $hospital_day;
										                    
										                    if(array_search($hospital_day, $treatment_add) !== false)
										                    {
										                        unset($treatment_add[array_search($hospital_day, $treatment_add)]);
										                    }
										                    if(array_search($hospital_day, $hospital_remove) !== false)
										                    {
										                        unset($hospital_add[array_search($hospital_day, $hospital_remove)]);
										                    }
										                }
										                else
										                {
										                    $treatment_remove[] = $hospital_day;
										                    $hospital_add[] = $hospital_day;
										                    $hospiz_remove[] = $hospital_dis;;
										                    
										                    if(array_search($hospital_day, $hospital_remove) !== false)
										                    {
										                        unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
										                    }
										                    if(array_search($hospital_day, $treatment_add) !== false)
										                    {
										                        unset($treatment_add[array_search($hospital_day, $treatment_add)]);
										                    }
										                }
										            } 
										            elseif(in_array($hospital_day, $discharge_location_starts))
										            {
										                if($client_settings['hosp_pat_dead'] == 'tr')
										                { //hospital patient discharge dead day is treatment day
                                                            $treatment_add[] = $hospital_day;
                                                            $hospital_remove[] = $hospital_day;
										                
    										                if(array_search($hospital_day, $hospital_add) !== false)
    										                {
    										                    unset($hospital_add[array_search($hospital_day, $hospital_add)]);
    										                }
    										                
    										                if(array_search($hospital_day, $treatment_remove) !== false)
    										                {
    										                    unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
    										                }
										                } else {
										            
                											$treatment_remove[] = $hospital_day;
                											$hospital_add[] = $hospital_day;
                											if(array_search($hospital_day, $hospital_remove) !== false)
                											{
                												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
                											}
                											if(array_search($hospital_day, $treatment_add) !== false)
                											{
                												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
                											}
                                                        }
										            }
										        } else {
										            
        											$treatment_remove[] = $hospital_day;
        											$hospital_add[] = $hospital_day;
        											if(array_search($hospital_day, $hospital_remove) !== false)
        											{
        												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
        											}
        											if(array_search($hospital_day, $treatment_add) !== false)
        											{
        												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
        											}
										        }
										    } 
										    else 
										    {   // calculate as before TODO-618
    											$treatment_remove[] = $hospital_day;
    											$hospital_add[] = $hospital_day;
    											if(array_search($hospital_day, $hospital_remove) !== false)
    											{
    												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
    											}
    											if(array_search($hospital_day, $treatment_add) !== false)
    											{
    												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
    											}
										    }
										}
									}
									else
									{
										if($client_settings['hosp_pat_dis'] == 'tr')
										{ //hospital patient discharge day is treatment day
											$treatment_add[] = $hospital_day;
											$hospital_remove[] = $hospital_day;
											if(array_search($hospital_day, $hospital_add) !== false)
											{
												unset($hospital_add[array_search($hospital_day, $hospital_add)]);
											}

											if(array_search($hospital_day, $treatment_remove) !== false)
											{
												unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
											}
										}
										else
										{
										    if($client_settings['hosp_pat_dis_final'] == '0')
										    {
										        if(in_array($hospital_day,$location_starts_not_hs) && !in_array(end($location_order),$hospital_locations)){  // this means that another location starts on the same day and hospital is not the last location  
										           
										            if(!in_array($hospital_day, $hospital_admission_days)&& !in_array($hospital_day, $hospital_first_days)  && !in_array($hospital_day, $hospiz_admission_days) && !in_array($hospital_day, $discharge_location_starts) )
										            {
										                if($client_settings['hosp_dis'] == 'tr')
										                { //hospital discharge is treatment day
										            
										                    $treatment_add[] = $hospital_day;
										                    $hospital_remove[] = $hospital_day;
										            
										                    if(array_search($hospital_day, $hospital_add) !== false)
										                    {
                                                                unset($hospital_add[array_search($hospital_day, $hospital_add)]);
                                                            }
    										                if(array_search($hospital_day, $treatment_remove) !== false)
    										                {
    										                   unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
    										                }
										                }
										                else
										                { //hospital discharge is NOT treatment day
										                
                                                            $treatment_remove[] = $hospital_day;
										                    $hospital_add[] = $hospital_day;
										                    
										                    if(array_search($hospital_day, $hospital_remove) !== false)
										                    {
										                      unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
        										            }
        										            
		          								            if(array_search($hospital_day, $treatment_add) !== false)
					                                        {
                                                                unset($treatment_add[array_search($hospital_day, $treatment_add)]);
                                                            }
										                }
										                //hospital discharge/hospital admission
                                                    }
								                    elseif(in_array($hospital_day, $hospiz_admission_days) && !in_array($hospital_day, $hospiz_discharge_days)  && !in_array($hospital_day, $discharge_location_starts) )
								                    {
									                    if($client_settings['hosp_dis_hospiz_adm'] == 'tr')
									                    { //hospital discharge/hospiz admission is treatment day
										            
									                        $treatment_add[] = $hospital_day;
									                        $hospital_remove[] = $hospital_day;
									                        $hospiz_remove[] = $hospital_day;
										            
								                            if(array_search($hospital_day, $hospital_add) !== false)
								                            {
								                                unset($hospital_add[array_search($hospital_day, $hospital_add)]);
										                    }
										            
										                    if(array_search($hospital_day, $treatment_remove) !== false)
									                        {
                                                                unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
										                    }
    								                    }
    						                            elseif($client_settings['hosp_dis_hospiz_adm'] == 'hz')
    						                            { //hospital discharge/hospiz admission is hospiz
        						            
        						                            $treatment_remove[] = $hospital_day;
        						                            $hospital_remove[] = $hospital_day;
        						                            $hospiz_add[] = $hospital_day;
        						            
        						                            if(array_search($hospital_day, $treatment_add) !== false)
        						                            {
        						                                unset($treatment_add[array_search($hospital_day, $treatment_add)]);
        						                            }
        						                            
        						                            if(array_search($hospital_day, $hospital_remove) !== false)
        						                            {
        						                                unset($hospital_add[array_search($hospital_day, $hospital_remove)]);
        						                            }
                                                        }
                                                        else
    										            {
                                                            $treatment_remove[] = $hospital_day;
                                                            $hospital_add[] = $hospital_day;
                                                            $hospiz_remove[] = $hospital_dis;;
                                                            
                                                            if(array_search($hospital_day, $hospital_remove) !== false)
                                                            {
                                                                unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
                                                            }
                                                            if(array_search($hospital_day, $treatment_add) !== false)
                                                            {
                                                                unset($treatment_add[array_search($hospital_day, $treatment_add)]);
                                                            }
                                                        }
                                                    }
                                                    elseif(in_array($hospital_day, $discharge_location_starts))
                                                    {
                                                        
                                                        if($client_settings['hosp_pat_dis'] == 'tr')
                                                        { //hospital patient discharge dead day is treatment day
                                                            $treatment_add[] = $hospital_day;
                                                            $hospital_remove[] = $hospital_day;
                                                    
                                                            if(array_search($hospital_day, $hospital_add) !== false)
                                                            {
                                                                unset($hospital_add[array_search($hospital_day, $hospital_add)]);
                                                            }
                                                    
                                                            if(array_search($hospital_day, $treatment_remove) !== false)
                                                            {
                                                                unset($treatment_remove[array_search($hospital_day, $treatment_remove)]);
                                                            }
                                                        } else {
                                                    
                                                            $treatment_remove[] = $hospital_day;
                                                            $hospital_add[] = $hospital_day;
                                                            if(array_search($hospital_day, $hospital_remove) !== false)
                                                            {
                                                                unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
                                                            }
                                                            if(array_search($hospital_day, $treatment_add) !== false)
                                                            {
                                                                unset($treatment_add[array_search($hospital_day, $treatment_add)]);
                                                            }
                                                        }
                                                    }
                                                    
										        } 
										        else
										        {
        											$treatment_remove[] = $hospital_day;
        											$hospital_add[] = $hospital_day;
        
        											if(array_search($hospital_day, $hospital_remove) !== false)
        											{
        												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
        											}
        											if(array_search($hospital_day, $treatment_add) !== false)
        											{
        												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
        											}
										        }
										        
										    }   
										    else
										    {
    											$treatment_remove[] = $hospital_day;
    											$hospital_add[] = $hospital_day;
    
    											if(array_search($hospital_day, $hospital_remove) !== false)
    											{
    												unset($hospital_remove[array_search($hospital_day, $hospital_remove)]);
    											}
    											if(array_search($hospital_day, $treatment_add) !== false)
    											{
    												unset($treatment_add[array_search($hospital_day, $treatment_add)]);
    											}
										    }
										}
									}
								}
							}
						}

						//hospital first days - app admission in hospital
						if(!empty($hospiz_first_days))
						{
							foreach($hospiz_first_days as $location_id => $hospiz_fd)
							{
								//check if the hospital admission day is NOT also a hospital/discharge day
								if(!in_array($hospiz_fd, $hospiz_discharge_days) && !in_array($hospiz_fd, $hospital_discharge_days))
								{
									if($client_settings['hospiz_first_day'] == 'tr')
									{ //hospiz admission is treatment day
										$treatment_add[] = $hospiz_fd;
										$hospiz_remove[] = $hospiz_fd;
									}
									else
									{ //hospital admission is NOT treatment day
										$treatment_remove[] = $hospiz_fd;
										$hospiz_add[] = $hospiz_fd;
									}
								}
							}
						}

						//hospiz admission // hospiz transfer
						if(!empty($hospiz_admission_days))
						{
							foreach($hospiz_admission_days as $location_id => $hospiz_adm)
							{
								//check if the hospital admission day is NOT also a hospital/discharge day
								if(!in_array($hospiz_adm, $hospiz_discharge_days) && !in_array($hospiz_adm, $hospital_discharge_days) && !in_array($hospiz_adm, $hospiz_first_days))
								{
									if($client_settings['hospiz_adm'] == 'tr')
									{ //hospiz admission is treatment day
										$treatment_add[] = $hospiz_adm;
    									$hospiz_remove[] = $hospiz_adm;
									}
									else
									{ //hospital admission is NOT treatment day
										$treatment_remove[] = $hospiz_adm;
										$hospiz_add[] = $hospiz_adm;
									}
								}
							}
						}


						//hospiz discharge
						if(!empty($hospiz_discharge_days))
						{
							foreach($hospiz_discharge_days as $location_id => $hospiz_dis)
							{
								//check if the hospiz discharge day is NOT also a hospital/hospiz admission day but not for the same location
								if((!in_array($hospiz_dis, $hospiz_admission_days) || array_search($hospiz_dis, $hospiz_admission_days) == $location_id || !in_array($hospiz_dis, $hospiz_first_days) || array_search($hospiz_dis, $hospiz_first_days) == $location_id ) && !in_array($hospiz_dis, $hospital_admission_days))
								{
									if($client_settings['hospiz_dis'] == 'tr')
									{ //hospiz discharge is treatment day
										$treatment_add[] = $hospiz_dis;
										$hospiz_remove[] = $hospiz_dis;
									}
									else
									{ //hospiz discharge is NOT treatment day
										$treatment_remove[] = $hospiz_dis;
										$hospiz_add[] = $hospiz_dis;
									}
									//hospiz discharge/hospiz admission
								}
								elseif(in_array($hospiz_dis, $hospiz_admission_days) && array_search($hospiz_dis, $hospiz_admission_days) != $location_id)
								{
									if($client_settings['hospiz_dis_hospiz_adm'] == 'tr')
									{ //hospiz discharge/hospiz admission is treatment day
										$treatment_add[] = $hospiz_dis;
										$hospz_remove[] = $hospiz_dis;
									}
									else
									{
										$treatment_remove[] = $hospiz_dis;
										$hospiz_add[] = $hospiz_dis;
									}
									//hospiz discharge/hospital admission
								}
								elseif(in_array($hospiz_dis, $hospital_admission_days))
								{
									if($client_settings['hospiz_dis_hosp_adm'] == 'tr')
									{ //hospiz discharge/hospital admission is treatment day
										$treatment_add[] = $hospiz_dis;
										$hospital_remove[] = $hospiz_dis;
										$hospiz_remove[] = $hospiz_dis;
									}
									elseif($client_settings['hospiz_dis_hosp_adm'] == 'hp')
									{ //hospiz discharge/hospital admission is hospital
										$treatment_remove[] = $hospiz_dis;
										$hospital_add[] = $hospiz_dis;
										$hospiz_remove[] = $hospiz_dis;
									}
									else
									{
										$treatment_remove[] = $hospiz_dis;
										$hospital_remove[] = $hospiz_dis;
										$hospiz_add[] = $hospiz_dis;
									}
								}
							}
						}

						//hospiz day
						if(!empty($hospiz_days))
						{
							foreach($hospiz_days as $hospiz_day)
							{
								//check if hospiz day is not patient discharge, hospital admission/discharge, hospiz admission/discharge
								if(!in_array($hospiz_day, $hospiz_admission_days) &&
									!in_array($hospiz_day, $hospiz_first_days) &&
									!in_array($hospiz_day, $hospiz_discharge_days) &&
									!in_array($hospiz_day, $hospital_admission_days) &&
									!in_array($hospiz_day, $hospital_first_days) &&
									!in_array($hospiz_day, $hospital_discharge_days) &&
									!in_array($hospiz_day, $discharge_days)
								)
								{

									if($client_settings['hospiz_day'] == 'tr')
									{ //hospiz day is treatment day
										if(!in_array($hospiz_day, $treatment_remove))
										{
											$treatment_add[] = $hospiz_day;
										}
										if(!in_array($hospiz_day, $hospital_add))
										{
											$hospiz_remove[] = $hospiz_day;
										}
									}
									else
									{ //hospiz day is NOT treatment day
										if(!in_array($hospiz_day, $treatment_add))
										{
											$treatment_remove[] = $hospiz_day;
										}
										if(!in_array($hospiz_day, $hospital_remove))
										{
											$hospiz_add[] = $hospiz_day;
										}
									}
								}
								elseif(in_array($hospiz_day, $discharge_days))
								{
									if(in_array($hospiz_day, $discharge_dead_days))
									{ //discharge dead
										if($client_settings['hospiz_pat_dead'] == 'tr')
										{ //hospiz patient discharge dead day is treatment day
											$treatment_add[] = $hospiz_day;
											$hospiz_remove[] = $hospiz_day;

											if(array_search($hospiz_day, $hospiz_add) !== false)
											{
												unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
											}

											if(array_search($hospiz_day, $treatment_remove) !== false)
											{
												unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
											}
										}
										else
										{
										    if($client_settings['hospiz_pat_dead_final'] == '0')
										    {
    										    if(in_array($hospiz_day,$location_starts_not_hz)  && !in_array(end($location_order),$hospiz_locations))
    										    { // this means that another location starts on the same day and hospiz is not the last location
    										        
    										        //check if the hospiz discharge day is NOT also a hospital/hospiz admission day but not for the same location
    										        if( !in_array($hospiz_day, $hospiz_admission_days) &&  !in_array($hospiz_day, $hospiz_first_days) && !in_array($hospiz_day, $hospital_admission_days) && !in_array($hospiz_day, $discharge_location_starts))
    										        {
    										            if($client_settings['hospiz_dis'] == 'tr')
    										            { //hospiz discharge is treatment day
    										                
    										                $treatment_add[] = $hospiz_day;
    										                $hospiz_remove[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $treatment_remove) !== false)
    										                {
    										                    unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospiz_add) !== false)
    										                {
    										                    unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
    										                }
    										                
    										            }
    										            else
    										            { //hospiz discharge is NOT treatment day
    										                
    										                $treatment_remove[] = $hospiz_day;
    										                $hospiz_add[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $treatment_add) !== false)
    										                {
    										                    unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospiz_remove) !== false)
    										                {
    										                    unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    										                }
    										            }
    										            //hospiz discharge/hospiz admission
    										        }
 
    										        elseif(in_array($hospiz_day, $hospital_admission_days)  && !in_array($hospiz_day, $hospital_discharge_days) && !in_array($hospiz_day, $discharge_location_starts))
    										        {
    										            if($client_settings['hospiz_dis_hosp_adm'] == 'tr')
    										            { //hospiz discharge/hospital admission is treatment day

    										                $treatment_add[] = $hospiz_day;
    										                $hospital_remove[] = $hospiz_day;
    										                $hospiz_remove[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $treatment_remove) !== false)
    										                {
    										                    unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospital_add) !== false)
    										                {
    										                    unset($hospital_add[array_search($hospiz_day, $hospital_add)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospiz_add) !== false)
    										                {
    										                    unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
    										                }
    										                
    										            }
    										            elseif($client_settings['hospiz_dis_hosp_adm'] == 'hp')
    										            { //hospiz discharge/hospital admission is hospital

    										                $treatment_remove[] = $hospiz_day;
    										                $hospital_add[] = $hospiz_day;
    										                $hospiz_remove[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $treatment_add) !== false)
    										                {
    										                    unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospital_remove) !== false)
    										                {
    										                    unset($hospital_remove[array_search($hospiz_day, $hospital_remove)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $hospiz_add) !== false)
    										                {
    										                    unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
    										                }
    										                
    										            }
    										            else
    										            {

    										                $hospiz_add[] = $hospiz_day;
    										                $treatment_remove[] = $hospiz_day;
    										                $hospital_remove[] = $hospiz_day;
    										                
    										                if(array_search($hospiz_day, $hospiz_remove) !== false)
    										                {
    										                    unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    										                }
    										                
    										                if(array_search($hospiz_day, $treatment_add) !== false)
    										                {
    										                    unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    										                }

    										                if(array_search($hospiz_day, $hospital_remove) !== false)
    										                {
    										                    unset($hospital_add[array_search($hospiz_day, $hospital_remove)]);
    										                }
    										                
    										            }
    										        }
    										        elseif( in_array($hospiz_day, $discharge_location_starts))
    										        {
    										            if($client_settings['hospiz_pat_dead'] == 'tr')
    										            { //hospiz patient discharge dead day is treatment day
        										            $treatment_add[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										            
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										            
        										            if(array_search($hospiz_day, $treatment_remove) !== false)
        										            {
        										                unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
        										            }
    										            }
    										            else
    										            {
    										                $treatment_remove[] = $hospiz_day;
    										                $hospiz_add[] = $hospiz_day;
    										                if(array_search($hospiz_day, $hospiz_remove) !== false)
    										                {
    										                    unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    										                }
    										            
    										                if(array_search($hospiz_day, $treatment_add) !== false)
    										                {
    										                    unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    										                }
    										            }
    										            
    										        }
    										        
    										    }
    										    else
    										    {
        											$treatment_remove[] = $hospiz_day;
        											$hospiz_add[] = $hospiz_day;
        											if(array_search($hospiz_day, $hospiz_remove) !== false)
        											{
        												unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        											}
        
        											if(array_search($hospiz_day, $treatment_add) !== false)
        											{
        												unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        											}
    										    }
										    
										    }
										    else
										    {
										        // calculate as before TODO-618
    											$treatment_remove[] = $hospiz_day;
    											$hospiz_add[] = $hospiz_day;
    											if(array_search($hospiz_day, $hospiz_remove) !== false)
    											{
    												unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    											}
    
    											if(array_search($hospiz_day, $treatment_add) !== false)
    											{
    												unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    											}
										    }
										}
									}
									else
									{
										if($client_settings['hospiz_pat_dis'] == 'tr')
										{ //hospiz patient discharge day is treatment day
											$treatment_add[] = $hospiz_day;
											$hospiz_remove[] = $hospiz_day;

											if(array_search($hospiz_day, $hospiz_add) !== false)
											{
												unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
											}

											if(array_search($hospiz_day, $hospiz_add) !== false)
											{
												unset($treatment_remove[array_search($hospiz_day, $hospiz_add)]);
											}
										}
										else
    									{
    									    if($client_settings['hospiz_pat_dis_final'] == '0')
    									    {
    										    if(in_array($hospiz_day,$location_starts_not_hz)  && !in_array(end($location_order),$hospiz_locations))
    										    { // this means that another location starts on the same day and hospiz is not the last location
    										    
        										    //check if the hospiz discharge day is NOT also a hospital/hospiz admission day but not for the same location
        										    if( !in_array($hospiz_day, $hospiz_admission_days) &&  !in_array($hospiz_day, $hospiz_first_days) && !in_array($hospiz_dis, $hospital_admission_days) && !in_array($hospiz_day, $discharge_location_starts))
        										    {
        										        if($client_settings['hospiz_dis'] == 'tr')
        										        { //hospiz discharge is treatment day
        										    
        										            $treatment_add[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $treatment_remove) !== false)
        										            {
        										                unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										    
        										        }
        										        else
        										        { //hospiz discharge is NOT treatment day
        										    
        										            $treatment_remove[] = $hospiz_day;
        										            $hospiz_add[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $treatment_add) !== false)
        										            {
        										                unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospiz_remove) !== false)
        										            {
        										                unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        										            }
        										        }
        										        //hospiz discharge/hospiz admission
        										    }
        										    elseif(in_array($hospiz_day, $hospital_admission_days) && !in_array($hospiz_day, $hospital_discharge_days)  && !in_array($hospiz_day, $discharge_location_starts))
        										    {
        										        
        										        if($client_settings['hospiz_dis_hosp_adm'] == 'tr')
        										        { //hospiz discharge/hospital admission is treatment day
        										    
        										            $treatment_add[] = $hospiz_day;
        										            $hospital_remove[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $treatment_remove) !== false)
        										            {
        										                unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospital_add) !== false)
        										            {
        										                unset($hospital_add[array_search($hospiz_day, $hospital_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										    
        										        }
        										        elseif($client_settings['hospiz_dis_hosp_adm'] == 'hp')
        										        { //hospiz discharge/hospital admission is hospital
        										    
        										            $treatment_remove[] = $hospiz_day;
        										            $hospital_add[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $treatment_add) !== false)
        										            {
        										                unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospital_remove) !== false)
        										            {
        										                unset($hospital_remove[array_search($hospiz_day, $hospital_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										    
        										        }
        										        else
        										        {
        										    
        										            $hospiz_add[] = $hospiz_day;
        										            $treatment_remove[] = $hospiz_day;
        										            $hospital_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $hospiz_remove) !== false)
        										            {
        										                unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $treatment_add) !== false)
        										            {
        										                unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $hospital_remove) !== false)
        										            {
        										                unset($hospital_add[array_search($hospiz_day, $hospital_remove)]);
        										            }
        										        }
        										    }
        										    elseif( in_array($hospiz_day, $discharge_location_starts))
        										    {
        										        if($client_settings['hospiz_pat_dis'] == 'tr')
        										        { //hospiz patient discharge dead day is treatment day
        										            $treatment_add[] = $hospiz_day;
        										            $hospiz_remove[] = $hospiz_day;
        										    
        										            if(array_search($hospiz_day, $hospiz_add) !== false)
        										            {
        										                unset($hospiz_add[array_search($hospiz_day, $hospiz_add)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $treatment_remove) !== false)
        										            {
        										                unset($treatment_remove[array_search($hospiz_day, $treatment_remove)]);
        										            }
        										        }
        										        else
        										        {
        										            $treatment_remove[] = $hospiz_day;
        										            $hospiz_add[] = $hospiz_day;
        										            if(array_search($hospiz_day, $hospiz_remove) !== false)
        										            {
        										                unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        										            }
        										    
        										            if(array_search($hospiz_day, $treatment_add) !== false)
        										            {
        										                unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        										            }
        										        }
        										    
        										    }
        										    
    										    }										    
    										    else
    										    {
        											$treatment_remove[] = $hospiz_day;
        											$hospiz_add[] = $hospiz_day;
        
        											if(array_search($hospiz_day, $hospiz_remove) !== false)
        											{
        												unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
        											}
        
        											if(array_search($hospiz_day, $treatment_add) !== false)
        											{
        												unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
        											}
    										    }
                                            }
                                            else
                                            {
    											$treatment_remove[] = $hospiz_day;
    											$hospiz_add[] = $hospiz_day;
    
    
    											if(array_search($hospiz_day, $hospiz_remove) !== false)
    											{
    												unset($hospiz_remove[array_search($hospiz_day, $hospiz_remove)]);
    											}
    
    											if(array_search($hospiz_day, $treatment_add) !== false)
    											{
    												unset($treatment_add[array_search($hospiz_day, $treatment_add)]);
    											}
                                            }	    
    									}
									}
								}
							}
						}
					}


					//we have the client settings arrays

					$hospital_add = array_unique($hospital_add);
					$hospital_remove = array_unique($hospital_remove);
					$hospiz_add = array_unique($hospiz_add);
					$hospiz_remove = array_unique($hospiz_remove);
					$treatment_add = array_unique($treatment_add);
					$treatment_remove = array_unique($treatment_remove);

					//cut "add" days to the active period

					$hospital_add = array_unique(@array_intersect($hospital_add, $final_patient[$patient['ipid']]['real_active_days']));
					$hospiz_add = array_unique(@array_intersect($hospiz_add, $final_patient[$patient['ipid']]['real_active_days']));
					$treatment_add = array_unique(@array_intersect($treatment_add, $final_patient[$patient['ipid']]['real_active_days']));


					//let's start appliying
					//first we add everything

					$final_patient[$patient['ipid']]['treatment_days'] = array_unique(array_merge($treatment_add, $final_patient[$patient['ipid']]['real_active_days']));

					if(!empty($final_patient[$patient['ipid']]['hospital']['real_days']))
					{
						$final_patient[$patient['ipid']]['hospital']['real_days_cs'] = array_unique(array_merge($hospital_add, $final_patient[$patient['ipid']]['hospital']['real_days']));
					}

					if(!empty($final_patient[$patient['ipid']]['hospiz']['real_days']))
					{
						$final_patient[$patient['ipid']]['hospiz']['real_days_cs'] = array_unique(array_merge($hospiz_add, $final_patient[$patient['ipid']]['hospiz']['real_days']));
					}

					//now let's substract and pray

					$final_patient[$patient['ipid']]['treatment_days'] = array_unique(array_diff($final_patient[$patient['ipid']]['treatment_days'], $treatment_remove));
					$final_patient[$patient['ipid']]['hospital']['real_days_cs'] = array_unique(array_diff($final_patient[$patient['ipid']]['hospital']['real_days_cs'], $hospital_remove));
					$final_patient[$patient['ipid']]['hospiz']['real_days_cs'] = array_unique(array_diff($final_patient[$patient['ipid']]['hospiz']['real_days_cs'], $hospiz_remove));

					//count everything so the output can be used right away
					$final_patient[$patient['ipid']]['treatment_days_no'] = sizeof($final_patient[$patient['ipid']]['treatment_days']);
					$final_patient[$patient['ipid']]['real_active_days_no'] = sizeof($final_patient[$patient['ipid']]['real_active_days']);
					$final_patient[$patient['ipid']]['hospital']['real_days_cs_no'] = sizeof($final_patient[$patient['ipid']]['hospital']['real_days_cs']);
					$final_patient[$patient['ipid']]['hospiz']['real_days_cs_no'] = sizeof($final_patient[$patient['ipid']]['hospiz']['real_days_cs']);


// 				sort($final_patient[$patient['ipid']]['treatment_days']);
// 				sort($final_patient[$patient['ipid']]['hospital']['real_days_cs']);
// 				sort($final_patient[$patient['ipid']]['hospiz']['real_days_cs']);
				}
			}


// 			if($_REQUEST['spd'] =="1"){
//     			print_r($final_patient); exit;
// 			}
			return $final_patient;
		}

		public function get_working_days($start_date, $end_date)
		{

			$begin = strtotime($start_date);
			$end = strtotime($end_date);
			if($begin < $end)
			{
				$no_days = 0;
				$weekends = 0;
				while($begin <= $end)
				{
					$no_days++; // no of days in the given interval
					$what_day = date("N", $begin);
					if($what_day > 5)
					{ // 6 and 7 are weekend days
						$weekends++;
					};
					$begin+=86400; // +1 day
				};
				$working_days = $no_days - $weekends;

				return $working_days;
			}
		}

		public function get_multiple_epids($ipids)
		{
			if (empty($ipids)){
				$ipids = array("0");
			}
			$q = Doctrine_Query::create()
			->select('ipid, epid')
			->from('EpidIpidMapping e')
			->andWhereIn('e.ipid', $ipids)
			->orderBy('e.ipid ASC');
			$q_res = $q->fetchArray();
			
			/* ZF1
			$q = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping e')
				->andWhereIn('e.ipid', $ipids)
				->orderBy('e.ipid ASC');
			$q_res = $q->fetchArray();
			*/

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$q_res_arr[$v_res['ipid']] = $v_res['epid'];
				}

				return $q_res_arr;
			}
			else
			{
				return false;
			}
		}

		public function get_multiple_ipids($epids)
		{
			if (empty($epids)){
				$epids = array("0");
			}
			$q = Doctrine_Query::create()
			->select('epid, ipid')
			->from('EpidIpidMapping e')
			->andWhereIn('e.epid', $epids)
			->orderBy('e.ipid ASC');
			$q_res = $q->fetchArray();
				
			/* ZF1
			$q = Doctrine_Query::create()
			->select('*')
			->from('EpidIpidMapping e')
			->andWhereIn('e.epid', $epids)
			->orderBy('e.ipid ASC');
			$q_res = $q->fetchArray();
			*/
			
			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$q_res_arr[strtolower($v_res['epid'])] = $v_res['ipid'];
				}

				return $q_res_arr;
			}
			else
			{
				return false;
			}
		}

		//used in RP invoice, control_sheet
		public function get_rp_price_mapping()
		{
			$location_type_match = array('2' => 'p_hospiz', '3' => 'p_nurse', '4' => 'p_nurse', '5' => 'p_home');

			return $location_type_match;
		}

		public function template_default_recipients()
		{
			$Tr = new Zend_View_Helper_Translate();
			$recipients = array(
				'none' => $Tr->translate('none_default_recipient'),
				'fdoc' => $Tr->translate('fdoc_default_recipient'),
				'hi' => $Tr->translate('hi_default_recipient'),
				'pat' => $Tr->translate('patient_default_recipient'),
				'cntpers' => $Tr->translate('contact_person_default_recipient'),
				'pfl' => $Tr->translate('nurse_default_recipient'),
				'apoth' => $Tr->translate('farmacy_default_recipient'),
				'supp' => $Tr->translate('supplies_default_recipient'),
			);

			return $recipients;
		}

		public function symptoms_attribute_values()
		{
			$atributes_values = array(
				0 => 'kein',
				1 => 'leicht',
				2 => 'leicht',
				3 => 'leicht',
				4 => 'leicht',
				5 => 'mittel',
				6 => 'mittel',
				7 => 'mittel',
				8 => 'schwer',
				9 => 'schwer',
				10 => 'schwer'
			);

			return $atributes_values;
		}

		public function system_tags_tabname()
		{
			$tabnames = array(
				'user_gen',
				'ispc_gen',
				'kv_receipt',
				'btm_receipt',
				'muster63',
				'control_sheet',
				'letter',
				'master_sheet',
			);

			return $tabnames;
		}

		public static function get_ipids($pids = false, $match_id_ipid = false)
		{
			if($pids)
			{
				if(!is_array($pids))
				{
					$pids_arr = array($pids);
				}
				else
				{
					$pids_arr = $pids;
				}

				$q = Doctrine_Query::create()
					->select('ipid')
					->from('PatientMaster')
					->whereIn('id', $pids_arr)
					->andWhere('isdelete = "0"');
				$q_res = $q->fetchArray();

				if($q_res)
				{
					foreach($q_res as $v_res)
					{
						if($match_id_ipid)
						{
							$patients_ipid[$v_res['id']] = $v_res['ipid'];
						}
						else
						{
							$patients_ipid[] = $v_res['ipid'];
						}
					}

					return $patients_ipid;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public static function get_ipids_from_epids($epids = false, $match_epid_ipid = false)
		{
			if($epids)
			{
				if(!is_array($epids))
				{
					$epids_arr = array($epids);
				}
				else
				{
					$epids_arr = $epids;
				}

				$q = Doctrine_Query::create()
					->select('ipid,epid')
					->from('EpidIpidMapping')
					->whereIn('epid', $epids_arr);
				$q_res = $q->fetchArray();

				if($q_res)
				{
					foreach($q_res as $v_res)
					{
						if($match_epid_ipid)
						{
							$patients_ipid[$v_res['epid']] = $v_res['ipid'];
						}
						else
						{
							$patients_ipid[] = $v_res['ipid'];
						}
					}

					return $patients_ipid;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function convert_umlauts($value , $reverse_convert = false)
		{
			//ispc 1739 - added this if
			if($reverse_convert===true){
				//replace arabics with umlaute und ß=small sharp s ("s-zet") 
				$value = mb_ereg_replace('ae', 'ä', $value);
				$value = mb_ereg_replace('Ae', 'Ä', $value);
				$value = mb_ereg_replace('oe', 'ö', $value);
				$value = mb_ereg_replace('Oe', 'Ö', $value);
				$value = mb_ereg_replace('ue', 'ü', $value);
				$value = mb_ereg_replace('Ue', 'Ü', $value);
				$value = mb_ereg_replace('ss', 'ß', $value);
			}
			else{
			$value = mb_ereg_replace('ä', 'ae', $value);
			$value = mb_ereg_replace('Ä', 'Ae', $value);
			$value = mb_ereg_replace('ö', 'oe', $value);
			$value = mb_ereg_replace('Ö', 'Oe', $value);
			$value = mb_ereg_replace('ü', 'ue', $value);
			$value = mb_ereg_replace('Ü', 'Ue', $value);
			$value = mb_ereg_replace('ß', 'ss', $value);
			}
			return $value;
		}
		//ispc 1739
		//this function is NOT recursive, change if you need
		public static function search_convert($value){
			$search = array(0=>'ei', 1=>'ay', 2=>'ey', 3=>'ai');
			$r = array();
			foreach($search as $k){
				foreach($search as $v){
					$r[] = str_replace($k, $v, $value);
				}
			}
			return array_unique($r);
		}
		
		public static function value_patternation(&$val , $begins_with = false, $return_array = false, $preg_quote = true){
		    
			    
			$regexp = array();
			$regexp[] = $val;
			if (($mbdetectencoding = mb_detect_encoding($val)) === false) {
				$mbdetectencoding = "UTF-8";
			}
			$lower  = mb_strtolower($val, $mbdetectencoding);
			$upper  = mb_strtoupper($val, $mbdetectencoding);
			$regexp[] = $lower;
			$regexp[] = $upper;
			
			$search_value_arabic = $regexp[] = self::convert_umlauts($val);
			$search_value_umlaut = $regexp[] = self::convert_umlauts($val, true);
			$r_1 = self::search_convert($val);
			$r_2 = self::search_convert($search_value_arabic);
			$r_3 = self::search_convert($search_value_umlaut);
			$regexp = array_merge($regexp, $r_1, $r_2, $r_3);
			
			//lower
			$search_value_arabic = $regexp[] = self::convert_umlauts($lower);
			$search_value_umlaut = $regexp[] = self::convert_umlauts($lower, true);
			$r_1 = self::search_convert($lower);
			$r_2 = self::search_convert($search_value_arabic);
			$r_3 = self::search_convert($search_value_umlaut);
			$regexp = array_merge($regexp, $r_1, $r_2, $r_3);
				
				
			//upper
			$search_value_arabic = $regexp[] = self::convert_umlauts($upper);
			$search_value_umlaut = $regexp[] = self::convert_umlauts($upper, true);
			$r_1 = self::search_convert($upper);
			$r_2 = self::search_convert($search_value_arabic);
			$r_3 = self::search_convert($search_value_umlaut);
			$regexp = array_merge($regexp, $r_1, $r_2, $r_3);
			
			
			if ($preg_quote) {
			    $regexp = array_map('preg_quote', $regexp);
			}
			
			if ($begins_with) {
			    //add begins with... so we have a natural sorting order?
			    $regexp = preg_filter('/^/', '^', $regexp);
			}
			
			$val = array_unique($regexp);
			
			if ( ! $return_array) {
			    $val = implode("|", $val);
			} 
			
		}
		
		/**
		 * http://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
		 */
		public static function normalizeString ($str = '')
		{
		    $str = strip_tags($str); 
		    $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
		    $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
		   // $str = strtolower($str);
		    $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
		    $str = htmlentities($str, ENT_QUOTES, "utf-8");
		    $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
		    $str = str_replace(' ', '-', $str);
		    $str = rawurlencode($str);
		    $str = str_replace('%', '-', $str);
		    return $str;
		}
		
		
		public static function filter_filename($filename, $beautify=true) {
			// sanitize filename
			$filename = preg_replace(
					'~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
					'-', $filename);
			// avoids ".", ".." or ".hiddenFiles"
			$filename = ltrim($filename, '.-');
			
			// optional beautification
			if ($beautify) $filename = self::beautify_filename($filename);
			
			// maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if (($mbdetectencoding = mb_detect_encoding($filename)) === false) {
				$mbdetectencoding = "UTF-8";
			}
			$filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), $mbdetectencoding) . ($ext ? '.' . $ext : '');
			return $filename;
		}
		
		public static function beautify_filename($filename) {
			
			// replace tokens
			$filename = preg_replace(array(
					// "file%date%name.zip" becomes "file20170907name.zip"
					'/%date%/',
					// "file%time%name.zip" becomes "file075901name.zip"
					'/%time%/',
					// "file%%%name.zip" becomes "file-name.zip"
					'/%+/'
			), array(
					date('Ymd'),
					date('His'),
					'-',
			), $filename);
			
			// reduce consecutive characters
			$filename = preg_replace(array(
					// "file   name.zip" becomes "file-name.zip"
					'/ +/',
					// "file___name.zip" becomes "file-name.zip"
					'/_+/',
					// "file---name.zip" becomes "file-name.zip"
					'/-+/'
			), '-', $filename);
			
			$filename = preg_replace(array(
					// "file--.--.-.--name.zip" becomes "file.name.zip"
					'/-*\.-*/',
					// "file...name..zip" becomes "file.name.zip"
					'/\.{2,}/'
			), '.', $filename);
			// lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
// 			if (($mbdetectencoding = mb_detect_encoding($filename)) === false) {
// 				$mbdetectencoding = "UTF-8";
// 			}
// 			$filename = mb_strtolower($filename, $mbdetectencoding);
			// ".file-name.-" becomes "file-name"
			$filename = trim($filename, '.-');
			return $filename;
		}
		
		
		/**
		 * referee.mx/wp-content/uploads/unicode_fix.php
		 */
		public static function unicode_conv($originalString , $reveres_con = false) {
		
			$unicodes = array(
					"#U00c0" => "À",
					"#U00c1" => "Á",
					"#U00c2" => "Â",
					"#U00c3" => "Ã",
					"#U00c4" => "Ä",
					"#U00c5" => "Å",
					"#U00c6" => "Æ",
					"#U00c7" => "Ç",
					"#U00c8" => "È",
					"#U00c9" => "É",
					"#U00ca" => "Ê",
					"#U00cb" => "Ë",
					"#U00cc" => "Ì",
					"#U00cd" => "Í",
					"#U00ce" => "Î",
					"#U00cf" => "Ï",
					"#U00d1" => "Ñ",
					"#U00d2" => "Ò",
					"#U00d3" => "Ó",
					"#U00d4" => "Ô",
					"#U00d5" => "Õ",
					"#U00d6" => "Ö",
					"#U00d8" => "Ø",
					"#U00d9" => "Ù",
					"#U00da" => "Ú",
					"#U00db" => "Û",
					"#U00dc" => "Ü",
					"#U00dd" => "Ý",
					"#U00df" => "ß",
					"#U00e0" => "à",
					"#U00e1" => "á",
					"#U00e2" => "â",
					"#U00e3" => "ã",
					"#U00e4" => "ä",
					"#U00e5" => "å",
					"#U00e6" => "æ",
					"#U00e7" => "ç",
					"#U00e8" => "è",
					"#U00e9" => "é",
					"#U00ea" => "ê",
					"#U00eb" => "ë",
					"#U00ec" => "ì",
					"#U00ed" => "í",
					"#U00ee" => "î",
					"#U00ef" => "ï",
					"#U00f0" => "ð",
					"#U00f1" => "ñ",
					"#U00f2" => "ò",
					"#U00f3" => "ó",
					"#U00f4" => "ô",
					"#U00f5" => "õ",
					"#U00f6" => "ö",
					"#U00f8" => "ø",
					"#U00f9" => "ù",
					"#U00fa" => "ú",
					"#U00fb" => "û",
					"#U00fc" => "ü",
					"#U00fd" => "ý",
					"#U00ff" => "ÿ",
					"#U00bf" => "¿",
					"#U00a1" => '¡',
					"#U00b0" => '°'
			);
			
			if ($reveres_con) {
				$unicodes = array_flip($unicodes);
			}
			
			
			return str_replace(array_keys($unicodes), $unicodes, $originalString);
		}
		
		
		public function a_sort($sorted_array)
		{
			//convert umlauts
			$converted_array = $sorted_array;
			array_walk($converted_array, function(&$value) {
				$value = Pms_CommonData::convert_umlauts($value);
			});
			asort($converted_array);

			//extract converted keys
			$converted_sorted_keys = array_keys($converted_array);

			//recreate initial array based on previous converted keys
			foreach($converted_sorted_keys as $kv => $vv)
			{
				$final_sorted_array[$vv] = $sorted_array[$vv];
			}

			return $final_sorted_array;
		}

		public function ar_sort($sorted_array)
		{
			//convert umlauts
			$converted_array = $sorted_array;
			array_walk($converted_array, function(&$value) {
				$value = Pms_CommonData::convert_umlauts($value);
			});
			arsort($converted_array);

			//extract converted keys
			$converted_sorted_keys = array_keys($converted_array);

			//recreate initial array based on previous converted keys
			foreach($converted_sorted_keys as $kv => $vv)
			{
				$final_sorted_array[$vv] = $sorted_array[$vv];
			}

			return $final_sorted_array;
		}

		//this will be the list with forms wich can have items mapped to specific contact form types
		//we use as form identifier the function name 
		public function mapped_forms()
		{
			$forms['1'] = 'shanlage14';
			$forms['2'] = 'sapvfb8_lmu';

			return $forms;
		}

		public function excluded_fl_patients()
		{
			//add here first day of period of the month (Y-m-d)
			$excluded_ipids_data = array(
				//live BWS10041
				'655aa3ac5880703d344eb1202d25259b8c6ecd54' => array(
					'2014-12-01',
				),
				//live TP10733
				'9bab92d550e687dfc624cf9954dd7c7a278c8162' => array(
					'2014-12-01',
				),
			);

			return $excluded_ipids_data;
		}

		//return the period(s) in which the BW Flatrate is excluded
		public function exclude_bw_flatrate($ipid)
		{
			$patientmaster = new PatientMaster();
			$excluded_ipids_data = self::excluded_fl_patients();

			if(array_key_exists($ipid, $excluded_ipids_data) && count($excluded_ipids_data[$ipid]) > '0')
			{
				foreach($excluded_ipids_data[$ipid] as $k_period => $v_period_start)
				{
					$month_days_nr = '';

					if(!function_exists('cal_days_in_month'))
					{
						$month_days_nr = date('t', mktime(0, 0, 0, date("n", strtotime($v_period_start)), 1, date("Y", strtotime($v_period_start))));
					}
					else
					{
						$month_days_nr = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_period_start)), date("Y", strtotime($v_period_start)));
					}

					if(empty($month_days_arr[$ipid]))
					{
						$month_days_arr[$ipid] = array();
					}

					if($month_days_nr)
					{
						$month_days_arr[$ipid] = array_merge($month_days_arr[$ipid], $patientmaster->getDaysInBetween($v_period_start, date('Y-m-', strtotime($v_period_start)) . $month_days_nr));
					}
				}


				return $month_days_arr[$ipid];
			}
			elseif(in_array($ipid, $excluded_ipids_data))
			{
				return $patientmaster->getDaysInBetween("2009-01-01", date('Y-m-d', time()));
			}
			else
			{
				return false;
			}
		}

		//convert upercase first letter using utf8 encoding
		//used in multiple array sort with self::a_sort and self::ar_sort
		function mb_ucfirst($string, $encoding = "utf-8")
		{
			$strlen = mb_strlen($string, $encoding);
			$firstChar = mb_substr($string, 0, 1, $encoding);
			$then = mb_substr($string, 1, $strlen - 1, $encoding);

			return mb_strtoupper($firstChar, $encoding) . $then;
		}

		public static function get_ipid_from_epid($epid, $clientid)
		{
			$pt = Doctrine_Query::create()
			->select('id, ipid')
			->from('EpidIpidMapping')
			->where('epid = ?', $epid)
			->andwhere('clientid = ?', $clientid)
			->fetchArray();
			
			if ( !empty($pt[0]['ipid']) ) {
				return $pt[0]['ipid'];
			}
			/* ZF1
			$pt = Doctrine::getTable('EpidIpidMapping')->findOneByEpidAndClientid($epid, $clientid);

			if($pt)
			{
				$ptarray = $pt->toArray();
				$ipid = $ptarray['ipid'];

				return $ipid;
			}
			*/
		}

		function sql_getters($case)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			switch($case)
			{
				//used with common data patients active function
				case 'patients_active':
					//get active ipids details
					$sql = "a.*,e.*,p.*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
					$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
					$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
					$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
					$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
					$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
					$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
					$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
					$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
					$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
					$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
					$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";

					// if super admin check if patient is visible or not
					if($logininfo->usertype == 'SA')
					{
						$sql = "a.*,e.*,p.*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex ";
					}
					break;

				default:
					$sql = '*';
					break;
			}

			return $sql;
		}

		public function calculate_meeting_xt_duration($meeting_details = false, $default_duration = "10")
		{
			$checks_passed = true;
			if($meeting_details && $meeting_details['patients_no'] > '0')
			{
				//calculate meeting duration
				if(!empty($meeting_details['date']))
				{
					$meeting_date = $meeting_details['date'];
				}
				else
				{
					$checks_passed = false;
				}

				$start_time_details = explode(':', $meeting_details['start_time']);
				if(!empty($meeting_details['start_time']) && count($start_time_details) == '2')
				{
					$start_h = $start_time_details[0];
					$start_m = $start_time_details[1];
				}
				else
				{
					$checks_passed = false;
				}

				$end_time_details = explode(':', $meeting_details['end_time']);
				if(!empty($meeting_details['end_time']) && count($end_time_details) == '2')
				{
					$end_h = $end_time_details[0];
					$end_m = $end_time_details[1];
				}
				else
				{
					$checks_passed = false;
				}

				if($checks_passed !== false)
				{
					$meeting_duration = Pms_CommonData::calculate_visit_duration($start_h, $end_h, $start_m, $end_m, $meeting_date);

					//calculate xt duration (meeting_duration / patients_no)
					if($meeting_duration > '0')
					{
						$calculated_xt_duration = round($meeting_duration / $meeting_details['patients_no']);

						if($calculated_xt_duration > '0')
						{
							//finaly we entered in the matrix
							return $calculated_xt_duration;
						}
						else
						{
							return $default_duration;
						}
					}
					else
					{
						return $default_duration;
					}
				}
				else
				{
					return $default_duration;
				}
			}
			else
			{
				//return default duration in case of something wrong
				return $default_duration;
			}
		}

		public function allinvoices($for_permisions = false)
		{
			//all from ISPC-1259 -> 1136 (used only bayern tagepauschale)
			$invoices = array(
				"bw_sapv_invoice_new",
//				"bw_sgbv_invoice",
//				"bw_sgbxi_invoice",
				"bw_medipumps_invoice",
//				"bayern_sapv_payback", //Bayern Tagepauschale Send Money Back
//				"he_invoice",
//				"bayern_invoice",
//				"nd_hi_invoice",
//				"nd_user_invoice",
//				"bre_sapv_invoice",
//				"bre_hospiz_invoice",
				"bayern_sapv_invoice", //Bayern Tagepauschale Invoice
				"sh_invoice", //SH Invoice
				"hospiz_invoice", //Hospiz Invoice :: ISPC 1679
				"rlp_invoice", //RLP billing  :: ISPC 2143
				"bre_kinder_invoice",//bremen Kinder new billing :: ISPC-2214
				"nr_invoice",//Nordrhein - new billing for new contract :: ISPC-2286
				"demstepcare_invoice"//Demstepcare ISPC-2461
			    
			);
			
			if($for_permisions == false) // this are not shown in client invoice type select
			{
				$second_invoices = array(
					"sh_internal_invoice", //SH internal invoice (available in sauna)
					"sh_shifts_internal_invoice", //SH internal invoice - user shifts (ISPC-2257 11.10.2018)
    				"bra_invoice", //BRA Invoice
    				"members_invoice", //BRA Invoice
    				"by_invoice", // OLD Invoice system :: ISPC-2016
    				"internal_invoice", // Internal invoices :: ISPC-2233 (30.08.2018)
					"nie_patient_invoice", //ISPC - 2365 - add bulk print
					"nie_user_invoice", //ISPC - 2365 - add bulk print
				    "demstepcare_internal_invoice", //ISPC-2585 Ancuta 16.06.2020
					//ISPC-2745 Carmen 16.11.2020
					"bw_sgbv_invoice",
					"bw_sgbxi_invoice",
					"bre_sapv_invoice",
					//"bre_hospiz_sapv_invoice", -> moved on top
					"he_invoice",
					"rpinvoice",
					//--
						
				);
				
				$invoices = array_merge($invoices, $second_invoices);
			}

			return $invoices;
		}
		
		
		/**
		 * ISPC-2312 Ancuta 08.12.2020 copy of  allinvoices
		 * @param boolean $for_permisions
		 * @return string[]|array
		 */
		public function allinvoicesmultiple($for_permisions = false)
		{
			//all from ISPC-1259 -> 1136 (used only bayern tagepauschale)
			$invoices = array(
				"bw_sapv_invoice_new",
				"bw_sgbv_invoice", //ISPC-2312 Ancuta 07.12.2020 - this is from now allowed
				"bw_sgbxi_invoice",//ISPC-2312 Ancuta 07.12.2020 - this is from now allowed
				"bw_medipumps_invoice",
//				"bayern_sapv_payback", //Bayern Tagepauschale Send Money Back
				"he_invoice",//ISPC-2312 Ancuta 07.12.2020 - this is from now allowed
//				"bayern_invoice",
			    "rpinvoice",//ISPC-2312 Ancuta 07.12.2020 - this is from now allowed
                "nie_patient_invoice",//ISPC-2312 Ancuta 07.12.2020 - this is from now allowed
				//"nie_user_invoice",//ISPC-2312 Ancuta 07.12.2020 - this is from now allowed
				"bre_sapv_invoice",//ISPC-2312 Ancuta 07.12.2020 - this is from now allowed
			    "bre_hospiz_sapv_invoice",//ISPC-2312 Ancuta 07.12.2020 - this is from now allowed
				"bayern_sapv_invoice", //Bayern Tagepauschale Invoice
				"sh_invoice", //SH Invoice
				"hospiz_invoice", //Hospiz Invoice :: ISPC 1679
				"rlp_invoice", //RLP billing  :: ISPC 2143
				"bre_kinder_invoice",//bremen Kinder new billing :: ISPC-2214
				"nr_invoice",//Nordrhein - new billing for new contract :: ISPC-2286
				"demstepcare_invoice",//Demstepcare ISPC-2461
			    "by_invoice", //ISPC-2312 Ancuta 07.12.2020 - this is from now allowed
			    
			);
			
			if($for_permisions == false) // this are not shown in client invoice type select
			{
				$second_invoices = array(
					"sh_internal_invoice", //SH internal invoice (available in sauna)
					"sh_shifts_internal_invoice", //SH internal invoice - user shifts (ISPC-2257 11.10.2018)
    				"bra_invoice", //BRA Invoice
    				"members_invoice", //BRA Invoice
    				//"by_invoice", // OLD Invoice system :: ISPC-2016
    				"internal_invoice", // Internal invoices :: ISPC-2233 (30.08.2018)
					"nie_patient_invoice", //ISPC - 2365 - add bulk print
					"nie_user_invoice", //ISPC - 2365 - add bulk print
				    "demstepcare_internal_invoice", //ISPC-2585 Ancuta 16.06.2020
					//ISPC-2745 Carmen 16.11.2020
					"bw_sgbv_invoice",
					"bw_sgbxi_invoice",
					"bre_sapv_invoice",
					//"bre_hospiz_sapv_invoice", -> moved on top
					"he_invoice",
					"rpinvoice",
					//--
						
				);
				
				$invoices = array_merge($invoices, $second_invoices);
			}

			return $invoices;
		}

		//click links based on invoice_type
		public function invoices_links($invoice = false)
		{
			$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();

			$inv_links['click_action'] = array(
			    
				"bayern_sapv_invoice" => $controller . "/bayernsapvinvoice",
				"sh_invoice" => $controller . "/shanlage14invoice",
				"bw_sapv_invoice_new" => $controller . "/bwsapvinvoice",
				"bw_medipumps_invoice" => $controller . "/bwmedipumpsinvoice",
				"rlp_invoice" => $controller . "/rlpinvoice",
				"bre_kinder_invoice" => $controller . "/systeminvoice",
				"nr_invoice" => $controller . "/systeminvoice",
				"demstepcare_invoice" => $controller . "/systeminvoice",
				"demstepcare_internal_invoice" => $controller . "/systeminvoice",
			    
				//ISPC-2746 Carmen 25.11.2020
				"bw_sgbv_invoice" => $controller . "/patientlist?invoice_type=bw_sgbv_invoice",
				"bw_sgbxi_invoice" => $controller . "/patientlist?invoice_type=bw_sgbxi_invoice",
				"he_invoice" => $controller . "/patientlist?invoice_type=he_invoice",
				"by_invoice" => $controller . "/patientlist?invoice_type=by_invoice",
				//--
			);


			if($invoice)
			{
				return $inv_links[$invoice];
			}
			else
			{
				return $inv_links;
			}
		}

		/**
		 * ISPC-2312 Ancuta 08.12.2020
		 * @param boolean $invoice
		 * @return string|string[]
		 */
		public function invoices_links2new($invoice = false)
		{
			$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();

			$inv_links['click_action'] = array(
			    
				"bayern_sapv_invoice" => "invoicenew/bayernsapvinvoice",
				"sh_invoice" => "invoicenew/shanlage14invoice",
				"bw_sapv_invoice_new" => "invoicenew/bwsapvinvoice",
			    "bw_medipumps_invoice" => "invoicenew/bwmedipumpsinvoice",
				"rlp_invoice" => "invoicenew/rlpinvoice",
				"bre_kinder_invoice" => "invoicenew/systeminvoice",
				"nr_invoice" => "invoicenew/systeminvoice",
				"demstepcare_invoice" => "invoicenew/systeminvoice",
			    "demstepcare_internal_invoice" => "invoicenew/systeminvoice",
			    
				//ISPC-2746 Carmen 25.11.2020
				"bw_sgbv_invoice" => "invoiceclient/patientlist?invoice_type=bw_sgbv_invoice",
				"bw_sgbxi_invoice" => "invoiceclient/patientlist?invoice_type=bw_sgbxi_invoice",
				"he_invoice" => "invoiceclient/patientlist?invoice_type=he_invoice",
				"rpinvoice" => $controller . "/patientlist?invoice_type=rpinvoice",
				"hospiz_invoice" => $controller . "/patientlist?invoice_type=hospiz_invoice",
				"by_invoice" => $controller . "/patientlist?invoice_type=by_invoice",
				//--
			);


			if($invoice)
			{
				return $inv_links[$invoice];
			}
			else
			{
				return $inv_links;
			}
		}

		public function generate_csv($data, $filename = 'export.csv', $field_delimiter = ',', $enclosure = '"')
		{
			$file = fopen('php://output', 'w');
			foreach($data as $key => $values)
			{
				if(empty($enclosure)) { //no enclosure needed
					fputs($file, implode($values, $field_delimiter)."\r\n");
				} else {
					fputcsv($file, $values, $field_delimiter, $enclosure);
				}
			}
			
			
			
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-dType: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=" . $filename);
			exit;
		}

		public static function get_patients_client($ipid = false)
		{
			if(is_array($ipid))
			{
				$ipids = $ipid;
			}
			elseif($ipid !== false)
			{
				$ipids = array($ipid);
			}

			$cldata = Doctrine_Query::create()
			->select('ipid, clientid')
			->from('EpidIpidMapping')
			->whereIn("ipid", $ipids);
			$clarr = $cldata->fetchArray();
			
			/* ZF1
			$cldata = Doctrine_Query::create()
			->select('*')
			->from('EpidIpidMapping')
			->whereIn("ipid", $ipids);
			$clarr = $cldata->fetchArray();
			*/
				
			if($clarr)
			{
				if(count($clarr) > 0)
				{
					foreach($clarr as $k_pat => $v_pat)
					{
						$ipid2client[$v_pat['ipid']] = $v_pat['clientid'];
					}

					return $ipid2client;
				}
				else
				{
					return false;
				}
			}
		}

		public function receipt_types()
		{
			$types = array(
				'kv_blank', //GKV-Rezept
				'kv_btm', //BTM Rezept
				'kv_green', //Grünes Rezept
				'kv_blue', //Privatrezept
				'kv_aid', //Hilfsmittel
			);
			return $types;
		}

		public function receipt_statuses()
		{
			$Tr = new Zend_View_Helper_Translate();

			$status_data['images'] = array(
				"1" => APP_BASE . 'icons_system/receipt_icon_red.png', //file
				"2" => APP_BASE . 'icons_system/receipt_icon_yellow.png', //print
				"3" => APP_BASE . 'icons_system/receipt_icon_green.png' //fax
			);

			$status_data['labels'] = array(
				"1" => $Tr->translate('receipt_status_1'),
				"2" => $Tr->translate('receipt_status_2'),
				"3" => $Tr->translate('receipt_status_3')
			);

			return $status_data;
		}

		public function receipt_status_colours()
		{
			$colors_data['status2css'] = array(
				"g" => "receipt_status_color_cell_green",
				"r" => "receipt_status_color_cell_red",
				"w" => "receipt_status_color_cell_white",
				"b" => "receipt_status_color_cell_white"
			);

			return $colors_data;
		}

		//new receipts only ("gww" status like 2 icons images)
		public function status2icons($status)
		{
			$status_colours = self::receipt_status_colours();
			$status_images = self::receipt_statuses();

			if(strlen($status) > '0' && strlen($status) <= "3")
			{
				$status_arr = str_split($status);

				foreach($status_arr as $k_status => $v_status_colour)
				{
					$new_status_data[] = array(
						'image' => $status_images['images'][($k_status + 1)],
						'css' => $status_colours['status2css'][$v_status_colour],
						'colour' => $v_status_colour
					);
				}

				return $new_status_data;
			}
			else
			{
				return false;
			}
		}
		
		public static function patients_standby($select = "*", $client = null, $periods = null, $ipids = null, $order_by = "p.last_name", $sort = "ASC", $search_sql = false, $limit = false, $page = '0')
		{
			if($periods)
			{
				foreach($periods as $period)
				{
					if(empty($period['end']))
					{
						$period['end'] = date('Y-m-d', strtotime('+1 day'));
					}
		
					$sql_period .= ' OR ((a.start BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (a.end BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (a.start <= "' . $period['start'] . '" AND (a.end = "0000-00-00" OR a.end >= "' . $period['end'] . '")))';
				}
		
				$sql_period = ' (' . substr($sql_period, 3) . ') ';
			}
			else
			{
				$sql_period = '1';
			}
		
			if($client && is_numeric($client))
			{
				$sql_client = 'e.clientid = "' . $client . '"';
			}
			else
			{
				$sql_client = '1';
			}
		
			if($ipids)
			{
				foreach($ipids as $ipid)
				{
					$sql_ipids .= '"' . $ipid . '",';
				}
		
				$sql_ipids = 'e.ipid IN (' . substr($sql_ipids, 0, -1) . ')';
			}
			else
			{
				$sql_ipids = '1';
			}
		
			$q = Doctrine_Query::create()
			->select($select)
			->from('EpidIpidMapping e')
			->leftJoin('e.PatientMaster p')
			->leftJoin('e.PatientActive a')
			->where($sql_client)
			->andWhere($sql_period)
			->andWhere($sql_ipids)
			->andWhere('p.isdelete = 0')
			->andWhere('p.isstandby = 1')
			->andWhere('p.isstandbydelete = 0')
			->orderBy($order_by . ' ' . $sort);
		
			if($search_sql)
			{
				$q->andWhere($search_sql);
			}
		
			//disable group by if counting
			if(strpos($select, "count") === false)
			{
				$q->groupBy('e.ipid');
			}
		
			if($limit)
			{
				if(is_numeric($limit) && $limit > '0')
				{
					$q->limit($limit);
					$q->offset($page * $limit);
				}
			}
			//			print_r($q->getSqlQuery());
			//			exit;
		
			return $q->fetchArray();
		}

		
		public static function get_medi_plans()
		{
			$Tr = new Zend_View_Helper_Translate();
			
			$plans_array = array(
					//"0" => "", 
					"1" => "Medikamente Wochenplan", 
					"2" => $Tr->translate('medicationgeneratepdf'), 
					"3" => "Medikation / Dosierung Plan", 
					"4" => "Schmerzpumpe Plan", 
					"5" => $Tr->translate('schmerzeandmedipdf'), 
					"6" => "Behandlungspflege Plan", 
					"7" => "Medikamentenplan"
			);
			
			return $plans_array;
		}
		
		
		public static function get_visit_types()
		{
			
			$types_array = array(
                 "kvno_nurse_form" =>'kvno_nurse_form',
		         "kvno_doctor_form" =>'kvno_doctor_form',
			     "visit_koordination_form" =>'visit_koordination_form',
			     "bayern_doctorvisit" =>'bayern_doctorvisit'
			);
			return $types_array;
		}
 
    
    
    public static function getKarnofskyFulltext($val=false){
		$Tr = new Zend_View_Helper_Translate();
        
        $k = array(
            "0" => " ",
            "10" => $Tr->translate('10_karnofsky_label_clinik'),
            "20" => $Tr->translate('20_karnofsky_label_clinik'),
            "30" => $Tr->translate('30_karnofsky_label_clinik'),
            "40" => $Tr->translate('40_karnofsky_label_clinik'),
            "50" => $Tr->translate('50_karnofsky_label_clinik'),
            "60" => $Tr->translate('60_karnofsky_label_clinik'),
            "70" => $Tr->translate('70_karnofsky_label_clinik'),
            "80" => $Tr->translate('80_karnofsky_label_clinik'),
            "90" => $Tr->translate('90_karnofsky_label_clinik'),
            "100" => $Tr->translate('100_karnofsky_label_clinik'),
        );       
        
        if($val){
            return $k[$val];
        } else{
            return $k;
        }        
        
    }
    
    public static function get_karnofsky()
    {
		$Tr = new Zend_View_Helper_Translate();
        $karnofsky = array(
            // "" => $Tr->translate('please_select'),
            "0" => array(
                "value" => "100",
                "label" => $Tr->translate('100_karnofsky_label')
            ),
            "1" => array(
                "value" => "90",
                "label" => $Tr->translate('90_karnofsky_label')
            ),
            "2" => array(
                "value" => "80",
                "label" => $Tr->translate('80_karnofsky_label')
            ),
            "3" => array(
                "value" => "70",
                "label" => $Tr->translate('70_karnofsky_label')
            ),
            "4" => array(
                "value" => "60",
                "label" => $Tr->translate('60_karnofsky_label')
            ),
            "5" => array(
                "value" => "50",
                "label" => $Tr->translate('50_karnofsky_label')
            ),
            "6" => array(
                "value" => "40",
                "label" => $Tr->translate('40_karnofsky_label')
            ),
            "7" => array(
                "value" => "30",
                "label" => $Tr->translate('30_karnofsky_label')
            ),
            "8" => array(
                "value" => "20",
                "label" => $Tr->translate('20_karnofsky_label')
            ),
            "9" => array(
                "value" => "10",
                "label" => $Tr->translate('10_karnofsky_label')
            ),
            "10" => array(
                "value" => "0",
                "label" => $Tr->translate('0_karnofsky_label')
            )
        );
 
        return $karnofsky;
    }
    public static function get_karnofsky_clinic()
    {
		$Tr = new Zend_View_Helper_Translate();
        $karnofsky = array(
            // "" => $Tr->translate('please_select'),
            "0" => array(
                "value" => "0",
                "label" => $Tr->translate('0_karnofsky_label_clinik')
            ),
            "1" => array(
                "value" => "10",
                "label" => $Tr->translate('10_karnofsky_label_clinik')
            ),
            "2" => array(
                "value" => "20",
                "label" => $Tr->translate('20_karnofsky_label_clinik')
            ),
            "3" => array(
                "value" => "30",
                "label" => $Tr->translate('30_karnofsky_label_clinik')
            ),
            "4" => array(
                "value" => "40",
                "label" => $Tr->translate('40_karnofsky_label_clinik')
            ),
            "5" => array(
                "value" => "50",
                "label" => $Tr->translate('50_karnofsky_label_clinik')
            ),
            "6" => array(
                "value" => "60",
                "label" => $Tr->translate('60_karnofsky_label_clinik')
            ),
            "7" => array(
                "value" => "70",
                "label" => $Tr->translate('70_karnofsky_label_clinik')
            ),
            "8" => array(
                "value" => "80",
                "label" => $Tr->translate('80_karnofsky_label_clinik')
            ),
            "9" => array(
                "value" => "90",
                "label" => $Tr->translate('90_karnofsky_label_clinik')
            ),
            "10" => array(
                "value" => "100",
                "label" => $Tr->translate('100_karnofsky_label_clinik')
            )
        );
 
        return $karnofsky;
    }
 
 

    
    public function death_wish_locations($drop = false)
    {
        $Tr = new Zend_View_Helper_Translate();
        $holiday = $Tr->translate('dwl_holiday');
        $atneighbor = $Tr->translate('dwl_atneighbor');
        $atwork = $Tr->translate('dwl_atwork'); 


        $home = $Tr->translate('dwl_home');
        $clinic = $Tr->translate('dwl_clinic');
        $athospiz = $Tr->translate('dwl_hospiz');
        $atheim = $Tr->translate('dwl_heim');
        $atcp = $Tr->translate('dwl_contact_person');
        
        if($drop){
            $death_wish_loc = array(
                '0' => $Tr->translate('select death wish location'), 
                '1' => $home, 
                '5'=>$clinic,
                '6'=>$athospiz,
                '7'=>$atheim,
                '8'=>$atcp
                
            );
             
        } else {
            $death_wish_loc = array(
                '1' => $home, 
                '5'=>$clinic,
                '6'=>$athospiz,
                '7'=>$atheim,
                '8'=>$atcp
                
                
                
            );
        }
    
        return $death_wish_loc;
    }
    
    
    public function care_options($drop = false )
    {
        $Tr = new Zend_View_Helper_Translate();
        
        $co_unnecessary = $Tr->translate('co_unnecessary');
        $co_available = $Tr->translate('co_available');
        $co_no = $Tr->translate('co_no');
        $co_planned = $Tr->translate('co_planned');
        $co_rejected = $Tr->translate('co_rejected');


        
        
        if($drop){
            $death_wish_loc = array('0'=> $Tr->translate('select death wish location'), '1' => $co_unnecessary, '2' => $co_available, '3' => $co_no, '4' => $co_planned, '5' => $co_rejected);
             
        } else {
            $death_wish_loc = array( '1' => $co_unnecessary, '2' => $co_available, '3' => $co_no, '4' => $co_planned, '5' => $co_rejected);
        }
    
        return $death_wish_loc;
    }

 
    public  function get_dates_of_quarter($quarter = 'current', $year = false, $format = null)
    {
        if ( !$year ) {        
        $year = date("Y",time());
        }
        
        $current_quarter = ceil(date('n')/3);
        switch (  strtolower($quarter) ) {
            case 'this':
            case 'current':
                $quarter = ceil(date('n')/3);
                break;
 
            case 'first':
            case '1':
                $quarter = 1;
                break;
    
            case 'second':
            case '2':
                $quarter = 2;
                break;
    
            case 'third':
            case '3':
                $quarter = 3;
                break;
    
            case 'last':
            case '4':
                $quarter = 4;
                break;
    
            default:
                $quarter = (!is_int($quarter) || $quarter < 1 || $quarter > 4) ? $current_quarter : $quarter;
                break;
        }
//         if ( $quarter === 'this' ) {
//             $quarter = ceil(date('n')/3);
//         }
        //date_default_timezone_set('Europe/Berlin');
        
        
        $start = new DateTime($year.'-'.(3*$quarter-2).'-1 00:00:00');
        $end = new DateTime($year.'-'.(3*$quarter).'-'.($quarter == 1 || $quarter == 4 ? 31 : 30) .' 23:59:59');
        
        return array(
            'start' => $format ? $start->format($format) : $start,
            'end' => $format ? $end->format($format) : $end,
        );
    }
    
    
    public function get_voluntary_shortcuts(){
        
        $shortcuts = array(
            array(
                "shortcut" => "K",
                "ctype" => "Kommentar",
                "font_color" => "000000",
                "isbold" => "0",
                "isitalic" => "0",
                "isunderline" => "0",
                "chk" => "0"
            ),
            array(
                "shortcut" => "M",
                "ctype" => "Mitarbeitergespräch",
                "font_color" => "000000",
                "isbold" => "1",
                "isitalic" => "0",
                "isunderline" => "0",
                "chk" => "0"
            ),
            array(
                "shortcut" => "XT",
                "ctype" => "Telefon",
                "font_color" => "FF0000",
                "isbold" => "0",
                "isitalic" => "0",
                "isunderline" => "0",
                "chk" => "0"
            ),
            array(
                "shortcut" => "P",
                "ctype" => "Persönliches",
                "font_color" => "000000",
                "isbold" => "0",
                "isitalic" => "0",
                "isunderline" => "1",
                "chk" => "0"
            ),
            //ISPC-2908,Elena,21.05.2021
            array(
                "shortcut" => "W",
                "ctype" => "TODO",
                "font_color" => "de358f",
                "isbold" => "1",
                "isitalic" => "0",
                "isunderline" => "0",
                "chk" => "0"
            )
        );
        return $shortcuts;
    }    
    
    
    // 
    /**
     * TODO: create Pms_FtpQueue and refactor
     * 
     * function ftp_download works only for password protected zip files, created by application
     * dowload of duplicate ziparchive, will fail,  if file_name field+clientid is not unique (both random folder and pdf name are the same) ... a id join should be implemented for this and faster queries
     * 
     * it only works with one file at a time... function must be modified for multiple downloads, so you don't create a new ftp_resource for every file
     * 
     * @param string $ftpath - usualy will result in legacy_path + zipfile
     * @param string $file_password
     * @param string $old
     * @param string $clientid
     * @param string $file_name  - usualy the file_name filed from the table of the owneship
     * @param string $first_location2search
     * @return boolean|string
     */
    public static function ftp_download($ftpath = '',  $file_password = '', $old = false , $clientid = null , $file_name = null, $parent_table = NULL, $parent_table_id = NULL)
    {        	
//     	setlocale(LC_ALL, 'de_DE.UTF-8');
    	
    	if ( $ftpath == '' || $file_password == '') {
    		return false;
    	}
    	
    	// local path to store the zipped file
    	$local_file_path = FTP_DOWNLOAD_PATH . "/". basename($ftpath);
    	
    	//if file_exists(zipeed) on localhost (after ftp_get or file-copy from ftp_put_queue folder)
    	$found_localy = false;
    	
    	//if filename was uploade to ftp and there was another with same name, so we uploaded the file with a prefix
    	$duplicate_file = false;
    	
    	//maybe file was not yet uploaded to ftp, or first we have to look localy for the zip
    	if ( ! $found_localy && ! is_null($file_name)) {
    		
    		if (is_null( $clientid)) {
    			
    			$logininfo	= new Zend_Session_Namespace('Login_Info');
    			$clientid	= $logininfo->clientid;
    			
    		}

    		$query = null;
    		if ( ! empty($parent_table) && ! empty($parent_table_id)) { 
    		    /**
    		     * @since 06.08.2018
    		     */
    		    $query = Doctrine_Query::create()
    		    ->select('*')
    		    ->from('FtpPutQueue')
    		    ->where('clientid = ?', (int)$clientid)
    		    ->andWhere('parent_table = ?', $parent_table)
    		    ->andWhere('parent_table_id = ?', $parent_table_id)
    		    ->andWhere('isdeleted = 0')
        		->andWhere("AES_DECRYPT(file_name, ?) = ?", [Zend_Registry::get('salt'), $file_name])
    		    ->limit(1)
    		    ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);    		    
    		} 
    		
    		if (empty($query)) {
    		
        		$query = Doctrine_Query::create()
        		->select('id, local_file_path, legacy_path, ftp_path, ftp_upload_performed')
        		->from('FtpPutQueue')
        		//->where('ftp_upload_performed = ?' , 'NO')
        		->where('clientid= ?', (int)$clientid) 
        		->andWhere('isdeleted = 0')
    //     		->andWhere("AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') = ?", $file_name) 
        		->andWhere("file_name = AES_ENCRYPT(?, ?)", [$file_name, Zend_Registry::get('salt')]) 
        		->limit(1)
        		->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY); 
        		
    		}
    		
    		if ($query['ftp_upload_performed'] == 'NO' && isset($query['local_file_path']) && file_exists(FTP_QUEUE_PATH ."/" .$query['local_file_path'])) {
       			
    			copy( FTP_QUEUE_PATH ."/" .$query['local_file_path'] , $local_file_path);
    			
    			if (file_exists($local_file_path)) {
    				
					$found_localy = true;
					    			
    			}
    			
    		} elseif ( $query['ftp_upload_performed'] == 'YES' && strpos($query['ftp_path'], Pms_FtpFileuploadv2::get_duplicate_file_prefix()) !== false) {
    			
    			//file was a duplicate
    			$ftpath = $query['ftp_path'];
    			
    			$duplicate_file = true;
    			
    			$legacy_path = $query['legacy_path'];
    			
    		}

    	}    	

		//zip file is not available localy
		//try to download from ftp
		if ( ! $found_localy ) {
			
			//connect to ftp
			if (defined("FTP_UPLOAD") && FTP_UPLOAD == "localhost") {
				$ftp_get = new Pms_FtpFileuploadFakeLocalhost();
			} else {
				$ftp_get = new Pms_FtpFileuploadv2();
			}
			
	    	if ($ftp_get->ftpconnect() === TRUE) {
	    		//download file
	    		if ($ftp_get -> filedownload($local_file_path, $ftpath, $old , $clientid) && file_exists($local_file_path) ){
	    			$found_localy = true;
	    		}
	    		
	    		$ftp_get->ftpconclose();
	    	}
		}
		
		
		//extract zip
		if ( $found_localy ) {
		    
		    /**
		     * update on 19.03.2018 added modifier param
		     * -o  overwrite files WITHOUT prompting  
		     */
// 			$cmd = "unzip -P " . $file_password . " ". $local_file_path .";";// rm -f ".$local_file_path.";";
			$cmd = "unzip -P " . $file_password . " -o ". $local_file_path ." -d '". FTP_DOWNLOAD_PATH . "'; rm -f ".$local_file_path .";";
			exec($cmd, $out, $rez);
			

			if (strpos($ftpath, 'clientuploads/') !== false) {

				$path = FTP_DOWNLOAD_PATH . "/clientuploads" ;
			
			} elseif(strpos($ftpath, 'uploads/') !== false) {
				
				$path = FTP_DOWNLOAD_PATH . "/uploads" ;
			
			} elseif ($duplicate_file) {
				
				$path = FTP_DOWNLOAD_PATH . "/". $legacy_path;
				
			} else {
				
				$path = FTP_DOWNLOAD_PATH ;
			}
					
			$path_info = pathinfo($local_file_path);
			
			$path .=   "/". $path_info['filename'] ;
			
			return $path;
			
		} else {
			//alert admin about missing file?
			return false;
		}
    	
    }
    
    /**
     * update 14.03.2018
	 * !! this fn return the full path
	 * !! this fn does not check like uniqfolder() in db
	 * + allways prefix
     *
     * TODO: self::uniqfolder() was modified
     * unify uniqfolder(), uniqfolder_v2(), tempfolder_for_ftp() under one single , and add 3rd param table where to check for unique name
     */
    public static function tempfolder_for_ftp( $prefix = '') 
    {
    	$dir = FTP_QUEUE_PATH;
    	
    	if (empty($prefix) || $prefix == 'date') {$prefix = date("dmY_");}
    	$template = "{$prefix}XXXXXXXXXX";
    	if (($dir) && (is_dir($dir))) { $tmpdir = "--tmpdir=$dir"; }
    	else { $tmpdir = '--tmpdir=' . sys_get_temp_dir(); }
    	return exec("mktemp -d $tmpdir $template");
    }
    
    
    //create zip archive of one single file
    //add file into ftp queue table for later transfer 
    //$legacy_path is the folder in wich the zip should be created localy, and depending on this folder the same applied on the ftp
    /**
     * TODO: create Pms_FtpQueue and refactor
     * 
     * @param string $local_file_path	= local file
     * @param string $legacy_path		= needed by the ftp upload function to know/create the path where the file is to be uploaded
     * @param array  $is_zipped			= !NULL ONLY when we allready have a passworded zip 
     * 									needed because of the ajax uploadify that uses $_SESSION['zipname']
     * 									$is_zipped = array(
     *	 										"is_zipped" => true,
	 *											"file_name" => $_SESSION['filename'],
	 *											"insert_id" => $inserted_file_id,
	 *											"db_table"	=> "PatientFileUpload",
	 * @param boolean $foster_file		= if no infos about this file are saved in any patient/cleint/vw/member files table... file is homeless.. 
	 * 									only record of it will be in ftp_que table
	 * 									forster files use another algorithm for ftp folders.. please check Pms_FtpFileuploadv2->foster_path_string
	 * 
     * @return boolean
     * 
     *  adding * at the end of zip uploads/folder/* will not include the .hidden files
     */
     public static function ftp_put_queue($local_file_path ,  $legacy_path = "uploads", $is_zipped = NULL, $foster_file = false , $clientid = NULL, $filepass = NULL)
     {	
//          dd(func_get_args());
     	//setlocale(LC_ALL, 'de_DE.UTF-8');
     	if ( is_null($clientid) || is_null($filepass) ){
	     	$logininfo	= new Zend_Session_Namespace('Login_Info');
	     	$clientid	= $logininfo->clientid;
	     	$userid		= $logininfo->userid;
	     	$usertype	= $logininfo->usertype;
	     	$filepass	= $logininfo->filepass;
     
     	} 
     	if (!file_exists($local_file_path) || !$clientid) {
     		return FALSE;
     	}
     	
     	//pathinfo() is locale aware, so for it to parse a path containing multibyte characters correctly, the matching locale must be set using the setlocale() function.
     	$path_parts = pathinfo($local_file_path);
     	$local_file_basename	= $path_parts['basename'];
     	$local_dirname_to_zip	= $path_parts['dirname'];
     	
     	$pathinfo_22  = pathinfo($path_parts['dirname']);
     	$local_file_parent_dir	=  $pathinfo_22['basename'];
     	
     	if ($local_file_basename == '' || $local_file_parent_dir == '' || $local_dirname_to_zip == ''){
     		return false;
     	}

     	
     	$lower_controllername = strtolower(Zend_Controller_Front::getInstance()->getRequest()->getControllerName());
     	$lower_actionname = strtolower(Zend_Controller_Front::getInstance()->getRequest()->getActionName());
     	
     	//local unique dir inside ftp_queue_folder to hold the new zip
     	$tempfolder_for_ftp = self::tempfolder_for_ftp();
     	
     	if ($tempfolder_for_ftp == '' || ! is_dir($tempfolder_for_ftp)) {
     		//cannot create temp folder to hold the zip
     		return FALSE;
     	}

     	//file is allready zipped with password, just movit to our que folder
     	if ( ! is_null($is_zipped) && $is_zipped['is_zipped']) {

     		$file_name_db = $is_zipped['file_name'] ; // name of path/file found inside the zip
     		
     		$zip_file_name =  $tempfolder_for_ftp . "/" . $local_file_basename; //zipfile full path from out que folder

     		//rename ( $local_file_path , $zip_file_name ); // move zip file to our folder
     		
            /**
             * REMEMBER TO MOVE FILE WHEN YOU ARE NOT IN DEBUG MODE
             */   
     		if (defined('APPLICATION_ENV')  && APPLICATION_ENV == 'development') {
     			$cmd_move_zip = "cp {$local_file_path} {$tempfolder_for_ftp} ";
     		} else {
     			$cmd_move_zip = "mv {$local_file_path} {$tempfolder_for_ftp} ";
     		}
     		@exec($cmd_move_zip);

     	} else {
     		//zip the file using password
	     	$zip_file_name =  $tempfolder_for_ftp . "/" . $local_file_parent_dir . ".zip"; //zipfile full path from out que folder
	
	     	if ($legacy_path == 'uploads' && strpos($local_file_path, "uploads/{$local_file_parent_dir}") !== false) {
	     		//use structure uploads/zipname/zipname.zip
	     		//export LC_ALL=de_DE.iso88591 && 
	     		$dirname = dirname($local_file_path);
	     		$cmd_create_zip = "sh -c \"cd '{$dirname}/../../'  && zip -9 -r -P " . $filepass ." " . $zip_file_name. " uploads/{$local_file_parent_dir}" . '/"';
	     		@exec($cmd_create_zip);
	     		
	     		//remove local zip file
	     		unlink($local_file_path);
	     		//remove local folder that holded our zip file;
	     		rmdir(dirname($local_file_path));
	     		
	     	} elseif ($legacy_path == 'clientuploads' && strpos($local_file_path, "clientuploads/{$local_file_parent_dir}") !== false) {
	     		
	     		$dirname = dirname($local_file_path);
	     		$cmd_create_zip = "sh -c \"cd '{$dirname}/../../'  && zip -9 -r -P " . $filepass ." " . $zip_file_name. " clientuploads/{$local_file_parent_dir}" . '/"';
	     		@exec($cmd_create_zip);
	     		
	     		unlink($local_file_path);
	     		rmdir(dirname($local_file_path));
	     		
	     	} else {
	     		//this else creates the archive without subfolders
	     		//jar cfM /tmp/sub_dir/pertinent_dir.zip -C /tmp/sub_dir pertinent_dir
	     		//$local_file_path  = str_replace("'", '', $local_file_path); 
	     		$cmd_create_zip = "zip -9 -j -P " . $filepass . " ". $zip_file_name . " '" . $local_file_path . "';" ;
	     		@exec($cmd_create_zip);
// 	     		die($cmd_create_zip . __FUNCTION__ . __CLASS__);
// 	     		die($cmd_create_zip );
	     	}
		   
		   
		    $file_name_db = $local_file_parent_dir . "/" .$local_file_basename ; // name of path/file found inside the zip
     	}

     	
     	$local_file_path_db =  substr($zip_file_name, strlen(FTP_QUEUE_PATH)+1, strlen($zip_file_name)-1);
     	
	    $file_name_db = Pms_CommonData::aesEncrypt($file_name_db);     
	    $FtpPutQueue = new FtpPutQueue();
	    $FtpPutQueue
		->set('clientid', $clientid)
		->set('local_file_path', $local_file_path_db)
		->set('file_name', $file_name_db)
		->set('legacy_path', $legacy_path)
		->set('ftp_upload_performed', 'NO')
		->set('controllername', $lower_controllername)
		->set('actionname', $lower_actionname)
		->set('foster_file', ($foster_file===false ? "NO" : "YES"));
		
	    if (defined('APPLICATION_ENV') && APPLICATION_ENV == 'development') {		
			//$FtpPutQueue->set('file_name_decrypt', Pms_CommonData::aesDecrypt($file_name_db));
		}

		$FtpPutQueue->save();
	    	
// 	    self::ftp_put_cron_upload();
	    
	    if ($FtpPutQueue) {
	    	return $FtpPutQueue->id;
	    } else {
	    	return FALSE;
	    }
     }
    
     
     /**
      * TODO: create Pms_FtpQueue and refactor 
      * 
      * pcntl_fork() cannot be used
      * 
      * @param string $limit
      * @throws Zend_Exception
      * @return boolean
      */
     public static function ftp_put_cron_upload( $limit = NULL) 
     {
         $error_messages = array();
         
     	$q = Doctrine_Query::create()
     	->select('*')
     	->from('FtpPutQueue')
     	->where('ftp_upload_performed = ?' , 'NO')
     	->andWhere('isdeleted = 0')
     	->andWhere('ftp_upload_try < 10') //there should be a query to search for this > 5 and alert-email admin something is fishy
     	->orderBy('id ASC');
     	
     	if ( ! is_null($limit) && (int)$limit > 0) {
     	    
     		$q->limit((int)$limit);
     		
     	}
     	
     	$query = $q->fetchArray();
     	//$cntr = $query->execute();
     	//$query = $cntr->toArray();
     	
     	if ( ! is_array($query) || count($query) == 0) {
     		//nothing to upload 
     		return false;
     	}

     	//connect to ftp
     	if (defined("FTP_UPLOAD") && FTP_UPLOAD == "localhost") {
     		$ftp_put = new Pms_FtpFileuploadFakeLocalhost();
     	} else {
     		$ftp_put = new Pms_FtpFileuploadv2();
     	}
     	
     	//connect
     	if ($ftp_put->ftpconnect() === TRUE) {

     		foreach ($query as $row) {
     			
     			$row_id = $row['id'];
     			$clientid = $row['clientid'] ;
     			$controllername = $row['controllername'] ;
     			$locpath_file = FTP_QUEUE_PATH . "/". $row['local_file_path'];
     			$remote_ftp_path = $row['legacy_path'] . "/". basename($row['local_file_path']);
     			
     			$upload_rez = $ftp_put -> fileupload($locpath_file, $remote_ftp_path , false, $row['clientid'] , ($row['foster_file']=="NO" ? false : true));

     			if ( $upload_rez == true ) {
     				$last_fileupload = $ftp_put ->get_last_fileupload();
//      				die(print_r($last_fileupload));
     				if (is_array($last_fileupload) && $last_fileupload['duplicate_name'] == true) {
     					//file uploaded under a different name, remember this cause we needit on download
     					//if is NOT foster file... then we care
     					if ($row['foster_file'] == 'NO') {
	     					/*
	     					 //update controller= owner of the file
	     					 $update = Doctrine_Query::create()
	     					 >update($controller_association[$controllername])
	     					 ->set('ftp_path', '?' , $remote_file)
	     					 ->where('ftp_put_queue_id = ?' , $row_id )
	     					 ->execute();
	     					 */
	     					//print_r($last_fileupload);
	     					//die("duplicate file");
	     				} else {
	     					
	     				}
     				}
	     		
	     			//save into dbf the new ftp_path
	     			$update = Doctrine_Query::create()
					->update("FtpPutQueue")
					->set('ftp_path', '?' , $last_fileupload['new_ftpath'])
					->set('ftp_upload_performed', '?','YES')
					->set('ftp_upload_try', '?', $row['ftp_upload_try']+1)
					->set('change_user', '-1')
					->set('change_date', '?' , date("Y-m-d H:i:s", time()) )
					->where('id = ?' , $row_id )
					->execute();	
	     			
	     			//remove local zip file
	     			unlink($locpath_file);
	     			//remove local folder that holded our zip file;
	     			rmdir(dirname($locpath_file));
	     			
	     			
	     		} else {
	     			//failed to upload file
	     			//$ftp_put -> log_error("{$row_id} - cannot upload file or file no longer exists on local");
	     			$error_messages[] = "{$row_id} - cannot upload file or file no longer exists on local" . PHP_EOL . print_r($row, true);
					//increment try number	
	     			$update = Doctrine_Query::create()
	     			->update("FtpPutQueue")
	     			->set('ftp_upload_try', '?', $row['ftp_upload_try']+1)
	     			->where('id = ?' , $row_id )
	     			->execute();
	     			
	     			continue;
	     		}
     		} //end foreach
     		
     		$ftp_put -> ftpconclose();
     		
     	}
     	
     	if ( ! empty($error_messages)) {
     	    
     	    $message =  implode(PHP_EOL, $error_messages);
     	    
     	    try {
     	        
     	        $logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
     	        $logger->log($message , 0);
     	        
     	    } catch (Zend_Controller_Action_Exception $e) {
     	        //die($e->getMessage());
     	        throw new Zend_Exception($message, Zend_Log::EMERG);
     	    }     	    
     	}
     	
     	return true;

     }
		
     
     /**
      * Grouping consecutive dates in an array together in PHP
      * https://stackoverflow.com/questions/12967267/grouping-consecutive-dates-in-an-array-together-in-php
      * @param array $selectedDays
      * 
$selectedDays = array( 2012-10-18,
 2012-10-19,
 2012-10-20,
 2012-10-23,
 2012-10-24,
 2012-10-29,
 2012-10-30)
 
will output
array(
'0'=>array('start' => '2012-10-18','end' => '2012-10-20'))
.
.
.
.etc
)
      */
     public static function days_to_intervals( $selectedDays = array() ) 
     {
     	//Grouping consecutive dates in an array together in PHP
     	//makeit work with scrambled arrays
     	
     	$reset_timezone = date_default_timezone_get(); 
     	date_default_timezone_set('UTC');
     	     	    	
     	$arr = $selectedDays;
     	$arr = array_map("strtotime", $arr);
     	sort($arr);
     	$arr = array_map(
     			function ($n){ return date("Y-m-d", $n);},
     			$arr
     	);
     	$selectedDays = $arr;
     	
     	$intervals = array();
     	$i=0;
     	$j=1;
     	$diff = strtotime('1 day', 0);
     	$period = $diff;
     	$nrInterval=0;
     	$intervals[$nrInterval]['start'] = $selectedDays[$i];
     	$intervals[$nrInterval]['end'] = $selectedDays[$i];
     	while($j<count($selectedDays)){
     		if(strtotime($selectedDays[$j])-strtotime($selectedDays[$i]) == $period){
     			$intervals[$nrInterval]['end'] = $selectedDays[$j];
     			$j++;
     			$period+=$diff;
     		}
     		else{
     			$i=$j;
     			$j++;
     			$nrInterval++;
     			$intervals[$nrInterval]['start'] = $selectedDays[$i];
     			$intervals[$nrInterval]['end'] = $selectedDays[$i];
     			$period = $diff;
     		}
     	}
     	
     	date_default_timezone_set($reset_timezone);
     	
     	return $intervals;
     }
     
     
     public static function send_errormail( $exception )
     {
     	$exception = func_get_args();
     	
     	$mailmessage .= "Page name :" . $_SERVER['REQUEST_URI'] . "<br />".PHP_EOL ;
     	$mailmessage .= "<div>" . implode("<br />\n", $exception) . " </div>".PHP_EOL ;
     	$mailmessage .= "<div> Date: " . date("d.m.Y H:m:i", time()) . "</div><br />".PHP_EOL ;

     	foreach ($_SERVER as $var=>$value) {
     		$mailmessage .= '<div> $_SERVER['.$var.'] : '.$value.' </div><br />'.PHP_EOL;
     	}
     		
     	foreach ($_SESSION as $var=>$value) {
     		$mailmessage .= '<div> $_SESSION['.$var.'] : '.$value.' </div><br />'.PHP_EOL;
     	}
     		
     	foreach ($_SESSION['Login_Info'] as $var=>$value) {
     		$mailmessage .= '<div> $_SESSION[Login_Info]['.$var.'] : '.$value.' </div><br />'.PHP_EOL;
     	}
     	
     	$mail = new Zend_Mail();
     	$mail->setBodyHtml($mailmessage)
     	->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
     	->addTo(ISPC_ERRORMAILTO, ISPC_ERRORSENDERNAME)
     	->setSubject('ISPC Error - ' .  $_SERVER['SERVER_NAME'] . '/' . $_SERVER['REQUEST_URI'] . ' (' . date("d.m.Y H:m:i") . ')')
     	->send();
     }
     
     
     public function array_sort($array, $on = NULL, $order = SORT_ASC)
     {
     	$new_array = array();
     	$sortable_array = array();
     
     	if(count($array) > 0)
     	{
     		foreach($array as $k => $v)
     		{
     			if(is_array($v))
     			{
     				foreach($v as $k2 => $v2)
     				{
     					if($k2 == $on)
     					{
     						if($on == 'birthd' || $on == 'admissiondate' || $on == 'admission_date_full' || $on == 'discharge_date_full' || $on == 'dob_full' || $on == 'admission_date' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date' || $on == "start_date")
     						{
     
     							if($on == 'birthdyears')
     							{
     								$v2 = substr($v2, 0, 10);
     							}
     							$sortable_array[$k] = strtotime($v2);
     						}
     						elseif($on == 'epid')
     						{
     							$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
     						}
     						elseif($on == 'percentage')
     						{
     							$sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
     						}
     						else
     						{
     							$sortable_array[$k] = ucfirst($v2);
     						}
     					}
     				}
     			}
     			else
     			{
     				if($on == 'birthd' || $on == 'admission_date' || $on == 'admissiondate' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date' || $on = "start_date")
     				{
     					if($on == 'birthdyears')
     					{
     						$v = substr($v, 0, 10);
     					}
     					$sortable_array[$k] = strtotime($v);
     				}
     				elseif($on == 'epid' || $on == 'percentage')
     				{
     					$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
     				}
     				elseif($on == 'percentage')
     				{
     					$sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
     				}
     				else
     				{
     					$sortable_array[$k] = ucfirst($v);
     				}
     			}
     		}
     
     		switch($order)
     		{
     			case SORT_ASC:
     				//						asort($sortable_array);
     				$sortable_array = Pms_CommonData::a_sort($sortable_array);
     				break;
     			case SORT_DESC:
     				//						arsort($sortable_array);
     				$sortable_array = Pms_CommonData::ar_sort($sortable_array);
     				break;
     		}
     		foreach($sortable_array as $k => $v)
     		{
     			$new_array[$k] = $array[$k];
     		}
     	}
     
     	return $new_array;
     }
     
     /**
      * @claudiu on 02.2018
      * 
      * this fn is to be used just for the selectbox !
      * it return only the active and not deleted !
      * 
      * Jul 10, 2017 @claudiu 
      * get the prefix used in the selectbox that has groups+users+pseudogroups
      * return array_keys must be unique
      * 
      * @return multitype:string
      */
     public static function get_users_selectbox_separator_string()
     {
     	$selectbox_separator_string = array(
     			//this 4 are used for the multi-select boxes
     			'all' => "a",
     			'user' => "u",
     			'group' => "g",
     			'pseudogroup' => "pseudogroup_",
     			
     			'glue_on_view' => "; ", //this is used for implode( glue, array) when you use for the viewer
     			
     	);		
     	return $selectbox_separator_string;
     }
     
    /**
     * @claudiu on 02.2018
     * 
     * this fn is to be used just for the selectbox !
     * it return only the active and not deleted !
     * 
     * @param number $clientid - optional
     * @param boolean $select_all_option - @since 18.02.2019, optional, add on top the option for all
     * @return multitype:Ambigous <string, Zend_View_Helper_Translate> multitype:string  multitype:NULL  multitype:Ambigous <string, NULL, string, Ambigous <string, Zend_View_Helper_Translate>>
     */ 
	public static function get_nice_name_multiselect ( $clientid = 0 , $select_all_option = false)
	{
				
	    if (empty($clientid)) {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	    }
	    
	    $Tr = new Zend_View_Helper_Translate();
	    
		$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
			
		$todousersarr = array(
				"0" => $Tr->translate('select'),
		);
		
		if ($select_all_option) {
		    $todousersarr[$selectbox_separator_string['all']]  = $this->view->translate('all');
		}
		
			
		$usergroup = new Usergroup();
		$todogroups = $usergroup->getClientGroups($clientid);
		$grouparraytodo = array();
		foreach ($todogroups as $group)
		{
			$grouparraytodo[$selectbox_separator_string['group'] .  $group['id']] = trim($group['groupname']);
		}
	
		$users = new User();
		$userarray = $users->getUserByClientid($clientid);
	
		User::beautifyName($userarray);
			
		$userarraytodo = array();
		foreach ($userarray as $user)
		{
			$userarraytodo[$selectbox_separator_string['user'] . $user['id']] = $user['nice_name'];
		}
	
		asort($userarraytodo);
		asort($grouparraytodo);
			
		if ( ! empty($grouparraytodo))
    		$todousersarr[$Tr->translate('group_name')] = $grouparraytodo;
		
		if ( ! empty($userarraytodo))
		  $todousersarr[$Tr->translate('users')] = $userarraytodo;
	
		$user_pseudo =  new UserPseudoGroup();
		$user_ps =  $user_pseudo->get_pseudogroups_for_todo($clientid);
		$pseudogrouparraytodo = array();
		if ( ! empty ($user_ps)) {
			
			//pseudogroup must have users in order to display 
			$user_ps_ids =  array_column($user_ps, 'id');
			$user_pseudo_users = new PseudoGroupUsers();
			$users_in_pseudogroups = $user_pseudo_users->get_users_by_groups($user_ps_ids);				
			
			foreach($user_ps as $row) {
				if ( ! empty($users_in_pseudogroups[$row['id']])){
					
				//Hack for JS (TODO - 1145) -> you should not fix a js problem by changing the php like that. just a specific case fix
				$pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = str_replace('"', ' ', str_replace("'", " ", $row['servicesname'])); // Hack for JS (TODO - 1145)
				}
			}
				
			$todousersarr[$Tr->translate('liste_user_pseudo_group')] = $pseudogrouparraytodo;
		}
	
		return $todousersarr;
	}
     
     
     /**
      * 
      * Jul 21, 2017 @claudiu
      * check if a string contains html tags
      * it was created so we can Zend_Mail->setBodyHtml()
      * first used in Application_Form_VwEmailsLog::save2email_log
      * 
      * @param string $string
      * @return boolean
      */
     public static function assertIsHtml( $string = null) 
     {
     	$html_tags_regex = '/<(br|basefont|hr|input|source|frame|param|area|meta|!--|col|link|option|base|img|wbr|!DOCTYPE).*?>|<(a|abbr|acronym|address|applet|article|aside|audio|b|bdi|bdo|big|blockquote|body|button|canvas|caption|center|cite|code|colgroup|command|datalist|dd|del|details|dfn|dialog|dir|div|dl|dt|em|embed|fieldset|figcaption|figure|font|footer|form|frameset|head|header|hgroup|h1|h2|h3|h4|h5|h6|html|i|iframe|ins|kbd|keygen|label|legend|li|map|mark|menu|meter|nav|noframes|noscript|object|ol|optgroup|output|p|pre|progress|q|rp|rt|ruby|s|samp|script|section|select|small|span|strike|strong|style|sub|summary|sup|table|tbody|td|textarea|tfoot|th|thead|time|title|tr|track|tt|u|ul|var|video).*?<\/\2>/i';
//      	return boolval(preg_match($html_tags_regex, $string));
     	return intval(preg_match($html_tags_regex, $string));
     }
     
     
     /**
      * this fn will sort an array based on you keys-array
      * Sort an Array by keys based on another Array
      * https://stackoverflow.com/questions/348410/sort-an-array-by-keys-based-on-another-array
      * Jul 28, 2017 @claudiu
      *
      * @param array $inputArray
      * @param array $keyList
      * @return multitype:
      */

     public static function sortArrayByArray(array $toSort, array $sortByValuesAsKeys)
     {
     	$commonKeysInOrder = array_intersect_key(array_flip($sortByValuesAsKeys), $toSort);
     	$commonKeysWithValue = array_intersect_key($toSort, $commonKeysInOrder);
     	$sorted = array_merge($commonKeysInOrder, $commonKeysWithValue);
     	return $sorted;
     }
     
     /**
      * sortArrayByArray plus filter non existing keys
      * Sort an Array by keys based on another Array
      * https://stackoverflow.com/questions/348410/sort-an-array-by-keys-based-on-another-array
      * Jul 28, 2017 @claudiu 
      * 
      * @param unknown $inputArray
      * @param unknown $keyList
      * @param string $removeUnknownKeys
      * @return multitype:
      */
     public static function sortAndFilterArrayByArray(array $inputArray, array $keyList, $removeUnknownKeys=true){
     	$keysAsKeys = array_flip($keyList);
     	$result = array_replace($keysAsKeys, $inputArray); // result = sorted keys + values from input +
     	$result = array_intersect_key($result, $inputArray); // remove keys are not existing in inputArray
     	if( $removeUnknownKeys ){
     		$result = array_intersect_key($result, $keysAsKeys); // remove keys are not existing in keyList
     	}
     	return $result;
     }
     
     
     /**
      * this fn is based on locale
      * Jul 31, 2017 @claudiu 
      * 
      * @return multitype:string
      */
     public static function getDaysOfWeek()
     {
     	$days_of_week_arr =  array();
     	$day_length = 86400;
		$time_monday = strtotime('monday this week');
		for( $i = 0; $i <= 6; $i++) {
			$curr_day = $time_monday + $day_length * $i;
			$days_of_week_arr[$i+1] = utf8_encode(strftime('%A', $curr_day));
		}
		return $days_of_week_arr;
     }
     
     public static function getRegisterTextareas()
     {
     	$typesarray = array(
     			'aufwand_mit' => 'besonderer Aufwand mit: ',//BL_Aufwand
//     			'problem_besonders' => 'BL_problem_1',//BL_problem_1
//     			'problem_ausreichend' => 'BL_problem_2',//BL_problem_2
     			'bedarf' => 'Behandlungs- und Begleitungsbedarf', // NO INFo
     			'massnahmen' => 'Maßnahmen', // NO INFo ?? 
     	);
     	return $typesarray;
     }
     

     public static function getFormsTextareas($form_name = false)
     {
     	$typesarray ['patientformnew/sisambulant'] = array (
				"movement"=>"movement_tr",
				"cognitive"=>"cognitive_tr",
				"mobility"=>"mobility_tr",
				"diseaserelated" =>"diseaserelated_tr",
				"selfcatering"=>"selfcatering_tr",
				"socialrelations"=>"socialrelations_tr",
				"financialmanagement"=>"financialmanagement_tr" 
		);
     	
     	$typesarray ['patientnew/hospizregisterv3'] = array (
     			'aufwand_mit' => 'aufwand_mit_tr',
      			'bedarf' => 'bedarf_tr', 
      			'massnahmen' => 'massnahmen_tr',
		);
     	
     	$typesarray ['patientformnew/sisstationary'] = array (
     			"movement"=>"movement_tr",
     			"cognitive"=>"cognitive_tr",
     			"mobility"=>"mobility_tr",
     			"diseaserelated" =>"diseaserelated_tr",
     			"selfcatering"=>"selfcatering_tr",
     			"socialrelations"=>"socialrelations_tr",
     			"financialmanagement"=>"financialmanagement_tr"
     	);
     	
     	$typesarray ['mambo/assessment'] = array (
     	    "feedback"=>"feedback_tr",
     	);
     	
     	$typesarray ['anyform'] = array (
     	    "todo"=>"todo_tr",
     	);
     	
     	// ISPC-2507 Lore 31.01.2020   	 // Maria:: Migration ISPC to CISPC 08.08.2020
     	$typesarray ['patientmedication/requestchanges'] = array (
     	    'pharmacymedicheck' => 'pharmacymedicheck_tr',
     	);
     	//.
     	
     	if($form_name && !empty($typesarray[$form_name])){
     		return $typesarray[$form_name];
     	} else {
     		return $typesarray;
     	}
     }
     
     public static function isPast($time) {
         return (strtotime($time) < time());
     }
     
     public static function isFuture($time) {
         return (strtotime($time) > time());
     }
     
     public static function isToday($time) {
         return (date("Y.m.d", strtotime($time)) == date("Y.m.d"));
     }

     public static function get_default_bw_price_location_types()
     {
     	$loc_types = array(
     			'5' => 'bw_loca_type_home',
     			'3' => 'bw_loca_type_pfle_altheim',
     			'2' => 'bw_loca_type_hospiz',
	     	    //ISPC-2549 Carmen 17.02.2020
     			'10' => 'bw_loca_type_assistance_disabled_people',
     	    	//--
     			'0' => 'bw_loca_type_other'
     	);
     	return $loc_types;
     }
     
     

     /**
      * Jan 05, 2017 @claudiu
      * array_key_exists for multidimensional arr
      * first used in Application_Form_VwEmailsLog::save2email_log
      * 
      * @param array $arr
      * @param string $key
      * @return boolean
      */
     public static function assertKeyExists($arr = array(), $key = null)
     {
     
         if ( ! is_array($arr) || is_null($key) || empty($arr)){
             return false;
         }
         
         if (array_key_exists($key, $arr)) {
             return true;
         }
     
         //$arr = array_filter($arr, 'is_array');
         
         foreach ($arr as $subarr) {
             if (is_array($subarr)) {
                 if (self::assertKeyExists($subarr, $key)) {
                     return true;
                 }
             }
     
         }
     
         return false;
     }
     
     public static function mb_trim($str) {
         return preg_replace("/(^\s+)|(\s+$)/us", "", $str);
     }
     
     public function complete_patternation($searchstring, $pattern, $ss_flag){
     	$pattern_arr = explode("|", $pattern);
     	
     	$chars[ 'ae' ] = 'Ä';
     	$chars[ 'oe' ] = 'Ö';
     	$chars[ 'ue' ] = 'Ü';
     	$chars[ 'AE' ] = 'Ä';
     	$chars[ 'OE' ] = 'Ö';
     	$chars[ 'UE' ] = 'Ü';
     	if($ss_flag == 1)
     	{
     		$chars[ 'ss' ] = 'ß';
     		$chars[ 'SS' ] = 'ß';
     	}
     	$searchstring_tr = $searchstring;
     	$searchstring_tr = strtr($searchstring_tr, $chars);
     	$pattern_arr[] = $searchstring_tr;
     			
     	$chars[ 'ae' ] = 'ä';
     	$chars[ 'oe' ] = 'Ö';
     	$chars[ 'ue' ] = 'Ü';
     	$chars[ 'AE' ] = 'ä';
     	$chars[ 'OE' ] = 'Ö';
     	$chars[ 'UE' ] = 'Ü';
     	if($ss_flag == 1)
     	{
     		$chars[ 'ss' ] = 'ß';
     		$chars[ 'SS' ] = 'ß';
     	}
     	$searchstring_tr = $searchstring;
     	$searchstring_tr = strtr($searchstring_tr, $chars);
     	$pattern_arr[] = $searchstring_tr;
     			
     	$chars[ 'ae' ] = 'ä';
     	$chars[ 'oe' ] = 'ö';
     	$chars[ 'ue' ] = 'Ü';
     	$chars[ 'AE' ] = 'ä';
     	$chars[ 'OE' ] = 'ö';
     	$chars[ 'UE' ] = 'Ü';
     	if($ss_flag == 1)
     	{
     		$chars[ 'ss' ] = 'ß';
     		$chars[ 'SS' ] = 'ß';
     	}
     	$searchstring_tr = $searchstring;
     	$searchstring_tr = strtr($searchstring_tr, $chars);
     	$pattern_arr[] = $searchstring_tr;
     			
     	$chars[ 'ae' ] = 'Ä';
     	$chars[ 'oe' ] = 'ö';
     	$chars[ 'ue' ] = 'Ü';
     	$chars[ 'AE' ] = 'Ä';
     	$chars[ 'OE' ] = 'ö';
     	$chars[ 'UE' ] = 'Ü';
     	if($ss_flag == 1)
     	{
     		$chars[ 'ss' ] = 'ß';
     		$chars[ 'SS' ] = 'ß';
     	}
     	$searchstring_tr = $searchstring;
     	$searchstring_tr = strtr($searchstring_tr, $chars);
     	$pattern_arr[] = $searchstring_tr;
     			
     	$chars[ 'ae' ] = 'Ä';
     	$chars[ 'oe' ] = 'ö';
     	$chars[ 'ue' ] = 'ü';
     	$chars[ 'AE' ] = 'Ä';
     	$chars[ 'OE' ] = 'ö';
     	$chars[ 'UE' ] = 'ü';
     	if($ss_flag == 1)
     	{
     		$chars[ 'ss' ] = 'ß';
     		$chars[ 'SS' ] = 'ß';
     	}
     	$searchstring_tr = $searchstring;
     	$searchstring_tr = strtr($searchstring_tr, $chars);
     	$pattern_arr[] .= $searchstring_tr;
     			
     	$chars[ 'ae' ] = 'Ä';
     	$chars[ 'oe' ] = 'Ö';
     	$chars[ 'ue' ] = 'ü';
     	$chars[ 'AE' ] = 'Ä';
     	$chars[ 'OE' ] = 'Ö';
     	$chars[ 'UE' ] = 'ü';
     	if($ss_flag == 1)
     	{
     		$chars[ 'ss' ] = 'ß';
     		$chars[ 'SS' ] = 'ß';
     	}
     	$searchstring_tr = $searchstring;
     	$searchstring_tr = strtr($searchstring_tr, $chars);
     	$pattern_arr[] = $searchstring_tr;
     	
     	$chars[ 'ae' ] = 'ä';
     	$chars[ 'oe' ] = 'Ö';
     	$chars[ 'ue' ] = 'ü';
     	$chars[ 'AE' ] = 'ä';
     	$chars[ 'OE' ] = 'Ö';
     	$chars[ 'UE' ] = 'ü';
     	if($ss_flag == 1)
     	{
     		$chars[ 'ss' ] = 'ß';
     		$chars[ 'SS' ] = 'ß';
     	}
     	$searchstring_tr = $searchstring;
     	$searchstring_tr = strtr($searchstring_tr, $chars);
     	$pattern_arr[] = $searchstring_tr;
    	
     	$pattern_arr = array_unique($pattern_arr);
     	//var_dump($pattern_arr);
     	$pattern = implode("|", $pattern_arr);
     	//var_dump($pattern);
     	return $pattern;
     
     }
     
     
     /**
      * strpos for array(needle), using a slow foreach, NOT to be used in patient controllers
      * @param unknown $haystack
      * @param unknown $needle
      * @return mixed|boolean
      */
     public static function strpos_arr($haystack, $needle, $offset = null) 
     {
         if ( ! is_array($needle)) {
             $needle = array($needle);
         }
         if ( ! empty($haystack) && ! empty($needle)) {         
             foreach ($needle as $what) {
                 if (($pos = strpos($haystack, $what, $offset)) !== false)
                     return $pos;
             }
         }
     
         return false;
     }
     
     
     
     private static function _patient_file_folder_is_unique($folder = '') 
     {
         if (empty($folder)) {
             return false;
         }
         
         $qr = Doctrine_Core::getTable('PatientFileUpload')
         ->createQuery('pfu')
         ->select('id')
         ->where('AES_DECRYPT(file_name, :salt) LIKE (:folder)')         
         ->fetchOne(array('salt' =>Zend_Registry::get('salt'),  "folder" => $folder . '%'), Doctrine_Core::HYDRATE_ARRAY)
         ;
          
         if ( ! empty($qr) && isset($qr['id'])) {
             //folder exists
             $logger = Zend_Registry::get('logger');
             $logger->log("avoided duplicate patient_file folder for {$folder}", Zend_Log::CRIT); 
             return false;
         } else {
             //folder does not exist
             return true;
         }
     }
     
     
     /**
      * http://php.net/manual/ro/function.readdir.php
      * frasq at frasq dot org ¶
      */
     public static function listdiraux($dir, &$files) {
         $handle = opendir($dir);
         while (($file = readdir($handle)) !== false) {
             if ($file == '.' || $file == '..') {
                 continue;
             }
             $filepath = $dir == '.' ? $file : $dir . '/' . $file;
             if (is_link($filepath))
                 continue;
             if (is_file($filepath))
                 $files[] = $filepath;
             else if (is_dir($filepath))
                 self::listdiraux($filepath, $files);
         }
         closedir($handle);
     }
     
     /**
      * use to replaces form elements for PDF generating
      * 
      * @cla on 29.12.2018
      * if $isDOMPDF then will use svg images for cb and radio
      * if ! $isDOMPDF then input=text and textarea are replaced with the value, and will use jpg images
      * $width & $height both work now
      * 
      * 
      * @param string $html
      * @param string $height
      * @param string $width
      * @param boolean $isDOMPDF
      * @return string
      */
     public static function html_prepare_dompdf($html = '', $height = "14px", $width = "auto", $isDOMPDF = true) { // replaces form elements for PDF generating
     
         $images = [
             'svg' => [
                 'radio'    => [
                     'checked'      => 'radio_checked.svg',
                     'unchecked'    => 'radio_unchecked.svg',
                 ],
                 'checkbox' => [
                     'checked'      => 'checkbox_checked.svg',
                     'unchecked'    => 'checkbox_unchecked.svg',
                 ],
             ],
             'jpg' => [
                 'radio'    => [
                     'checked'      => 'radio-selected-btn.jpg',
                     'unchecked'    => 'radio-btn.jpg',
                 ],
                 'checkbox' => [
                     'checked'      => 'check02.jpg',
                     'unchecked'    => 'check01.jpg',
                 ],
             ],
         ];
         $images =  $isDOMPDF === true ? $images['svg'] : $images['jpg'];
         
         // define functions used for callbacks
         $radioFilter = function($match) use ($width, $height, $images) {
             $pattern = "/checked=[\"']?checked[\"']\1?/iU";
             if (preg_match($pattern, $match [0])) {
                 return '<img src="' . PUBLIC_PATH . '/images/' . $images['radio']['checked'] . '"  style="width:'.$width.'; height:'.$height.'; margin:2px 2px 0 0;;"  alt="" />&nbsp;';
             } else {
                 return '<img src="' . PUBLIC_PATH . '/images/' . $images['radio']['unchecked'] . '"  style="width:'.$width.'; height:'.$height.'; margin:2px 2px 0 0;;" alt="" />&nbsp;';
             }
         };
         
         $checkboxFilter = function($match) use ($width, $height, $images) {
             $pattern = "/checked=[\"']?checked[\"']\1?/iU";
             if (preg_match($pattern, $match [0])) {
                 return '<img src="' . PUBLIC_PATH . '/images/' . $images['checkbox']['checked'] . '" style="width:'.$width.'; height:'.$height.'; margin:2px 2px 0 0;;" alt="" />&nbsp;';
             } else {
                 return '<img src="' . PUBLIC_PATH . '/images/' . $images['checkbox']['unchecked'] . '" style="width:'.$width.'; height:'.$height.'; margin:2px 2px 0 0;;" alt="" />&nbsp;';
             }
         };
          
          
         $textFilter = function($match) {
             $pattern = '/value=(?:["\']?)(.*?)(?:["\'])\1?/i';
             if (preg_match($pattern, $match [0], $val)) {
                 return $val[1];                 
             } else {
                 return str_replace('<input ', '<input readonly="true" ', $match [0]);                 
             }
             
         };
         $textareaFilter = function($match) {
             $pattern = "/<textarea.*>(.*)<\\/textarea>/isU";
             if (preg_match($pattern, $match [0], $val)) {
                 return nl2br($val[1]);
             } else {
                 return str_replace('<textarea ', '<textarea readonly="true" ', $match [0]);
             }
         };
         
         
         
         // match radios and replace
         $radio_pat = "/<input.*type=[\"']?radio[\"']?.*>/iU";
         $html = preg_replace_callback ( $radio_pat, $radioFilter, $html);
         
         // match checkboxes and replace
         $checkbox_pat = "/<input.*type=[\"']?checkbox[\"']?.*>/iU";
         $html = preg_replace_callback ( $checkbox_pat, $checkboxFilter, $html);
         
         if ($isDOMPDF !== true) {
             // inputs text make readonly or replace with value
             $text_pattern = "/<input.*type=[\"']?text[\"']?.*>/iU";
             $html = preg_replace_callback ( $text_pattern, $textFilter, $html);
              
             // textarea make readonly or replace with value
             $textarea_pattern = "/<textarea.*>.*<\\/textarea>/isU";
             $html = preg_replace_callback ( $textarea_pattern, $textareaFilter, $html);
         }
         
         // new lines
         //$html = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $html);
         
         return $html;
     }
     
     
     /**
      * 
      * @param string $html
      * @return mixed
      */
     public static function html_prepare_fpdf($html = '') 
     { 
         /*
          * @cla on 11.03.2019
          * fpdf does NOT apply css class=display_none (.display_none = display:none ) to a <TR/> .. 
          * so i remove such tr's
          */
         $text_pattern = "/<tr.*?class=[\"'](.*)?display_none(.*)?[\"'](.*)?>([\s\S]*)?<\\/tr>/iU";
         $html = preg_replace ( $text_pattern, '', $html);
         
         return $html;
     }
     
     
     /**
      * @cla on 13.07.2018 
      * taken from  'steven at nevvix dot com'  
      * http://php.net/manual/ro/function.vsprintf.php
      * 
      * $format = <<<SQL
      * CREATE DATABASE IF NOT EXISTS {database};
      * GRANT ALL PRIVILEGES ON {database_name}.* TO '{user}'@'{host}';
      * SET PASSWORD = PASSWORD('{pass}');
      * SQL;
      * 
      * $args = ["database"=>"people", "user"=>"staff", "pass"=>"pass123", "host"=>"localhost"];
      * 
      * echo vsprintf_named($format, $args);
      * echo vsprintf_named($format, $args, "/:(\w+)/");
      * 
      * 
      * Return a formatted string like vsprintf() with named placeholders.
      *
      * When a placeholder doesn't have a matching key in `$args`,
      *   the placeholder is returned as is to see missing args.
      * @param string $format
      * @param array $args
      * @param string $pattern
      * @return string
      */
     public static function vsprintf_named($format, array $args, $pattern="/\{(\w+)\}/") {
         return preg_replace_callback($pattern, function ($matches) use ($args) {
             return @$args[$matches[1]] ?: $matches[0];
         }, $format);
     }
     
     
     /**
      * @cla on 07.2018
      * http://php.net/manual/en/function.json-last-error.php
      * @deprecated
      * 
      * use Zend_Json_Encoder::encode()
      * or 
      * Zend_Json::$useBuiltinEncoderDecoder = true; Zend_Json::encode();
      */
     public static function safe_json_encode($value, $options = 0, $depth = 512) {
         $encoded = json_encode($value, $options, $depth);
         if ($encoded === false && $value && json_last_error() == JSON_ERROR_UTF8) {
             $encoded = json_encode(self::utf8ize($value), $options, $depth);
         }
         return $encoded;
     }
     public static function utf8ize($mixed) {
         if (is_array($mixed)) {
             foreach ($mixed as $key => $value) {
                 $mixed[$key] = self::utf8ize($value);
             }
         } elseif (is_string($mixed)) {
             return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
         }
         return $mixed;
     }
     
     
     /**
      * @author claudiu on 28.08.2018
      * created for layout top-right counter of TODOS
      * count countTodos Of loghed in User from this Ipids
      * ipids= $filter_allowed_ipids, are taken via new PatientUsers()->getUserPatients($userid);
      * logic was taken from TodosController()->managementAction(), EXCEPT that we only use only one user_id = $logininfo->userid
      * 
      * read IconsPatient::get_patient_todos - for bug examples 28.02.2018
      * 
      * @param bool|array $ipids
      * @return number
      */
     public static function countTodosOfUserFromIpids($ipids = array())
     {
         $logininfo = new Zend_Session_Namespace('Login_Info');
         	
         if (empty($logininfo->userid)) {
             return 0;//fail-safe
         }
         
         
         
         // Get todos
         $todo_q = Doctrine_Query::create()
         ->select("count(*) as count")
//          ->addSelect("CONCAT_WS('_', ipid, todo, until_date, additional_info ) as flawed_groupBy")
         ->from('ToDos')
         ->where('client_id = ?', $logininfo->clientid)
         ->andWhere('isdelete = "0"')
         ->andWhere('iscompleted = "0"');
         
         if ($ipids !== false) { 
            $ipids =  is_array($ipids) ? array_values($ipids) : [$ipids];
            $todo_q->andWhereIn('ipid', $ipids);
         }
         
         if($logininfo->usertype != 'SA')
         {
             //get client coord groups
             $usergroup = new Usergroup();
             $usersgroups = $usergroup->getUserGroups([6]);
             $coord_groups = ! empty($usersgroups) ? array_column($usersgroups, 'id') : null;

             if ( ! empty($coord_groups) && ! in_array($logininfo->groupid, $coord_groups)) {
                 $todo_q->andWhere('triggered_by != "system"');
             }
     
             if ($logininfo->groupid > 0) {
                 $sql_group = " OR group_id = {$logininfo->groupid} ";
             }
             
             $todo_q->andWhere("user_id = {$logininfo->userid}  {$sql_group} ");
         }
//          $todo_q->groupBy('flawed_groupBy');
           
         $todos_array = $todo_q->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
         
         
         return (int) $todos_array['count'];
     }
     
     
     /**
      * google php array_column_recursive
      * 
      * @param string $input
      * @param string $columnKey
      * @param string $indexKey
      * @return NULL|boolean|Ambigous <multitype:, multitype:unknown Ambigous <NULL, unknown> >
      */
     public static function array_column_recursive(array $haystack, $needle) {
         $found = [];
         array_walk_recursive($haystack, function($value, $key) use (&$found) {
             if ($key === func_get_arg(2))
                 $found[] = $value;
         }, $needle);
         return $found;
     }
     
     
     

     /**
      * @cla - this is ONLY for php, not for mysql
      *
      * http://php.net/manual/ro/function.com-create-guid.php
      * Returns a GUIDv4 string
      *
      * Uses the best cryptographically secure method
      * for all supported pltforms with fallback to an older,
      * less secure version.
      *
      * @param bool $trim
      * @return string
      */
     public static function GUIDv4 ($trim = true)
     {
         // Windows
         if (function_exists('com_create_guid') === true) {
             if ($trim === true)
                     return trim(com_create_guid(), '{}');
                 else
                         return com_create_guid();
                 }
 
                 // OSX/Linux
                 if (function_exists('openssl_random_pseudo_bytes') === true) {
                     $data = openssl_random_pseudo_bytes(16);
                     $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
                     $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
                     return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
                 }
 
                 // Fallback (PHP 4.2+)
                 mt_srand((double)microtime() * 10000);
                 $charid = strtolower(md5(uniqid(rand(), true)));
                 $hyphen = chr(45);                  // "-"
                 $lbrace = $trim ? "" : chr(123);    // "{"
                 $rbrace = $trim ? "" : chr(125);    // "}"
                 $guidv4 = $lbrace.
                           substr($charid,  0,  8).$hyphen.
                           substr($charid,  8,  4).$hyphen.
                           substr($charid, 12,  4).$hyphen.
                           substr($charid, 16,  4).$hyphen.
                   substr($charid, 20, 12).
                   $rbrace;
         return $guidv4;
     }
     
     
     public static function array_flatten_v2(array $array) 
     {
         $return = array();
         array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
         return $return;
     }
     
     
     


     public static function temporary_files_delete($folder, $age = '86400')
     {
         if($handle = opendir($folder))
         {
             while(false !== ($entry = readdir($handle)))
             {
                 $filename = $folder . '/' . $entry;
                 $mtime = @filemtime($filename);
                 if(is_file($filename) && $mtime && (time() - $mtime > $age))
                 {
                     @unlink($filename);
                 }
             }
             closedir($handle);
         }
     }
     public static function temporary_image_create($data, $type = 'svg', $stype = 'human')
     {
         $tmp_file = uniqid('img' . rand(1000, 9999));
         $tmp_file_path = APPLICATION_PATH . '/../public/temp/' . $tmp_file . '.png';
         $tmp_folder = APPLICATION_PATH . '/../public/temp';
         self::temporary_files_delete($tmp_folder, '7200'); //delete all files older than 2 hours
     
         switch($type)
         {
             case 'svg':
                 if(get_magic_quotes_gpc())
                 {
                     $data = stripslashes($data);
                 }
     
                 $data = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . $data;
     
                 $handle = fopen($tmp_file_path, 'w+');
                 fclose($handle);
     
                 $im = new Imagick();
                 $im->readImageBlob($data);
                 $im->setImageFormat("jpeg");
                 $im->writeImage($tmp_file_path);
                 $im->clear();
                 $im->destroy();
     
                 break;
     
             case 'base64':
                 $data = substr($data, stripos($data, '64,') + 3);
                 $data = base64_decode($data);
     
                 //transparent answer image
                 $im = @imagecreatefromstring($data);
                 $rgb = imagecolorat($im, 1, 1);
                 $colors = imagecolorsforindex($im, $rgb);
     
                 if($colors['alpha'] > 0 && $colors['red'] == 0)
                 {
                     //stupid hack CHANGE THIS!!!!!
                     imagecolortransparent($im, imagecolorallocatealpha($im, 0, 0, 0, 127));
                 }
                 elseif($colors['red'] == 255)
                 {
                     imagecolortransparent($im, imagecolorallocatealpha($im, 255, 255, 255, 127));
                 }
     
                 //human body background
                 if($stype == 'human-big')
                 {
                     $bg = imagecreatefromjpeg(APPLICATION_PATH . '/../public/images/human_big.jpg');
                 }
                 else if($stype == 'human-huge')
                 {
                     $bg = imagecreatefrompng(APPLICATION_PATH . '/../public/images/painlocation.png');
                 }
                 else
                 {
                     $bg = imagecreatefromjpeg(APPLICATION_PATH . '/../public/images/human_small.jpg');
                 }
     
                 if($stype == 'human-big')
                 {
                     imagecopymerge($bg, $im, 0, 0, 0, 0, 850, 600, 100);
                 }
                 else
                 {
                     imagecopymerge($bg, $im, 0, 0, 0, 0, 550, 388, 100);
                 }
     
                 imagepng($bg, $tmp_file_path);
                 imagedestroy($bg);
     
                 break;
     
             default:
                 break;
         }
     
         if(is_readable($tmp_file_path))
         {
             return $tmp_file_path;
         }
         else
         {
             return false;
         }
     }
     
     
     

     public static function getMedicationSettings ($ipid = '', $clientid = 0)
     {
     
         if (empty($ipid)) {
             return []; //failsafe without throw
         }
         
         if (empty($clientid)) {
             //clientid could be fetched from ipid
             $_login_info =  new Zend_Session_Namespace('Login_Info');
             $clientid = $_login_info->clientid;
         }
     
         $modules = new Modules();
         $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
         if ($individual_medication_time_m) {
             $individual_medication_time = 1;
         } else {
             $individual_medication_time = 0;
         }
          
          
     
         if ($individual_medication_time == 1) {
             //get time scchedule options
             $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
             $time_blocks = array('all');
             foreach($client_med_options as $mtype=>$mtime_opt){
                 if($mtime_opt['time_schedule'] == "1"){
                     $time_blocks[]  = $mtype;
                     $timed_scheduled_medications[]  = $mtype;
                 }
             }
         }
         else
         {
             $timed_scheduled_medications = array("actual","isivmed"); // default
             $time_blocks = array("actual","isivmed"); // default
         }
     
         $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid, $clientid, $time_blocks);
          
         if($patient_time_scheme['patient']){
             foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
             {
                 if($med_type != "new"){
                     $set = 0;
                     foreach($dos_data  as $int_id=>$int_data)
                     {
                         if(in_array($med_type,$patient_time_scheme['patient']['new'])){
                              
                             $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
                             $interval_array['interval'][$med_type][$int_id]['custom'] = '1';
                              
                             $dosage_settings[$med_type][$set] = $int_data;
                             $set++;
                              
                             $dosage_intervals[$med_type][$int_data] = $int_data;
                         }
                         else
                         {
                              
                              
                             $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
                             $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
                             $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;
                              
                             $dosage_settings[$med_type][$set] = $int_data;
                             $set++;
                              
                             $dosage_intervals[$med_type][$int_data] = $int_data;
                         }
                     }
                 }
             }
         }
         else
         {
             foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
             {
                  
                 $inf=1;
                 $setc= 0;
                 foreach($mtimes as $int_id=>$int_data){
                      
                     $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
                     $interval_array['interval'][$med_type][$inf]['custom'] = '1';
                     $dosage_settings[$med_type][$setc] = $int_data;
                     $setc++;
                     $inf++;
                      
                     $dosage_intervals[$med_type][$int_data] = $int_data;
                 }
             }
         }
     
     
     
     
     
         //UNIT
         $medication_unit = MedicationUnit::client_medication_unit($clientid);
         $client_medication_extra = array();
         foreach($medication_unit as $k=>$unit){
             $client_medication_extra['unit'][$unit['id']] = $unit['unit'];
         }
          
         //DOSAGE FORM
         $medication_dosage_forms = MedicationDosageform::client_medication_dosage_form($clientid);
          
         foreach($medication_dosage_forms as $k=>$df){
             $client_medication_extra['dosage_form'][$df['id']] = $df['dosage_form'];
         }
     
         //TYPE
         $medication_types = MedicationType::client_medication_types($clientid);
         foreach($medication_types as $k=>$type){
             $client_medication_extra['type'][$type['id']] = $type['type'];
         }
          
         //INDICATIONS
         $medication_indications = MedicationIndications::client_medication_indications($clientid);
          
         foreach($medication_indications as $k=>$indication){
             $client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
             $client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
         }
     
         // Packaging - ISPC-2176
         $packaging_array = PatientDrugPlanExtra::intubated_packaging();
     
     
         return array(
             'dosage_intervals'             => $dosage_intervals,
             'interval_array'               => $interval_array,
             'timed_scheduled_medications'  => $timed_scheduled_medications,
             'client_medication_extra'      => $client_medication_extra,
             'packaging_array'              => $packaging_array,
         );
     
     }
     
     public static function driving_time_limit(){     	
     		return "360"; //ISPC-2470 Carmen 24.10.2019// Maria:: Migration ISPC to CISPC 08.08.2020
     		//return "180";
     }
     
        public static function str_safeascii($string, $extra = '\.', $replace = "_")
        {
            $string = Pms_CommonData::convert_umlauts($string);
            // Remove all characters that are not the separator, a-z, 0-9, or whitespace
            $string = preg_replace('![^' . preg_quote($replace) . $extra . 'A-Za-z0-9\s]+!', '_', $string);
            // Replace all separator characters and whitespace by a single separator
            $string = preg_replace('![' . preg_quote($replace) . '\s]+!u', '_', $string);

            return $string;
        }


        /**
        * return mimetype of extension, default pdf
        */
        public static function extensionToMime ($extension){
            $extension=str_replace('.','',$extension);
            $extension=strtoupper($extension);

            $mimes=array(
                'PDF'=>"application/pdf",
                'DOCX'=>"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                'DOTX'=>"application/vnd.openxmlformats-officedocument.wordprocessingml.template",
                'XLSX'=>"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                'XLS'=>"application/ms-excel",
                'DOC'=>"application/msword",
                'JPG'=>"image/jpeg",
                'PNG'=>"image/png",
                'BMP'=>"image/bmp",
                'PNG'=>"image/png",
                'RTF'=>"application/rtf",
                'TXT'=>"text/plain"
            );
            $return=$mimes[$extension];
            if(!$return){
                $return=$mimes['PDF'];
            }
            return $return;
        }

        public static function calculate_current_age($date){
            $arr = explode('.',$date);
            $day=$arr[0];
            $month=$arr[1];
            $year=$arr[2];
            $age = (date('Y') - $year) - intval((date('j') < $day) AND (date('n' ) == $month) ) - intval(date('n' ) < $month);
            return $age;
        }

     public static function isValidXml($content)
     { // Maria:: Migration ISPC to CISPC 08.08.2020
         $content = trim($content);
         if (empty($content)) {
             return false;
         }
         //html go to hell!
         if (stripos($content, '<!DOCTYPE html>') !== false) {
             return false;
         }
         
         libxml_use_internal_errors(true);
         simplexml_load_string($content);
         $errors = libxml_get_errors();
         libxml_clear_errors();
         
         return empty($errors);
     }
     
     /**
      * @author Ancuta
      * 12.02.2020 
      * ISPC-2432
      * @param string $ident_type
      * @return string
      * 12.03.2020 - added a new "section"  for todos
      */
     public static function mePatientIdentification($ident_type = 'default'){
         
         if(empty($ident_type)){
             $ident_type = 'default';
         }
         
         $mePatient  = array();
         //DEFAULT
         $mePatient['default']['file']['tag_tabname'] = "mePatient";
         $mePatient['default']['file']['name'] = "mePatientFile";
         $mePatient['default']['file']['course_entry'] = "Eine mePatient Datei wurde durch den Patienten übermittelt.";
         $mePatient['default']['file']['course_shortcut'] = "PG";
         $mePatient['default']['file']['course_tabname'] = "mePatient_uploaded_img_from_device";
         
         
         
         $mePatient['default']['results']['tag_tabname'] = "mePatient_Fragebogen";
         $mePatient['default']['results']['name'] = "Fragebogen";
         $mePatient['default']['results']['course_entry'] = "Ein mePatient Fragebogen wurde ausgefüllt.";
         $mePatient['default']['results']['course_shortcut'] = "PF";
         $mePatient['default']['results']['course_tabname'] = "mePatient_survey_pdf_from_device";
         
         
         $mePatient['default']['notifications_interval']['course_entry'] = 'Eine Interval-Push Nachricht mit dem Inhalt "%message%" wurde versendet.';
         $mePatient['default']['notifications_interval']['course_shortcut'] = "PG";
         
         $mePatient['default']['notifications_pushNow']['course_entry'] = 'Eine Push Nachricht mit dem Inhalt "%message%" wurde versendet.';
         $mePatient['default']['notifications_pushNow']['course_shortcut'] = "PG";
         
         // Ancuta 12.03.2020
         $mePatient['default']['todos']['text'] = "Bitte prüfen - %patient_name% , Bild(er) wurden durch den Patienten hochgeladen";
         // --
         
         // LIGETIS
         $mePatient['ligetis']['file']['tag_tabname'] = "ligetis_xhcange";//"Ligetis XHCANGE";
         $mePatient['ligetis']['file']['name'] = "Ligetis XCHANGE Datei";
         $mePatient['ligetis']['file']['course_entry'] = "Eine Ligetis XCHANGE Datei wurde durch den Patienten übermittelt.";
         $mePatient['ligetis']['file']['course_shortcut'] = "PG";
         $mePatient['ligetis']['file']['course_tabname'] = "mePatient_uploaded_img_from_device";
         
         
         $mePatient['ligetis']['results']['tag_tabname'] = "ligetis_xhcange_survey";//"Ligetis XHCANGE";
         $mePatient['ligetis']['results']['name'] = "Fragebogen";
         $mePatient['ligetis']['results']['course_entry'] = "Ein Ligetis XCHANGE Fragebogen wurde ausgefüllt.";
         $mePatient['ligetis']['results']['course_shortcut'] = "PF";
         $mePatient['ligetis']['results']['course_tabname'] = "mePatient_survey_pdf_from_device";
         
         
         $mePatient['ligetis']['notifications_interval']['course_entry'] = 'Eine Interval-Push Nachricht mit dem Inhalt "%message%" wurde versendet.';
         $mePatient['ligetis']['notifications_interval']['course_shortcut'] = "PG";
         
         $mePatient['ligetis']['notifications_pushNow']['course_entry'] = 'Eine Push Nachricht mit dem Inhalt "%message%" wurde versendet.';
         $mePatient['ligetis']['notifications_pushNow']['course_shortcut'] = "PG";
         
         // Ancuta 12.03.2020
         $mePatient['ligetis']['todos']['text'] = "Es liegen neue Informationen in der Akte des Patienten %patient_name% vor. Bitte holen Sie das Originaldokument beim behandelnden Arzt des Patienten ein.";
         // --
         return $mePatient[$ident_type];
     }
     
     
     
     /**
      * @author Ancuta
      * 14.04.2020
      * ISPC-2517  
      * @return string[]
      * #ISPC-2512PatientCharts
      */
     public function available_events(){
         
         $events = array(
            'vital_signs',    
            'organic_entries_exits', 
            'awake_sleep_status',
            'contact_form_main',
            'contact_form_items',
            'custom_events',
            'positioning',
            'suckoff',
         	'artificial_entries_exits',
         	'symptomatology',
         	'medication_dosage_interaction',
         	'symptomatologyII', //ISPC-2516 Carmen 15.07.2020
         	'vigilance_awareness', //ISPC-2683 carmen 15.10.2020
			 'beatmung', //ISPC-2697, elena, 10.11.2020
			 'ventilation_info', //ISPC-2841 Lore 22.03.2021
             'patient_problems', //ISPC-2864 Ancuta 13.04.2021
             'pcoc_phase',//TODO-4163
         );
         
         return $events;
     }
     
     
     /**
      * @author Lore
      * 25.06.2020
      * ISPC-2612
      * @return string[]
      */
     public function connection_lists(){
         
         $menu = Doctrine_Query::create()
         ->select("*")
		 ->from("Menus")
		 ->where('isdelete = "0" ');
		 $menu_arr = $menu->execute();
		
		 $menu_name = array();
		 foreach($menu_arr as $keym => $valm){
		     if(!empty($valm['menu_link'])){
		         $menu_name[$valm['menu_link']] = $valm['menu_title'];
		     }
		 }
		 
         $lists = array(
             'Pharmacy' => array(                                   //Apotheke
                 'link' => "pharmacy/pharmacylist",
                 'db_name' => "sysdat",
                 'db_table_name' => "pharmacy",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["pharmacy/pharmacylist"]
             ),
             'Locations' => array(                                  //Aufenthaltsorte
                 'link' => "locations/locationslist",
                 'patient_connection' => array(
                     'patient_model' => "PatientLocation",
                     'patient_db_name' => "idat",
                     'patient_db_table_name' => "patient_location",
                     'patient_db_column_name' => "location_id",
                 ),
                 'db_name' => "sysdat",
                 'db_table_name' => "locations_master",
                 'client_column' => "client_id",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["locations/locationslist"]
             ),
             'OrderAdmission' => array(                             //Auftrag bei Kontaktaufnahme
                 'link' => "client/orderadmission",
                 'db_name' => "mdat",
                 'db_table_name' => "orderadmission",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["client/orderadmission"]
             ),
             'PatientReferredBy' => array(                          //Überwiesen durch
                 'link' => "patientreferredby/referredbylist",
                 'patient_connection' => array(
                     'patient_model' => "PatientMaster",
                     'patient_db_name' => "idat",
                     'patient_db_table_name' => "patient_master",
                     'patient_db_column_name' => "referred_by",
                 ),
                 'db_name' => "sysdat",
                 'db_table_name' => "patient_referredby",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["patientreferredby/referredbylist"]
             ),
//              'BedarfsmedicationMaster' => array(                       //Bedarfs Medikation
//                  'link' => "medication/bedarfsmediclist",
//                  'db_name' => "sysdat",
//                  'db_table_name' => "bedarfsmedication_master",
//                  'client_column' => "clientid",
//                  'isdelete_column' => 'isdelete',
//                  'list_ident_column' => "",
//                  'list_ident_value' => "",
//                  'except_columns' => array('id','create_user','change_user','create_date','change_date'),
//                  'menu_name'  => $menu_name["medication/bedarfsmediclist"]
//              ),
             'MedicationTreatmentCare' => array(                       //Behandlungspflege
                 'link' => "medication/listtreatmentcare",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_treatment_care",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medication/listtreatmentcare"]
             ),
 
             'Servicesfuneral' => array(                                //Bestatter
                 'link' => "servicesfuneral/servicesfunerallist",
                 'db_name' => "sysdat",
                 'db_table_name' => "services_funeral",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["servicesfuneral/servicesfunerallist"]
             ),
             'FamilyDegree' => array(                                   //Beziehung zu Patienten
                 'link' => "contactpersonmaster/relation",
                 'db_name' => "sysdat",
                 'db_table_name' => "family_degree",
                 'patient_connection' => array(
                     'patient_model' => "ContactPersonMaster",
                     'patient_db_name' => "idat",
                     'patient_db_table_name' => "contactperson_master",
                     'patient_db_column_name' => "cnt_familydegree_id",
                 ),
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["contactpersonmaster/relation"]
             ),
       
             'Voluntaryworkers' => array(                                //Ehrenamtlichen / Koordinator
                 'link' => "voluntaryworkers/voluntaryworkerslist",
                 'db_name' => "sysdat",
                 'db_table_name' => "voluntaryworkers",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["voluntaryworkers/voluntaryworkerslist"].' (includes '.$menu_name["hospiceassociation/hospiceassociationlist"].')'
             ),
             'HealthInsurance' => array(                                //eigene Krankenkassen
                 'link' => "healthinsurance/clienthealthinsurancelist",
                 'db_name' => "sysdat",
                 'db_table_name' => "health_insurance",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["healthinsurance/clienthealthinsurancelist"]
             ),
             'DischargeMethod' => array(                                //Entlassungsart
                 'link' => "dischargemethod/listmethod",
                 'patient_connection' => array(
                     'patient_model' => "PatientDischarge",
                     'patient_db_name' => "idat",
                     'patient_db_table_name' => "patient_discharge",
                     'patient_db_column_name' => "discharge_method",
                 ),
                 'db_name' => "sysdat",
                 'db_table_name' => "discharge_method",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["dischargemethod/listmethod"]
             ),
             'DischargeLocation' => array(                               //Entlassungsorte
                 'link' => "dischargelocation/listlocation",
                 'patient_connection' => array(
                     'patient_model' => "PatientDischarge",
                     'patient_db_name' => "idat",
                     'patient_db_table_name' => "patient_discharge",
                     'patient_db_column_name' => "discharge_location",
                 ),
                 'db_name' => "mdat",
                 'db_table_name' => "discharge_location",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["dischargelocation/listlocation"]
             ),
             
             'Nutrition' => array(                                       //Ernährung
                 'link' => "medication/listmedicationnutrition",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_nutrition",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medication/listmedicationnutrition"]
             ),
             
//              'SpecialistsTypes' => array(                                //Facharzt Typen
//                  'link' => "specialists/specialiststypes",
//                  'db_name' => "sysdat",
//                  'db_table_name' => "specialists_types",
//                  'client_column' => "clientid",
//                  'isdelete_column' => 'isdelete',
//                  'list_ident_column' => "",
//                  'list_ident_value' => "",
//                  'except_columns' => array('id','create_user','change_user','create_date','change_date'),
//                  'menu_name'  => $menu_name["specialists/specialiststypes"]
//              ),
             
             'Specialists' => array(                                   //Fachärzte
                 'link' => "specialists/specialists",
                 'db_name' => "sysdat",
                 'db_table_name' => "specialists",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["specialists/specialists"] .'( includes'.$menu_name["specialists/specialiststypes"].')'
             ),
             
             
             'FamilyDoctor' => array(                                   //Hausärzte
                 'link' => "familydoctor/familydoctorlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "family_doctor",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["familydoctor/familydoctorlist"]
             ),
             'OrganicEntriesExitsLists' => array(                       //Organic
                 'link' => "clientlists/organicentriesexitslist",
                 'db_name' => "sysdat",
                 'db_table_name' => "organic_entries_exits_lists",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["clientlists/organicentriesexitslist"]
             ),
             
             'ClientOrderMaterials.auxiliaries' => array(                                   // Hilfsmittel der Bestellung
                 'link' => "orders/auxiliarieslist",
                 'db_name' => "mdat",
                 'db_table_name' => "client_order_materials",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'special_identify_column' => "category",
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["orders/auxiliarieslist"]
             ),
             'ClientOrderMaterials.nursingauxiliaries' => array(                                   //Pflegehilfsmittel der Bestellung
                 'link' => "orders/nursingauxiliarieslist",
                 'db_name' => "mdat",
                 'db_table_name' => "client_order_materials",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'special_identify_column' => "category",
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["orders/nursingauxiliarieslist"]
             ),
             'Remedies' => array(                                              //Heilmittel
                 'link' => "remedies/remedieslist",
                 'db_name' => "sysdat",
                 'db_table_name' => "remedies",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["remedies/remedieslist"]
             ),
             'Aid' => array(                                           //Hilfsmittel
                 'link' => "aid/aidlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "aid",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["aid/aidlist"]
             ),
             
             'Homecare' => array(                                   //Homecare
                 'link' => "homecare/homecarelist",
                 'db_name' => "sysdat",
                 'db_table_name' => "homecare",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["homecare/homecarelist"]
             ),
             /* 
             'Hospiceassociation' => array(                             //Hospizvereine
                 'link' => "hospiceassociation/hospiceassociationlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "hospice_association",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["hospiceassociation/hospiceassociationlist"]
             ),
              */
             
             'Services' => array(                             //Leistungen
                 'link' => "services/serviceslist",
                 'db_name' => "sysdat",
                 'db_table_name' => "services",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["services/serviceslist"]
             ),
             'MedicationFrequency' => array(                             //Krisen Intervalle
                 'link' => "medicationnew/listmedicationfrequency",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_frequency",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/listmedicationfrequency"]
             ),
             'Medipumps' => array(                             //Medikamenten Pumpen
                 'link' => "medication/medipumps",
                 'db_name' => "sysdat",
                 'db_table_name' => "medipumps",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medication/medipumps"]
             ),
             'MedicationReceipt' => array(                             //Rezept Medikation
                 'link' => "medication/listreceiptmedication",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_receipt",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medication/listreceiptmedication"]
             ),
             'Pflegedienstes' => array(                             //Pflegedienst
                 'link' => "pflegedienste/pflegedienstelist",
                 'db_name' => "sysdat",
                 'db_table_name' => "pflegedienste",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["pflegedienste/pflegedienstelist"]
             ),
             
             'CareservicesGroups' => array(                             //Pflege-Leistungs-Gruppen
                 'link' => "careservices/list",
                 'db_name' => "sysdat",
                 'db_table_name' => "care_services_groups",
                 'client_column' => "client",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["careservices/list"],
                 'subcategory'=> array(
                     'sub_model' => 'CareservicesItems', 
                     'link' => "careservices/list",
                     'db_name' => "sysdat",
                     'db_table_name' => "care_services_items",
                     'client_column' => "clientid", //ISPC-2652, elena, 08.10.2020
                     'isdelete_column' => 'isdelete',
                     'list_ident_column' => "",
                     'list_ident_value' => "",
                     'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                     'menu_name'  => $menu_name["careservices/list"]
                )
             ),
             'Physiotherapists' => array(                             //Physiotherapeut
                 'link' => "physiotherapist/physiotherapistlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "physiotherapist",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["physiotherapist/physiotherapistlist"]
             ),
             'Supplies' => array(                             //Sanitätshäuser
                 'link' => "supplies/supplieslist",
                 'db_name' => "sysdat",
                 'db_table_name' => "supplies",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["supplies/supplieslist"]
             ),
             'ClientShifts' => array(                             //Schichten
                 'link' => "roster/shiftlist",
                 'patient_connection' => array(
                     'patient_model' => "Roster",
                     'patient_db_name' => "sysdat",
                     'patient_db_table_name' => "duty_roster",
                     'patient_db_column_name' => "shift",
                 ),
                 'db_name' => "sysdat",
                 'db_table_name' => "client_shifts",
                 'client_column' => "client",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','istours','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["roster/shiftlist"]
             ),
             'Suppliers' => array(                             //sonst. Versorger
                 'link' => "suppliers/supplierslist",
                 'db_name' => "sysdat",
                 'db_table_name' => "suppliers",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["suppliers/supplierslist"]
             ),
             'Medication' => array(                             //Medikamente
                 'link' => "medication/listmedication",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_master",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medication/listmedication"]
             ),
             'Churches' => array(                             //Pfarreien
                 'link' => "churches/churcheslist",
                 'db_name' => "sysdat",
                 'db_table_name' => "churches",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "indrop",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["churches/churcheslist"]
             ),
/*              'FormsTextsList' => array(                             //Formular Satzbausteine
                 'link' => "clientlists/formstextslist",
                 'db_name' => "sysdat",
                 'db_table_name' => "forms_texts_list",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["clientlists/formstextslist"]
             ), */
             'HospitalReasons' => array(                             //Grund der Aufnahme
                 'link' => "locations/listhospitalreasons",
                 'db_name' => "sysdat",
                 'db_table_name' => "locations_hospital_reasons",
                 'patient_connection' => array(
                     'patient_model' => "PatientLocation",
                     'patient_db_name' => "idat",
                     'patient_db_table_name' => "patient_location",
                     'patient_db_column_name' => "reason",
                 ),
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["locations/listhospitalreasons"]
             ),
             'NutritionFormularList' => array(                             //Ernährung Form -> Applikation
                 'link' => "clientlists/nutritionformularlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "nutrition_form_list",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["clientlists/nutritionformularlist"]
             ),
/*              'Member' => array(                             //Mitgliederzahl
                 'link' => "member/memberslist",
                 'db_name' => "sysdat",
                 'db_table_name' => "member",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["member/memberslist"]
             ), */

             'ClientSymptoms' => array(                             //Symptome II
                 'link' => "symptomatology/clientsymptomlist",
                 'patient_connection' => array(
                     'patient_model' => "FormBlockClientSymptoms",
                     'patient_db_name' => "mdat",
                     'patient_db_table_name' => "form_block_client_symptoms",
                     'patient_db_column_name' => "symptom_id",
                 ),
                 'db_name' => "mdat",
                 'db_table_name' => "client_symptoms",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["symptomatology/clientsymptomlist"]
             ),
             
             
             'ClientSymptomsGroups' => array(                             //Symptome II >>
                 'link' => "symptomatology/clientsymptomgroupslist",
                 'db_name' => "mdat",
                 'db_table_name' => "client_symptoms_groups",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
//                  'menu_name'  => $menu_name["symptomatology/clientsymptomgroupslist"].' ( includes '.$menu_name["symptomatology/clientsymptomlist"].')'
                 'menu_name'  => $menu_name["symptomatology/clientsymptomgroupslist"]
             ),
             
             
          /*    'MedicationUnit' => array(                             //Medikation - Liste Einheit
                 'link' => "medicationnew/unitlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_unit",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/unitlist"]
             ),
             'MedicationType' => array(                             //Medikamente II ->  Applikationsweg
                 'link' => "medicationnew/typelist",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_type",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/typelist"]
             ),
             'MedicationDosageform' => array(                             //Medikation - Liste Darreichungsform
                 'link' => "medicationnew/dosageformlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_dosage_form",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/dosageformlist"]
             ),
             'MedicationIndications' => array(                             //Medikation - Liste Indikation
                 'link' => "medicationnew/indicationlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_indication",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/indicationlist"]
             ), */
/*              'MedicationIntervals' => array(                               //Medikamente II -> Zeitschema
                 'link' => "medicationnew/timescheme",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_intervals",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/timescheme"]
             ), */
             
             
             'MedicationUnit' => array(                             //Medikation - Liste Einheit
                 'link' => "medicationnew/unitlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_unit",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/unitlist"]
             ),
             
             'MedicationType' => array(                             //Medikamente II ->  Applikationsweg
                 'link' => "medicationnew/typelist",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_type",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/typelist"]
             ),
             
             'MedicationDosageform' => array(                             //Medikation - Liste Darreichungsform
                 'link' => "medicationnew/dosageformlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_dosage_form",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/dosageformlist"]
             ),
             
             
             'MedicationIndications' => array(                             //Medikation - Liste Indikation
                 'link' => "medicationnew/indicationlist",
                 'db_name' => "sysdat",
                 'db_table_name' => "medication_indication",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "extra",
                 'list_ident_value' => "0",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medicationnew/indicationlist"]
             ),
             
             'ClientOrderMaterials.dressings' => array(                             //Verbandsstoffe der Bestellung
                 'link' => "orders/dressingslist",
                 'db_name' => "mdat",
                 'db_table_name' => "client_order_materials",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'special_identify_column' => "category",
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["orders/dressingslist"]
             ),
             'ClientOrderMaterials.drugs' => array(                             //Arzneimittel der Bestellung
                 'link' => "orders/drugslist",
                 'db_name' => "mdat",
                 'db_table_name' => "client_order_materials",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'special_identify_column' => "category",
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["orders/drugslist"]
             ),
             
             'FormBlocksSettings.measures' => array(                             //Arzneimittel der Bestellung
                 'link' => "socialcode/blockmeasuresoptions",
                 'db_name' => "mdat",
                 'db_table_name' => "form_blocks_settings",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'special_identify_column' => "block",
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["socialcode/blockmeasuresoptions"]
             ),
 
 
             'FormBlocksSettings.ebmii' => array(                             //Arzneimittel der Bestellung
                 'link' => "socialcode/formblocksettings",
                 'db_name' => "mdat",
                 'db_table_name' => "form_blocks_settings",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'special_identify_column' => "block",
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["socialcode/formblocksettings"]
             ),
             'FormBlocksSettings.goaii' => array(                             //Arzneimittel der Bestellung
                 'link' => "socialcode/formblocksettings",
                 'db_name' => "mdat",
                 'db_table_name' => "form_blocks_settings",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'special_identify_column' => "block",
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["socialcode/formblocksettings"]
             ),
             
             'Diagnosis' => array(                             //Liste->Client Diagnosis list 
                 'link' => "diagnosis/listclientdiagnosis",
                 'db_name' => "sysdat",
                 'db_table_name' => "diagnosis",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["diagnosis/listclientdiagnosis"]
             ),
             
             'BedarfsmedicationMaster' => array(                              
                 'link' => "medication/bedarfsmediclist",
                 'db_name' => "sysdat",
                 'db_table_name' => "bedarfsmedication_master",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medication/bedarfsmediclist"]
             ), 
  
             
             'MedicationsSetsList' => array(
                 'link' => "medication/medicationssetslist",
                 'db_name' => "mdat",
                 'db_table_name' => "medications_sets_list",
                 'client_column' => "clientid",
                 'isdelete_column' => 'isdelete',
                 'list_ident_column' => "",
                 'list_ident_value' => "",
                 'except_columns' => array('id','create_user','change_user','create_date','change_date'),
                 'menu_name'  => $menu_name["medication/medicationssetslist"]
             ),
             
             
             
         );
         
         return $lists;
         

     }
     
     
     /**
      * @author Lore
      * 06.07.2020
      * ISPC-2614
      * @return string[]
      */
     public function connection_lists_versorgers(){
         
         
         $extra_forms = Doctrine_Query::create()
         ->select("*")
         ->from("ExtraForms IndexBy id")
         ->where('isdelete = "0" ');
         $extra_forms_arr = $extra_forms->fetchArray();
         
         
         $lists_versorger = array(
             
             "FamilyDoctor" => array(                                   //Hausarzt - FamilyDoctor
                 "form_id" => "9",
                 "form_name" => $extra_forms_arr['9']['formname'],
                 "form_models" => "FamilyDoctor",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "family_doctor",
                 "master_lists" => array("FamilyDoctor"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                         )
                     ),
                 "has_module_id" => ""
             ),
             
             "PatientChurches" => array(                                //Pfarreien - PatientChurches
                 "form_id" => "53",
                 "form_name" => $extra_forms_arr['53']['formname'],
                 "form_models" => "PatientChurches",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "patient_churches",
                 "master_lists" => array("Churches"),
                 "connected" => array(
                     "Churches" => array(                   
                         "model" => "Churches",
                         "name" => "sysdat",
                         "table_name" => "churches",
                     )
                 ),
                 "has_module_id" => ""
             ),
            /*  
             "PatientRemedies" => array(                                //Hilfsmittel II - PatientRemedies  vs PatientRemedies2Supplies
                 "form_id" => "49",
                 "form_name" => $extra_forms_arr['49']['formname'],
                 "form_models" => "PatientRemedies",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "patient_remedies",
                 "master_lists" => array("Supplies"),
                 "connected" => array(
                        "Supplies" => array(
                            "model" => "Supplies",
                            "name" => "sysdat",
                            "table_name" => "supplies",
                        )
                 ),
                 "has_module_id" => ""
             ), */
             
             "PatientPhysiotherapist" => array(                             //Physiotherapeuten - PatientPhysiotherapist
                 "form_id" => "47",
                 "form_name" => $extra_forms_arr['47']['formname'],
                 "form_models" => "PatientPhysiotherapist",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "patient_physiotherapist",
                 "master_lists" => array("Physiotherapists"),
                 "connected" => array(
                     "Physiotherapists" => array(
                         "model" => "Physiotherapists",
                         "name" => "sysdat",
                         "table_name" => "physiotherapist",
                     )
                 ),
                 "has_module_id" => ""
             ),
             
             "PatientSpecialists" => array(                                 //Facharzt - PatientSpecialists
                 "form_id" => "44",
                 "form_name" => $extra_forms_arr['44']['formname'],
                 "form_models" => "PatientSpecialists",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_specialists",
                 "master_lists" => array("Specialists"),
                 "connected" => array(
                        "Specialists" => array(
                            "model" => "Specialists",
                            "name" => "sysdat",
                            "table_name" => "specialists",
                        )
                 ),
                 "has_module_id" => ""
             ),
             
             "PatientPharmacy" => array(                                                 //Apotheke - PatientPharmacy
                 "form_id" => "26",             
                 "form_name" => $extra_forms_arr['26']['formname'],
                 "form_models" => "PatientPharmacy",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "patient_pharmacy",
                 "master_lists" => array("Pharmacy"),
                 "connected" => array(
                     "Pharmacy" => array(
                         "model" => "Pharmacy",
                         "name" => "sysdat",
                         "table_name" => "pharmacy",
                     )
                 ),
                 "has_module_id" => ""
             ),
             
             "PatientPflegedienste" => array(                                              //Pflegedienst - PatientPflegedienste
                 "form_id" => "15",
                 "form_name" => $extra_forms_arr['15']['formname'],
                 "form_models" => "PatientPflegedienste",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "patient_pflegedienste",
                 "master_lists" => array("Pflegedienstes"),
                 "connected" => array(
                     "Pflegedienstes" => array(
                         "model" => "Pflegedienstes",
                         "name" => "sysdat",
                         "table_name" => "pflegedienste",
                     )
                 ),
                 "has_module_id" => ""
             ),
             
             "PatientVoluntaryworkers" => array(                                           //Ehrenamtlichen / Koordinator - PatientVoluntaryworkers
                 "form_id" => "38",
                 "form_name" => "Ehrenamtliche",
                 "form_models" => $extra_forms_arr['38']['formname'],
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "patient_voluntaryworkers",
                 "master_lists" => array("Voluntaryworkers"),
                 "connected" => array(
                     "Voluntaryworkers" => array(
                         "model" => "Voluntaryworkers",
                         "name" => "sysdat",
                         "table_name" => "voluntaryworkers",
                     )
                 ),
                 "has_module_id" => ""
             ),

             "PatientHealthInsurance" => array(                                           //Krankenkasse - PatientHealthInsurance
                 "form_id" => "10",
                 "form_name" => $extra_forms_arr['10']['formname'],
                 "form_models" => "PatientHealthInsurance",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_health_insurance",
                 "master_lists" => array("HealthInsurance"),
                 "connected" => array(
                     "HealthInsurance" => array(
                         "model" => "HealthInsurance",
                         "name" => "sysdat",
                         "table_name" => "health_insurance",
                     )
                 ),
                 "has_module_id" => ""
             ),
                 
             "PatientSupplies" => array(                                                //Sanitätshäuser - PatientSupplies
                 "form_id" => "41",
                 "form_name" => $extra_forms_arr['41']['formname'],
                 "form_models" => "PatientSupplies",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "patient_supplies",
                 "master_lists" => array("Supplies"),
                 "connected" => array(
                        "Supplies" => array(
                              "model" => "Supplies",
                              "name" => "sysdat",
                              "table_name" => "supplies",
                              )
                 ),
                 "has_module_id" => ""
             ),

             "PatientHospiceassociation" => array(                                      //Hospizdienst - PatientHospiceassociation
                 "form_id" => "42",
                 "form_name" => $extra_forms_arr['42']['formname'],
                 "form_models" => "PatientHospiceassociation",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "patient_hospice_association",
                 "master_lists" => array("Voluntaryworkers","Hospiceassociation"),
                 "connected" => array(
                     "Hospiceassociation" => array(
                         "model" => "Hospiceassociation",
                         "name" => "sysdat",
                         "table_name" => "hospice_association",
                     )
                 ),
                 "has_module_id" => ""
             ),


             "PatientSuppliers" => array(                                               //sonst. Versorger - PatientSuppliers
                 "form_id" => "46",
                 "form_name" => $extra_forms_arr['46']['formname'],
                 "form_models" => "PatientSuppliers",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "patient_suppliers",
                 "master_lists" => array("Suppliers"),
                 "connected" => array(
                     "Suppliers" => array(
                         "model" => "Suppliers",
                         "name" => "sysdat",
                         "table_name" => "suppliers",
                     )
                 ),
                 "has_module_id" => ""
             ),

             "PatientHomecare" => array(                                               //Homecare - PatientHomecare
                 "form_id" => "48",
                 "form_name" => $extra_forms_arr['48']['formname'],
                 "form_models" => "PatientHomecare",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "sysdat",
                 "db_table_name" => "patient_homecare",
                 "master_lists" => array("Homecare"),
                 "connected" => array(
                     "Homecare" => array(
                         "model" => "Homecare",
                         "name" => "sysdat",
                         "table_name" => "homecare",
                     )
                 ),
                 "has_module_id" => ""
             ),
 
         );
         
         return $lists_versorger;
         
     }
     
     /**
      * @author Lore
      * 07.07.2020
      * ISPC-2614
      * @return string[]
      */
     public function connection_lists_stammdaten(){
         
         $extra_forms = Doctrine_Query::create()
         ->select("*")
         ->from("ExtraForms IndexBy id")
         ->where('isdelete = "0" ');
         $extra_forms_arr = $extra_forms->fetchArray();
         
         $lists_stammdaten = array(
             
//              "PatientMaster" => array(                          //Patient
//                  "form_id" => "14",
//                  "form_name" => $extra_forms_arr['14']['formname'],
//                  "form_models" => "PatientMaster",
//                  "entry_db" => "single" ,
//                  "settings_form" => "extraforms/formlist",
//                  "db_name" => "idat",
//                  "db_table_name" => "patient_master",
// //                  "master_lists" => array("PatientMaster"),
//                  "connected" => array(
//                      /*                    "PatientReligions" => array(
//                       "model" => "PatientReligions",
//                       "name" => "idat",
//                       "table_name" => "patient_religionszugehorigkeit",
//                       ) */
//                  ),
//                  "has_module_id" => ""
//              ),
             
             "ContactPersonMaster" => array(                          //Ansprechpartner
                 "form_id" => "12",
                 "form_name" => $extra_forms_arr['12']['formname'],
                 "form_models" => "ContactPersonMaster",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "contactperson_master",
                 //                  "master_lists" => array("ContactPersonMaster"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     ),
                     "PatientAcp" => array(
                         "model" => "PatientAcp",
                         "name" => "idat",
                         "table_name" => "patient_acp",
                     )
                 ),
                 "has_module_id" => "102"
             ),
             
             
             
             
             
             "PatientOrientation" => array(                                   //Orientierung II
                 "form_id" => "54",
                 "form_name" => $extra_forms_arr['54']['formname'],
                 "form_models" => "PatientOrientation",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "patient_orientation",
                 //"master_lists" => array("PatientOrientation"), // no master list used
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                 ),
                 "has_module_id" => ""
             ),
                          
             "PatientMobility2" => array(                                   //Mobilität II
                 "form_id" => "55",
                 "form_name" => $extra_forms_arr['55']['formname'],
                 "form_models" => "PatientMobility2",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_mobility2",
                 //"master_lists" => array("PatientMobility2"),// no master list used
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                 ),
                 "has_module_id" => ""
             ),
             /* 
             // ADD TO FIRST CONNECTION LIST
             "PatientArtificialEntriesExits" => array(                          //Künstliche Zugänge - Ausgänge - artificialentryexit
                 "form_id" => "100",
                 "form_name" => $extra_forms_arr['100']['formname'],
                 "form_models" => "PatientArtificialEntriesExits",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "patient_artificial_entries_exits",
                 "master_lists" => array("PatientArtificialEntriesExits"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                 ),
                 "has_module_id" => ""
             ), */
             /* 
             "PatientMaster_Hospiz_Hospizverein_SAPV_AAPV" => array(                          //Hospiz - Hospizverein - SAPV/AAPV
                 "form_id" => "40",
                 "form_name" => $extra_forms_arr['40']['formname'],
                 "form_models" => "PatientMaster_Hospiz_Hospizverein_SAPV_AAPV",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_qpa_mapping",
                 "master_lists" => array("PatientMaster_Hospiz_Hospizverein_SAPV_AAPV"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                 ),
                 "has_module_id" => ""
             ), */
             
             "PatientMedipumps" => array(                          //Hilfsmittel Verleih
                 "form_id" => "43",
                 "form_name" => $extra_forms_arr['43']['formname'],
                 "form_models" => "PatientMedipumps",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "patient_medipumps",
                 "master_lists" => array("Medipumps"),
                 "connected" => array(
                     "Medipumps" => array(
                         "model" => "Medipumps",
                         "name" => "sysdat",
                         "table_name" => "medipumps",
                     )/* ,
                     "ToDos" => array(
                         "model" => "ToDos",
                         "name" => "mdat",
                         "table_name" => "todos",
                     ) */
                 ),
                 "has_module_id" => ""
             ),

             "PatientHospizverein" => array(                          //Hospizverein
                 "form_id" => "39",
                 "form_name" => $extra_forms_arr['39']['formname'],
                 "form_models" => "PatientHospizverein",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_hospizverein",
                 //"master_lists" => array("PatientHospizverein"),
                 "connected" => array(
                     "Messages" => array(
                         "model" => "Messages",
                         "name" => "mdat",
                         "table_name" => "messages",
                         "client_id" => "48"                //   ????
                     ),
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )

                 ),
                 "has_module_id" => ""
             ),

             "PatientTherapieplanung" => array(                          //Vorausschauende Therapieplanung
                 "form_id" => "37",
                 "form_name" => $extra_forms_arr['37']['formname'],
                 "form_models" => "PatientTherapieplanung",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_therapieplanung",
                 //"master_lists" => array("PatientTherapieplanung"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                     
                 ),
                 "has_module_id" => ""
             ),
             
             /*
              * Setting added by user  per patient NOT OK to sync
              *     
             "PatientVisitsSettings" => array(                          //VisitsPlanning = Tourenplanung
                 "form_id" => "45",
                 "form_name" => $extra_forms_arr['45']['formname'],
                 "form_models" => "PatientVisitsSettings",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_visits_settings",
                 "master_lists" => array("PatientVisitsSettings"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                     
                 ),
                 "has_module_id" => ""
             ), */
             
             "PatientMaintainanceStage" => array(                          //Pflegegrade = Pflegestufe
                 "form_id" => "7",
                 "form_name" => $extra_forms_arr['7']['formname'],
                 "form_models" => "PatientMaintainanceStage",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_maintainance_stage",
                 //"master_lists" => array("PatientMaintainanceStage"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                     
                 ),
                 "has_module_id" => ""
             ),

             "PatientSupply" => array(                          //Versorgung
                 "form_id" => "4",
                 "form_name" => $extra_forms_arr['4']['formname'],
                 "form_models" => "PatientSupply",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_supply",
                 //"master_lists" => array("PatientSupply"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )/* ,
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     ) */
                     
                 ),
                 "has_module_id" => ""
             ),
            
             "PatientReligions" => array(                          //Religionszugehörigkeit
                 "form_id" => "8",
                 "form_name" => $extra_forms_arr['8']['formname'],
                 "form_models" => "PatientReligions",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_religionszugehorigkeit",
                 //"master_lists" => array("PatientReligions"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                     /* ,
                     "BoxHistory" => array(
                     "model" => "BoxHistory",
                     "name" => "mdat",
                     "table_name" => "box_history",
                     ) */
                     
                 ),
                 "has_module_id" => ""
             ),
             
             "PatientGermination" => array(                          //Keimbesiedelung
                 "form_id" => "52",
                 "form_name" => $extra_forms_arr['52']['formname'],
                 "form_models" => "PatientGermination",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_germination",
                 //"master_lists" => array("PatientGermination"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                     /* ,
                      "BoxHistory" => array(
                      "model" => "BoxHistory",
                      "name" => "mdat",
                      "table_name" => "box_history",
                      ) */
                     
                 ),
                 "has_module_id" => ""
             ),

             "PatientLives" => array(                          //Patient lebt
                 "form_id" => "1",
                 "form_name" => $extra_forms_arr['1']['formname'],
                 "form_models" => "PatientLives",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_lives",
                 //"master_lists" => array("PatientLives"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                     /* ,
                      "BoxHistory" => array(
                      "model" => "BoxHistory",
                      "name" => "mdat",
                      "table_name" => "box_history",
                      ) */
                     
                 ),
                 "has_module_id" => ""
             ),
           /*   
            * THis block will be sync-ed separatly in Patient Locations 
             "PatientLocation" => array(                          //Aufenthaltsort
                 "form_id" => "13",
                 "form_name" => $extra_forms_arr['13']['formname'],
                 "form_models" => "PatientLocation",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_location",
                 "master_lists" => array("Locations"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
//                      ,
//                       "Messages" => array(
//                          "model" => "Messages",
//                          "name" => "mdat",
//                          "table_name" => "messages",
//                          "client_id" => "48"                //   ????
//                      ),
//                       "BoxHistory" => array(
//                       "model" => "BoxHistory",
//                       "name" => "mdat",
//                       "table_name" => "box_history",
//                       )
                 ),
                 "has_module_id" => "65"
             ),
 */
             
             /* 
             "PatientCrisisHistory" => array(                          //Krisengeschichte == crisishistory
                 "form_id" => "60",
                 "form_name" => $extra_forms_arr['60']['formname'],
                 "form_models" => "PatientCrisisHistory",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_crisis_history",
//                  "master_lists" => array("PatientCrisisHistory"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     )
                 ),
                 "has_module_id" => ""
             ),
              */
     
//              "PatientAcp" => array(                          //Patientenverfügung = ACP = Advanced Care Planning
//                  "form_id" => "6",
//                  "form_name" => $extra_forms_arr['6']['formname'],
//                  "form_models" => "PatientAcp",
//                  "entry_db" => "single" ,
//                  "settings_form" => "extraforms/formlist",
//                  "db_name" => "idat",
//                  "db_table_name" => "patient_acp",
//                  //"master_lists" => array("PatientAcp"),
//                  "connected" => array(
//                      "ContactPersonMaster" => array(
//                          "model" => "ContactPersonMaster",
//                          "name" => "idat",
//                          "table_name" => "contactperson_master",
//                      ),
//                      "PatientFileUpload" => array(
//                          "model" => "PatientFileUpload",
//                          "name" => "mdat",
//                          "table_name" => "patient_file",
//                      )
//                  ),
//                  "has_module_id" => ""
//              ),

             "SapvVerordnung" => array(                          //SAPV Verordnung
                 "form_id" => "11",
                 "form_name" => $extra_forms_arr['11']['formname'],
                 "form_models" => "SapvVerordnung",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_sapvverordnung",
//                  "master_lists" => array("SapvVerordnung",'FamilyDoctor','Locations'),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     ),
                     "FamilyDoctor" => array(
                         "model" => "FamilyDoctor",
                         "name" => "sysdat",
                         "table_name" => "family_doctor",
                     ),
                     "Specialists" => array(
                         "model" => "Specialists",
                         "name" => "sysdat",
                         "table_name" => "specialists",
                     ),
                     "Locations" => array(
                         "model" => "Locations",
                         "name" => "sysdat",
                         "table_name" => "locations_master",
                     ),
                     "PatientStandbyDetails" => array(
                         "model" => "PatientStandbyDetails",
                         "name" => "idat",
                         "table_name" => "patient_standby_details",
                     ),
                     "PatientReadmission" => array(
                         "model" => "PatientReadmission",
                         "name" => "idat",
                         "table_name" => "patient_readmission",
                     ),
                     "PatientDischarge" => array(
                         "model" => "PatientDischarge",
                         "name" => "idat",
                         "table_name" => "patient_discharge",
                     ),
                     "SgbvForms" => array(
                         "model" => "SgbvForms",
                         "name" => "mdat",
                         "table_name" => "sgbv_forms",
                     ),
                     "SgbvFormsHistory" => array(
                         "model" => "SgbvFormsHistory",
                         "name" => "mdat",
                         "table_name" => "sgbv_forms_history",
                     ),
                     "SgbvFormsItems" => array(
                         "model" => "SgbvFormsItems",
                         "name" => "mdat",
                         "table_name" => "sgbv_forms_items",
                     ),
                     "PatientApprovedVisitTypes" => array(
                         "model" => "PatientApprovedVisitTypes",
                         "name" => "mdat",
                         "table_name" => "patient_approved_visit_types",
                     ),
                     "PatientCourse" => array(
                         "model" => "PatientCourse",
                         "name" => "mdat",
                         "table_name" => "patient_course",
                     ),
                     "VollversorgungHistory" => array(
                         "model" => "VollversorgungHistory",
                         "name" => "mdat",
                         "table_name" => "vollversorgung_history",
                     ),
                      "BoxHistory" => array(
                          "model" => "BoxHistory",
                          "name" => "mdat",
                          "table_name" => "box_history",
                      )
                 ),
                 "has_module_id" => array("70","71","97")
             ),
             
             "PatientMobility" => array(                          //Mobilität
                 "form_id" => "5",
                 "form_name" => $extra_forms_arr['5']['formname'],
                 "form_models" => "PatientMobility",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_mobility",
                 //"master_lists" => array("PatientMobility"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
            /*  
             * 
             * This is per patient -  and related topatien details, not ok to sync
             "PatientSurveySettings" => array(                          //PatientSurveySettings = patient Survey Settings
                 "form_id" => "70",
                 "form_name" => $extra_forms_arr['70']['formname'],
                 "form_models" => "PatientSurveySettings",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_survey_settings",
                 "master_lists" => array("PatientSurveySettings"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
 */
             "PatientEmploymentSituation" => array(                          //Erwerbssituation = employment situation
                 "form_id" => "56",
                 "form_name" => $extra_forms_arr['56']['formname'],
                 "form_models" => "PatientEmploymentSituation",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "patient_employment_situation",
                 //"master_lists" => array("PatientEmploymentSituation"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
/* 
 * 
 * This are set per patient with DEVICES, not ok to sync
             "MePatientDevicesNotifications" => array(                          //MePatientDevicesNotifications = MePatient notifications for devices
                 "form_id" => "91",
                 "form_name" => $extra_forms_arr['91']['formname'],
                 "form_models" => "MePatientDevicesNotifications",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "mePatient_devices_notifications",
                 "master_lists" => array("MePatientDevicesNotifications"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),

             "MePatientDevices" => array(                          //MePatientDevices
                 "form_id" => "90",
                 "form_name" => $extra_forms_arr['90']['formname'],
                 "form_models" => "MePatientDevices",
                 "entry_db" => "multiple" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "mePatient_devices",
                 "master_lists" => array("MePatientDevices"),
                 "connected" => array(

                 ),
                 "has_module_id" => ""
             ),
              */
             "Stammdatenerweitert_orientierung" => array(                          //orientierung
                 "form_id" => "19",
                 "form_name" => $extra_forms_arr['19']['formname'],
                 "form_models" => "Stammdatenerweitert",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "stammdatenerweitert",
                 //"master_lists" => array("Stammdatenerweitert_orientierung"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
             
             "Stammdatenerweitert_wunsch" => array(                          //Wunsch des Patienten
                 "form_id" => "25",
                 "form_name" => $extra_forms_arr['25']['formname'],
                 "form_models" => "Stammdatenerweitert",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "stammdatenerweitert",
                // "master_lists" => array("Stammdatenerweitert_wunsch"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),

             "Stammdatenerweitert_ernahrung" => array(                          //Ernährung
                 "form_id" => "20",
                 "form_name" => $extra_forms_arr['20']['formname'],
                 "form_models" => "Stammdatenerweitert",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "stammdatenerweitert",
                // "master_lists" => array("Stammdatenerweitert_ernahrung"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),

             "Stammdatenerweitert_hilfsmittel" => array(                          //Hilfsmittel
                 "form_id" => "24",
                 "form_name" => $extra_forms_arr['24']['formname'],
                 "form_models" => "Stammdatenerweitert",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "stammdatenerweitert",
                 //"master_lists" => array("Stammdatenerweitert_hilfsmittel"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
             
             "Stammdatenerweitert_stastszugehorigkeit" => array(                          //Staatszugehörigkeit
                 "form_id" => "17",
                 "form_name" => $extra_forms_arr['17']['formname'],
                 "form_models" => "Stammdatenerweitert",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "stammdatenerweitert",
                 //"master_lists" => array("Stammdatenerweitert_stastszugehorigkeit"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
             
             "Stammdatenerweitert_familienstand" => array(                          //Familienstand
                 "form_id" => "16",
                 "form_name" => $extra_forms_arr['16']['formname'],
                 "form_models" => "Stammdatenerweitert",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "stammdatenerweitert",
                 //"master_lists" => array("Stammdatenerweitert_familienstand"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
             
             "Stammdatenerweitert_kunstliche" => array(                          //Künstliche Ausgänge
                 "form_id" => "22",
                 "form_name" => $extra_forms_arr['22']['formname'],
                 "form_models" => "Stammdatenerweitert",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "stammdatenerweitert",
                 //"master_lists" => array("Stammdatenerweitert_kunstliche"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
             
             "Stammdatenerweitert_ausscheidung" => array(                          //Ausscheidung
                 "form_id" => "21",
                 "form_name" => $extra_forms_arr['21']['formname'],
                 "form_models" => "Stammdatenerweitert",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "stammdatenerweitert",
                 //"master_lists" => array("Stammdatenerweitert_ausscheidung"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
             
             "Stammdatenerweitert_vigilanz" => array(                          //Vigilanz
                 "form_id" => "18",
                 "form_name" => $extra_forms_arr['18']['formname'],
                 "form_models" => "Stammdatenerweitert",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "mdat",
                 "db_table_name" => "stammdatenerweitert",
                 //"master_lists" => array("Stammdatenerweitert_vigilanz"),
                 "connected" => array(
                     "BoxHistory" => array(
                         "model" => "BoxHistory",
                         "name" => "mdat",
                         "table_name" => "box_history",
                     )
                 ),
                 "has_module_id" => ""
             ),
             /* 
              * his block, is contained in patient status
             "PatientReadmission" => array(                                  //Patient history == Fallhistorie// + 51 Fallhistorie II
                 "form_id" => "35",
                 "form_name" => $extra_forms_arr['35']['formname'],
                 "form_models" => "PatientReadmission",
                 "entry_db" => "single" ,
                 "settings_form" => "extraforms/formlist",
                 "db_name" => "idat",
                 "db_table_name" => "patient_readmission",
                 "master_lists" => array("PatientReadmission"),
                 "connected" => array(
                     "PatientMaster" => array(
                         "model" => "PatientMaster",
                         "name" => "idat",
                         "table_name" => "patient_master",
                     ),
                     "PatientDischarge" => array(
                         "model" => "PatientDischarge",
                         "name" => "idat",
                         "table_name" => "patient_discharge",
                     ),
                     "PatientStandbyDetails" => array(
                         "model" => "PatientStandbyDetails",
                         "name" => "idat",
                         "table_name" => "patient_standby_details",
                     ),
                     "PatientVisitnumber" => array(
                         "model" => "PatientVisitnumber",
                         "name" => "idat",
                         "table_name" => "patient_visitnumber",
                     ),
                     "VollversorgungHistory" => array(
                         "model" => "VollversorgungHistory",
                         "name" => "mdat",
                         "table_name" => "vollversorgung_history",
                     )
                 ),
                 "has_module_id" => array("147","184","225")
             ),
              */
             // 51 + 36  ???? idem ca 35 ???
             
         );
         
         return $lists_stammdaten;
     }
     
     
     public function intense_connection_options(){
         
         $options = array();
         
         $options =  array(
             'patient' =>  array(
                 'patient_falls'=>array(
                     'option_name' => 'Patient falls',
                     'master_lists' => array('DischargeMethod','DischargeLocation'),
                     'option_models' =>array('PatientMaster','PatientActive','PatientStandby','PatientStandbyDetails','PatientStandbyDelete','PatientStandbyDeleteDetails','PatientReadmission')
                 ),
                 
                 'PatientCrisisHistory'=>array(
                     'option_name' => "PatientCrisisHistory",
                     "option_models" => array('PatientCrisisHistory'),
                 ),
                 
                 
                 'PatientDiagnosis'=>array(
                     'option_name' => 'Patient diagnosis',
                     'master_lists' => array('Diagnosis'),
                     'option_tables' => array('PatientDiagnosis')
                 ),
                 'PatientLocation'=>array(
                     'option_name' => 'Patient locations',
                     'master_lists' => array('Locations'),
                     'option_tables' => array('PatientLocation')
                 ),
                 'PatientDrugPlan'=>array(
                     'option_name' => 'Patient medication',
                     'master_lists' => array('Medication'),
                     'option_tables' => array('PatientDrugplan')
                 ),
//                  'patient_files'=>array(
//                      'option_name' => 'Patient files',
//                  ),
                 'PatientHospizvizits'=>array(
                     'option_name' =>'Patient vw work',
                     'master_lists' => array('Voluntaryworkers'), // list for grund ??
                     'option_tables' =>array('PatientHospizvizits')
                 ),
             ),
             'patient_suppliers' => Pms_CommonData::connection_lists_versorgers(),
             'patient_details' => Pms_CommonData::connection_lists_stammdaten()
     );
         return $options;
     }
     
	//12.08.2020 Ancuta CSS HACK 
     public function remove_from_css_hack(){
         // contoller/ action

         $pages =  array(
             'roster/dayplanningnew',
			 'mambo/assessment',
             'patientformnew/munster4new'
         );
         
         return $pages;
     }
     

     //ISPC-2654 Lore 07.10.2020
     public function get_diagnosis_category_default(){
         
         $category = array(
             
             
             //TODO-4120 Ancuta 07.05.2021
            /*           
              array(
                 "category" => "1",   //"Hauptdiagnose (main diagnosis)",
                 "name" => "Hauptdiagnose",
                 "shortcut" => "H",
                 "color" => "#ffcc66",
                 "filter_column" => "main_category",
                 "db_name" => "main_diagnosis",
             ), 
             */
             array(
                 "category" => "2",   //"Grunderkrankungen ( primary disease)",
                 "name" => "Grunderkrankungen",
                 "shortcut" => "G",
                 "color" => "#996600",
                 "filter_column" => "main_category",
                 //TODO-4120 Ancuta 07.05.2021
                 //"db_name" => "primary_disease",
                 "db_name" => "main_diagnosis",
                 //--
             ),
             array(
                 "category" => "3",   //"Folgeerkrankung (secondary disease)",
                 "name" => "Folgeerkrankung",
                 "shortcut" => "F",
                 "color" => "#bf80ff",
                 "filter_column" => "main_category",
                 "db_name" => "secondary_disease",
             ),
             array(
                 "category" => "4",   //"Symptome (symptoms)",
                 "name" => "Symptome",
                 "shortcut" => "S",
                 "color" => "#00ffff",
                 "filter_column" => "symptoms",
                 "db_name" => "symptoms",
             ),
             array(
                 "category" => "5",   //"Archiv (archieved Diagnosis)",
                 "name" => "Archiv",
                 "shortcut" => "X",
                 "color" => "#737373",
                 "filter_column" => "archived",
                 "db_name" => "archived",
             ),             
             array(
                 "category" => "6",   //"Diagnose (side diagnosis)",
                 "name" => "Diagnose",
                 "shortcut" => "D",
                 "color" => "#660033",
                 "filter_column" => "side_diagnosis",
                 "db_name" => "side_diagnosis",
             ),             
             array(
                 "category" => "7",   //"Aktueller Aufenthalt (relevant for THIS hospital stay)",
                 "name" => "Aktueller Aufenthalt",
                 "shortcut" => "A",
                 "color" => "#8bb992",
                 "filter_column" => "relevant2hospitalstay",
                 "db_name" => "relevant2hospitalstay",
             ),
         );
         return $category;
     } 

     //ISPC-2654 Lore 07.10.2020
     public function get_sort_column_diagnosis_default(){
         
         $category = array(
             array(
                 "main_sort_col" => "1",//
                 "secondary_sort_col" => "5",
                 "sort_order" => "1",         //ASC
             ),
         );
         return $category;
     } 
     
     //Lore 19.10.2020
     /**
      *  //ISPC-2474 Ancuta 23.10.2020
      * @return string[][]|NULL[][]|array[][]|string[][][]
      */
     public function patient_related_models(){
         $data = array( 
             "Anlage14" => array(
                 "base" => "BaseAnlage14",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,      // null daca nu are
                 "has_softdelete" =>"0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage14Control" => array(
                 "base" => "BaseAnlage14Control",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage14Hospitals" => array(
                 "base" => "BaseAnlage14Hospitals",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage2" => array(
                 "base" => "BaseAnlage2",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,       // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage3" => array(
                 "base" => "BaseAnlage3",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,       // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage5CurrentProblems" => array(
                 "base" => "BaseAnlage5CurrentProblems",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage5Nie" => array(
                 "base" => "BaseAnlage5Nie",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,       // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage5Part1" => array(
                 "base" => "BaseAnlage5Part1",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage5Part2" => array(
                 "base" => "BaseAnlage5Part2",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage5Pretreatment" => array(
                 "base" => "BaseAnlage5Pretreatment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage6" => array(
                 "base" => "BaseAnlage6",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,      // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "Anlage6Extra" => array(
                 "base" => "BaseAnlage6Extra",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,      // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "AokprojectsCat" => array(
                 "base" => "BaseAokprojectsCat",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,      // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "AokprojectsCopd" => array(
                 "base" => "BaseAokprojectsCopd",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,      // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "AokprojectsHerzinsuffienz" => array(
                 "base" => "BaseAokprojectsHerzinsuffienz",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,      // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "AokprojectsKurzassessment" => array(
                 "base" => "BaseAokprojectsKurzassessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),         
             ),
             "AokprojectsTherapiesteuerung" => array(
                 "base" => "BaseAokprojectsTherapiesteuerung",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,      // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "AssessmentProblems" => array(
                 "base" => "BaseAssessmentProblems",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",      // null daca nu are
                 "has_softdelete" => "1",            // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "BayEmergencyPlan" => array(
                 "base" => "BaseBayEmergencyPlan",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",      // null daca nu are
                 "has_softdelete" => "0",           // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),             
             ),
             "BayernDoctorSymp" => array(
                 "base" => "BaseBayernDoctorSymp",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),         
             ),
             "BayernDoctorVisit" => array(
                 "base" => "BaseBayernDoctorVisit",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BayernInvoices" => array(
                 "base" => "BaseBayernInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BayernInvoicesNew" => array(
                 "base" => "BaseBayernInvoicesNew",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BoxHistory" => array(
                 "base" => "BaseBoxHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BraAnlage5" => array(
                 "base" => "BaseBraAnlage5",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BraAnlage5Products" => array(
                 "base" => "BaseBraAnlage5Products",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BraAnlage5Weeks" => array(
                 "base" => "BaseBraAnlage5Weeks",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BraInvoices" => array(
                 "base" => "BaseBraInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BraSapvControl" => array(
                 "base" => "BaseBraSapvControl",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BreHospizInvoices" => array(
                 "base" => "BaseBreHospizInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BreInvoices" => array(
                 "base" => "BaseBreInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BreKinderPerformance" => array(
                 "base" => "BaseBreKinderPerformance",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BreRecordingReport" => array(
                 "base" => "BaseBreRecordingReport",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BreSapvControl" => array(
                 "base" => "BaseBreSapvControl",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BwInvoices" => array(
                 "base" => "BaseBwInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BwInvoicesNew" => array(
                 "base" => "BaseBwInvoicesNew",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BwPerformanceRecord" => array(
                 "base" => "BaseBwPerformanceRecord",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "BwPerformanceRecordFlatrate" => array(
                 "base" => "BaseBwPerformanceRecordFlatrate",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ClientInvoices" => array(
                 "base" => "BaseClientInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isDelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ComplaintForm" => array(
                 "base" => "BaseComplaintForm",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ContactForms" => array(
                 "base" => "BaseContactForms",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('ContactForms2PatientCourseListener'),
             ),
             "ContactFormServiceEntry" => array(
                 "base" => "BaseContactFormsServiceEntry",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ContactFormsSymp" => array(
                 "base" => "BaseContactFormsSymp",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ContactPersonMaster" => array(
                 "base" => "BaseContactPersonMaster",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PatientContactPhoneListener', 'IntenseConnectionListener'),
             ),
             "DailyPlanningVisits" => array(
                 "base" => "BaseDailyPlanningVisits",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DailyPlanningVisits2" => array(
                 "base" => "BaseDailyPlanningVisits2",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DashboardEvents" => array(
                 "base" => "BaseDashboardEvents",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DemstepcareControl" => array(
                 "base" => "BaseDemstepcareControl",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DgpKern" => array(
                 "base" => "BaseDgpKern",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DgpPatientsHistory" => array(
                 "base" => "BaseDgpPatientsHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DgpSapv" => array(
                 "base" => "BaseDgpSapv",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DoctorCustomEvents" => array(
                 "base" => "BaseDoctorCustomEvents",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DoctorLetter" => array(
                 "base" => "BaseDoctorLetter",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DoctorLetterFaxvor" => array(
                 "base" => "BaseDoctorLetterFaxvor",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DoctorLetterTodes" => array(
                 "base" => "BaseDoctorLetterTodes",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "DoctorLetterZapv" => array(
                 "base" => "BaseDoctorLetterZapv",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "EmailLog" => array(
                 "base" => "BaseEmailLog",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "EmergencyPlan" => array(
                 "base" => "BaseEmergencyPlan",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "EmergencyPlanSapv" => array(
                 "base" => "BaseEmergencyPlanSapv",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "EmergencyPlanSapv24" => array(
                 "base" => "BaseEmergencyPlanSapv24",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             //ISPC-2736 Lore 12.11.2020
             "EmergencyPlanSapvII" => array(
                 "base" => "BaseEmergencyPlanSapvII",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "EntranceAssessment" => array(
                 "base" => "BaseEntranceAssessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
/*              "EpidIpidMapping" => array(
                 "base" => "BaseEpidIpidMapping",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ), */
             "EthicalForm" => array(
                 "base" => "BaseEthicalForm",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Extraopsleistung" => array(
                 "base" => "BaseExtraopsleistung",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FallprotocolForm" => array(
                 "base" => "BaseFallprotocolForm",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Feststellung" => array(
                 "base" => "BaseFeststellung",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FinalDocumentation" => array(
                 "base" => "BaseFinalDocumentation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FinalDocumentationLocation" => array(
                 "base" => "BaseFinalDocumentationLocation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockAdditionalUsers" => array(
                 "base" => "BaseFormBlockAdditionalUsers",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockAdverseevents" => array(
                 "base" => "BaseFormBlockAdverseevents",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockArtificialEntriesExits" => array(
                 "base" => "BaseFormBlockArtificialEntriesExits",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockAwakeSleepingStatus" => array(
                 "base" => "BaseFormBlockAwakeSleepingStatus",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockBefund" => array(
                 "base" => "BaseFormBlockBefund",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockBowelMovement" => array(
                 "base" => "BaseFormBlockBowelMovement",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockBraSapv" => array(
                 "base" => "BaseFormBlockBraSapv",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockClassification" => array(
                 "base" => "BaseFormBlockClassification",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockClientSymptoms" => array(
                 "base" => "BaseFormBlockClientSymptoms",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockCoordinatorActions" => array(
                 "base" => "BaseFormBlockCoordinatorActions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockCustom" => array(
                 "base" => "BaseFormBlockCustom",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockCustomEvent" => array(
                 "base" => "BaseFormBlockCustomEvent",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockDelegation" => array(
                 "base" => "BaseFormBlockDelegation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockDrivetimedoc" => array(
                 "base" => "BaseFormBlockDrivetimedoc",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockEbm" => array(
                 "base" => "BaseFormBlockEbm",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockEbmBer" => array(
                 "base" => "BaseFormBlockEbmBer",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockEbmi" => array(
                 "base" => "BaseFormBlockEbmi",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockEbmii" => array(
                 "base" => "BaseFormBlockEbmii",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockGoa" => array(
                 "base" => "BaseFormBlockGoa",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockGoai" => array(
                 "base" => "BaseFormBlockGoai",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockGoaii" => array(
                 "base" => "BaseFormBlockGoaii",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockHospizimex" => array(
                 "base" => "BaseFormBlockHospizimex",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockHospizmedi" => array(
                 "base" => "BaseFormBlockHospizmedi",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockInfusion" => array(
                 "base" => "BaseFormBlockInfusion",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockInfusiontimes" => array(
                 "base" => "BaseFormBlockInfusiontimes",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockIpos" => array(
                 "base" => "BaseFormBlockIpos",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockKeyValue" => array(
                 "base" => "BaseFormBlockKeyValue",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockLmuVisit" => array(
                 "base" => "BaseFormBlockLmuVisit",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockMeasures" => array(
                 "base" => "BaseFormBlockMeasures",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockOrganicEntriesExits" => array(
                 "base" => "BaseFormBlockOrganicEntriesExits",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockPcoc" => array(
                 "base" => "BaseFormBlockPcoc",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockPositioning" => array(
                 "base" => "BaseFormBlockPositioning",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockPuncture" => array(
                 "base" => "BaseFormBlockPuncture",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockResources" => array(
                 "base" => "BaseFormBlockResources",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockSgbv" => array(
                 "base" => "BaseFormBlockSgbv",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockSgbxiActions" => array(
                 "base" => "BaseFormBlockSgbxiActions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockSingleValue" => array(
                 "base" => "BaseFormBlockSingleValue",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockSuckoff" => array(
                 "base" => "BaseFormBlockSuckoff",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockTimeDivision" => array(
                 "base" => "BaseFormBlockTimeDivision",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockTimedocumentationClinic" => array(
                 "base" => "BaseFormBlockTimedocumentationClinic",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockTodos" => array(
                 "base" => "BaseFormBlockTodos",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockTracheostomy" => array(
                 "base" => "BaseFormBlockTracheostomy",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockTreatmentPlan" => array(
                 "base" => "BaseFormBlockTreatmentPlan",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockVentilation" => array(
                 "base" => "BaseFormBlockVentilation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockVisitClasification" => array(
                 "base" => "BaseFormBlockVisitClasification",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockVisitType" => array(
                 "base" => "BaseFormBlockVisitType",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockVitalSigns" => array(
                 "base" => "BaseFormBlockVitalSigns",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "FormBlockXbdtEbmii" => array(
                 "base" => "BaseFormBlockXbdtEbmii",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormBlockXbdtGoaii" => array(
                 "base" => "BaseFormBlockXbdtGoaii",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormGenericSimpleForm" => array(
                 "base" => "BaseFormGenericSimpleForm",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "FormLock" => array(
                 "base" => "BaseFormLock",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Formone" => array(
                 "base" => "BaseFormone",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Genehmigungs" => array(
                 "base" => "BaseGenehmigungs",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "GroupPatientShortcuts" => array(
                 "base" => "BaseGroupPatientShortcuts",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "HeInvoices" => array(
                 "base" => "BaseHeInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "HiInvoices" => array(
                 "base" => "BaseHiInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Hl7DocSend" => array(
                 "base" => "BaseHl7DocSend",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Hl7MessagesProcessed" => array(
                 "base" => "BaseHl7MessagesProcessed",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Hl7MessagesSent" => array(
                 "base" => "BaseHl7MessagesSent",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "HospizControl" => array(
                 "base" => "BaseHospizControl",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "HospizInvoices" => array(
                 "base" => "BaseHospizInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "HospizQuestionnaire" => array(
                 "base" => "BaseHospizQuestionnaire",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "IconsPatient" => array(
                 "base" => "BaseIconsPatient",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "IntenseConnectionsLog" => array(
                 "base" => "BaseIntenseConnectionsLog",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "InternalInvoices" => array(
                 "base" => "BaseInternalInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Interventions" => array(
                 "base" => "BaseInterventions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "InvoiceSystem" => array(
                 "base" => "BaseInvoiceSystem",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "KinderEntranceAssessment" => array(
                 "base" => "BaseKinderEntranceAssessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "KinderEntranceAssessmentSorrowfully" => array(
                 "base" => "BaseKinderEntranceAssessmentSorrowfully",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "KinderSapvHospiz" => array(
                 "base" => "BaseKinderSapvHospiz",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "KvnoAnlage7" => array(
                 "base" => "BaseKvnoAnlage7",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "KvnoAssessment" => array(
                 "base" => "BaseKvnoAssessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "KvnoDoctor" => array(
                 "base" => "BaseKvnoDoctor",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "KvnoDoctorSymp" => array(
                 "base" => "BaseKvnoDoctorSymp",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "KvnoNurse" => array(
                 "base" => "BaseKvnoNurse",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "KvnoNurseSymp" => array(
                 "base" => "BaseKvnoNurseSymp",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "LmuPatientSpecialAttributes" => array(
                 "base" => "BaseLmuPatientSpecialAttributes",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MamboAssessment" => array(
                 "base" => "BaseMamboAssessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "MdkSapvQuestionnaire" => array(
                 "base" => "BaseMdkSapvQuestionnaire",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MdkSchne" => array(
                 "base" => "BaseMdkSchne",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MedicationClientHistory" => array(
                 "base" => "BaseMedicationClientHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MedicationClientStock" => array(
                 "base" => "BaseMedicationClientStock",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MedicationPatientHistory" => array(
                 "base" => "BaseMedicationPatientHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MedipumpsInvoices" => array(
                 "base" => "BaseMedipumpsInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MedipumpsInvoicesNew" => array(
                 "base" => "BaseMedipumpsInvoicesNew",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MePatientDevices" => array(
                 "base" => "BaseMePatientDevices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MePatientDevicesNotifications" => array(
                 "base" => "BaseMePatientDevicesNotifications",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MePatientDevicesSurveys" => array(
                 "base" => "BaseMePatientDevicesSurveys",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MePatientNotificationsHistory" => array(
                 "base" => "BaseMePatientNotificationsHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "MessageCoordinator" => array(
                 "base" => "BaseMessageCoordinator",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Messages" => array(
                 "base" => "BaseMessages",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Munster" => array(
                 "base" => "BaseMunster",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Munster1a" => array(
                 "base" => "BaseMunster1a",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Munster4" => array(
                 "base" => "BaseMunster4",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Munster63kinder" => array(
                 "base" => "BaseMunster63kinder",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Muster13" => array(
                 "base" => "BaseMuster13",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Muster13Log" => array(
                 "base" => "BaseMuster13Log",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Muster2b" => array(
                 "base" => "BaseMuster2b",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "NieRecordingReport" => array(
                 "base" => "BaseNieRecordingReport",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "NordrheinBilling" => array(
                 "base" => "BaseNordrheinBilling",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "NutritionFormular" => array(
                 "base" => "BaseNutritionFormular",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "OrganicEntriesExitsSets" => array(
                 "base" => "BaseOrganicEntriesExitsSets",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PainQuestionnaire" => array(
                 "base" => "BasePainQuestionnaire",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PalliativeEmergency" => array(
                 "base" => "BasePalliativeEmergency",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientAcp" => array(
                 "base" => "BasePatientAcp",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete()); 
                 "listners" => array(),         
             ),
             "PatientActive" => array(
                 "base" => "BasePatientActive",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionAdmissionsListener'),
             ),
             "PatientAnlage33a" => array(
                 "base" => "BasePatientAnlage33a",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientApprovedVisitTypes" => array(
                 "base" => "BasePatientApprovedVisitTypes",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientArtificialEntriesExits" => array(
                 "base" => "BasePatientArtificialEntriesExits",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientBarthel" => array(
                 "base" => "BasePatientBarthel",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientBesd" => array(
                 "base" => "BasePatientBesd",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientCareInsurance" => array(
                 "base" => "BasePatientCareInsurance",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientCareservices" => array(
                 "base" => "BasePatientCareservices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientCaseStatus" => array(
                 "base" => "BasePatientCaseStatus",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientCaseStatusLog" => array(
                 "base" => "BasePatientCaseStatusLog",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientChildMourning" => array(
                 "base" => "BasePatientChildMourning",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientChurches" => array(
                 "base" => "BasePatientChurches",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientClinicBed" => array(
                 "base" => "BasePatientClinicBed",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientCloseContact" => array(
                 "base" => "BasePatientCloseContact",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientContactphone" => array(
                 "base" => "BasePatientContactphone",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PatientContactphoneHydrateListener', 'HidemagicListener'),
             ),
             "PatientCourse" => array(
                 "base" => "BasePatientCourse",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('ContactForms2PatientCourseListener'),
             ),
             "PatientCrisisHistory" => array(
                 "base" => "BasePatientCrisisHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientCurrentProblems" => array(
                 "base" => "BasePatientCurrentProblems",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientCustomActions" => array(
                 "base" => "BasePatientCustomActions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDatamatrixImport" => array(
                 "base" => "BasePatientDatamatrixImport",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDayStructure" => array(
                 "base" => "BasePatientDayStructure",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDayStructureActions" => array(
                 "base" => "BasePatientDayStructureActions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDeath" => array(
                 "base" => "BasePatientDeath",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDeathwish" => array(
                 "base" => "BasePatientDeathwish",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDemstepcare" => array(
                 "base" => "BasePatientDemstepcare",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDiagnoOrder" => array(
                 "base" => "BasePatientDiagnoOrder",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDiagnosis" => array(
                 "base" => "BasePatientDiagnosis",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseDiagnosisConnectionListener'),
             ),
             "PatientDiagnosisAct" => array(
                 "base" => "BasePatientDiagnosisAct",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",         // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDiagnosisClinical" => array(
                 "base" => "BasePatientDiagnosisClinical",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,      // null daca nu are
                 "has_softdelete" =>"0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ), 
             "PatientDiagnosisMeta" => array(
                 "base" => "BasePatientDiagnosisMeta",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDiagnosisObserved" => array(
                 "base" => "BasePatientDiagnosisObserved",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDisabilityDegree" => array(
                 "base" => "BasePatientDisabilityDegree",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDischarge" => array(
                 "base" => "BasePatientDischarge",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionAdmissionsListener'),
             ),
             "PatientDischargePlanning" => array(
                 "base" => "BasePatientDischargePlanning",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDischargePlanningAid" => array(
                 "base" => "BasePatientDischargePlanningAid",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDivergentAttitude" => array(
                 "base" => "BasePatientDivergentAttitude",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlan" => array(
                 "base" => "BasePatientDrugPlan",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseMedicationConnectionListener'),
             ),
             "PatientDrugPlanAllergies" => array(
                 "base" => "BasePatientDrugPlanAllergies",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanAlt" => array(
                 "base" => "BasePatientDrugPlanAlt",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanAltCocktails" => array(
                 "base" => "BasePatientDrugPlanAltCocktails",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanAtc" => array(
                 "base" => "BasePatientDrugPlanAtc",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanCocktails" => array(
                 "base" => "BasePatientDrugPlanCocktails",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanDosage" => array(
                 "base" => "BasePatientDrugPlanDosage",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanDosageAlt" => array(
                 "base" => "BasePatientDrugPlanDosageAlt",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanDosageGiven" => array(
                 "base" => "BasePatientDrugPlanDosageGiven",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanDosageHistory" => array(
                 "base" => "BasePatientDrugPlanDosageHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanDosageIntervals" => array(
                 "base" => "BasePatientDrugPlanDosageIntervals",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanExtra" => array(
                 "base" => "BasePatientDrugPlanExtra",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanExtraAlt" => array(
                 "base" => "BasePatientDrugPlanExtraAlt",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanExtraHistory" => array(
                 "base" => "BasePatientDrugPlanExtraHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanHistory" => array(
                 "base" => "BasePatientDrugPlanHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugPlanShare" => array(
                 "base" => "BasePatientDrugPlanShare",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientDrugplanTransition" => array(
                 "base" => "BasePatientDrugplanTransition",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientEmploymentSituation" => array(
                 "base" => "BasePatientEmploymentSituation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientEvn" => array(
                 "base" => "BasePatientEvn",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientExpectedSymptoms" => array(
                 "base" => "BasePatientExpectedSymptoms",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientFeedbackCareAids" => array(
                 "base" => "BasePatientFeedbackCareAids",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientFeedbackGeneral" => array(
                 "base" => "BasePatientFeedbackGeneral",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientFeedbackMedication" => array(
                 "base" => "BasePatientFeedbackMedication",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientFeedbackVitalSigns" => array(
                 "base" => "BasePatientFeedbackVitalSigns",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
//              "PatientFileUpload" => array(
//                  "base" => "BasePatientFileUpload",
//                  "patient_ident"  => "ipid",
//                  "delete_column"  => "isdeleted",  // null daca nu are
//                  "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
//                  "listners" => array('FtpPutQueue2RecordListener'),
//              ),
             "PatientGeneralPractitionerInitial" => array(
                 "base" => "BasePatientGeneralPractitionerInitial",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientGermination" => array(
                 "base" => "BasePatientGermination",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientGroupPermissions" => array(
                 "base" => "BasePatientGroupPermissions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientGroups" => array(
                 "base" => "BasePatientGroups",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientHandicappedCard" => array(
                 "base" => "BasePatientHandicappedCard",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientHealthInsurance" => array(
                 "base" => "BasePatientHealthInsurance",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientHealthInsurance2Subdivisions" => array(
                 "base" => "BasePatientHealthInsurance2Subdivisions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
/*              "PatientHealthInsuranceHistory" => array(
                 "base" => "BasePatientHealthInsuranceHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ), */
             "PatientHistoryLog" => array(
                 "base" => "BasePatientHistoryLog",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientHomecare" => array(
                 "base" => "BasePatientHomecare",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientHospiceassociation" => array(
                 "base" => "BasePatientHospiceassociation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientHospiceCertification" => array(
                 "base" => "BasePatientHospiceCertification",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientHospizCourse" => array(
                 "base" => "BasePatientHospizCourse",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientHospizverein" => array(
                 "base" => "BasePatientHospizverein",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientHospizvizits" => array(
                 "base" => "BasePatientHospizvizits",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientKarnofsky" => array(
                 "base" => "BasePatientKarnofsky",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientLives" => array(
                 "base" => "BasePatientLives",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientLivesV2" => array(
                 "base" => "BasePatientLivesV2",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientLocation" => array(
                 "base" => "BasePatientLocation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PatientContactPhoneListener', 'IntenseConnectionListener'),
             ),
             "PatientMaintainanceStage" => array(
                 "base" => "BasePatientMaintainanceStage",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientMaster" => array(
                 "base" => "BasePatientMaster",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PatientContactPhoneListener'),
             ),
             "PatientMedipumps" => array(
                 "base" => "BasePatientMedipumps",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientMedipumpsControl" => array(
                 "base" => "BasePatientMedipumpsControl",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientMemo" => array(
                 "base" => "BasePatientMemo",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientMigration" => array(
                 "base" => "BasePatientMigration",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientMobility" => array(
                 "base" => "BasePatientMobility",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientMobility2" => array(
                 "base" => "BasePatientMobility2",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientMoreInfo" => array(
                 "base" => "BasePatientMoreInfo",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientMovementnumber" => array(
                 "base" => "BasePatientMovementnumber",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientMre" => array(
                 "base" => "BasePatientMre",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,       // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostUpdateWriteToPatientCourseListener', 'PostInsertWriteToPatientCourseListener'),
             ),
/*              "PatientMunster4" => array(
                 "base" => "BasePatientMunster4",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ), */
             "PatientNextContactBy" => array(
                 "base" => "BasePatientNextContactBy",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientNraapv" => array(
                 "base" => "BasePatientNraapv",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientNutritionalStatus" => array(
                 "base" => "BasePatientNutritionalStatus",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientOps" => array(
                 "base" => "BasePatientOps",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientOrientation" => array(
                 "base" => "BasePatientOrientation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientPermissions" => array(
                 "base" => "BasePatientPermissions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientPflegedienste" => array(
                 "base" => "BasePatientPflegedienste",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientPharmacy" => array(
                 "base" => "BasePatientPharmacy",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientPhysiotherapist" => array(
                 "base" => "BasePatientPhysiotherapist",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientPort" => array(
                 "base" => "BasePatientPort",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientPsychooncological" => array(
                 "base" => "BasePatientPsychooncological",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientQpaLeading" => array(
                 "base" => "BasePatientQpaLeading",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientRass" => array(
                 "base" => "BasePatientRass",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientReadmission" => array(
                 "base" => "BasePatientReadmission",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionAdmissionsListener'),
             ),
             "PatientReadmissionDetails" => array(
                 "base" => "BasePatientReadmissionDetails",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientRegularChecks" => array(
                 "base" => "BasePatientRegularChecks",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientReligions" => array(
                 "base" => "BasePatientReligions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientRemedies" => array(
                 "base" => "BasePatientRemedies",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientRubin" => array(
                 "base" => "BasePatientRubin",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientRubinForms" => array(
                 "base" => "BasePatientRubinForms",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientRubinFormsAnswers" => array(
                 "base" => "BasePatientRubinFormsAnswers",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientSavoir" => array(
                 "base" => "BasePatientSavoir",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientSavoirSapv" => array(
                 "base" => "BasePatientSavoirSapv",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientsMarked" => array(
                 "base" => "BasePatientsMarked",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientsOrdersAllowed" => array(
                 "base" => "BasePatientsOrdersAllowed",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientsOrdersDetails" => array(
                 "base" => "BasePatientsOrdersDetails",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('HistoryListener'),
             ),
             "PatientsOrdersDetailsHistory" => array(
                 "base" => "BasePatientsOrdersDetailsHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientsOrdersPeriods" => array(
                 "base" => "BasePatientsOrdersPeriods",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientSpecialists" => array(
                 "base" => "BasePatientSpecialists",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientSpiritualAttitude" => array(
                 "base" => "BasePatientSpiritualAttitude",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
//              "PatientsShareLog" => array(
//                  "base" => "BasePatientsShareLog",
//                  "patient_ident"  => "ipid",
//                  "delete_column"  => NULL,  // null daca nu are
//                  "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
//                  "listners" => array(),
//              ),
             "PatientStammblattsapv" => array(
                 "base" => "BasePatientStammblattsapv",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientStandby" => array(
                 "base" => "BasePatientStandby",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionAdmissionsListener'),
             ),
             "PatientStandbyDelete" => array(
                 "base" => "BasePatientStandbyDelete",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientStandbyDeleteDetails" => array(
                 "base" => "BasePatientStandbyDeleteDetails",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionAdmissionsListener'),
             ),
             "PatientStandbyDetails" => array(
                 "base" => "BasePatientStandbyDetails",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionAdmissionsListener'),
             ),
             "PatientSteps" => array(
                 "base" => "BasePatientSteps",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientStressValues" => array(
                 "base" => "BasePatientStressValues",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientSuppliers" => array(
                 "base" => "BasePatientSuppliers",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientSupplies" => array(
                 "base" => "BasePatientSupplies",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientSupply" => array(
                 "base" => "BasePatientSupply",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientSurveySettings" => array(
                 "base" => "BasePatientSurveySettings",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientSync" => array(
                 "base" => "BasePatientSync",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientTherapieplanung" => array(
                 "base" => "BasePatientTherapieplanung",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientTreatmentPlan" => array(
                 "base" => "BasePatientTreatmentPlan",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "PatientUsers" => array(
                 "base" => "BasePatientUsers",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientVersorger" => array(
                 "base" => "BasePatientVersorger",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientVices" => array(
                 "base" => "BasePatientVices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientVisitnumber" => array(
                 "base" => "BasePatientVisitnumber",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientVisitsSettings" => array(
                 "base" => "BasePatientVisitsSettings",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdeleted",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientVoluntaryworkers" => array(
                 "base" => "BasePatientVoluntaryworkers",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "PatientWhitebox" => array(
                 "base" => "BasePatientWhitebox",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdeleted",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientXbdtActions" => array(
                 "base" => "BasePatientXbdtActions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PharmaPatientDrugplan" => array(
                 "base" => "BasePharmaPatientDrugplan",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PharmaPatientDrugplanDosage" => array(
                 "base" => "BasePharmaPatientDrugplanDosage",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PharmaPatientDrugplanExtra" => array(
                 "base" => "BasePharmaPatientDrugplanExtra",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PharmaPatientDrugplanRequests" => array(
                 "base" => "BasePharmaPatientDrugplanRequests",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PharmaPatientRequests" => array(
                 "base" => "BasePharmaPatientRequests",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PharmaRequestsProcessed" => array(
                 "base" => "BasePharmaRequestsProcessed",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PharmaRequestsReceived" => array(
                 "base" => "BasePharmaRequestsReceived",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PpunIpid" => array(
                 "base" => "BasePpunIpid",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "QuestionnaireB" => array(
                 "base" => "BaseQuestionnaireB",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "QuestionnaireC" => array(
                 "base" => "BaseQuestionnaireC",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Reactions" => array(
                 "base" => "BaseReactions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Reassessment" => array(
                 "base" => "BaseReassessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ReceiptLog" => array(
                 "base" => "BaseReceiptLog",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Receipts" => array(
                 "base" => "BaseReceipts",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "RecordingAssessment" => array(
                 "base" => "BaseRecordingAssessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "RlpControl" => array(
                 "base" => "BaseRlpControl",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "RlpInvoices" => array(
                 "base" => "BaseRlpInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Rpassessment" => array(
                 "base" => "BaseRpassessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "RpControl" => array(
                 "base" => "BaseRpControl",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "RpInvoices" => array(
                 "base" => "BaseRpInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "RpTermination" => array(
                 "base" => "BaseRpTermination",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Ruhen" => array(
                 "base" => "BaseRuhen",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SaeReactions" => array(
                 "base" => "BaseSaeReactions",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapAnfrage" => array(
                 "base" => "BaseSapAnfrage",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
/*              "SapfiveDetails" => array(          // nu gasesc tabela in db
                 "base" => "BaseSapfiveDetails",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ), */
             "SapfiveImagetags" => array(
                 "base" => "BaseSapfiveImagetags",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Sapsymptom" => array(
                 "base" => "BaseSapsymptom",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapvEvaluation" => array(
                 "base" => "BaseSapvEvaluation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapvEvaluationIpos1" => array(
                 "base" => "BaseSapvEvaluationIpos1",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapvEvaluationIpos2" => array(
                 "base" => "BaseSapvEvaluationIpos2",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapvEvaluationMsp1" => array(
                 "base" => "BaseSapvEvaluationMsp1",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapvEvaluationMsp2" => array(
                 "base" => "BaseSapvEvaluationMsp2",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapvExportHistory" => array(
                 "base" => "BaseSapvExportHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapvQuestionnaire" => array(
                 "base" => "BaseSapvQuestionnaire",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapvReevaluation" => array(
                 "base" => "BaseSapvReevaluation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdeleted",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SapvVerordnung" => array(
                 "base" => "BaseSapvVerordnung",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "SgbvForms" => array(
                 "base" => "BaseSgbvForms",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SgbvFormsHistory" => array(
                 "base" => "BaseSgbvFormsHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SgbvFormsItems" => array(
                 "base" => "BaseSgbvFormsItems",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SgbvFormsSignaturePdf" => array(
                 "base" => "BaseSgbvFormsSignaturePdf",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SgbvInvoices" => array(
                 "base" => "BaseSgbvInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SgbxiFormsSignaturePdf" => array(
                 "base" => "BaseSgbxiFormsSignaturePdf",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SgbxiInvoices" => array(
                 "base" => "BaseSgbxiInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ShInvoices" => array(
                 "base" => "BaseShInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ShSapvQuestionnaire" => array(
                 "base" => "BaseShSapvQuestionnaire",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             
             "ShShiftsInternalInvoices" => array(
                 "base" => "BaseShShiftsInternalInvoice",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SisAmbulant" => array(
                 "base" => "BaseSisAmbulant",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SisAmbulantThematics" => array(
                 "base" => "BaseSisAmbulantThematics",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SisStationary" => array(
                 "base" => "BaseSisStationary",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SisStationaryThematics" => array(
                 "base" => "BaseSisStationaryThematics",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Stammblatt" => array(
                 "base" => "BaseStammblatt",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Stammblatt4" => array(
                 "base" => "BaseStammblatt4",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Stammblatt5" => array(
                 "base" => "BaseStammblatt5",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Stammblatt7" => array(
                 "base" => "BaseStammblatt7",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Stammblattlmu" => array(
                 "base" => "BaseStammblattlmu",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Stammblattsapv" => array(
                 "base" => "BaseStammblattsapv",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Stammdatenerweitert" => array(
                 "base" => "BaseStammdatenerweitert",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('IntenseConnectionListener'),
             ),
             "StandardDocumentation" => array(
                 "base" => "BaseStandardDocumentation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SurveyPatient2chain" => array(
                 "base" => "BaseSurveyPatient2chain",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Symptomatology" => array(
                 "base" => "BaseSymptomatology",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SystemsSyncPackets" => array(
                 "base" => "BaseSystemsSyncPackets",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "TerminalExtra" => array(
                 "base" => "BaseTerminalExtra",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Therapyplan" => array(
                 "base" => "BaseTherapyplan",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "TherapyplanItems" => array(
                 "base" => "BaseTherapyplanItems",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ToDos" => array(
                 "base" => "BaseToDos",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "TodosReminderLog" => array(
                 "base" => "BaseTodosReminderLog",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "User2admission" => array(
                 "base" => "BaseUser2admission",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "UserChartsNavigation" => array(
                 "base" => "BaseUserChartsNavigation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "UserInvoices" => array(
                 "base" => "BaseUserInvoices",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "UserPatientShortcuts" => array(
                 "base" => "BaseUserPatientShortcuts",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "UserSettingsMediSort" => array(
                 "base" => "BaseUserSettingsMediSort",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Usershifts2patients" => array(
                 "base" => "BaseUsershifts2patients",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "UserTableSorting" => array(
                 "base" => "BaseUserTableSorting",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "VacationsReplacements" => array(
                 "base" => "BaseVacationsReplacements",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "VisitKoordination" => array(
                 "base" => "BaseVisitKoordination",
                 "patient_ident"  => "ipid",
                 "delete_column"  => NULL,  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "VollversorgungHistory" => array(
                 "base" => "BaseVollversorgungHistory",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "Wallnews" => array(
                 "base" => "BaseWallnews",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "WeeklyMeeting" => array(
                 "base" => "BaseWeeklyMeeting",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "WlAnlage7" => array(
                 "base" => "BaseWlAnlage7",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "WlAnlage7HospitalStays" => array(
                 "base" => "BaseWlAnlage7HospitalStays",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "WlAssessment" => array(
                 "base" => "BaseWlAssessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array('PostInsertWriteToPatientCourseListener'),
             ),
             "WoundDocumentation" => array(
                 "base" => "BaseWoundDocumentation",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ZapvAssessment" => array(
                 "base" => "BaseZapvAssessment",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ZapvAssessmentII" => array(
                 "base" => "BaseZapvAssessmentII",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "ZapvAssessmentIISymp" => array(
                 "base" => "BaseZapvAssessmentIISymp",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",  // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             //------------- epid -----------------//
             "PatientCase" => array(
                 "base" => "BasePatientCase",
                 "patient_ident"  => "epid",
                 "delete_column"  => NULL,       // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientQpaMapping" => array(
                 "base" => "BasePatientQpaMapping",
                 "patient_ident"  => "epid",
                 "delete_column"  => NULL,       // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "SystemsSyncHospital" => array(
                 "base" => "BaseSystemsSyncHospital",
                 "patient_ident"  => "epid",
                 "delete_column"  => NULL,        // null daca nu are
                 "has_softdelete" => "0",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             
             "PatientAmbulantChildrenHospiceService" => array(
                 "base" => "BasePatientAmbulantChildrenHospiceService",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientChildrensHospice" => array(
                 "base" => "BasePatientChildrensHospice",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientFamilySupportService" => array(
                 "base" => "BasePatientFamilySupportService",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientIntegrationAssistance" => array(
                 "base" => "BasePatientIntegrationAssistance",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientKindergarten" => array(
                 "base" => "BasePatientKindergarten",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientOtherSuppliers" => array(
                 "base" => "BasePatientOtherSuppliers",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientPlaygroup" => array(
                 "base" => "BasePatientPlaygroup",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientSapvTeam" => array(
                 "base" => "BasePatientSapvTeam",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientSchool" => array(
                 "base" => "BasePatientSchool",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientWorkshopDisabledPeople" => array(
                 "base" => "BasePatientWorkshopDisabledPeople",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             "PatientYouthWelfareOffice" => array(
                 "base" => "BasePatientYouthWelfareOffice",
                 "patient_ident"  => "ipid",
                 "delete_column"  => "isdelete",        // null daca nu are
                 "has_softdelete" => "1",        // 0 sau 1 daca are  Softdelete());
                 "listners" => array(),
             ),
             
        );
             
         return $data;
     }
     
     //ISPC-2748 Lore 16.11.2020
     // ISPC-2312 Ancuta
     public function clients_invoices_details(){
         $Tr = new Zend_View_Helper_Translate();
         //$client_details = new Client();
       
         $data = array( 
             "nie_patient_invoice" => array(
                 "name"   => "Rechnungen ND - Patient Liste",
                 "models"   =>array(
                     'invoice'=>"HiInvoices",
                     'items'=>"HiInvoiceItems",
                     'payment'=>"HiInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_HiInvoices",
                 ),
                 'links'=>array(
                     'edit' =>'invoice/edithiinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoice/healthinsuranceinvoices?invoiceid=[inv_id]&pdfquick=1',
                     'print_storno' =>'invoice/healthinsuranceinvoices?invoiceid=[inv_id]&storno=[storno_inv_id]&pdfquick=1&stornopdf=1',
                 ),
                 "bulk_print" =>'0',
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/healthinsuranceinvoices",
                 "generation_page" => "invoice/newinvoicepatientlist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",                                    //ISPC-2747 Lore 24.11.2020
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("admission","daily","visits"),
                 "pricelist_title" => $Tr->translate('Price List ND'),                          //"Niedersachsen",
                 "price_model"    => array("PriceAdmission","PriceDaily","PriceVisits"),
             ),
             "nie_user_invoice" => array(
                 "name"   => "Rechnungen ND - Benutzer",
                 "models"   =>array(
                     'invoice'=>"UserInvoices",
                     'items'=>"UserInvoiceItems",
                     'payment'=>"UserInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_UserInvoices",
                 ),
                 
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/clientusersinvoices",
                 "generation_page" => "invoice/healthinsuranceinvoices",
                 "has_template"    => "yes",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("admission","daily","visits"),
                 "pricelist_title" => $Tr->translate('Price List ND'),                          //"Niedersachsen",
                 "price_model"    => array("PriceAdmission","PriceDaily","PriceVisits"),
             ),
             "bw_sapv_invoice" => array(
                 "name"   => "Rechnung BW - SAPV Rechnungen",
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/bwinvoices",
                 "generation_page" => "invoice/sapvinvoicepatientlist",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("performance","performancebylocation"),
                 "pricelist_title" => "Price List BW II",
                 "price_model"    => array("PricePerformance","PricePerformance"),
             ),
             
             "bw_sgbv_invoice" => array(
                 "name"   => "Rechnung BW - SGB V Rechnungen",
                 "models"   =>array(
                     'invoice'=>"SgbvInvoices",
                     'items'=>"SgbvInvoiceItems",
                     'payment'=>"SgbvInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_SgbvInvoices",
                 ),
                 'links'=>array(
                     'edit' =>'invoice/editsgbvinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoice/socialcoderecord?iid=[inv_id]&mode=pdfs&pdfquick=1',
                     'print_storno' =>'invoice/socialcoderecord?iid=[inv_id]&storno=[storno_inv_id]&mode=pdfs&pdfquick=1&stornopdf=1',
                 ),
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/sgbvinvoices",
                 "generation_page" => "invoice/sapvinvoicepatientlist",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("performance","performancebylocation"),
                 "pricelist_title" => "Price List BW II",
                 "price_model"    => array("PricePerformance","PricePerformance"),
             ),
             
             "bw_sapv_invoice_new" => array(
                 "name"   => "BW SAPV Rechnung (2)",
                 "models"   =>array(
                     'invoice'=>"BwInvoicesNew",
                     'items'=>"BwInvoiceItemsNew",
                     'payment'=>"BwInvoicePaymentsNew",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_BwInvoicesNew",
                 ),
                 'links'=>array(
                     'edit' =>'invoicenew/editbwsapvinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoicenew/bwsapvinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&type=pdf',
                     'print_storno' =>'invoicenew/bwsapvinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&stornopdf=1&stornoid=[storno_inv_id]&type=pdf',
                 ),
                 "billing_methods" => array(),
                 "page_invoices_listed" => "invoicenew/invoicesnew",
                 "generation_page" => "invoicenew/newinvoicepatientlist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("performance","performancebylocation"), 
                 "pricelist_title" => "Price List BW II",
                 "price_model"    => array("PricePerformance","PricePerformance"),
             ),
             "bw_mp_invoice" => array(
                 "name"   => "Rechnungen BW -  Medikamenten Pumpen",
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/medipumpsinvoices",
                 "generation_page" => "invoice/sapvinvoicepatientlist",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("medipumps"),
                 "pricelist_title" => $Tr->translate('Price List BW MEDIPUMPS'),                                //"Medikamenten Pumpen",
                 "price_model"    => array("PriceMedipumps"),
             ),  
             
             "bw_medipumps_invoice" => array(
                 "name"   => "BW Medipumps Rechnung",
                 "models"   =>array(
                     'invoice'=>"MedipumpsInvoicesNew",
                     'items'=>"MedipumpsInvoiceItemsNew",
                     'payment'=>"MedipumpsInvoicePaymentsNew",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_MedipumpsInvoicesNew",
                 ),
                 'links'=>array(
                     'edit' =>'invoicenew/editmedipumpsinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoicenew/bwmedipumpsinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&type=pdf',
                     'print_storno' =>'invoicenew/bwmedipumpsinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&stornopdf=1&stornoid=[storno_inv_id]&type=pdf',
                 ),
                 "billing_methods" => array(),
                 "page_invoices_listed" => "invoicenew/invoicesnew",
                 "generation_page" => "invoicenew/newinvoicepatientlist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("medipumps"),
                 "pricelist_title" => $Tr->translate('Price List BW MEDIPUMPS'),                                //"Medikamenten Pumpen",
                 "price_model"    => array("PriceMedipumps"),
             ),
             "bra_invoice" => array(
                 "name"   => "Rechnungen BRA",
                 "billing_methods" => array(),
                 "page_invoices_listed" => "invoicenew/brainvoices",
                 "generation_page" => "patientformnew/treatmentweeks",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("bra_sapv","bra_sapv_weg"),                 
                 "pricelist_title" => $Tr->translate('Price List BRA'),                                 //"Brandenburg",
                 "price_model"    => array("PriceBraSapv","PriceBraSapvWeg"),
             ), 
             
             "bre_sapv_invoice" => array(
                 "name"   => "Rechnungen BRE - SAPV Rechnungen",
                 "models"   =>array(
                     'invoice'=>"BreInvoices",
                     'items'=>"BreInvoiceItems",
                     'payment'=>"BreInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_BreInvoices",
                 ),
                 'links'=>array(
                     'edit' =>'invoice/editbresapvinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'patientform/bresapvperformance?iid=[inv_id]&mode=pdfs',
                     'print_storno' =>'patientform/bresapvperformance?iid=[inv_id]&storno=[storno_inv_id]&mode=pdfs&pdfquick=1&stornopdf=1',
                 ),
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/breinvoices",
                 "generation_page" => "invoice/bresapvinvoicepatientlist",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("bre_sapv", "bre_dta"),         
                 "pricelist_title" => $Tr->translate('Price List BRE'),                                 //"Bremen si Bremen DTA",
                 "price_model"    => array("PriceBreSapv", "PriceBreDta"),
             ), 
/*              "bre_dta_invoice" => array(
                 "name"   => "Rechnungen BRE DTA",
                 "billing_methods" => array(),
                 "page_invoices_listed" => "dta/dtahospizinvoices",
                 "generation_page" => "",
                 "has_template"    => "yes",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("bre_dta"),
                 "pricelist_title" => $Tr->translate('Price List BRE DTA'),                                 //"Bremen DTA",
                 "price_model"    => array("PriceBreDta"),
             ), */
 
             "bre_hospiz_sapv_invoice" => array(
                 "name"   => "Rechnungen BRE Hospiz - SAPV Hospiz Rechnungen",
                 "models"   =>array(
                     'invoice'=>"BreHospizInvoices",
                     'items'=>"BreHospizInvoiceItems",
                     'payment'=>"BreHospizInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_BreHospizInvoices",
                 ),
                 'links'=>array(
                     'edit' =>'invoice/editbrehospizsapvinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoice/editbrehospizsapvinvoice?invoiceid=[inv_id]&pdf=1',
                     'print_storno' =>'invoice/editbrehospizsapvinvoice?invoiceid=[inv_id]&storno=[storno_inv_id]&pdf=1&stornopdf=1',
                 ),
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/brehospizinvoices",
                 "generation_page" => "invoice/brehospizsapvinvoicepatientlist",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("bre_hospiz"), 
                 "pricelist_title" => $Tr->translate('Price List BRE Hospiz'),                              //"Bremen - Hospiz-Leistungsbogen",
                 "price_model"    => array("PriceBreHospiz"),
             ), 
             "by_invoice" => array(
                 "name"   => "Rechnung",
                 "models"   =>array(
                     'invoice'=>"ClientInvoices",
                     'payment'=>"InvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_Invoices",
                 ),
                 'links'=>array(
                     'edit' =>'invoice/editinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoice/editinvoice?invoiceid=[inv_id]&pdfquick=1',
                     'print_storno' =>'invoice/editinvoice?invoiceid=[inv_id]&storno=[storno_inv_id]&pdfquick=1&stornopdf=1',
                 ),
                 "billing_methods" => array("overall", "admission"),
                 "page_invoices_listed" => "invoice/invoice",
                 "generation_page" => "invoice/invoice",
                 "has_template"    => "yes",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("bayern_sapv"),
                 "pricelist_title" => $Tr->translate('Price Bayern'),                              //"Bayern",
                 "price_model"    => array("PriceBayernSapv"),
             ),

             "bw_sgbxi_invoice" => array(
                 "name"   => "Rechnung BW - SGB XI Rechnungen",
                 "models"   =>array(
                     'invoice'=>"SgbxiInvoices",
                     'items'=>"SgbxiInvoiceItems",
                     'payment'=>"SgbxiInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_SgbxiInvoices",
                 ),
                 'links'=>array(
                     'edit' =>'invoice/editsgbxiinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoice/sgbxiinvoice?iid=[inv_id]&mode=pdfs&pdfquick=1',
                     'print_storno' =>'invoice/sgbxiinvoice?iid=[inv_id]&storno=[storno_inv_id]&mode=pdfs&pdfquick=1&stornopdf=1',
                 ),
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/sgbxiinvoices",
                 "generation_page" => "invoice/sapvinvoicepatientlist",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("sgbxi"),
                 "pricelist_title" => "Price SGB XI",
                 "price_model"    => array("PriceSgbxi"),
             ),
             "bayern_invoice" => array(
                 "name"   => "Rechnung Tagespauschale",
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/bayerninvoices",
                 "generation_page" => "invoice/bayerninvoicepatientlist",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("bayern","bayern_sapv"),
                 "pricelist_title" => $Tr->translate('Price Bayern #2'),                              //"Bayern Tagespauschale",
                 "price_model"    => array("PriceBayern","PriceBayernSapv"),
             ),
             
             
             "bayern_sapv_invoice" =>array(
                 "name"   => "Rechnung Tagespauschale",
                 "models"   =>array(
                     'invoice'=>"BayernInvoicesNew",
                     'items'=>"BayernInvoiceItemsNew",
                     'payment'=>"BayernInvoicePaymentsNew",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_BayernInvoicesNew",
                 ),
                 'links'=>array(
                     'edit' =>'invoicenew/editbayerninvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoicenew/bayernsapvinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&type=pdf',
                     'print_storno' =>'invoicenew/bayernsapvinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&stornopdf=1&stornoid=[storno_inv_id]&type=pdf',
                 ),
                 "billing_methods" => array("per_month"),
                 "page_invoices_listed" => "invoice/bayerninvoices",
                 "generation_page" => "invoice/bayerninvoicepatientlist",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("bayern","bayern_sapv"),
                 "pricelist_title" => $Tr->translate('Price Bayern #2'),                              //"Bayern Tagespauschale",
                 "price_model"    => array("PriceBayern","PriceBayernSapv"),
             ), 
             
             "new_bayern_invoice" => array(
                 "name"   => "Rechnung Tagespauschale",
                 "models"   =>array(
                     'invoice'=>"BayernInvoicesNew",
                     'items'=>"BayernInvoiceItemsNew",
                     'payment'=>"BayernInvoicePaymentsNew",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_BayernInvoicesNew",
                 ),
//                  'links'=>array(
//                      'edit' =>'invoice/edithiinvoice?invoiceid=[inv_id]&redirect2new=1',
//                      'print' =>'invoice/healthinsuranceinvoices?invoiceid=[inv_id]&pdfquick=1',
//                      'print_storno' =>'invoice/healthinsuranceinvoices?invoiceid=[inv_id]&storno=[storno_inv_id]&pdfquick=1&stornopdf=1',
//                  ),
                 "billing_methods" => array(),
                 "page_invoices_listed" => "invoicenew/bayerninvoices",
                 "generation_page" => "invoicenew/newinvoicepatientlist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("bayern","bayern_sapv"),    
                 "pricelist_title" => $Tr->translate('Price Bayern #2'),                              //"Bayern Tagespauschale",
                 "price_model"    => array("PriceBayern","PriceBayernSapv"),
             ),
             
//              "rp_invoice" => array(
//                  "name"   => "Rechnungen RP",
//                  "models"   =>array(
//                      'invoice'=>"RpInvoices",
//                      'items'=>"RpInvoiceItems",
//                      'payment'=>"RpInvoicePayments",
//                  ),
//                  "forms"   =>array(
//                      'invoice'=>"Application_Form_RpInvoices",
//                  ),
//                  "billing_methods" => array("sapv_period"),
//                  "page_invoices_listed" => "invoice/rpinvoiceslist",
//                  "generation_page" => "invoice/rpinvoice",
//                  "has_template"    => "no",
//                  "custom_invoice"  => "yes",
//                  "invoice_generation_type" => "individual",
//                  "has_multiple_print" => "yes",
//                  "pricelist_used" => array("rp"),                 
//                  "pricelist_title" => "Rheinland Pfalz",
//                  "price_model"    => array("PriceRpInvoice"),
//              ), 
             "rpinvoice" => array(
                 "name"   => "Rechnungen RP",
                 "models"   =>array(
                     'invoice'=>"RpInvoices",
                     'items'=>"RpInvoiceItems",
                     'payment'=>"RpInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_RpInvoices",
                 ),
                 
                  'links'=>array(
                          'edit' =>'invoice/editrpinvoice?invoiceid=[inv_id]&redirect2new=1',
                          'print' =>'invoice/editrpinvoice?invoiceid=[inv_id]&pdf=1',
                          'print_storno' =>'invoice/editrpinvoice?invoiceid=[inv_id]&storno=[storno_inv_id]&pdf=1&stornopdf=1',
                      ),
                 "billing_methods" => array("sapv_period"),
                 "page_invoices_listed" => "invoice/rpinvoiceslist",
                 "generation_page" => "invoice/rpinvoice",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("rp","rp_dta"),
                 "pricelist_title" => $Tr->translate('Rheinland Pfalz'),
                 "price_model"    => array("PriceRpInvoice","PriceRpDta"),
             ),              
             "rlp_invoice" => array(
                 "name"   => "Rheinland Pfalz",
                 "models"   =>array(
                     'invoice'=>"RlpInvoices",
                     'items'=>"RlpInvoiceItems",
                     'payment'=>"RlpInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_RlpInvoices",
                 ),
                 'links'=>array(
                     'edit' =>'invoicenew/editrlpinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoicenew/rlpinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=[sapvid]&type=pdf',
                     'print_storno' =>'invoicenew/rlpinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&stornopdf=1&stornoid=[storno_inv_id]&type=pdf',
                 ),
                 
                 "billing_methods" => array(),
                 "page_invoices_listed" => "invoicenew/invoicesnew",
                 "generation_page" => "invoicenew/newinvoicepatientlist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("rlp"),
                 "pricelist_title" => $Tr->translate('Rheinland Pfalz 2018'),
                 "price_model"    => array("PriceRlp"),
                 
                 "print_function"    => "generate_rlpinvoice",
             ),
             "rlp_invoice_2018" => array(
                 "name"   => "Rheinland Pfalz 2018 - Rechnungen",
                 "billing_methods" => array(),
                 "page_invoices_listed" => "",
                 "generation_page" => "",
                 "has_template"    => "yes",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("rlp"),
                 "pricelist_title" => $Tr->translate('Rheinland Pfalz 2018'),
                 "price_model"    => array("PriceRlp"),
             ),
             "sh_invoice" => array(
                 "name"   => "Rechnungen SH",
                 "models"   =>array(
                     'invoice'=>"ShInvoices",
                     'items'=>"ShInvoiceItems",
                     'payment'=>"ShInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_ShInvoices",
                 ),
                 'links'=>array(
                     'edit' =>'invoicenew/editshinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoicenew/shanlage14invoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=[sapvid]&type=pdf',
                     'print_storno' =>'invoicenew/shanlage14invoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=[sapvid]&stornopdf=1&stornoid=[storno_inv_id]&type=pdf',
                 ),
                 "billing_methods" => array("admission", "sapv_period", "per_month"),
                 "page_invoices_listed" => "invoicenew/shinvoices",
                 "generation_page" => "invoicenew/shinvoicepatientlist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("sh", "sh_report", "sh_internal", "sh_internal_user_shifts"),                 
                 "pricelist_title" => $Tr->translate('price_sh'),                              //"Schleswig-Holstein",
                 "price_model"    => array("PriceShInvoice", "PriceShReport", "PriceShInternal", "PriceShInternalUserShifts"),
             ),
             "sh_internal_invoice" => array(
                 "name"   => "Interne Rechnungen SH",
                 "billing_methods" => array("per_month", "per_user"),
                 "page_invoices_listed" => "invoicenew/shinternalinvoices",
                 "generation_page" => "invoicenew/shinternalinvoiceuserslist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("sh", "sh_report", "sh_internal", "sh_internal_user_shifts"),  
                 "pricelist_title" => $Tr->translate('price_sh'),                   //"SH interne Preislisten",  
                 "price_model"    => array("PriceShInvoice", "PriceShReport", "PriceShInternal", "PriceShInternalUserShifts"),
             ),
             "sh_shifts_internal_invoice" => array(
                 "name"   => "Interne Rechnungen SH II",
                 "billing_methods" => array("per_month", "per_user"),
                 "page_invoices_listed" => "invoicenew/shshiftsinternalinvoices",
                 "generation_page" => "invoicenew/shshiftsinternalinvoiceuserslist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("sh", "sh_report", "sh_internal", "sh_internal_user_shifts"),
                 "pricelist_title" => $Tr->translate('price_sh'),                   //"SH interne Preislisten II",
                 "price_model"    => array("PriceShInvoice", "PriceShReport", "PriceShInternal", "PriceShInternalUserShifts"),
             ),
             "hospiz_invoice" => array(
                 "name"   => "Hospiz Rechnung",
                 "models"   =>array(
                     'invoice'=>"HospizInvoices",
                     'items'=>"HospizInvoiceItems",
                     'payment'=>"HospizInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_HospizInvoices",
                 ),
                 
                 'links'=>array(
                     'edit' =>'invoicenew/edithospizinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoicenew/hospizinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=[sapvid]&type=pdf',
                     'print_storno' =>'invoicenew/hospizinvoice?iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&stornopdf=1&stornoid=[storno_inv_id]&type=pdf',
                 ),
                 "billing_methods" => array("overall","per_month"),
                 "page_invoices_listed" => "invoicenew/invoicesnew",
                 "generation_page" => "",               //"invoicenew/patienthospizinvoice?id=......",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("hospiz"),             
                 "pricelist_title" => $Tr->translate('price_hospiz'),                              //"Preis Hospiz",
                 "price_model"    => array("PriceHospiz"),
             ),
             "nr_invoice" => array(
                 "name"   => "Nordrhein Anlage10 Rechnungen",
                 "models"   =>array(
                     'invoice'=>"InvoiceSystem",
                     'items'=>"InvoiceSystemItems",
                     'payment'=>"InvoiceSystemPayments",
                     'form'=>"Application_Form_InvoiceSystem"
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_InvoiceSystem",
                 ),
                 'links'=>array(
                     'edit' =>'invoicenew/editinvoice?invoice_type=[inv_type]&invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoicenew/systeminvoice?invoice_type=[inv_type]&iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&type=pdf',
                     'print_storno' =>'invoicenew/systeminvoice?invoice_type=[inv_type]&iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&stornopdf=1&stornoid=[storno_inv_id]',
                 ),
                 
                 "billing_methods" => array(),
                 "page_invoices_listed" => "invoicenew/invoicesnew",
                 "generation_page" => "invoicenew/newinvoicepatientlist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("nr_anlage10"),             
                 "pricelist_title" => "Price Nordrhein Anlage10",                               //Nordrhein Anlage10
                 "price_model"    => array("PriceNordrhein"),
             ),
             "nr_invoice_2018" => array(
                 "name"   => "Nordrhein 2018 Rechnungen",
                 "billing_methods" => array(),
                 "page_invoices_listed" => "",
                 "generation_page" => "",
                 "has_template"    => "",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("nr_invoice"),
                 "pricelist_title" => "Price Nordrhein 2018",                               //Nordrhein 2018
                 "price_model"    => array("PriceNordrhein"),
             ),
             "bre_kinder_invoice" => array(
                 "name"   => "Bremen Kinder SAPV Rechnungen",
                 "models"   =>array(
                     'invoice'=>"InvoiceSystem",
                     'items'=>"InvoiceSystemItems",
                     'payment'=>"InvoiceSystemPayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_InvoiceSystem",
                 ),
                 'links'=>array(
                     'edit' =>'invoicenew/editinvoice?invoice_type=[inv_type]&invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoicenew/systeminvoice?invoice_type=[inv_type]&iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&type=pdf',
                     'print_storno' =>'invoicenew/systeminvoice?invoice_type=[inv_type]&iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&stornopdf=1&stornoid=[storno_inv_id]',
                 ),
                 "billing_methods" => array(),
                 "page_invoices_listed" => "invoicenew/invoicesnew",
                 "generation_page" => "",
                 "has_template"    => "",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("bre_kinder"),            
                 "pricelist_title" => $Tr->translate('bre kinder 2018'),                         //"Bremen",
                 "price_model"    => array("PriceBreKinder"),
             ),
             "demstepcare_invoice" => array(
                 "name"   => "Demstepcare Rechnungen",
                 "models"   =>array(
                     'invoice'=>"InvoiceSystem",
                     'items'=>"InvoiceSystemItems",
                     'payment'=>"InvoiceSystemPayments",
                     'form'=>"Application_Form_InvoiceSystem"
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_InvoiceSystem",
                 ),
                 
                 'links'=>array(
                     'edit' =>'invoicenew/editinvoice?invoice_type=[inv_type]&invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoicenew/systeminvoice?invoice_type=[inv_type]&iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&type=pdf',
                     'print_storno' =>'invoicenew/systeminvoice?invoice_type=[inv_type]&iid=[inv_id]&only_invoice=1&patient=[patient_ipid]&sapvid=&stornopdf=1&stornoid=[storno_inv_id]',
                 ),
                 
                 
                 "billing_methods" => array(),
                 "page_invoices_listed" => "invoicenew/invoicesnew",
                 "generation_page" => "invoicenew/newinvoicepatientlist",
                 "has_template"    => "yes",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "multiple",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("demstepcare_invoice"),             
                 "pricelist_title" => $Tr->translate('demstepcare price list  2018'),          //"DemStepCare",
                 "price_model"    => array("PriceDemstepcare"),
             ),
             "demstepcare_internal_invoice" => array(
                 "name"   => "Interne Rechnungen Demstepcare",
                 "billing_methods" => array("per_patient"),
                 "page_invoices_listed" => "invoicenew/demstepcareinternal",
                 "generation_page" => "patientformnew/demstepcarecontrol",
                 "has_template"    => "",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array(),
                 "pricelist_title" => "DemStepCare",
                 "price_model"    => array("PriceDemstepcare"),
             ),
             "he_invoice" => array(
                 "name"   => "Rechnungen HE",
                 "models"   =>array( 
                     'invoice'=>"HeInvoices",
                     'items'=>"HeInvoiceItems",
                     'payment'=>"HeInvoicePayments",
                 ),
                 "forms"   =>array(
                     'invoice'=>"Application_Form_HeInvoices",
                 ),
                 'links'=>array(
                     'edit' =>'invoice/editheinvoice?invoiceid=[inv_id]&redirect2new=1',
                     'print' =>'invoice/editheinvoice?invoiceid=[inv_id]&pdf=1',
                     'print_storno' =>'invoice/editheinvoice?invoiceid=[inv_id]&storno=[storno_inv_id]&pdf=1&stornopdf=1',
                 ),
                 
                 "billing_methods" => array("per_patient"),
                 "page_invoices_listed" => "invoice/heinvoiceslist",
                 "generation_page" => "invoice/heinvoice",
                 "has_template"    => "no",
                 "custom_invoice"  => "yes",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "yes",
                 "pricelist_used" => array("hessen","hessen_dta","xa"),             
                 "pricelist_title" => $Tr->translate('Price List Hessen'),                      //"Hessen si Hessen DTA"
                 "price_model"    => array("PriceHessen","PriceHessenDta","PriceXbdtActions"),
             ), 
/*              "he_dta_invoice" => array(
                 "name"   => "Rechnungen HE DTA",
                 "billing_methods" => array(""),
                 "page_invoices_listed" => "dta/listdtaheinvoices",
                 "generation_page" => "",
                 "has_template"    => "",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("hessen_dta"),
                 "pricelist_title" => $Tr->translate('pricelist_hessen_dta_header'),                      //"Hessen DTA"
                 "price_model"    => array("PriceHessenDta"),
             ), */
             "members_invoice" => array(
                 "name"   => "Mitgliedsverwaltung - Rechnungen",
                 "billing_methods" => array(),
                 "page_invoices_listed" => "invoicenew/membersinvoices",
                 "generation_page" => "",
                 "has_template"    => "yes",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("memberships"),
                 "pricelist_title" => $Tr->translate('Membership - Price List Details'),                    //"Price Memberships",
                 "price_model"    => array("PriceMemberships"),
             ),
             "xbdtactions" => array(                    /// ?????????????????????????
                 "name"   => "BDT Leistungen",
                 "billing_methods" => array(),
                 "page_invoices_listed" => "",
                 "generation_page" => "",
                 "has_template"    => "",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("xa"),
                 "pricelist_title" => $Tr->translate('BDT Leistungen Preise'),                      //"Price ",
                 "price_model"    => array("PriceXbdtActions"),
             ),
/*              "rp_dta_invoice" => array(
                 "name"   => "Rheinland Pfalz DTA - Rechnungen",
                 "billing_methods" => array(),
                 "page_invoices_listed" => "dta/listdtarpinvoices",
                 "generation_page" => "",
                 "has_template"    => "yes",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("rp","rp_dta"),
                 "pricelist_title" => $Tr->translate('Rheinland Pfalz Dta'),
                 "price_model"    => array("PriceRpInvoice","PriceRpDta"),
             ), */
             "form_blocks" => array(
                 "name"   => "Form Blocks Preis",
                 "billing_methods" => array(),
                 "page_invoices_listed" => "",
                 "generation_page" => "",
                 "has_template"    => "yes",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "",
                 "has_multiple_print" => "",
                 "pricelist_used" => array("formblocks"),
                 "pricelist_title" => $Tr->translate('Form Blocks Preis'),
                 "price_model"    => array("PriceFormBlocks"),
             ),
             "internal_invoice" => array(
                 "name"   => "Interne Rechnungen",
                 "billing_methods" => array("overall","per_month", "per_patient", "per_user"),
                 "page_invoices_listed" => "internalinvoice/invoices",
                 "generation_page" => "",               //internalinvoice/patientinvoice?id=....
                 "has_template"    => "",
                 "custom_invoice"  => "no",
                 "invoice_generation_type" => "individual",
                 "has_multiple_print" => "",
                 "pricelist_used" => array(),
                 "pricelist_title" => "",
                 "price_model"    => array(),
             ),

         );
         return $data;
         
     }
     
     //ISPC-2902 Lore 28.04.2021
     public function patient_course_options($shortcut = false){
         
         $shortcuts = array(
                 'XT' => array( 
                     '0' => '------',
                     '1' => 'mit Patienten',
                     '2' => 'mit Angehörigen',
                     '3' => 'mit Professionellen'
                 ),
         );
         
        return $shortcuts;     
     
     }
     

    /**
     * IM-58 - 31.08.2020
     * @author Nico
     * used in clinic letters
     */
     public static function transform_list_to_text($l){
         //transform [x1,x2,x3] into "x1, x2 und x3"
             $t = "";
             if(!is_Array($l) || count($l)==0){
                 return "";
             }
             if(count($l)==1){
                 return $l[0];
             }
             if(count($l)==2){
                 return $l[0] ." und ".$l[1];
             }
             if(count($l)>2){
                 $a=array_slice($l, 0, count($l)-1);
                 $b=" und " . $l [count($l)-1];
                 $a=join(', ', $a);
                 return $a . $b;
             }
     }


        /**
         * @author Nico
         * convert some BB-Tags to HTML
         * used in Contact-Form tinymce-textareas
         */
     public static function bb_to_html($in,$options=[]){
         $aBBTags = ['[b]', '[/b]', '[u]', '[/u]', '[i]', '[/i]'];
         $aTags = ['<b>', '</b>', '<u>', '</u>', '<i>', '</i>'];
         $x=htmlspecialchars($in);
         $x2 = str_replace($aBBTags, $aTags, $x);
         return str_replace("\r\n",'<br>', $x2);
     }


}//end of class
