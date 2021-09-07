<?

class Application_Form_StoreForm extends Pms_Form{

	public function validate($post)
	{

		$fd = Doctrine_Core::getTable('FbFormFields')->findBy('formid',$_GET['frmid']);
		$fdarr = $fd->toArray();

		$valid = new Pms_Validation();
		$error = 0;

		foreach($fdarr as $key=>$val)
		{

			if($val['type']=='text') continue;

			if($val['isrequired']==1)
			{
				if(strlen($val['validator'])==0){
					$val['validator'] = 'Text';
				}
				if(is_array($post['field_'.$val['id']])) continue;
				if(!$valid->{$this->validators[$val['validator']]}|($post['field_'.$val['id']])){

					$this->error_message['field_'.$val['id']] = "Please provide correct values for ".$val['label'];
					$error++;
				}
			}

		}

		if($error>0){
			return false;
		}

		return true;
	}

	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');


		$fd = Doctrine_Core::getTable('FbFormFields')->findBy('formid',$_GET['frmid']);
		$fdarr = $fd->toArray();

		$epid = $_GET['id'];

		$this->editForm($post,$fdarr);

	}

	private function editform($post,$fdarr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$epid = $_GET['id'];

		foreach($fdarr as $key=>$val)
		{
			if(is_array($post['field_'.$val['id']])){
					
				$post['field_'.$val['id']] = serialize($post['field_'.$val['id']]);
			}

			$update = false;

			switch ($val['type'])
			{
				case 'text':

					break;
				case 'textarea':
					if(strlen($val['linkedtable'])>0)
					{
						$update = false;
					}
					break;
				case 'textbox':
					if(strlen($val['linkedtable'])>0)
					{
						$update = false;
					}
					break;
				case 'dropdown':
					$update = true;
					break;
				case 'checkbox':
					$update = true;
					break;
				case 'checkboxmatrix':
					$update = true;
					break;
				case 'radio':
					$update = true;
					break;
				case 'datetime':
					$update = true;
					break;
				case 'fileupload':
					break;
				case 'fbbutton':
					break;
			}


			if($update)
			{
				$q = Doctrine_Query::create()
				->select("*")
				->from('FbFieldValues')
				->where("fieldid= ?", $val['id'])
				->andWhere("formid= ?", $_GET['frmid'])
				->andWhere("patientid= ?", $epid)
				->andWhere("clientid= ?", $logininfo->clientid);
				$qe = $q->execute();
				$qarr = $qe->toArray();
					
				if(count($qarr)>0)
				{
					$q = Doctrine_Query::create()
					->update('FbFieldValues')
					->set('fieldvalue',"'".$post['field_'.$val['id']]."'")
					->where("fieldid= ?", $val['id'])
					->andWhere("formid= ?", $_GET['frmid'])
					->andWhere("patientid= ?", $epid)
					->andWhere("clientid= ?", $logininfo->clientid);
					$q->execute();
					
				} else {

					$fv = new FbFieldValues();
					$fv->fieldid = $val['id'];
					$fv->formid = $val['formid'];
					$fv->clientid = $logininfo->clientid;
					$fv->patientid = $epid;
					$fv->fieldvalue = $post['field_'.$val['id']];
					$fv->save();

				}
			}
		}

	}

	private function insertform($post,$fdarr)
	{
		$epid = $_GET['id'];


		$logininfo= new Zend_Session_Namespace('Login_Info');
		foreach($fdarr as $key=>$val)
		{
			if(strlen($val['linkedfield'])>0)
			{

					
			}else{
				$fv = new FbFieldValues();
				$fv->fieldid = $val['id'];
				$fv->formid = $val['formid'];
				$fv->clientid = $logininfo->clientid;
				$fv->patientid = $epid;
				if(is_array($post['field_'.$val['id']])){

					$post['field_'.$val['id']] = serialize($post['field_'.$val['id']]);
				}
				$fv->fieldvalue = $post['field_'.$val['id']];
				$fv->save();
			}

		}
	}
}

?>