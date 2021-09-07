<?php

class Pms_Uuid
{

	public static function GenerateIpid()
	{
		return sha1(uniqid(rand(), true));
	}

	public static function GenerateEpid($clnid, $ids = array())
	{

		$client = Doctrine::getTable('Client')->findOneBy('id', $clnid);


		$cl = Doctrine_Query::create()
			->select('*')
			->from('EpidIpidMapping')
			->where("clientid = '" . $clnid . "'")
			->orderBy('epid_num desc')
			->limit(1);

		$clexe = $cl->execute();

		if($clexe)
		{
			$clarr = $clexe->toArray();
			$oldcounter = $clarr[0]['epid_num'];
		}


		if($client)
		{
			$clientarray = $client->toArray();
			$epid_chars = $clientarray['epid_chars'];
			$epid_start_no = $clientarray['epid_start_no'];
		}

		if($epid_start_no > $oldcounter)
		{
			$newepid = $epid_start_no + 1;
		}
		else
		{
			$newepid = $oldcounter + 1;
		}

		// $newepid = 	$epid_start_no+$pcount;
		$epid = $epid_chars . $newepid;
		return $epid;
	}

	/* public static function GenerateEpid($clnid,$ids)
	  {

	  $client = Doctrine::getTable('Client')->findOneBy('id',$clnid);


	  $ipid = Doctrine_Query::create()
	  ->select('*')
	  ->from('EpidIpidMapping')
	  ->where("clientid = '".$clnid."'");

	  $ipidexec =	$ipid->execute();
	  $ipidarray = $ipidexec->toArray();

	  $pcount = count($ipidarray);

	  $clientarr = Pms_CommonData::getclientIpid($clnid);
	  $cnts = count($ipidarray);
	  //print_r($clientarr);


	  if($client)
	  {
	  $clientarray = $client->toArray();
	  $epid_chars = $clientarray['epid_chars'];
	  $epid_start_no = $clientarray['epid_start_no'];
	  }

	  $newepid = 	$epid_start_no+$pcount;
	  $epid = $epid_chars.$newepid;
	  return $epid;
	  } */

	public static function GenerateSortEpid($clnid)
	{

		$client = Doctrine::getTable('Client')->findOneBy('id', $clnid);


		$cl = Doctrine_Query::create()
			->select('*')
			->from('EpidIpidMapping')
			->where("clientid = '" . $clnid . "'")
			->orderBy('epid_num desc')
			->limit(1);

		$clexe = $cl->execute();

		if($clexe)
		{
			$clarr = $clexe->toArray();
			$oldcounter = $clarr[0]['epid_num'];
		}


		if($client)
		{
			$clientarray = $client->toArray();
			$epid_chars = $clientarray['epid_chars'];
			$epid_start_no = $clientarray['epid_start_no'];
		}

		if($epid_start_no > $oldcounter)
		{
			$newepid = $epid_start_no + 1;
		}
		else
		{
			$newepid = $oldcounter + 1;
		}


		// $newepid = 	$epid_start_no+$pcount;
		$epid = $epid_chars . $newepid;
		//$epid = $epid_chars.$newepid;
		$sortepid['epid_chars'] = $epid_chars;
		$sortepid['epid_num'] = $newepid;

		return $sortepid;
	}

	/* public static function decrypt($eid)
	  {
	  /* $pass = base64_decode($eid);
	  $pass = convert_uudecode($pass);
	  $pass = base64_decode($pass);
	  $pass = base64_decode($pass);
	  $pass = convert_uudecode($pass);


	  return base64_decode($eid);

	  }

	  public static function encrypt($did)
	  {
	  /*$pass = convert_uuencode($did);
	  $pass = base64_encode($pass);
	  $pass = base64_encode($pass);
	  $pass = convert_uuencode($pass);
	  $pass = base64_encode($pass);

	  return base64_encode($did);

	  }
	 */

	public static function encrypt($string, $key = NULL)
	{
		$key = Zend_Registry::get('idSalt');
		srand((double)microtime() * 1000000); //for sake of MCRYPT_RAND
		$key = md5($key); //to improve variance
		/* Open module, and create IV */
		$td = mcrypt_module_open('des', '', 'cfb', '');
		$key = substr($key, 0, mcrypt_enc_get_key_size($td));
		$iv_size = mcrypt_enc_get_iv_size($td);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		/* Initialize encryption handle */
		if(mcrypt_generic_init($td, $key, $iv) != -1)
		{
			/* Encrypt data */
			$c_t = mcrypt_generic($td, $string);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			$c_t = $iv . $c_t;
			return base64_encode(base64_encode($c_t));
		} //end if
	}

	public static function decrypt($string, $key = NULL)
	{
		$key = Zend_Registry::get('idSalt');
		$string = base64_decode(base64_decode($string));
		$key = md5($key); //to improve variance
		/* Open module, and create IV */
		$td = mcrypt_module_open('des', '', 'cfb', '');
		$key = substr($key, 0, mcrypt_enc_get_key_size($td));
		$iv_size = mcrypt_enc_get_iv_size($td);
		$iv = substr($string, 0, $iv_size);
		$string = substr($string, $iv_size);
		/* Initialize encryption handle */
		if(mcrypt_generic_init($td, $key, $iv) != -1)
		{
			/* Encrypt data */
			$c_t = mdecrypt_generic($td, $string);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			return $c_t;
		} //end if
	}

}

?>