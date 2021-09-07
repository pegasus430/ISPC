<?php

abstract class BasePatientHealthInsurance2Subdivisions extends Pms_Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('patient_health_insurance2subdivision');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint','length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('company_id', 'bigint', NULL, array('type' => 'bigint','length' => NULL));
		$this->hasColumn('subdiv_id', 'integer', 5, array('type' => 'integer','length' => 5));

		$this->hasColumn('ins2s_name', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_insurance_provider', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_contact_person', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_street1', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_street2', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_zip', 'integer', 8, array('type' => 'integer','length' => 8));
		$this->hasColumn('ins2s_city', 'varchar', 255, array('type' => 'varchar','length' => 255));
		$this->hasColumn('ins2s_phone', 'varchar', 255, array('type' => 'varchar','length' => 255));
		$this->hasColumn('ins2s_phone2', 'varchar', 255, array('type' => 'varchar','length' => 255));
		$this->hasColumn('ins2s_email', 'varchar', 255, array('type' => 'varchar','length' => 255));
		$this->hasColumn('ins2s_fax', 'varchar', 255, array('type' => 'varchar','length' => 255));
		
		$this->hasColumn('ins2s_post_office_box', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_post_office_box_location', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_email', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_zip_mailbox', 'string', 255, array('type' => 'string','length' => 255));

		$this->hasColumn('ins2s_iknumber', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_ikbilling', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_kvnumber', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ins2s_debtor_number', 'string', 255, array('type' => 'string','length' => 255));
		
		
		
		$this->hasColumn('comments', 'text', NULL, array('type' => 'text','length' => NULL));
		$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer','length' => 11));

	}
	function setUp()
	{
		$this->actAs(new Softdelete());
	    
		$this->actAs(new Timestamp());

	}


}

?>