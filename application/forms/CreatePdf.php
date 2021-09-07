<?

class Application_Form_CreatePdf extends Pms_Form{


	public function InsertData($post)
	{

		$frm = new PdfForms();
		$frm->pdfname = $post['pdfname'];
		$frm->formid = $post['formid'];
		$frm->name = $post['documentname'];
		$frm->version = $post['documentname'];
		$frm->dimension = $post['dimensions'];
		$frm->dimensionwidth = $post['altdimension']['width'];
		$frm->dimensionheight = $post['altdimension']['height'];
		$frm->noofpages = $post['noofpages'];
		$frm->header = $post['header'];
		$frm->footer = $post['footer'];
		$frm->headerheight = $post['headerheight'];
		$frm->footerheight = $post['footerheight'];
		$frm->save();

		$pdfid = $frm->id;

		if(!is_array($post['properties'])){

			return $formid;
		}

		foreach($post['properties'] as $key=>$val)
		{
				
			$frmfields = new PdfFields();
			$frmfields->pdfid = $pdfid;
			$frmfields->fieldelementid = $key;
			$frmfields->type = $val['type'];
			$frmfields->label = $val['label'];
			$frmfields->pageno = $val['pageno'];
			$frmfields->linkedtable = $val['linkedTables'];
			$frmfields->linkedfield = $val['linkedFields'];
			$frmfields->options = $val['values'];
			$frmfields->columns = $val['columns'];
			$frmfields->description = $val['description'];
			$frmfields->content = $val['content'];
			$frmfields->posx = $val['posx'];
			$frmfields->posy = $val['posy'];
			$frmfields->dimwidth = $val['dimwidth'];
			$frmfields->dimheight = $val['dimheight'];
			$frmfields->ishide = $val['hideifempty'];
			$frmfields->fieldid = $val['fieldid'];
			$frmfields->labelwidth = $val['labelwidth'];
			$frmfields->labelhide = $val['labelhide'];
			$frmfields->labelfont = $val['labelfont'];
			$frmfields->labelfontsize = $val['labelfontsize'];
			$frmfields->linethickness = $val['linethickness'];
			$frmfields->linelength = $val['linelength'];
			$frmfields->linecolor = $val['linecolor'];
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
			}else{

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

	public function generateCollection($post)
	{

		$fdarr = array();

		foreach($post['properties'] as $key=>$val)
		{
			$frmfields = array();
			$frmfields['fieldid'] = $key;
			$frmfields['type'] = $val['type'];
			$frmfields['label'] = $val['label'];
			$frmfields['pageno'] = $val['pageno'];
			$frmfields['linkedtable'] = $val['linkedTables'];
			$frmfields['linkedfield'] = $val['linkedFields'];
			$frmfields['options'] = $val['values'];
			$frmfields['columns'] = $val['columns'];
			$frmfields['description'] = $val['description'];
			$frmfields['content'] = $val['content'];
			$frmfields['posx'] = $val['posx'];
			$frmfields['posy'] = $val['posy'];
			$frmfields['dimwidth'] = $val['dimwidth'];
			$frmfields['dimheight'] = $val['dimheight'];
			$frmfields['ishide'] = $val['hideifempty'];
			$frmfields['labelwidth'] = $val['labelwidth'];
			$frmfields['labelhide'] = $val['labelhide'];
			$frmfields['labelfontsize'] = $val['labelfontsize'];
			$frmfields['linecolor'] = $val['linecolor'];
			$frmfields['linethickness'] = $val['linethickness'];
			$frmfields['linelength'] = $val['linelength'];
			$fdarr[] = $frmfields;
		}

		return $fdarr;


	}
}

?>