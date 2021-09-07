<?php
// ISPC-2612 Ancuta
return array(
    'Connections lists'=>"Listen Vernetzungen",// Title of page
    "connection_list_parent" =>"Hauptmandant",// table Header
    "connection_type" =>"",// table Header
    "menu_name" =>"Navigation",// table Header
    "connection_leading_client" =>"Hauptmandant",// table Header
    "connection_list_kids"=>"vernetzter Mandant",// table Header
    "connection_followers"=>"vernetzter Mandant",// table Header
    "edit connection" =>"Bearbeiten",// "link to edit connection"
    
    "the parent must be set for this connection"=>"Es muss ein Hauptmandant gewählt sein",// Error if no parent is selected
    "Please select following clients for current connection"=>"Bitte vernetzte Mandanten auswählen.",// error if no follower is selected
    "Please select following clients for current connection - different from PARENT"=>"Der vernetzte Mandant muss ein anderer Mandant als der Hauptmandant sein.",// error if parent is selected as follower
    
	//ISPC-2615 Carmen 14.10.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
	'patientWrongClient' => 'Patient aktiv in anderem Mandant',
	'patientwrongclientdescription' => 'Dieser Patient hat einen aktiven Fall in einem anderen Mandanten. Wollen Sie dort jetzt hinwechseln?',
	'go_to_active_client' => 'Gehe zu aktiven Fall: ',
	'NO, stay in current discharge  pateitn from current client!' => 'Ich möchte in der Akte in diesem Mandanten bleiben',
	//--
    
    )
?>

