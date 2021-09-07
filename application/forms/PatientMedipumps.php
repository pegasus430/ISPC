<?php
require_once("Pms/Form.php");
class Application_Form_PatientMedipumps extends Pms_Form
{
    
    public function getVersorgerExtract() {
        return array(
    
            array( "label" => $this->translate('medipumps'), "cols" => array("medipump")),
            array( "label" => $this->translate('start_time'), "cols" => array("start_date")),
            array( "label" => $this->translate('end_time'), "cols" => array("end_date")),
    
        );
    }

	public function insert_patient_medipumps ( $post )
	{

		$start_date = date('Y-m-d H:i:s', strtotime($post['medipump_date_start']));
		if (strlen($post['medipump_date_end']) > 0)
		{
			$end_date = date('Y-m-d H:i:s', strtotime($post['medipump_date_end']));
		}
		else
		{
			$end_date = '0000-00-00 00:00:00';
		}

		$med = new PatientMedipumps();
		$med->ipid = $post['ipid'];
		$med->medipump = $post['medipump'];
		$med->start_date = $start_date;
		$med->end_date = $end_date;
		$med->isdelete = "0";
		$med->save();

		return $med;
	}

	public function update_patient_medipump ( $post, $medipump )
	{
		$start_date = date('Y-m-d H:i:s', strtotime($post['medipump_edit_start']));

		if (strlen($post['medipump_edit_end']) > 0)
		{
			$end_date = date('Y-m-d H:i:s', strtotime($post['medipump_edit_end']));
		}
		else
		{
			$end_date = '0000-00-00 00:00:00';
		}


		$med = Doctrine::getTable('PatientMedipumps')->find($medipump);
		$med->medipump = $post['medipump'];
		$med->start_date = $start_date;
		$med->end_date = $end_date;
		$med->isdelete = "0";
		$med->save();
	}

	public function delete_patient_medipump ( $ipid, $medipump )
	{
		$med = Doctrine::getTable('PatientMedipumps')->findOneByIpidAndId($ipid, $medipump);
		$med->isdelete = '1';
		$med->save();
	}

	public function close_medipump_lent_period ( $ipid, $end_date )
	{
		if (strlen($end_date) > 0)
		{
			$end_date = date('Y-m-d H:i:s', strtotime($end_date));
		}
		else
		{
			$end_date = date('Y-m-d H:i:s', time());
		}


		if (strtotime($end_date) >= strtotime($verify_patient_medipumpe[0]['start_date']) )
		{
			$update = Doctrine_Query::create()
			->update('PatientMedipumps')
			->set('end_date', "'".$end_date."'")
			->where('ipid ="'.$ipid.'"')
			->andWhere('isdelete = "0"')
			->andWhere('end_date = "0000-00-00 00:00:00"');
			$update_res = $update->execute();

			return $update_res;
		}
	}

	public function check_patient_medipump_lend ( $ipid )
	{
		$mp = Doctrine_Query::create()
		->select('*')
		->from('PatientMedipumps')
		->where('ipid LIKE "' . $ipid . '"')
		->andWhere('isdelete = "0"')
		->andWhere('end_date = "0000-00-00 00:00:00"')
		->orderBy('start_date ASC')
		->limit(1);
		$mp_res = $mp->fetchArray();

		if ($mp_res)
		{
			return $mp_res;
		}
		else
		{
			return false; //patient has no rent
		}
	}

	public function reset_patient_medipumps($ipid, $start_date)
	{
		$start_date = date('Y-m-d H:i:s',strtotime($start_date));

		$q_del = Doctrine_Query::create()
		->delete('PatientMedipumpsControl')
		->where('MONTH(date) = MONTH("' . $start_date . '")')
		->andWhere('YEAR(date) = YEAR("' . $start_date . '")')
		->andWhere('ipid LIKE "'.$ipid.'"');
		$q_del_res = $q_del->execute();
	}
	
	
	
	
	
	
	
	

