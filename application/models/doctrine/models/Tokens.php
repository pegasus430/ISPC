<?php
// App::uses('ClassRegistry', 'Utility');


// App::uses('ProjectPersons', 'Model');
// App::uses('ProjectEthics', 'Model');
// App::uses('ProjectMeeting', 'Model');
// App::uses('Partner', 'Model');
// App::uses('Ethics', 'Model');
// App::uses('Category', 'Model');
// App::uses('Files', 'Model');
// App::uses('Contact', 'Model');
// App::uses('Project', 'Model');


// App::uses( 'ContactsLib', 'Lib' );
// App::uses( 'Projects', 'Lib' );
// App::uses( 'UserLib', 'Lib' );
// App::uses( 'MeetingLib', 'Lib' );
/**
 *
 * @author claudiu
 *
 *	This class was Made By Andrei@originalware, please contact him for support
 *
 */

class Tokens
{
	private $_token_list = array();
	private $_token_invoice_list = array();
	private $_salutation_list = array();
	
	public function __construct()
	{
		$this->_salutation_list = array(
				'Frau' => 'Sehr geehrte Frau',
				'Herr' => 'Sehr geehrter Herr',
				'Frau Dr.' => 'Sehr geehrte Frau Dr.',
				'Herr Dr.' => 'Sehr geehrter Herr Dr.',
				'Frau PD Dr.' => 'Sehr geehrte Frau PD Dr.',
				'Herr PD Dr.' => 'Sehr geehrter Herr PD Dr.',
				'Frau Prof. Dr.' => 'Sehr geehrte Frau Prof. Dr.',
				'Herr Prof. Dr.' => 'Sehr geehrter Herr Prof. Dr.',
		
		);
		
		/*
		 * 'SALUTATION' => array(
					'type' => 'multiple'
				),
		 */
		
		$this->_token_invoice_list = array(
				'Adresse_Rechnung' => array(
					'type' => 'single'
				),
				
				'Rechnung_Datum' => array(
					'type' => 'single'
				),
				
				'Rechnungs_Daten' => array(
					'type' => 'single'
				),
				
				'Rechnungsnummer' => array(
					'type' => 'single'
				),
				
				'Rechnungs_Kopfzeile' => array(
					'type' => 'single'
				),
				
				'Rechnung_Fusszeile' => array(
					'type' => 'single'
				),
				
				'Rechnung_Produkte' => array(
					'type' => 'single'
				),
				
				'Projekt_Ersteller' => array(
					'type' => 'single'
				),
				
				'Projekt_Ersteller_mit_Titel' => array(
					'type' => 'single'
				),
				
				'PROJECT_TYPE' => array(
					'type' => 'single'
				),
				
				'PROJECT_NUMBER' => array(
					'type' => 'single'
				),
				
				'EUDRACT' => array(
					'type' => 'single'
				),
				
				'EUDAMED' => array(
					'type' => 'single'
				),
				
				'PROJECT_TITLE' => array(
					'type' => 'single'
				),
				
				'PROTOCOL' => array(
					'type' => 'single'
				),
				
				'Anrede_Projektersteller' => array(
					'type' => 'single'
				)
				
		);
		
		$this->_token_list = array(			
				'EK_Faxnummer' => array(
						'type' => 'single'
				),
				'persoenliche_Faxnummer' => array(
						'type' => 'single'
				),
				'eigene_Anrede' => array(
						'type' => 'single'
				),
				'eigener_Name' => array(
						'type' => 'single'
				),
				'eigene_Telefonnummer' => array(
						'type' => 'single'
				),
				'Datum' => array(
						'type' => 'single'
				),				
				'Antragsnummer' => array(
					'type' => 'single'
				),
				'Antragstitel' => array(
					'type' => 'single'
				),
				'Protokoll_Nummer' => array(
					'type' => 'single'
				),
				'Datum_der_Erstellung_Projekt' => array(
					'type' => 'single'
				),
				'Adressblock_Antragsteller' => array(
					'type' => 'single'
				),
				'Adressblock_Pruefer' => array(
					'type' => 'multiple'
				),
				'pruefer' => array(
					'type' => 'multiple'
				),
				'pruefer_anrede' => array(
					'type' => 'single'
				),
				'Adressblock_Federfuehrung_EK' => array(
					'type' => 'single'
				),
				'Anrede_Projektersteller' => array(
						'type' => 'single'
				),
				'Projekt_Ersteller' => array(
					'type' => 'single'
				),
				'Projekt_Ersteller_mit_Titel' => array(
					'type' => 'single'
				),
				'EUDRACT' => array(
						'type' => 'single'
				),
				'EUDAMED' => array(
						'type' => 'single'
				),
				'Sponsor_kurz' => array(
					//depends on project, if project has only 1 sponsor = single else =multiple
					'type' => 'variable'
				),
				'Sponsor_mit_Adresse' => array(
					'type' => 'variable'
				),
				'verantwortlicher_Leiter' => array(
					'type' => 'variable'
				),
				'Datum_plus_2Wochen' => array(
					'type' => 'single'
				),
				'Datum_der_Sitzung' => array(
					'type' => 'single'
				),
				'Pruefer_in_eigener_EK' => array(
					'type' => 'single'
				),				
				'Projekt_Dateien' => array(
					'type' => 'single'
				),
				'beteiligte_EK' => array(
					'type' => 'single'
				),
				'nicht_positiv_bewertetete_Pruefstellen' => array(
					'type' => 'single'
				),				
				'eingereichte_Dokumente' => array(
					'type' => 'single'
				),
				'eingereichte_Dokumente_ohne_CS' => array(
						'type' => 'single'
				),
				'LKP' => array(
					'type' => 'single'
				),
				'Antrag_Kategorie_Name_lang' => array(
					'type' => 'single'
				),
				'Name_der_Sitzung' => array(
					'type' => 'single'
				),
				'Datum_Sitzung' => array(
					'type' => 'single'
				),
				'Uhrzeit_Sitzung' => array(
					'type' => 'single'
				),
				'Tagesordnung' => array(
					'type' => 'single'
				),
				'Zeitpunkt_besprechung' => array(
					'type' => 'single'
				),
				'protokoll_sitzung' => array(
					'type' => 'single'
				),
				'kontakt' => array(
					'type' => 'multiple'
				),
				'kontakt_anrede' => array(
					'type' => 'single'
				),
				'PROJECT_TYPE' => array(
						'type' => 'single'
				),
				'PROJECT_NUMBER' => array(
						'type' => 'single'
				),				
				'PROJECT_TITLE' => array(
						'type' => 'single'
				),				
				'PROTOCOL' => array(
						'type' => 'single'
				),
				'votum_kommission_mitglieder' => array(
						//voters selected in meeting protocol for this project
						//if 1 meeting => single, else multiple
						'type' => 'variable'
				),
				'bundesbehoerde' => array(
						//Federal ministry from Addresses
						'type' => 'single'
				)
												
		);
	}
	
	public function parse_docx_tokens( $vars )
	{
		if ( !is_array( $vars ) || empty( $vars ) )
		{
			return false;
		}
		
		$tokens = array();
		
		foreach( $vars as $section => $sTokens )
		{
			foreach( $sTokens as $token )
			{				
				$tokens[ $token ] = $token;
			}			
		}
		
		//pr( $tokens );
		//pr( $this->_token_list);
		
		return $tokens;
	}
	
	public function getInvoiceTokenValue($token, $invoice_info, $html = false )
	{
		if ( !array_key_exists( $token, $this->_token_invoice_list) )
		{
			return null;
		}
		
		switch( $token )
		{
			case 'Adresse_Rechnung':
				
				return $this->_token_invoice_address( $invoice_info );
				
				break;
				
			case 'Rechnung_Datum':
				
				return $this->_token_invoice_date( $invoice_info );
				
				break;
				
			case 'Rechnungs_Daten':
				
				if ( $html )
				{
					return $this->_token_invoice_title( $invoice_info );
				}
				else 
				{
					return null;
				}
				
				break;
				
			case 'Rechnungsnummer':
				
				return $this->_token_invoice_name( $invoice_info );
				break;
				
			case 'Rechnungs_Kopfzeile':
				
				if ( $html )
				{				
					return $this->_token_invoice_header( $invoice_info );
				}
				else
				{
					return null;
				}
				break;
				
			case 'Rechnung_Produkte':
				
				if ( $html )
				{
					return $this->_token_invoice_product_table( $invoice_info );
				}
				else
				{
					return null;
				}			
				
				break;
				
			case 'Rechnung_Fusszeile':
				
				if ( $html )
				{
					return $this->_token_invoice_footer( $invoice_info );
				}
				else
				{
					return null;
				}
				
				break;
				
			case 'Projekt_Ersteller':
				
				//CakeLog::error('Tokens project creator: ' . print_r($invoice_info, true) );
								
				return $this->_token_project_creator( $invoice_info['Project'] );
				
				break;
				
			case 'Projekt_Ersteller_mit_Titel':
			
				return $this->_token_project_creator_with_title( $invoice_info['Project'] );
			
				break;
				
			//case 'SALUTATION_PROJECT_CREATOR':
			case 'Anrede_Projektersteller':
					
				return $this->_token_project_creator_salutation( $invoice_info['Project'] );
			
				break;
				
			case 'PROJECT_TYPE':
				
				return $this->_token_project_type( $invoice_info );
				
				break;
				
			case 'PROJECT_NUMBER':
				
				return $this->_token_project_number( $invoice_info );
				
				break;
				
			case 'EUDRACT':
					
				return $this->_token_eudract( $invoice_info );
			
				break;
			
			case 'EUDAMED':
					
				return $this->_token_eudamed( $invoice_info );
			
				break;
				
			case 'PROJECT_TITLE':
				
				return $this->_token_project_title( $invoice_info ); 
				
				break;
				
			case 'PROTOCOL':
				
				return $this->_token_project_protocol( $invoice_info );
				
				break;
										
			default:
				return null;
				break;
		}
	}
	
