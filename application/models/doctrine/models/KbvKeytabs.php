<?php

	Doctrine_Manager::getInstance()->bindComponent('KbvKeytabs', 'SYSDAT');

	class KbvKeytabs extends BaseKbvKeytabs {

		public function getKbvKeytabs($isdrop)
		{
			$Tr = new Zend_View_Helper_Translate();
			$insurancedrop = Doctrine_Query::create()
				->select('*')
				->from('KbvKeytabs')
				->where("valid=0 and sn='S_KBV_VERSICHERTENSTATUS'")
				->orderBy('dn ASC');
			$statusdropexec = $insurancedrop->execute();

			if($statusdropexec)
			{
				if($isdrop == 1)
				{
					$dropoid = array("" => $Tr->translate("pleaseselect"));
					foreach($statusdropexec->toArray() as $key => $val)
					{
						$dropoid[$val['v']] = $val['dn'];
					}
				}
				else
				{
					$dropoid = $statusdropexec->toArray();
				}
				
				//TODO-3528 Lore 12.11.2020
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
				$modules = new Modules();
				$extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
				if($extra_healthinsurance_statuses){
				    $dropoid += array( 
				                "disabled_delimiter"=>"---- bes. Abrechnungsstatus ---",//TODO-3528 Ancuta 20.11.2020
        				        "00" => "Gesamtsumme aller Stati",
            				    "11" => "Mitglieder West",
            				    "19" => "Mitglieder Ost",
            				    "31" => "Angehörige West",
            				    "39" => "Angehörige Ost",
            				    "51" => "Rentner West",
            				    "59" => "Rentner Ost",
            				    "99" => "nicht zuzuordnende Stati",
            				    "07" => "Auslandsabkommen" );
				}
				//.
				
				return $dropoid;
			}
		}

	}

?>