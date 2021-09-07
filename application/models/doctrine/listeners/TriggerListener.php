<?php
/**
 * 
 * @claudiu update 05.01.2018: added ELSE for the PatientDiagnosis insert
 *
 */

	class TriggerListener extends Doctrine_Record_Listener {

		protected $fire_firstname_trigger = false;

		protected $isTrigger = false;
		
		public function preInsert(Doctrine_Event $event)
		{
			$triggerformid = isset($event->getInvoker()->triggerformid) ? $event->getInvoker()->triggerformid : 0;
			$triggerformname = $event->getInvoker()->triggerformname;

			if($triggerformid < 1)
			{
				$this->isTrigger = false;
				return;
			}
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$invoker = $event->getInvoker();
			$mod = $invoker->getModified();
			$this->gmod = $mod;
			$this->gpost = $_POST;

			
			$query = Doctrine_Query::create()
				->select('*')
				->from('FieldTrigger')
				->where("formid=" . $triggerformid . " and event=2 and clientid=" . $logininfo->clientid);
			$result = $query->execute();
			$newarr = $result->toArray();
			$this->triggerFieldid = array();

			foreach($newarr as $key => $val)
			{
				
				$query1 = Doctrine_Query::create()
					->select('*')
					->from('TriggerFields')
					->where("formid=" . $val['formid'] . " and id=" . $val['fieldid']);
				$result1 = $query1->execute();
				$newarr1 = $result1->toArray();

				if($triggerformid == 9)
				{
					foreach($newarr1 as $key1 => $val1)
					{
						if(array_key_exists($val1['fieldname'], $mod))
						{
							if(array_key_exists('diagnosis', $_POST))
							{
								$this->triggerFieldid[] = $val1['id'];
							} 
							/*
							 * @claudiu introduced this else for wlassessmentAction
							 */
							elseif (Zend_Controller_Front::getInstance()->getRequest()->isPost()
							    && Pms_CommonData::assertKeyExists($_POST, $val1['fieldname']))
							{
							    $this->triggerFieldid[] = $val1['id'];
							}
						}
					}
				}
				else
				{
					foreach($newarr1 as $key1 => $val1)
					{
						if(array_key_exists($val1['fieldname'], $mod))
						{
							if(array_key_exists($val1['fieldname'], $_POST))
							{
								$this->triggerFieldid[] = $val1['id'];
							}
						}
						elseif($val1['fieldname'] == $triggerformname)
						{
							$this->triggerFieldid[] = $val1['id'];
						}
					}
				}
			}

			if(count($this->triggerFieldid) > 0)
			{
				$this->isTrigger = true;
				$this->triggerFormid = $triggerformid;
			}
			else
			{
				$this->isTrigger = false;
			}
		}

		public function preUpdate(Doctrine_Event $event)
		{
			$triggerformname = $event->getInvoker()->triggerformname;
			$triggerformid = isset($event->getInvoker()->triggerformid) ? $event->getInvoker()->triggerformid : 0;
				
			if($triggerformid < 1)
			{
				$this->isTrigger = false;
				return;
			}
			
			$invoker = $event->getInvoker();
			$mod = $invoker->getModified();

			$this->gmod = $mod;
			//print_r($mod);
			$this->gpost = $_POST;

			$query = Doctrine_Query::create()
				->select('*')
				->from('FieldTrigger')
				->where("formid=" . $triggerformid . " and event=1 and isdelete=0 and clientid=" . $logininfo->clientid);
			$result = $query->execute();
			$newarr = $result->toArray();
			$this->triggerFieldid = array();

			foreach($newarr as $key => $val)
			{
				$query1 = Doctrine_Query::create()
					->select('*')
					->from('TriggerFields')
					->where("formid=" . $val['formid'] . " and id=" . $val['fieldid']);
				$result1 = $query1->execute();
				$newarr1 = $result1->toArray();
				if($triggerformid == 9)
				{
					foreach($newarr1 as $key1 => $val1)
					{
						if(array_key_exists($val1['fieldname'], $mod))
						{
							if(array_key_exists('diagnosis', $_POST))
							{
								$this->triggerFieldid[] = $val1['id'];
							}
						}
					}
				}
				//TODO-3595 Ancuta 21.12.2020
				elseif($triggerformid == 5)
				{
				    foreach($newarr1 as $key1 => $val1)
				    {
				        
				        if(array_key_exists($val1['fieldname'], $mod) || array_key_exists("valid_from", $_POST)  || array_key_exists("valid_till", $_POST))
				        {
				            if(array_key_exists($val1['fieldname'], $_POST)  )
				            {
				                $this->triggerFieldid[] = $val1['id'];
				            }
				        }
				        elseif($val1['fieldname'] == $triggerformname)
				        {
				            $this->triggerFieldid[] = $val1['id'];
				        }
				    }
				}
				//--
				else
				{
					foreach($newarr1 as $key1 => $val1)
					{
						if(array_key_exists($val1['fieldname'], $mod))
						{
							if(array_key_exists($val1['fieldname'], $_POST))
							{
								$this->triggerFieldid[] = $val1['id'];
							}
						}
						elseif($val1['fieldname'] == $triggerformname)
						{
							$this->triggerFieldid[] = $val1['id'];
						}
					}
				}
			}

			if(count($this->triggerFieldid) > 0)
			{
				$this->isTrigger = true;
				$this->triggerFormid = $triggerformid;
			}
			else
			{
				$this->isTrigger = false;
			}
		}

		public function postUpdate(Doctrine_Event $event)
		{
			$fieldidarr = $this->triggerFieldid;

			if($this->isTrigger)
			{
				Pms_Triggers::callTrigger($event, $this->triggerFormid, 1, $fieldidarr, $this->gmod, $this->gpost);
			}
		}

		public function postInsert(Doctrine_Event $event)
		{
			$fieldidarr = $this->triggerFieldid;
			if($this->isTrigger)
			{
				Pms_Triggers::callTrigger($event, $this->triggerFormid, 2, $fieldidarr, $this->gmod, $this->gpost);
			}
		}

	}

?>