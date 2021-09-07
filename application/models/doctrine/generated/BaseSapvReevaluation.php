<?php

	abstract class BaseSapvReevaluation extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sapv_reevaluation');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('epid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hi_company_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kassen_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betriebsstatten_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('institutskennzeichen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('admisioncycle', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('gender', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('age', 'string', 3, array('type' => 'string', 'length' => 3));
			$this->hasColumn('beginSapvFall', 'datetime', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('icddiagnosis', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('icdNDdiagnosis', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('firstSapvMaxbe', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('firstSapvMaxko', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('firstSapvMaxtv', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('firstSapvMaxvv', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('erstsapv', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('weideraufnahme', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('stathospiz', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('kranken', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('palliativ', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('statpflege', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('ambhospizdienst', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('ambpflege', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('harzt', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('farzt', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('patange', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('beratung', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('alone', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('house_of_relatives', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('hospiz', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('nursingfacility', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('curentlivingother', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('curentlivingmore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('stagekeine', 'string', 5, array('type' => 'string', 'length' => 5));
			$this->hasColumn('stageone', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('stagetwo', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('stagethree', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('beantragt', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('nbeantragt', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('expectationkeine', 'string', 5, array('type' => 'string', 'length' => 5));
			$this->hasColumn('expectationsonstiges', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('expectation', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('preabilitation', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('symptomrelief', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('nohospital', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('nolifeenxendingmeasures', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('leftalone', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('activeparticipation', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('treatmentscopeexpectation', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('patientexpectationsapv', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('painsymptoms', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('gastrointestinalsymptoms', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('psychsymptoms', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('urogenitalsymptoms', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('ulztumor', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('cardiacsymptoms', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('ethicalconflicts', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('acutecrisispat', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('paliatifpflege', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('privatereferencesupport', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('sociolegalproblems', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('securelivingenvironment', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('coordinationcare', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('complexeventsmore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('otherrequirements', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('actualconductedbe', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('actualconductedko', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('actualconductedtv', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('actualconductedvv', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('endSapvFall', 'datetime', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('stabilization', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('causaltherapy', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('regulationexpiration', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('laying', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('deceased', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('noneedsapv', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('sapvterminationother', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('sapvstatusja', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('sapvstatusnein', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('sapvstatuspartialy', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('sapvstatusunknown', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('stagelastkeine', 'string', 5, array('type' => 'string', 'length' => 5));
			$this->hasColumn('stagelastone', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('stagelasttwo', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('stagelastthree', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('lastbeantragt', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('nlastbeantragt', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('homedead', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('heimdead', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('hospizdead', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('palliativdead', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('krankendead', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('deathwishja', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('deathwishnein', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('deathwishunknown', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('bereitschft', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('allhospitaldays', 'string', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('besuche', 'string', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('hospitalwithNotarz', 'string', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('hospitalwithoutNotarz', 'string', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('export_ready', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>