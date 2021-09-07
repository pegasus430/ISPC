<?php

require_once("Pms/Form.php");

class Application_Form_PatientHospizvizits extends Pms_Form {

	public function validate ($post)
	{

		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();

		if ($post['vw_id'] == 0)
		{
			$this->error_message['hospiz'] = $Tr->translate ('hospizusererror');

			$error = 1;
		}

		if ($post['type'] != 'b')
		{ //validate date field,  normal type
			if (empty ($post['hospizvizit_date']))
			{
				$this->error_message['hospiz'] = $Tr->translate ('hospizdateerror');

				$error = 1;
			}
		}
		else
		{//validate amount field, bulk type
			if (empty ($post['amount']) || $post['amount'] == '0')
			{
				$this->error_message['hospiz'] = $Tr->translate ('hospizamounterrorupdate');

				$error = 1;
			}
		}

		if ($error == 0)
		{
			return true;
		}

		return false;
	}

	public function validate_multiple_simple ($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$err = array();
		$val = new Pms_Validation();


		foreach ($post['vw_id'] as $k_post => $v_post)
		{
			if ($v_post != '0' && empty ($post['hospizvizit_date'][$k_post]))
			{
				$this->error_message['hospiz_s'] = $Tr->translate ('hospizdateerror');

				$error = 1;
			}
			else if ($v_post == 0 && !empty ($post['date'][$k_post]))
			{
				$this->error_message['hospiz_s'] = $Tr->translate ('hospizusererror');

				$error = 1;
			}

			if ($v_post == 0)
			{
				$err[] = 'x';
			}
		}

		if (count ($err) == count ($post['vw_id']))
		{
			$this->error_message['hospiz_s'] = $Tr->translate ('hospizallemptyerror');
			$error = 1;
		}

		if ($error == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function validate_multiple_cumulative ($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$err = array();
		$val = new Pms_Validation();

		foreach ($post['vw_id'] as $k_post => $v_post)
		{
			if ($v_post != '0' && empty ($post['amount'][$k_post]))
			{
				$this->error_message['hospiz_b'] = $Tr->translate ('hospizamounterror');

				$error = 1;
			}
			else if ($v_post == 0 && !empty ($post['amount'][$k_post]))
			{
				$this->error_message['hospiz_b'] = $Tr->translate ('hospizusererror');

				$error = 1;
			}

			if ($v_post == 0)
			{
				$err[] = 'x';
			}
		}

		if (count ($err) == count ($post['vw_id']))
		{
			$this->error_message['hospiz_b'] = $Tr->translate ('hospizallemptyerror');
			$error = 1;
		}

		if ($error == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function InsertData ($post)
	{
		$decid = Pms_Uuid::decrypt ($_GET['id']);
		$ipid = Pms_CommonData::getIpid ($decid);
		$epid = Pms_CommonData::getEpid ($ipid);

		$logininfo = new Zend_Session_Namespace ('Login_Info');
		$clientid = $logininfo->clientid;
		$vizit_date = explode (".", $post['hospizvizit_date']);

		$cust = new PatientHospizvizits();
		$cust->type = 'n'; //normal
		$cust->ipid = $ipid;
		$cust->vw_id = $post['vw_id'];
		$cust->amount = $post['amount'];
		$cust->hospizvizit_date = $vizit_date[2] . "-" . $vizit_date[1] . "-" . $vizit_date[0] . " 00:00:00";
		$cust->besuchsdauer = $post['besuchsdauer'];
		$cust->fahrtkilometer = $post['fahrtkilometer'];
		$cust->fahrtzeit = $post['fahrtzeit'];
		$cust->grund = $post['grund'];
		$cust->nightshift = $post['nightshift'];
		$cust->save ();
	}

	public function UpdateData ($post)
	{
		$cust = Doctrine::getTable ('PatientHospizvizits')->find ($_GET['vizitid']);
		$cust->vw_id = $post['vw_id'];
		if ($post['type'] == 'b')
		{
			$cust->amount = $post['amount'];
			$cust->hospizvizit_date = $post['hospizvizit_date']."-01-01 00:00:00";
		}
		else
		{
			$vizit_date = explode (".", $post['hospizvizit_date']);
			$cust->hospizvizit_date = $vizit_date[2] . "-" . $vizit_date[1] . "-" . $vizit_date[0] . " 00:00:00";
		}

		$cust->besuchsdauer = $post['besuchsdauer'];
		$cust->fahrtkilometer = $post['fahrtkilometer'];
		$cust->fahrtzeit = $post['fahrtzeit'];
		$cust->grund = $post['grund'];
		$cust->nightshift = $post['nightshift'];
		$cust->save ();
	}

	public function InsertDataMultiple ($post)
	{
		$decid = Pms_Uuid::decrypt ($_GET['id']);
		$ipid = Pms_CommonData::getIpid ($decid);
		$epid = Pms_CommonData::getEpid ($ipid);
		$type = $post['type'];
		$logininfo = new Zend_Session_Namespace ('Login_Info');
		$clientid = $logininfo->clientid;
		foreach ($post['vw_id'] as $k_hv => $v_hv)
		{
			if ($v_hv != '0')
			{
				$cust = new PatientHospizvizits();
				$cust->type = $type;
				$cust->ipid = $ipid;
				$cust->vw_id = $v_hv;

				if ($type == 'b')
				{
					$cust->amount = $post['amount'][$k_hv];
					$cust->hospizvizit_date = $post['hospizvizit_date'][$k_hv]."-01-01 00:00:00";
				}
				else if ($type == 'n')
				{
					$vizit_date = date('Y-m-d H:i:s', strtotime($post['hospizvizit_date'][$k_hv]));
					$cust->hospizvizit_date = $vizit_date;
				}

				$cust->besuchsdauer = $post['besuchsdauer'][$k_hv];
				$cust->fahrtkilometer = $post['fahrtkilometer'][$k_hv];
				$cust->fahrtzeit = $post['fahrtzeit'][$k_hv];
				$cust->grund = $post['grund'][$k_hv];
				$cust->nightshift = $post['nightshift'][$k_hv];
				$cust->save ();
			}
		}
	}
}

?>