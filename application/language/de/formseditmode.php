<?php
return [
    "Formular Editmode" => "Formular Überwachung",
    "This Formular Has multiple editors pugin, checking if anyone else is editing it now" => "Dieses Formular ist im Schutz vor gleichzeitiger Bearbeitung. Prüfung ob jemand gerade dieses Dokument bearbeitet.",
    "ISPC Browser Notification Permissions" => "ISPC Browser Benachrichtigungen",
    "Here you will receive informations when one of your coleags is doying work on the same patient/formular as you, so you both don't to the same job twice and break things" => "Sie erhalten Nachrichten, wenn ein anderer Benutzer im gleichen Formular Änderungen vornehmen will.",
    
    //b: if no-one else is editing then a warning yellow message:
    "Become formular's editor" => "Formular Überwachung - Andere Benutzer informieren, dass Sie nun an diesem Formular arbeiten", // title
    "No one is checked-in as this formular's editor" => "Momentan hat kein anderer Benutzer markiert, in diesem Formular zu arbeiten.",
    'Check-In as formular editor' => "Bearbeitung übernehmen", //label on the button
    'so you become the editor of this formular' => "",
    "By doing so, if someone else wants to edit this formular, he will be informed that you are editing it" => "Wenn Sie die Bearbeitung übernehmen, werden andere Benutzer informiert, dass Sie gerade aktiv an diesem Formular arbeiten. Bitte BEENDEN Sie diesen Modus nach Ihrer Bearbeitung um das Dokument für andere wieder freizugeben.",
    
    //c: a green info message
    "You are checked-in as editor" => "Sie sind Bearbeiter dieses Formulars", //title
    "Check-Out as formular editor" => "Bearbeitung beenden",//label on the button // TODO-3942 Ancuta 11.03.2021
    "so you let someone else become the editor, without saving the form" => "Beenden Sie den Bearbeitungsmodus sobald Sie mit Ihren Änderungen fertig sind und Ihre Eingaben gespeichert haben, um das Dokument für andere freizugeben.",
    
    //d: if someone-else is editing (login incognito with a different user), a red warning message
    "User %s is editing the formular, since %s" => "Benutzer %s bearbeitet dieses Formular seit %s", //title
    "Another user is editing the formular, since %s" =>"Ein Admin editiert dieses Formular seit %s", //a different title is displayed to a normal user if a sadmin is editing the form
    "Check-In Overwrite Editor" => "Bearbeitung übernehmen",//label on the button
    'You can take over the editing. Maybe the other user forgot to check out. Attention: It can happen that the work of the other user is lost.' => 'Sie können die Bearbeitung übernehmen. Vielleicht hat der andere Benutzer vergessen sich auszuchecken. <br/>Achtung: Es kann so passieren, dass die Arbeit des anderen Benutzers verloren geht.',
]; 