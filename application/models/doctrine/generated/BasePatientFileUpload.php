<?php

	abstract class BasePatientFileUpload extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_file');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			
			//$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('title', 'string', NULL, array('type' => 'string', 'length' => NULL));
			
			$this->hasColumn('file_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('source_ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('source_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdeleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('file_type', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('file_date', 'date', null, array(
					'type' => 'date',
					'default' => NULL,
					'comments' => 'file original date',
			));
			
			$this->hasColumn('recordid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('tabname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('system_generated', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
            //ISPC-2831 Dragos 15.03.2021
            $this->hasColumn('meta_name', 'string', 255, array('type' => 'string', 'notnull' => false, 'length' => 255));
            $this->hasColumn('comment', 'text', null, array('type' => 'text', 'notnull' => false, 'length' => null));
            $this->hasColumn('admission_id', 'integer', 11, array('type' => 'integer', 'notnull' => false, 'length' => 11));
            // -- //
		}

		function setUp()
		{
		    $this->hasMany('PatientFile2tags', array(
		        'local' => 'id',
		        'foreign' => 'file'
		    ));
		    
		    //ISPC - 2129
		    $this->hasOne('PatientFileVersion', array(
		    		'local' => 'id',
		    		'foreign' => 'file'
		    ));
            //ISPC-2831 Dragos 15.03.2021
            $this->hasOne('PatientReadmission', array(
                'local' => 'admission_id',
                'foreign' => 'id'
            ));
            // -- //
		    
			$this->actAs(new Timestamp());
			$this->actAs(new PatientInsert());
			$this->actAs(new Trigger());
			$this->actAs(new PatientFile2tag());
			
			
			/**
			 * link a FtpPutQueue record -> to this model's primaryKey
			 * @see FtpPutQueue2RecordListener
			 */
			$this->addListener(new FtpPutQueue2RecordListener(), 'FtpPutQueue2RecordListener');
			
		}

	}

?>
