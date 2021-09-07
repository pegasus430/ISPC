<?php
/**
 * 
 * @author claudiu 
 * Jun 27, 2018
 *
 */
class Application_Form_PatientVoluntaryworkers extends Pms_Form
{
    
    public function getVersorgerExtract()
    {
        //Hospizverein
        //Versorgungs-Start
        //Versorgungs-Ende
        
        return  array(
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('hospice_association'), "cols"=>array("Voluntaryworkers" => "hospice_association")),
            array("label"=>$this->translate('practice_phone'), "cols"=>array("Voluntaryworkers" => "phone")),
            array("label"=>$this->translate('vw_start_date'), "cols"=>array("Localized_start_date")),
            array("label"=>$this->translate('vw_end_date'), "cols"=>array("Localized_end_date")),
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("nice_name")),
            array(array("Voluntaryworkers"=>"street")),
            array(array("Voluntaryworkers"=>"zip"), array("Voluntaryworkers"=>"city")),
        );
    }
    
    
    /**
     * @cla on 27.06.2018 
     *
     * @param array $options, optional values to populate the form
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_patient_voluntaryworker($options =  array() , $elementsBelongTo = null)
    {
        $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
    
        $this->mapSaveFunction(__FUNCTION__, "save_form_patient_voluntaryworker");
        
        
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend("voluntaryworkers");
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
    
//         	    	            	    if (!empty($options)) dd($options);
    
        if ( ! isset($options['Voluntaryworkers'])) {
            $options['Voluntaryworkers'] = $options;
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
//         $subform->addElement('hidden', 'self_id', array(
//             'value'        => $options['vwid'] ? $options['vwid'] : null ,
//             'required'     => false,
//             'filters'      => array('StringTrim'),
//             'decorators'   => array('ViewHelper'),
//         ));
         
        $subform->addElement('hidden', 'hospice_association', array(
            'value'        => $options['Voluntaryworkers']['hospice_association'] ? $options['Voluntaryworkers']['hospice_association'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
        $subform->addElement('hidden', 'status', array(
            'value'        => $options['Voluntaryworkers']['status'] ? $options['Voluntaryworkers']['status'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
        $subform->addElement('hidden', 'parent_id', array(
            'value'        => $options['Voluntaryworkers']['parent_id'] ? $options['Voluntaryworkers']['parent_id'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
        $subform->addElement('hidden', 'indrop', array(
            'value'        => isset($options['Voluntaryworkers']['indrop']) ? $options['Voluntaryworkers']['indrop'] : 1 ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));

        
        
        
        $subform->addElement('hidden', 'vwid', array(
            'value'        => $options['vwid'] ? $options['vwid'] : -1 ,
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
    

		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        /* visible inputs */
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['Voluntaryworkers']['first_name'] ,
            'label'        => 'firstname',
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
        ));
        $subform->addElement('text', 'last_name', array(
            'value'        => $options['Voluntaryworkers']['last_name'] ,
            'label'        => 'lastname',
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
            'data-livesearch'  => 'Voluntaryworkers',
        ));
        $subform->addElement('text', 'salutation', array(
            'value'        => $options['Voluntaryworkers']['salutation'] ,
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
        $subform->addElement('text', 'street', array(
            'value'        => $options['Voluntaryworkers']['street'] ,
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
            'value'        => $options['Voluntaryworkers']['zip'] ,
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
            'value'        => $options['Voluntaryworkers']['city'],
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
        $subform->addElement('text', 'phone', array(
            'value'        => $options['Voluntaryworkers']['phone'],
            'label'        => 'phone',
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
        $subform->addElement('text', 'mobile', array(
            'value'        => $options['Voluntaryworkers']['mobile'],
            'label'        => 'mobile',
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
            'value'        => $options['Voluntaryworkers']['email'],
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

         
        $subform->addElement('textarea', 'vw_comment', array(
            'value'        => ! empty($options['vw_comment']) ?  $options['vw_comment'] : $options['Voluntaryworkers']['comments'],
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

        
        
        
        //dd($options);
        $subform->addElement('text', 'start_date', array(
            'value'        => empty($options['start_date']) || $options['start_date'] == "0000-00-00 00:00:00" ? "" : date('d.m.Y', strtotime($options['start_date'])),
            'label'        => 'vw_start_date',
            
            'required'   => true,
            
            'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
            
            //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
            
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly'=>true)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly'=>true)),
            ),
            'class' => 'date allow_future',
            'data-altfield' => 'start_date',
            'data-altformat' => 'yy-mm-dd',
        ));
        
        /*
        $hidden_date = $subform->createElement('hidden', 'start_date', array(
            'label'    => null,
            'value'    => empty($options['start_date'])  ? "" : date('Y.m.d', strtotime($options['start_date'])) ,
             
            'required'   => true,
            'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("date_format"=>'Y-m-d'))),
             
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'closeOnly'=>true)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)),
            ),
        ));
        $subform->addElement($hidden_date, 'hidden_start_date'); // this is the alternative format.. so not to use date in php for db
        */
        
            
            
            
        $subform->addElement('text', 'end_date', array(
            'value'        => empty($options['end_date']) || $options['end_date'] == "0000-00-00 00:00:00" ? "" : date('d.m.Y', strtotime($options['end_date'])) ,
            'label'        => 'vw_end_date',
            
            'required'   => false,
            
//             'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("date_format"=>'dd.MM.Y'))),
            
            'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'d.m.Y'))),
            
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly'=>true)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly'=>true)),
            ),
            'class' => 'date allow_future',
            'data-altfield' => 'end_date',
            'data-altformat' => 'yy-mm-dd',
        ));
        /*
        $hidden_date = $subform->createElement('hidden', 'end_date', array(
            'label'    => null,
            'value'    => empty($options['end_date']) ? "" : date('Y.m.d', strtotime($options['end_date'])) ,
             
            'required'   => true,
             
            'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("date_format"=>'Y-m-d'))),
             
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'closeOnly'=>true)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)),
            ),
        ));
        $subform->addElement($hidden_date, 'hidden_end_date'); // this is the alternative format.. so not to use date in php for db
        */
            
            
        return $subform;
    
    }
    
    
    public function getHospiceAssociationArray( $clientids =  array())
    {
        
        if (empty($clientids) || ! is_array($clientids)) {
            $clientids = array($this->logininfo->clientid);
        }
        
        $hospice_association_array = array();
        
        $hospice_association_q = Doctrine_Query::create()
        ->select("*")
        ->from('Hospiceassociation')
        ->whereIn('clientid', $clientids)
        ->andWhere("isdelete=0")
        ->fetchArray()
        ;
        
//         dd($hospice_association_q);
        
        foreach ($hospice_association_q as $row) {
            $hospice_association_array[$row['id']] = $row['hospice_association'];
        }
        
        return $hospice_association_array;
    }
    
    

    /*
    public function save_form_patient_voluntaryworker($ipid =  '' , $data = array())
    {
    
        if (empty($ipid) || empty($data)) {
            return;
        }
        $entity = new Voluntaryworkers();
        
        $data['clientid'] = $this->logininfo->clientid; //TODO : this is not ok, cause you can have vw from others
            
        $voluntaryworker = $entity->findOrCreateOneBy('id', $data['vwid'], $data);
        
        if ($data['vwid'] != $voluntaryworker->id) {
            //new one
            $data['vwid'] = $voluntaryworker->id;
    
            // 	        if (empty($data['self_id']) ) {
            // 	            $this->_manual_nurse_message_send($care_service, $ipid);
            // 	        }
        }
        
        if ( ! empty($data['start_date'])) {
            $date = new Zend_Date($data['start_date']);
            $data['start_date'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
        } else {
            $data['start_date'] = null;
        }
        if ( ! empty($data['end_date'])) {
            $date = new Zend_Date($data['end_date']);
            $data['end_date'] = $date->toString( Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
        } else {
            $data['end_date'] = null;
        }
        
        $entity = new PatientVoluntaryworkers();
        return $entity->findOrCreateOneByIpidAndId( $ipid, $data['id'], $data);
    
    }
    */
    
    
    
    
    
    
    
    
    
    /**
     *
     * @param string $ipid
     * @param array $data
     * @param number $indrop 0 = in the liveSearch, 1 = not
     * @return void|Doctrine_Record
     */
    public function save_form_patient_voluntaryworker($ipid =  '', $data = array(), $indrop = 1)
    {
        $patientModel   = 'PatientVoluntaryworkers';
        $relationModel  = 'Voluntaryworkers';
    
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
        
        //IPSC-2614
        $pc_listener->setOption('disabled', false);
        //--
        
        //ISPC-2614 Ancuta :: Hack to re-trigger listner -
        $entity->vw_comment = $data['vw_comment'].' ';
        $entity->save();
        //-- 
        
        
    
        return $entity;
    
    }
    
    
    
    
}