	public function getTokenValue( $token, $project_info, $user, $html = false, $request_data = null )
	{
		if ( !array_key_exists( $token, $this->_token_list) )
		{
			return null;
		}
		
		/*
		case 'SALUTATION':
		
			return $this->_salutation_list;
		
			break;
		*/
		
		switch( $token )
		{
			//USER_FAX
			case 'EK_Faxnummer':

				return $this->_token_user_fax();
			
				break;
				
			//case 'ADMIN_FAX':
			case 'persoenliche_Faxnummer':
			
				return $user['fax'];
			
				break;
			
			//case 'SALUTATION_ADMIN_USER':
			case 'eigene_Anrede':
		
				if( isset($user['salutation_letter']) && !empty($user['salutation_letter']) )
				{
					return $user['salutation_letter'];
				}
				else 
				{
					return '';
				}
				
							
				break;
			
			//case 'ADMIN_USER':
			case 'eigener_Name':
			
				return $user['full_name'];
			
				break;

			//case 'ADMIN_PHONE':
			case 'eigene_Telefonnummer':
			
				return $user['phone'];
			
				break;
				
			//case 'DATE':
			case 'Datum':
			
				return date("d.m.Y", time());
			
				break;
				
			case 'PROJECT_NUMBER':
			case 'Antragsnummer':
			
				return $this->_token_project_number( $project_info );
												
				break;
			
			case 'PROJECT_TITLE':
			case 'Antragstitel':
				
				return $this->_token_project_title( $project_info );
											
				break;
				
			case 'PROTOCOL':
			case 'Protokoll_Nummer':
				
				return $this->_token_project_protocol( $project_info );
								
				break;
				
			//case 'PROJECT_DATE':
			case 'Datum_der_Erstellung_Projekt':
				
				return $project_info['Project']['created_formated'];
				
				break;
				
			//case 'PROJECT_CREATOR_BLOCK':
			case 'Adressblock_Antragsteller':
				
				//(Title Firstname Surname <br> Street <br> ZIP City <br> Country)
				
				$user_lib = new UserLib();
				
				if ( $project_info['Project']['company'] == 'yes' )
				{
					$data = isset($project_info['User']['company']) ? $project_info['User']['company'] : '';
					
					$data .= '\\n';
					$data .= isset($project_info['User']['applicant_name']) ? $project_info['User']['applicant_name'] : '';
				}
				else 
				{
					$data = isset($project_info['User']['applicant_name']) ? $project_info['User']['applicant_name'] : '';
				}
				
				$data .= '\\n';
				$data .= isset($project_info['User']['street']) ? $project_info['User']['street'] : '';
				$data .= '\\n';
				$data .= isset($project_info['User']['zip']) ? $project_info['User']['zip'] : '';
				$data .= " ";
				$data .= isset($project_info['User']['city']) ? $project_info['User']['city'] : '';
				$data .= '\\n';				
				$data .= isset($project_info['User']['country']) ? $user_lib->get_country_name( $project_info['User']['country'] ) : '';
				
								
				return $data;
				
				break;
				
			//case 'PROJECT_PERSON':
			case 'Adressblock_Pruefer':
				
				return $this->_token_project_person( $project_info );
								
				break;
				
			case 'pruefer':
				
				return $this->_token_project_person( $project_info, true);
				
				break;
				
			//project person salutation
			case 'pruefer_anrede':
				
				return $this->_token_project_person_salutation( $project_info, $request_data );
				
				break;
				
			case 'kontakt':
				
				return $this->_token_project_contact( $project_info );
				
				break;
				
			//project contact salutation
			case 'kontakt_anrede':
				
				return $this->_token_project_contact_salutation( $project_info, $request_data );
				
				break;
				
			//case 'ETHIC_LEADER':
			case 'Adressblock_Federfuehrung_EK':
				
				return $this->_token_ethic_leader( $project_info );
								
				break;
				
			//case 'SALUTATION_PROJECT_CREATOR':
			case 'Anrede_Projektersteller':
					
				return $this->_token_project_creator_salutation( $project_info );
														
				break;
				
			//case 'PROJECT_CREATOR':
			case 'Projekt_Ersteller':
				
				//CakeLog::error('Tokens project creator: ' . print_r($project_info, true) );
				
				return $this->_token_project_creator( $project_info );
												
				break;

			case 'Projekt_Ersteller_mit_Titel':
				
				return $this->_token_project_creator_with_title( $project_info );
																
				break;
				
			case 'EUDRACT':
			
				return $this->_token_eudract( $project_info );

				break;
				
			case 'EUDAMED':
			
				return $this->_token_eudamed( $project_info );
												
				break;
				
			//case 'SPONSOR_SHORT':
			case 'Sponsor_kurz':
							
				return $this->_token_sponsor_short( $project_info);
								
								
				break;
				
			//case 'SPONSOR_LONG':
			case 'Sponsor_mit_Adresse':
				
				return $this->_token_sponsor_long( $project_info );
				
				break;
				
			//caregiver
			case 'verantwortlicher_Leiter':
				
				return $this->_token_caregiver( $project_info );
				
				break;
				
			//case 'DATE_2WEEKS':
			case 'Datum_plus_2Wochen':
				
				return date("d.m.Y", strtotime("+2 weeks"));
				
				break;
				
			//case 'DATE_MEETING':
			case 'Datum_der_Sitzung':
				
				return $this->_token_date_meeting( $project_info );
				
				break;
				
			//case 'ETHIC_CLIENT_PERSONS':
			case 'Pruefer_in_eigener_EK':

				return $this->_token_ethic_client_persons( $project_info );
								
				break;
			
			//case 'PROJECT_FILES':
			case 'Projekt_Dateien':
				
				return $this->_token_project_files( $project_info );
				
				break;
				
			//case 'ETHIC_COMMITTEES':
			case 'beteiligte_EK':
				
				return $this->_token_ethic_committees( $project_info );
				
				break;
				
			//case 'NONAPPROVED_PROJECT_LOCATIONS':
			case 'nicht_positiv_bewertetete_Pruefstellen':
				
				return $this->_token_nonapproved_project_locations( $project_info );
								
				break;
				
			//uploaded files
			case 'eingereichte_Dokumente':
				
				return $this->_token_uploaded_files( $project_info );
				break;
				
			//uploaded files without checksum
			case 'eingereichte_Dokumente_ohne_CS':
				
				return $this->_token_uploaded_files( $project_info, false );
				break;
				
			case 'LKP':
				
				return $this->_token_lkp_person( $project_info );
				break;
				
			case 'Antrag_Kategorie_Name_lang':
				
				return $this->_token_category_description( $project_info );
				break;

			case 'Name_der_Sitzung':
				
				return $this->_token_meeting_name( $project_info );
				break;

			case 'Datum_Sitzung':
				
				return $this->_token_meeting_date( $project_info );
				break;
				
			case 'Uhrzeit_Sitzung':
				
				return $this->_token_meeting_time( $project_info );
				break;

			case 'Tagesordnung':
				
				//if ( $html )
				//{
					return $this->_token_meeting_agenda( $project_info );
				//}
				//else
				//{
					//return null;
				//}
				
				break;
				
			case 'Zeitpunkt_besprechung':
				
				if ( $html )
				{
					return $this->_token_meeting_agenda_time( $project_info );
				}
				else
				{
					return null;
				}
				
				break;

			case 'protokoll_sitzung':
				
				if ( $html )
				{
					return $this->_token_meeting_protocol_projects( $project_info );
				}
				else
				{
					return null;
				}
				
				break;
				
			case 'PROJECT_TYPE':
				
				return $this->_token_project_type( $project_info );
				
				break;
				
			case 'votum_kommission_mitglieder':
				
				return $this->_token_meeting_protocol_project_voters( $project_info );
				
				break;
				
			case 'bundesbehoerde':
			
				return $this->_token_project_federal_ministry( $project_info );
			
				break;
				
			default:
				return null;
				break;
		}
		
	}
	
	private function _token_project_person( $project_info, $short = false )
	{
		//case 'PROJECT_PERSON':
		//case 'Adressblock_Pruefer':
		
		$ProjectPersons = new ProjectPersons();
		$user_lib = new UserLib();
		
		$persons = $ProjectPersons->find('all', array(
				'conditions' => array(
						'ProjectPersons.project_id' => $project_info['Project']['id'],
						'ProjectPersons.deleted' => 'no'
				)
		));
		
		$data = array();
		
		foreach( $persons as $person )
		{
			if ( $short )
			{
				$str = $person['ProjectPersons']['full_name'];
				
				$data[ $person['ProjectPersons']['firstname'] . " " . $person['ProjectPersons']['lastname'] ] = $str;
			}
			else
			{
				$data[ $person['ProjectPersons']['firstname'] . " " . $person['ProjectPersons']['lastname'] ] = 
				$person['ProjectPersons']['company'] .
				'\\n'.
				$person['ProjectPersons']['full_name'] .
				'\\n'.
				$person['ProjectPersons']['street'].
				'\\n'.
				$person['ProjectPersons']['zip'].
				" ".
				$person['ProjectPersons']['city'].
				'\\n'.
				(isset($person['ProjectPersons']['country']) ? $user_lib->get_country_name( $person['ProjectPersons']['country'] ) : '')
				;
				
			}
		}
		
		return $data;
		
	}
	
