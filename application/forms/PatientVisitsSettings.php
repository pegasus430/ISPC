<?php
class Application_Form_PatientVisitsSettings extends Pms_Form
{
    //TODO : all the js box creation to be in here
    public function create_form_patient_visits_settings( $options = array(), $elementsBelongTo = null)
    {
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
    
        $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_visits_settings");
    
        $subform = $this->subFormTable(array(
            'columns' => [
                $this->translate('users'),
                $this->translate('short_Monday'),
                $this->translate('short_Tuesday'),
                $this->translate('short_Wednesday'),
                $this->translate('short_Thursday'),
                $this->translate('short_Friday'),
                $this->translate('short_Saturday'),
                $this->translate('short_Sunday'),
                '',
            ],
            // 'class' => 'datatable',
            'id' => 'table_visits_per_day'
        ));
        
        
         
        //$subform->addDecorator('HtmlTag', array('tag' => 'div' , 'class' => 'acp_accordion accordion_c'));
        $subform->setLegend('PatientVisitsSettings');
        $subform->setAttrib("class", "label_same_size inlineEdit " . __FUNCTION__);
         
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
        $subform->addElement('note',  'comments', array(
            'escape' => false,
            'value'        => '<a onclick="assign_tourenplan(0,0)" href="javascript:void(0);" id="assign_tourenplan" style="padding:5px; float:left;" class="dontPrint" >
								<img src="' . APP_BASE . 'images/btttt_plus.png" title="' . $this->translate('displayvisitssettings') . '" />
							</a>'
            ,
            'label'        => null,
            'required'     => false,
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'colspan' => 9)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'add_new_row')),
        
            ),
        ));
                
        $subform->addElement('text',  'visit_duration', array(
            'value'        => $options['visit_duration'] ? $options['visit_duration'] : 30,
            'label'        => 'visit_duration',
            'required'     => false,
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'colspan' => 9)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'id' => 'visit_duration_table' , 'style' => "display:none")),
        
            ),
        ));   
        
        
        return $this->filter_by_block_name($subform, __FUNCTION__);
    }
    
    
    
    public function save_form_patient_visits_settings($ipid =  null , $data = array())
    {
        if (empty($ipid) || ! is_array($data)) {
            return;
        }
        
        
        $entity = new PatientVisitsSettings();
        
        /*
         * $data['visit_duration']  is saved as the max id with visitor_id = 0
         */
        $data['visit_duration'] = isset($data['visit_duration']) ? (int)$data['visit_duration'] : null;
        
        if ( ! empty($data['visit_duration'] )) {
            
            $saved = $entity->getTable()->findByIpidAndVisitorId( $ipid, 0);
            
            
            
            if ($saved->count()) {
                foreach ($saved->getIterator() as $row) {
                    $row->visit_duration = $data['visit_duration'];
                }
                $saved->save();
                
            } else {
                
                //insert a new one
                $entity->visit_duration = $data['visit_duration']; 
                $entity->visitor_id = 0;
                $entity->ipid = $ipid;
                $entity->save();
                
                $result = $entity;
            }         
        }
    
        
        return $result;
        
    }
}