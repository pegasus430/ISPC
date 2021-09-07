<?php

	class Application_Form_MmiTextBlocks extends Pms_Form {

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();
			if(!$val->isstring($post['text']))
			{
				$this->error_message['text'] = $Tr->translate('mmi_text_error_not_empty');
				$error = 1;
			}
			if($error == 0)
			{
				return true;
			}
			return false;
		}

		public function insert_data($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$ins = new MmiReceiptTxtBlocks();
			$ins->clientid = $clientid;
			$ins->text = $post['text'];
			$ins->isdeleted = "0";
			$ins->save();
		}

		public function update_data($item, $post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;


			$update = Doctrine_Core::getTable('MmiReceiptTxtBlocks')->findOneById($item);
			if($update)
			{
				$update->text = $post['text'];
				$update->save();
			}
		}

		public function remove_data($item)
		{
			$update = Doctrine_Core::getTable('MmiReceiptTxtBlocks')->findOneById($item);
			if($update)
			{
				$update->isdeleted = "1";
				$update->save();
			}
		}
		
		public function remove_multiple_data($post, $clientid)
		{
			if(count($post['select_mmi_txt']) > '0')
			{
				$upd = Doctrine_Query::create()
					->update('MmiReceiptTxtBlocks')
					->set('isdeleted', "1")
					->whereIn("id", $post['select_mmi_txt'])
					->andWhere("clientid = '" . $clientid . "'")
					->andWhere("isdeleted = '0'");
				$update = $upd->execute();
			}
		}

	}

?>