<?php
require_once("Pms/Form.php");
/**
 * ISPC-2654 Ancuta 07.10.2020
 * @author  Oct 7, 2020  ancuta
 *
 */

class Application_Form_PatientMre extends Pms_Form
{
    public function getColumnMapping($fieldName, $revers = false)
    {
        $overwriteMapping = [
//             'option_status' => array('ok' => 'in Ordnung', 'not ok' => 'Nicht in Ordnung')
        ];
        
        $values = PatientMreTable::getInstance()->getEnumValues($fieldName);
        
        
        $values = array_combine($values, array_map("self::translate", $values));
        
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
        
        if( $fieldName == 'pathogen')
        {
        	$values_empty[''] = self::translate('select'); //ISPC-266 carmen 31.08.2020
        	$values = $values_empty+ $values;
        }
        
        return $values;
        
    }
    
 
    public function create_mre_list($options = array(), $ipid){
        
    }
    
    public function create_mre($blockname, $options,$ipid){


        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
        
    	foreach($options as $kops => $vops)
        {
        	$data['mredata'][$kops]['id'] = $vops['id'];
        	if($vops['pathogen'] == 'pathogen_other')
        	{
        		$data['mredata'][$kops]['pathogen'] = $this->translate($vops['pathogen']). " " .$this->translate($vops['pathogen_other']);
        	}
        	else 
        	{
        		$data['mredata'][$kops]['pathogen'] = $this->translate($vops['pathogen']);
        	}
        	$data['mredata'][$kops]['first_pathogen_date'] = $vops['first_pathogen_date'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($vops['first_pathogen_date'])) : '';
        	$data['mredata'][$kops]['last_pathogen_date'] = $vops['last_pathogen_date'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($vops['last_pathogen_date'])) : '';
        	$data['mredata'][$kops]['negative_evidence_date'] = $vops['negative_evidence_date'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($vops['negative_evidence_date'])) : '';
        	if($vops['rehabilitation'])
        	{
        		$data['mredata'][$kops]['rehabilitation'] = $this->translate($vops['rehabilitation']);
        	}
        	ELSE
        	{
        		$data['mredata'][$kops]['rehabilitation'] = '';
        	}
        	if($vops['rehabilitation_status'])
        	{
        		$data['mredata'][$kops]['rehabilitation_status'] = $this->translate($vops['rehabilitation_status']);
        	}
        	else
        	{
        		$data['mredata'][$kops]['rehabilitation_status'] = '';
        	}
        	
        	if(empty($vops['localization'])){
        	    $data['mredata'][$kops]['localization'] =""; 
        	} else{
        	    
            	if($vops['localization'] == 'wound')
            	{
            		$data['mredata'][$kops]['localization'] = $this->translate($vops['localization']).' '.$vops['localization_wound'];
            	}
            	elseif ($vops['localization'] == 'device_type')
            	{
            		$data['mredata'][$kops]['localization'] = $this->translate($vops['localization']).' '.$vops['localization_device'];
            	}
            	elseif ($vops['localization'] == 'other_localization')
            	{
            		$data['mredata'][$kops]['localization'] = $this->translate($vops['localization_other']);
            	}
            	else
            	{        	
            		$data['mredata'][$kops]['localization'] = $this->translate($vops['localization']);
            	}
        	}
        	
        	
        	
        	$data['mredata'][$kops]['carrier_status'] = '';
        	$comma = '';
        	if($vops['carrier_status'])
        	{
        		foreach($vops['carrier_status'] as $cas)
        		{
        			$data['mredata'][$kops]['carrier_status'] .= $comma . $this->translate($cas);
        			$comma = ',';
        		}
        	}
        	
        	$data['mredata'][$kops]['actions'] = '<span class="edit_mre" data-entry_id = "' . $vops["id"] . '"><img title="'.$this->translate("edit").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/edit.png" /></span><span class="delete_mre" data-entry_id = "' . $vops["id"] . '"><img title="'.$this->translate("delete").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_delete.png" /></span>';
        	
        }

        $blockconfig = array(
            'blockname' => "MRE",
            'template' => 'patient_diagnosis_mre.phtml',
            'formular_type' => $pdf,
        );

        return $this->create_subform_ui($blockconfig, $data );


    }