	private function _token_project_contact( $project_info )
	{
		//get all contacts for this project				
		$project_id = $project_info['Project']['id'];
				
		$contacts_lib = new ContactsLib();
		$project_helper = new Projects();
				
		if( !is_null( $project_info['Project']['amendment_id'] ) )
		{
			try
			{
				$project_id = $project_helper->find_parent_project($project_id);
			}
			catch (Exception $e)
			{
				$project_id = null;
			}
		}
		
		$contacts = $contacts_lib->get_contacts( $project_id );
		
		//CakeLog::error('Tokens::_token_project_contact: contacts=' . print_r($contacts, true) );
		
		$data = array();
		
		foreach( $contacts as $row )
		{
			//make sure $key is the same as CONCAT in _project_contact_salutation
			
			//$key = ( !empty($row['Contact']['name']) ? $row['Contact']['name'] : $row['Contact']['last_name'] . ', ' . $row['Contact']['first_name'] );
			//$key = ( (empty($row['Contact']['last_name']) && empty($row['Contact']['first_name']) ) ? $row['Contact']['name'] : $row['Contact']['last_name'] . ', ' . $row['Contact']['first_name'] );
			$key = $row['Contact']['first_name'] . ' ' . $row['Contact']['last_name'] . ' - ' . $row['Contact']['name'];
			
			$data[ $key ] = empty($row['Contact']['prefix']) ? '' : $row['Contact']['prefix'] . ' ';
			
			$data[ $key ] .= $row['Contact']['first_name'] . ' ';
			$data[ $key ] .= $row['Contact']['last_name'];
			
			$data[ $key ] .= empty($row['Contact']['postfix']) ? '' : ', ' . $row['Contact']['postfix'];
			
		}
		
		//CakeLog::error('Tokens::_token_project_contact: ret data=' . print_r($data, true) );
		
		return $data;
	}
	
	private function _token_project_person_salutation( $project_info, $request_data )
	{
		$persons = $this->_token_project_person($project_info, true);
		
		//CakeLog::error('Tokens::_token_project_person_salutation req_data = ' . print_r($request_data, true) . "\n persons=" . print_r($persons, true) );
		
		foreach( $persons as $person => $output )
		{
			if( $output == $request_data['Document']['token']['pruefer'] )
			{
				return $this->_project_person_salutation( $person, $project_info );
			}
		}

		return '';
	}
	
	private function _token_project_contact_salutation( $project_info, $request_data )
	{
		//salutation for the selected contact
		$contacts = $this->_token_project_contact( $project_info );
		
		//CakeLog::error('Tokens::_token_project_contact_salutation req_data = ' . print_r($request_data, true) . "\n contacts=" . print_r($contacts, true) );
		
		foreach( $contacts as $contact => $output )
		{
			if( $output == $request_data['Document']['token']['kontakt'] )
			{
				return $this->_project_contact_salutation( $contact, $project_info );
			}
		}
		
		return '';
	}
	
	private function _project_person_salutation( $person, $project_info )
	{
// 		$ProjectPersons = ClassRegistry::init( 'ProjectPersons' );
		$ProjectPersons = new ProjectPersons();

						//'ProjectPersons.full_name' => $person,
		$info = $ProjectPersons->find('first', array(
				'conditions' => array(
						'ProjectPersons.project_id' => $project_info['Project']['id'],
						'ProjectPersons.deleted' => 'no',
						'CONCAT(ProjectPersons.firstname, " ", ProjectPersons.lastname)' => $person
				)
		));
		
// 		CakeLog::error('Tokens::_project_person_salutation: person='.$person.', info='.print_r($info, true) );
		
		if ( empty($info) )
		{
			return '';
		}
		
		return $info['ProjectPersons']['salutation_letter'];
		
		//return 'testing sal=' . $person . print_r($info, true);
	}
	
	private function _project_contact_salutation( $contact, $project_info )
	{
		$Contacts = new Contact();//ClassRegistry::init( 'Contact' );
		
		$project_id = $project_info['Project']['id'];
				
		$project_helper = new Projects();
				
		if( !is_null( $project_info['Project']['amendment_id'] ) )
		{
			try
			{
				$project_id = $project_helper->find_parent_project($project_id);
			}
			catch (Exception $e)
			{
				$project_id = null;
			}
		}
		
		//concat same as $key in _token_project_contact
		$info = $Contacts->find('first', array(
			'conditions' => array(
					'Contact.project_id' => $project_id,					
					'Contact.deleted' => 'no',
					'CONCAT(Contact.first_name, " ", Contact.last_name, " - ", Contact.name)' => $contact
			)
		));
		
		if ( empty($info) )
		{
			return '';
		}
		
		return $info['Contact']['salutation_letter'];
		
		//return 'testing sal=' . $contact . print_r($info, true);
	}
	
	private function _token_lkp_person( $project_info )
	{
		$ProjectPersons = new ProjectPersons();
		
		$person = $ProjectPersons->find('first', array(
			'conditions' => array(
				'ProjectPersons.project_id' => $project_info['Project']['id'],
				'ProjectPersons.type' => 'lkp',
				'ProjectPersons.deleted' => 'no'	
			)
		));
		
		if ( empty($person) )
		{
			return '';
		}

		$data = $person['ProjectPersons']['full_name'];//$person['ProjectPersons']['title']. ' ' . $person['ProjectPersons']['firstname'] . ' ' . $person['ProjectPersons']['lastname'];
		
		return $data;
	}
		
	private function _token_ethic_leader( $project_info )
	{

		// institution <br /> street<br /> zip city
		$ProjectEthics = new ProjectEthics();
		
		$ethic = $ProjectEthics->find('first', array('conditions' => array(
				'ProjectEthics.project_id' => $project_info['Project']['id'],
				'ProjectEthics.leader' => 'yes'
		)));
		
		$data = '';
		
		if ( !empty($ethic) )
		{
			$data = $ethic['Ethics']['name'].
			'\\n'.
			$ethic['Ethics']['street'].
			'\\n'.
			$ethic['Ethics']['zip'].
			" ".
			$ethic['Ethics']['city'];
				
		}
		
		return $data;
	}
	
	private function _token_eudract( $project_info )
	{

		//get category info
		$Category = new Category();
			
		$cat = $Category->find('first', array(
				'conditions' => array(
						'Category.id' => $project_info['Project']['type'],
						'Category.deleted' => 'no'
				)
		));
			
		$data = '';
			
		if ( empty($cat) )
		{
			return $data;
		}
			
		if ( $cat['Category']['eu_reg_type'] == 'eudract' && !is_null( $project_info['Project']['eu_reg_no'] ) )
		{
			$data = $project_info['Project']['eu_reg_no'];
		}
			
		return $data;
	}
	
	private function _token_invoice_address( $invoice_info )
	{
		
		return str_ireplace('<br>', '\\n', $invoice_info['Invoice']['address_formated']);		
	}
	
	private function _token_invoice_date( $invoice_info )
	{
		return $invoice_info['Invoice']['date_formated'];
	}
	
	private function _token_invoice_title( $invoice_info )
	{
		$data = '';
		
		$data .= '<table width="100%" style="width: 100%;border-collapse:collapse; table-layout:fixed;"><tbody>';
		
		if(@unserialize($invoice_info['Invoice']['title']) !== false)
		{
			//table
		
			$invoice_title = @unserialize($invoice_info['Invoice']['title']);
		
			//pr($invoice_title);
				
				/*
				$data .= '<tr style="height: 1pt;">';
				for( $i=0; $i< 10; $i++)
				{
					$data .= '<td width="10%" style="width: 10%;height: 1pt;">&nbsp;</td>';
				}
				
				$data .= '</tr>';
				*/
				
				foreach($invoice_title as $line):
				
					$data .= '<tr>';
					
					if( count($line) == 1 ):

			  			$data .= '<td colspan="10">'. $line[0] .' &nbsp;</td>';

			  		else:
			  			$data .= '<td colspan="3">'. $line[0] .':</td>';
			  		
			  			$data .= '<td colspan="7">'. $line[1] .'</td>';		  			
			  		endif;
			  		
			  		$data .= '</tr>';
			  		
			  	endforeach;
			  	
			  	
				   	
			}
			else
			{
				$data .= '<tr><td>';
				$data .= $invoice_info['Invoice']['title'];
				$data .= '</td></tr>';
			}
			
		$data .= '</tbody></table>';
			
		//CakeLog::debug( print_r($data,true) );
		return $data;
	}
	
