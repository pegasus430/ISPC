<?php
/**
 * 
 * @author claudiu 
 * Jun 27, 2018
 *
 */
class Application_Form_PatientHospiceassociation extends Pms_Form
{

    public function getVersorgerExtract()
    {
        //Hospizverein
        //Versorgungs-Start
        //Versorgungs-Ende

        return  array(
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('hospice_service'), "cols"=>array("Hospiceassociation" => "hospice_association")),
            array("label"=>$this->translate('practice_phone'), "cols"=>array("Hospiceassociation" => "phone_practice")),
            array("label"=>$this->translate('emergency_phone'), "cols"=>array("Hospiceassociation" => "phone_emergency")),
            array("label"=>$this->translate('fax'), "cols"=>array("Hospiceassociation" => "fax")),
        );
    }

    public function getVersorgerAddress()
    {
        return array(
            array(array("Hospiceassociation"=>"hospice_association")),
            array(array("nice_name")),
            array(array("Hospiceassociation"=>"street1")),
            array(array("Hospiceassociation"=>"zip"), array("Hospiceassociation"=>"city")),
        );
    }


    /**
     * @cla on 27.06.2018
     *
     * @param array $options, optional values to populate the form
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_patient_hospiceassociation($options =  array() , $elementsBelongTo = null)
    {
        $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
        
        $this->mapSaveFunction(__FUNCTION__, "save_form_patient_hospiceassociation");
        
        
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend("hospice_service");
        $subform->setAttrib("class", "label_same_size");
        
        
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }

//         	            	    	            	    if (!empty($options)) dd($options);

        if ( ! isset($options['Hospiceassociation'])) {
            $options['Hospiceassociation'] = $options;
        }




        /* start with the hidden fields */
        $subform->addElement('hidden', 'id', array(
            'value'        => $options['id'] ? $options['id'] : 0 ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            //'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                // 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),

        ),
        ));

            //TODO: add column self_id in Voluntaryworkers table.. so you know what-id you selected from the dropdown
            //this is id_master ??
            $subform->addElement('hidden', 'self_id', array(
                'value'        => $options['h_association_id'] ? $options['h_association_id'] : null ,
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array('ViewHelper'),
            ));
             
            $subform->addElement('hidden', 'h_association_id', array(
                'value'        => $options['h_association_id'] ? $options['h_association_id'] : -1 ,
                'required'     => false,
                'filters'      => array('StringTrim'),
                //'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    // 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
            ),
            ));



            /* visible inputs */
            $subform->addElement('text', 'hospice_association', array(
                'value'        => $options['Hospiceassociation']['hospice_association'] ,
                'label'        => 'hospice_association',
                'required'     => true,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'data-livesearch'  => 'Hospiceassociation',
            ));

		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['Hospiceassociation']['first_name'] ,
            'label'        => 'first_name',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));


        $subform->addElement('text', 'last_name', array(
                'value'        => $options['Hospiceassociation']['last_name'] ,
                'label'        => 'last_name',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'data-livesearch'  => 'Hospiceassociation',
            ));
            
            $subform->addElement('text', 'salutation', array(
                'value'        => $options['Hospiceassociation']['salutation'] ,
                'label'        => 'salutation',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            
            
            
            
            $subform->addElement('text', 'street1', array(
                'value'        => $options['Hospiceassociation']['street1'] ,
                'label'        => 'address',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            $subform->addElement('text', 'zip', array(
                'value'        => $options['Hospiceassociation']['zip'] ,
                'label'        => 'zip',
                'data-livesearch'  => 'zip',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),doctornumber
            ));
            $subform->addElement('text', 'city', array(
                'value'        => $options['Hospiceassociation']['city'],
                'label'        => 'city',
                'data-livesearch'   => 'city',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            $subform->addElement('text', 'phone_practice', array(
                'value'        => $options['Hospiceassociation']['phone_practice'],
                'label'        => 'practice_phone',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            $subform->addElement('text', 'phone_emergency', array(
                'value'        => $options['Hospiceassociation']['phone_emergency'],
                'label'        => 'emergency_phone',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            $subform->addElement('text', 'fax', array(
                'value'        => $options['Hospiceassociation']['fax'],
                'label'        => 'fax',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));

            $subform->addElement('text', 'email', array(
                'value'        => $options['Hospiceassociation']['email'],
                'label'        => 'email',
                'required'   => false,
                'filters'    => array('StringTrim'),
    	        'validators' => array('NotEmpty', 'EmailAddress'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));

             
            $subform->addElement('textarea', 'h_association_comment', array(
                'value'        => ! empty($options['h_association_comment']) ? $options['h_association_comment'] : $options['Hospiceassociation']['comments'],
                'label'        => 'comments',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'rows'         => 3,
                //'cols'         => 60,
            ));



            return $subform;

    }
    
    /**
     *
     * @param string $ipid
     * @param array $data
     * @param number $indrop 0 = in the liveSearch, 1 = not
     * @return void|Doctrine_Record
     */
    public function save_form_patient_hospiceassociation($ipid =  '', $data = array(), $indrop = 1)
    {
        $patientModel   = 'PatientHospiceassociation';
        $relationModel  = 'Hospiceassociation';
    
        $ipid = ! empty($ipid) ? $ipid : $this->_ipid ;
    
        if (empty($ipid) || empty($data)) {
            return;//fail-safe
        }
    
        $entity = new $patientModel();
        //IPSC-2614
        $pc_listener = $entity->getListener()->get('IntenseConnectionListener');
        $pc_listener->setOption('disabled', true);
        //--
        $entity = $entity->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
    
        if ( ! $entity) {
            return; //fail-safe
        }
    
        $localField = null;
        $foreignField = null;
    
        if ($relation = $entity->getTable()->getRelation($relationModel, false)) {
            $relation = $relation->toArray();
            $localField = $relation['local'];
            $foreignField = $relation['foreign'];
        }
    
    
        if ( ! is_null($localField) && ! is_null($foreignField) && $data[$localField] != $entity->{$localField}) {
            $data[$localField] = $entity->{$localField};
        }
    
        $data['indrop'] = $indrop;
    
    
        $relationEntity = new $relationModel();
        $relationEntity = $relationEntity->findOrCreateOneBy(['id', 'clientid'], [$data[$localField], $this->logininfo->clientid], $data);
    
        if ($relationEntity && ! is_null($localField) && $entity->{$localField} != $relationEntity->{$foreignField}) {
            //it was a new one
            $entity->{$localField} = $relationEntity->{$foreignField};
            $entity->save();
    
            //             if (empty($data['self_id']) ) {
            //                 $this->_manual_voluntaryworker_message_send($relationEntity);
            //             }
        }
    
        //IPSC-2614 Ancuta 20.07.2020:: reactivate trigger
        $pc_listener->setOption('disabled', false);
        //--
        
        //ISPC-2614 Ancuta :: Hack to re-trigger listner -
        $entity->h_association_comment = $data['h_association_comment'].' ';
        $entity->save();
        //--
        
        
        return $entity;
    
    }
}
?>