<?php

// require_once("Pms/Form.php");

class Application_Form_SapvVerordnung extends Pms_Form 
{

    
    public function getVersorgerExtract() {
        return array(
            array(
                "label" => null,
                "cols" => array(
                    "__division_legend",
                    "__division"
                ),
                "vsprintf_named" => '<span class="selector_divisions division_{__division}" data-division={__division} >{__division_legend}</span>'
            ),
            
            array( 
                "label" => null,
                "cols" => array(
                    "sapv_order_name", 
                    "verordnet_longtext",
                    "dotsLegend",
                    "status_color",
                ),
                "vsprintf_named" => '<span class="s1">{sapv_order_name} <span class="dontPrint">{dotsLegend}</span></span> <span class="s2"><font color="{status_color}">{verordnet_longtext}</font></span>'
            ),
            
            
            array( 
                "label" => null,
                "cols" => array(
                    "verordnet_von_nice_name",
                    "verordnungam", 
                    "verordnungbis",
                ),
                "vsprintf_named" => '<span class="s1">{verordnet_von_nice_name}</span>  <span class="s2">{verordnungam} - {verordnungbis}</span>'
                
                
            ),
            array( 
                "label" => null, 
                "cols" => array(
                    "bra_options_formated",
                ),
                "vsprintf_named" => '<span class="s1">' . $this->translate('Sapv bra options') . '</span> <span class="s2">{bra_options_formated}</span>',
                
            ),
            
            array(
                "label" => null,
                "cols" => array(
                    "case_number",
                ),
                "vsprintf_named" => '<span class="s1">' . $this->translate('sapv_case_number') . '</span> <span class="s2">{case_number}</span>',
                
            
            ),
            
            
    
    
        );
    }
    
//     public function getVersorgerAddress()
//     {
//         return array(
//             array(array("nice_name")),
//             array(array("cnt_street1")),
//             array(array("cnt_zip"), array("cnt_city")),
//         );
//     }
    
    
	public function validate($post) {
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();
		$error = 0;
		$val = new Pms_Validation();
		if (!$val->isstring($post['verordnet_von'])) {
			$this->error_message['verordnet_von'] = $Tr->translate('verordnet_von_err');
			$error = 1;
		}
		if (!$val->isstring($post['verordnungam'])) {
			$this->error_message['verordnungam'] = $Tr->translate('verordnungam_err');
			$error = 2;
		} else{

		    if(date('Y', strtotime($post['verordnungam'])) < '2008')
		    {
		        $this->error_message['verordnungam'] = $Tr->translate('date_error_before_2008');
		        $error = 11;
		    }
		    
		}
		
		
		
		if (!$val->isstring($post['verordnungbis'])) {
			$this->error_message['verordnungbis'] = $Tr->translate('verordnungbis_err');
			$error = 3;
		}

		if (strlen($post['verordnet'][0]) < 1) {
			$this->error_message['verordnet'] = $Tr->translate('verordnet_err');
			$error = 4;
		}
		if (strlen($post['status']) < 1) {
			$this->error_message['status'] = $Tr->translate('verordnet_status_err');
			$error = 5;
		}
		
		if($val->isstring($post['verordnungam']) && $val->isstring($post['verordnungbis']))
		{
			list($VonDay,$VonMonth,$VonYear) = explode(".", $post['verordnungam']);
			list($BisDay,$BisMonth,$BisYear) = explode(".", $post['verordnungbis']);
				
			$von_time = mktime(0, 0, 0,$VonMonth,$VonDay,$VonYear);
			$bis_time = mktime(0, 0, 0,$BisMonth,$BisDay,$BisYear);

			if($bis_time < $von_time)
			{
				$this->error_message['verordnungbis'] = $Tr->translate('verordnungbis_big_err');
				$error = 6;
			}
		}
		
		if($val->isstring($post['regulation_start']) && $val->isstring($post['regulation_end']))
		{
			list($VonDay_new,$VonMonth_new,$VonYear_new) = explode(".", $post['regulation_start']);
			list($BisDay_new,$BisMonth_new,$BisYear_new) = explode(".", $post['regulation_end']);
		
			$von_time_new = mktime(0, 0, 0,$VonMonth_new,$VonDay_new,$VonYear_new);
			$bis_time_new = mktime(0, 0, 0,$BisMonth_new,$BisDay_new,$BisYear_new);
		
			if($bis_time_new < $von_time_new)
			{
				$this->error_message['regulation_end'] = $Tr->translate('regulation_end_big_err');
				$error = 9;
			}
		}


		$modules = new Modules();
		/* if ($modules->checkModulePrivileges("70", $logininfo->clientid)) // primary status : Verordnung
		{
			if (strlen($post['primary_set']) < 1 || $post['primary_set'] == 0 ) {
				$this->error_message['primary_set'] = $Tr->translate('verordnet_primary_err');
				$error = 7;
			}
		}


		if ($modules->checkModulePrivileges("71",  $logininfo->clientid)) // secondary status : Verordnung 2nd Page
		{
			if (strlen($post['secondary_set']) < 1 || $post['secondary_set'] == 0 ) {
				$this->error_message['secondary_set'] = $Tr->translate('verordnet_secondary_err');
				$error = 8;
			}
		} */
		if ($error == 0) {
			return true;
		}

		return false;
	}

