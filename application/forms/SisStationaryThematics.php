<?php
	//require_once("Pms/Form.php");

	class Application_Form_SisStationaryThematics extends Pms_Form {

		/*public function insert($post_data)
		{
		    
            foreach($post_data['theme'] as $theme => $values )
            {
                // clear data
                $this->clear_thematics($post_data['ipid'],$post_data['form_id'],$theme);
                
                // insert 
                $cust = new SisAmbulantThematics();
    			$cust->ipid = $post_data['ipid'];
    			$cust->clientid = $post_data['clientid'];
    			$cust->form_id = $post_data['form_id'];
    			$cust->thematic = $theme;
    			$cust->thematic_text= htmlspecialchars($values['thematic_text']);
    			$cust->dekubitus = $values['dekubitus'];
    			$cust->future_dekubitus= $values['future_dekubitus'];
    			$cust->beratung_dekubitus= $values['beratung_dekubitus'];
    			$cust->sturz= $values['sturz'];
    			$cust->future_sturz= $values['future_sturz'];
    			$cust->beratung_sturz= $values['beratung_sturz'];
    			$cust->inkontinenz= $values['inkontinenz'];
    			$cust->future_inkontinenz= $values['future_inkontinenz'];
    			$cust->beratung_inkontinenz= $values['beratung_inkontinenz'];
    			$cust->schmerz= $values['schmerz'];
    			$cust->future_schmerz= $values['future_schmerz'];
    			$cust->beratung_schmerz= $values['beratung_schmerz'];
    			$cust->ernahrung= $values['ernahrung'];
    			$cust->future_ernahrung= $values['future_ernahrung'];
    			$cust->beratung_ernahrung= $values['beratung_ernahrung'];
    			$cust->sonstiges= $values['sonstiges'];
    			$cust->future_sonstiges= $values['future_sonstiges'];
    			$cust->beratung_sonstiges= $values['beratung_sonstiges'];
    			$cust->save();
            }
		}
		
		public function clear_thematics($ipid,$form_id,$theme)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    if(!empty($ipid) && !empty($form_id))
		    {
		        $loc = Doctrine_Query::create()
		        ->update("SisAmbulantThematics")
		        ->set('isdelete', "1")
		        ->set('change_date', '"'.date("Y-m-d H:i:s", time()).'"')
		        ->set('change_user', $userid)
		        ->where("form_id = '" .$form_id . "'")
		        ->andWhere("ipid = '" .$ipid . "'")
		        ->andWhere("thematic = '" .$theme . "'");
		        $loc->execute();
		    }
		    
		}*/
		
		/**
		 *
		 * @param unknown $ipid
		 * @param unknown $data
		 * @throws Exception
		 * @return NULL|Doctrine_Record
		 */
		public function save_form_sisstationary_thematics($ipid = null, array $data = array())
		{
			if (empty($ipid) || empty($data)) {
				return; //nothing to save
			}
			 
			//         dd($data);
			//formular will be saved first so we have a id
		
			$records =  new Doctrine_Collection('SisStationaryThematics');
		
			$records->synchronizeWithArray($data);
		
			$records->save();
		
			return $records;
		}
		
		
	}
?>