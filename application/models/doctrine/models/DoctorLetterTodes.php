<?php

	Doctrine_Manager::getInstance()->bindComponent('DoctorLetterTodes', 'MDAT');

	class DoctorLetterTodes extends BaseDoctorLetterTodes {

		public $triggerformid = 14;
		public $triggerformname = "frmdoctorletter";

	}

?>