<?php

	abstract class BaseVoluntaryworkers extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('voluntaryworkers');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('parent_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('hospice_association', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('salutation', 'string', 255, array('type' => 'string', 'length' => 255));
            $this->hasColumn('gender', 'integer', 1, array('type' => 'integer', 'length' => 1));//ISPC-2884,Elena,14.04.2021
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('inactive', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('n', 'e', 'k', 'p')));
			$this->hasColumn('status_color', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('g', 'y', 'r','b')));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('birthdate', 'date', null, array('type' => 'date', 'length' => null));
			$this->hasColumn('street', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mobile', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('indrop', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('comments', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('children', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('profession', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('appellation', 'string', 255, array('type' => 'string', 'length' => 255));
			//ISPC-2618 Carmen 30.07.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
			$this->hasColumn('linguistic_proficiency', 'string', 255, array('type' => 'string', 'length' => 255));
			//
			$this->hasColumn('edication_hobbies', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('working_week_days', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('working_hours', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('has_car', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('special_skils', 'text', NULL, array('type' => 'string', 'length' => NULL));
			//ISPC-2618 Carmen 30.07.2020
			$this->hasColumn('limitations_uncertainties', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('measles_vaccination', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('received_certificate', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('received_certificate_date', 'datetime', null, array('type' => 'datetime', 'length' => null));
			$this->hasColumn('course_management', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('conversation_leader', 'string', 255, array('type' => 'string', 'length' => 255));
			//--
			$this->hasColumn('img_path', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('img_deleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('gc_certificate', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('gc_certificate_date', 'datetime', null, array('type' => 'datetime', 'length' => null));
			//ISPC-2618 Carmen 28.07.2020
			$this->hasColumn('gc_entry', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gc_checked_by', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('confidentiality', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('health_aptitude', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('activity_agreement', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('photo_permission', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//--
			$this->hasColumn('engagement_date', 'datetime', null, array('type' => 'datetime', 'length' => null));
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('isarchived', 'integer', 1, array('type' => 'integer', 'length' => 1)); //ISPC - 2114 - archive function for vw
			$this->hasColumn('ineducation', 'integer', 1, array('type' => 'integer', 'length' => 1)); //ISPC-2401 - ineducation function for vw
			
			//ISPC-1977
			$this->hasColumn('comments_availability', 'text'); // ? this column sould be in a new table, because of the select(*)
			
			//ISPC-2618 Carmen 30.07.2020
			$this->hasColumn('bank_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('iban', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bic', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('account_holder', 'string', 255, array('type' => 'string', 'length' => 255));
			//--
		}

		function setUp()
		{
		    parent::setUp();
		    
		    /*
		     * if you request is from patient/versorger, prevent deleting the ones with indrop=0
		     */
		    $this->addListener(new PreventIndrodDelete(array("indrop" => "indrop")));
		    
		    $this->actAs(new Softdelete());
		    
			$this->actAs(new Timestamp());
			

			$this->hasMany('VoluntaryworkersStatuses', array(
			    'local' => 'id',
			    'foreign' => 'vw_id'
			));
			
			// ISPC-2603 Andrei 02.06.2020
			$this->hasMany('VoluntaryworkersCourse', array(
			    'local' => 'id',
			    'foreign' => 'vw_id'
			));
			
			
		}

	}

?>