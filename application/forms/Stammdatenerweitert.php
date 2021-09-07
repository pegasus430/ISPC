<?php

require_once("Pms/Form.php");

class Application_Form_Stammdatenerweitert extends Pms_Form
{
    
    protected $_model = 'Stammdatenerweitert';
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_Stammdatenerweitert';
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_nationality' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],
            
            'create_form_marital_status' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],            
        ],
    ];

    protected $_block_name_allowed_inputs =  array(
    
        "WlAssessment" => [
            'create_form_nationality' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
            
            'create_form_marital_status' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
            
        ],
    
        "PatientDetails" => [
            'create_form_nationality' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
            'create_form_marital_status' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
			//Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
        	'create_form_artificial_entries_exits' => [
        			//this are removed
        			'__removed' => [
        				//this have been introduced for updates from Contactform
        				'option_name',
        			],
        			//only this are allowed
        			'__allowed' => [],
        	]
        ],
    
        "MamboAssessment" => [
            'create_form_nationality' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
            'create_form_marital_status' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
            
        ],
    );
    //Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
    //#ISPC-2512PatientCharts
    protected $_client_options = null;
    //Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
    public function __construct($options = null) {
    
    	if ( ! empty($options['_client_options'])) {
    		$this->_client_options = $options['_client_options'];
    		unset($options['_client_options']);
    	}
    
    	//         parent::__construct(...$args);
    	parent::__construct($options);
    }
    
	public function validate($post)
	{

		$Tr = new Zend_View_Helper_Translate();
			
		$error=0;

		$val = new Pms_Validation();

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{

		$ernahrung = Pms_CommonData::arraytocommastring($post['ernahrung']);
		$orientierung = Pms_CommonData::arraytocommastring($post['orientierung']);
		$kunstliche = Pms_CommonData::arraytocommastring($post['kunstliche']);
		$ausscheidung = Pms_CommonData::arraytocommastring($post['ausscheidung']);


		$custs = Doctrine::getTable('Stammdatenerweitert')->findBy('ipid',$post['ipid']);
		if($custs)
		{
			$starr = $custs->toArray();
		}

			
		if(count($starr)>0)
		{
			$cust = Doctrine::getTable('Stammdatenerweitert')->find($starr[0]['id']);
		}
		else
		{
			$cust = new Stammdatenerweitert();
			$cust->ipid = $post['ipid'];

		}
		$cust->familienstand = $post['familienstand'];
		$cust->vigilanz = $post['vigilanz'];
		$cust->ernahrung = $ernahrung;
		$cust->kunstliche = $kunstliche;
		$cust->orientierung = $orientierung;
		$cust->ausscheidung = $ausscheidung;
		$cust->stastszugehorigkeit = $post['stastszugehorigkeit'];
		$cust->ausgepragte = $post['ausgepragte'];
		$cust->schmerzen = $post['schmerzen'];
		$cust->neuropat = $post['neuropat'];
		$cust->viszerale = $post['viszerale'];
		$cust->ubelkeit = $post['ubelkeit'];
		$cust->respiratorische = $post['respiratorische'];
		$cust->atemnot = $post['atemnot'];
		$cust->sprachstorung = $post['sprachstorung'];
		$cust->reizhusten = $post['reizhusten'];
		$cust->sprachlich = $post['sprachlich'];
		$cust->kognitiv = $post['kognitiv'];
		$cust->horprobleme = $post['horprobleme'];
		$cust->verschleimung = $post['verschleimung'];
		$cust->anderefree = $post['anderefree'];
		$cust->gastrointestinale = $post['gastrointestinale'];
		$cust->aszites = $post['aszites'];
		$cust->bluterbrechen = $post['bluterbrechen'];
		$cust->durchfall = $post['durchfall'];
		$cust->obstipation = $post['obstipation'];
		$cust->soor = $post['soor'];
		$cust->schluckstorungen = $post['schluckstorungen'];
		$cust->neurologische = $post['neurologische'];
		$cust->angst = $post['angst'];
		$cust->depression = $post['depression'];
		$cust->unruhe = $post['unruhe'];
		$cust->desorientierung = $post['desorientierung'];
		$cust->krampfanfalle = $post['krampfanfalle'];
		$cust->lahmungen = $post['lahmungen'];
		$cust->gangunsicherheit = $post['gangunsicherheit'];
		$cust->schwindel = $post['schwindel'];
		$cust->sensibilitatsstogg = $post['sensibilitatsstogg'];
		$cust->ulzerierende = $post['ulzerierende'];
		$cust->decubitus = $post['decubitus'];
		$cust->exulcerationen = $post['exulcerationen'];
		$cust->lymph_odeme = $post['lymph_odeme'];
		$cust->urogenitale = $post['urogenitale'];
		$cust->harnverhalt = $post['harnverhalt'];
		$cust->soziale = $post['soziale'];
		$cust->lebensqualitat = $post['lebensqualitat'];
		$cust->organisationsprob = $post['organisationsprob'];
		$cust->finanzprobleme = $post['finanzprobleme'];
		$cust->sonstiges = $post['sonstiges'];
		$cust->fatique = $post['fatique'];
		$cust->juckreiz = $post['juckreiz'];
		$cust->kachexie = $post['kachexie'];
		$cust->ethische = $post['ethische'];
		$cust->sozial_rechtliche = $post['sozial_rechtliche'];
		$cust->unterstutzungsbedarf = $post['unterstutzungsbedarf'];
		$cust->existentielle = $post['existentielle'];

		$cust->save();
		return $cust;


	}
	public function InsertStamdatenData($post)
	{


		if(!empty($post['patid'])){
			$custs = Doctrine::getTable('Stammdatenerweitert')->findBy('ipid',$post['ipid']);
			if($custs)
			{
				$starr = $custs->toArray();
			}
		}
			
		if(count($starr)>0)
		{
			$cust = Doctrine::getTable('Stammdatenerweitert')->find($starr[0]['id']);
		}
		else
		{
			$cust = new Stammdatenerweitert();
			$cust->ipid = $post['ipid'];

		}
		//get existing data
		$stam = new Stammdatenerweitert();
		$stamarr = $stam->getStammdatenerweitert($post['ipid']);
		$fieldvalue =  $post['chkval'];
		if($post['fldname'] == "familienstand"){
			$cust->familienstand = $post['chkval'];
		}
		if($post['fldname'] == "vigilanz"){
			$cust->vigilanz = $post['chkval'];
		}
		if($post['fldname'] == "ernahrung"){
			//explode strings
			$base = explode(",", $stamarr[0]['ernahrung']);	//values from db
			$compare = explode(",", $post['chkval']);		//values from post

			if($base[0] != 0){ //something is checked
				$diff['unchecked'] = array_merge(array_diff($base, $compare));
				//if diff unchecked count is 0 then something was added else something was ckecked

				if (count($diff['unchecked']) == 0) { // we have add => reverse diff
					unset($diff);
					$diff['checked'] = array_merge(array_diff($compare, $base));
				}
					
				if(count($diff['unchecked'])>0){
					$fieldvalue = $diff['unchecked'][0]."-0";
				} else {
					$fieldvalue = $diff['checked'][0]."-1";
				}
			} else { //nothing checked
				$fieldvalue = $post['chkval']."-1";
			}

			$cust->ernahrung = $post['chkval'];
		}
		if($post['fldname'] == "kunstliche"){
			//explode strings
			$base = explode(",", $stamarr[0]['kunstliche']);	//values from db
			$compare = explode(",", $post['chkval']);		//values from post

			if($base[0] != 0){ //something is checked
				$diff['unchecked'] = array_merge(array_diff($base, $compare));
				//if diff unchecked count is 0 then something was added else something was ckecked

				if (count($diff['unchecked']) == 0) { // we have add => reverse diff
					unset($diff);
					$diff['checked'] = array_merge(array_diff($compare, $base));
				}
					
				if(count($diff['unchecked'])>0){
					$fieldvalue = $diff['unchecked'][0]."-0";
				} else {
					$fieldvalue = $diff['checked'][0]."-1";
				}
			} else { //nothing checked
				$fieldvalue = $post['chkval']."-1";
			}
			$cust->kunstliche = $post['chkval'];
		}
		if($post['fldname'] == "kunstlichemore"){
			$cust->kunstlichemore = $post['chkval'];
		}
		if($post['fldname'] == "kunstliche" && $post['chkval'] == "0"){
			$cust->kunstlichemore = "";
		}




		if($post['fldname'] == "orientierung"){
			//explode strings
			$base = explode(",", $stamarr[0]['orientierung']);	//values from db
			$compare = explode(",", $post['chkval']);		//values from post

			if($base[0] != 0){ //something is checked
				$diff['unchecked'] = array_merge(array_diff($base, $compare));
				//if diff unchecked count is 0 then something was added else something was ckecked

				if (count($diff['unchecked']) == 0) { // we have add => reverse diff
					unset($diff);
					$diff['checked'] = array_merge(array_diff($compare, $base));
				}

				if(count($diff['unchecked'])>0){
					$fieldvalue = $diff['unchecked'][0]."-0";
				} else {
					$fieldvalue = $diff['checked'][0]."-1";
				}
			} else { //nothing checked
				$fieldvalue = $post['chkval']."-1";
			}
			$cust->orientierung = $post['chkval'];
		}
		if($post['fldname'] == "sprachlich"){
			$cust->sprachlich = $post['chkval'];
		}
		if($post['fldname'] == "kognitiv"){
			$cust->kognitiv = $post['chkval'];
		}
		if($post['fldname'] == "horprobleme"){
			$cust->horprobleme = $post['chkval'];
		}
		if($post['fldname'] == "ausscheidung"){
			//explode strings
			$base = explode(",", $stamarr[0]['ausscheidung']);	//values from db
			$compare = explode(",", $post['chkval']);		//values from post

			if($base[0] != 0){ //something is checked
				$diff['unchecked'] = array_merge(array_diff($base, $compare));
				//if diff unchecked count is 0 then something was added else something was ckecked

				if (count($diff['unchecked']) == 0) { // we have add => reverse diff
					unset($diff);
					$diff['checked'] = array_merge(array_diff($compare, $base));
				}
					
				if(count($diff['unchecked'])>0){
					$fieldvalue = $diff['unchecked'][0]."-0";
				} else {
					$fieldvalue = $diff['checked'][0]."-1";
				}
			} else { //nothing checked
				$fieldvalue = $post['chkval']."-1";
			}

			$cust->ausscheidung = $post['chkval'];
		}
		if($post['fldname'] == "stastszugehorigkeit"){
			$cust->stastszugehorigkeit = $post['chkval'];
		}
		if($post['fldname'] == "anderefree"){
			$cust->anderefree = $post['chkval'];
		}

		if ($post['fldname'] == "hilfsmittel") {
			//explode strings
			$base = explode(",", $stamarr[0]['hilfsmittel']);	//values from db
			$compare = explode(",", $post['chkval']);		//values from post

			if($base[0] != 0){ //something is checked
				$diff['unchecked'] = array_merge(array_diff($base, $compare));
				//if diff unchecked count is 0 then something was added else something was ckecked

				if (count($diff['unchecked']) == 0) { // we have add => reverse diff
					unset($diff);
					$diff['checked'] = array_merge(array_diff($compare, $base));
				}
					
				if(count($diff['unchecked'])>0){
					$fieldvalue = $diff['unchecked'][0]."-0";
				} else {
					$fieldvalue = $diff['checked'][0]."-1";
				}
			} else { //nothing checked
				$fieldvalue = $post['chkval']."-1";
			}


			$cust->hilfsmittel = $post['chkval'];
		}

		if($post['fldname'] == "wunsch"){
			//explode strings
			$base = explode(",", $stamarr[0]['wunsch']);	//values from db
			$compare = explode(",", $post['chkval']);		//values from post

			if($base[0] != 0){ //something is checked
				$diff['unchecked'] = array_merge(array_diff($base, $compare));
				//if diff unchecked count is 0 then something was added else something was ckecked

				if (count($diff['unchecked']) == 0) { // we have add => reverse diff
					unset($diff);
					$diff['checked'] = array_merge(array_diff($compare, $base));
				}
					
				if(count($diff['unchecked'])>0){
					$fieldvalue = $diff['unchecked'][0]."-0";
				} else {
					$fieldvalue = $diff['checked'][0]."-1";
				}
			} else { //nothing checked
				$fieldvalue = $post['chkval']."-1";
			}

			$cust->wunsch = $post['chkval'];
		}
		if($post['fldname'] == "wmore"){
			$cust->wunschmore = $post['chkval'];
		}
		if($post['fldname'] == "wunsch" && $post['chkval'] == "0"){
			$cust->wunschmore = "";
		}
			
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;



		$history = new BoxHistory();
		$history->ipid = $post['ipid'];
		$history->clientid = $clientid;
		$history->fieldname = $post['fldname'];
		$history->fieldvalue = $fieldvalue;
		$history->formid = $post['formid'];
		$history->save();


		$cust->save();
		return $cust;


	}
	public function UpdateData($post)
	{


	}
	 

	
	//familienstand = Marital_status
	public function create_form_marital_status($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_marital_status");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend($this->translate('Marital status:'));
	    $subform->setAttrib("class", "label_same_size multipleCheckboxes inlineEdit " . __FUNCTION__);
	   
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    
	    
	    $marital_statuses = Stammdatenerweitert::getFamilienstandfun();
	    
	    $subform->addElement('radio', 'familienstand', array(
	        'label'      => null,//$this->translate('enable/disable module'),
	        'separator'  => " ", //'&nbsp;',
	        'required'   => false,
	        'multiOptions' =>  $marital_statuses,
	        'value' => is_array($values) && isset($values['familienstand']) ? $values['familienstand'] : $values,
	        'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        )
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	public function save_form_marital_status($ipid =  '' , $data = array())
	{
	    //radio
	    if(empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    $entity = new Stammdatenerweitert();
	    
	    $newEntity = $entity->findOrCreateOneBy('ipid', $ipid, $data);
	    
	    $this->_save_box_History($ipid, $newEntity, 'familienstand', 'grow16', 'radio');
	    
	    return $newEntity;
	}
	
	//stastszugehorigkeit
	public function create_form_nationality($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_nationality");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend($this->translate('Nationality:'));
	    $subform->setAttrib("class", " multipleCheckboxes inlineEdit {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    
	    $nationalitys = Stammdatenerweitert::getStastszugehorigkeitfun();
	    // change from radio to select - TODO-1890
	    $subform->addElement('select', 'stastszugehorigkeit', array(
	        'label'      => $this->translate('stastszugehorigkeit'),
	        'separator'  => " ",// '&nbsp;',
	        'required'   => false,
	        'multiOptions'=> $nationalitys,
	        'value' => $values['stastszugehorigkeit'],
	        'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "1") {$(".nationality-free1", $(this).parents(\'table\')).hide().val(\'\');} else if (this.value == "2") {$(".nationality-free1", $(this).parents(\'table\')).show();}',
	    ));
	    
	    $display = ($values['stastszugehorigkeit'] != 2 ? 'display:none' : null);
	    
	    $subform->addElement('text', 'anderefree', array(
	        'label'        => "",
	        'value'        => $values['anderefree'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => $this->translate('freetext'),
	        'decorators'   =>   array(
	            'ViewHelper',
	            array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'style'        => $display,
	        'class'        => 'comments nationality-free1',
	    ));
	    
	    
	    
	    // New fields -  TODO-1890
	    $subform->addElement('select', '2ndstastszugehorigkeit', array(
	        'label'      => $this->translate('2ndstastszugehorigkeit'),
	        'separator'  => " ",// '&nbsp;',
	        'required'   => false,
	        'multiOptions'=> $nationalitys,
	        'value' => $values['2ndstastszugehorigkeit'],
	        'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "1") {$(".nationality-free2", $(this).parents(\'table\')).hide().val(\'\');} else if (this.value == "2") {$(".nationality-free2", $(this).parents(\'table\')).show();}',
	    ));
	    
	    $display_second = ($values['2ndstastszugehorigkeit'] != 2 ? 'display:none' : null);
	    
	    $subform->addElement('text', '2ndanderefree', array(
	        'label'        => "",
	        'value'        => $values['2ndanderefree'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => $this->translate('freetext'),
	        'decorators'   =>   array(
	            'ViewHelper',
	            array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'style'        => $display_second,
	        'class'        => 'comments  nationality-free2',
	    ));
	    
	    
	    
	    $subform->addElement('text', 'dolmetscher', array(
	        'label'        => $this->translate('dolmetscher'),
	        'value'        => $values['dolmetscher'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => '',
	        'decorators'   =>   array(
	            'ViewHelper',
	            array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'        => 'comments',
	    ));
	    
	    // END: TODO-1890
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	public function save_form_nationality($ipid =  '' , $data = array())
	{
	    //this is radio
	    if(empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    $entity = new Stammdatenerweitert();	    
	    
	    $newEntity = $entity->findOrCreateOneBy('ipid', $ipid, $data);
	     
	    $this->_save_box_History($ipid, $newEntity, 'stastszugehorigkeit', 'grow17', 'radio');
	    $this->_save_box_History($ipid, $newEntity, 'anderefree', 'grow17', 'text');
	    $this->_save_box_History($ipid, $newEntity, '2ndstastszugehorigkeit', 'grow17', 'radio');
	    $this->_save_box_History($ipid, $newEntity, '2ndanderefree', 'grow17', 'text');
	    $this->_save_box_History($ipid, $newEntity, 'dolmetscher', 'grow17', 'text');
	     
	    return $newEntity;
	        
	}
	
	
	
	
	
	public function create_form_vigilanz($values =  array() , $elementsBelongTo = null)
	{
	    
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	    
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_vigilanz");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend($this->translate('Vigilance:'));
	    $subform->setAttrib("class", "label_same_size_100 multipleCheckboxes inlineEdit " . __FUNCTION__);
	
	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	
	    $vigilances = Stammdatenerweitert::getVigilanzfun(); 
	
	    $subform->addElement('radio', 'vigilanz', array(
	        'label'      => null,//$this->translate('enable/disable module'),
	        'separator'  => " ", //'&nbsp;',
	        'required'   => false,
	        'multiOptions'=> $vigilances,
		    'value' => $values,
	        
	        'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        )
	    ));
	
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	}
	public function save_form_vigilanz($ipid =  '' , $data = array())
	{
   	    //this is radio
	    if(empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    $entity = new Stammdatenerweitert();
	    
	    $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
	    
	    $this->_save_box_History($ipid, $newEntity, 'vigilanz', 'grow18', 'radio');
	     
	    return $newEntity;
    }
	

    //kunstliche
    public function create_form_artificial_exits($values =  array() , $elementsBelongTo = null)
    {
        
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
        
        $this->mapSaveFunction(__FUNCTION__ , "save_form_artificial_exits");
        
        
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend($this->translate('Artificial exits:'));
        $subform->setAttrib("class", "label_same_size_auto multipleCheckboxes inlineEdit " . __FUNCTION__);
    
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
        $Kunstlichefun = Stammdatenerweitert::getKunstlichefun();
        
        $subform->addElement('multiCheckbox', 'kunstliche', array(
            'label'      => null,
            'separator'  => " ", //'&nbsp;',
            'required'   => false,
            'filters'      => array('StringTrim'),
            'multiOptions'=> $Kunstlichefun,
            'value' => $values['kunstliche'],
            'decorators' =>   array(
                'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if (this.value == "5" && this.checked) { $(".comments", $(this).parents("table")).show();} else if (this.value == "5" && !this.checked) {$(".comments", $(this).parents("table")).hide();}',
        ));

        
        $display = ! in_array('5', $values['kunstliche']) ? 'display:none' : null;
        $subform->addElement('text', 'kunstlichemore', array(
            'label'        => null,
            'value'        => $values['kunstlichemore'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'placeholder'  => $this->translate('freetext'),
            'decorators'   => array(
                'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'comments', 'style' => $display )),
            ),
            
        ));
        
        return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    public function save_form_artificial_exits($ipid =  '' , $data = array())
    {
        //this is cb
        if(empty($ipid)) {
            return;
        }
        
        $saveData = [];
        
        $saveData['kunstliche'] = implode(',' , $data['kunstliche']);
        
        if (isset($data['kunstlichemore'])) {
            $saveData['kunstlichemore'] = $data['kunstlichemore'];
        }

        //ISPC-2807 Lore 24.02.2021
        $save_toVerlauf = $this->save_artificial_exits_to_Verlauf($data);
        
        $entity = new Stammdatenerweitert();
        
        $newEntity = $entity->findOrCreateOneBy('ipid', $ipid, $saveData);

        $this->_save_box_History($ipid, $newEntity, 'kunstliche', 'grow22', 'checkbox');
        $this->_save_box_History($ipid, $newEntity, 'kunstlichemore', 'grow22', 'text');
        
        return $newEntity;
    }
    
    
    
    //ausscheidung
    public function create_form_excretion($values =  array() , $elementsBelongTo = null)
    {
        
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
        
        $this->mapSaveFunction(__FUNCTION__ , "save_form_excretion");
        
        
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend($this->translate('Excretion:'));
        $subform->setAttrib("class", "label_same_size_100 multipleCheckboxes inlineEdit " . __FUNCTION__);
    
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        $excretions = Stammdatenerweitert::getAusscheidungfun();
        
        
        $subform->addElement('multiCheckbox', 'ausscheidung', array(
            'label'      => null,
            'separator'  => " ", //'&nbsp;',
            'required'   => false,
            'multiOptions'=> $excretions,
            'value' => $values,
            'decorators'   => array(
                'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
    
        return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    public function save_form_excretion($ipid =  '' , $data = array())
    {
        //this is cb ausscheidung
        if(empty($ipid)) {
            return;
        }
        
        $saveData = [ 'ausscheidung' =>  implode(',' , $data['ausscheidung'])];
        
        $entity = new Stammdatenerweitert();
        

        $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $saveData);
        
        $this->_save_box_History($ipid, $newEntity, 'ausscheidung', 'grow21', 'checkbox');
         
        return $newEntity;
    }
    
    
    
    private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $checkbox_or_radio_or_text) 
    {
        
        $newModifiedValues = $newEntity->getLastModified();
        
        if (isset($newModifiedValues[$fieldname])) {
            $oldValues = $newEntity->getLastModified(true);
            
            $add_sufix = "";
            $remove_sufix = "";
            $added = [];
            $removed = [];
            
            switch ($checkbox_or_radio_or_text) {
                
                case  "checkbox" :
                    
                    $new_values = explode(',', $newModifiedValues[$fieldname]);
                    $old_values = explode(',', $oldValues[$fieldname]);
                    
                    $added = array_diff($new_values, $old_values);
                    $removed = array_diff($old_values , $new_values);
                    
                    $add_sufix = "-1";
                    $remove_sufix = "-0";
                    
                    break;
                
                case "radio" :
                case "text" :
                default:
                    
                    $new_values = $newModifiedValues[$fieldname];
                    $old_values = $oldValues[$fieldname];
                    
                    $added = [$new_values];
                    
                    break;
            }
            
            $history = [];
        
            if ( ! empty($added)) {
                foreach ($added as $val) {
                    $history[] = [
                        'ipid' => $ipid,
                        'clientid' => $this->logininfo->clientid,
                        'formid' => $formid,
                        'fieldname' => $fieldname,
                        'fieldvalue' => $val . $add_sufix,
                    ];
                }
            }
        
        
            if ( ! empty($removed)) {
                foreach ($removed as $val) {
                    $history[] = [
                        'ipid' => $ipid,
                        'clientid' => $this->logininfo->clientid,
                        'formid' => $formid,
                        'fieldname' => $fieldname,
                        'fieldvalue' => $val . $remove_sufix,
                    ];
                }
            }
        
            if ( ! empty($history)) {
                $coll = new Doctrine_Collection("BoxHistory");
                $coll->fromArray($history);
                $coll->save();
            }
        }
        
    }
    
    
    
    //Hilfsmittel = aid
    public function create_form_hilfsmittel($values =  array() , $elementsBelongTo = null)
    {
    
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
    
        $this->mapSaveFunction(__FUNCTION__ , "save_form_hilfsmittel");
    
    
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend('patient_aid');
        $subform->setAttrib("class", "label_same_size_100 multipleCheckboxes inlineEdit " . __FUNCTION__);
    
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        $optionsCb = Stammdatenerweitert::getOptionsHilfsmittel();
    
        
    
        $subform->addElement('multiCheckbox', 'hilfsmittel', array(
            'label'      => null,
            'separator'  => " ", //'&nbsp;',
            'required'   => false,
            'multiOptions'=> $optionsCb,
            'value' => $values['hilfsmittel'],
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
    

        
        $subform->addElement('checkbox', 'pumps', array(
            'checkedValue'    => '1',
            'uncheckedValue'  => '0',
            'label'      => 'pumps',
            'required'   => false,
            'value' => $values['PatientMoreInfo']['pumps'],
            'decorators'   => array(
                'ViewHelper',
                array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'belongsTo' => 'PatientMoreInfo',            	
        ));
        
        
        
        return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    public function save_form_hilfsmittel($ipid =  '' , $data = array())
    {
        //this is cb
        if(empty($ipid)) {
            return;
        }
    
        $saveData = [];
        
        $saveData['hilfsmittel'] =  implode(',' , $data['hilfsmittel']);
    
        $entity = new Stammdatenerweitert();
        
        if (isset($data['pumps'])) {
            //this is from another model
            $entityMoreInfo = new PatientMoreInfo;
            $newEntityMoreInfo = $entityMoreInfo->findOrCreateOneBy('ipid', $ipid, ['pumps' => $data['pumps']]);
            
            $this->_save_box_History($ipid, $newEntityMoreInfo, 'pumps', 'grow24', 'checkbox');
            
            
        }
        
        $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $saveData);
        
        $this->_save_box_History($ipid, $newEntity, 'hilfsmittel', 'grow24', 'checkbox');
         
        return $newEntity;
        
        
    }
    
    
    //ernahrung = nutrition
    public function create_form_ernahrung($values =  array() , $elementsBelongTo = null)
    {
    
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
    
        $this->mapSaveFunction(__FUNCTION__ , "save_form_ernahrung");
    
    
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend('nutrition');
        $subform->setAttrib("class", "label_same_size_100 multipleCheckboxes inlineEdit " . __FUNCTION__);
    
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        $optionsCb = Stammdatenerweitert::getErnahrungfun();
    
    
        $subform->addElement('multiCheckbox', 'ernahrung', array(
            'label'      => null,
            'separator'  => " ", //'&nbsp;',
            'required'   => false,
            'multiOptions'=> $optionsCb,
            'value' => $values['ernahrung'],
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
    
        
//         if (!empty($values)) dd($values, PatientMoreInfo);
        
        $subform->addElement('checkbox', 'peg', array(
            'checkedValue'    => '1',
            'uncheckedValue'  => '0',
            'label'      => 'peg',
            'required'   => false,
            'value' => $values['PatientMoreInfo']['peg'],
            'decorators'   => array(
                'ViewHelper',
                array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'belongsTo' => 'PatientMoreInfo',
            'onChange' => 'if (this.checked) {$("input[name*=\'pegmore\']", $(this).parents(\'table\')).show();} else {$("input[name*=\'pegmore\']", $(this).parents(\'table\')).hide().val(\'\');}',
            
        ));
        $display = ($values['PatientMoreInfo']['peg'] == 0 ? 'display:none' : null);
        $subform->addElement('text', 'pegmore', array(
            'label'        => 'Ablauf PEG:',
            'value'        => $values['PatientMoreInfo']['pegmore'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            //             'placeholder'  => $this->translate('portmore'),
            'decorators'   =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'style'=>"vertical-align:bottom")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
            ),
            'style'        => $display,
            'class'        => 'comments',
        ));
        
        $subform->addElement('checkbox', 'port', array(
            'checkedValue'    => '1',
            'uncheckedValue'  => '0',
            'label'      => 'port',
            'required'   => false,
            'value' => $values['PatientMoreInfo']['port'],
            'decorators'   => array(
                'ViewHelper',
                array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'belongsTo' => 'PatientMoreInfo',
            'onChange' => 'if (this.checked) {$("input[name*=\'portmore\']", $(this).parents(\'table\')).show();} else {$("input[name*=\'portmore\']", $(this).parents(\'table\')).hide().val(\'\');}',
            
        ));
        
         
        $display = ($values['PatientMoreInfo']['port'] == 0 ? 'display:none' : null);
         
        $subform->addElement('text', 'portmore', array(
            'label'        => 'Ablauf Port:',
            'value'        => $values['PatientMoreInfo']['portmore'],
            'required'     => false,
            'filters'      => array('StringTrim'),
//             'placeholder'  => $this->translate('portmore'),
            'decorators'   =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'style'=>"vertical-align:bottom")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
            ),
            'style'        => $display,
            'class'        => 'comments',
        ));

        
        $subform->addElement('checkbox', 'zvk', array(
            'checkedValue'    => '1',
            'uncheckedValue'  => '0',
            'label'      => 'zvk',
            'required'   => false,
            'value' => $values['PatientMoreInfo']['zvk'],
            'decorators'   => array(
                'ViewHelper',
                array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'belongsTo' => 'PatientMoreInfo',
        ));
        
        
        $subform->addElement('checkbox', 'magensonde', array(
            'checkedValue'    => '1',
            'uncheckedValue'  => '0',
            'label'      => 'magensonde',
            'required'   => false,
            'value' => $values['PatientMoreInfo']['magensonde'],
            'decorators'   => array(
                'ViewHelper',
                array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'belongsTo' => 'PatientMoreInfo',
        ));
        
        
        
        return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    public function save_form_ernahrung($ipid =  '' , $data = array())
    {
        //this is cb
        if(empty($ipid)) {
            return;
        }
    
        

        if (isset($data['magensonde'])) {
            //this is from another model
            $entityMoreInfo = new PatientMoreInfo;
            $newEntityMoreInfo = $entityMoreInfo->findOrCreateOneBy('ipid', $ipid, $data);
            
            $this->_save_box_History($ipid, $newEntityMoreInfo, 'peg', 'grow20', 'text');
            $this->_save_box_History($ipid, $newEntityMoreInfo, 'pegmore', 'grow20', 'text');
            $this->_save_box_History($ipid, $newEntityMoreInfo, 'port', 'grow20', 'text');
            $this->_save_box_History($ipid, $newEntityMoreInfo, 'portmore', 'grow20', 'text');
            $this->_save_box_History($ipid, $newEntityMoreInfo, 'zvk', 'grow20', 'text');
            $this->_save_box_History($ipid, $newEntityMoreInfo, 'zvkmore', 'grow20', 'text');
            $this->_save_box_History($ipid, $newEntityMoreInfo, 'magensonde', 'grow20', 'text');
            $this->_save_box_History($ipid, $newEntityMoreInfo, 'magensondemore', 'grow20', 'text');
            
            
        }
        
        $saveData = [];
        
        $saveData['ernahrung'] =  implode(',' , $data['ernahrung']);
    
        $entity = new Stammdatenerweitert();
        
        $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $saveData);
        
        $this->_save_box_History($ipid, $newEntity, 'ernahrung', 'grow20', 'checkbox');
         
        return $newEntity;
        
        
        
        
        
    }
    
    
    //wunsch = wish
    public function create_form_wunsch($values =  array() , $elementsBelongTo = null)
    {
    
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
    
        $this->mapSaveFunction(__FUNCTION__ , "save_form_wunsch");
    
    
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend('Patient wish');
        $subform->setAttrib("class", "label_same_size_100 multipleCheckboxes inlineEdit " . __FUNCTION__);
    
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        $optionsCb = Stammdatenerweitert::getOptionsWunsch();
    
        $subform->addElement('multiCheckbox', 'wunsch', array(
            'label'      => null,
            'separator'  => " ", //'&nbsp;',
            'required'   => false,
            'multiOptions'=> $optionsCb,
            'value' => $values['wunsch'],
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            
            'onChange' => 'if (this.value == "13" && this.checked) { $(".comments", $(this).parents("table")).show();} else if (this.value == "13" && !this.checked) {$(".comments", $(this).parents("table")).hide();}',
            
        ));
    
        
        
        $display = ! in_array('13', $values['wunsch']) ? 'display:none' : null;
        $subform->addElement('text', 'wunschmore', array(
            'label'        => null,
            'value'        => $values['wunschmore'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'placeholder'  => $this->translate('freetext'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'comments', 'style' => $display )),
            ),
        
        ));
        
        
        
        
        
        
        
        
        
        return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    public function save_form_wunsch($ipid =  '' , $data = array())
    {
        //this is cb
        if(empty($ipid)) {
            return;
        }
    
        $saveData = [];
        
        $saveData['wunsch'] =  implode(',' , $data['wunsch']);
        $saveData['wunschmore'] = $data['wunschmore'];
    
        $entity = new Stammdatenerweitert();
        
        $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $saveData);
        
        $this->_save_box_History($ipid, $newEntity, 'wunsch', 'grow25', 'checkbox');
         
        return $newEntity;
        
        
    }
    
    
    
    
    //wunsch = wish
    public function create_form_orientierung($values =  array() , $elementsBelongTo = null)
    {
    
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
    
        $this->mapSaveFunction(__FUNCTION__ , "save_form_orientierung");
    
    
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend('orientation');
        $subform->setAttrib("class", "label_same_size_100 multipleCheckboxes inlineEdit " . __FUNCTION__);
    
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        $optionsCb1 = Stammdatenerweitert::getOrientierungfun();
        $subform->addElement('multiCheckbox', 'orientierung', array(
            'label'      => null,
            'separator'  => " ", //'&nbsp;',
            'required'   => false,
            'multiOptions'=> $optionsCb1,
            'value' => $values['orientierung'],
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));        
    
        $optionsCb2 = Stammdatenerweitert::getOrientierungfun2();
        
        foreach ($optionsCb2 as $cb => $tr) {
        	$subform->addElement('checkbox', $cb, array(
        		'checkedValue'    => '1',
        		'uncheckedValue'  => '0',
        	    'label'      => $tr,
        	    'required'   => false,
        	    'value' => in_array($cb, $values['orientierung']) ? 1 : 0,
        	    'decorators'   => array(
        	        'ViewHelper',
        	    	array('Label', array('placement'=> 'IMPLICIT_APPEND')),
        	        array('Errors'),
        	        array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
        	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'group_communication_limited')),
        	    ),
        	));
        	
        	
        }
        
        
        return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    public function save_form_orientierung($ipid =  '' , $data = array())
    {
        //this is cb
        if(empty($ipid)) {
            return;
        }
        
        $group_communication_limited = Stammdatenerweitert::getOrientierungfun2();
        /*      
        foreach ($group_communication_limited as $k => $tr) {
            if (($key = array_search($k, $data['orientierung'])) !== false) {
                unset($data['orientierung'][$key]);
                $data[$k] = 1;
            } else {
                $data[$k] = 0;
            }            
        }
        */

        
        $data['orientierung'] =  implode(',' , $data['orientierung']);
    
        $entity = new Stammdatenerweitert();
        
        $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
        
        $this->_save_box_History($ipid, $newEntity, 'orientierung', 'grow19', 'checkbox');
        
        foreach ($group_communication_limited as $k => $tr) {
            $this->_save_box_History($ipid, $newEntity, $k, 'grow19', 'text');
        }
         
        return $newEntity;
        
        
    }    
    
    //künstliche Zugänge - Ausgänge ISPC-2508 Carmen 23.01.2020
	//Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
    public function create_form_artificial_entries_exits($values =  array() , $elementsBelongTo = null)
    {
    
    	$this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
    
    	$this->mapSaveFunction(__FUNCTION__ , "save_form_artificial_entries_exits");
    
    
    	$subform = new Zend_Form_SubForm();
    	$subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
    	$subform->setLegend($this->translate('Artificial entries exits:'));
    	$subform->setAttrib("class", "label_same_size_auto multipleCheckboxes inlineEdit " . __FUNCTION__);
    
    
    	if ( ! is_null($elementsBelongTo)) {
    		$subform->setOptions(array(
    				'elementsBelongTo' => $elementsBelongTo
    		));
    	} elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
    		$subform->setOptions(array(
    				'elementsBelongTo' => $elementsBelongTo
    		));
    	}
    	
    	$client_artificial_select = array();
    	$client_artificial_select[''] = self::translate('entries_exits');
    	
    	$client_artificial_entries = array();
    	$client_artificial_exits = array();
    	
    	foreach($this->_client_options as $lrow)
    	{
    		if($lrow['type'] == 'entry')
    		{
    			$client_artificial_entries[$lrow['id']] = $lrow['name'];
    		}
    		else
    		{
    			$client_artificial_exits[$lrow['id']] = $lrow['name'];
    		}
    		$client_options[$lrow['id']] = $lrow;
    			
    	}
    	if(!empty($client_artificial_entries))
    	{
    		//$client_artificial_select['en'] = self::translate('entries');
    		foreach($client_artificial_entries as $kr => $vr)
    		{
    			$client_artificial_select['artificial_entries'][$kr] = $vr;
    		}
    	}
    	
    	if(!empty($client_artificial_exits))
    	{
    		//$client_artificial_select['ex'] = self::translate('exits');
    		foreach($client_artificial_exits as $kr => $vr)
    		{
    			$client_artificial_select['artificial_exits'][$kr] = $vr;
    		}
    	}
    	
	    //var_dump($client_options); exit;
    	
    	/* $subform->addElement('text', 'fixdatepicker', array(
    			'value' => '',
    			//'readonly' => true,
    			'decorators' =>   array(
    					'ViewHelper',
    					array('Errors'),
    					array(array('data' => 'HtmlTag'), array('tag' => 'td', "style" => "border: 0px;")),
    					//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    			),
    			style => "height:0px; top:-50px; position:absolute",
    	)); */
    	
    	$subform->addElement('hidden', 'id', array(
    			'value' => $values['id'] != '' ? $values['id'] : '',
    			'readonly' => true,
    			'decorators' =>   array(
    					'ViewHelper',
    					array('Errors'),
    					array(array('data' => 'HtmlTag'), array('tag' => 'td', "style" => "border: 0px;")),
    					//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    			),
    	));
    	//ISPC-2508 Carmen 20.05.2020 new design
    	if($values['action'] != 'remove' || $values['isremove'] == '1')
    	{
	    	$subform->addElement('select', 'option_id', array(
	    			'label' 	   => $values['option_id'] ? ($client_options[$values['option_id']]['type'] == 'entry') ? self::translate('artificial_entries') : self::translate('artificial_exits') : self::translate('entries_exits'),
	    			'multiOptions' => $client_artificial_select,
	    			'value'        => $values['option_id'],
	    			'required'     => true,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators' =>   array(
	    					'ViewHelper',
	    					array('Errors'),
	    					array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	    					array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	    			),
	    			
	    			'onChange' => 'if(clientSettings(this.value, ' . Zend_Json::encode($client_options) . ').localization_available == "yes") {$(".localization_available", $(this).parents(\'table\')).show(); $(".option_name", $(this).parents(\'table\')).val(clientSettings(this.value, ' . Zend_Json::encode($client_options) . ').name); } else {$(".localization_available", $(this).parents(\'table\')).hide().val(\'\'); $(".option_name", $(this).parents(\'table\')).val(clientSettings(this.value, ' . Zend_Json::encode($client_options) . ').name); }' ,
	    	));
    	}
    	else 
    	{
    		$subform->addElement('text', 'saved_option_name', array(
    			'label' 	   => $values['option_id'] ? ($client_options[$values['option_id']]['type'] == 'entry') ? self::translate('artificial_entries') : self::translate('artificial_exits') : self::translate('entries_exits'),
    			'value' => $client_options[$values['option_id']]['name'],
    			'readonly' => false,
    			'attribs'    => array('disabled' => 'disabled'),
    			'decorators' =>   array(
    					'ViewHelper',
    					array('Errors'),
    					array(array('data' => 'HtmlTag'), array('tag' => 'td', "style" => "border: 0px;")),
    					array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    			),
    	));
    	}
    	//--
    	$subform->addElement('hidden', 'option_name', array(
    			'value' => '',
    			'readonly' => true,
    			'decorators' =>   array(
    					'ViewHelper',
    					array('Errors'),
    					array(array('data' => 'HtmlTag'), array('tag' => 'td', "style" => "border: 0px;")),
    					//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    			),
    			'class' => 'option_name'
    	));
    	//ISPC-2508 Carmen 20.05.2020 new design
    	if($values['action'] != 'remove' || $values['isremove'] == '1')
    	{
	    	$subform->addElement('text', 'option_date', array(
	    			'label'        => self::translate('artificial_option_date'),
	    			'value'        => ! empty($values['option_date']) ? date('d.m.Y', strtotime($values['option_date'])) : date('d.m.Y'),
	    			'required'     => true,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'class'        => 'date option_date',
	    			'decorators' =>   array(
	    					'ViewHelper',
	    					array('Errors'),
	    					array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
	    					array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	    					array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
	    			),
	    		  
	    	));
	    	
	    	$option_time = ! empty($values['option_date']) ? date('H:i:s', strtotime($values['option_date'])) : date("H:i");
	    	$subform->addElement('text', 'option_time', array(
	    			//'label'        => self::translate('clock:'),
	    			'value'        => $option_time,
	    			'required'     => true,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'class'        => 'time option_time',
	    			'decorators' =>   array(
	    					'ViewHelper',
	    					array('Errors'),
	    					array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
	    					//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	    					array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	    			),
	    	));
    	}
    	else 
    	{
    		$subform->addElement('text', 'option_date', array(
    				'label'        => self::translate('artificial_option_date'),
    				'value'        => ! empty($values['option_date']) ? date('d.m.Y', strtotime($values['option_date'])) : date('d.m.Y'),
    				//'required'     => true,
    				'filters'      => array('StringTrim'),
    				'validators'   => array('NotEmpty'),
    				'attribs'    => array('disabled' => 'disabled'),
    				'class'        => 'option_date',
    				'decorators' =>   array(
    						'ViewHelper',
    						array('Errors'),
    						array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
    						array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    				),
    				 
    		));
    		
    		$option_time = ! empty($values['option_date']) ? date('H:i:s', strtotime($values['option_date'])) : date("H:i");
    		$subform->addElement('text', 'option_time', array(
    				//'label'        => self::translate('clock:'),
    				'value'        => $option_time,
    				'required'     => true,
    				'filters'      => array('StringTrim'),
    				'validators'   => array('NotEmpty'),
    				'attribs'    => array('disabled' => 'disabled'),
    				'class'        => 'time option_time',
    				'decorators' =>   array(
    						'ViewHelper',
    						array('Errors'),
    						array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
    						//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    				),
    		));
    	}
    	//--
    	//ISPC-2508 added for charts Carmen 23.04.2020 and new design
    	if((values['id'] != '' && $values['remove_date'] != '0000-00-00 00:00:00' && $values['isremove'] == '1') || $values['action'] == 'remove')
    	{
    		$subform->addElement('text', 'remove_date', array(
    				'label'        => $values['subaction'] == 'remove' ? self::translate('artificial_remove_date') : ($values['subaction'] == 'refresh' ? self::translate('artificial_refresh_date') : ''),
    				'value'        => (! empty($values['remove_date']) && $values['remove_date'] != '0000-00-00 00:00:00') ? date('d.m.Y', strtotime($values['remove_date'])) : date('d.m.Y'),
    				'required'     => true,
    				'filters'      => array('StringTrim'),
    				'validators'   => array('NotEmpty'),
    				'class'        => 'date option_date',
    				'decorators' =>   array(
    						'ViewHelper',
    						array('Errors'),
    						array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
    						array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    				),
    		
    		));
    		 
    		$remove_time = (! empty($values['remove_date']) && $values['remove_date'] != '0000-00-00 00:00:00') ? date('H:i:s', strtotime($values['remove_date'])) : date("H:i");
    		$subform->addElement('text', 'remove_time', array(
    				//'label'        => self::translate('clock:'),
    				'value'        => $remove_time,
    				'required'     => true,
    				'filters'      => array('StringTrim'),
    				'validators'   => array('NotEmpty'),
    				'class'        => 'time option_time',
    				'decorators' =>   array(
    						'ViewHelper',
    						array('Errors'),
    						array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
    						//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    				),
    		));
    	}
    	//--
    	if(values['option_id'] && $client_options[$values['option_id']]['localization_available'] == 'yes')
    	{
	    	$subform->addElement('text',  'option_localization', array(
	    			'label'        => self::translate('artificial_option_localization'),
	    			'value'        => ! empty($values['option_localization']) ? $values['option_localization'] : '',
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators' => array(
	    					'ViewHelper',
	    					array('Errors'),
	    					array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
	    					array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	    					array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'localization_available',)),
	    			),
	    			'class' => 'localization_available loc_input',
	    	));
    	}
    	else 
    	{
    		//this is necessary if changed from an option without localization to one that has
    		$subform->addElement('text',  'option_localization', array(
    				'label'        => self::translate('artificial_option_localization'),
    				'value'        => ! empty($values['option_localization']) ? $values['option_localization'] : '',
    				'filters'      => array('StringTrim'),
    				'validators'   => array('NotEmpty'),
    				'decorators' => array(
    						'ViewHelper',
    						array('Errors'),
    						array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
    						array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display: none;', 'class' => 'localization_available',)),
    				),
    				'class' => 'localization_available loc_input',
    		));
    	}
    
    	return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    
	//Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
    public function save_form_artificial_entries_exits($ipid =  '' , $data = array())
    {
    	
    	//this is cb
    	if(empty($ipid)) {
    		return;
    	}
    	
    	$model = 'PatientArtificialEntriesExits'; //ISPC-2508 Carmen 20.05.2020 new design
    	
    	if($data['id'] == '')
    	{
    		$data['id'] = null;
    	}
    	
    	if($data['option_time'] != "")
    	{
    		$data['option_time'] = $data['option_time'] . ":00";
    	}
    	else
    	{
    		$data['option_time'] = '00:00:00';
    	}
    	
    	if($data['option_date'] != "")
    	{
    		$data['option_date'] = date('Y-m-d H:i:s', strtotime($data['option_date'] . ' ' . $data['option_time']));
    	}
    	else
    	{
    		$data['option_date'] = '0000-00-00 00:00:00';
    	}
    	
    	//ISPC-2508 added for charts Carmen 23.04.2020
    	if(array_key_exists('remove_date', $data))
    	{
	    	if($data['remove_date'] == '' && $data['remove_time'] == '')
	    	{
	    		$data['isremove'] = '0';
	    	}
	    	else
	    	{
	    		$data['remove_time'] = $data['remove_time'] . ":00";
	    		$data['remove_date'] = date('Y-m-d H:i:s', strtotime($data['remove_date'] . ' ' . $data['remove_time']));
	    	}
    	}
    	//--
    	//ISPC-2508 Carmen 20.05.2020 new design
    	switch ($data['action'])
    	{
    		case 'refresh':
    			 
    			$primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
    			 
    			$fn = "findOneBy" . Doctrine_Inflector::classify($primaryKey) . "AndIpid";
    			//TODO-3433 Carmen 21.09.2020
    			if ($oldentity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid)) {
    					
    				$oldentity->isremove = 1;
    				//$entity->remove_date = date('Y-m-d H:i:s', time());
    				$oldentity->remove_date = $data['remove_date'];
    				$oldentity->save();
    				
    				$olddatesarr = [
    						'option_id' => $oldentity->option_id,
    						'option_date' => $oldentity->option_date,
    						'remove_date' => $oldentity->remove_date,
    						'option_localization' => $oldentity->option_localization,
    				];
    			}
    			//--	
    			$data['id'] = '';
    			$data['option_id'] = $oldentity->option_id;
    			//TODO-3433 Carmen 21.09.2020
    			//$data['option_localization'] = $entity->option_localization;
    			//--
    			$current_data = date('Y-m-d H:i:s', time());
    			$data['option_date'] = empty($data['remove_date']) || $data['remove_date'] == "" ? date('Y-m-d', time()) : $data['remove_date'];//TODO-4030 Ancuta 13.04.2021
    			$data['option_time'] = substr($current_data, 11, 5);
    				
    			unset($data['__subaction']);
    			unset($data['remove_date']);
    			unset($data['remove_time']);
    	
    			$entity = PatientArtificialEntriesExitsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
    			
    			//TODO-3433 Carmen 21.09.2020
    			$newdatesarr = [
    					'option_id' => $entity->option_id,
    					'option_date' => $entity->option_date,
    					'remove_date' => $entity->remove_date,
    					'option_localization' => $entity->option_localization,
    			];
    			$this->_save_box_artificial_entries_exits_History($ipid, $newdatesarr, $olddatesarr, 'grow100', $data['action']);
    			//--
    			break;
    		case 'remove':
    			 
    			$primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
    	
    			$fn = "findOneBy" . Doctrine_Inflector::classify($primaryKey) . "AndIpid";
    	
    			if ($entity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid)) {
    					
    				$entity->isremove = 1;
    				//$entity->remove_date = date('Y-m-d H:i:s', time());
    				$entity->remove_date = $data['remove_date'];
    				$entity->save();
    			}
    			//TODO-3433 Carmen 21.09.2020
    			$newdatesarr = [
    					'option_id' => $entity->option_id,
    					'option_date' => $entity->option_date,
    					'remove_date' => $entity->remove_date,
    					'option_localization' => $entity->option_localization,
    			];
    			$this->_save_box_artificial_entries_exits_History($ipid, $newdatesarr, $olddatesarr, 'grow100', $data['action']);
    			//--
    			break;
    		default:
    			//TODO-3433 Carmen 21.09.2020
    			if($data['id'])
    			{
	    			$primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
	    			
	    			$fn = "findOneBy" . Doctrine_Inflector::classify($primaryKey) . "AndIpid";
	    			
	    			if ($oldentity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid)) {
	    				
	    				$olddatesarr = [
	    						'option_id' => $oldentity->option_id,
	    						'option_date' => $oldentity->option_date,
	    						'remove_date' => $oldentity->remove_date,
	    						'option_localization' => $oldentity->option_localization,
	    				        'id' => $oldentity->id,        //ISPC-2807 Lore 25.02.2021
	    				];
	    			}
    			}
    			//--
    			$entity = PatientArtificialEntriesExitsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
    			//TODO-3433 Carmen 21.09.2020
    			$newdatesarr = [
    					'option_id' => $entity->option_id,
    					'option_date' => $entity->option_date,
    					'remove_date' => $entity->remove_date,
    					'option_localization' => $entity->option_localization,
    					'option_type' => $data['id'] ? 'edit' : 'add',
    			];
    			
    			//ISPC-2807 Lore 24.02.2021
    			$this->save_form_artificial_entries_exits_toVerlauf($ipid, $newdatesarr, $olddatesarr);
    			//.
    			
    			$this->_save_box_artificial_entries_exits_History($ipid, $newdatesarr, $olddatesarr, 'grow100');
    			//--
    			break;
    	}
    	//--
    	//var_dump($data); exit;
    	
    	//$entity = PatientArtificialEntriesExitsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data); //ISPC-2508 Carmen 20.05.2020 new design
    	
    	/*$this->_save_box_History($ipid, $entity, 'option_date', 'grow90', 'text');
    	$this->_save_box_History($ipid, $entity, 'option_date', 'grow90', 'text');
    	$this->_save_box_History($ipid, $entity, 'option_time', 'grow90', 'text');
    	$this->_save_box_History($ipid, $entity, 'localization', 'grow90', 'text');
     */
    	return $entity;
    }
    
    //TODO-3433 Carmen 21.09.2020
    private function _save_box_artificial_entries_exits_History($ipid, $newEntity, $oldEntity = null, $formid, $action = false)
    {
    	$fieldsarr = [
    			'option_id',
    			'option_date',
    			'remove_date',
    			'option_localization'
    	];
   
    	foreach($this->_client_options as $clopt)
    	{
    		$clopptdet[$clopt['id']] = $clopt;
    	}
    	
    	if($action == 'refresh')
    	{
    		foreach($fieldsarr as $fieldv)
    		{
    			if($oldEntity[$fieldv] != '' && $oldEntity[$fieldv])
    			{
	    			if($fieldv == 'option_date' &&  $oldEntity[$fieldv] != '0000-00-00 00:00:00')
	    			{
	    				$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($oldEntity[$fieldv]));
	    			}
	    			elseif($fieldv == 'remove_date' &&  $oldEntity[$fieldv] != '0000-00-00 00:00:00')
	    			{
	    				$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($oldEntity[$fieldv]));
	    			}
	    			elseif($fieldv == 'remove_date' &&  $oldEntity[$fieldv] == '0000-00-00 00:00:00')
	    			{
	    				$newevalue[$fieldv] = '';
	    			}
	    			elseif($fieldv == 'option_id')
	    			{
	    				$newevalue['option_name'] = $clopptdet[$oldEntity[$fieldv]]['name'];
	    				$fieldv = 'option_name';
	    			}
	    			else
	    			{
	    				$newevalue[$fieldv] = $oldEntity[$fieldv];
	    			}
	    			
	    			if($newevalue[$fieldv] != '')
	    			{
		    			$history[] = [
		    					'ipid' => $ipid,
		    					'clientid' => $this->logininfo->clientid,
		    					'formid' => $formid,
		    					//'fieldname' => $this->getColumnMapping($kr) . '(' . date('d.m.Y', strtotime($verstart)) . ' - ' . date('d.m.Y', strtotime($verend)) . ')',
		    					'fieldname' =>  'entfernen'. '<br />' .$this->translate('artificial_'.$fieldv),
		    					'fieldvalue' => $newevalue[$fieldv] ,
		    			];
	    			}
    			}
    		}
    		
    		foreach($fieldsarr as $fieldv)
    		{
    		if($newEntity[$fieldv] != '' && $newEntity[$fieldv])
    			{
	    			if($fieldv == 'option_date' &&  $newEntity[$fieldv] != '0000-00-00 00:00:00')
	    			{
	    				$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($newEntity[$fieldv]));
	    			}
	    			elseif($fieldv == 'remove_date' &&  $newEntity[$fieldv] != '0000-00-00 00:00:00')
	    			{
	    				$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($newEntity[$fieldv]));
	    			}
	    			elseif($fieldv == 'remove_date' &&  $newEntity[$fieldv] == '0000-00-00 00:00:00')
	    			{
	    				$newevalue[$fieldv] = '';
	    			}
	    			elseif($fieldv == 'option_id')
	    			{
	    				$newevalue['option_name'] = $clopptdet[$newEntity[$fieldv]]['name'];
	    				$fieldv = 'option_name';
	    			}
	    			else
	    			{
	    				$newevalue[$fieldv] = $newEntity[$fieldv];
	    			}
	    			
	    			if($newevalue[$fieldv] != '')
	    			{
		    			$history[] = [
		    					'ipid' => $ipid,
		    					'clientid' => $this->logininfo->clientid,
		    					'formid' => $formid,
		    					//'fieldname' => $this->getColumnMapping($kr) . '(' . date('d.m.Y', strtotime($verstart)) . ' - ' . date('d.m.Y', strtotime($verend)) . ')',
		    					'fieldname' =>  'neu anlegen'. '<br />' .$this->translate('artificial_'.$fieldv),
		    					'fieldvalue' => $newevalue[$fieldv] ,
		    			];
	    			}
	    			
    			}
    		}
    	}
    	elseif($action == 'remove')
    	{
    		foreach($fieldsarr as $fieldv)
    		{
    		if($newEntity[$fieldv] != '' && $newEntity[$fieldv])
    			{
	    			if($fieldv == 'option_date' &&  $newEntity[$fieldv] != '0000-00-00 00:00:00')
	    			{
	    				$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($newEntity[$fieldv]));
	    			}
	    			elseif($fieldv == 'remove_date' &&  $newEntity[$fieldv] != '0000-00-00 00:00:00')
	    			{
	    				$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($newEntity[$fieldv]));
	    			}
	    			elseif($fieldv == 'remove_date' &&  $newEntity[$fieldv] == '0000-00-00 00:00:00')
	    			{
	    				$newevalue[$fieldv] = '';
	    			}
	    			elseif($fieldv == 'option_id')
	    			{
	    				$newevalue['option_name'] = $clopptdet[$newEntity[$fieldv]]['name'];
	    				$fieldv = 'option_name';
	    			}
	    			else
	    			{
	    				$newevalue[$fieldv] = $newEntity[$fieldv];
	    			}
	    			
	    			if($newevalue[$fieldv] != '')
	    			{
		    			$history[] = [
		    					'ipid' => $ipid,
		    					'clientid' => $this->logininfo->clientid,
		    					'formid' => $formid,
		    					//'fieldname' => $this->getColumnMapping($kr) . '(' . date('d.m.Y', strtotime($verstart)) . ' - ' . date('d.m.Y', strtotime($verend)) . ')',
		    					'fieldname' =>  'entfernen'. '<br />' .$this->translate('artificial_'.$fieldv),
		    					'fieldvalue' => $newevalue[$fieldv] ,
		    			];
	    			}
	    			
    			}
    		}
    	}
    	else
    	{
    		if($newEntity['option_type'] == 'edit')
    		{
    			$savetype = 'bearbeiten';
    			unset($newEntity['option_type']);
    			foreach($fieldsarr as $fieldv)
    			{
    				if($newEntity[$fieldv] != '' && $newEntity[$fieldv])
    				{
    					if($fieldv == 'option_date' &&  $newEntity[$fieldv] != '0000-00-00 00:00:00')
    					{
    						$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($newEntity[$fieldv])) . '/' .date('d.m.Y H:i:s', strtotime($oldEntity[$fieldv]));
    					}
    					elseif($fieldv == 'remove_date' &&  $newEntity[$fieldv] != '0000-00-00 00:00:00')
    					{
    						$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($newEntity[$fieldv])) . '/' .date('d.m.Y H:i:s', strtotime($oldEntity[$fieldv]));;
    					}
    					elseif($fieldv == 'remove_date' &&  $newEntity[$fieldv] == '0000-00-00 00:00:00')
    					{
    						$newevalue[$fieldv] = '';
    					}
    					elseif($fieldv == 'option_id')
    					{
    						$newevalue['option_name'] = $clopptdet[$newEntity[$fieldv]]['name']. '/' .$clopptdet[$oldEntity[$fieldv]]['name'];
    						$fieldv = 'option_name';
    					}
    					else
    					{
    						$newevalue[$fieldv] = $newEntity[$fieldv]. '/' .$oldEntity[$fieldv];
    					}
    					 
    					if($newevalue[$fieldv] != '')
    					{
    						$history[] = [
    								'ipid' => $ipid,
    								'clientid' => $this->logininfo->clientid,
    								'formid' => $formid,
    								//'fieldname' => $this->getColumnMapping($kr) . '(' . date('d.m.Y', strtotime($verstart)) . ' - ' . date('d.m.Y', strtotime($verend)) . ')',
    								'fieldname' =>  $savetype. '<br />' .$this->translate('artificial_'.$fieldv),
    								'fieldvalue' => $newevalue[$fieldv] ,
    						];
    					}
    					 
    				}
    			}
    		}
    		else 
    		{
    			$savetype = 'neu anlegen';
    			unset($newEntity['option_type']);
    			foreach($fieldsarr as $fieldv)
    			{
    				if($newEntity[$fieldv] != '' && $newEntity[$fieldv])
    				{
    					if($fieldv == 'option_date' &&  $newEntity[$fieldv] != '0000-00-00 00:00:00')
    					{
    						$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($newEntity[$fieldv]));
    					}
    					elseif($fieldv == 'remove_date' &&  $newEntity[$fieldv] != '0000-00-00 00:00:00')
    					{
    						$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($newEntity[$fieldv]));
    					}
    					elseif($fieldv == 'remove_date' &&  $newEntity[$fieldv] == '0000-00-00 00:00:00')
    					{
    						$newevalue[$fieldv] = '';
    					}
    					elseif($fieldv == 'option_id')
    					{
    						$newevalue['option_name'] = $clopptdet[$newEntity[$fieldv]]['name'];
    						$fieldv = 'option_name';
    					}
    					else
    					{
    						$newevalue[$fieldv] = $newEntity[$fieldv];
    					}
    			
    					if($newevalue[$fieldv] != '')
    					{
    						$history[] = [
    								'ipid' => $ipid,
    								'clientid' => $this->logininfo->clientid,
    								'formid' => $formid,
    								//'fieldname' => $this->getColumnMapping($kr) . '(' . date('d.m.Y', strtotime($verstart)) . ' - ' . date('d.m.Y', strtotime($verend)) . ')',
    								'fieldname' =>  $savetype. '<br />' .$this->translate('artificial_'.$fieldv),
    								'fieldvalue' => $newevalue[$fieldv] ,
    						];
    					}
    			
    				}
    			}
    		}
    	}
    	
    	if ( ! empty($history)) {
    		$coll = new Doctrine_Collection("BoxHistory");
    		$coll->fromArray($history);
    		$coll->save();
    	}
    }
    //--
    
    
    //ISPC-2807 Lore 24.02.2021
    public function save_artificial_exits_to_Verlauf($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $model= new Stammdatenerweitert();
        $old_vals_db_data = $model->getStammdatenerweitert($ipid);
        $values_trans = $model->getKunstlichefun();
        
        if(!empty($old_vals_db_data)){
            $old_vals_db_data_arr = explode(",", $old_vals_db_data[0]['kunstliche']);
            foreach($old_vals_db_data_arr as $k_v=> $v_val){
                if($k_v > 0 && $k_v < count($old_vals_db_data_arr)){
                    $last_value .= ', ';
                }
                $last_value .= $values_trans[$v_val];
            }
        }

        
        foreach($post as $key => $vals){
            foreach($vals as $k_v=> $v_val){
                if($k_v > 0 && $k_v < count($vals)){
                    $new_val .= ', ';
                }
                $new_val .= $values_trans[$v_val];
            }
        }

        //TODO-3930 Lore 08.03.2021
        $course_title = '';
        if($last_value != $new_val){
            $course_title = "Der Künstliche Ausgänge wurde geändert: ".$last_value .' -> '.$new_val . "\n\r";
        }
        
        $recordid = $old_vals_db_data[0]['id'];
        if(!empty($course_title)){
            $insert_pc = new PatientCourse();
            $insert_pc->ipid =  $ipid;
            $insert_pc->course_date = date("Y-m-d H:i:s", time());
            $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
            $insert_pc->recordid = $recordid;
            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_title));
            $insert_pc->user_id = $userid;
            $insert_pc->save();
        }
            

        
        
    }
    
    //ISPC-2807 Lore 24.02.2021
    public function save_form_artificial_entries_exits_toVerlauf($ipid, $newdatesarr, $olddatesarr)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        // TODO-4158 Ancuta 26.05.2021
        //$type_entries = array("entry" => "Zugänge", "exit" => "Ausgänge");
        $type_entries = array("entry" => "Zugang", "exit" => "Ausgang");
        $course_title = '';
        
        foreach($this->_client_options as $key => $vals){

            if($newdatesarr['option_id'] == $vals['id']){
                if($newdatesarr['option_type'] == 'add'){
                    $course_title = "Ein ".$type_entries[$vals['type']]." wurde hinzugefügt: ".$vals['name'] ;
                } else {
                    $course_title = "Ein ".$type_entries[$vals['type']]." wurde geändert: ".$vals['name'] ;
                }
            }
        }
        
        $recordid = $olddatesarr['id'];
        
        if(!empty($course_title)){              //TODO-3930 Lore 08.03.2021
            $insert_pc = new PatientCourse();
            $insert_pc->ipid =  $ipid;
            $insert_pc->course_date = date("Y-m-d H:i:s", time());
            $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
            $insert_pc->recordid = $recordid;
            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_title));
            $insert_pc->user_id = $userid;
            $insert_pc->save();
        }

        
        
    }
}

?>