	private function _token_invoice_product_table( $invoice_info )
	{
		App::uses('CakeNumber', 'Utility');
		App::uses('HtmlHelper', 'View/Helper');
		App::uses('View', 'View');
		
		$HtmlHelper = new HtmlHelper(new View(null));
		
		$data = '';

		$data .= '<table width="100%" style="width: 100%;border-collapse:collapse; table-layout:fixed;"><tbody>';
		
		//<td style="width:15mm;background:transparent;"></td>
		
		$data .= '
		<tr style=" color:#FFFFFF;font-weight: bold; font-size: 10pt;">
			
			<th colspan="1" style="border:0.1mm solid #000;background-color:#7F7F7F;width: 32.5mm;text-align:left;font-size: 10pt;">'. __('ID') .'</th>
			<th colspan="3" style="border:0.1mm solid #000;background-color:#7F7F7F;width: 97mm;text-align:left;font-size: 10pt;">'. __('Product') .'</th>
		';
		
		if ( isset($invoice_info['active_invoice_factor']) && $invoice_info['active_invoice_factor'] )
		{
			$data .= '<th colspan="1" style="border:0.1mm solid #000;background-color:#7F7F7F;width: 32.5mm;text-align:center;font-size: 10pt;">'.  __('Price factor') .'</th>';
		}
		
		$data .= '
			<th colspan="1" style="border:0.1mm solid #000;background-color:#7F7F7F;width: 32.5mm;text-align:center;font-size: 10pt;">'.  __('Price') .'</th>	  		
		';
		
		
		
		$data .= '</tr>';
		
				
		$total = 0;
		$vat = 0;
		if (isset($invoice_info['Item']) && is_array($invoice_info['Item']))
		{
		  	foreach ($invoice_info['Item'] as $index=>$product)
		  	{
		  		
		  		
		  		//$row[0][0][0] = '';//, array('class' => 'test'));
		  		//$row[0][0][1] = 'style="width:'. '15mm'.';background:transparent;font-size: 10pt;"';
		  		
		  		
		
		  		$row[0][1][0] = $product['item_id'];
		  		$row[0][1][1] = 'style="border:0.1mm solid #000;font-size: 10pt;"';
		  		
		  		
		  		
		  		$row[0][2][0] = $product['name'];
		  		$row[0][2][1] = 'style="border:0.1mm solid #000;font-size: 10pt;"';
		  				  		
		  		//$row[2] = str_replace('.',',',$product['price']);
		  		$row[0][3][0] = CakeNumber::currency($product['price'], 'EUR', array('places' => 2, 'wholePosition' => 'after', 'negative' => '-'));
		  		$row[0][3][1] = 'style="border:0.1mm solid #000;text-align:right;font-size: 10pt;"';
		
		  		//$data .= $HtmlHelper->tableCells($row, null, null, false, true);
		  		
		  		$data .= '<tr>';
		  		
		  		$data .= '<td colspan="1" style="border:0.1mm solid #000;font-size: 10pt;">';
		  		$data .= $product['item_id'];
		  		$data .= '</td>';

		  		$data .= '<td colspan="3" style="border:0.1mm solid #000;font-size: 10pt;">';
		  		$data .= $product['name'];
		  		$data .= '</td>';

		  		if ( $invoice_info['active_invoice_factor'] )
		  		{
		  			$data .= '<td colspan="1" style="border:0.1mm solid #000; text-align:right; font-size: 10pt;">';
		  			$data .= str_replace('.',',',$product['price_factor']);
		  			$data .= '</td>';
		  		}
		  		
		  		$data .= '<td colspan="1" style="border:0.1mm solid #000;text-align:right;font-size: 10pt;">';
		  		$data .= CakeNumber::currency($product['price'], 'EUR', array('places' => 2, 'wholePosition' => 'after', 'negative' => '-'));
		  		$data .= '</td>';
		  		
		  		
		  		//pr($row);
		  		
		  		if ( $invoice_info['active_invoice_factor'] )
		  		{
		  			$total += $product['price'] * $product['price_factor'];
		  		}
		  		else
		  		{
		  			$total += $product['price'];
		  		}
		  		
		  		if ( isset($invoice_info['invoice_vat']) )
		  		{
		  			$vat = $invoice_info['invoice_vat'] * $total;
		  		}
		  	}
		}
		
		$data .= '<tr><td style="background:transparent;" colspan="3">&nbsp;</td></tr>';
		
		$data .= '<tr style="font-size: 10pt;">';
			
		//$data .= '<td style="width:15mm;background:transparent;"></td>';
		
		$data .= '<td colspan="1" style="border:none;">&nbsp;</td>';
		$data .= '<td colspan="3" style="border:0.1mm solid #000;">'. __('Amount') .'</td>';
		
		$data .= '<td colspan="'.(isset($invoice_info['active_invoice_factor']) && $invoice_info['active_invoice_factor'] ? 2 : 1 ).'" style="border:0.1mm solid #000;text-align:right;">';
		
		$data .= CakeNumber::currency($total, 'EUR', array('places' => 2, 'wholePosition' => 'after', 'negative' => '-'));

		$data .= '</td>';
		
				
		$data .= '</tr>';
		
		 
		if ( isset($invoice_info['invoice_vat']) && $invoice_info['invoice_vat'] != 0 && $invoice_info['Invoice']['apply_vat'] == 'yes' )
		{
			$data .= '<tr style="font-size: 10pt;">';			
			//$data .= '<td style="width:15mm;background:transparent;"></td>';
			
			$data .= '<td colspan="1" style="border:none;">&nbsp;</td>';
		  	$data .= '<td colspan="3" style="border:0.1mm solid #000;">';		  	
		  	$data .= ($invoice_info['invoice_vat']*100).'% '.__('VAT');
		  	$data .= '</td>';
		  	
		  	$data .= '<td colspan="'.(isset($invoice_info['active_invoice_factor']) && $invoice_info['active_invoice_factor'] ? 2 : 1 ).'" style="border:0.1mm solid #000;text-align:right;">';
		  	$data .= CakeNumber::currency($vat, 'EUR', array('places' => 2, 'wholePosition' => 'after', 'negative' => '-'));
		  	$data .= '</td>';
		  	
		  	$data .= '</tr>';
		  	
		  	$data .= '<tr style="font-size: 10pt;">';
		  	
			//$data .= '<td style="width:15mm;background:transparent;"></td>';
			
			$data .= '<td colspan="1" style="border:none;">&nbsp;</td>';
			
		  	$data .= '<td colspan="3" style="border:0.1mm solid #000;">';
		  	$data .= __('Total');
		  	$data .= '</td>';
		  	
		  	$data .= '<td colspan="'.(isset($invoice_info['active_invoice_factor']) && $invoice_info['active_invoice_factor'] ? 2 : 1 ).'" style="border:0.1mm solid #000;text-align:right;">';
		  	$data .= CakeNumber::currency(($total + $vat), 'EUR', array('places' => 2, 'wholePosition' => 'after', 'negative' => '-'));
		  	$data .= '</td>';

		  	$data .= '</tr>';
		 
		}
			
		$data .= '</tbody></table>';
		
		//CakeLog::debug( print_r( $data, true) );
		return $data;
	}
	
	private function _token_invoice_name( $invoice_info )
	{
		return $invoice_info['Invoice']['invoice_name'];
	}
	
	private function _token_invoice_header( $invoice_info )
	{
		return $invoice_info['Invoice']['header_formated'];
	}
	
	private function _token_invoice_footer( $invoice_info )
	{
		return $invoice_info['Invoice']['footer_formated'];
	}
	
	private function _token_eudamed( $project_info )
	{

		//get category info
		$Category = new Category();
			
		$cat = $Category->find('first', array(
				'conditions' => array(
						'Category.id' => $project_info['Project']['type'],
						'Category.deleted' => 'no'
				)
		));
			
		$data = '';
			
		if ( empty($cat) )
		{
			return $data;
		}
			
		if ( $cat['Category']['eu_reg_type'] == 'eudamed' && !is_null( $project_info['Project']['eu_reg_no'] ) )
		{
			$data = $project_info['Project']['eu_reg_no'];
		}
			
		return $data;
	}
	
	private function _token_category_description( $project_info )
	{
		$Category = new Category();
		
		$cat = $Category->find('first', array(
				'conditions' => array(
					'Category.id' => $project_info['Project']['type'],
					'Category.deleted' => 'no'
				)
		));
						
		if ( empty($cat) )
		{
			return '';
		}
				
		return $cat['Category']['description'];
	}
	
	private function _token_meeting_name( $project_info )
	{
		//get meeting name
		App::uses('Meeting', 'Model');
		
		$Meeting = new Meeting();
		
		if ( isset($project_info['ProjectMeeting']['meeting_id']) )
		{
			$name = $Meeting->field('name', array('id' => $project_info['ProjectMeeting']['meeting_id']) );
		}
		else
		{
			if ( isset($project_info['Meeting']['name']) )
			{
				$name = $project_info['Meeting']['name'];
			}
			else 
			{
				$name = '';
			}
		}
		 
		return $name;
	}
	
	private function _token_meeting_date( $project_info )
	{
		//get meeting name
		App::uses('Meeting', 'Model');
		
		$Meeting = new Meeting();
		
		if ( isset($project_info['ProjectMeeting']['meeting_id']) )
		{
			$date = $Meeting->field('date_formated', array('id' => $project_info['ProjectMeeting']['meeting_id']) );
		}
		else 
		{
			if ( isset($project_info['Meeting']['date_formated']) )
			{
				$date = $project_info['Meeting']['date_formated'];
			}
			else 
			{
				$date = '';
			}
		}
			
		return $date;
	}
	
	private function _token_meeting_time( $project_info )
	{
		return isset($project_info['ProjectMeeting']['start_time']) ?
					$project_info['ProjectMeeting']['start_time'] :
					( isset($project_info['Meeting']['time_formated']) ? $project_info['Meeting']['time_formated'] : '' );
	}
	
	private function _token_meeting_agenda( $project_info )
	{
		return isset($project_info['ProjectMeeting']['project_list']) ?
					$project_info['ProjectMeeting']['project_list'] :
					( isset($project_info['Meeting']['project_list']) ? $project_info['Meeting']['project_list'] : '');
	}
	
	private function _token_meeting_agenda_time( $project_info )
	{
		$data = '';
		
// 		$data .= '<pre>';
// 		$data .= print_r( $project_info, true);
// 		$data .= '</pre>';
		
		if ( isset($project_info['Projects']) )
		{
				
			$data .= '<table cellpadding="0" cellspacing="0" style="width: 100%;border-collapse:separate; border: 0.1mm solid #000; table-layout:fixed; "><thead>';
			
			//<td style="width:15mm;background:transparent;"></td>
			
			$data .= '
			<tr style=" color:#FFFFFF;font-weight: bold; font-size: 10pt;">
			
				<th colspan="50" style="width: 50%;border:0.1mm solid #000;background-color:#7F7F7F;text-align:left;font-size: 10pt;">'. __('Time') .'</th>
				<th colspan="50" style="width: 50%;border:0.1mm solid #000;background-color:#7F7F7F;text-align:left;font-size: 10pt;">'. __('Name') .'</th>
				
			</tr>';
			
			$data .= '</thead>';
			
			$data .= '<tbody>';
			
		
			foreach( $project_info['Projects'] as $row )
			{
				$data .= '<tr>';
				
				$data .= '<td colspan="50" style="border: 0.1mm solid #000;">';
				$data .= $row['ProjectMeeting']['start_time'] . ' - ' . $row['ProjectMeeting']['end_time'];
				$data .= '</td>';
				
				$data .= '<td colspan="50" style="border: 0.1mm solid #000;">';
				
				if ( $row['ProjectMeeting']['project_id'] == -1 )
				{
					$data .= $row['text'];
				}
				else
				{
					$data .= $row['title_formated'];
				}
				
				$data .= '</td>';
				
				$data .= '</tr>';
			}
			
		
		
			$data .= '</tbody>';
			$data .= '</table>';
			
		}
		
		return $data;
	}
	
