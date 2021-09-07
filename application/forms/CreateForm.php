<?

class Application_Form_CreateForm extends Pms_Form{


	public function InsertData($post)
	{

		$frm = new FbForms();
		$frm->formname = $post['formname'];
		$frm->clientid = $post['clientid'];
		$frm->patientid = $post['hdnpatientid'];
		$frm->save();

		$formid = $frm->id;

		if(!is_array($post['properties'])){

			return $formid;
		}

		foreach($post['properties'] as $key=>$val)
		{
			$frmfields = new FbFormFields();
			$frmfields->formid = $formid;
			$frmfields->fieldid = $key;
			$frmfields->type = $val['type'];
			$frmfields->label = $val['label'];
			$frmfields->columnno = $val['columnno'];
			$frmfields->linkedtable = $val['linkedTables'];
			$frmfields->linkedfield = $val['linkedFields'];
			$frmfields->isrequired = $val['required'];
			$frmfields->validator = $val['required_vars'];
			$frmfields->options = $val['values'];
			$frmfields->columns = $val['columns'];
			$frmfields->description = $val['description'];
			$frmfields->content = $val['content'];
			$frmfields->clientid = $post['clientid'];
			$frmfields->patientid = $post['hdnpatientid'];

			$frmfields->save();
		}

		return $formid;

	}


	public function UpdateData($post)
	{
		$frm = Doctrine_Core::getTable('FbForms')->find($_GET['frmid']);
		$frm->formname = $post['formname'];
		$frm->save();

		if(!is_array($post['properties'])){

			return;
		}

		foreach($post['properties'] as $key=>$val)
		{
			$fld = Doctrine_Core::getTable('FbFormFields')->findBy('fieldid',$key);
			$fldarr = $fld->toArray();

			if(count($fldarr)==0)
			{
				$frmfields = new FbFormFields();
				$frmfields->formid = $_GET['frmid'];
				$frmfields->fieldid = $key;
				$frmfields->type = $val['type'];
				$frmfields->label = $val['label'];
				$frmfields->columnno = $val['columnno'];
				$frmfields->linkedtable = $val['linkedTables'];
				$frmfields->linkedfield = $val['linkedFields'];
				$frmfields->isrequired = $val['required'];
				$frmfields->validator = $val['required_vars'];
				$frmfields->options = $val['values'];
				$frmfields->columns = $val['columns'];
				$frmfields->description = $val['description'];
				$frmfields->content = $val['content'];
				$frmfields->clientid = $post['clientid'];
				$frmfields->patientid = $post['hdnpatientid'];
				$frmfields->save();
			}
			else
			{
				$fup = Doctrine_Core::getTable('FbFormFields')->find($fldarr[0]['id']);
				$fup->label = $val['label'];
				$fup->columnno = $val['columnno'];
				$fup->linkedtable = $val['linkedTables'];
				$fup->linkedfield = $val['linkedFields'];
				$fup->isrequired = $val['required'];
				$fup->validator = $val['required_vars'];
				$fup->options = $val['values'];
				$fup->columns = $val['columns'];
				$fup->description = $val['description'];
				$fup->content = $val['content'];
				$fup->save();
			}
		}
	}
}

?>