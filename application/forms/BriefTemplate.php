<?php

	require_once("Pms/Form.php");

	class Application_Form_BriefTemplate extends Pms_Form {

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();
			if(!$val->isstring($post['title']))
			{
				$this->error_message['title'] = $Tr->translate('brief_template_title_error');
				$error = 1;
			}

			if(!$val->isstring($post['template_filename']) && !$val->isstring($post['template_filetype']) && !$val->isstring($post['template_filepath']))
			{
				$this->error_message['file'] = $Tr->translate('brief_template_file_error');
				$error = 2;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function insert_template_data($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$res = new BriefTemplates();
			$res->clientid = $clientid;
			$res->title = $post['title'];
			$res->recipient = $post['recipient'];
			$res->file_type = $post['template_filetype'];
			$res->file_path = $post['template_filepath'];
			$res->isdeleted = "0";
			$res->save();

			if(!empty($post['template_filename']))
			{
				$this->move_uploaded_template($res->id);
			}
		}

		public function delete_template($clientid, $icon_id)
		{
			$cust = Doctrine::getTable('BriefTemplates')->findOneByIdAndClientid($icon_id, $clientid);
			if($cust)
			{
				$cust->isdelete = '1';
				$cust->save();
			}
		}

		public function update_template_data($clientid, $post)
		{
			$cust = Doctrine::getTable('BriefTemplates')->findOneByIdAndClientid($post['template_id'], $clientid);
			if($cust)
			{

				$cust->title = $post['title'];
				$cust->recipient = $post['recipient'];
				$cust->save();

				if($post['change_file'] == '1')
				{
					//in case of change file(backup_old_file)
					$cust_array = $cust->toArray();
					$backup = true;

					if($cust_array)
					{
						$backup = $this->backup_old_template($cust_array['file_path']);
					}

					$cust->file_type = $post['template_filetype'];
					$cust->save();

//					if($backup)
//					{
					$this->move_uploaded_template($post['template_id']);
//					}
				}
				else
				{
					//cleanup uploaded file if change template checkbox is not checked
					unlink('brief_templates/' . $post['template_filepath']);
				}
				$cust->save();
			}
		}

		private function move_uploaded_template($inserted_file_id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			//move icon file to desired destination /public/icons/clientid/icon_db_id.ext
			$template_upload_path = 'brief_templates/' . $_SESSION['template_filepath'];
			$template_new_path = 'brief_templates/' . $clientid . '/' . $inserted_file_id . '.' . $_SESSION['template_filetype'];

			copy($template_upload_path, $template_new_path);
			unlink($template_upload_path);

			//@TODO: change this query with the one wich is not updating change_date & change_user!
			$update = Doctrine::getTable('BriefTemplates')->find($inserted_file_id);
			$update->file_path = $clientid . '/' . $inserted_file_id . '.' . $_SESSION['template_filetype'];
			$update->save();
		}

		private function backup_old_template($old_file_path)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$template_old_path = 'brief_templates/' . $old_file_path;

			$extension = explode('.', $old_file_path);
			$file_name = $extension[0];
			$file_ext = $extension[count($extension) - 1];
			$new_backup_path = $file_name . '_' . time() . '_bak.' . $file_ext;

			$template_new_backup_path = 'brief_templates/' . $new_backup_path;


			return rename($template_old_path, $template_backup_new_path);
		}

	}

?>