	private function _token_meeting_protocol_projects( $project_info )
	{
		$data = '';
		
		$data .= '<table width="100%" style="width: 100%;border-collapse:separate; border: 0.1mm solid #000; table-layout:fixed; "><thead>';
		
		//<td style="width:15mm;background:transparent;"></td>
		
		$data .= '
		<tr style=" color:#FFFFFF;font-weight: bold; font-size: 10pt;">
		
			<th style="border:0.1mm solid #000;background-color:#7F7F7F;text-align:left;font-size: 10pt;">'. __('Name') .'</th>
			<th style="border:0.1mm solid #000;background-color:#7F7F7F;text-align:left;font-size: 10pt;">'. __('Number') .'</th>
		
		</tr>';
		
		$data .= '
		<tr style=" color:#FFFFFF;font-weight: bold; font-size: 10pt;">
		
			<th colspan="2" style="border:0.1mm solid #000;background-color:#7F7F7F;text-align:left;font-size: 10pt;">'. __('Official comments') .'</th>
					
		</tr>';
		
		
		$data .= '</thead>';
		
		$data .= '<tbody>';
		
		foreach( $project_info['Project'] as $row )
		{
			$data .= '<tr>';
				
			$data .= '<td style="border: 0.1mm solid #000;">';
			if ( $row['ProjectMeeting']['project_id'] == -1 )
			{
				$data .= $row['text'];
			}
			else
			{
				$data .= $row['title_formated'];
			}
			$data .= '</td>';
				
			$data .= '<td style="border: 0.1mm solid #000;">';
			
			if ( $row['ProjectMeeting']['project_id'] == -1 )
			{
				$data .= '';
			}
			else
			{		
				$data .= $row['project_number_formated'];
			}			
			
			$data .= '</td>';
				
			$data .= '</tr>';
			
			$data .= '<tr>';
			
			$data .= '<td colspan="2" style="border: 0.1mm solid #000;">';
			
			if ( isset($row['MeetingProtocol']['official_comments']) )
			{
				$data .= nl2br( $row['MeetingProtocol']['official_comments'] );
			}
			
// 			$data .= '<pre>';
// 			$data .= print_r( $row, true);
// 			$data .= '</pre>';
			
			$data .= '</td>';
			
			$data .= '</tr>';
		}
		
		$data .= '</tbody>';
		$data .= '</table>';
		
		return $data;
		
	}
	
	private function _token_meeting_protocol_project_voters( $project_info )
	{
		//depends on project, if project is in 1 meeting = string else = array
		$project_id = $project_info['Project']['id'];
				
		$meeting_lib = new MeetingLib();
						
		$data = $meeting_lib->get_voters_project_meetings($project_id);
				
		return $data;
	}
	
	private function _token_sponsor_short( $project_info )
	{
		//depends on project, if project has only 1 sponsor = string else =array
		/*
		$Partner = new Partner();
		
		$Partner->bindModel(array(
				'hasOne' => array(
						'SponsorRepresentative' => array(
								'className' => 'SponsorRepresentative'
						)
				)
		), true);
		
		$sponsors = $Partner->find('all', array('conditions' => array(
				'Partner.project_id' => $project_info['Project']['id'],
				'Partner.type' => 'sponsor',
				'Partner.deleted' => 'no'
		)));
		*/
		
		$project_id = $project_info['Project']['id'];
		
		
		$contacts_lib = new ContactsLib();
		$project_helper = new Projects();
		
		if( !is_null( $project_info['Project']['amendment_id'] ) )
		{
			try 
			{
				$project_id = $project_helper->find_parent_project($project_id);				
			} 
			catch (Exception $e) 
			{
				$project_id = null;
			}
		}
		
		$sponsors = $contacts_lib->get_project_sponsors( $project_id );
		
		$data = '';
		
		//pr( $sponsors );
		
		if ( count($sponsors) > 1 )
		{
			$data = array();
				
			foreach( $sponsors as $sponsor )
			{
				$data[ $sponsor['Contact']['name'] ] = $sponsor['Contact']['name'];
				 
				/*
				if ( $sponsor['SponsorRepresentative']['contact'] == 'yes' )
				{
					$data[ $sponsor['Partner']['name'] ] = $sponsor['Partner']['name'];
				}
				else
				{
					$data[ $sponsor['Partner']['name'] ] = $sponsor['Partner']['name'];
				}
				*/
				
			}
		}
		elseif( !empty($sponsors) )
		{
			$data = $sponsors[0]['Contact']['name'];
			
			/*
			if ( $sponsors[0]['SponsorRepresentative']['contact'] == 'yes' )
			{
				$data = $sponsors[0]['Partner']['name'];
			}
			else
			{
				$data = $sponsors[0]['Partner']['name'];
			}
			*/
		}
		
		return $data;
		
	}
	
	private function _token_sponsor_long( $project_info )
	{
		//case 'SPONSOR_LONG':
		//case 'Sponsor_mit_Adresse':
				
		/*
		$Partner = new Partner();
			
		$Partner->bindModel(array(
				'hasOne' => array(
						'SponsorRepresentative' => array(
								'className' => 'SponsorRepresentative'
						)
				)
		), true);
		
		$sponsors = $Partner->find('all', array('conditions' => array(
				'Partner.project_id' => $project_info['Project']['id'],
				'Partner.type' => 'sponsor',
				'Partner.deleted' => 'no'
		)));
		*/

		$project_id = $project_info['Project']['id'];
		
		
		$contacts_lib = new ContactsLib();
		$project_helper = new Projects();
		$user_lib = new UserLib();
		
		
		if( !is_null( $project_info['Project']['amendment_id'] ) )
		{
			try
			{
				$project_id = $project_helper->find_parent_project($project_id);
			}
			catch (Exception $e)
			{
				$project_id = null;
			}
		}
		
		$sponsors = $contacts_lib->get_project_sponsors( $project_id );
		
// 		pr( $sponsors );

		$representative = $contacts_lib->get_contact_type($project_id, null, 'representant');
		
// 		pr( $representative );
		
		if ( !empty($representative) )
		{
			$rep_data = ( !empty($representative['Contact']['name']) ? $representative['Contact']['name'] .', ' : "" ).
		
					( !empty($representative['Contact']['department']) ? $representative['Contact']['department'] .', ' : "" ).
		
					$representative['Contact']['first_name'] .
					" ".
					$representative['Contact']['last_name'].
					", ".
					$representative['Contact']['street'].
					", ".
					$representative['Contact']['zip'].
					" ".
					$representative['Contact']['city'].

					(isset($representative['Contact']['country']) ? ', ' . $user_lib->get_country_name( $representative['Contact']['country'] ) : '')
					;
		}
		
		$data = '';
			
		if ( count($sponsors) > 1 )
		{
			$data = array();
		
			foreach( $sponsors as $sponsor )
			{
				/*
				if ( $sponsor['SponsorRepresentative']['contact'] == 'yes' )
				{
					$data[ $sponsor['Partner']['name'] ] = ( !empty($sponsor['SponsorRepresentative']['company']) ? $sponsor['SponsorRepresentative']['company'] .', ' : "" ).
		
					( !empty($sponsor['SponsorRepresentative']['department']) ? $sponsor['SponsorRepresentative']['department'] .', ' : "" ).
		
					$sponsor['SponsorRepresentative']['firstname'] .
					" ".
					$sponsor['SponsorRepresentative']['lastname'].
					", ".
					$sponsor['SponsorRepresentative']['street'].
					", ".
					$sponsor['SponsorRepresentative']['zip'].
					" ".
					$sponsor['SponsorRepresentative']['city']
					;
				}
				else*/
				if( !empty($representative) )
				{
					$data[ $sponsor['Contact']['name'] ] = $rep_data;
				}
				else
				{
					$data[ $sponsor['Contact']['name'] ] = $sponsor['Contact']['name'].
					", ".
					$sponsor['Contact']['street'].
					", ".
					$sponsor['Contact']['zip'].
					" ".
					$sponsor['Contact']['city'].
					
					(isset($sponsor['Contact']['country']) ? ', ' . $user_lib->get_country_name( $sponsor['Contact']['country'] ) : '')
					;
				}
		
			}
		}
		elseif( !empty($sponsors) )
		{
			/*
			if ( $sponsors[0]['SponsorRepresentative']['contact'] == 'yes' )
			{
				$data = ( !empty($sponsors[0]['SponsorRepresentative']['company']) ? $sponsors[0]['SponsorRepresentative']['company'] .', ' : "" ).
		
				( !empty($sponsors[0]['SponsorRepresentative']['department']) ? $sponsors[0]['SponsorRepresentative']['department'] .', ' : "" ).
		
				$sponsors[0]['SponsorRepresentative']['firstname'] .
				" ".
				$sponsors[0]['SponsorRepresentative']['lastname'].
				", ".
				$sponsors[0]['SponsorRepresentative']['street'].
				", ".
				$sponsors[0]['SponsorRepresentative']['zip'].
				" ".
				$sponsors[0]['SponsorRepresentative']['city']
				;
			}
			*/
			if( !empty($representative) )
			{
				$data = $rep_data;
			}
			else
			{
				$data = $sponsors[0]['Contact']['name'].
				", ".
				$sponsors[0]['Contact']['street'].
				", ".
				$sponsors[0]['Contact']['zip'].
				" ".
				$sponsors[0]['Contact']['city'].

				(isset($sponsors[0]['Contact']['country']) ? ', ' . $user_lib->get_country_name( $sponsors[0]['Contact']['country'] ) : '')
				;
			}
		}
		
		return $data;
		
	}
	
