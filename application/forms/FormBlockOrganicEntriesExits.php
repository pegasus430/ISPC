<?php
/**
 * 
 * @author carmen
 * Apr 09, 2020 ISPC-2518+ISPC-2520
 * #ISPC-2512PatientCharts
 */
class Application_Form_FormBlockOrganicEntriesExits extends Pms_Form
{
    
    protected $_model = 'FormBlockOrganicEntriesExits';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockOrganicEntriesExits::TRIGGER_FORMID;
    private $triggerformname = FormBlockOrganicEntriesExits::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockOrganicEntriesExits::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    
    protected $_client_options = null;
    //protected $_opensets = null;
    protected $_allsets = null; //ISPC-2661 Carmen
    
    public function __construct($options = null)
    {
    	if ( ! empty($options['_client_options'])) {
    		$this->_client_options = $options['_client_options'];
    		unset($options['_client_options']);
    	}
    	//ISPC-2661 pct 14 Carmen 16.09.2020
    	/* if ( ! empty($options['_opensets'])) {
    		$this->_opensets = $options['_opensets'];
    		unset($options['_opensets']);
    	} */
    	if ( ! empty($options['_allsets'])) {
    		$this->_allsets = $options['_allsets'];
    		unset($options['_allsets']);
    	}
    	//--
    
    	parent::__construct($options);
    
    }


