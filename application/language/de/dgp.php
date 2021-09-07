<?php 
return array(
    

    //ISPC-2198
    "[Selected patient admission interval]" => "Fall auswählen", //legend
    "[The discharge part or this form is not relevant]" => "Eine Entlassung für diesen Fall liegt noch nicht vor.", // a message to display on a fall that has no discharge.. this can be empty
    
    //succes message when sending xml
    "[Export to www.hospiz-palliativ-register.de was successful]" => "Export an das Register war erfolgreich",
    
    //some error messages.. that should never occur
    "[Failed to export, please contact admin]" => "Fehler beim Export, bitte smart-Q informieren",
    "[Export to www.hospiz-palliativ-register.de Failed with unknown error]" => "Der Export ist aufgrund eines unbekannten Fehlers gescheitert, bitte informieren Sie smart-Q",
    
    //this are from the error codes... for a full info check reg's table reg_fehler_upload_code
    "All OK data received and saved." => "Alles OK Daten empfangen und gespeichert",
    "Error during login (access data wrong)" => "Fehler bei der Anmeldung (Zugangsdaten falsch)",
    "Error while logging in (functional area not set)" => "Fehler bei der Anmeldung (Funktionsbereich nicht festgelegt)",
    "Login failed (specified version not allowed)" => "Fehler bei der Anmeldung (angegebene Version nicht zulässig)",
    "No SSL encryption (no https connection)" => "Keine SSL-Verschlüsselung (keine https-Verbindung)",
    "Error while logging in (account locked)" => "Fehler bei der Anmeldung (Account gesperrt)",
    "Error of delivered data (no data received)" => "Fehler der angelieferten Daten (keine Daten empfangen)",
    "Error of received data (data does not correspond to the XSD file - no valid XML format)" => "Fehler der empfangenen Daten (Daten entsprechen nicht der XSD-Datei - keine gültiges XML-Format)",
    "Unknown serious mistake" => "Unbekannter schwerer Fehler",
    "Error of received data (mandatory fields, insufficient amount of data)" => "Fehler der empfangenen Daten (Pflichtfelder, keine ausreichende Datenmenge)",
    "Errors of the received data (Pat_ID and Dat_ID do not match)" => "Fehler der empfangenen Daten (Pat_ID und Dat_ID passen nicht zueinander)",
    "Errors of the received data (Dat_ID submitted multiple times)" => "Fehler der empfangenen Daten (Dat_ID mehrfach eingereicht)",
    "Reaching the maximum script runtime" => "Erreichen der maximalen Scriptlaufzeit",
    "No lock on the center" => "Kein Lock auf das Zentrum erhalten",
    
    
);
?>