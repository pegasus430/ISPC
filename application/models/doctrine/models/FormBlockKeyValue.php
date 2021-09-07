<?php
Doctrine_Manager::getInstance()->bindComponent('FormBlockKeyValue', 'MDAT');
class FormBlockKeyValue extends BaseFormBlockKeyValue
{
    /**
     * insert into patient_course will use this
     */
    const PATIENT_COURSE_TAB_TALKCONTENT = 'contact_form_talkcontent';
    const PATIENT_COURSE_TITLE_TALKCONTENT_CREATE    = 'Gesprächsinhalt wurde erstellt';
    const PATIENT_COURSE_TITLE_TALKCONTENT_UPDATE    = 'Gesprächsinhalt wurde bearbeitet';
    const PATIENT_COURSE_TAB_PALLIATIV_ASSESSMENT = 'contact_form_palliativ_assessment';
    const PATIENT_COURSE_TITLE_PALLIATIV_ASSESSMENT_CREATE    = 'Beurteilung wurde erstellt';
    const PATIENT_COURSE_TAB_CLINIC_SOAP = 'contact_form_clinic_soap';
    const PATIENT_COURSE_TITLE_CLINIC_SOAP_CREATE    = 'Körperliche Anamnese wurde erstellt';
    const PATIENT_COURSE_TITLE_CLINIC_SOAP_UPDATE    = 'Körperliche Anamnese wurde bearbeitet';
    const PATIENT_COURSE_TAB_CLINIC_SHIFT = 'contact_form_clinic_shift';
    const PATIENT_COURSE_TAB_CLINIC_MEASURE = 'contact_form_clinic_measure';


    /**
     * insert into patient_course will use this
     */
    const PATIENT_COURSE_TITLE      = ' ';
    const PATIENT_COURSE_TABNAME    = ' ';
    const PATIENT_COURSE_TYPE       = 'K'; // add letter


