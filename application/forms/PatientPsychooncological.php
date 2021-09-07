<?php
class Application_Form_PatientPsychooncological extends Pms_Form
{
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientPsychooncological';
    
    
    
    /**
     * @claudiu 2017.12.08
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_psycho_oncological_support ($values =  array() , $elementsBelongTo = null)
    {
    
        $subform = $this->subFormTable();
        $subform->setLegend($this->translate('Psycho-oncological support:'));
        $subform->setAttrib("class", "label_same_size_auto");
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        
        $el = $this->createElement('radio', 'integrated', array(
            'value'         => $values['integrated'],
            'multiOptions'  => array('already_integrated' => 'schon eingebunden'),
            'required'      => false,
            'decorators'    => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true )),
            ),
// 	        'isArray' => true,
            'onChange' => 'if (this.value=="already_integrated") {$("input:radio.necessity_radio", $(this).parents(\'table\')).attr("checked", false);  $(".necessity_label", $(this).parents(\'table\')).hide(); $("input:text.comments", $(this).parents("table")).show();};'
    
	    ));
	    $subform->addElement($el, 'yes');
    	     
	    $display = $values['integrated'] == "already_integrated" ? '' : 'display:none';
	    $subform->addElement('text', 'comment', array(
            'value'        => $values['comment'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
            ),
            'style'         => $display,
            'class'         => 'comments',
            'size'          => 60,
        ));
    	     
    	     
        $el = $this->createElement('radio', 'integrated', array(
            'value'         => $values['integrated'],
            'multiOptions'  => array('not_yet_integrated'=>'Notwendigkeit'),
            'required'      => false,
            'decorators'    => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true )),
            ),
            // 	        'belongsTo' => 'hospizverein',
            // 	        'isArray' => true,
            'onChange' => 'if (this.value=="not_yet_integrated") {$("input:text.comments", $(this).parents("table")).val("").hide(); $(".necessity_label", $(this).parents(\'table\')).show();};'
        ));
        $subform->addElement($el, 'no');
    	     
    
	    $display = $values['integrated'] == "not_yet_integrated" ? '' : 'display:none';
	    $subform->addElement('radio', 'necessity', array(
	        'value'        => $values['necessity'],
	        'multiOptions' => array('yes'=> 'Ja' ,'no' => 'Nein'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
	        ),
	        'class'        => 'necessity_radio',
	        'labelClass'   => 'necessity_label',
	        'labelStyle'   => $display,
	        'separator'    => '&nbsp;',
	    ));

	    return $subform;
    }

    public function save_form_psycho_oncological_support($ipid = '', $data = array())
    {
        if (empty($ipid)) {
            return;
        }
    
        $entity  = new PatientPsychooncological();
        return $entity->findOrCreateOneBy('ipid', $ipid, array(
            'integrated'        => $data['integrated'],
            'necessity'         => $data['necessity'],
            'comment'           => $data['comment'],
            'wlassessment_id'   => $data['wlassessment_id'],
        ));
    }
}
?>