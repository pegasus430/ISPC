<?php
require_once("Pms/Form.php");
/**
 * ISPC-2654 Carmen 07.10.2020
 * @author  Oct 7, 2020  carmen
 *
 */

class Application_Form_PatientOps extends Pms_Form
{
    public function getColumnMapping($fieldName, $revers = false)
    {
        $overwriteMapping = [
//             'option_status' => array('ok' => 'in Ordnung', 'not ok' => 'Nicht in Ordnung')
        ];
        
        $values = PatientOpsTable::getInstance()->getEnumValues($fieldName);
        
        
        $values = array_combine($values, array_map("self::translate", $values));
        
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
        
        return $values;
        
    }
   
    public function create_ops($blockname, $options,$ipid){


        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
        
        foreach($options as $kops => $vops)
        {
        	$data['opsdata'][$kops]['opscode'] = $vops['opscode'];
        	$data['opsdata'][$kops]['operation'] = $vops['operation'];
        	$data['opsdata'][$kops]['operation_date'] = $vops['operation_date'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($vops['operation_date'])) : '';
        	$data['opsdata'][$kops]['operation_place'] = $vops['operation_place'];
        	$data['opsdata'][$kops]['operation_comment'] = $vops['operation_comment'];
        	$data['opsdata'][$kops]['actions'] = '<span class="edit_ops" data-entry_id = "' . $vops["id"] . '"><img title="'.$this->translate("edit").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/edit.png" /></span><span class="delete_ops" data-entry_id = "' . $vops["id"] . '"><img title="'.$this->translate("delete").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_delete.png" /></span>';
        	
        }
		
        $blockconfig = array(
            'blockname' => "OPS",
            'template' => 'patient_diagnosis_ops.phtml',
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
        
        $this->mapSaveFunction($__fnName , "save_ops");
        
        $subform = $this->subFormTable([
            'id' => "ops_Table",
            'class'    => null ]);
        $subform->addDecorator('Form',array('class'=>'ops_from_add', 'id'=>'ops_form','method'=>'post'));
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
        
        $subform->addElement('text', 'opscode', array(
        		'label' 	   => self::translate('opscoded'),
        		'value'        => $values['opscode'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'placeholder'  => $this->translate('freetext'),
        		'id' => 'opscode_0',
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true)),
        				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => $display, 'openOnly' => true )),
        		),
        		'class'=>'ops_code livesearchopscode'
        ));

        $subform->addElement('hidden', 'hidd_opscode', array(
        		//'label' 	   => self::translate('opscode'),
        		'value'        => "",
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'placeholder'  => $this->translate('freetext'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'div', 'placement' => 'APPEND', 'id' => 'opsdropdown[0]', 'style' => 'position:absolute;display:none;', 'class' => 'listPatMedEd livesearchdropdown' )),
        				//array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'ops_code livesearchopscode', 'style' => $display )),
        		),
        
        ));
        
        $subform->addElement('text', 'operation', array(
        		'label' 	   => self::translate('operation'),
        		'value'        => $values['operation'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'placeholder'  => $this->translate('freetext'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'operation', )),
        		),
        		'id' => 'operation',
        		'style' => 'width: 350px;'
        ));
        
        $subform->addElement('text', 'operation_place', array(
        		'label' 	   => self::translate('operation_place'),
        		'value'        => $values['operation_place'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'placeholder'  => $this->translate('freetext'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'operation_place', )),
        		),
        		'style' => 'width: 350px;'
        ));
        
        $subform->addElement('text', 'operation_date', array(
        		'label'        => self::translate('operation_date'),
        		'value'        => ! empty($values['operation_date']) ? date('d.m.Y', strtotime($values['operation_date'])) : "",
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
        
        $subform->addElement('text', 'operation_comment', array(
        		'label' 	   => self::translate('operation_comment'),
        		'value'        => $values['operation_comment'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'placeholder'  => $this->translate('freetext'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'operation_comment',  )),
        		),
        		'style' => 'width: 350px;'
        ));
        
        return $this->filter_by_block_name($subform, $__fnName);
    }
    
    
    public function save_ops ($ipid =  null , $data =  array())
    {
        if (empty($ipid) || empty($data)) {
            return;
        }
 
        
        if($data['operation_date'] != "")
        {
            $data['operation_date'] = date('Y-m-d H:i:s', strtotime($data['operation_date']));
        }
        else
        {
            $data['operation_date'] = '0000-00-00 00:00:00';
        }
        
        $data['ipid'] = $ipid;

        $entity = PatientOpsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
        
        
        return $entity;
    }



}