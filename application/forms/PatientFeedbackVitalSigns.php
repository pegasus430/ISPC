<?php
/**
 *
* @author claudiu✍
* Dec 10, 2018
*
*/
class Application_Form_PatientFeedbackVitalSigns extends Pms_Form {


    protected $_model = 'PatientFeedbackVitalSigns';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = PatientFeedbackVitalSigns::TRIGGER_FORMID;
    private $triggerformname = PatientFeedbackVitalSigns::TRIGGER_FORMNAME;

//     //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = PatientFeedbackVitalSigns::LANGUAGE_ARRAY;

    protected $_block_name_allowed_inputs =  array(
        "MamboAssessment" => [
            'create_form_nutrition' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    );

    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_nutrition' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
//                 "further_assessment",
//                 "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
//                 "inclusion_measures",
            ],
        ],
    ];





    //Stehen Sie in regelmäßiger hausärztlicher Behandlung?
    //Are you undergoing regular family medical treatment?
    public function create_form_nutrition ($values =  array() , $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
         
        $this->mapValidateFunction($__fnName , "create_form_isValid");

        $this->mapSaveFunction($__fnName , "save_form_patient_VitalSigns");
         

        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('VitalSigns'));
        $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
         
        $this->__setElementsBelongTo($subform, $elementsBelongTo);


        $subform->addElement('hidden', 'id', array(
            'label'        => null,
            'value'        => ! empty($values['id']) ? $values['id'] : '',
            'required'     => false,
            'readonly'     => true,
            'filters'      => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
            ),
        ));
         
        if ( ! empty($values['source']))
        {
            $subform->addElement('hidden', 'source', array(
                'label'        => null,
                'value'        => $values['source'],
                'required'     => false,
                'readonly'     => true,
                'filters'      => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
                ),
            ));
             
        }




        $subform->addElement('text', 'weight', array(
            'value'    => ! empty($values['weight']) ? number_format($values['weight'], 2, ',', '') : null,
            'label'      => $this->translate("weight"),
            'placeholder'   => 'kg',
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag'      => 'td',
                    'class'    => 'element',
                    'colspan'  => 3,
                )),
                array('Label', array(
                    'tag' => 'td',
                    'tagClass'=>'print_column_first',
                    // 	                'placement'=> 'IMPLICIT_APPEND'
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                )),
            ),
            'data-mask' => "999,99",
            'class' => 'selector_weight',
            'onFocusout' => "if (Number(str2number_format(this.value)) > 0 && Number(str2number_format($(this).parents('table').find('.selector_height').val()))) {\$(this).parents('table').find('.selector_bmi').val(number_format ( (Number(str2number_format(this.value)) / Math.pow(Number(str2number_format($(this).parents('table').find('.selector_height').val())), 2)) *10000, 2, ',', ''));}",
            
        ));
        
        
        $subform->addElement('text', 'height', array(
            'value'    => ! empty($values['height']) ? number_format($values['height'], 2, ',', '') : null,
            'label'      => $this->translate("height"),
            'placeholder'   => 'cm',
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag'      => 'td',
                    'class'    => 'element',
                    'colspan'  => 3,
                )),
                array('Label', array(
                    'tag' => 'td',
                    'tagClass'=>'print_column_first',
                    // 	                'placement'=> 'IMPLICIT_APPEND'
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                )),
            ),
            'data-mask' => "999.99",
            'class' => 'selector_height',
            'onFocusout' => "if (Number(str2number_format(this.value)) > 0 && Number(str2number_format($(this).parents('table').find('.selector_weight').val()))) {\$(this).parents('table').find('.selector_bmi').val(number_format ( (Number(str2number_format($(this).parents('table').find('.selector_weight').val())) / Math.pow(Number(str2number_format(this.value)), 2)) *10000, 2, ',', ''));}",
            
        ));
        $subform->addElement('text', 'bmi', array(
            'value'    => ! empty($values['__bmi']) ? number_format($values['__bmi'], 2, ',', '') : null,
            'label'      => $this->translate("bmi"),
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag'      => 'td',
                    'class'    => 'element',
                    'colspan'  => 3,
                )),
                array('Label', array(
                    'tag' => 'td',
                    'tagClass'=>'print_column_first',
                    // 	                'placement'=> 'IMPLICIT_APPEND'
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                )),
            ),
            "readonly" => true,
            'class' => 'selector_bmi',
            
        ));

        //         Drinking amount per day
        $subform->addElement('select', 'drinking_daily', array(

            'value'    => ! empty($values['drinking_daily']) ? $values['drinking_daily'] : null,

            'multiOptions' => $this->getColumnMapping('drinking_daily'),

            'label'      => $this->translate("Drinking amount per day"),
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                )),
            ),

        ));





         
         
        $subform->addElement('radio', 'weight_loss', array(

            'value'    => ! empty($values['weight_loss']) ? $values['weight_loss'] : null,

            'multiOptions' => $this->getColumnMapping('weight_loss'),

            'label'      => $this->translate('Weight change'),
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>1)),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                    'openOnly' => true,
                )),
            ),
            'onChange' => "if (this.value=='weight_loss' || this.value=='weight_gain') {\$(this).parents('tr').find('.weight_loss_freetext').show();} else {\$(this).parents('tr').find('.weight_loss_freetext').hide();}",

        ));
         
         
        $display = $values['weight_loss'] == 'weight_loss' || $values['weight_loss'] == 'weight_gain' ? '' : "display:none";
         
        $subform->addElement('text', 'weight_loss_freetext', array(
             
            'value'    => ! empty($values['weight_loss_freetext']) ? $values['weight_loss_freetext'] : null,
            'placeholder'      => $this->translate("wieviel in den letzten 8-12 Wochen"),
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag'      => 'td',
                    'class'    => 'element weight_loss_freetext',
                    'colspan'  => 2,
                    "style"    => $display,
                    // 	                "closeOnly" => true,
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                    'closeOnly' => true,
                )),
            ),
            'style'=>'width:100%',
        ));
         
         