	private function _token_caregiver( $project_info )
	{
		$project_id = $project_info['Project']['id'];
		
		
		$contacts_lib = new ContactsLib();
		$project_helper = new Projects();
		$user_lib = new UserLib();
		
		
		if( !is_null( $project_info['Project']['amendment_id'] ) )
		{
			try
			{
				$project_id = $project_helper->find_parent_project($project_id);
			}
			catch (Exception $e)
			{
				$project_id = null;
			}
		}
		
		$caregivers = $contacts_lib->get_project_caregivers( $project_id );
		
		$data = '';
			
		if ( count($caregivers) > 1 )
		{
			$data = array();
		
			foreach( $caregivers as $row )
			{				
				$data[ $row['Contact']['first_name'] . ' ' . $row['Contact']['last_name'] ] = $row['Contact']['first_name']. ' ' . $row['Contact']['last_name'].
					", ".
					$row['Contact']['street'].
					", ".
					$row['Contact']['zip'].
					" ".
					$row['Contact']['city'];
					//.
						
					//(isset($sponsor['Contact']['country']) ? ', ' . $user_lib->get_country_name( $sponsor['Contact']['country'] ) : '')
					//;
					
			}
		}
		elseif( !empty($caregivers) )
		{		
				$data = $caregivers[0]['Contact']['first_name']. ' ' . $caregivers[0]['Contact']['last_name'].
				", ".
				$caregivers[0]['Contact']['street'].
				", ".
				$caregivers[0]['Contact']['zip'].
				" ".
				$caregivers[0]['Contact']['city'];//.
		
				//(isset($sponsors[0]['Contact']['country']) ? ', ' . $user_lib->get_country_name( $sponsors[0]['Contact']['country'] ) : '')
				//;
		
		}
		
		return $data;
	}
	
	private function _token_date_meeting( $project_info )
	{

		$ProjectMeeting = new ProjectMeeting();
		
		$meeting = $ProjectMeeting->find('first', array(
				'conditions' => array(
						'ProjectMeeting.project_id' => $project_info['Project']['id'],
						'Meeting.deleted' => 'no'
				),
				'order' => array('Meeting.date' => 'DESC')
		));
		
		$data = '';
		
		if ( !empty($meeting) )
		{
			$data = $meeting['Meeting']['date_formated'];
		}
		
		return $data;
		
	}
	
	private function _token_ethic_client_persons( $project_info )
	{
		//persons from client ethics
		
		//find client ethic
		$Ethics = new Ethics();
		
		$ethic = $Ethics->find('first', array(
				'conditions' => array(
						'Ethics.client' => 'yes',
						'Ethics.deleted' => 'no'
				),
				'order' => array('Ethics.created' => 'DESC')
		));
		
		$data = '';
		
		//pr( $ethic );
		
		if ( empty($ethic) )
		{
			return $data;
		}
		
		//find persons for this ethic
		$ProjectPersons = new ProjectPersons();
		
		$persons = $ProjectPersons->find('all', array(
				'conditions' => array(
						'ProjectPersons.project_id' => $project_info['Project']['id'],
						'ProjectPersons.ethics_id' => $ethic['Ethics']['id'],
						'ProjectPersons.deleted' => 'no'
				),
				'order' => array('ProjectPersons.lastname' => 'ASC')
		));
		
		//pr( $persons );
		
		if ( !empty($persons) )
		{
			foreach( $persons as $person )
			{
				$data .= $person['ProjectPersons']['firstname'].
				" ".
				$person['ProjectPersons']['lastname'].
				", ";
			}
				
			$data = rtrim( $data, ", " );
		}
		
		return $data;
		
	}
	
	private function _token_project_files( $project_info )
	{

		$Files = new Files();//ClassRegistry::init('Files');
		$ProjectPersons = new ProjectPersons();//ClassRegistry::init('ProjectPersons');
		$Location = new Location();//ClassRegistry::init('Location');
		
		$data = '';
		
		
		$Files->bindModel(
				array(
						'hasAndBelongsToMany' => array(
								'Tag' => array(
										'className' => 'Tag',
		
								)
						),
				), false);
		
		$ProjectPersons->bindModel(array(
				'hasMany' => array(
						'Files' => array(
								'className' => 'Files',
								'foreignKey' => 'template_type',
								'conditions' => array(
										'Files.deleted' => 'no',
										'Files.type' => 'person',
										'Files.project_id' => $project_info['Project']['id']
								)
						)
				)
		), false);
		
		$Location->bindModel(array(
				'hasMany' => array(
						'ProjectPersons' => array(
								'className' => 'ProjectPersons',
								'conditions' => array(
										'ProjectPersons.deleted' => 'no'
								)
						),
						'Files' => array(
								'className' => 'Files',
								'foreignKey' => 'template_type',
								'conditions' => array(
										'Files.deleted' => 'no',
										'Files.type' => 'location',
		
								),
								'order' => array('Files.modified' => 'DESC')
						)
				)
		), false);
		
		$location_green_list = $Location->find('all', array(
				'conditions' => array(
						'Location.project_id' => $project_info['Project']['id'],
						'Location.deleted' => 'no',
						'Location.status' => 'green'
				),
					
				'recursive' => 9,
				'order' => array("FIELD(status, 'green', 'yellow', 'red')")
		));
		
		//pr($location_green_list);
		
		
		
		$location_other_list = $Location->find('all', array(
				'conditions' => array(
						'Location.project_id' => $project_info['Project']['id'],
						'Location.deleted' => 'no',
						'Location.status NOT ' => 'green'
				),
				'recursive' => 9,
				'order' => array("FIELD(status, 'green', 'yellow', 'red')")
		));
		
		//$data .= print_r( $location_other_list, true);
		
		$join1 = array(
				'table' => 'files_tags',
				'alias' => 'FilesTag',
				'type' => 'LEFT',
				'conditions' => array(
						'Files.id = FilesTag.file_id'
				)
		);
		
		$join2 = array(
				'table' => 'tags',
				'alias' => 'Tag',
				'type' => 'LEFT',
				'conditions' => array(
						'Tag.id = FilesTag.tag_id'
				)
		);
		
		$Files->unbindModel(array('hasAndBelongsToMany' => array('Tag')), false);
		
		$file_list = $Files->find('all', array(
				'conditions' => array(
						'Files.type NOT' => array('approved', 'location', 'person', 'letter'),
						'Files.project_id' => $project_info['Project']['id'],
						'Files.deleted' => 'no'
				),
				'joins' => array($join1, $join2),
				'fields' => array('Files.*', 'Tag.name', 'Tag.order'),
				'order' => array('Files.modified' => 'DESC')
		));
		
		
		
		if ( !empty($file_list) )
		{
				
			foreach( $file_list as $file )
			{
		
				if ( !empty($file['Tag']['name']) )
				{
					$data .= $file['Tag']['name'];
					$data .= ' - ';
				}
		
				$data .= $file['Files']['name'];
				$data .= ' (' . __('added');
				$data .= ' ' . $file['Files']['modified_formated'];
				$data .= ')';
					
				$data .= '\\n';
		
			}
			$data .= '\\n';
		}
		
			
		$location_list = am($location_green_list, $location_other_list);
		
		if( !empty( $location_list ) )
		{
			foreach( $location_list as $location )
			{
				$location_str = '';
				$show_location_str = false;
		
				$location_str .= "  ";
				$location_str .= $location['Location']['name'];
				$location_str .= '\\n';
					
				if(!empty( $location['Files'] ))
				{
					$show_location_str = true;
						
					foreach( $location['Files'] as $file )
					{
						$location_str .= "  ";
		
						if( !empty($file['Tag']) )
						{
								
							$location_str .= $file['Tag'][0]['name'];
							$location_str .= ' - ';
						}
		
						$location_str .= $file['name'];
						$location_str .= ' (' . __('added');
						$location_str .= ' ' . $file['modified_formated'];
						$location_str .= ')';
		
						$location_str .= '\\n';
					}
				}
					
				if( !empty( $location['ProjectPersons'] ) )
				{
					foreach( $location['ProjectPersons'] as $person )
					{
						$show_person_str = false;
		
						$person_str = '';
		
						$person_str .= "    ";
						$person_str .= trim($person['full_name']);
		
						switch( $person['type'] )
						{
							case 'examiner':
		
								$person_str .= ' (P)';
								break;
		
							case 'deputy':
		
								$person_str .= ' (Stellv.)';
								break;
		
							case 'lkp':
		
								$person_str .= ' (LKP)';
								break;
									
						}
		
						$person_str .= '\\n';
		
						if( !empty($person['Files']) )
						{
							$show_location_str = true;
							$show_person_str = true;
								
							foreach( $person['Files'] as $file )
							{
								$person_str .= "    ";
		
								if( !empty($file['Tag']) )
								{
									$person_str .= $file['Tag'][0]['name'];
									$person_str .= ' - ';
								}
		
								$person_str .= $file['name'];
								$person_str .= ' (' . __('added');
								$person_str .= ' ' . $file['modified_formated'];
								$person_str .= ')';
		
								$person_str .= '\\n';
							}
						}
		
		
						if ( $show_person_str )
						{
							$location_str .= $person_str;
						}
					}
				}
					
				if( $show_location_str )
				{
					$data .= $location_str;
				}
			}
		}
		
		return $data;
		
	}
	
