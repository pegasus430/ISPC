<?php

	Doctrine_Manager::getInstance()->bindComponent('DoctorLetterZapv', 'MDAT');

	class DoctorLetterZapv extends BaseDoctorLetterZapv {

		public $triggerformid = 14;
		public $triggerformname = "frmdoctorletter";

	}

?>