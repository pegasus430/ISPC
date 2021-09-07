<?php

	require_once("Pms/Form.php");

	class Application_Form_ReminderInvoiceTemplate extends Pms_Form {

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();
			if(!$post['template_id'])
			{
				if(!$val->isstring($post['title']))
				{
					$this->error_message['title'] = $Tr->translate('invoice_template_title_error');
					$error = 1;
				}

				if(!$val->isstring($post['sh_inv_template_filename']) && !$val->isstring($post['sh_inv_template_filetype']) && !$val->isstring($post['sh_inv_template_filepath']))
				{
					$this->error_message['file'] = $Tr->translate('invoice_template_file_error');
					$error = 2;
				}
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function insert_reminder_template_data($post)
		{
			$clientid = $post['clients'];
			$res = new RemindersInvoiceTemplates();
			$res->clientid = $clientid;
			$res->title = $post['title'];
			$res->invoice_type = $post['invoice_type'];
			$res->template_type = $post['template_type'];
			$res->file_type = $post['sh_inv_template_filetype'];
			$res->file_path = $post['sh_inv_template_filepath'];
			$res->isdeleted = "0";
			$res->save();

			if(!empty($post['sh_inv_template_filename']))
			{
				$this->move_reminder_uploaded_template($res->id);
			}
		}

		public function update_reminder_template_data($post)
		{
			$cust = Doctrine::getTable('RemindersInvoiceTemplates')->findOneById($post['template_id']);
		
			if($cust)
			{
				if($post['change_file'] == '1')
				{
					//in case of change file(backup_old_file)
					$cust_array = $cust->toArray();
					$backup = true;
					
					if($cust_array)
					{
						$backup = $this->backup_old_reminder_template($cust_array['file_path']);
					}

					$cust->file_type = $post['sh_inv_template_filetype'];

					$this->move_reminder_uploaded_template($post['template_id']);
				}
				/*else 
				{
					$cust->file_path = "";
					//cleanup uploaded file if change template checkbox is not checked
					unlink(REMINDERINVOICE_TEMPLATE_PATH . '/' . $post['filepath']);
				}*/
				$cust->title = $post['title'];
				$cust->invoice_type = $post['invoice_type'];
				$cust->template_type = $post['template_type'];
				$cust->save();
			}
		}
		
		private function move_reminder_uploaded_template($inserted_file_id)
		{
			//move icon file to desired destination /public/icons/clientid/icon_db_id.ext
			//			$template_upload_path = 'brief_templates/' . $_SESSION['template_filepath'];
			//			$template_new_path = 'brief_templates/' . $clientid . '/' . $inserted_file_id . '.' . $_SESSION['sh_inv_template_filetype'];
		
			$template_upload_path = REMINDERINVOICE_TEMPLATE_PATH . '/' . $_SESSION['sh_inv_template_filepath'];
			$template_new_path = REMINDERINVOICE_TEMPLATE_PATH . '/' . $inserted_file_id . '.' . $_SESSION['sh_inv_template_filetype'];
		
			copy($template_upload_path, $template_new_path);
			unlink($template_upload_path);
		
			//@TODO: change this query with the one wich is not updating change_date & change_user!
			$update = Doctrine::getTable('RemindersInvoiceTemplates')->find($inserted_file_id);
			$update->file_path = $inserted_file_id . '.' . $_SESSION['sh_inv_template_filetype'];
			$update->save();
		}

		private function backup_old_reminder_template($old_file_path)
		{
			$template_old_path = REMINDERINVOICE_TEMPLATE_PATH . '/' .$old_file_path;

			$extension = explode('.', $old_file_path);
			$file_name = $extension[0];
			$file_ext = $extension[count($extension) - 1];
			$new_backup_path = $file_name . '_' . time() . '_bak.' . $file_ext;

			$template_new_backup_path = REMINDERINVOICE_TEMPLATE_PATH . '/' . $new_backup_path;
			//var_dump($template_new_backup_path); exit;
			return rename($template_old_path, $template_backup_new_path);
		}

	}

?>