/*

        $subform->addElement('radio', 'weight_gain', array(

            'value'    => ! empty($values['weight_gain']) ? $values['weight_gain'] : null,

            'multiOptions' => $this->getColumnMapping('weight_gain'),

            'label'      => $this->translate('Weight gain'),
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>1)),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                    'openOnly' => true,
                )),
            ),
            'onChange' => "if (this.value=='no') {\$(this).parents('tr').find('.weight_gain_freetext').hide();} else {\$(this).parents('tr').find('.weight_gain_freetext').show();}",

        ));
         
         
        $display = $values['weight_gain'] == 'yes' ? '' : "display:none";
         
        $subform->addElement('text', 'weight_gain_freetext', array(
             
            'value'    => ! empty($values['weight_gain_freetext']) ? $values['weight_gain_freetext'] : null,
            'placeholder'      => $this->translate("wieviel in den letzten 8-12 Wochen"),
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag'      => 'td',
                    'class'    => 'element print_column_data weight_gain_freetext',
                    'colspan'  => 2,
                    "style"    => $display,
                    // 	                "closeOnly" => true,
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                    'closeOnly' => true,
                )),
            ),
        ));
         
         
*/
         
         
        return $this->filter_by_block_name($subform, $__fnName);
    }







    public function save_form_patient_VitalSigns ($ipid =  null , $data =  array())
    {
        if (empty($ipid) || empty($data)) {
            return;
        }

        
        if ( ! empty($data['__formular']['formular_date'])) {
            //fail-safe, FormBlockVitalSignsTable must be on a date
            $entityFormBlockVitalSigns =  FormBlockVitalSignsTable::getInstance()->findOrCreateOneBy(['ipid', 'source', 'signs_date'], [$ipid, 'mambo_assessment', date('Y-m-d H:i:s', strtotime($data['__formular']['formular_date']))], $data);
        }
         
         
        $entity = Doctrine_Core::getTable('PatientFeedbackVitalSigns')->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);

        return $entity;
    }



    public function getColumnMapping($fieldName, $revers = false)
    {

        //             $fieldName => [ value => translation]
        $overwriteMapping = [
            'weight_loss' => [//added like this cause translations
//                 'weight_loss' => '',
//                 'weight_gain' => '',
//                 'no' => "Nein",
//                 'yes' => "Ja",
            ],
            'weight_gain' => [//added like this cause translations
                'no' => "Nein",
                'yes' => "Ja",
            ],

             
            'drinking_daily' => [
                ''  => '---', //extra empty value for select
            ],
             
        ];


        $values = PatientFeedbackVitalSignsTable::getInstance()->getEnumValues($fieldName);

        $values = array_combine($values, array_map("self::translate", $values));

        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }

        return $values;

    }
}