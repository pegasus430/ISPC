<?php

	Doctrine_Manager::getInstance()->bindComponent('Stammblatt', 'MDAT');

	class Stammblatt extends BaseStammblatt {

		public function genderVal()
		{
			$gender = array('1' => "Mann", "2" => "Frau");
			return $gender;
		}

		public function familienstandVal()
		{
			$familienstand = array('1' => "ledig", "2" => "verwitwet", '3' => "geschieden", "4" => "getrennt", "5" => "Ehe/Partnerschaft");
			return $familienstand;
		}

		public function staatszugehorigkeitVal()
		{
			$staatszugehorigkeit = array("1" => "Deutsch", "2" => "andere Welche?");
			return $staatszugehorigkeit;
		}

		public function religionszugehorigkeitVal()
		{
			$Tr = new Zend_View_Helper_Translate();
			$orthodox = $Tr->translate('orthodox');
			$judisch = $Tr->translate('judisch');
			$muslimisch = $Tr->translate('muslimisch');
			$keine = $Tr->translate('keine');
			$wunschtbegleitlung = $Tr->translate('wunschtbegleitlung');
			$evangluth = $Tr->translate('evangluth');
			$romkath = $Tr->translate('romkath');

			$religionszugehorigkeit = array("1" => $wunschtbegleitlung, "2" => $evangluth, "3" => $romkath, "4" => $orthodox, "5" => $judisch, "6" => $muslimisch, "7" => $keine);
			return $religionszugehorigkeit;
		}

		public function diagnosegruppeVal()
		{
			$diagnosegruppe = array("1" => "bösartige Erkrankung", "2" => "nicht bösartig", "3" => "nicht bekannt", "4" => "Infektionserkrankung", "5" => "neurol. Erkrankung", "6" => "AIDS");
			return $diagnosegruppe;
		}

		public function primartumorVal()
		{
			$primartumor = array("1" => "Mamma", "2" => "Verdauungsorgane", "3" => "Urogenital", "4" => "Atmungsorgane", "5" => "Gehirn", "6" => "HNO", "7" => "Haut", "8" => "Knochen", "9" => "Lymphe", "10" => "Blut", "11" => "sonstige");
			return $primartumor;
		}

		public function metastasenVal()
		{
			$metastasen = array("1" => "Lunge", "2" => "Knochen", "3" => "Leber", "4" => "Gehirn", "5" => "Haut", "6" => "Lymphe", "7" => "sonstige");
			return $metastasen;
		}

		public function sapvverordnungdurchVal()
		{
			$sapvverordnungdurch = array("1" => "Arzt", "2" => "Klinik");
			return $sapvverordnungdurch;
		}

		public function alsVal()
		{
			$als = array("1" => "Beratung", "2" => "Koordination", "3" => "Teilversorgung", "4" => "Vollversorgung");
			return $als;
		}

		public function schmerzenVal()
		{
			$schmerzen = array("1" => "kein", "2" => "leicht", "3" => "mittel", "4" => "stark");
			return $schmerzen;
		}

	}

?>