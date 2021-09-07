<?php
/**
 * 
 * @author carmen
 * 
 * 13.08.2019
 * ISPC-2370
 */
class Application_Form_FormUsersStamps extends Pms_Form
{	
	protected $_multiple_stamps = null;
	
	protected $_user_stamps = null;
	
	protected $_user = null;

	public function __construct($options = null)
	{
		parent::__construct($options);
		
		$users = new User();
		$userarray = $users->getUserByClientid($this->logininfo->clientid, 0, true);
		
		if ( ! empty($userarray)) {
			User::beautifyName($userarray);
		} else {
			//some sort of SA or other... fetch his data
			$userarray = User::get_AllByClientid($this->logininfo->clientid);
		}
		
		if($userarray)
		{
			$user_data = array();
			$user_ids = array();
			foreach($userarray as $user)
			{
				$user_data[$user['id']]['name'] = trim($user['last_name']) . " " . trim($user['first_name']);
				$user_data[$user['id']]['businessnr'] = $user['betriebsstattennummer'];
				$user_data[$user['id']]['doctornr'] = $user['LANR'];
				$user_data_ids[] = $user['id'];
			}
				
		$this->_user = $user_data;	
	//var_dump($userarray); exit;			
			//$user_data['businessnr'] = $uarray['betriebsstattennummer'];
			//$user_data['doctornr'] = $uarray['LANR'];
		}
		
		if($this->logininfo->usertype == 'SA' || $this->logininfo->usertype == 'CA')
		{
			$isadmin = '1';
		}
		
		if($isadmin == 1)
		{
			$showselect = 1;
		}
		else
		{
			// show select to all
			$showselect = 1;
		}
		//$this->view->showselect = $showselect;
		
		$ustamp = new UserStamp();
		$multipleuser_stamp = $ustamp->getAllUsersActiveStamps($user_data_ids);
		
		foreach($multipleuser_stamp as $ks => $uspamp)
		{
			$multiple_user_stamps[$uspamp['userid']]['user_id'] = $uspamp['userid'];
			$multiple_user_stamps[$uspamp['userid']]['user_name'] = $user_data[$uspamp['userid']];
			$multiple_user_stamps[$uspamp['userid']]['user_stamps'][$uspamp['id']] = $uspamp['stamp_name'];
		}
		
		$this->_user_stamps = array();
		$this->_user_stamps['0'] = $this->translate('please select');
		
		if($this->_clientModules['64'] === true)
		{
			foreach($multiple_user_stamps as $kus=>$vus)
			{
				$user_stamps = array();
				foreach($vus['user_stamps'] as $kst=>$vst)
				{
					$user_stamps[$kus.'-'.$kst] = $vst;
				}
					
				$this->_user_stamps[$vus['user_name']['name']] = $user_stamps;
			}
		}
		else 
		{
			foreach($this->_user as $ku=>$vu)
			{
				$this->_user_stamps[$ku] = $vu['name'];
			}
		}
		
	}	
	
	public function isValid($data)
	{
	    
	    return parent::isValid($data);
	    
	}
	
	public function _create_form_userstamps($options = array(), $elementsBelongTo = null)
	{
		$subtable = new Zend_Form_SubForm();
		$subtable->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
		));
	
		if($this->_elementsBelongTo)
		{
			$this->__setElementsBelongTo($subtable, $this->_elementsBelongTo );
		}
		else if ( ! is_null($elementsBelongTo)) {
			$subtable->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$subtable->addElement('select', 'userstamps', array(
				'multiOptions' => $this->_user_stamps,
				'value'		   => '',
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				// 	        'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
						array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;')),
						array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'')),
				),
				'id' => 'stampusers_doct'
		));
		
		$subtable->addElement('note', 'stamp_alert', array(
				'value'        => $this->translate("no stamp information"),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'stamp_alert')),
						array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;')),
						array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'')),
				),
		));
		
		$subtable->addElement('note', 'user_stamp_block', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'id' => 'user_stamp_block')),
						array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;')),
						array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'')),
				),
		));
		

		return $subtable;
		
	}
	
}