<?php

	Doctrine_Manager::getInstance()->bindComponent('DoctorLetterFaxvor', 'MDAT');

	class DoctorLetterFaxvor extends BaseDoctorLetterFaxvor {

		public $triggerformid = 14;
		public $triggerformname = "frmdoctorletter";

	}

?>