    //fetch with original db-keys
    public function getLmuFachdiensteInDbScheme($ipid, $fbkv=null){
        if(!$fbkv){
            $fbkv=$this->getLastBlockValues($ipid, 'lmu_pmba_psysoz');
        }
        if ($fbkv){
            $fachstellen=array(
                "hausarzt_id"=>array("Hausarzt", array()),
                "hausarzt2_id"=>array("Arzt", array()),
                "pflegedienst_id"=>array("Pflegedienst", array()),
                "palliativpflegedienst_id"=>array("Palliativpflegedienst", array()),
                "sapv_team_id"=>array("SAPV-Team", array()),
                "ehrenamtlicher_dienst_id"=>array("Ehrenamtlicher Dienst", array()),
                "pflegeheim_id"=>array("Pflegeheim", array()),
                "stat_hospiz_id"=>array("Stationäres Hospiz", array()),
                "infusionsdienst_id"=>array("Infusionsdienst", array()),
                "weitere_fachdienste_id"=>array("Weitere Fachdeinste", array()),
                "sanihaus_id"=>array("Sanitätshaus", array()),
                "apotheke_id"=>array("Apotheke", array())
            );



            $fachids=array();


            foreach ($fachstellen as $rowid=>$foo){
                if (is_array($fbkv[$rowid])){
                    foreach ($fbkv[$rowid] as $fachid){
                        if ($fachid>0){
                            if(is_array($fachids[$rowid])){
                                $fachids[$rowid][]=$fachid;
                            } else{
                                $fachids[$rowid]=array($fachid);
                            }

                        }
                    }
                }
            }

            foreach ($fachids as $group => $ids){
                switch ($group){
                    case "apotheke_id":
                        $drop = Doctrine_Query::create()
                            ->select('*')
                            ->from('Pharmacy p')
                            ->whereIn('id',$ids);
                        $droparray=$drop->fetchArray();
                        if(is_array($droparray)){
                            foreach ($droparray as $val){
                                $newentry=array();
                                $newentry['id'] = $val['id'];
                                $newentry['pharmacy'] = html_entity_decode($val['pharmacy'], ENT_QUOTES, "utf-8");
                                $newentry['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
                                $newentry['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
                                $newentry['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
                                $newentry['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
                                $newentry['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
                                $newentry['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
                                //$drop_array[$key]['phone_private'] = html_entity_decode($val['phone_private'], ENT_QUOTES, "utf-8");
                                $newentry['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");

                                $fachstellen[$group][1][]=$newentry;
                            }
                        }
                        break;
                    case "pflegedienst_id":
                    case "palliativpflegedienst_id":
                    case "sapv_team_id":
                    case "ehrenamtlicher_dienst_id":
                    case "pflegeheim_id":
                    case "stat_hospiz_id":
                    case "infusionsdienst_id":
                    case "weitere_fachdienste_id":
                        $drop = Doctrine_Query::create()
                            ->select('*')
                            ->from('Pflegedienstes p')
                            ->whereIn('id',$ids);
                        $droparray=$drop->fetchArray();
                        if(is_array($droparray)){
                            foreach ($droparray as $val){
                                $newentry=array();
                                $newentry['id'] = $val['id'];
                                $newentry['nursing'] = html_entity_decode($val['nursing'], ENT_QUOTES, "utf-8");
                                $newentry['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
                                $newentry['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
                                $newentry['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
                                $newentry['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
                                $newentry['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
                                $newentry['phone_practice'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
                                $newentry['phone_private'] = html_entity_decode($val['phone_private'], ENT_QUOTES, "utf-8");
                                $newentry['phone_emergency'] = html_entity_decode($val['phone_emergency'], ENT_QUOTES, "utf-8");
                                $newentry['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");

                                $fachstellen[$group][1][]=$newentry;
                            }
                        }
                        break;
                    case "hausarzt_id":
                    case "hausarzt2_id":
                        $drop = Doctrine_Query::create()
                            ->select('*')
                            ->from('FamilyDoctor p')
                            ->whereIn('id',$ids);
                        $droparray=$drop->fetchArray();
                        if(is_array($droparray)){
                            foreach ($droparray as $val){
                                $newentry=array();
                                $newentry['id'] = $val['id'];
                                $newentry['practice'] = html_entity_decode($val['practice'], ENT_QUOTES, "utf-8");
                                $newentry['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
                                $newentry['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
                                $newentry['title'] = html_entity_decode($val['title'], ENT_QUOTES, "utf-8");
                                $newentry['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
                                $newentry['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
                                $newentry['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
                                $newentry['phone_practice'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
                                $newentry['phone_private'] = html_entity_decode($val['phone_private'], ENT_QUOTES, "utf-8");
                                //$newentry['phone_cell'] = html_entity_decode($val['phone_cell'], ENT_QUOTES, "utf-8");
                                $newentry['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");

                                $fachstellen[$group][1][]=$newentry;
                            }
                        }
                        break;
                    case "sanihaus_id":
                        $drop = Doctrine_Query::create()
                            ->select('*')
                            ->from('Supplies p')
                            ->whereIn('id',$ids);
                        $droparray=$drop->fetchArray();
                        if(is_array($droparray)){
                            foreach ($droparray as $val){
                                $newentry=array();
                                $newentry['id'] = $val['id'];
                                $newentry['supplier'] = html_entity_decode($val['supplier'], ENT_QUOTES, "utf-8");
                                $newentry['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
                                $newentry['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
                                $newentry['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
                                $newentry['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
                                //$drop_array[$key]['phone_private'] = html_entity_decode($val['phone_private'], ENT_QUOTES, "utf-8");
                                $newentry['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");

                                $fachstellen[$group][1][]=$newentry;
                            }
                        }
                        break;
                }


            }


            return $fachstellen;

        }
    }


    public function getLmuFachdienste($ipid, $fbkv=null){
        if(!$fbkv){
            $fbkv=$this->getLastBlockValues($ipid, 'lmu_pmba_psysoz');
        }
        if ($fbkv){
            $fachstellen=array(
                "hausarzt_id"=>array("Hausarzt", array()),
                "hausarzt2_id"=>array("Arzt", array()),
                "pflegedienst_id"=>array("Pflegedienst", array()),
                "palliativpflegedienst_id"=>array("Palliativpflegedienst", array()),
                "sapv_team_id"=>array("SAPV-Team", array()),
                "ehrenamtlicher_dienst_id"=>array("Ehrenamtlicher Dienst", array()),
                "pflegeheim_id"=>array("Pflegeheim", array()),
                "stat_hospiz_id"=>array("Stationäres Hospiz", array()),
                "infusionsdienst_id"=>array("Infusionsdienst", array()),
                "weitere_fachdienste_id"=>array("Weitere Fachdienste", array()),
                "sanihaus_id"=>array("Sanitätshaus", array()),
                "apotheke_id"=>array("Apotheke", array())
            );



            $fachids=array();


            foreach ($fachstellen as $rowid=>$foo){
                if (is_array($fbkv[$rowid])){
                    foreach ($fbkv[$rowid] as $fachid){
                        if ($fachid>0){
                            if(is_array($fachids[$rowid])){
                                $fachids[$rowid][]=$fachid;
                            } else{
                                $fachids[$rowid]=array($fachid);
                            }

                        }
                    }
                }
            }

            foreach ($fachids as $group => $ids){
                switch ($group){
                    case "apotheke_id":
                        $drop = Doctrine_Query::create()
                            ->select('*, p.pharmacy as practice')
                            ->from('Pharmacy p')
                            ->whereIn('id',$ids);
                        $droparray=$drop->fetchArray();
                        break;
                    case "pflegedienst_id":
                    case "palliativpflegedienst_id":
                    case "sapv_team_id":
                    case "ehrenamtlicher_dienst_id":
                    case "pflegeheim_id":
                    case "stat_hospiz_id":
                    case "infusionsdienst_id":
                    case "weitere_fachdienste_id":
                        $drop = Doctrine_Query::create()
                            ->select('*, p.nursing as practice, p.phone_practice as phone')
                            ->from('Pflegedienstes p')
                            ->whereIn('id',$ids);
                        $droparray=$drop->fetchArray();
                        break;
                    case "hausarzt_id":
                    case "hausarzt2_id":
                        $drop = Doctrine_Query::create()
                            ->select('*, p.phone_practice as phone')
                            ->from('FamilyDoctor p')
                            ->whereIn('id',$ids);
                        $droparray=$drop->fetchArray();
                        break;
                    case "sanihaus_id":
                        $drop = Doctrine_Query::create()
                            ->select('*, p.supplier as practice')
                            ->from('Supplies p')
                            ->whereIn('id',$ids);
                        $droparray=$drop->fetchArray();
                        break;
                }

                if(is_array($droparray)){
                    foreach ($droparray as $val){
                        $newentry=array();
                        $newentry['id'] = $val['id'];
                        $newentry['name'] = html_entity_decode($val['practice'], ENT_QUOTES, "utf-8");
                        if(!$newentry['name']) $newentry['name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8") . " ". html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");;
                        $newentry['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
                        $newentry['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
                        $newentry['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
                        $newentry['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
                        //$drop_array[$key]['phone_private'] = html_entity_decode($val['phone_private'], ENT_QUOTES, "utf-8");
                        $newentry['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");

                        $fachstellen[$group][1][]=$newentry;
                    }
                }
            }


        return $fachstellen;

        }
    }

	public function getPatientFormBlockKeyValues ( $ipid, $contact_form_id, $allow_deleted = false, $blockname)
	{

		$groups_sql = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockKeyValue')
		    ->where('ipid LIKE "' . $ipid . '"')
		    ->andWhere('contact_form_id ="' . $contact_form_id . '"')
		    ->andWhere('block = ?',$blockname);
		if(!$allow_deleted)
		{
			$groups_sql->andWhere('isdelete = 0');
		}
		
		$groupsarray = $groups_sql->fetchArray();

		if ($groupsarray)
		{

			$returnarray=array();
			foreach ($groupsarray as $elem)
				{
				if (isset($returnarray[$elem['k']]))
					{		
					if (is_array($returnarray[$elem['k']]))
						{
							$returnarray[$elem['k']][]=$elem['v'];
						} 
						else 
						{
							$returnarray[$elem['k']]=array($returnarray[$elem['k']],$elem['v']);
						}
					} 
					else
					{
						$returnarray[$elem['k']]=$elem['v'];
					}
				}
			return $returnarray;
			
		}
	}


    public static function getBayernleistungHtmlFromTable($ipid, $cid){
        $bayernleistungview = new Zend_View();
        $bayernleistungview->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");
        $bayernleistung_block=new FormBlockKeyValue();
        $patient_bayernleistung = $bayernleistung_block->getPatientFormBlockKeyValues($ipid, $cid, false, 'bayernleistung');

        if(is_array($patient_bayernleistung)){
            $patient_bayernleistung=json_decode($patient_bayernleistung['bayernleistung']);
        }
        $bayernleistungview->values=$patient_bayernleistung;
        $html=$bayernleistungview->render('cfblock_bayernleistung.html');
        return $html;
    }

    public static function getBayernleistungHtmlFromPost($sapvleistung){
        $sapvleistungview = new Zend_View();
        $sapvleistungview->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");
        $sapvleistungview->pdf=1;
        $sapvleistungview->values=$sapvleistung;
        $html=$sapvleistungview->render('cfblock_bayernleistung.html');
        return $html;
    }
	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function getAllBlockValues_with_contactform ( $ipid, $blockname, $datefrom=null, $dateto=null, $contactform_id=false)
    {
        if(!isset($datefrom))
            $datefrom = '1900-01-01 00:00:00';
        if(!isset($dateto))
            $dateto = '9999-01-01 00:00:00';


        $q1 = Doctrine_Query::create()
            ->select("c.id, c.ipid, c.contact_form_id, c.k, c.v, e.start_date, e.create_user")
            ->from("FormBlockKeyValue c")
            ->leftJoin("c.ContactForm e")
            ->where ('e.isdelete=0')
            ->andWhere("c.ipid=?", $ipid)
            ->andWhere('c.block=?', $blockname)
            ->andWhere('c.create_date > ?', $datefrom)
            ->andWhere('c.create_date < ?', $dateto)
            ->andWhere('c.isdelete = 0');
            if($contactform_id)
                $q1->andWhereIn('c.contact_form_id', $contactform_id);
            $q1->orderBy('id DESC');
            $q1->limit(200); //maybe we have forms with more than 100 entries

        return $q1->fetchArray();

    }

    public function getLastBlockValues ( $ipid, $blockname)
    {

        $groups_sql = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockKeyValue')
            ->where('ipid LIKE "' . $ipid . '"')
            ->andWhere('block = ?',$blockname)
            ->andWhere('isdelete = 0')
            ->orderBy('id DESC')
            ->limit(200); //maybe we have forms with more than 100 entries

        $groupsarray = $groups_sql->fetchArray();

        if ($groupsarray)
        {
            $cfid=$groupsarray[0]['contact_form_id'];
            $returnarray=array();
            foreach ($groupsarray as $elem)
            {
                if($elem['contact_form_id']==$cfid){
                    if (isset($returnarray[$elem['k']]))
                    {
                        if (is_array($returnarray[$elem['k']]))
                        {
                            $returnarray[$elem['k']][]=$elem['v'];
                        }
                        else
                        {
                            $returnarray[$elem['k']]=array($returnarray[$elem['k']],$elem['v']);
                        }
                    }
                    else
                    {
                        $returnarray[$elem['k']]=$elem['v'];
                    }
                }
            }
            return $returnarray;

        }
    }


    public function getLastBlockValuesWithUserid ( $ipid, $blockname)
    {

        $groups_sql = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockKeyValue')
            ->where('ipid LIKE "' . $ipid . '"')
            ->andWhere('block = ?',$blockname)
            ->andWhere('isdelete = 0')
            ->orderBy('id DESC')
            ->limit(200); //maybe we have forms with more than 100 entries

        $groupsarray = $groups_sql->fetchArray();

        if ($groupsarray)
        {
            $cfid=$groupsarray[0]['contact_form_id'];
            $userid=$groupsarray[0]['create_user'];
            $returnarray=array();
            foreach ($groupsarray as $elem)
            {
                if($elem['contact_form_id']==$cfid){
                    if (isset($returnarray[$elem['k']]))
                    {
                        if (is_array($returnarray[$elem['k']]))
                        {
                            $returnarray[$elem['k']][]=$elem['v'];
                        }
                        else
                        {
                            $returnarray[$elem['k']]=array($returnarray[$elem['k']],$elem['v']);
                        }
                    }
                    else
                    {
                        $returnarray[$elem['k']]=$elem['v'];
                    }
                }
            }
            return array($returnarray,$userid);

        }
    }
public static function getPossibleLetteraddresses($ipid)
{
    $v_list = array('"hausarzt_id"', '"hausarzt2_id"', '"pflegedienst_id"', '"palliativpflegedienst_id"', '"sapv_team_id"', '"ehrenamtlicher_dienst_id"', '"pflegeheim_id"', '"stat_hospiz_id"', '"infusionsdienst_id"', '"weitere_fachdienste_id"', '"apotheke_id"', '"sanihaus_id"');
    $v_list_s = implode(",", $v_list);
    $se_query = Doctrine_Query::create()
        ->select('*')
        ->from('FormBlockKeyValue f')
        ->where('f.ipid = ?', $ipid)
        ->andWhere('f.block=?', 'lmu_pmba_psysoz')
        ->andWhere('f.v <> ""')
        ->andWhere('f.k IN (' . $v_list_s . ')')
        ->andWhere('f.isdelete=0')
        ->orderBy('id DESC')
        ->limit(15);
    $se_arr = $se_query->fetchArray();

    $pat_addr = PatientMaster::getPatientAddress($ipid);

    $addresses = array('patient_himself' => array('address' => $pat_addr[0], 'salutation_letter' => $pat_addr[1]));

    $ins_m = new PatientHealthInsurance;
    $ins_arr=$ins_m->getPatientHealthInsurance($ipid);

    if($ins_arr){
        $ins_cntprs="";
        if($ins_arr[0]['ins_contactperson'] ){
            $ins_cntprs=$ins_arr[0]['ins_contactperson'] . "\n";
        }
        $insaddr=$ins_arr[0]['company_name'] . "\n" . $ins_cntprs . $ins_arr[0]['ins_street'] . "\n" .  $ins_arr[0]['ins_zip'] . "\n" .  $ins_arr[0]['ins_city'];
        $addresses = array('Krankenversicherung' => array('address' => $insaddr, 'salutation_letter' => 'Sehr geehrte Damen und Herren,'));
    }



    foreach ($se_arr as $address) {
        if (!$addresses[$address['k'] . $address['v']]) {
            $tabname = "Pflegedienstes";
            if ($address['k'] == "hausarzt_id" || $address['k'] == "hausarzt2_id") {
                $tabname = "FamilyDoctor";
            }
            if ($address['k'] == "apotheke_id") {
                $tabname = "Pharmacy";
            }
            $ise_query = Doctrine_Query::create()
                ->select('*')
                ->from($tabname . ' f')
                ->where('f.id = ?', $address['v'])
                ->limit(1);
            $ise_arr = $ise_query->fetchArray();

            foreach ($ise_arr as $entry) {

                $salutation_anrede = $entry['salutation'];

                $salutation = "";
                if ($entry['title']) {
                    $salutation = $entry['title'] . " ";
                };
                if ($entry['first_name']) {
                    $salutation .= $entry['first_name'] . " ";
                };
                if ($entry['last_name']) {
                    $salutation .= $entry['last_name'] . " ";
                };
                if ($salutation == "" && $entry['nursing']) {
                    $salutation = $entry['nursing'];
                }
                if ($salutation == "" && $entry['practice']) {
                    $salutation = $entry['practice'];
                }
                if ($salutation == "" && $entry['pharmacy']) {
                    $salutation = $entry['pharmacy'];
                }
                if ($salutation_anrede) {
                    $salutation = $salutation_anrede . "\n" . $salutation;
                }

                $taddress = $salutation . "\n" . $entry['street1'] . "\n" . $entry['zip'] . " " . $entry['city'];
                $salutation_letter = "Sehr Geehrte Kollegen,";
                if ($entry['salutation_letter']) {
                    $salutation_letter = $entry['salutation_letter'];
                }
                $addresses[$address['k'] . $address['v']] = array('address' => $taddress, 'salutation_letter' => $salutation_letter);
            }
        }
    }

    $addresses[] = array('address' => "", 'salutation_letter' => "Sehr Geehrte Kollegen,");
    return $addresses;
}

    public static function getPmbaBodyAsText($ipid, $options=[]){
        $return=[];

        $filled_query = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockKeyValue f')
            ->where('ipid=?',$ipid)
            ->andWhere('block=?', "lmu_pmba_body")
            ->andWhere('v<>""')
            ->andWhere('isdelete=0')
            ->orderBy('id dESC')
            ->limit(1);
        $filled = $filled_query->fetchArray();

        if(!$filled){
            return "";
        }

        $cfid=$filled[0]['contact_form_id'];

        $vals_query = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockKeyValue f')
            ->where('ipid=?',$ipid)
            ->andWhere('block=?', "lmu_pmba_body")
            ->andWhere('v<>""')
            ->andWhere('contact_Form_id=?',$cfid);
        $dbvals = $vals_query->fetchArray();

        $cats=[
            'allgemeinzustand'=>'Allgemeinzustand und Ernährungszustand',
            'kopfundhals'=>'Kopf und Hals',
            'lunge'=>'Lunge',
            'herz'=>'Herz',
            'abdomen'=>'Abdomen',
            'becken'=>'Becken- und Geschlechtsorgane',
            'extremitaeten'=>'Extremitäten',
            'nervensystem'=>'Nervensystem',
            'psyche'=>'Psyche',
            'zugaenge'=>'Portkatheter/zentrale Zugänge',
            'wunden'=>'OP-Wunden',
        ];

        $vlist=[];

        foreach ($dbvals as $v){
            $parts=explode('_',$v['k']);
            $val=$v['v'];
            switch ($v['k']) {
                case "lunge_freq":
                    $val="Atemfrequenz ". $val ."/min";
                    break;
                case "herz_freq":
                    $val="Frequenz ". $val ."/min";
                    break;
                case "herz_rr":
                    $val="RR ". $val ."";
                    break;
            }
            

            $vlist[$parts[0]][]=$val;
        }

        foreach ($cats as $catk=>$caption){
            if(isset($vlist[$catk])) {
                $as_text = Pms_CommonData::transform_list_to_text($vlist[$catk]);
                $row = [
                    'caption' => $caption,
                    'key' => $catk,
                    'entries' => $vlist[$catk],
                    'as_text' => $as_text
                ];

                $return[$catk] = $row;
            }
        }

        if(isset($options['as_Array'])) {
            return $return;
        }else{
            $out="";
            foreach ($return as $row){
                $out=$out . $row['caption'] . ':\n\r' . $row['as_text'] .'\n\r\n\r';
            }
            return $out;
        }
    }

    /**
     * @param $ipid
     * @param $end_date
     * @param $end_time
     * @param false $exclude_id
     * @throws Doctrine_Query_Exception
     *
     * ISPC-2904,Elena,30.04.2021
     */
    public function cancelLastOxygenEvents($ipid, $end_date, $end_time, $exclude_id = false){
        $be_query = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockKeyValue f')
            ->where('f.ipid = ?', $ipid)
            ->andWhere('f.block=?', 'FormBlockBeatmung')
            ->andWhere('f.isdelete=0')
            ->orderBy('id DESC')
            ->limit(20);
        $be_arr = $be_query->fetchArray();

        foreach($be_arr as $be_entry){

            $data = json_decode($be_entry['v'], true);
            if($be_entry['id'] != $exclude_id){
                if($data['beatmung']['form'] == 'oxygen' && ( intval($data['beatmung']['oxygen_open_end'])  == 1 || $data['beatmung']['oxygen_date_bis'] == '' )){

                    $old_date_from = date_create_from_format('d.m.Y H:i', $data['beatmung']['oxygen_date_from'] . ' ' . $data['beatmung']['oxygen_time_from'] );
                    if($old_date_from === false){
                        $old_date_from = date_create_from_format('d.m.Y', $data['beatmung']['oxygen_date_from'] );
                    }
                    $new_date_bis = date_create_from_format('d.m.Y H:i', $end_date . ' ' . $end_time );
                    if($new_date_bis == false){
                        $new_date_bis= date_create_from_format('d.m.Y', $end_date );
                    }

                    //compare dates, prevent to set date_bis < date_from
                    if($old_date_from < $new_date_bis){
                        $data['beatmung']['oxygen_date_bis'] = $end_date;
                        $data['beatmung']['oxygen_time_bis'] = $end_time;
                    }else{
                        $data['beatmung']['oxygen_date_bis'] = $data['beatmung']['oxygen_date_from'];
                        $data['beatmung']['oxygen_time_bis'] = $data['beatmung']['oxygen_time_from'];
                    }

                    $data['beatmung']['oxygen_open_end'] = '';
                    $data_str = json_encode($data);


                    $upd = Doctrine_Query::create()
                        ->update('FormBlockKeyValue')
                        ->set('v', '?', ($data_str))
                        ->where("id=? ", $be_entry['id']);
                    $upd->execute();
                }elseif($data['beatmung']['form'] == 'oxygen' ){

                    $old_date_bis= date_create_from_format('d.m.Y H:i', $data['beatmung']['oxygen_date_bis'] . ' ' . $data['beatmung']['oxygen_time_bis'] );
                    if($old_date_bis === false){
                        $old_date_bis = date_create_from_format('d.m.Y', $data['beatmung']['oxygen_date_bis'] );
                    }
                    $new_date_bis = date_create_from_format('d.m.Y H:i', $end_date . ' ' . $end_time );
                    if($new_date_bis == false){
                        $new_date_bis= date_create_from_format('d.m.Y', $end_date );
                    }
                    if($old_date_bis > $new_date_bis){
                        $data['beatmung']['oxygen_date_bis'] = $end_date;
                        $data['beatmung']['oxygen_time_bis'] = $end_time;
                        $data_str = json_encode($data);
                        $upd = Doctrine_Query::create()
                            ->update('FormBlockKeyValue')
                            ->set('v', '?', ($data_str))
                            ->where("id=? ", $be_entry['id']);
                        $upd->execute();
                    }

                }

            }

        }
    }

}
?>
