<?php

	require_once("Pms/Form.php");

	class Application_Form_PseudoUser extends Pms_Form {

		public function validate($post)
		{
			$error = 0;
			$val = new Pms_Validation();
			$Tr = new Zend_View_Helper_Translate();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			
			if($post['user'] == '0')
			{
				$this->error_message['user'] = $Tr->translate('field_linked_user_not_selected');
				$error = 1;
			}

			if(!$val->isstring($post['last_name']) && $error == '0')
			{
				$this->error_message['last_name'] = $Tr->translate('field_last_name_not_empty');
				$error = 2;
			}

			if(!$val->isstring($post['first_name']) && $error == '0')
			{
				$this->error_message['first_name'] = $Tr->translate('field_first_name_not_empty');
				$error = 3;
			}


			if($error == '0')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function insert_user($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$ins_usr = new PseudoUsers();
			$ins_usr->client = $clientid;
			$ins_usr->user = $post['user'];
			$ins_usr->title = $post['title'];
			$ins_usr->first_name = $post['first_name'];
			$ins_usr->last_name = $post['last_name'];
			$ins_usr->shortname = $post['shortname'];
			$ins_usr->ishidden = (int)$post['ishidden'];
			$ins_usr->save();

			if($ins_usr->id)
			{
				return $ins_usr->id;
			}
			else
			{
				return false;
			}
		}

		public function update_user($post)
		{
			if($post['pseudo_user_id'] && $post['pseudo_user_id'] > '0')
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$userid = $logininfo->userid;
				$clientid = $logininfo->clientid;

				$pseudo_user = $post['pseudo_user_id'];
				$upd_usr = Doctrine::getTable('PseudoUsers')->findOneByIdAndClient($pseudo_user, $clientid);
				if($upd_usr)
				{
					$upd_usr->client = $clientid;
					$upd_usr->user = $post['user'];
					$upd_usr->title = $post['title'];
					$upd_usr->first_name = $post['first_name'];
					$upd_usr->last_name = $post['last_name'];
					$upd_usr->shortname = $post['shortname'];
					$upd_usr->ishidden = (int)$post['ishidden'];
					$upd_usr->save();

					if($upd_usr->id)
					{
						return $upd_usr->id;
					}
					else
					{
						return false;
					}
				}
				else
				{
					//user does not belong to curent client or doesnt exist
					return false;
				}
			}
			else
			{
				//nothing to edit
				return false;
			}
		}

		public function delete_pseudo_user($client = false, $pseudo_user = false)
		{
			if($client && $pseudo_user)
			{
				$del = Doctrine::getTable('PseudoUsers')->findOneByIdAndClient($pseudo_user, $client);
				if($del)
				{
					$del->isdelete = "1";
					$del->save();
				}

				if($del->id)
				{
					return $del->id;
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

	}

?>