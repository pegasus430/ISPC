<?php

	Doctrine_Manager::getInstance()->bindComponent('DoctorLetter', 'MDAT');

	class DoctorLetter extends BaseDoctorLetter {

		public $triggerformid = 14;
		public $triggerformname = "frmdoctorletter";

	}

?>