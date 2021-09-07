<?php

	Doctrine_Manager::getInstance()->bindComponent('LettersTextBoxes', 'MDAT');

	class LettersTextBoxes extends BaseLettersTextBoxes {

		public function client_letter_boxes($clientid)
		{
			$drop  = $this->getTable()->createQuery()
				->select("*")
				->where("clientid='" . $clientid . "'");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function client_letter_boxes_item($clientid, $item)
		{
			$drop  = $this->getTable()->createQuery()
				->select("*")
				->where("clientid='" . $clientid . "'")
				->andWhere("id='" . $item . "'");
			$droparray = $drop->fetchArray();

			return $droparray;
		}
	}
?>