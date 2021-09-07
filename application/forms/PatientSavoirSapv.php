<?php
/**
 * 
 * @author claudiu
 * 
 * 08.01.2018
 *
 */
class Application_Form_PatientSavoirSapv extends Pms_Form
{
	
	private $triggerformid = PatientSavoirSapv::TRIGGER_FORMID;
	private $triggerformname = PatientSavoirSapv::TRIGGER_FORMNAME;
	protected $_translate_lang_array = PatientSavoirSapv::LANGUAGE_ARRAY;
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	
	
	/**
	 * ISPC-2144
	 * fn created for patientformnew/savoir
	 *
	 * @param array $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_patient_savoir_sapv(array $values = array(array()), $elementsBelongTo = null)
	{
	     
	    $subform = $this->subFormTable(array(
	        'columns' => array(
	            $this->translate('verordnetvon'),
	            $this->translate('validfrom'),
	            $this->translate('validtill'),
	            $this->translate('verordnet'),
	            $this->translate('sapv symptom'),
	            $this->translate('sapv prescriber'),
	        ),
	        'class' => 'PatientSavoirSapvTable',
	    ));
	    $subform->removeDecorator('Fieldset');
	    $subform->setAttrib("class", "label_same_size_auto");
	     
	     
	     
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    $row_cnt = 0;
	    foreach ($values['SapvVerordnung'] as $pat_sapv) {
	        
	        if (empty($pat_sapv)) continue;
	         
	        $saved_patient_sapvverordnung_symptom = null;
	        $saved_patient_sapvverordnung_prescriber = null;
	        
	        foreach ($values['PatientSavoirSapv'] as $pss) {
	            
	            if ($pss['patient_sapvverordnung_id'] == $pat_sapv['id']) {
	                
	                $saved_patient_sapvverordnung_symptom = $pss['patient_sapvverordnung_symptom'] ;
	                $saved_patient_sapvverordnung_prescriber = $pss['patient_sapvverordnung_prescriber'] ;
	                
	                break;
	            }
	        }
	        
	        $sapv = new SapvVerordnung();
	        $verordnet_von = $sapv->get_verordnet_von($pat_sapv['verordnet_von'], $pat_sapv['verordnet_von_type']);
	        
	        $verordnet = SapvVerordnung::getVerordnetAsShorttext($pat_sapv['verordnet']);
	        
	        $statuscolorsarray = SapvVerordnung::getDefaultStatusColors();
            $color= $statuscolorsarray[$pat_sapv['status']];
	        
	       
	        
	        $subform_row = new Zend_Form_SubForm();
	        $subform_row->clearDecorators()->setDecorators(array('FormElements'));
	         
	        if ( ! is_null($elementsBelongTo)) {
	            $subform_row->setOptions(array(
	                'elementsBelongTo' => $elementsBelongTo
	            ));
	        }
	        
	        $subform_row->addElement('note', 'verordnet_von', array(
	            'value'    => $verordnet_von,
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element diagicd')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true , 'class' => "print_first_row")),
	            ),
	        ));
	        $subform_row->addElement('note', 'verordnungam', array(
	            'value'        => $pat_sapv['verordnungam'] != '0000-00-00 00:00:00' ? date('d.m.Y',strtotime($pat_sapv['verordnungam'])) : '-',
	            'decorators'   => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            ),
	        ));
	        $subform_row->addElement('note', 'verordnungbis', array(
	            'value'        => $pat_sapv['verordnungbis'] != '0000-00-00 00:00:00' ? date('d.m.Y',strtotime($pat_sapv['verordnungbis'])) : '-',
	            'decorators'   => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            ),
	        ));
	        $subform_row->addElement('note', 'verordnet', array(
	            'value'        => $verordnet,
	            'decorators'   => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style'=>"color:{$color}")),
	            ),
	        ));
	        $subform_row->addElement('multiCheckbox', 'patient_sapvverordnung_symptom', array(
	            'multiOptions' => PatientSavoirSapv::getDefaults('patient_sapvverordnung_symptom'),
	            'value'        => $saved_patient_sapvverordnung_symptom,
	            'required'     => false,
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                // 		        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            ),
	            'separator' => '<br/>' . PHP_EOL,
	             
	        ));
	        $subform_row->addElement('radio', 'patient_sapvverordnung_prescriber', array(
	            'multiOptions' => PatientSavoirSapv::getDefaults('patient_sapvverordnung_prescriber'),
	            'value'        => $saved_patient_sapvverordnung_prescriber,
	            'required'     => false,
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                // 		        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            ),
	            'separator' => '<br/>' . PHP_EOL,
	             
	        ));
	        //add hidden
	        $subform_row->addElement('hidden', 'patient_sapvverordnung_id', array(
	            'value' => $pat_sapv['id'],
	            'readonly' => true,
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'class' => 'hidden')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	            ),
	        ));
	        
	        
	        $subform->addSubForm($subform_row, $row_cnt);
	        $row_cnt++;
	    }
	
	    return $subform;
	}
	
	

    /**
     *
     * @param unknown $ipid
     * @param unknown $data
     * @throws Exception
     * @return NULL|Doctrine_Record
     */
    public function save_form_patient_savoir_sapv($ipid = null, array $data = array())
    {
        if (empty($ipid) || empty($data)) {
            return; //nothing to save
        }
         
//         dd($data);
        //formular will be saved first so we have a id
    
        $records =  new Doctrine_Collection('PatientSavoirSapv');
    
        $records->synchronizeWithArray($data);
    
        $records->save();
    
        return $records;
    }
}