    public function create_subform_ui($blockconfig,  $data){

        $newview = new Zend_View();

        foreach ($data as $key=>$value){
            $newview->$key = $value;
        }
        // necessary for Baseassesment Pflege, does nothing with another form blocks
        $newview->blockconfig = $blockconfig;
        $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
        $html = $newview->render($blockconfig['template']);

        return $html;
    }
    
    public function create_form ($values =  array() , $elementsBelongTo = null)
    {
        // 	    dd($values);
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName , "create_form_isValid");
        
        $this->mapSaveFunction($__fnName , "save_mre");
        
        $subform = $this->subFormTable([
            'id' => "mre_Table",
            'class'    => null ]);
        $subform->addDecorator('Form',array('class'=>'mre_from_add', 'id'=>'mre_form','method'=>'post'));
        $subform->removeDecorator('Fieldset');
        
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
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
        
 
        //Erreger
        $subform->addElement('select', 'pathogen', array(
            'label' 	   => self::translate('pathogen'),
            'multiOptions' => $this->getColumnMapping('pathogen'),
            'value'        => $values['pathogen'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators' =>   array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if ($(this).val() == "pathogen_other") { $(".pathogen_other", $(this).parents("table")).show();} else {$(".pathogen_other", $(this).parents("table")).hide(); $("#pathogen_other").val("");}',
        ));
        
        $subform->addElement('note', 'Note_pathogen_err', array(
        		'value'        => $this->translate('pathogen_err'),
        		'decorators'   => array(
        				'ViewHelper',
        				array(array('data' => 'HtmlTag'), array(
        						'tag' => 'td', 'colspan' => 2,
        				)),
        				array(array('row' => 'HtmlTag'), array(
        						'tag'      => 'tr', 'id' => 'pathogen_error',
        				)),
        		),
        ));
 
        $display = $values['pathogen'] != "pathogen_other" ? 'display:none' : null;
        $subform->addElement('text', 'pathogen_other', array(
            'label' 	   => self::translate('pathogen_other'),
            'value'        => $values['pathogen_other'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            //'placeholder'  => $this->translate('freetext'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'pathogen_other', 'style' => $display )),
            ),
            
        ));

        //Datum erster Erregernachweis 
        $subform->addElement('text', 'first_pathogen_date', array(
            'label'        => self::translate('first_pathogen_date'),
            'value'        => ! empty($values['first_pathogen_date']) && $values['first_pathogen_date'] != "0000-00-00 00:00:00" ? date('d.m.Y', strtotime($values['first_pathogen_date'])) : "",
            'required'     => false,
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
        
        //Sanierung
        $subform->addElement('radio', 'rehabilitation', array(
        		'label' 	   => self::translate('rehabilitation'),
        		'multiOptions' => $this->getColumnMapping('rehabilitation'),
        		'value'        => $values['rehabilitation'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'decorators' =>   array(
        				'ViewHelper',
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
        		),
        		'onChange' => 'if ($(this).val() == "reh_yes") { $(".rehabilitation_status_other", $(this).parents("table")).show();} else {$(".rehabilitation_status_other", $(this).parents("table")).hide(); $("input[name=\'rehabilitation_status\']").removeAttr("checked");}',
        ));
        
        
        $display = $values['rehabilitation'] != "reh_yes" ? 'display:none' : null;
        $subform->addElement('radio', 'rehabilitation_status', array(
        		'label' 	   => self::translate('rehabilitation_status'),
        		'multiOptions' => $this->getColumnMapping('rehabilitation_status'),
        		'value'        => $values['rehabilitation_status'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'decorators' =>   array(
        				'ViewHelper',
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'rehabilitation_status_other', 'style' => $display )),
        		),
        ));
        
        // Datum letzter Erregernachweis
        $subform->addElement('text', 'last_pathogen_date', array(
        		'label'        => self::translate('last_pathogen_date'),
        		'value'        => ! empty($values['last_pathogen_date']) && $values['last_pathogen_date'] != "0000-00-00 00:00:00" ? date('d.m.Y', strtotime($values['last_pathogen_date'])) : date('d.m.Y'),
        		'required'     => false,
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

        
        //Lokalisation
        $subform->addElement('radio', 'localization', array(
            'label' 	   => self::translate('localization'),
            'multiOptions' => $this->getColumnMapping('localization'),
            'value'        => $values['localization'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators' =>   array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if ($(this).val() == "wound") { $(".localization_wound_other", $(this).parents("table")).show();} else {$(".localization_wound_other", $(this).parents("table")).hide(); $("#localization_wound").val("");}
        				   if ($(this).val() == "device_type") { $(".localization_device_other", $(this).parents("table")).show();} else {$(".localization_device_other", $(this).parents("table")).hide(); $("#localization_device").val("")}
        				   if ($(this).val() == "other_localization") { $(".localization_other_other", $(this).parents("table")).show();} else {$(".localization_other_other", $(this).parents("table")).hide(); $("#localization_other").val("")}
        				  ',
        ));
        
        $display = ($values['localization'] != 'wound') ? 'display:none' : null;
        $subform->addElement('text', 'localization_wound', array(
        		'label' 	   => self::translate('localization_wound'),
        		'value'        => $values['localization_wound'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'placeholder'  => $this->translate('freetext'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'localization_wound_other', 'style' => $display )),
        		),
        		 
        ));
        
        $display = ($values['localization'] != 'device_type') ? 'display:none' : null;
        $subform->addElement('text', 'localization_device', array(
        		'label' 	   => self::translate('localization_device'),
        		'value'        => $values['localization_device'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'placeholder'  => $this->translate('freetext'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'localization_device_other', 'style' => $display )),
        		),
        		 
        ));
        
        $display = ($values['localization'] != 'other_localization') ? 'display:none' : null;
        $subform->addElement('text', 'localization_other', array(
        		'label' 	   => self::translate('localization_other'),
        		'value'        => $values['localization_other'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'placeholder'  => $this->translate('freetext'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'localization_other_other', 'style' => $display )),
        		),
        		 
        ));
        
        //negativer Nachweis (Datum)
        $subform->addElement('text', 'negative_evidence_date', array(
        		'label'        => self::translate('negative_evidence_date'),
        		'value'        => ! empty($values['negative_evidence_date']) && $values['negative_evidence_date'] != "0000-00-00 00:00:00" ? date('d.m.Y', strtotime($values['negative_evidence_date'])) : "",
        		'required'     => false,
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
        
        //Trägerstatus
        $subform->addElement('multiCheckbox', 'carrier_status', array(
            'multiOptions' => $this->getColumnMapping('carrier_status'),
            'value'        => $values['carrier_status'],
            'label'        => self::translate('carrier_status'),
            'required'   => false,
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        
        return $this->filter_by_block_name($subform, $__fnName);
    }
    
    
    public function save_mre ($ipid =  null , $data =  array())
    {
        if (empty($ipid) || empty($data)) {
            return;
        }
 
        
        if($data['first_pathogen_date'] != "")
        {
            $data['first_pathogen_date'] = date('Y-m-d H:i:s', strtotime($data['first_pathogen_date']));
        }
        else
        {
            $data['first_pathogen_date'] = '0000-00-00 00:00:00';
        }
        if($data['last_pathogen_date'] != "")
        {
            $data['last_pathogen_date'] = date('Y-m-d H:i:s', strtotime($data['last_pathogen_date']));
        }
        else
        {
            $data['last_pathogen_date'] = '0000-00-00 00:00:00';
        }
        if($data['negative_evidence_date'] != "")
        {
            $data['negative_evidence_date'] = date('Y-m-d H:i:s', strtotime($data['negative_evidence_date']));
        }
        else
        {
            $data['negative_evidence_date'] = '0000-00-00 00:00:00';
        }
        
        $data['ipid'] = $ipid;
        
        if($data['id'])
        {
        	$course_text = 'MRE wurde editiert' . "\n";
        	$olddata = PatientMreTable::getInstance()->find($data['id'], Doctrine_Core::HYDRATE_ARRAY);
        }
        else
        {
        	$course_text = 'MRE wurde hinzugefügt' . "\n";
        	$olddata = array();
        }
       
        $course_arr = array();
        $has_changed = false;
        foreach($data as $kd => $vd)
        {
        	
        	if(!is_array($vd ))
        	{
        		if($vd != $olddata[$kd] && $vd != "" && $kd != "localization_wound" && $kd != "localization_device" && $kd != "localization_other" && $kd != 'ipid' && $kd != "pathogen_other")
        		{
	        		if($kd == "first_pathogen_date" || $kd == "last_pathogen_date" || $kd == "negative_evidence_date")
	        		{
	        			if($vd != '0000-00-00 00:00:00')
	        			{
	        				$course_arr[] = $this->translate($kd) . ': ' . date('d.m.Y', strtotime($vd)); 
	        			}
	        			elseif($olddata[$kd] != '0000-00-00 00:00:00' && $olddata[$kd])
	        			{
	        				$course_arr[] = $this->translate($kd) . ': ' . '';
	        			}
	        			else 
	        			{
	        				continue;
	        			}
	        		}
	        		elseif($kd == "localization" && $vd == "wound")
	        		{        			
	        			$course_arr[] = $this->translate($kd) . ': ' . $this->translate('wound'). ' '.$data['localization_wound'];
	        		}
	        		elseif($kd == "localization" && $vd == "device_type")
	        		{
	        			$course_arr[] = $this->translate($kd) . ': ' . $this->translate('device_type'). ' '.$data['localization_device'];
	        		}
	        		elseif($kd == "localization" && $vd == "other_localization")
	        		{
	        			$course_arr[] = $this->translate($kd) . ': ' . $this->translate('other_localization'). ' '.$data['localization_other'];
	        		}
	        		elseif($kd == "pathogen" && $vd == "pathogen_other")
	        		{
	        			$course_arr[] = $this->translate($kd) . ': ' . $this->translate('pathogen_other'). ' '.$data['pathogen_other'];
	        		}
	        		else 
	        		{
	        			$course_arr[] = $this->translate($kd) . ': ' . $this->translate($vd);
	        		}
	        		$has_changed = true;
        		}        		
        	}
        	else
        	{
        		if(count($vd ) != count($olddata[$kd]))
        		{
        			$comma = '';
        			$course_temp = '';
        			foreach($vd as $vdv)
        			{
        				$course_temp .= $comma . $this->translate($vdv);	
        				$comma = ',';
        			}
        			$course_arr[] = $this->translate($kd) . ': ' . $course_temp;
        			$has_changed = true;
        		}
        		else 
        		{
        			
        			foreach($vd as $kvdv => $vdv)
        			{        				
        				if($vdv != $olddata[$kd][$kvdv])
        				{
        					$course_arr[] = $this->translate($kd) . ': ' . $this->translate($vdv);
        					$has_changed = true;
        				}
        			}
        		}
        		
        	}
        }
       
        $patmre = new PatientMre();
        if ( $has_changed && $data['id']
        		&& $pc_listener = $patmre->getListener()->get('PostUpdateWriteToPatientCourse'))
        {
        	
        	$change_date = "";//removed from pc; ISPC-2071
        	$done_date = date('Y-m-d H:i:s');
        	$userid = $this->logininfo->userid;
        	$course_text .= implode("\n", $course_arr);
        	 
        	$pc_listener->setOption('disabled', false);
        	$pc_listener->setOption('course_title', $course_text);
        	$pc_listener->setOption('course_type', 'K');
        	$pc_listener->setOption('ipid', $ipid);
        	 
        	$pc_listener->setOption('done_date', $done_date);
        	$pc_listener->setOption('user_id', $userid);
        	 
        	 
        }
        elseif ( $has_changed && !$data['id']
        		&& $pc_listener = $patmre->getListener()->get('PostInsertWriteToPatientCourse'))
        {
        	$change_date = "";//removed from pc; ISPC-2071
        	$done_date = date('Y-m-d H:i:s');
        	$userid = $this->logininfo->userid;
        	$course_text .= implode("\n", $course_arr);
        	
        	$pc_listener->setOption('disabled', false);
        	$pc_listener->setOption('course_title', $course_text);
        	$pc_listener->setOption('course_type', 'K');
        	$pc_listener->setOption('ipid', $ipid);
        	
        	$pc_listener->setOption('done_date', $done_date);
        	$pc_listener->setOption('user_id', $userid);
        }
 
        $entity = PatientMreTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
        
        
        return $entity;
    }



}