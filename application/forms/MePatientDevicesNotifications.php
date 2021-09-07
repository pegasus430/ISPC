<?php
/**
 *
 * @author Ancuta
 * ISPC-2432
 * 22.01.2020
 *
 */
class Application_Form_MePatientDevicesNotifications extends Pms_Form
{
    
    protected $_model = 'MePatientDevicesNotifications';
    
    private $triggerformid = 0; //use 0 if you want not to trigger
    
    private $triggerformname = "frmMePatientDevicesNotifications";  //define the name if you want to piggyback some triggers
    
    protected $_translate_lang_array = 'MePatientDevicesNotifications_box_lang';
    
    public function isValid($data)
    {
        return parent::isValid($data);
    }
    
    public function getVersorgerExtract() {
        return array(
            
            array( "label" => $this->translate('Push notification status'), "cols" => array("status_name")),
            array( "label" => $this->translate('mePateint_interval_start_date'), "cols" => array("start_date")),
            array( "label" => $this->translate('mePatient_send_interval'), "cols" => array("send_interval_name")),
//             array( "label" => $this->translate('sen _notification_now'), "cols" => array("send_now_button")),
        );
    }
    
    
    public function create_form_MePatientDevicesNotifications( $options = array(), $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        // 	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
        
        $this->mapSaveFunction(__FUNCTION__ , "save_form_MePatientDevicesNotifications");
        
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'div' , 'class' => 'acp_accordion accordion_c'));
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('Me_pat_not_box_title'));
        $subform->setAttrib("class", "label_same_size inlineEdit {$__fnName}");
        $this->__setElementsBelongTo($subform, $elementsBelongTo);

        if ( ! isset($options['devices_arr'][0])) {
            $options['devices_arr'] = is_array($options['devices_arr']) ? $options['devices_arr'] : [];
        }
        
        $subform->addElement('hidden', 'id', array(
            'value'        => $options['id'] ? $options['id'] : 0 ,
            'required'     => true,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
                
            ),
        ));
        $subform->addElement('note',  'activate_push_notifications', array(
            'label'        => null,
            'required'     => false,
            'value'        => $this->translate('mePatient_activate_push_notifications'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'entrydetail')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => ' dontPrint', 'style' => '')),
            ),
        ));
        
        $subform->addElement('radio',  'status', array(
            'value'        => isset($options['status']) && ! empty($options['status']) ? $options['status'] : "disabled",
            'label'        => $this->translate('Status'),
            'required'     => false,
            'multiOptions' => array('disabled' => 'Deaktiviert', 'enabled' => 'Aktiviert'),
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                )),
            ),
            'onChange' => "if(this.value == 'enabled') {\$('.show_hide', \$(this).parents('table')).show(); if($('.push_interval_selector').val() == 'custom'){\$('.selector_send_interval_options', \$(this).parents('table')).show()}} else {\$('.show_hide', \$(this).parents('table')).hide()}; if(this.value == 'disabled') {\$('.selector_send_interval_options', \$(this).parents('table')).hide()}",            
        ));
        $display = $options['status'] == 'enabled' ? '' : 'display:none;';
        
 
        if ( ! empty($options['devices_arr']) && count($options['devices_arr']) >= 1) {
            
//             $subform->addElement('select', 'notification_type', array(
//                 'value'        => $options['notification_type'],
//                 'label'        => $this->translate('mePatient_notification_type'),
//                 'multiOptions' => array( '' => '' , 'scheduled' => 'scheduled_notification', 'send_now' => 'send_now_notification'),
//                 'required'   => false,
//                 'filters'    => array('StringTrim'),
//                 'validators' => array('NotEmpty'),
//                 'decorators' => array(
//                     'ViewHelper',
//                     array('Errors'),
//                     array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
//                     array('Label', array('tag' => 'td')),
//                     array(array('row' => 'HtmlTag'), array('tag' => 'tr' ,'class' => 'show_hide',  'style' => $display)),
//                 ),
//                 'onChange' => "if(this.value == 'scheduled') {\$('.meP_scheduled', \$(this).parents('table')).show()  } else {\$('.meP_scheduled', \$(this).parents('table')).hide()}  if(this.value == 'send_now') {\$('.meP_send_now', \$(this).parents('table')).show()  } else {\$('.meP_send_now', \$(this).parents('table')).hide()}",
//             ));
            
            
//             $display_scheduled = $options['notification_type'] == 'scheduled' ? '' : 'display:none' ;
            $display_scheduled = $display;
//             $display_send_now = $options['notification_type'] == 'send_now' ? '' : 'display:none' ;
            $display_send_now = $display;
            
            
            //#############
            //SCHEDULED    
            //#############
            $subform->addElement('note',  'delayed', array(
                'label'        => null,
                'required'     => false,
                'value'        => $this->translate('mePatient_schedule_delayed_notification'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'entrydetail','style' => 'padding-top:30px;padding-bottom:10px')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => '    show_hide', 'style' => $display_scheduled)),
                ),
            ));
            $subform->addElement('textarea',  'notification_text', array(
                'value'        => !empty($options['notification_text']) ? $options['notification_text'] : '',
                'label'        => $this->translate('push_comment'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'class'        => 'notification_push_comment',
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','style'=>'vertical-align:middle')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => '  show_hide', 'style' => $display_scheduled)),
                ),
            ));
            
            $subform->addElement('text',  'start_date', array(
                'value'        => $options['start_date'] && $options['start_date']!="0000-00-00" ? date('d.m.Y', strtotime($options['start_date'])) : date('d.m.Y'),
                'label'        => $this->translate('mePateint_interval_start_date'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'class'        => 'date allow_future disable_past',
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => '  show_hide', 'style' => $display_scheduled)),
                ),
            ));
            
            
            
            $subform->addElement('select', 'send_interval', array(
                'value'        => $options['send_interval'],
                'multiOptions' => array( 'daily' => 'scheduled_daily' , 'weekly' => 'scheduled_weekly', 'custom' => 'scheduled_custom'),
                'label'        => $this->translate('mePatient_send_interval'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'show_hide',  'style'=>$display_scheduled)),
                ),
               'onChange' => "if(this.value == 'custom') {\$('.selector_send_interval_options', \$(this).parents('table')).show();  $('.push_date_nr').val('') } else {\$('.selector_send_interval_options', \$(this).parents('table')).hide()}",
                'class' =>'push_interval_selector'
            ));
            
            
            $display_options = $options['send_interval'] != 'custom'  ? "display:none": "";
            
            $subform->addElement('text', 'send_interval_options', array(
                'value'        => $options['send_interval_options'],
                'required'     => false,
                'label'         => null,
                'filters'      => array('StringTrim', 'Digits'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'selector_send_interval_options  meP_scheduled',  'style'=>$display_scheduled.$display_options)),
                    
                    ),
                    
                    'data-inputmask'   => "'alias':'numeric', 'suffix':' ml' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
                    'pattern'          => "^[0-9,]*( ml)$",
                    'class'            => 'push_date_nr'
                    ));
            
                              
            //#############
            //send_now
            //#############
             /* 
            // Comented on 12.03.2020 - button was moved outside the scheduled
            
            $subform->addElement('note',  'send_notification_now', array(
                'label'        => null,
                'required'     => false,
                'value'        => $this->translate('sen _notification_now'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'entrydetail','style' => 'padding-top:30px;padding-bottom:10px')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => $display_send_now)),
                ),
            ));
            
            $subform->addElement('button', 'send_push_button', array(
                'type'         => 'button',
                'value'        => 'save',
                'label'        => $this->translator->translate('send_push_notification'),
                'onclick'      => 'send_push_now(this)',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => ' ')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => $display_send_now)),
                ),
            ));  
            
            */ 
                                    
                                    
        }
        else
        {
            $subform->addElement('note',  'no_devices', array(
                'label'        => null,
                'required'     => false,
                'value'        => $this->translate('mePatient_no_devices'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => '')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => $display)),
                ),
            ));
        }
        
        
        // Send now buttons
      /*   if ( ! empty($options['devices_arr']) && count($options['devices_arr']) >= 1) {
            
            
            $subform->addElement('note',  'send_notification_now', array(
                'label'        => null,
                'required'     => false,
                'value'        => $this->translate('sen _notification_now'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'entrydetail')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => '')),
                ),
            ));
            
            $subform->addElement('button', 'send_push_button', array(
                'type'         => 'button',
                'value'        => 'save',
                'label'        => $this->translator->translate('send_push_notification'),
                'onclick'      => 'send_push_now(this)',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => ' ')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => '')),
                ),
            ));
            
        } */
        
        
        
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
    public function save_form_MePatientDevicesNotifications($ipid =  null , $data = array())
    {
        if (empty($ipid) || ! is_array($data)) {
            return;
        }
        if($data['start_date'] != "")
        {
            $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
        }
        else
        {
            $data['start_date'] = '0000-00-00';
        }
        
        $data['notification_type'] = 'scheduled';// manually set scheduled 03.02.2020 
                
        // Set days interval for the other 2 cases ( daily and weekly)
        if($data['send_interval'] != "custom") 
        {
            if($data['send_interval'] == 'daily')
            {
                $data['send_interval_options'] = '1';
            } 
            elseif($data['send_interval'] == 'weekly')
            {
                $data['send_interval_options'] = '7';
            }
        }
        
        if($data['status'] == 'disabled'){
            $data['start_date'] = '0000-00-00';
            $data['send_interval'] = null;
            $data['send_interval_options'] = '';
            $data['notification_text'] = '';
        }
        
        
        $entity = MePatientDevicesNotificationsTable::getInstance()->findOrCreateOneBy(['ipid'], [$ipid], $data);
        $this->_save_box_History($ipid, $entity, 'status', 'grow91', 'text');
        $this->_save_box_History($ipid, $entity, 'start_date', 'grow91', 'text');
        $this->_save_box_History($ipid, $entity, 'interval_days', 'grow91', 'text');
        
        
        // SEND SURVEY
        $send_on_save = "0";
        if($send_on_save == "1"){
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            
            $allow_surveys = Modules::checkModulePrivileges("197", $clientid);
            if($allow_surveys){
                
//                 if (Zend_Registry::isRegistered('mypain')) {
                    
//                     $mypain_cfg = Zend_Registry::get('mypain');
//                     $ipos_survey_id = $mypain_cfg['ipos']['chain'];
                    
//                     $form = new Application_Form_ClientSurveySettings(); //why in foreach? i forgot
//                     $result_survey = $form->create_pateint_survey_email($clientid,$ipos_survey_id,$ipid);
//                 }
            }
        }
        
        
        return $entity;
        
    }
    
    private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $division_tab )
    {
        
        $newModifiedValues = $newEntity->getLastModified();
        
        if (isset($newModifiedValues[$fieldname])) {
            
            $new_values = $newModifiedValues[$fieldname];
            
            if($fieldname == 'receiver')
            {
                if(substr($data['receiver'], 0, 1) == 'p')
                {
                    $new_values = $this->_patientMasterData['last_name'] . ' ' . $this->_patientMasterData['first_name'] . ' - ' . $this->_patientMasterData['email'];
                }
                else
                {
                    $new_values = $this->_patientMasterData['ContactPersonMaster'][substr($data['receiver'], 1)]['cnt_last_name'] . ' ' . $this->_patientMasterData['ContactPersonMaster'][substr($data['receiver'], 1)]['cnt_first_name'] . ' - ' . $this->_patientMasterData['ContactPersonMaster'][substr($data['receiver'], 1)]['cnt_email'];
                }
            }
            
            $history = [
                'ipid' => $ipid,
                'clientid' => $this->logininfo->clientid,
                'formid' => $formid,
                'fieldname' => $fieldname,
                'fieldvalue' => $new_values,
            ];
            
            $newH = new BoxHistory();
            $newH->fromArray($history);
            $newH->save();
            
        }
        
    }
    
}