	private function _token_ethic_committees( $project_info )
	{

		$ProjectEthics = new ProjectEthics();
		
		
		$ethics_list = $ProjectEthics->find('all', array(
				'conditions' => array(
						'project_id' => $project_info['Project']['id'],
						'Ethics.deleted' => 'no'
				),
				'order' => array('Ethics.client' => 'DESC')
		));
		
		$data = '';
		
		if ( !empty($ethics_list) )
		{
			foreach( $ethics_list as $ethics )
			{
				$data .= $ethics['Ethics']['name'];
				$data .= '\\n';
		
				if ( !empty($ethics['Ethics']['street']) )
				{
					$data .= $ethics['Ethics']['street'];
					$data .= '\\n';
				}
		
				if ( !empty($ethics['Ethics']['zip']) )
				{
					$data .= $ethics['Ethics']['zip'].' ';
				}
		
				if ( !empty($ethics['Ethics']['city']) )
				{
					$data .= $ethics['Ethics']['city'];
				}
		
				if (!empty($ethics['Ethics']['zip']) || !empty($ethics['Ethics']['city']))
				{
					$data .= '\\n';
				}
		
				if (!empty( $ethics['Ethics']['phone']))
				{
					$data .= __('Tel.');
					$data .= ': ';
					$data .= $ethics['Ethics']['phone'];
					$data .= '\\n';
				}
		
				if (!empty( $ethics['Ethics']['fax'] ))
				{
					$data .= __('Fax');
					$data .= ': ';
					$data .= $ethics['Ethics']['fax'];
				}
		
				$data .= '\\n\\n';
			}
		}
		
		return $data;
		
	}
	
	private function _token_nonapproved_project_locations( $project_info )
	{				
		$Location = new Location();//ClassRegistry::init('Location');
		
		$data = '';
		
		$Location->bindModel(array(
				'hasMany' => array(
						'ProjectPersons' => array(
								'className' => 'ProjectPersons',
								'conditions' => array(
										'ProjectPersons.deleted' => 'no'
								)
						)
				)
		), false);
		
		$location_other_list = $Location->find('all', array(
				'conditions' => array(
						'Location.project_id' => $project_info['Project']['id'],
						'Location.deleted' => 'no',
						'Location.status NOT ' => 'green'
				),
				'recursive' => 2,
				'order' => array("FIELD(status, 'green', 'yellow', 'red')")
		));
		
		//$data .= print_r( $location_other_list, true);
		
		if ( !empty($location_other_list) )
		{
			foreach( $location_other_list as $location )
			{
				$data .= $location['Location']['name'];
				$data .= '\\n';
		
				if(!empty( $location['ProjectPersons'] ))
				{
					foreach( $location['ProjectPersons'] as $person )
					{
						$data .= trim($person['full_name']);
		
						switch( $person['type'] )
						{
							case 'examiner':
								$data .= ' (P)';
								break;
							case 'deputy':
								$data .= ' (Stellv.)';
								break;
		
							case 'lkp':
									
								$data .= ' (LKP)';
								break;
									
						}
		
						$data .= '\\n';
		
						if ( strlen(trim( $person['official_comment'] )) )
						{
							$data .= trim( $person['official_comment'] );
							$data .= '\\n';
						}
		
						//echo '</div>';
		
		
						//echo '<br>';
					}
				}
					
				if ( in_array($location['Location']['status'], array('yellow', 'red')) && strlen(trim($location['Location']['official_comment'])) )
				{
					$data .= '\\n';
					$data .= trim($location['Location']['official_comment']);
					$data .= '\\n\\n';
				}
				else
				{
					$data .= '\\n';
				}
		
			}
		
			//$data .= "\n";
		}
		
		return trim($data);
		
	}
	
	private function _token_user_fax()
	{
		//fax number from client ethic commission
		
		$Ethics = new Ethics();
		
		$ethic = $Ethics->find('first', array(
				'conditions' => array(
						'Ethics.client' => 'yes',
						'Ethics.deleted' => 'no'
				),
				'order' => array('Ethics.created' => 'DESC')
		));
		
		$data = '';
		
		if ( !empty($ethic) )
		{
			$data = $ethic['Ethics']['fax'];
		}
		
		return $data;
		
	}
	
	private function _token_project_number( $project_info )
	{
		$project_internal_id = '';

		if (!is_null($project_info['Project']['amendment_id']))
		{
			$project_type = __('AMENDMENT');
			$project_internal_id = $project_info['Project']['amendment_formated'];
		}
		else
		{
			$project_type = __('PROJECT');
			$project_internal_id = $project_info['Project']['project_id_formated'];
		}
		
		return $project_internal_id;
		
	}
	
	private function _token_project_title( $project_info )
	{
		return $project_info['Project']['title_formated'];
	}
	
	private function _token_project_protocol( $project_info )
	{

		if ( is_null($project_info['Project']['protocol']) )
		{
			$project_info['Project']['protocol'] = '';
		}
		
		return $project_info['Project']['protocol'];
		
	}
	
	private function _token_project_creator( $project_info, $force_person = false )
	{
		if ( isset($project_info['Project']['company']) )
		{
			$project_info['company'] = $project_info['Project']['company'];
		}	
			
		if ( $force_person || $project_info['company'] == 'no' )
		{
			//this is used also for ask_for_information
			return isset($project_info['User']['full_name']) ? $project_info['User']['full_name'] : '';
		}
		else 
		{
			return $this->_token_project_creator_company($project_info); 
				
		}				
	}
	
	private function _token_project_creator_with_title( $project_info )
	{
		return isset($project_info['User']['applicant_name']) ? $project_info['User']['applicant_name'] : '';		
	}
	
	private function _token_project_creator_company( $project_info )
	{
		return isset($project_info['User']['company']) ? $project_info['User']['company'] : '';
	}
	
	private function _token_project_creator_salutation( $project_info )
	{

		//pr( $project_info['User'] );
			
		if( isset($project_info['User']['salutation_letter']) && !empty($project_info['User']['salutation_letter']) )
		{
			return $project_info['User']['salutation_letter'];
		}
		else
		{
			return '';
		}
		
		/*
		 if ( isset($project_info['User']['title']) )
		 {
		$user_title = trim($project_info['User']['title']);
		}
		
		if ( !isset($project_info['User']['title']) || empty($user_title) )
		{
		return $this->_salutation_list;
		}
		else
		{
		return $this->_salutation_list[ $user_title ];
		}
		*/
		
		//return 'Sehr geehrte/r';
	}
	
	
	private function _token_uploaded_files( $project_info, $with_checksum = true )
	{
		$data = '';
			
		//MUST be left with classregistry::init, for virtualfields
		$Files = ClassRegistry::init( 'Files' );
				
		$Files->virtualFields['created_formated'] = 'DATE_FORMAT(Files.created, "%d.%m.%Y")';
				
		
		//get files from this project uploaded by project creator

		$join1 = array(
				'table' => 'files_tags',
				'alias' => 'FilesTag',
				'type' => 'LEFT',
				'conditions' => array(
						'Files.id = FilesTag.file_id'
				)
		);
		
		$join2 = array(
				'table' => 'tags',
				'alias' => 'Tag',
				'type' => 'LEFT',
				'conditions' => array(
						'Tag.id = FilesTag.tag_id'
				)
		);
		
		$Files->virtualFields['tag_name'] = 'Tag.name';
		
		$file_list = $Files->find('all', array(
			'conditions' => array(
				'Files.project_id' => $project_info['Project']['id'],
				'AND' => array(
					'OR' => array(
						array('Files.type' => 'project', 'Files.user_id' => $project_info['Project']['user_id']),
						
						array(
								'Files.user_id' => $project_info['Project']['user_id'],
								'Files.type' => array(
										'location',
										'person'
								)
						),
						
						array('Files.type' => array(
									'reduction',
									'message',
									'ask',
									'reply',
									'ask2',
									'reply2'
						))
					)
				),
				'OR' => array(
						'Files.public' => 'yes',
						'Files.user_id' => $project_info['Project']['user_id']
				),
				'Files.deleted' => 'no'
				
			),
			'joins' => array( $join1, $join2 ),
			'order' => array('Files.name' => 'ASC')
		));
		
		//CakeLog::debug( 'Tokens::_token_uploaded_files file_list=' . print_r($file_list, true) );
		
		foreach( $file_list as $row )
		{
			$data .= empty($row['Files']['tag_name']) ? __('none') : $row['Files']['tag_name'];
			$data .= ', ';
			$data .= $row['Files']['name'];
			$data .= ', ';
			$data .= $row['Files']['created_formated'];
			
			if ( $with_checksum )
			{
				$data .= ', ';
				$data .= $row['Files']['sha1sum'];
			}
			
			$data .= '\\n';
		}
						
		return $data;
	}
	
