<?php
class Application_Form_PatientPort extends Pms_Form
{
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientPort';
    
    
    /**
     * @claudiu 2017.12.08
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_port ($values =  array() , $elementsBelongTo = null)
    {
       
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend($this->translate('Port:'));
        $subform->setAttrib("class", "label_same_size_100");
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
//         PatientPort::getDefaultPort();
        
	    $subform->addElement('radio', 'port', array(
	        'label'      => null,
	        'separator'  => '&nbsp;',
	        'required'   => false,
	        'multiOptions'=> array('no' => 'Nein', 'yes' => 'Ja'),
		    'value' => $values['port'],
	        'decorators' =>   array(
                'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly'=>true)),
	        ),
	        'onchange' => 'if (this.value == "yes") {$(".Port_yes_options", $(this).parents(\'table\')).show();} else {$(".Port_yes_options", $(this).parents(\'table\')).hide().find(\'input\').each(function() {$(this).prop("checked", false);});}',
	    ));
	    
	       
	    $display = ! in_array('yes', $values['port']) ? 'display:none' : null;

	    if ($values['port'] === "yes") {
	        $display = null;
	        $values['port'] = $values['yes'];
	    }
	    
	    
	    $subform->addElement('radio', 'yes', array(
	        'label'      => null,
	        'separator'  => '&nbsp;',
	        'required'   => false,
	        'multiOptions'=> array('right' => 'rechts', 'left' => 'links'),
	        'value' => $values['port'],
	        'decorators' =>   array(
                'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)),
	        ),
	        'labelClass' => 'Port_yes_options',
	        'labelStyle' => $display
	        
	    ));
    
    
        return $subform;
    }


    
    
    public function save_form_port($ipid =  '' , $data = array())
    {

        //radio + cb
        if(empty($ipid) || empty($data)) {
            return;
        }

        
        $result = array();
        $entity = new PatientPort();
         
        if( ! is_array($data['port'])) {
            $data['port'] = array($data['port']);
        }
        if( ! is_array($data['yes'])) {
            $data['yes'] = array($data['yes']);
        }
        
        $data['port'] = array_merge($data['port'], $data['yes']);
      
        
        //delete all previous
        $q =  $entity->getTable()->createQuery()
        ->delete()
        ->where('ipid = ?', $ipid)
        ->andWhere('isdelete = 0')
        ->andWhereNotIn('port' , $data['port'])
        ->execute();

        //insert/update one by one
        foreach ($data['port'] as $port) {
            $row =  $data;
            $row['port'] = $port;
            $result[] = $entity->findOrCreateOneByIpidAndPort($ipid, $port, $row);
        }
        
        return $result;
    
    }
}
?>