	/**
	 * @cla on 12.07.2018
	 *
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_patient_medipump($values =  array() , $elementsBelongTo = null)
	{
	
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	     
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_medipump");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend('Add Patient Medipumps');
	    $subform->setAttrib("class", "label_same_size " . __FUNCTION__);
	     
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	
// 	    if (!empty($values)) dd($values);

	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : null,
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	        ),
	    ));
	    
	    
	    $notEmpty = new Zend_Validate_NotEmpty(["type"=> "integer"]);
	    
	    $subform->addElement('select', 'medipump', array(
	        'label'      => 'referredby',
	        'multiOptions' => $this->getClientMedipumpsArray(),
	        'required'   => true,
	        'value'        => ! empty($values['medipump']) ? $values['medipump'] : 0,
	        
	        'filters'    => array('StringTrim'),
	        
	        'validators' => array(['NotEmpty' ,["type"=> "integer"]]),
	        
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    
	    
	    $subform->addElement('text', 'start_date', array(
	        'value'        => empty($values['start_date']) || $values['start_date'] == "0000-00-00 00:00:00" ? "" : date('d.m.Y', strtotime($values['start_date'])),
	        'label'        => 'start_time',
	    
	        'required'   => true,
	    
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	    
	        //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
	    
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class' => 'date allow_future',
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',
	    ));
	    
	    $subform->addElement('text', 'end_date', array(
	        'value'        => empty($values['end_date']) || $values['end_date'] == "0000-00-00 00:00:00" ? "" : date('d.m.Y', strtotime($values['end_date'])),
	        'label'        => 'end_time',
	    
	        'required'   => false,
	    
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	    
	        //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
	    
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class' => 'date allow_future',
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',
	    ));
	    
	    
        return $this->filter_by_block_name($subform, __FUNCTION__);
	
	}
	

	public function getClientMedipumpsArray()
	{
	    $result = [0 => $this->translate('select_medipumps')];
	     
	    if ( isset( $this->_patientMasterData['Medipumps'])) {
	        foreach ($this->_patientMasterData['Medipumps'] as $row) {
	            $result[ $row['id'] ] =  $row['medipump'];
	        }
	    } else {
	        //do this ELSE if you really needit
	    }
	     
	    return $result;
	
	}
	
	
	
	public function save_form_patient_medipump($ipid = '', $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    
	    if ( ! empty($data['start_date'])) {
	        $date = new Zend_Date($data['start_date']);
	        $data['start_date'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
	        $data['start_date'] = '0000-00-00 00:00:00';
	    }
	    
	    if ( ! empty($data['end_date'])) {
	        $date = new Zend_Date($data['end_date']);
	        $data['end_date'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
	        $data['end_date'] = '0000-00-00 00:00:00';
	    }
	    
	    $entity = new PatientMedipumps();
	    
	    $result = $entity ->findOrCreateOneByIpidAndId($ipid, $data['id'], $data);
	     
	    
	    if (empty($data['id'])) {
	        $this->_send_todo_medipumps_to_Koordination($ipid);
	    }
	    
	}

	
	
	private function _send_todo_medipumps_to_Koordination ($ipid = '') 
	{
	    if (empty($ipid)) {
	        return;
	    }
	    
	    $result = null;
	    
	    $master_groups = array("6"); //Koordination master group
	    $user_group = new Usergroup();
	    $users_groups = $user_group->getUserGroups($master_groups);
	    
	    $text = 'Rezept Medikamentenpumpe bestätigen';
	    
	    $until_date = $create_date = date('Y-m-d H:i:s', time());
	    
	    $records_todo = [];
	    
	    if(count($users_groups) > 0)
	    {
	        foreach($users_groups as $group)
	        {
	            $records_todo[] = array(
	                "client_id" => $this->logininfo->clientid,
	                "user_id" => $this->logininfo->userid,
	                "group_id" => $group['id'],
	                "ipid" => $ipid,
	                "todo" => $text,
	                "triggered_by" => 'system_medipumps',
	                "create_date" => $until_date,
	                "until_date" => $until_date
	            );
	        }
	    }
	    
	    if(count($records_todo) > 0)
	    {	        
	        $collection = new Doctrine_Collection('ToDos');
	        $collection->fromArray($records_todo);
	        $collection->save();
	        
	        $result = $collection;
	    }
	    
	    return $result;
	}
	
	
	
}
?>