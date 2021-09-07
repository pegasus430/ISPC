<?php

	Doctrine_Manager::getInstance()->bindComponent('IcdOpsMreSettings', 'SYSDAT');

	/**
	 * ISPC-2654 Lore 05.10.2020
	 * @author Loredana
	 *
	 */
	
	class IcdOpsMreSettings extends BaseIcdOpsMreSettings {

		
	    public function getIcdOpsMreSettings($clientid)
		{
			$ioms = Doctrine_Query::create()
				->select('*')
				->from('IcdOpsMreSettings')
				->where('clientid = ?',$clientid)
				->andWhere('isdelete = 0' );
			$iomsarr = $ioms->fetchArray();
			
			return $iomsarr;

		}
		
		public static function getCategory()
		{
		    $category = array(
		        '1' => "Hauptdiagnose",
		        '2' => "Grunderkrankungen",
		        '3' => "Folgeerkrankung",
		        '4' => "Symptome",
		        '5' => "Archiv",
		        '6' => "Diagnose",
		        '7' => "Aktueller Aufenthalt",
		    );
		    
		    return $category;
		}
		
		public static function getShortcutCategory()
		{
		    $shortcut = array(
		        '1' => "H",
		        '2' => "G",
		        '3' => "F",
		        '4' => "S",
		        '5' => "X",
		        '6' => "D",
		        '7' => "A",
		    );
		    return $shortcut;
		}

	}

?>