    public function getColumnMapping($fieldName, $revers = false)
    {
    
        //             $fieldName => [ value => translation]
        $overwriteMapping = [
    
        ];
        
        $values = FormBlockSuckoffTable::getInstance()->getEnumValues($fieldName);
   
        $values = array_combine($values, array_map("self::translate", $values));
       	
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
        
        return $values;
    
    }
    
    
    
    
	public function create_form_block_organic_entries_exits ($values =  array() , $elementsBelongTo = null)
	{
// 	    dd($values);
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_block_organic_entries_exits");
	
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend($this->translate('organicentriesexits'));
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    $client_organic_select = array();
	    //$client_organic_select['all'] = self::translate('entries_exits');
	     
	    $client_organic_entries = array();
	    $client_organic_exits = array();
	     
	    foreach($this->_client_options as $lrow)
	    {
	    	if($lrow['type'] == 'entry')
	    	{
	    		$client_organic_entries[$lrow['id']] = $lrow['name'];
	    	}
	    	else
	    	{
	    		$client_organic_exits[$lrow['id']] = $lrow['name'];
	    	}
	    	$client_options[$lrow['id']] = $lrow;
	    	 
	    }
	    if(!empty($client_organic_entries))
	    {
	    	//$client_artificial_select['en'] = self::translate('entries');
	    	foreach($client_organic_entries as $kr => $vr)
	    	{
	    		$client_organic_select['organic_entries'][$kr] = $vr;
	    	}
	    }
	     
	    if(!empty($client_organic_exits))
	    {
	    	//$client_artificial_select['ex'] = self::translate('exits');
	    	foreach($client_organic_exits as $kr => $vr)
	    	{
	    		$client_organic_select['organic_exits'][$kr] = $vr;
	    	}
	    }
	    
	    //ISPC-2661 pct.14 Carmen 16.09.2020
	  
	    $opensets_details = array();
	    $opensets_details[''] = self::translate('select');
	    $allsets_details = array();
	    $allsets_details[''] = self::translate('select');
	    $opensetsids = array();

	    //foreach($this->_opensets as $ops)
	    foreach($this->_allsets as $ops)
	    {
	    	$comma = '';
	    	$opensets_details_str = '';
	    	$opensetd = FormBlockOrganicEntriesExitsTable::getInstance()->findBySetId($ops['id'], Doctrine_Core::HYDRATE_ARRAY);
	    	usort($opensetd, array(new Pms_Sorter('organic_date'), "_date_compare"));

	    	if(!empty($opensetd)){
	    		if($ops['endset'] == '0')
	    		{
                	$opensets_details[$ops['id']] = "Starte Bilanzierung: ".date("d.m.Y",strtotime($opensetd[0]['organic_date'])) ;
	    			$opensetsids[] = $ops['id'];
	    		}
	    		
	    		$allsets_details[$ops['id']] = "Starte Bilanzierung: ".date("d.m.Y",strtotime($opensetd[0]['organic_date'])) ;	    		
	    	}
	    }
	    
	    if(empty($opensetsids))
	    {
	    	unset($opensets_details['']);
	    }
	   
	    $all_set_data = FormBlockOrganicEntriesExitsTable::getInstance()->findByIpid($values['ipid'], Doctrine_Core::HYDRATE_ARRAY);
	    usort($all_set_data, array(new Pms_Sorter('organic_date'), "_date_compare"));
 
	    $open_set_data = array();
	    $entries2sets = array();
	    foreach($all_set_data as $k=>$ente){
	        if($ente['setid']!=0){
	            $sets_data[$ente['setid']][] = $ente;
	            $entries2sets[$ente['id']] = $ente['setid'];
	            if(!empty($opensetd) && $opensetd['id'] == $ente['setid']){
	                $open_set_data[] = $ente;
	            }
	        }
	    }
	    $entry_set_info = array();
	    foreach($sets_data as $set_id =>$set_Values){
	        $entry_set_info[$set_id]['first'] = $set_Values[0]['id'];
	        $last_of_Set[$set_id] = end($set_Values);
	        $entry_set_info[$set_id]['last'] = $last_of_Set[$set_id]['id'];
	    }
	    
		    $subform->addElement('hidden', 'id', array(
		        'label'        => null,
		        'value'        => ! empty($values['id']) ? $values['id'] : '',
		        'required'     => false,
		        'readonly'     => true,
		        'filters'      => array('StringTrim'),
		        'decorators' => array(
		            'ViewHelper',
		            array(array('data' => 'HtmlTag'), array(
		                'tag' => 'td',
		                'colspan' => 2,
		            )),
		            array(array('row' => 'HtmlTag'), array(
		                'tag' => 'tr',
		                'class'    => 'dontPrint',
		            )),
		        ),
		    ));
		    //ISPC-2661 pct.14 Carmen 16.09.2020
		    $display = ($values['id'] == '' && empty($opensetsids)) || ($entry_set_info[$values['setid']]['first'] == $values['id'] && $values['id'] != "" && $values['setid'] != 0) ? '':'display: none';
		    $subform->addElement('checkbox', 'startset', array(
		    		'checkedValue'    => '1',
		    		'uncheckedValue'  => '0',
		        'value'        =>  $entry_set_info[$values['setid']]['first'] == $values['id'] && $values['id'] != "" ? "1" : "0",
		    		'label'        => self::translate('start_bilancing'),
		    		'required'   => false,
		    		'decorators' => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
		    				array('Label', array('tag' => 'td')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'startsettr', 'class' => 'startset', 'style' => $display)),
		    		),
		    		'onchange' => 'if($(this).is(":checked")) { if($("#opensettr").is(":visible")) { opensetsorg=1; $("#opensettr").hide();} if( $("#endsettr").is(":visible")) {endsetorg =1; $("#endsettr").hide(); }} else {if(opensetsorg == 1) { $("#opensettr").show(); } if(endsetorg == 1) {$("#endsettr").show(); }}', 
		    ));
		    
		    //$display = (($values['id'] == '' && !empty($opensetsids)) || ($values['id'] != "" && $values['id'] != $entry_set_info[$values['setid']]['first'] && $values['id'] == $entry_set_info[$values['setid']]['last'])) ? '':'display:none';
		    $display = (($values['id'] == '' && !empty($opensetsids)) || ($values['id'] != "" && $values['id'] != $entry_set_info[$values['setid']]['first'] && $values['setid'] != 0)) ? '':'display:none';
		    $subform->addElement('checkbox', 'endset', array(
		    		'checkedValue'    => '1',
		    		'uncheckedValue'  => '0',
		            //'value'        =>  $values['id'] != '' && $entry_set_info[$values['setid']]['last'] == $values['id'] && !in_array($values['setid'], $opensetsids) ? "1" : "0",
		    		'value'        =>  $values['id'] != '' && !in_array($values['setid'], $opensetsids) && $values['setid'] != 0 ? "1" : "0",
		    		'label'        => self::translate('end_bilancing'),
		    		'required'   => false,
		    		'decorators' => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
		    				array('Label', array('tag' => 'td')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'endsettr', 'class' => 'endset', 'style' => $display)),
		    		),
		    		'onchange' => 'if($(this).is(":checked")) { if($("#startsettr").is(":visible")) { startsetsorg=1; $("#startsettr").hide();} } else {if(startsetsorg == 1) { $("#startsettr").show(); }}',
		    ));
		    
		    //$display = (($values['id'] == '' && !empty($opensetsids)) || ($values['id'] != 0 && $values['setid'] != 0)) ? '':'display:none';
		    $display = 'display: none';
		    $subform->addElement('select', 'opensets', array(
		    		//'multiOptions' => $opensets_details,
		    		'multiOptions' => $values['setid'] == 0 && !empty($opensetsids) ? $opensets_details : $allsets_details,
		    		'value'            => $values['setid'] != 0 ? $values['setid'] : (!empty($opensetsids) ? $opensetsids[0] : ''),
		    		'decorators' => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
		    				array('Label', array('tag' => 'td')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'opensettr', 'style' => $display)),
		    		),
		    		'attribs'    => (!empty($values['id'] && !empty($values['setid'])) || ($values['id'] == 0 && !empty($opensetsids))) ? array('disable' => 'disabled') : array(),
		    		'class' => (!empty($values['id'] && !empty($values['setid'])) || ($values['id'] == 0 && !empty($opensetsids))) ? 'opensets' : '',
		    		'onchange' => 'if($(this).val() != "") { if($("#startsettr").is(":visible")) { startsetsorg=1; $("#startsettr").hide();} } else {if(startsetsorg == 1) { $("#startsettr").show(); }}',
		    ));
		    //--
		    
		    $subform->addElement('select', 'organic_id', array(
		    		'label' 	   => self::translate('organic_entries_exits'),
		    		'multiOptions' => $client_organic_select,
		    		'value'        => $values['organic_id'],
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    		),
		    		 
		    		'onChange' => 'show_extrafields($(this).val());' ,
		    ));
		    
		    //$display = $values['organic_id'] == '' ? (!empty($client_organic_select)) ? null : 'display:none' : null;
		    $subform->addElement('text', 'organic_amount', array(
		    		'label'        => self::translate('organic_amount'),
		    		'value'        => $values['organic_amount'],
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		            'placeholder'  => 'Menge (in ml)',          //ISPC-2518 Lore 14.05.2020
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array('Errors'),
		    		    array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    		    array(array('row' => 'HtmlTag'), array('tag' => 'tr',  'openOnly' => true, 'class'=>'organic_more',/* 'style' => $display */)), //ISPC-2661 pct.14 Carmen 16.09.2020
		    		),
		    		 
		    ));
		    
		    ////ISPC-2517 pct.j Ancuta 20.05.2020
		    $subform->addElement('note', 'ml', array(
		        'value'        =>' ml',
		        'required'     => false,
		        'filters'      => array('StringTrim'),
		        'validators'   => array('NotEmpty'),
		        'class'        => 'amount_ml',
		        'decorators' =>   array(
		            'ViewHelper',
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
		            //array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true,  /*'style' => $display*/)),
		        ),
		    ));
		    
		    $subform->addElement('text', 'organic_date', array(
		    		'label'        => self::translate('organic_date'),
		    		'value'        => ! empty($values['organic_date']) ? date('d.m.Y', strtotime($values['organic_date'])) : date('d.m.Y'),
		    		'required'     => true,
		    		'filters'      => array('StringTrim'),
		    		'validators'   => array('NotEmpty'),
		    		'class'        => 'date option_date',
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true, 'class'=>'organic_more', /*'style' => $display*/)),
		    		),
		    
		    ));
		   
		    $organic_time = ! empty($values['organic_date']) ? date('H:i:s', strtotime($values['organic_date'])) : date("H:i");
		    $subform->addElement('text', 'organic_time', array(
		    		//'label'        => self::translate('clock:'),
		    		'value'        => $organic_time,
		    		'required'     => true,
		    		'filters'      => array('StringTrim'),
		    		'validators'   => array('NotEmpty'),
		    		'class'        => 'time option_time',
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
		    				//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true, 'class'=>'organic_more', /*'style' => $display*/)),
		    		),
		    ));
		    
		    //ISPC-2661 Carmen
		    $subform->addElement('hidden', 'setid', array(
		    		'label'        => null,
		    		'value'        => ! empty($values['setid']) ? $values['setid'] : '',
		    		'required'     => false,
		    		'readonly'     => true,
		    		'filters'      => array('StringTrim'),
		    		'decorators' => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td',
		    						'colspan' => 2,
		    						'style' => 'border-bottom: 0px;',
		    				)),
		    				array(array('row' => 'HtmlTag'), array(
		    						'tag' => 'tr',
		    						'class'    => 'dontPrint',
		    				)),
		    		),
		    ));
		    
		     
		    $subform->addElement('hidden', 'firstsetid', array(
		    		'label'        => null,
		    		'value'        => ! empty($entry_set_info[$values['setid']]['first']) ? $entry_set_info[$values['setid']]['first'] : '',
		    		'required'     => false,
		    		'readonly'     => true,
		    		'filters'      => array('StringTrim'),
		    		'decorators' => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td',
		    						'colspan' => 2,
		    						'style' => 'border-bottom: 0px;',
		    				)),
		    				array(array('row' => 'HtmlTag'), array(
		    						'tag' => 'tr',
		    						'class'    => 'dontPrint',
		    				)),
		    		),
		    ));
		     
		    $subform->addElement('hidden', 'lastsetid', array(
		    		'label'        => null,
		    		'value'        => ! empty($entry_set_info[$values['setid']]['last']) ? $entry_set_info[$values['setid']]['last'] : '',
		    		'required'     => false,
		    		'readonly'     => true,
		    		'filters'      => array('StringTrim'),
		    		'decorators' => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td',
		    						'colspan' => 2,
		    						'style' => 'border-bottom: 0px;',
		    				)),
		    				array(array('row' => 'HtmlTag'), array(
		    						'tag' => 'tr',
		    						'class'    => 'dontPrint',
		    				)),
		    		),
		    ));
		     
		    //--

	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	public function create_form_block_organic_extrafields ($values =  array() , $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->clearDecorators()
		->setDecorators( array(
				'FormElements',
				//array('HtmlTag',array('tag'=>'table', 'class' => 'formular_actions', 'style' => 'border: 1px solid #000;')),
		));
		$this->__setElementsBelongTo($subform, $elementsBelongTo);
		
		$extrafields = array();
		if(!empty($values['_extrafields']))
		{
			foreach($values['_extrafields'] as $Kr => $vr)
			{
				if(empty($extrafields[$vr['organic_extrafield']]))
				{
					$extrafields[$vr['organic_extrafield']][''] = $this->translate('select');
				}
				$extrafields[$vr['organic_extrafield']][$vr['id']] = $vr['organic_option'];
				
			}
			
			foreach($extrafields as $ko => $vo)
			{
				$subform->addElement('select', 'organic_'.$ko, array(
						'label' 	   => self::translate('organic_'.$ko.'_label'),
						'multiOptions' => $vo,
						'value'        => $values['values']['organic_'.$ko],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators' =>   array(
								'ViewHelper',
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow')),
						),
				));
			}
		}
		
		return $subform;
	}
	
	public function save_form_block_organic_entries_exits ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }

	    if(!$data['contact_form_id'])
	    {
	    	if($data['organic_time'] != "")
	    	{
	    		$data['organic_time'] = $data['organic_time'] . ":00";
	    	}
	    	else
	    	{
	    		$data['organic_time'] = '00:00:00';
	    	}
	    	 
	    	if($data['organic_date'] != "")
	    	{
	    		$data['organic_date'] = date('Y-m-d H:i:s', strtotime($data['organic_date'] . ' ' . $data['organic_time']));
	    	}
	    	else
	    	{
	    		$data['organic_date'] = '0000-00-00 00:00:00';
	    	}
	    	if(!$data['organic_color'])
	    	{
	    		$data['organic_color'] = '';
	    	}
	    	if(!$data['organic_type'])
	    	{
	    		$data['organic_type'] = '';
	    	}
	    }
    	
    	$data['ipid'] = $ipid;

	    	
	    //var_dump($data); exit;
	    //if not from charts
	   if($data['contact_form_id'])
	   {
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_organicentriesexits_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_organicentriesexits_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_organicentriesexits_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	   }
	   // TODO-4158 Ancuta 26.05.2021
	   else
	   {
	       $this->__save_organicentriesexits_patient_course($ipid , $data);
	   }
	   //-- 
	   
	    if($data['startset'] == '1' && $data['id'] == '')
	    {
	    	$dataset['id'] = '';
	    	$dataset['endset'] = 0;
	    	$dataset['ipid'] = $ipid;
	    	$result = OrganicEntriesExitsSetsTable::getInstance()->findOrCreateOneBy(['id'], [$dataset['id']], $dataset);
	    	$result = OrganicEntriesExitsSetsTable::findlastid_by_ipid($ipid);
	    	
	    }
	    
	    if($data['opensets'] != '')
	    {
	        $result = OrganicEntriesExitsSetsTable::findlastid_by_ipid($ipid);
	    }
	    
	  
	    if($data['endset'] == '1' && $data['opensets'] != '')
	    {
	    	//ISPC-2661 Carmen
	    	$opensetd = FormBlockOrganicEntriesExitsTable::getInstance()->findBySetId($data['setid'], Doctrine_Core::HYDRATE_ARRAY);
	    	usort($opensetd, array(new Pms_Sorter('organic_date'), "_date_compare"));
	    	
	    	foreach($opensetd as $kopd => $vopd)
	    	{
	    		if($kopd == 0) continue;
	    		if($kopd != $data['id'] && strtotime($vopd['organic_date']) > strtotime($data['organic_date'])){
	    			$todelfromset = FormBlockOrganicEntriesExitsTable::getInstance()->find($vopd['id']);
	    			if($todelfromset)
	    			{
	    				/* $todelfromset->setid = 0;
	    				$todelfromset->save(); */
	    				$todelfromset->delete();
	    			}
	    		}
	    	}
	    	//--
	    	$dataset['id'] = $data['opensets'];
	    	$dataset['endset'] = 1;
	    	$result = OrganicEntriesExitsSetsTable::getInstance()->findOrCreateOneBy(['id'], [$dataset['id']], $dataset);
	    }
	    
	    if(!$data['endset'] && $data['id'] != '' && $data['id'] == $data['lastsetid'] && $data['setid'] != '')
	    {
	    	$result = OrganicEntriesExitsSetsTable::getInstance()->findset($data["setid"], Doctrine_Core::HYDRATE_RECORD);
	    	$result->endset = 0;
	    	$result->save();
	    	
	    }
	    
	    if($result->id)
	    {	    	
	    	$data['setid'] = $result->id;	    	
	    }
	    
	    $entity = FormBlockOrganicEntriesExitsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    return $entity;
	}
	
	/**
	 * !! $data used by reference
	 *
	 * copy-paste the old saved values of the block, when this user has no access to this block
	 *
	 * @param string $ipid
	 * @param array $data
	 */
	private function __save_form_organicentriesexits_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || in_array('suckoff', $data['__formular']['allowed_blocks'])
	    )
	    {
	        return;
	    }
	     
	     
	    $oldValues = FormBlockOrganicEntriesExitsTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	         
	        unset($oldValues[FormBlockOrganicEntriesExitsTable::getInstance()->getIdentifier()]);
	        	
	        $data = array_merge($data, $oldValues);
	        $data['contact_form_id'] = $data['__formular']['contact_form_id'];
	       
	    }
	     
	}
	
	/**
	 * write or erase the patientcourse text
	 *
	 * @param string $ipid
	 * @param unknown $data
	 */
	private function __save_form_organicentriesexits_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || ! in_array('organic_entries_exits', $data['__formular']['allowed_blocks']))
	    {
	        return;
	    }
	     
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	     
	    if ( ! in_array('organic_entries_exits', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	     
	    
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_organicentriesexits_patient_course_format($data);
	   
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	         
	        $oldValues = FormBlockOrganicEntriesExitsTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	        
	        if (empty($oldValues)) {
	             
	            //missing previous values, so we save
	            $save_2_PC = true ;
	             
	        } else {
	             
	            $course_arr_OLD =  $this->__save_form_organicentriesexits_patient_course_format($oldValues);
	           
	            if ($course_arr_OLD === $course_arr) {
	                //same pc... nothing to insert
	            } else {
	                $save_2_PC = true ;
	            }
	             
	        }
	         
	    }
	     
	     
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockOrganicEntriesExitsTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
	    {
	        $course_str =  implode(PHP_EOL, $course_arr);
	        $pc_listener->setOption('disabled', false);
	        $pc_listener->setOption('course_title', $course_str);
	        $pc_listener->setOption('done_date', date('Y-m-d H:i:s', strtotime($data['__formular']['date'] . ' ' . $data['__formular']['begin_date_h'] . ':' . $data['__formular']['begin_date_m'] . ':00' )));
	        $pc_listener->setOption('user_id', $this->logininfo->userid);
	         
	    } elseif ($save_2_PC
	        && empty($course_arr)
	        && ! empty($formular['old_contact_form_id']))
	    {
	        //must manualy remove from PC this option
	        $pc_entity = new PatientCourse();
	        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockInfusion::PATIENT_COURSE_TABNAME);
	
	    }
	     
	}
	
	
	/**
	 * format the patientcourse title message
	 *
	 * @param unknown $data
	 * @return multitype:string
	 */
	private function __save_form_organicentriesexits_patient_course_format($data = [])
	{
	    $course_arr = [];
	   
	    
	    
	    return $course_arr;
	}
	
	/**
	 * set isdelete = 1 for the old block
	 *
	 * @param string $ipid
	 * @param number $contact_form_id
	 * @return boolean
	 */
	private function __save_form_organicentriesexits_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockOrganicEntriesExitsTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->execute();
	         
	        return true;
	    }
	}
	
	

	/**
	 * TODO-4158 Ancuta 26.05.2021
	 * @param unknown $ipid
	 * @param array $data
	 */
	private function __save_organicentriesexits_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data) )
	    {
	        return;
	    }

	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    $client_options = OrganicEntriesExitsListsTable::getInstance()->findAllOptions($clientid);
	    
	    $cl_opt_details = array();
	    foreach($client_options as $k=>$co){
	        $cl_opt_details[$co['id']] = $co['name'];
	    }
	    
	    
	    if(empty($data['id'])){
	        $comment = "Eine Ein- und Ausfuhr wurde erfasst: ".$cl_opt_details[$data['organic_id']];
	    } else{
	        $comment = "Eine Ein- und Ausfuhr wurde geändert: ".$cl_opt_details[$data['organic_id']];
	    }
	    
	    $cust = new PatientCourse();
	    $cust->ipid = $ipid;
	    $cust->course_date = date("Y-m-d H:i:s", time());
	    $cust->course_type = Pms_CommonData::aesEncrypt('K');
	    $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
	    $cust->tabname = Pms_CommonData::aesEncrypt(addslashes('FormBlockOrganicEntriesExits'));
	    $cust->user_id = $userid;
	    $cust->save();
	    
	}
	
	
	
	
	
	
	
}