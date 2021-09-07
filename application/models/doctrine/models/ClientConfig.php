<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('ClientConfig', 'SYSDAT');
class ClientConfig extends BaseClientConfig
{
    /**
     * Client-Configuration for ISPC Clinic.
     * This configuration can be manipulated by client-admins.
     * For this configuration there are extra configuration sites available.
     * For each configuration-item exist an extra entry in table ClientConfig
     *
     * @param $clientid
     * @param $item
     * @return bool|mixed
     */
    public static function getConfig($clientid, $item){
        $find = Doctrine::getTable('ClientConfig')->findOneByConfigitemAndClientId($item, $clientid);

        if ($find===false){
            return false;
        }else{
            return json_decode($find->content, 1);
        }
    }

    public static function getConfigOrDefault($clientid, $item)
    {
        //Start TODO-4163
        if($clientid==0){
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
        }
        //END TODO-4163

        $config= self::getConfig($clientid, $item);
        if(!$config){
            $config = self::getDefaultConfig($item);
        }

        return $config;

    }

    public static function saveConfig($clientid, $item, $content){
        $entry = Doctrine::getTable('ClientConfig')->findOneByConfigitemAndClientId($item,$clientid);

        if ($entry === false){
            $entry= new ClientConfig();
        }

        $entry->client_id=$clientid;
        $entry->configitem=$item;
        $entry->content=json_encode($content);
        $entry->save();
    }