	public function InsertData($post) {
		$comma = "";
		for ($i = 0; $i < count($post['verordnet']); $i++) {
			$verordnet .= $comma . $post['verordnet'][$i];
			$comma = ",";
		}


		if ($post['verordnungam'] != "") {
			$verordnungam = date('Y-m-d', strtotime($post['verordnungam']));
		}
		if ($post['verordnungbis'] != "") {
			$verordnungbis = date('Y-m-d', strtotime($post['verordnungbis']));
		}
		if ($post['regulation_start'] != "") {
			$regulation_start = date('Y-m-d', strtotime($post['regulation_start']));
		}
		if ($post['regulation_end'] != "") {
			$regulation_end = date('Y-m-d', strtotime($post['regulation_end']));
		}		
		if ($post['verorddisabledate'] != ""   && $post['status'] == "1") {
			$verorddisabledate = date('Y-m-d', strtotime($post['verorddisabledate']));
		}

		if ($post['approved_date'] != "" && $post['status'] == "2") {
			$approved_date = date('Y-m-d', strtotime($post['approved_date']));
		}

		$ipid = Doctrine_Query::create()
		->select('*')
		->from('SapvVerordnung')
		->where("ipid='" . $post['ipid'] . "'")
		->limit(1)
		->orderBy('id desc');
		$epexe = $ipid->execute();

		if ($epexe) {
			$maintainarr = $epexe->toArray();

			if (count($maintainarr) > 0) {
				$cust = Doctrine::getTable('SapvVerordnung')->find($maintainarr[0]['id']);
				$cust->tilldate = date('Y-m-d', time());
				$cust->save();
			}
		}

		if ($post['hidd_verordnet_von'] > 0) {
			$cust = new SapvVerordnung();
			$cust->ipid = $post['ipid'];
			$cust->sapv_order = $post['sapv_order'];
			$cust->verordnet_von = $post['hidd_verordnet_von'];
			$cust->verordnet_von_type = $post['hidd_verordnet_von_type'];
			$cust->verordnungam = $verordnungam;
			$cust->verordnungbis = $verordnungbis;
			$cust->regulation_start = $regulation_start;
			$cust->regulation_end = $regulation_end;
			$cust->verorddisabledate = $verorddisabledate;
			$cust->approved_date = $approved_date;
			$cust->approved_number= $post['approved_number'];
			$cust->verordnet = $verordnet;
			$cust->status = $post['status'];
			$cust->after_opposition = $post['after_opposition'];
			$cust->extra_set = $post['extra_set'];
			$cust->primary_set = $post['primary_set'];
			$cust->secondary_set = $post['secondary_set'];
			$cust->bra_options = implode(",",$post['bra_options']);
			$cust->case_number = $post['case_number'];
			$cust->fromdate = date('Y-m-d', time());
			$cust->save();
		} else {
			if (strlen($post['verordnet_von']) > 0) {
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$fdoc = new FamilyDoctor();
				$fdoc->clientid = $logininfo->clientid;
				$fdoc->last_name = $post['verordnet_von'];
				$fdoc->indrop = 1;
				$fdoc->save();

				$cust = new SapvVerordnung();
				$cust->ipid = $post['ipid'];
				$cust->sapv_order = $post['sapv_order'];
				$cust->verordnet_von = $fdoc->id;
				$cust->verordnungam = $verordnungam;
				$cust->verordnungbis = $verordnungbis;
				$cust->regulation_start = $regulation_start;
				$cust->regulation_end = $regulation_end;
				$cust->verorddisabledate = $verorddisabledate;
				$cust->approved_date = $approved_date;
				$cust->approved_number= $post['approved_number'];
				$cust->verordnet = $verordnet;
				$cust->status = $post['status'];
				$cust->after_opposition = $post['after_opposition'];
				$cust->extra_set = $post['extra_set'];
				$cust->primary_set = $post['primary_set'];
				$cust->secondary_set = $post['secondary_set'];
				$cust->bra_options = implode(",",$post['bra_options']);
				$cust->case_number = $post['case_number'];
				$cust->fromdate = date('Y-m-d', time());
				$cust->save();
			}
		}
	}

	public function UpdateData($post) {

		$comma = "";
		for ($i = 0; $i < count($post['verordnet']); $i++) {
			$verordnet .= $comma . $post['verordnet'][$i];
			$comma = ",";
		}

		if ($post['verordnungam'] != "0000-00-00 00:00:00") {
			$verordnungam = date('Y-m-d', strtotime($post['verordnungam']));
		}
		if ($post['verordnungbis'] != "0000-00-00 00:00:00") {
			$verordnungbis = date('Y-m-d', strtotime($post['verordnungbis']));
		}
		if ($post['regulation_start'] != "" && $post['regulation_start'] != "0000-00-00 00:00:00") {
			$regulation_start = date('Y-m-d', strtotime($post['regulation_start']));
		}
		if ($post['regulation_end'] != "" && $post['regulation_end'] != "0000-00-00 00:00:00") {
			$regulation_end = date('Y-m-d', strtotime($post['regulation_end']));
		}
		if ($post['verorddisabledate'] != "0000-00-00 00:00:00"   && $post['status'] == "1") {
			$verorddisabledate = date('Y-m-d', strtotime($post['verorddisabledate']));
		}
		if ($post['approved_date'] != "0000-00-00 00:00:00"  && $post['status'] == "2") {
			$approved_date = date('Y-m-d', strtotime($post['approved_date']));
		}


		$cust = Doctrine::getTable('SapvVerordnung')->find($_GET['vid']);
		$cust->sapv_order = $post['sapv_order'];
		$cust->verordnungam = $verordnungam;
		$cust->verordnet_von = $post['hidd_verordnet_von'];
		$cust->verordnet_von_type = $post['hidd_verordnet_von_type'];
		$cust->verordnungbis = $verordnungbis;
		$cust->regulation_start = $regulation_start;
		$cust->regulation_end = $regulation_end;
		$cust->verorddisabledate = $verorddisabledate;
		$cust->approved_date= $approved_date;
		$cust->approved_number = $post['approved_number'];
		$cust->status = $post['status'];
		$cust->after_opposition = $post['after_opposition'];
		$cust->extra_set = $post['extra_set'];
		$cust->primary_set = $post['primary_set'];
		$cust->secondary_set = $post['secondary_set'];
		$cust->bra_options = implode(",",$post['bra_options']);
		$cust->case_number = $post['case_number'];
		$cust->verordnet = $verordnet;
		$cust->save();
	}

