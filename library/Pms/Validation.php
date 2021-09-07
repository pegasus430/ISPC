<?php
class Pms_Validation
{
	function isstring($string)
	{
		$error =0;
		if(strlen(trim($string))<1){$error = 1;}

		if($error==0)
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	function email($email)
	{
		if(strlen(trim($email))<1)
		{
		  return false;
		}

		if (preg_match('/^[^0-9._-][a-zA-Z0-9._-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$/', $email))
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	function name($name)
	{
		if(preg_match("/^[A-Za-z]+$/",$name) && $this->isstring($name))
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	function isscript($str)
	{
		if(preg_match("/<(.*)script(.*)>/",$str))
		{

			return true;

		}
		else
		{

			return false;
		}

	}

	function alphanum($str)
	{
		if(preg_match("/^[A-Za-z0-9\s]+$/",$str))
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	function integer($str)
	{
		if(preg_match("/^[0-9]+$/",$str))
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	function isusername($username)
	{

		if(preg_match("/[<>';#\"\s]/",$username)>0)
		{

			return false;
		}
		else
		{

			return true;
		}

	}

	function isdate ( $str )
	{
	    if (substr_count($str, '.') == 2)
	    {
		list($d, $m, $y) = explode('.', $str);		
		return checkdate($m, $d, sprintf('%04u', $y));
	    }

	    return false;
	}


}

?>