    public static function getDefaultConfig($item)
    {
        $config = false;
        switch ($item) {
            case 'configjob':
                $config = array('items' => array('Beruf/Interessen/Kontext', 'Anmerkungen'), 'token' => '0');
                break;
            case 'placesofdeathlist':
                $config = Pms_CommonData::death_wish_locations(false);
                break;
            case 'supplieslist':
                $config = array("Zuhause", "Zuhause mit SAPV", "Stationäre Pflegeeinrichtung", "Stationäre Pflegeeinrichtung mit SAPV", "Hospiz", "Keine Entlassplanung nötig");
                break;
            case'dischargelist':
                $config = array(
                    array("Medikamentenplan","Medikamentenplan"),
                    array("Übergaben","Hausarzt\r\nSAPV\r\nPumpendienst"),
                    array("Medikamente","Nicht erforderlich\r\nRezept erforderlich\r\nMitgabe notwendig (Anordnung lt. OA: ###)\r\nMedikamente bestellt\r\nLieferung bis ###\r\nAbholung ab ###\r\nMedikamente zur Mitnahme fertig"),
                    array("Entlassmappe","Verordnungen\/Bescheinigungen\r\nRezepte\r\nTransportschein\r\nArztbrief\/Befunde"),
                    array("Transport","Transport bestellt für ### Uhr"));
                break;
            case 'careprocesslist':
                $config = Client::get_clinic_careprocess_config();
                break;
            case 'goalsandplans':
                $config = Client::get_clinic_plan_goal_config();
                break;
            case 'configdepression':
                $config = array(
                    "Fühlten Sie sich im letzten Monat häufig niedergeschlagen, traurig bedrückt oder hoffnungslos?",
                    "Hatten Sie im letzten Monat deutlich weniger Lust und Freude an Dingen, die Sie sonst gerne tun?"
                );
                break;
            case 'configtalkwith':
            	//ISPC-2663 Carmen 02.09.2020
                /* $config = array(
                    'Patient', 'Angehörige',
                    'Partner/Partnerin/Lebensgefährte/Lebenbensgefährtin/Ehefrau/Ehemann',
                    'Kinder (0-5 Jahre)', 'Kinder (6-13 Jahre)', 'Kinder (14-17 Jahre)', 'Kinder (ab 18 Jahre)',
                    'Eltern', 'Dolmetscher', 'KH Pflege', 'KH Arzt', 'Hauspsychologen','Hausseelsorge','Einweiser',
                    'Hausarzt',  'gesetzl. Betreuuer', 'Palliativstation','Pflegedienst', 'Pflegeheim', 'SAPV',
                    'Hospiz', 'Sanitätshaus', 'Kostenträger', 'AAPV','Jugendamt','Telefonat','kollegiales Gespräch','Sonstige',
	                    ); */
	            	$config_first_array = array(
	            			'Patient', 'Angehörige',
	            			'Partner/Partnerin/Lebensgefährte/Lebenbensgefährtin/Ehefrau/Ehemann',
	            			'Kinder (0-5 Jahre)', 'Kinder (6-13 Jahre)', 'Kinder (14-17 Jahre)', 'Kinder (ab 18 Jahre)',
	            			'Eltern'
	            	);
	            	$config_sep_first = array(str_repeat("-",70));
	            	$config_second_array = array(
	            			'Dolmetscher',
	            			'KH Pflege',
	            			'KH Arzt',
	            			'Hauspsychologen',
	            			'Hausseelsorge',
	            			'Einweiser',
	            			'Hausarzt',
	            			'gesetzl. Betreuuer',
	            			'Palliativstation',
	            			'Pflegedienst',
	            			'Pflegeheim',
	            			'SAPV',
	            			'Hospiz',
	            			'Sanitätshaus',
	            			'Kostenträger',
	            			'AAPV',
	            			'Jugendamt',
	            			'Telefonat',
	            			'kollegiales Gespräch',
	            			'Sonstige',
	            			'Kinderärzt*in',
	            			'Apotheke',
	            			'Bunter Kreis',
	            			'Ambulanter Kinderkrankenpflegedienst',
	            			'Stationäres Kinderhospiz',
	            			'Familienunterstützender Dienst',
	            			'Wohnheim',
	            			'Kindergarten',
	            			'Schule'
	            	);
	            	
	            	usort($config_second_array, array(new Pms_Sorter(), "_strcmp"));
	            	
	            	$config = array_merge($config_first_array, $config_sep_first, $config_second_array);
	            	//--
                break;
             //ISPC-2663 Carmen 02.09.2020
             case 'configtalkwithsingleselection':
             	/* $config_first_array = array(
	             	'Patient', 'Angehörige',
	             	'Partner/Partnerin/Lebensgefährte/Lebenbensgefährtin/Ehefrau/Ehemann',
	             	'Kinder (0-5 Jahre)', 'Kinder (6-13 Jahre)', 'Kinder (14-17 Jahre)', 'Kinder (ab 18 Jahre)',
	             	'Eltern', str_repeat("-",70)
             	);
             	$config_second_array = array(
             			'Dolmetscher',
             			'KH Pflege',
             			'KH Arzt',
             			'Einweiser',
             			'Hausarzt',
             			'gesetzl. Betreuuer',
             			'Pflegedienst',
             			'Pflegeheim',
             			'SAPV',
             			'Hospiz',
             			'Sanitätshaus',
             			'Kostenträger',
             			'Sonstige',
             			'Kinderärzt*in',
             			'Apotheke',
             			'Bunter Kreis',
             			'Ambulanter Kinderkrankenpflegedienst',
             			'Stationäres Kinderhospiz',
             			'Familienunterstützender Dienst',
             			'Wohnheim',
             			'Kindergarten',
             			'Schule'             			
             	);
             	
             	usort($config_second_array, array(new Pms_Sorter(), "_strcmp"));
             	
               	$config = array_merge($config_first_array, $config_second_array); */
             	$config = array(
             		'Patient',
             		'Angehörige',
             		'Partner/Partnerin/Lebensgefährte/Lebenbensgefährtin/Ehefrau/Ehemann',
             		'Kinder (0-5 Jahre)',
             		'Kinder (6-13 Jahre)',
             		'Kinder (14-17 Jahre)', 'Kinder (ab 18 Jahre)',
             		'Eltern',
             		'Dolmetscher',
             		'KH Pflege',
             		'KH Arzt',
             		'Einweiser',
             		'Hausarzt',
             		'gesetzl. Betreuuer',
             		'Pflegedienst',
             		'Pflegeheim',
             		'SAPV',
             		'Hospiz',
             		'Sanitätshaus',
             		'Kostenträger',
             		'Sonstige'
             	);
                break;
            //--
        }
        return $config;
    }

    /**
     * Get the Configuration for the Contactform-Block 'Conversation-content' (IM-46)
     * Separate the items, that are not relevant for the given Contactform
     * Complete the items with further informations for storing the contactform
     * @param $clientid
     * @param $form_type_id
     * @return array
     */
    public static function getConfigTalkContent($clientid, $form_type_id){

        $erg = array();
        $config = self::getConfig($clientid, 'configtalkcontent');

        foreach($config as $conf){
            if(in_array( $form_type_id, $conf['visible'])){
                unset($conf['visible']);
                $conf['freetext_val'] = '';
                $conf['checkbox_val'] = '0';
                $erg[] = $conf;
            }
        }

        return $erg;
    }
}
?>