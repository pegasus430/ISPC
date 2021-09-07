<?php
/**
 * @author claudiu 
 * 26.06.2018
 *
 */
class Application_Form_PatientSuppliers extends Pms_Form
{

    
    public function getVersorgerExtract()
    {
        return  array(
            array("label"=>$this->translate('suppliers'), "cols"=>array("Suppliers"=>"supplier")),
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('phone1'), "cols"=>array("Suppliers" => "phone")),
            array("label"=>$this->translate('fax'), "cols"=>array("Suppliers" => "fax")),
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("nice_name")),
            array(array("Suppliers"=>"street1")),
            array(array("Suppliers"=>"zip"), array("Suppliers"=>"city")),
        );
    }
    
    
    
    
    
    /**
     * @cla on 26.06.2018
     *
     * @param array $options, optional values to populate the form
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_patient_suppliers($options =  array() , $elementsBelongTo = null)
    {
        $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
        
        $this->mapSaveFunction(__FUNCTION__, "save_form_patient_suppliers");
        
        
    
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend("suppliers");
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
    
//         	    if (!empty($options)) dd($options);
    
        if ( ! isset($options['Suppliers'])) {
            $options['Suppliers'] = $options;
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

        //TODO: add column self_id in Pflegedienstes table.. so you know what-id you selected from the dropdown
        $subform->addElement('hidden', 'self_id', array(
            'value'        => $options['supplier_id'] ? $options['supplier_id'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
         
        $subform->addElement('hidden', 'supplier_id', array(
            'value'        => $options['supplier_id'] ? $options['supplier_id'] : -1 ,
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
        $subform->addElement('text', 'supplier', array(
            'value'        => $options['Suppliers']['supplier'] ,
            'label'        => 'suppliers',
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
            'data-livesearch'  => 'Suppliers',
        ));
        
        $subform->addElement('text', 'type', array(
            'value'        => $options['Suppliers']['type'] ,
            'label'        => 'supplier_type',
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
		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['Suppliers']['first_name'] ,
            'label'        => 'firstname',
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
            'value'        => $options['Suppliers']['last_name'] ,
            'label'        => 'lastname',
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
        $subform->addElement('text', 'salutation', array(
            'value'        => $options['Suppliers']['salutation'] ,
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
            'value'        => $options['Suppliers']['street1'] ,
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
            'value'        => $options['Suppliers']['zip'] ,
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
            'value'        => $options['Suppliers']['city'],
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
            'value'        => $options['Suppliers']['phone'],
            'label'        => 'phone1',
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
            'value'        => $options['Suppliers']['fax'],
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
            'value'        => $options['Suppliers']['email'],
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

         
        $subform->addElement('textarea', 'supplier_comment', array(
            'value'        => ! empty($options['supplier_comment']) ?  $options['supplier_comment'] : $options['Suppliers']['comments'],
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
    public function save_form_patient_suppliers($ipid =  '', $data = array(), $indrop = 1)
    {
        $patientModel   = 'PatientSuppliers';
        $relationModel  = 'Suppliers';
        
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
        }
        
        //IPSC-2614
        $pc_listener->setOption('disabled', false);
        //--
        
        //ISPC-2614 Ancuta :: Hack to re-trigger listner -
        $entity->supplier_comment = $data['supplier_comment'].' ';
        $entity->save();
        //-- 
    
        return $entity;
    
    }
    
    
    
    
    
}


?>