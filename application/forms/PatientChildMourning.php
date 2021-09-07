<?php
class Application_Form_PatientChildMourning extends Pms_Form
{
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientChildMourning';
    
    
    
    public function create_form_child_mourning($values =  array() , $elementsBelongTo = null)
    {

        $subform = $this->subFormTable();
        $subform->setLegend($this->translate('Child Mourning Work: (To be completed only in minors in the household)'));
        $subform->setAttrib("class", "label_same_size_auto");
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
//         $subform->addElement('note', 'note', array(
//             'label'      => null,
//             'required'   => false,
//             'value' => $this->translate('Question from the patient / relatives'),
//             'decorators' =>   array(
//                 'ViewHelper',
//                 array('Errors'),
//                 array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
//                 array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
//             ),
//         ));
        
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

        $display = $values['integrated'] == 'already_integrated' ? '' : 'display:none';
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
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true )),
            ),
            // 	        'belongsTo' => 'hospizverein',
            // 	        'isArray' => true,
            'onChange' => 'if (this.value=="not_yet_integrated") {$("input:text.comments", $(this).parents("table")).val("").hide(); $(".necessity_label", $(this).parents(\'table\')).show();};'
        ));
        $subform->addElement($el, 'no');


        $display = $values['integrated'] == 'not_yet_integrated' ? '' : 'display:none';
        $subform->addElement('radio', 'necessity', array(
            'value'        => $values['necessity'],
            'multiOptions' => array('yes'=> 'Ja' ,'no' => 'Nein'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
            ),
            'class'        => 'necessity_radio',
            'labelClass'   => 'necessity_label',
            'labelStyle'   => $display,
            'separator'    => '&nbsp;',
        ));

        return $subform;
    }    
    
    
    
    
    
    
    
    
    
    /**
     * 
     * @param string $ipid
     * @param unknown $data
     * @return void|Doctrine_Record
     */
    public function save_form_child_mourning($ipid = '', $data = array())
    {        
        if (empty($ipid)) {
            return;
        }

        $entity  = new PatientChildMourning();
        return $entity->findOrCreateOneBy('ipid', $ipid, array(
            'integrated'        => $data['integrated'],
            'necessity'         => $data['necessity'],
            'comment'           => $data['comment'],
            'wlassessment_id'   => $data['wlassessment_id'],
        ));
    }
    
    //TODO
//     private function _saveBoxHistory($data = array())
//     {
//         //save box history
//         $history = new BoxHistory();
//         $history->ipid = $ipid;
//         $history->clientid = $clientid;
//         $history->fieldname = $_GET['fldname'];
//         $history->fieldvalue = $_GET['chkval'];
//         $history->formid = $_GET['formid'] ;
//         $history->save();
        
//         $this->_helper->json->sendJson(array(
//             'msg'		=> "Success",
//             'formid'	=> $_GET['formid'],
        
//         ));
//     }
    
}
?>