	public function UpdateDataFromAdmission($post) {

		$comma = "";
		for ($i = 0; $i < count($post['verordnet']); $i++) {
			$verordnet .= $comma . $post['verordnet'][$i];
			$comma = ",";
		}


		if ($post['verordnungam'] != "0000-00-00 00:00:00") {
			$verordnungam = date('Y-m-d', strtotime($post['verordnungam']));
		}
		if ($post['verordnungbis'] != "0000-00-00 00:00:00") {
			$verordnungbis = date('Y-m-d', strtotime($post['verordnungbis']));
		}
		if ($post['verorddisabledate'] != "0000-00-00 00:00:00") {
			$verorddisabledate = date('Y-m-d', strtotime($post['verorddisabledate']));
		}
		if ($post['approved_date'] != "0000-00-00 00:00:00") {
			$approved_date = date('Y-m-d', strtotime($post['approved_date']));
		}
		
		$bra_options = implode(",",$post['bra_options']);

		$q = Doctrine_Query::create()
		->update('SapvVerordnung')		
		->set('verordnungam', "'" . $verordnungam . "'")
		->set('verordnet_von', "'" . $post['hidd_verordnet_von'] . "'")
		->set('verordnet_von_type', "'" . $post['hidd_verordnet_von_type'] . "'")
		->set('verordnungbis', "'" . $verordnungbis . "'")
		->set('verorddisabledate', "'" . $verorddisabledate . "'")
		->set('approved_date', "'" . $approved_date . "'")
		->set('approved_number', "'" . $post['approved_number'] . "'")
		->set('verordnet', "'" . $verordnet . "'")
		->set('status', "'" . $post['status'] . "'")
		->set('extra_set', "'" . $post['extra_set'] . "'")
		->set('primary_set', "'" . $post['primary_set'] . "'")
		->set('secondary_set', "'" . $post['secondary_set'] . "'")
		->set('bra_options', "'" . $bra_options . "'")
		->set('case_number', "'" . $post['case_number'] . "'")
		->where("id = '" . $post['hidd_lastid'] . "'");

		$q->execute();
	}

	public function deleteSapvVerordnung($dids) {
		$sveror = Doctrine::getTable('SapvVerordnung')->find($dids);
		$sveror->isdelete = 1;
		$sveror->save();
	}

	
	
	
	
	
	
	
	/**
	 * !! this form uses $this->_patientMasterData['ModulePrivileges'];
	 * Modules 69, 70, 71, 79
	 * module 149
	 * 
	 * @param unknown $options
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_patient_sapv_verordnung( $options = array(), $elementsBelongTo = null)
	{
	     
	
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_sapv_verordnung");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend('sapvverordnungttl');
	    $subform->setAttrib("class", "label_same_size " . __FUNCTION__);
	    //ISPC-2359, elena, 29.10.2020
        $subform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');

        $sapvExtraStatusesColor = SapvVerordnung::getSapvExtraStatusesColor();
	     
	    
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	
// 	isf(!empty($options))dd($options);
	    
	    /*
	     * hidden inputs
	     */
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=> 3 , 'openOnly' =>true)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' , 'openOnly' =>true)),
	    
	        ),
	    ));
	    $subform->addElement('hidden',  'verordnet_von', array(
	        'value'        =>  $options['verordnet_von'] ? $options['verordnet_von'] : 0 ,
	        'decorators' => array(
	            'ViewHelper',	    
	        ),
	    ));
	    $subform->addElement('hidden',  'verordnet_von_type', array(
	        'value'        =>  $options['verordnet_von_type'] ? $options['verordnet_von_type'] : 0 ,
	        'decorators' => array(
	            'ViewHelper',	    
	        ),
	    ));
	    //last hidden one
	    $subform->addElement('hidden', 'idxx', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' =>true)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' =>true)),
	             
	        ),
	    ));
	    
	    
	    
	    
	    
	    

	    //row1
	    $subform->addElement('select',  'sapv_order', array(
	        'multiOptions' => [ "1" => 'Erstverordnung', "2" => 'Folgeverordnung'],	
	        'value'        => isset($options['sapv_order']) ? $options['sapv_order'] : (isset($options['__total_saved_in_tab_sapv']) && $options['__total_saved_in_tab_sapv'] > 0 ? 2 : 1),
	        'label'        => 'sapv_order',
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'colspan'=> 2)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	    
	        ),
	    ));
	    
	    
	    //row2 Verordner
	    $subform->addElement('text',  'verordnet_von_nice_name', array(
	        'value'        => $options['verordnet_von_nice_name'],
	        'label'        => 'Verordner',
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=> 2)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	    
	        ),
	        'data-livesearch'   => 'SapvVerordner',
	    ));
	    
	    
	    
	    
	     
	    
	    //row3 Verordnungszeitraum
	    $subform->addElement('note',  'Verordnungszeitraum', array(
	        'value'        => 'Verordnungszeitraum',
	        'label'        => null,
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , "openOnly" => true)),
	             
	        ),
	    ));
	    $subform->addElement('text',  'regulation_start', array(
	        'value'        => empty($options['regulation_start']) || $options['regulation_start'] == "0000-00-00 00:00:00" || $options['regulation_start'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['regulation_start'])),
	         
	        'label'        => 'start_time',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND', "class" => "label_date_sapv")),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),	    
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	        
	        'onChange' => 'if ($("input[name$=\"\[verordnungam\]\"]", $(this).parents("table")).val() == "") { $("input[name$=\"\[verordnungam\]\"]", $(this).parents("table")).val(this.value) }',
	    
	        
	    ));
	    $subform->addElement('text',  'regulation_end', array(
	        'value'        => empty($options['regulation_end']) || $options['regulation_end'] == "0000-00-00 00:00:00" || $options['regulation_end'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['regulation_end'])),
	         
	        'label'        => 'end_time',
	        'required'     => false,
	        
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND', "class" => "label_date_sapv")),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	    
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',
	        'onChange' => 'if ($("input[name$=\"\[verordnungbis\]\"]", $(this).parents("table")).val() == "") { $("input[name$=\"\[verordnungbis\]\"]", $(this).parents("table")).val(this.value) }',
	         
	    ));
	    
	    
	    
	    
	    
	    //row4 Genehmigungszeitraum
	    $subform->addElement('note',  'Genehmigungszeitraum', array(
	        'value'        => 'Genehmigungszeitraum',
	        'label'        => null,
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'required')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , "openOnly" => true)),
	    
	        ),
	    ));
	    $subform->addElement('text',  'verordnungam', array(
	        'value'        => $options['verordnungam'],
	        'value'        => empty($options['verordnungam']) || $options['verordnungam'] == "0000-00-00 00:00:00" || $options['verordnungam'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['verordnungam'])),
	         
	        'label'        => 'start_time',
	        'required'     => true,
	        
	        'filters'      => array('StringTrim'),
	        
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND', "class" => "label_date_sapv")),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'onChange' => 'if ($("input[name$=\"\[regulation_start\]\"]", $(this).parents("table")).val() == "") { $("input[name$=\"\[regulation_start\]\"]", $(this).parents("table")).val(this.value) }',
	        
	    ));
	    $subform->addElement('text',  'verordnungbis', array(
	        'value'        => empty($options['verordnungbis']) || $options['verordnungbis'] == "0000-00-00 00:00:00" || $options['verordnungbis'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['verordnungbis'])),
	         
	        'label'        => 'end_time',
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	         
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND', "class" => "label_date_sapv")),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	             
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'onChange' => 'if ($("input[name$=\"\[regulation_end\]\"]", $(this).parents("table")).val() == "") { $("input[name$=\"\[regulation_end\]\"]", $(this).parents("table")).val(this.value) }',
	        
	    ));
	    
	    
	    
	    
	    
	    //row5 Verordnet
	    $values =  isset($options['verordnet']) && ! is_array($options['verordnet']) ? array_map('trim', explode(",", $options['verordnet'])) : $options['verordnet'];
	     
	    $subform->addElement('multiCheckbox',  'verordnet', array(
	        'value'        => $values,
	        'label'        => 'verordnet',
	        'separator'    => ' ',
	        'required'     => true,
	        'multiOptions' =>  Pms_CommonData::getSapvCheckBox(),
	         
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "colspan" => 2)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'tr_verordnet')),
	        ),