	public function isMultiple( $token, $project_info, $user )
	{
		if ( !array_key_exists($token, $this->_token_list) )
		{
			return null;
		}
		
		if ( $this->_token_list[ $token ]['type'] == 'multiple' )
		{
			return true;
		}
		elseif ( $this->_token_list[ $token ]['type'] == 'variable' )
		{
			//check if for this project the token should be "multiple" or not
			switch( $token )
			{
				//case 'SALUTATION_ADMIN_USER':
				case 'eigene_Anrede':

					return false;
					
					/*
					if ( isset($user['title']) )
					{
						$user_title = trim($user['title']);
					}
					
					if ( !isset($user['title']) || empty( $user_title ) )
					{
						return true;
					}
					else
					{												
						return false;
					}
					*/
					
					break;
					
				//case 'SALUTATION_PROJECT_CREATOR':
				case 'Anrede_Projektersteller':
					
					/*
					if ( isset($project_info['User']['title']) )
					{
						$user_title = trim($project_info['User']['title']);
					}
					
					if ( !isset($project_info['User']['title']) || empty( $user_title ) )
					{
						return true;
					}
					else
					{												
						return false;
					}
					*/
					
					return false;
					
					break;
					
				//case 'SPONSOR_SHORT':
				//case 'SPONSOR_LONG':
				case 'Sponsor_kurz':
				case 'Sponsor_mit_Adresse':

					/*
					$Partner = new Partner();
					
					$sponsors = $Partner->find('count', array('conditions' => array(
						'Partner.project_id' => $project_info['Project']['id'],
						'Partner.type' => 'sponsor',
						'Partner.deleted' => 'no'
					)));
					*/

					
					$project_id = $project_info['Project']['id'];
					
					
					$contacts_lib = new ContactsLib();
					$project_helper = new Projects();
					
										
					if( !is_null( $project_info['Project']['amendment_id'] ) )
					{
						try
						{
							$project_id = $project_helper->find_parent_project($project_id);
						}
						catch (Exception $e)
						{
							$project_id = null;
						}
					}
					
					$sponsors = $contacts_lib->get_project_sponsors( $project_id );
					
					$sponsors = count( $sponsors );
					
					
					if ( $sponsors > 1 )
					{
						return true;
					}
					else
					{						
						return false;
					}
					
					break;
					
				case 'verantwortlicher_Leiter':
					
					$project_id = $project_info['Project']['id'];
					
					
					$contacts_lib = new ContactsLib();
					$project_helper = new Projects();
					
										
					if( !is_null( $project_info['Project']['amendment_id'] ) )
					{
						try
						{
							$project_id = $project_helper->find_parent_project( $project_id );
						}
						catch (Exception $e)
						{
							$project_id = null;
						}
					}
					
					$caregivers = $contacts_lib->get_project_caregivers( $project_id );
					
					$caregivers = count( $caregivers );
					
					
					if ( $caregivers > 1 )
					{
						return true;
					}
					else
					{						
						return false;
					}
					
					break;
					
				case 'votum_kommission_mitglieder':
					
					$project_id = $project_info['Project']['id'];
					
					$meeting_lib = new MeetingLib();
					
					//count meetings with this project assigned					
					$count = $meeting_lib->count_project_meetings($project_id);
					
					if ( $count > 1 )
					{
						return true;
					}
					else 
					{
						return false;
					}
					
					break;
					
				default:
					return null;
					break;
			}
			
		}
		else
		{			
			return false;
		}
	}
	
	public function invoice_title($title_string, $project_info)
	{
		//pr($title_string);
		
		$result = array();
		
		//split lines
		$lines = split("\r\n", trim($title_string));
		
		foreach ($lines as $index=>$line)
		{
			//pr($line);

			//split column on  ": $"
			if (preg_match('/(.*):[[:space:]]+(\$.*)/i', $line, $matches))
			{			
				//pr($matches);
				$result[$index][] = $matches[1];
								
				$result[$index][] = $this->_invoice_title_token($matches[2], $project_info);
				
			}
			else
			{
				//string
				if (trim($line) == '')
				{
					$result[$index][] = '&nbsp;';
				}
				else
				{
					$result[$index][] = $this->_invoice_title_token($line, $project_info);
				}
			}
		}
		
		//pr($result);
		
		return $result;
	}
	
	public function ask_for_info_text( $string, $project_info, $user )
	{
		$token = 'Projekt_Ersteller';
		
// 		$token_value = $this->getTokenValue($token, $project_info, $user);
		$token_value = $this->_token_project_creator($project_info, true);
		
		//$title_string = str_ireplace('$'.$token.'$', $project_info['User']['full_name'], $string);
		$title_string = str_ireplace('$'.$token.'$', $token_value, $string);
		
		return $title_string;
	}
	
	public function ek_email( $string, $project_info, $user )
	{
		$token = 'Antragsnummer';
						
		$token_value = $this->_token_project_number($project_info);				
		$title_string = str_ireplace( '$'.$token.'$', $token_value, $string );
		
		$token = 'Projekt_Ersteller_mit_Titel';
		
		$token_value = $this->_token_project_creator_with_title($project_info);
		$title_string = str_ireplace( '$'.$token.'$', $token_value, $title_string );
		
		return $title_string;
	}
	
	private function _token_project_type( $project_info )
	{
		if (!is_null($project_info['Project']['amendment_id']))
		{
			$project_type = __('AMENDMENT');
			//$project_internal_id = $project_info['Project']['amendment_formated'];
								
		}
		else
		{
			$project_type = __('PROJECT');
			//$project_internal_id = $project_info['Project']['project_id_formated'];
		
		}
		
		return $project_type;		
	}
	
	private function _token_project_federal_ministry( $project_info )
	{
		$project_lib = new Projects();
		
		$project_id = $project_info['Project']['id'];
		
		//get parent project
		if( !is_null( $project_info['Project']['amendment_id'] ) )
		{
			try
			{
				$project_id = $project_lib->find_parent_project($project_id);
			}
			catch (Exception $e)
			{
				$project_id = null;
			}
		}
						
		$info = $project_lib->get_project_federal_authority( $project_id, null);
		
		switch( $info['ProjectFederalAuthority']['option'] )
		{
			case 'BFArM':
				
				$data = 'Bundesinstitut für Arzneimittel und Medizinprodukte (BfArM)';
				$data .= '\\n';
				$data .= 'Kurt-Georg-Kiesinger-Allee 3';
				$data .= '\\n';
				$data .= '53175 Bonn';
				
				break;
				
			case 'PEI':
				
				$data = 'Paul-Ehrlich-Institut';
				$data .= '\\n';
				$data .= 'Bundesinstitut für Impfstoffe und biomedizinische Arzneimittel';
				$data .= '\\n';
				$data .= 'Paul-Ehrlich-Straße 51-59';
				$data .= '\\n';
				$data .= 'D- 63225 Langen';
				
				break;
				
			default:
				$data = '';
				break;
		}
		
		//$data .= '\\n'.print_r( $info, true );
		
		return $data;
	}
	
	private function _invoice_title_token($title_string, $project_info)
	{

		/*
		if (!is_null($project_info['Project']['amendment_id']))
		{
			$project_type = __('AMENDMENT');
			$project_internal_id = $project_info['Project']['amendment_formated'];
			
			
		}
		else
		{
			$project_type = __('PROJECT');
			$project_internal_id = $project_info['Project']['project_id_formated'];
		
		}
		*/
		
		$project_type = $this->_token_project_type( $project_info );
		$project_internal_id = $this->_token_project_number( $project_info );
		$project_title = $this->_token_project_title( $project_info );
		$project_protocol = $this->_token_project_protocol( $project_info );
		
		if ( $project_info['Project']['company'] == 'yes' )
		{
			$project_creator = $this->_token_project_creator_company( $project_info );			
		}
		else
		{
			$project_creator = $this->_token_project_creator_with_title( $project_info );
		}
		
		
		$title_string = str_ireplace('$PROJECT_TYPE$', $project_type, $title_string);
		$title_string = str_ireplace('$PROJECT_NUMBER$', $project_internal_id, $title_string);
		$title_string = str_ireplace('$EUREGNUMBER$', 
				(is_null($project_info['Project']['eu_reg_no']) ? '' : $project_info['Project']['eu_reg_no']), 
				$title_string);
		
		//$title_string = str_ireplace('$PROJECT_TITLE$', $project_info['Project']['title_formated'], $title_string);
		$title_string = str_ireplace('$PROJECT_TITLE$', $project_title, $title_string);
		
		//$title_string = str_ireplace('$PROJECT_CREATOR$', $project_info['User']['applicant_name'], $title_string);
		$title_string = str_ireplace('$PROJECT_CREATOR$', $project_creator, $title_string);
		
		//$title_string = str_ireplace('$PROTOCOL$', $project_info['Project']['protocol'], $title_string);
		$title_string = str_ireplace('$PROTOCOL$', $project_protocol, $title_string);
		
		//find sponsor
		if(preg_match_all ( '/\$SPONSOR_([0-9]+)\$/i', $title_string, $matches ))
		{		
			//pr($matches);

					
			//get sponsor list
			if (is_null($project_info['Project']['amendment_id']))
			{
				$project_id = $project_info['Project']['id'];
			}
			else
			{
				//parent project id
				
				/*
				$project = new Project();
				
				$parent_project_info = $project->find('first', array(
						'conditions' => array(
								'Project.deleted' => 'no', 
								'Project.project_id' => $project_info['Project']['project_id'], 
								'Project.amendment_id' => null, 
								'Project.user_id' => $project_info['Project']['user_id']
						)
				));
				
				$project_id = $parent_project_info['Project']['id'];
				*/
				
				$project_helper = new Projects();
				
				try 
				{
					$project_id = $project_helper->find_parent_project($project_info['Project']['id']);					
				} 
				catch (Exception $e) 
				{
					$project_id = null;
				}
			}
			
			$contacts_lib = new ContactsLib();
			
			$sponsor_list = $contacts_lib->get_project_sponsors_list( $project_id );
			
			//pr($sponsor_list);
			
			foreach($matches[1] as $sponsor_number)
			{
				//pr($sponsor_number);
				
				$title_string = str_ireplace('$SPONSOR_'.$sponsor_number.'$', 
						(isset($sponsor_list[$sponsor_number]) ? $sponsor_list[$sponsor_number] : ''), 
						$title_string
				);
			}
		}
		
		return $title_string;	
	}
}