// 	        'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide()}",
	         
	    ));
	    
	    
	    
// 	    
	    
	    //row6 Status
	    $subform->addElement('radio',  'status', array(
	        'value'        => isset($options['status']) ? $options['status'] : null,
	        'label'        => 'status',
	        'separator'    => ' ',
	        'required'     => true,
	        'multiOptions' =>  SapvVerordnung::getSapvRadios(),
	    
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "colspan" => 2 , "openOnly" => true)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', "openOnly" => true, 'class'=> 'label_status')),
	        ),
	        'onChange' => "if(this.value == '1') {\$('.show_hide_1', \$(this).parents('table')).show(); \$('.show_hide_2', \$(this).parents('table')).hide(); } else if(this.value == '2') {\$('.show_hide_2', \$(this).parents('table')).show(); \$('.show_hide_1', \$(this).parents('table')).hide(); } else { \$('.show_hide_1, .show_hide_2', \$(this).parents('table')).hide(); }",
	    ));
	    
	    $subform->addElement('checkbox',  'after_opposition', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'value'        => isset($options['after_opposition']) ? $options['after_opposition'] : 0,
	        'label'        => 'nach Widerspruch',
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', "closeOnly" => true)),
	        ),
	        // 	        'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide()}",
	    ));
	    
	    //row 6+1 abgelehnt am
	    $display = $options['status'] == 1 ? "" : "display:none";
	    $subform->addElement('text',  'verorddisabledate', array(
	        'value'        => empty($options['verorddisabledate']) || $options['verorddisabledate'] == "0000-00-00 00:00:00" || $options['verorddisabledate'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['verorddisabledate'])),
	         
	        'label'        => 'disableddate',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide_1', 'style' => $display)),
	    
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	    ));
	     
	    
	    //row 6+2 genehmigt am
	    $display = $options['status'] == 2 ? "" : "display:none";
	    $subform->addElement('text',  'approved_date', array(
	        'value'        => empty($options['approved_date']) || $options['approved_date'] == "0000-00-00 00:00:00" || $options['approved_date'] == "1970-01-01 00:00:00"  ? "" : date('d.m.Y', strtotime($options['approved_date'])),
	         
	        'label'        => 'sapv_approved_date',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide_2', 'style' => $display)),
	             
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	    ));
	    //row 6+3 Genehmigungs-Nr.
	    $subform->addElement('text',  'approved_number', array(
	        'value'        => $options['approved_number'],
	        'label'        => 'sapv_approved_number',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide_2', 'style' => $display)),
	    
	        ),
	    ));
	     
	    
	    
	    $ClientModules =  $this->_patientMasterData['ModulePrivileges'];
	     
	    if ($ClientModules[69]) {
    	    //row7 Status Extra
    	    $subform->addElement('radio',  'extra_set', array(
    	        'value'        => isset($options['extra_set']) ? $options['extra_set'] : null,
    	        'label'        => '',
    	        'separator'    => ' ',
    	         
    	        'multiOptions' =>  SapvVerordnung::getSapvExtraRadios(),
    	         
    	        'filters'      => array('StringTrim'),
    	        'validators'   => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "colspan" => 2)),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=> 'label_extra_set')),
    	        ),
    	        // 	        'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide()}",
    	    ));
	    }
	     
	    /*
	     * //ISPC-2539, elena, 29.10.2020
	    if ($ClientModules[70]) {
    	    //row8 Verordnung
    	    $subform->addElement('select',  'primary_set', array(
    	        
    	        'multiOptions' => SapvVerordnung::getSapvExtraStatusesRadios(),
    	        
    	        'value'        => $options['primary_set'],
    	        'label'        => 'Primary Status',
    	        'required'     => false,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	             
    	        ),
    	    ));
	    }
	     
	    
	    if ($ClientModules[71]) {
    	    //row9 2. Seite 
    	    $subform->addElement('select',  'secondary_set', array(
    	        'multiOptions' => SapvVerordnung::getSapvExtraStatusesRadios(),
    	        'value'        => $options['secondary_set'],
    	        'label'        => '2nd Page',
    	        'required'     => false,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	    
    	        ),
    	    ));
	    }*/
	    
	    if ($ClientModules[97]) {
    	    //row10 SAPV Brandenburg
    	    
	        $values =  isset($options['bra_options']) && ! is_array($options['bra_options']) ? array_map('trim', explode(",", $options['bra_options'])) : $options['bra_options'];
	        
    	    $subform->addElement('multiCheckbox',  'bra_options', array(
    	        'multiOptions' =>  Pms_CommonData::get_bra_options_checkboxes(),
    	        'value'        => $values,
    	        'label'        => 'verordnet',
    	        'separator'    => ' ',
    	    
    	        'filters'      => array('StringTrim'),
    	        'validators'   => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "colspan" => 2)),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=> 'label_bra_options')),
    	        ),
    	        // 	        'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide()}",
    	    
    	    ));
	    }
	     
	    
	    if ($ClientModules[149]) {
	        	
	        $values =  isset($options['case_number']) && ! is_array($options['case_number']) ? array_map('trim', explode(",", $options['bra_options'])) : $options['bra_options'];
	         
	        $subform->addElement('text',  'case_number', array(
	            'value'        => isset($options['case_number']) ? $options['case_number'] : null,
	            'label'        => 'sapv_case_number',
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', "colspan" => 2)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	            // 	        'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide()}",
	            	
	        ));
	    }
	    
	    //ISPC-2539, elena, 29.10.2020
        if ($ClientModules[70] || $ClientModules[71]) {

            $newview = new Zend_View();
            //$newview->pdf = $pdf;
            /*

            foreach ($data as $key=>$value){
                $newview->$key = $value;
            }
            $newview->leistungen =  json_decode(ClientConfig::getConfig($clientid, 'socialservices'));
            // necessary for Baseassesment Pflege, does nothing with another form blocks
            $newview->blockconfig = $blockconfig;
            */


            $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
            $newview->primary_set = $options['primary_set'];
            $newview->secondary_set = $options['secondary_set'];
            $newview->primary_set_permitted = $ClientModules[70];
            $newview->secondary_set_permitted = $ClientModules[71];
            $newview->sapvExtraStatusesColor = $sapvExtraStatusesColor;
            $newview->sapvExtraStatusesRadios = SapvVerordnung::getSapvExtraStatusesRadios();
            $newview->primary_color = $newview->sapvExtraStatusesColor[$options['primary_set']];
            $newview->primary_label = $newview->sapvExtraStatusesRadios[$options['primary_set']];
            $newview->secondary_color = $newview->sapvExtraStatusesColor[$options['secondary_set']];
            $newview->secondary_label = $newview->sapvExtraStatusesRadios[$options['secondary_set']];
            $setHistory = new VerordnungSetStatusHistory();
            //print_r($setHistory->getSql());
            $allhistory = $setHistory->getHistoryForVerordnung($options['id'], $options['ipid']);
            //print_r($allhistory);
            $history = [];
            $history['primary_set'] = [];
            $history['secondary_set'] = [];
            foreach($allhistory as $his){
                //print_r($his['fieldname']);
                if($his['set_value'] == 'primary_set'){
                    $history['primary_set'][] = $his;
                }elseif($his['set_value'] == 'secondary_set'){
                    $history['secondary_set'][] = $his;
                }
            }
            $newview->history_verordnung = $history['primary_set'];
            $newview->history_seite2 = $history['secondary_set'];
            $html1 = $newview->render("sapvverordnungsgroup_top.html");


            $subform->addElement('note', 'block_' . 'conf', array(
                'value' => $html1,
                'decorators' => array(
                    'SimpleTemplate',
                ),
            ));


            if ($ClientModules[70]) {
                //row8 Verordnung
                $subform->addElement('select',  'primary_set', array(

                    'multiOptions' => SapvVerordnung::getSapvExtraStatusesRadios(),

                    'value'        => $options['primary_set'],
                    //'label'        => 'Primary Status',
                    'required'     => false,
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'),
                            array('tag' => 'div', 'id' => 'select_primary')),
                        array(array('row' => 'HtmlTag'),
                         array('tag' => 'td',  'style' => 'border-bottom:none;width:50%;height:20px;')),

                    ),
                ));
            }


            if ($ClientModules[71]) {
                //row9 2. Seite
                $subform->addElement('select',  'secondary_set', array(
                    'multiOptions' => SapvVerordnung::getSapvExtraStatusesRadios(),
                    'value'        => $options['secondary_set'],
                    //'label'        => '2nd Page',
                    'required'     => false,
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'),
                            array('tag' => 'div', 'id' => 'select_secondary')),
                        array(array('row' => 'HtmlTag'),
                         array('tag' => 'td',  'style' => 'border-bottom:none;width:50%;height:20px;')),

                    ),
                ));
            }

            $html2 = $newview->render("sapvverordnungsgroup_bottom.html");

            $subform->addElement('note', 'block_' . 'conf2', array(
                'value' => $html2,
                'decorators' => array(
                    'SimpleTemplate',
                ),
            ));



        }

	    
	
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	
	}
	
	
	public function save_form_patient_sapv_verordnung($ipid =  null , $data = array())
	{
	    
	    if (empty($ipid) || ! is_array($data)) {
	        return;
	    }
	    /*
	    if ( ! empty($data['regulation_start'])) {
	        $date = new Zend_Date($data['regulation_start']);
	        $data['regulation_start'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
	        $data['regulation_start'] = null;
	    }
	    
	    if ( ! empty($data['regulation_end'])) {
	        $date = new Zend_Date($data['regulation_end']);
	        $data['regulation_end'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
	        $data['regulation_end'] = null;
	    }
	    
	    if ( ! empty($data['verordnungam'])) {
	        $date = new Zend_Date($data['verordnungam']);
	        $data['verordnungam'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
	        $data['verordnungam'] = null;
	    }
	    
	    if ( ! empty($data['verordnungbis'])) {
	        $date = new Zend_Date($data['verordnungbis']);
	        $data['verordnungbis'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
	        $data['verordnungbis'] = null;
	    }
	    
	    if ( ! empty($data['approved_date'])) {
	        $date = new Zend_Date($data['approved_date']);
	        $data['approved_date'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
	        $data['approved_date'] = null;
	    }
	    
	    if ( ! empty($data['verorddisabledate'])) {
	        $date = new Zend_Date($data['verorddisabledate']);
	        $data['verorddisabledate'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
	        $data['verorddisabledate'] = null;
	    }
	    */
	    
	    
	    
	    $data['bra_options'] = isset($data['bra_options']) ?  implode(",", $data['bra_options']) : null;
	    $data['verordnet'] = isset($data['verordnet']) ?  implode(",", $data['verordnet']) : null;
	    
	    
	    if (empty($data['verordnet_von'])) {
	        //we must create this
	        $fd_form = new Application_Form_Familydoctor(
	            array(
	                "_patientMasterData" => $this->_patientMasterData,
            ));
	        
	        $newFamilyDoctor =  $fd_form->save_form_family_doctor($ipid , [
	            'last_name' => $data['verordnet_von_nice_name'],
	            'indrop' => 1,
	            '__thisIsNotThePatientsFamilyDoctor' => true //this was added to fix TODO-1752
	        ]);
	        
	        $data['verordnet_von'] = $newFamilyDoctor->id;
	        $data['verordnet_von_type'] = 'family_doctor';
	    }
	    
	    $entity = new SapvVerordnung();
	    $oldEntity = array();
	    if($data['id'])
	    {
	    	$oldEntity = $entity->getTable()->findBy('id', $data['id'], Doctrine_Core::HYDRATE_RECORD);	    
	    	$oldEntity = $oldEntity->getData();
	    }	   	
	    
	    $olddatesarr = [
		    'regulation_start' => explode(' ', $oldEntity[0]['regulation_start'])[0],
		    'regulation_end' => explode(' ', $oldEntity[0]['regulation_end'])[0],
		    'verordnungam' => explode(' ', $oldEntity[0]['verordnungam'])[0],
		    'verordnungbis' => explode(' ', $oldEntity[0]['verordnungbis'])[0],
		    'verorddisabledate' => $oldEntity[0]['verorddisabledate'] != "0000-00-00 00:00:00" ? explode(' ', $oldEntity[0]['verorddisabledate'])[0] : "",
		    'approved_date' => $oldEntity[0]['approved_date'] != "0000-00-00 00:00:00" ? explode(' ', $oldEntity[0]['approved_date'])[0] : "",
	    ];
        //ISPC-2539, elena, 01.11.2020
	    if(empty($oldEntity) && (!isset($data['primary_set']) || $data['primary_set'] == null )){
            $data['primary_set'] = 0;
        }
        if(empty($oldEntity) && (!isset($data['secondary_set'])|| $data['secondary_set'] == null )){
            $data['secondary_set'] = 0;
        }
	    
        //TODO-3974 Lore 26.03.2021
        if(!isset($data['primary_set'])){
            if(empty($oldEntity)){
                $data['primary_set'] = 0;
            } else {
                $data['primary_set'] = $oldEntity[0]['primary_set'];
            }
        }
        if(!isset($data['secondary_set'])){
            if(empty($oldEntity)){
                $data['secondary_set'] = 0;
            } else {
                $data['secondary_set'] = $oldEntity[0]['secondary_set'];
            }
        }
        //.
        
	    $result = $entity->findOrCreateOneByIpidAndId($ipid, $data['id'], $data);
	    //ISPC-2539, elena, 28.10.2020
        $check_sets = false;
        //@todo i don't understand why primary_set and secondary_set aren't always saved directly.
        //@todo but i saw in debug that it didn't happen, although some other fields hadn't this problem
        //@todo i don't understand it, really, because doctrine have to update all fields, i see no difference
        //@todo that's why i try to do it manually, i hope it's safe - elena, 29.10.2020
	    if(empty($oldEntity)  || $oldEntity[0]['primary_set'] != $data['primary_set']){
            if($data['primary_set'] != $result->primary_set) {
                SapvVerordnung::setPrimarySet($ipid, $result->id, $data['primary_set']);
            }
	        // i force to let the system know that primary_set changed
            if(isset($oldEntity[0]['primary_set'])){
                $olddatesarr['primary_set'] = $oldEntity[0]['primary_set'];
            }else{
                $olddatesarr['primary_set'] = null;
            }

            $check_sets = true;


	        $vHis = new VerordnungSetStatusHistory();
            $logininfo = new Zend_Session_Namespace('Login_Info');
	        $vHis->clientid = $logininfo->clientid;
	        $vHis->sapv_verordnung_id = $result->id;
            $vHis->ipid = $ipid;
	        $vHis->value = $data['primary_set'];
	        $vHis->datum = date('Y-m-d', time());
	        $vHis->old_value = $oldEntity[0]['primary_set'] ;
	        $vHis->set_value = 'primary_set';
	        $vHis->save();

        }

        if(empty($oldEntity)  || $oldEntity[0]['secondary_set'] != $data['secondary_set']){
            if($data['secondary_set'] != $result->secondary_set){
                SapvVerordnung::setSecondarySet($ipid, $result->id, $data['secondary_set']);
            }
            // i force to let the system know that secondary_set changed
            if(isset($oldEntity[0]['secondary_set'])){
                $olddatesarr['secondary_set'] = $oldEntity[0]['secondary_set'];
            }else{
                $olddatesarr['secondary_set'] = null;
            }

            $check_sets = true;
            $vHis = new VerordnungSetStatusHistory();
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $vHis->clientid = $logininfo->clientid;
            $vHis->sapv_verordnung_id = $result->id;
            $vHis->value = $data['secondary_set'];
            $vHis->ipid = $ipid;
            $vHis->datum = date('Y-m-d', time());
            $vHis->old_value = $oldEntity[0]['secondary_set'] ;
            $vHis->set_value = 'secondary_set';
            $vHis->save();

        }

        // i ask method to check primary_set and secondary_set explicit
    	$this->_save_box_History($ipid, $result, $olddatesarr, 'grow11', false, $check_sets);

	    return $result;
	     
	
	
	}
	
	private function _save_box_History($ipid, $newEntity, $oldEntity = null, $formid, $deleted = false, $check_sets = false)
	{
		$excludedfieldarr = [
				'id',
				'fromdate',
				'tilldate',
				'change_date',
				'change_user',
				'ipid',
				'create_date',
				'create_user',
		];
		

		$datefieldarr = [
				'regulation_start',
				'regulation_end',
				'verordnungam',
				'verordnungbis',
				'verorddisabledate',
				'approved_date',
		];

		$sapv_verordnets = Pms_CommonData::getSapvCheckBox();
		$sapvradios = SapvVerordnung::getSapvRadios();
		$sapvextraradios = SapvVerordnung::getSapvExtraRadios();
		$sapvprimsec_set = SapvVerordnung::getSapvExtraStatusesRadios();
		

		$famdoc = new FamilyDoctor();
		$spec = new Specialists();
		$loc = new Locations();
		$history = [];
		
		if(!$deleted)
		{
			$newModifiedValues = $newEntity->getLastModified();

			if($check_sets){
			    // i check whether doctrine updated primary_set and secondary_set itself and knows it
			    $checkEntity = new SapvVerordnung();
			    $aNewData = $checkEntity->getTable()->find($newEntity->id)->getData();

			    if(!(isset($newModifiedValues['primary_set'] )) && isset($oldEntity['primary_set']) && $aNewData['primary_set'] != $oldEntity['primary_set']){
                    $newModifiedValues['primary_set'] = $aNewData['primary_set'];
                }
			    if(!(isset($newModifiedValues['secondary_set'] )) &&  isset($oldEntity['secondary_set']) && $aNewData['secondary_set'] != $oldEntity['secondary_set']){
                    $newModifiedValues['secondary_set'] = $aNewData['secondary_set'];
                }
            }


			$newModifiedValuesinitial = $newModifiedValues;
			$newModifiedValues = array();
			array_walk($newModifiedValuesinitial, function(&$item, $index, $excludedfieldarr) use (&$newModifiedValuesinitial, &$newModifiedValues){
				if(!in_array($index, $excludedfieldarr))
				{
					$newModifiedValues[$index] = $item;
				}
			}, $excludedfieldarr);
			
			$oldValues = $newEntity->getLastModified(true);
			$olddatesValues = $oldEntity;
			
			foreach($newModifiedValues as $kr=>$vr)
			{
				$added = array();
				if(!in_array($kr, $datefieldarr))
				{
					$old_values = $oldValues[$kr];
				}
				else
				{
					$old_values = $olddatesValues[$kr];
				}
				
				$added = [];
				
				$new_values = $vr;
		
				if($new_values != $old_values)
				{
					
					if($kr == 'verordnet_von')
					{								
						switch($newEntity->getData()['verordnet_von_type'])
						{						
							case 'family_doctor':
								$new_values_arr = $famdoc->getFamilyDoc($new_values);
								FamilyDoctor::beautifyName($new_values_arr);
								$new_values = $new_values_arr[0]['nice_name'];
								$old_values_arr = $famdoc->getFamilyDoc($old_values);
								FamilyDoctor::beautifyName($old_values_arr);
								$old_values = $old_values_arr[0]['nice_name'];
								$old_values = $old_values ? $old_values : null;
								
								break;
							case 'specialists':
								$new_values_arr = $spec->get_specialist($new_values);
								PatientSpecialists::beautifyName($new_values_arr);
								$new_values = $new_values_arr[0]['nice_name'];
								$old_values_arr = $spec->get_specialist($old_values);
								PatientSpecialists::beautifyName($old_values_arr);
								$old_values = $old_values_arr[0]['nice_name'];
								$old_values = $old_values ? $old_values : null;
								
								break;
							case 'locations':
								$new_values = $loc->getLocationbyId($new_values)[0];
								$old_values = $old_values ? $loc->getLocationbyId($old_values)[0] : null;
								
								break;
						}
					}
					
					if($kr == 'verordnet_von_type')
					{
						$new_values = $this->getColumnMapping($new_values);
						$old_values = $old_values ? $this->getColumnMapping($old_values) : null;
						
					}
					
					if($kr == 'verordnet')
					{
						$verarr = explode(',', $new_values);
						foreach($verarr as $vr)
						{
							$verr_name[] = $sapv_verordnets[$vr];
						}
						$new_values = implode(',', $verr_name);
						
						if($old_values)
						{
							$verarr = explode(',', $old_values);
							$verr_name = array();
							
							foreach($verarr as $vr)
							{
								$verr_name[] = $sapv_verordnets[$vr];
							}
							$old_values = implode(',', $verr_name);
						}
					}
					
					if($kr == 'sapv_order')
					{
						$new_values = $new_values == '1' ? 'Erstverordnung' : 'Folgeverordnung';
						$old_values = $old_values == '1' ? 'Erstverordnung' : $old_values == '2' ? 'Folgeverordnung' : null;
					}
					
					if($kr == 'status')
					{
						$new_values = $sapvradios[$new_values];
						$old_values = $old_values ? $sapvradios[$old_values] : null;
					}
					
					if($kr == 'extra_set')
					{
						$new_values = $sapvextraradios[$new_values];
						$old_values = $old_values ? $sapvextraradios[$old_values] : null;
					}
					
					if($kr == 'primary_set')
					{
						$new_values = $new_values != 0 ? $sapvprimsec_set[$new_values] : "";
						$old_values = $old_values ? $sapvprimsec_set[$old_values] : null;
					}
					
					if($kr == 'secondary_set')
					{
						$new_values = $new_values != 0 ? $sapvprimsec_set[$new_values] : "";
						$old_values = $old_values ? $sapvprimsec_set[$old_values] : null;
					}
					
					if($kr == 'after_opposition')
					{
						
						if($old_values !== null)
						{
							$new_values = $new_values == '0' ? 'nach Widerspruch entfernt' : 'nach Widerspruch hinzugefügt';
						}
						else 
						{
							$new_values = "";
						}
					}
					
					if(in_array($kr, $datefieldarr))
					{
						$new_values = date('d.m.Y', strtotime($new_values));
						$old_values = $old_values != "" ? date('d.m.Y', strtotime($old_values)) : null;
					}
					
					if($kr == 'after_opposition')
					{
						$added = [$new_values];
					}
					else
					{
						if($old_values)
						{
							$added = [$old_values, $new_values];
						}
						else 
						{
							$added = [$new_values];
						}
					}
				}
				
				$valnew = "";
				if ( ! empty($added)) {
					if(count($added) == 1)
					{
						$valnew = $added[0];
					}
					elseif(count($added) > 1)
					{						 
						$valnew = implode(" -> ", $added);
					}
					
					if($valnew !="")
					{
						$verstart = $olddatesValues['verordnungam'] ? $olddatesValues['verordnungam'] : $newModifiedValues['verordnungam'];
						$verend = $olddatesValues['verordnungbis'] ? $olddatesValues['verordnungbis'] : $newModifiedValues['verordnungbis'];
						$history[] = [
								'ipid' => $ipid,
								'clientid' => $this->logininfo->clientid,
								'formid' => $formid,
								//'fieldname' => $this->getColumnMapping($kr) . '(' . date('d.m.Y', strtotime($verstart)) . ' - ' . date('d.m.Y', strtotime($verend)) . ')',
								'fieldname' =>  date('d.m.Y', strtotime($verstart)) . ' - ' . date('d.m.Y', strtotime($verend)) . "<br />". str_repeat("_", 20) . "<br />" . $this->getColumnMapping($kr),
								'fieldvalue' => $valnew . $add_sufix,
						];
					}
				}
			}
		}
		else 
		{
			$sverorarr = $newEntity->getData();
			
			foreach($sverorarr as $kr=>$vr)
			{
				$new_values = "";
				if($vr && !in_array($kr, $excludedfieldarr))
				{
					if($kr == 'verordnet_von')
					{
						switch($sverorarr['verordnet_von_type'])
						{
							case 'family_doctor':
								$new_values_arr = $famdoc->getFamilyDoc($vr);
								FamilyDoctor::beautifyName($new_values_arr);
								$new_values = $new_values_arr[0]['nice_name'] . ' entfernt';
					
								break;
							case 'specialists':
								$new_values_arr = $spec->get_specialist($vr);
								PatientSpecialists::beautifyName($new_values_arr);
								$new_values = $new_values_arr[0]['nice_name'] . 'entfernt';
					
								break;
							case 'locations':
								$new_values = $loc->getLocationbyId($vr)[0] . ' entfernt';
					
								break;
						}
					}						
					elseif($kr == 'verordnet_von_type')
					{
						$new_values = $this->getColumnMapping($vr) . ' entfernt';
					
					}						
					elseif($kr == 'verordnet')
					{
						$verarr = explode(',', $vr);
						foreach($verarr as $vvr)
						{
							$verr_name[] = $sapv_verordnets[$vvr];
						}
						$new_values = implode(',', $verr_name) . ' entfernt';
					}						
					elseif($kr == 'sapv_order')
					{
						if($vr == '1')
						{
							$new_values = 'Erstverordnung' . ' entfernt';
						}
						elseif($vr == '2')
						{
						 	$new_values = 'Folgeverordnung' . ' entfernt';
						}
						else 
						{
							$new_values = '';
						}
					}						
					elseif($kr == 'status')
					{
						$new_values = $sapvradios[$vr] . ' entfernt';
					}						
					elseif($kr == 'extra_set')
					{
						$new_values = $sapvextraradios[$vr] . ' entfernt';
					}						
					elseif($kr == 'primary_set')
					{
						$new_values = $vr != 0 ? $sapvprimsec_set[$vr] . ' entfernt' : "";
					}						
					elseif($kr == 'secondary_set')
					{
						$new_values = $vr != 0 ? $sapvprimsec_set[$vr] . ' entfernt' : "";
					}					
					elseif($kr == 'after_opposition')
					{	
						$new_values = ($vr == '0' || $vr === null) ? '' : 'nach Widerspruch entfernt';
					}					
					elseif(in_array($kr, $datefieldarr))
					{
						if($sverorarr['status'] == '1')
						{
							if($kr == 'approved_date' && $vr != "0000-00-00 00:00:00")
							{
								$vr = "0000-00-00 00:00:00";
							}								
						}
						
						if($sverorarr['status'] == '2')
						{
							if($kr == 'verorddisabledate' && $vr != "0000-00-00 00:00:00")
							{
								$vr = "0000-00-00 00:00:00";
							}
						}
						
						$new_values = $vr != "0000-00-00 00:00:00" ? date('d.m.Y', strtotime($vr)) . ' entfernt' : '';
					}
					else 
					{
						if($sverorarr['status'] == '1')
						{
							if($kr == 'approved_number' && $vr != "")
							{
								$vr = "";
							}
						}
						
						$new_values = $vr . ' entfernt';
					}
					
					if($new_values != "")
					{
						$history[] = [
								'ipid' => $ipid,
								'clientid' => $this->logininfo->clientid,
								'formid' => $formid,
								//'fieldname' => $this->getColumnMapping($kr) . '(' . date('d.m.Y', strtotime($sverorarr['verordnungam'])) . ' - ' . date('d.m.Y', strtotime($sverorarr['verordnungbis'])) . ')',
								'fieldname' =>  date('d.m.Y', strtotime($sverorarr['verordnungam'])) . ' - ' . date('d.m.Y', strtotime($sverorarr['verordnungbis'])) . "<br />" . str_repeat("_", 20) . "<br />" . $this->getColumnMapping($kr),
								'fieldvalue' => $new_values . $add_sufix,
						];
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
	
	public function getColumnMapping($fieldName, $revers = false)
	{
	
		//             $fieldName => [ value => translation]
		$overwriteMapping = [
				'verordnet_von' => 'Verordner',
				'verordnet_von_type' => 'Verordner typ',
				'regulation_start' => 'Verordnungszeitraum am',
				'regulation_end' => 'Verordnungszeitraum bis',
				'verordnungam' => 'Genehmigungszeitraum am',
				'verordnungbis' => 'Genehmigungszeitraum bis',
				'verordnet' => 'Verordnet',
				'status' => 'Status',
				'after_opposition' => 'nach Widerspruch',
				'extra_set' => 'Status',
				'primary_set' => 'Verordnung',
				'secondary_set' => '2. Seite',
				'bra_options' => 'Verordnet',
				'family_doctor' => 'Hausarzt',
				'specialists' =>'Fachärzte',
				'locations' => 'Aufenthaltsorte',
				'sapv_order' => 'Verordnung Ort',
				'approved_date' => 'genehmigt am',
				'approved_number' => 'Genehmigungs-Nr.',
				'verorddisabledate' => 'abgelehnt am',
				'case_number' => 'Fallnummer',
				
		];
	
	
	//	$values = FormBlockAdverseeventsTable::getInstance()->getEnumValues($fieldName);
	
			
		//$values = array_combine($values, array_map("self::translate", $values));
	
		if (isset($overwriteMapping[$fieldName])) {
			$values = $overwriteMapping[$fieldName];// + $values;
		}
		
		return $values;
	
	}
	
	public function delete_form_patient_sapv_verordnung($ipid =  null , $data = array())
	{
		$model = 'SapvVerordnung';
		$primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
		
		$fn = "findOneBy" . Doctrine_Inflector::classify($primaryKey) . "AndIpid";
		
		if ($entity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid, Doctrine_Core::HYDRATE_RECORD)) {
		
			$entity->delete();

			$this->_save_box_History($ipid, $entity, null, 'grow11', true);
		}
	}
}

?>