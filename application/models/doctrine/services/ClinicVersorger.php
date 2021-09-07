<?php
/**
 * @clau 
 * Sep 5, 2018
 * this class is 99.9% from Nico - Clinic, ISPC-2254, it was just renamed from Versorger->ClinicVersorger
 * it will be used for the sync only 
 * since we don't have a __timestamp, we will e using create_date||change_date , _get_data_last_change_date
 *
 * @baerbel
 * Aug 27, 2019
 *
 * This class is now used for the Feature "controldischargeplanning" for CLINIC after Migration von Clinic to ISPC (IM-3)
 * We need a list of categories without having a patient-ipid, so we can't use
 * Application_Form_PatientDetails->getAllCategories().
 * // Maria:: Migration ISPC to CISPC 08.08.2020
 */
class ClinicVersorger
{
    private $_change_date = [];//@cla
    private $_logger = null;//@cla

    function __construct(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->clientid=$clientid;
        $this->categories=array();

        $this->_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
        
        $this->categories["family_doctor"]=array(
            "label"=>"Hausarzt",
            "table"=>"FamilyDoctor",
            "cols"=>array(
                array("db"=>"practice","class"=>"name","label"=>"Praxis"),
                array("db"=>"title","class"=>"title","label"=>"Titel"),
                array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),

                array("db"=>"street1","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone_practice","class"=>"phone","label"=>"Telefon"),
                array("db"=>"phone_private","class"=>"phone_private","label"=>"Telefon privat"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),

                array("db"=>"doctornumber", "class"=>"doctornumber", "label"=>"Lebenslange Arztnummer"),

                array("db"=>"medical_speciality","class"=>"medical_speciality", "label"=>"Spezialisierung"),
                array("db"=>"doctor_bsnr", "class"=>"doctor_bsnr", "label"=>"BSNR"),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen")
            ),
            "patientmapping"=>array(
                "table"=>"PatientMaster",
                "key"=>"familydoc_id",
                "ipid"=>"ipid",
            ),
            "features"=>array('isdelete', 'clientid', 'indrop'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("title", "first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Telefon privat", "cols"=>array(array("phone_private"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("title", "first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );
        $specmap = SpecialistsTypes::get_specialists_types_mapping($clientid);
        $this->categories["specialists"]=array(
            "label"=>"Facharzt",
            "table"=>"Specialists",
            "cols"=>array(
                array("db"=>"practice","class"=>"name","label"=>"Praxis"),
                array("db"=>"title","class"=>"title","label"=>"Titel"),
                array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
                array("db"=>"street1","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone_practice","class"=>"phone","label"=>"Telefon"),
                array("db"=>"phone_private","class"=>"phone_private","label"=>"Telefon privat"),
                array("db"=>"phone_cell","class"=>"phone_cell","label"=>"Handy-Nr."),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"doctornumber", "class"=>"doctornumber", "label"=>"Lebenslange Arztnummer"),

                array("db"=>"medical_speciality","class"=>"medical_speciality", "label"=>"Spezialisierung",  "uiclass"=>"select", "items"=>$specmap, "flat_for_export"=>true),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen", "prefill_into"=>"comments2")
            ),
            "patientmapping"=>array(
                "table"=>"PatientSpecialists",
                "key"=>"sp_id",
                "ipid"=>"ipid",
                "isdelete"=>true,
                "addcols"=>array(
                    array("db"=>"comment", "class"=>"comments2", "label"=>"Notizen", "prefill"=>"comments"),
                ),
            ),
            "features"=>array('isdelete', 'clientid', 'indrop'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Telefon privat", "cols"=>array(array("phone_private"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );
        $kbv=new KbvKeytabs();
        $ins_status_items=$kbv->getKbvKeytabs(1);

        $this->categories["health_insurance"]=array(
            "label"=>"Krankenversicherung",
            "table"=>"PatientHealthInsurance",
            "cols"=>array(
                array("db"=>"company_name","class"=>"name","label"=>"Krankenversicherung", "encrypt"=>1),
                array("db"=>"ins_insurance_provider", "class"=>"ins_insurance_provider","label"=>"Abteilung", "encrypt"=>1),
                array("db"=>"ins_contactperson", "class"=>"ins_contactperson","label"=>"Kontaktperson", "encrypt"=>1),
                array("db"=>"ins_street","class"=>"street","label"=>"Straße", "encrypt"=>1),
                array("db"=>"ins_zip","class"=>"zip","label"=>"PLZ", "encrypt"=>1),
                array("db"=>"ins_city","class"=>"city","label"=>"Ort", "encrypt"=>1),
                array("db"=>"ins_phone","class"=>"phone","label"=>"Telefon", "encrypt"=>1),
                array("db"=>"ins_phone2","class"=>"phone2","label"=>"Telefon 2", "encrypt"=>1),

                array("db"=>"ins_phonefax","class"=>"fax","label"=>"Fax", "encrypt"=>1),
                array("db"=>"ins_email","class"=>"email","label"=>"E-Mail", "encrypt"=>1),

                array("db"=>"ins_post_office_box","class"=>"post_office_box","label"=>"Postfach", "encrypt"=>1),
                array("db"=>"ins_zip_mailbox","class"=>"zip_mailbox","label"=>"PLZ Postfach", "encrypt"=>1),
                array("db"=>"ins_post_office_box_location","class"=>"post_office_box_location","label"=>"Ort Postfach", "encrypt"=>1),

                array("db"=>"institutskennzeichen","class"=>"institutskennzeichen","label"=>"Institutskennzeichen"),
                array("db"=>"kvk_no","class"=>"kvk_no","label"=>"Kassennummer"),
                array("db"=>"insurance_no","class"=>"insurance_no","label"=>"Versichertennummer"),
                array("db"=>"rezeptgebuhrenbefreiung","class"=>"rezeptgebuhrenbefreiung","label"=>"Gebührenbefreiung", "uiclass"=>"checkbox"),
                array("db"=>"privatepatient","class"=>"privatepatient","label"=>"Privatpatient", "uiclass"=>"checkbox"),
                array("db"=>"direct_billing","class"=>"direct_billing","label"=>"Direktabrechnung", "uiclass"=>"checkbox"),
                array("db"=>"bg_patient","class"=>"bg_patient","label"=>"BG Patient", "uiclass"=>"checkbox"),
                array("db"=>"insurance_status","class"=>"insurance_status","label"=>"Versicherungsstatus", "uiclass"=>"select", "items"=>$ins_status_items, "encrypt"=>1),
                array("db"=>"comment","class"=>"comment","label"=>"Notizen", "encrypt"=>1),

            ),
            "patientmapping"=>array(
                "table"=>"PatientHealthInsurance",
                "key"=>"id",
                "ipid"=>"ipid"
            ),
            "addressbook"=>array(
                "table"=>"HealthInsurance",
                //TODO-3496 Ancuta 08.10.2020  added ,'extra','onlyclients'
                //"features"=>array('isdelete','clientid'),
                "features"=>array('isdelete','clientid','extra','onlyclients'),
                // -- 
                "cols"=>array(
                    array("db"=>"name","class"=>"name","label"=>"Krankenversicherung"),
                    array("db"=>"street1","class"=>"street","label"=>"Straße"),
                    array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                    array("db"=>"city","class"=>"city","label"=>"Ort"),
                    array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
                    array("db"=>"phone2","class"=>"phone2","label"=>"Telefon 2"),
                    array("db"=>"phonefax","class"=>"fax","label"=>"Fax"),
                    array("db"=>"email","class"=>"email","label"=>"E-Mail"),

                    array("db"=>"post_office_box","class"=>"email","label"=>"Postfach"),
                    array("db"=>"zip_mailbox","class"=>"email","label"=>"PLZ Postfach"),
                    array("db"=>"post_office_box_location","class"=>"email","label"=>"Ort Postfach"),

                    array("db"=>"kvnumber","class"=>"kvk_no", "label"=>"Kassennummer"),
                    array("db"=>"iknumber","class"=>"institutskennzeichen", "label"=>"Institutskennzeichen")
                ),
                "address"=>array(
                    array("label"=>"Name", "cols"=>array(array("name"))),
                    array("label"=>"Straße", "cols"=>array(array("street"))),
                    array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
                )
            ),
            "features"=>array(),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
                array("label"=>"Versichertennummer", "cols"=>array(array("insurance_no"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );

        $this->categories["pflegedienst"]=array(
            "label"=>"Pflegedienst",
            "table"=>"Pflegedienstes",
            "cols"=>array(
                array("db"=>"nursing","class"=>"name","label"=>"Pflegedienst"),
                array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
                array("db"=>"street1","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone_practice","class"=>"phone","label"=>"Telefon"),
                array("db"=>"phone_emergency","class"=>"phone_emergency","label"=>"Telefon Notfall"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"ik_number","class"=>"ik_number","label"=>"IK-Nummer"),

                array("db"=>"palliativpflegedienst", "class"=>"palliativpflegedienst", "label"=>"ist Palliativ-Pflegedienst", "uiclass"=>"checkbox"),

                array("db"=>"medical_speciality","class"=>"medical_speciality", "label"=>"Spezialisierung"),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen"),
                array("db"=>"is_contact", "class"=>"is_contact", "label"=>"ist Kontaktnummer", "uiclass"=>"checkbox", "hide_in_editor"=>true),
            ),
            "patientmapping"=>array(
                "table"=>"PatientPflegedienste",
                "key"=>"pflid",
                "ipid"=>"ipid",
                "addcols"=>array(
                    array("db"=>"pflege_comment", "class"=>"patient_comment", "label"=>"Kommentare"),
                    array("db"=>"pflege_emergency", "class"=>"patient_emergency", "label"=>"Hausnotruf", "uiclass"=>"checkbox"),
                    array("db"=>"pflege_emergency_comment", "class"=>"patient_emergency_comment", "label"=>"Hausnotruf Kommentar"),
                ),
                "isdelete"=>true,
            ),
            "features"=>array('isdelete', 'clientid', 'indrop'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Telefon Notfall", "cols"=>array(array("phone_emergency"))),
                array("label"=>"Hausnotruf", "cols"=>array(array("patient_emergency_comment"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );

        $this->categories["pharmacy"]=array(
            "label"=>"Apotheke",
            "table"=>"Pharmacy",
            "cols"=>array(
                array("db"=>"pharmacy","class"=>"name","label"=>"Apotheke"),
                array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
                array("db"=>"street1","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen", "prefill_into"=>"comments2"),

            ),
            "patientmapping"=>array(
                "table"=>"PatientPharmacy",
                "key"=>"pharmacy_id",
                "ipid"=>"ipid",
                "addcols"=>array(
                    array("db"=>"pharmacy_comment", "class"=>"comments2", "label"=>"Notizen", "prefill"=>"comments"),
                ),
                "isdelete"=>true,
            ),
            "features"=>array('isdelete', 'clientid', 'indrop'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );
        $this->categories["location"]=array(//TODO-3496, elena, 09.10.2020
            "label"=>"Aufenthaltsorte",
            "table"=>"Locations",
            "cols"=>array(
                array("db"=>"location","class"=>"name","label"=>"Aufenhaltsort","encrypt"=>1),
                array("db"=>"location_type","class"=>"type","label"=>"Typ"),
                //array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                //array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
                //TODO-3915,Elena,05.03.2021
                array("db"=>"street","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone1","class"=>"phone","label"=>"Telefon"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                //array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"comment","class"=>"comments", "label"=>"Notizen", "prefill_into"=>"comments2"),

            ),
            "patientmapping"=>array(
                "table"=>"PatientLocation",
                "key"=>"location_id",
                "ipid"=>"ipid",
                "addcols"=>array(
                    array("db"=>"comment", "class"=>"comments2", "label"=>"Notizen", "prefill"=>"comments"),
                ),
                "isdelete"=>true,
            ),
            "features"=>array('isdelete', 'client_id'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );

        $this->categories["supplies"]=array(
            "label"=>"Sanitätshaus",
            "table"=>"Supplies",
            "cols"=>array(
                array("db"=>"supplier","class"=>"name","label"=>"Sanitätshaus"),
                array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
                array("db"=>"street1","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen", "prefill_into"=>"comments2")
            ),
            "patientmapping"=>array(
                "table"=>"PatientSupplies",
                "key"=>"supplier_id",
                "ipid"=>"ipid",
                "addcols"=>array(
                    array("db"=>"supplier_comment", "class"=>"comments2", "label"=>"Notizen", "prefill"=>"comments"),
                ),
                "isdelete"=>true,
            ),
            "features"=>array('isdelete', 'clientid', 'indrop'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );

        $this->categories["hospice_association"]=array(
            "label"=>"Hospizdienst",
            "table"=>"Hospiceassociation",
            "cols"=>array(
                array("db"=>"hospice_association","class"=>"name","label"=>"Hospizdienst"),
                array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
                array("db"=>"street1","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone_practice","class"=>"phone","label"=>"Telefon"),
                array("db"=>"phone_emergency","class"=>"phone_emergency","label"=>"Telefon Notfall"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen", "prefill_into"=>"comments2")
            ),
            "indrop"=>true,
            "patientmapping"=>array(
                "table"=>"PatientHospiceassociation",
                "key"=>"h_association_id",
                "ipid"=>"ipid",
                "addcols"=>array(
                    array("db"=>"h_association_comment", "class"=>"comments2", "label"=>"Notizen", "prefill"=>"comments"),
                ),
                "isdelete"=>true,
            ),
            "features"=>array('isdelete', 'clientid', 'indrop'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );

        $this->categories["homecare"]=array(
            "label"=>"Homecare",
            "table"=>"Homecare",
            "cols"=>array(
                array("db"=>"homecare","class"=>"name","label"=>"Homecare"),
                array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
                array("db"=>"street1","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone_practice","class"=>"phone","label"=>"Telefon"),
                array("db"=>"phone_emergency","class"=>"phone_emergency","label"=>"Telefon Notfall"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"is_contact","class"=>"is_contact","label"=>"ist die Kontakt-Telefonnummer", "uiclass"=>"checkbox"),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen", "prefill_into"=>"comments2")
            ),
            "indrop"=>true,
            "patientmapping"=>array(
                "table"=>"PatientHomecare",
                "key"=>"homeid",
                "ipid"=>"ipid",
                "addcols"=>array(
                    array("db"=>"home_comment", "class"=>"comments2", "label"=>"Notizen", "prefill"=>"comments"),
                ),
                "isdelete"=>true,
            ),
            "features"=>array('isdelete', 'clientid', 'indrop'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );

        $this->categories["physiotherapists"]=array(
            "label"=>"Physiotherapeut",
            "table"=>"Physiotherapists",
            "cols"=>array(
                array("db"=>"physiotherapist","class"=>"name","label"=>"Praxis"),
                array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
                array("db"=>"street1","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone_practice","class"=>"phone","label"=>"Telefon"),
                array("db"=>"phone_emergency","class"=>"phone_emergency","label"=>"Telefon Notfall"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen", "prefill_into"=>"comments2"),
                array("db"=>"is_contact","class"=>"is_contact","label"=>"ist die Kontakt-Telefonnummer", "uiclass"=>"checkbox"),
            ),
            "indrop"=>true,
            "patientmapping"=>array(
                "table"=>"PatientPhysiotherapist",
                "key"=>"physioid",
                "ipid"=>"ipid",
                "addcols"=>array(
                    array("db"=>"physio_comment", "class"=>"comments2", "label"=>"Notizen", "prefill"=>"comments"),
                ),
                "isdelete"=>true,
            ),
            "features"=>array('isdelete', 'clientid', 'indrop'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );

        $this->categories["suppliers"]=array(
            "label"=>"Sonst. Versorger",
            "table"=>"Suppliers",
            "cols"=>array(
                array("db"=>"supplier","class"=>"name","label"=>"Versorger"),
                array("db"=>"type","class"=>"type","label"=>"Typ"),
                array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
                array("db"=>"street1","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen", "prefill_into"=>"comments2")
            ),
            "indrop"=>true,
            "patientmapping"=>array(
                "table"=>"PatientSuppliers",
                "key"=>"supplier_id",
                "ipid"=>"ipid",
                "addcols"=>array(
                    array("db"=>"supplier_comment", "class"=>"comments2", "label"=>"Notizen", "prefill"=>"comments"),
                ),
                "isdelete"=>true,
            ),
            "features"=>array('isdelete', 'clientid', 'indrop'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );


        $this->categories["funeral"]=array(//TODO-3496, elena, 09.10.2020
            "label"=>"Bestatter",
            "table"=>"Servicesfuneral",
            "cols"=>array(
                array("db"=>"services_funeral_name","class"=>"name","label"=>"Bestatter"),
                //array("db"=>"type","class"=>"type","label"=>"Typ"),
                array("db"=>"cp_fname","class"=>"first_name","label"=>"Vorname"),
                array("db"=>"cp_lname","class"=>"last_name","label"=>"Nachname"),
                array("db"=>"street","class"=>"street","label"=>"Straße"),
                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
                array("db"=>"city","class"=>"city","label"=>"Ort"),
                array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
                //array("db"=>"salutation","class"=>"salutation","label"=>"Anrede"),
                array("db"=>"comments","class"=>"comments", "label"=>"Notizen", "prefill_into"=>"comments2")
            ),
            "indrop"=>false,
            "patientmapping"=>array(

            ),
            "features"=>array('isdelete', 'clientid'),
            "extract"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Telefon", "cols"=>array(array("phone"))),
                array("label"=>"Fax", "cols"=>array(array("fax"))),
            ),
            "address"=>array(
                array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
                array("label"=>"Straße", "cols"=>array(array("street"))),
                array("label"=>"PLZ/Ort", "cols"=>array(array("zip", "city"))),
            )
        );

       //ISPC-2672 Carmen 21.10.2020 
       $this->categories["kindergarten"]=array(
        		"label"=>"Kindergarten",
        		"table"=>"PatientKindergarten",
        		"cols"=>array(
	                array("db"=>"name_of_kindergarten","class"=>"name","label"=>"Name des Kindegarten"),
	                array("db"=>"type_of_kindergarten","class"=>"type","label"=>"Art"),
	                /* array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
	                array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"), */
        			array("db"=>"contactperson","class"=>"contactperson","label"=>"Ansprechpartner"),
	                array("db"=>"street","class"=>"street","label"=>"Straße"),
	                array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
	                array("db"=>"city","class"=>"city","label"=>"Ort"),
	                array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
	                array("db"=>"fax","class"=>"fax","label"=>"Fax"),
	                array("db"=>"email","class"=>"email","label"=>"E-Mail"),
            	),
        		"indrop"=>false,
        		"patientmapping"=>array(
        				"table"=>"PatientKindergarten",
        				"key" => 'id',
        				"ipid"=>"ipid",
        		),
           		// Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
        		//"features"=>array('isdelete', 'clientid'),
        		"features"=>array('isdelete'),
        		"extract"=>array(
        				array("label"=>"Name", "cols"=>array(array("name", "contactperson"))),
        				array("label"=>"Telefon", "cols"=>array(array("phone"))),
        				array("label"=>"Fax", "cols"=>array(array("fax"))),
        		),
        		"address"=>array(
        				
        		)
        );
      $this->categories["playgroup"]=array(
       		"label"=>"Spielgruppe",
       		"table"=>"PatientPlaygroup",
       		"cols"=>array(
       				array("db"=>"name","class"=>"name","label"=>"Name der Spielgruppe"),
       				//array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
	                //array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
        			array("db"=>"contactperson","class"=>"contactperson","label"=>"Ansprechpartner"),
       				array("db"=>"street","class"=>"street","label"=>"Straße"),
       				array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
       				array("db"=>"city","class"=>"city","label"=>"Ort"),
       				array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
       				array("db"=>"fax","class"=>"fax","label"=>"Fax"),
       				array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       		),
       		"indrop"=>false,
       		"patientmapping"=>array(
       				"table"=>"PatientPlaygroup",
       				"key" => 'id',
       				"ipid"=>"ipid",
       		),
			// Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       		//"features"=>array('isdelete', 'clientid'),
       		"features"=>array('isdelete'),
       		"extract"=>array(
       				array("label"=>"Name", "cols"=>array(array("name", "contactperson"))),
       				array("label"=>"Telefon", "cols"=>array(array("phone"))),
       				array("label"=>"Fax", "cols"=>array(array("fax"))),
       		),
       		"address"=>array(
       
       		)
       );
       $this->categories["school"]=array(
       		"label"=>"Schule",
       		"table"=>"PatientSchool",
       		"cols"=>array(
       				array("db"=>"type","class"=>"type","label"=>"Art"),
       				//array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
	                //array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
        			array("db"=>"contactperson","class"=>"contactperson","label"=>"Ansprechpartner"),
       				array("db"=>"street","class"=>"street","label"=>"Straße"),
       				array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
       				array("db"=>"city","class"=>"city","label"=>"Ort"),
       				array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
       				array("db"=>"fax","class"=>"fax","label"=>"Fax"),
       				array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       		),
       		"indrop"=>false,
       		"patientmapping"=>array(
       				"table"=>"PatientSchool",
       				"key" => 'id',
       				"ipid"=>"ipid",
       		),
            // Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       		//"features"=>array('isdelete', 'clientid'),
       		"features"=>array('isdelete'),
       		"extract"=>array(
       				array("label"=>"Ansprechpartner", "cols"=>array(array("contactperson"))),
       				array("label"=>"Telefon", "cols"=>array(array("phone"))),
       				array("label"=>"Fax", "cols"=>array(array("fax"))),
       		),
       		"address"=>array(
       				 
       		)
       );
       $this->categories["workshop_for_disabled_people"]=array(
       		"label"=>"Werkstatt für behinderte Menschen",
       		"table"=>"PatientWorkshopDisabledPeople",
       		"cols"=>array(
       				array("db"=>"name","class"=>"name","label"=>"Name der Werkstatt"),
       				//array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
       				//array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
       				array("db"=>"contactperson","class"=>"contactperson","label"=>"Ansprechpartner"),
		       		array("db"=>"street","class"=>"street","label"=>"Straße"),
		       		array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
		       		array("db"=>"city","class"=>"city","label"=>"Ort"),
		       		array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
		       		array("db"=>"fax","class"=>"fax","label"=>"Fax"),
		       		array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       ),
       "indrop"=>false,
       "patientmapping"=>array(
		       		"table"=>"PatientWorkshopDisabledPeople",
		       		"key" => 'id',
		       		"ipid"=>"ipid",
       ),
       // Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       //"features"=>array('isdelete', 'clientid'),
       "features"=>array('isdelete'),
       "extract"=>array(
       		array("label"=>"Name", "cols"=>array(array("name", "contactperson"))),
       		array("label"=>"Telefon", "cols"=>array(array("phone"))),
       		array("label"=>"Fax", "cols"=>array(array("fax"))),
       ),
       "address"=>array(
       
       )
       );
       $this->categories["othersupplier"]=array(
       		"label"=>"Sonstiges",
       		"table"=>"PatientOtherSuppliers",
       		"cols"=>array(
       				array("db"=>"name","class"=>"name","label"=>"Name des Sonstigen"),
       				//array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
       				//array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
       				array("db"=>"contactperson","class"=>"contactperson","label"=>"Ansprechpartner"),
		       		array("db"=>"street","class"=>"street","label"=>"Straße"),
		       		array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
		       		array("db"=>"city","class"=>"city","label"=>"Ort"),
		       		array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
		       		array("db"=>"fax","class"=>"fax","label"=>"Fax"),
		       		array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       ),
       "indrop"=>false,
       "patientmapping"=>array(
	       		"table"=>"PatientOtherSuppliers",
	       		"key" => 'id',
	       		"ipid"=>"ipid",
       ),
       // Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       //"features"=>array('isdelete', 'clientid'),
       "features"=>array('isdelete'),
       "extract"=>array(
       		array("label"=>"Name", "cols"=>array(array("name", "contactperson"))),
       		array("label"=>"Telefon", "cols"=>array(array("phone"))),
       		array("label"=>"Fax", "cols"=>array(array("fax"))),
       ),
       "address"=>array(
       		 
       )
       );
       $this->categories["sapv_team"]=array(
       		"label"=>"SAPV Team",
       		"table"=>"PatientSapvTeam",
       		"cols"=>array(
       				array("db"=>"name_sapv","class"=>"name","label"=>"Name des SAPV Teams"),
       				array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
       				array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
       				array("db"=>"street","class"=>"street","label"=>"Straße"),
       				array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
       				array("db"=>"city","class"=>"city","label"=>"Ort"),
       				array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
       				array("db"=>"fax","class"=>"fax","label"=>"Fax"),
       				array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       		),
       		"indrop"=>false,
       		"patientmapping"=>array(
       				"table"=>"PatientSapvTeam",
       				"key" => 'id',
       				"ipid"=>"ipid",
       		),
	        // Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       		//"features"=>array('isdelete', 'clientid'),
       		"features"=>array('isdelete'),
       		"extract"=>array(
       				array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
       				array("label"=>"Telefon", "cols"=>array(array("phone"))),
       				array("label"=>"Fax", "cols"=>array(array("fax"))),
       		),
       		"address"=>array(
       
       		)
       );
       $this->categories["childrens_hospice"]=array(
       		"label"=>"Kinderhospiz",
       		"table"=>"PatientChildrensHospice",
       		"cols"=>array(
       				array("db"=>"name_hospice","class"=>"name","label"=>"Name des Kinderhospizes "),
       				array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
       				array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
       				array("db"=>"street","class"=>"street","label"=>"Straße"),
       				array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
       				array("db"=>"city","class"=>"city","label"=>"Ort"),
       				array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
       				array("db"=>"fax","class"=>"fax","label"=>"Fax"),
       				array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       		),
       		"indrop"=>false,
       		"patientmapping"=>array(
       				"table"=>"PatientChildrensHospice",
       				"key" => 'id',
       				"ipid"=>"ipid",
       		),
            // Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       		//"features"=>array('isdelete', 'clientid'),
       		"features"=>array('isdelete'),
       		"extract"=>array(
       				array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
       				array("label"=>"Telefon", "cols"=>array(array("phone"))),
       				array("label"=>"Fax", "cols"=>array(array("fax"))),
       		),
       		"address"=>array(
       				 
       		)
       );
       $this->categories["family_support_service"]=array(
       		"label"=>"Familien unterstützender Dienst",
       		"table"=>"PatientFamilySupportService",
       		"cols"=>array(
       				array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
       				array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
       				array("db"=>"street","class"=>"street","label"=>"Straße"),
       				array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
       				array("db"=>"city","class"=>"city","label"=>"Ort"),
       				array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
       				array("db"=>"fax","class"=>"fax","label"=>"Fax"),
       				array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       		),
       		"indrop"=>false,
       		"patientmapping"=>array(
       				"table"=>"PatientFamilySupportService",
       				"key" => 'id',
       				"ipid"=>"ipid",
       		),
            // Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       		//"features"=>array('isdelete', 'clientid'),
       		"features"=>array('isdelete'),
       		"extract"=>array(
       				array("label"=>"Name", "cols"=>array(array("first_name", "last_name"))),
       				array("label"=>"Telefon", "cols"=>array(array("phone"))),
       				array("label"=>"Fax", "cols"=>array(array("fax"))),
       		),
       		"address"=>array(
       
       		)
       );
       $this->categories["ambulant_children_hospice_service"]=array(
       		"label"=>"Ambulanter Kinderhospizdienst",
       		"table"=>"PatientAmbulantChildrenHospiceService",
       		"cols"=>array(
       				array("db"=>"name_of_children_hospice_service","class"=>"name","label"=>"Name des Kinderhospizdienstes"),
       				array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
       				array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
       				array("db"=>"street","class"=>"street","label"=>"Straße"),
       				array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
       				array("db"=>"city","class"=>"city","label"=>"Ort"),
       				array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
       				array("db"=>"fax","class"=>"fax","label"=>"Fax"),
       				array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       		),
       		"indrop"=>false,
       		"patientmapping"=>array(
       				"table"=>"PatientAmbulantChildrenHospiceService",
       				"key" => 'id',
       				"ipid"=>"ipid",
       		),
            // Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       		//"features"=>array('isdelete', 'clientid'),
       		"features"=>array('isdelete'),
       		"extract"=>array(
       				array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
       				array("label"=>"Telefon", "cols"=>array(array("phone"))),
       				array("label"=>"Fax", "cols"=>array(array("fax"))),
       		),
       		"address"=>array(
       
       		)
       );
       $this->categories["youth_welfare_office"]=array(
       		"label"=>"Jugendamt",
       		"table"=>"PatientYouthWelfareOffice",
       		"cols"=>array(
       				array("db"=>"name_of_youth_welfare_office","class"=>"name","label"=>"Name des Jugendamtes"),
       				array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
       				array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
       				array("db"=>"street","class"=>"street","label"=>"Straße"),
       				array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
       				array("db"=>"city","class"=>"city","label"=>"Ort"),
       				array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
       				array("db"=>"fax","class"=>"fax","label"=>"Fax"),
       				array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       		),
       		"indrop"=>false,
       		"patientmapping"=>array(
       				"table"=>"PatientYouthWelfareOffice",
       				"key" => 'id',
       				"ipid"=>"ipid",
       		),
	        // Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       		//"features"=>array('isdelete', 'clientid'),
       		"features"=>array('isdelete'),
       		"extract"=>array(
       				array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
       				array("label"=>"Telefon", "cols"=>array(array("phone"))),
       				array("label"=>"Fax", "cols"=>array(array("fax"))),
       		),
       		"address"=>array(
       				 
       		)
       );
       $this->categories["integration_assistance"]=array(
       		"label"=>"Eingliederungshilfe",
       		"table"=>"PatientIntegrationAssistance",
       		"cols"=>array(
       				array("db"=>"name_of_service_provider","class"=>"name","label"=>"Name des Leistungserbringers"),
       				array("db"=>"first_name","class"=>"first_name","label"=>"Vorname"),
       				array("db"=>"last_name","class"=>"last_name","label"=>"Nachname"),
       				array("db"=>"street","class"=>"street","label"=>"Straße"),
       				array("db"=>"zip","class"=>"zip","label"=>"PLZ"),
       				array("db"=>"city","class"=>"city","label"=>"Ort"),
       				array("db"=>"phone","class"=>"phone","label"=>"Telefon"),
       				array("db"=>"fax","class"=>"fax","label"=>"Fax"),
       				array("db"=>"email","class"=>"email","label"=>"E-Mail"),
       		),
       		"indrop"=>false,
       		"patientmapping"=>array(
       				"table"=>"PatientIntegrationAssistance",
       				"key" => 'id',
       				"ipid"=>"ipid",
       		),
            // Ancuta 25.11.2020 - removed clientid from features - as it does not exist in table
       		//"features"=>array('isdelete', 'clientid'),
       		"features"=>array('isdelete'),
       		"extract"=>array(
       				array("label"=>"Name", "cols"=>array(array("name"), array("first_name", "last_name"))),
       				array("label"=>"Telefon", "cols"=>array(array("phone"))),
       				array("label"=>"Fax", "cols"=>array(array("fax"))),
       		),
       		"address"=>array(
       
       		)
       );
       //--
        
        $this->extracategories=array();
        $this->extracategories['hilfsmittel']=array('label'=>"Hilfsmittel", 'custom_category'=>true);
    }




    public function getAllCategories(){
        $out=$this->categories + $this->extracategories;
        return $out;
    }

	public function getCategoriesSortByLabel()
    {
        $out = $this->categories;

        //get the labels
        $label = array();
        foreach ($out as $key=>$value) {
            $label[$key] = $out[$key]['label'];
        }

        //sort the labels
        asort($label);

        //build the return listsorted by label
        $returnarray = array();
        foreach ($label as $key=>$value) {
            $returnarray[$key] = $out[$key];
        }


        return $returnarray;

    }

    /**
     * Returns all assigned Versorger for this Patient
     */
    function getPatientData($ipid, $for_export=false){
        $output=array();
        foreach ($this->categories as $catkey=>$cat){
            $catrows=array();
            if($catkey == 'funeral'){//TODO-3496, elena, 09.10.2020
                continue; //funerals have no assignments with patients
            }

            $sql = Doctrine_Query::create()
                ->select('*')
                ->from($cat['patientmapping']['table'])
                ->where($cat['patientmapping']['ipid'].'=?',$ipid);
            if($cat['patientmapping']['isdelete']) {
                $sql->andWhere('isdelete=0');
            }
            $mapping_array = $sql->fetchArray();
            $ids=array_column($mapping_array,$cat["patientmapping"]["key"]);
            $pids=array_column($mapping_array,"id");

            if(count($ids)>0) {
                if($cat['table']!=$cat["patientmapping"]["table"]) {
                    $sql = Doctrine_Query::create()
                        ->select('*')
                        ->from($cat['table'])
                        ->whereIn("id", $ids);
                    $table_array = $sql->fetchArray();
                }else{
                    //Mapping-Table is Data-Table, example:HealthInsurance
                    $table_array=$mapping_array;
                }
                $table_array_by_id=array();
                foreach ($table_array as $row){
                    $table_array_by_id[$row['id']]=$row;
                }

                foreach ($mapping_array as $mrow) {
                    if($mrow[$cat['patientmapping']['key']]>0) {
                        $catrows[] = $this->aggregate_row($table_array_by_id[$mrow[$cat["patientmapping"]["key"]]], $catkey, $cat, $mrow, $for_export);
                    }
                }
            }
            $output[$catkey]=$catrows;
        }
        $output['__meta-timestamp']=$this->getTimestamp($ipid);

        return $output;
    }


    /**
     * Bring received data in our structure to be able to import it
     * skip fdoctor, PatientHealthInsurance they sync in general sync
     */
    function prepare_received_data($data){
        $newdata=array();
        $ispecmap = SpecialistsTypes::get_specialists_types_mapping($this->clientid);
        foreach($data['PatientSpecialists'] as $item){
            $newitem=$item['data'];
            $spec_map=$data['__meta-categorys']['PatientSpecialists']['userDefinedList']['medical_speciality'];
            if(isset($newitem['medical_speciality']) && isset($spec_map[$newitem['medical_speciality']])){
                $newitem['medical_speciality']=$spec_map[$newitem['medical_speciality']];
            }else{
                $newitem['medical_speciality']="";
            }

            if(in_array($newitem['medical_speciality'], $ispecmap)){
                $newitem['medical_speciality'] = array_search($newitem['medical_speciality'], $ispecmap);
            }else{
                $types_form = new Application_Form_SpecialistsTypes();
                $newitem['medical_speciality'] = intval($types_form->insert_specialist_type($this->clientid, array('specialist_type'=>$newitem['medical_speciality'])));
            }

            $newdata['specialists'][]['data']=$newitem;

        }

        foreach($data['PatientSupplies'] as $item){
            $newdata['supplies'][]['data']=$item['data'];
        }
        foreach($data['PatientHomecare'] as $item){
            $newdata['homecare'][]['data']=$item['data'];
        }
        foreach($data['PatientPhysiotherapist'] as $item){
            $newdata['physiotherapists'][]['data']=$item['data'];
        }
        foreach($data['PatientSuppliers'] as $item){
            $newdata['suppliers'][]['data']=$item['data'];
        }
        foreach($data['PatientHospiceassociation'] as $item){
            $newdata['hospice_association'][]['data']=$item['data'];
        }
        foreach($data['PatientPharmacy'] as $item){
            $newdata['pharmacy'][]['data']=$item['data'];
        }
        foreach($data['PatientPflegedienste'] as $item){
            $newdata['pflegedienst'][]['data']=$item['data'];
        }
        foreach($data['PatientLocations'] as $item){//TODO-3496, elena, 09.10.2020
            $newdata['location'][]['data']=$item['data'];
        }

        if(count($newdata)) {
            $newdata['__meta-timestamp'] = strtotime($data['__meta-timestamp']);

            foreach($newdata as $cat=>$catdata){
                foreach($catdata as $row_no=>$datarow){
                    foreach($this->categories[$cat]['cols'] as $col){
                        $newdata[$cat][$row_no]['data'][$col['class']]=$datarow['data'][$col['db']];
                    }
                    foreach($this->categories[$cat]['patientmapping']['addcols'] as $col){
                        $newdata[$cat][$row_no]['data'][$col['class']]=$datarow['data'][$col['db']];
                    }
                }
            }

        }
        return $newdata;
    }

    /**
     * This is a pseudo-sync
     * removes all patient-versorger if any versorger are in data-input for that category
     */
    function updatePatientData($ipid, $data){
        $data=$this->prepare_received_data($data);

        $old_versorger=$this->getPatientData($ipid);
        $time_incoming=$data['__meta-timestamp'];
        $time_db=$old_versorger['__meta-timestamp'];

        //if both times are equal, take over the transfered data
        if($time_db>$time_incoming){
            //return;//todo:debug
        }else{
            $this->updateTimestamp($ipid, $time_incoming);
        }

        foreach ($this->categories as $catkey=>$cat){
            if(isset($data[$catkey]) && count($data[$catkey])>0){
                //first delete all entries from patient
                foreach($old_versorger[$catkey] as $olddata){
                    $olddata['data']['__delete']=1;
                    $olddata['data']['_category']=$catkey;
                    $this->newPatientEntry($ipid, $olddata['data']);
                }
                //then add all new
                foreach($data[$catkey] as $newdata){
                    $newdata['data']['_category']=$catkey;
                    unset($newdata['data']['_pid']);
                    unset($newdata['data']['_id']);

                    $this->newPatientEntry($ipid, $newdata['data']);
                }
            }
        }
    }


    /**
     * Returns all Versorger of category
     * set max=0 for complete list
     */
    function getAddressbook($catkey, $clientid, $max=10, $search_string=""){

        $cat=$this->categories[$catkey];

        if(isset($cat['addressbook'])){
            $cat=$cat['addressbook'];
        }

        
        // Ancuta 25.11.2020 - Quick hotfix - to check if category has any client identification column, if not - retudn 
        $client_columns = array('client','client_id','clientid');
        $columns_arr = array_intersect($client_columns,$cat['features']);
        if(empty($columns_arr)){
            return array();
        }
        //-- 
        
        
        $sql = Doctrine_Query::create()
            ->select('*')
            ->from($cat['table'])
            ->where(1);

        if(strlen($search_string)>0){
            $search_string=trim(strtolower($search_string));
            $cols=array();
            foreach($cat['cols'] as $col){
                if(in_array($col['class'], array('name','first_name','last_name'))){
                    $cols[]=$col['db'];
                }
            }
            if(count($cols)>1) {
                $imploded=implode(',',$cols);
                $sql->andwhere("CONCAT(".$imploded.") like ?", "%".$search_string."%");
            }elseif(count($cols)==1){
                $sql->andwhere($cols[0]." like ?", "%".$search_string."%");
            }
        }

        if(in_array('indrop',$cat['features'])) {
            $sql->andwhere("indrop=0");
        }
        if(in_array('isdelete',$cat['features'])) {
            $sql->andwhere("isdelete=0");
        }
        if(in_array('clientid',$cat['features'])) {
            //TODO-3496, elena, 07.10.2020
            // without clientid 0
            $sql->andwherein("clientid",array($clientid));
        }
        if(in_array('client_id',$cat['features'])) {
            //TODO-3496, elena, 09.10.2020
            // without clientid 0, especially for locations
            $sql->andwherein("client_id",array($clientid));
        }

        // TODO-3496 Ancuta 08.10.2020  added ,'extra','onlyclients'
        if(in_array('extra',$cat['features'])) {
            $sql->andwhere("extra=0");
        }
        if(in_array('onlyclients',$cat['features'])) {
            $sql->andwhere("onlyclients=1");
        }

        //TODO-3915,Elena,05.03.2021
        if($cat['table'] == 'FamilyDoctor'){
            $sql->andWhere("valid_till='0000-00-00'");
            //$sql->andWhere("valid_till='0000-00-00' or valid_till>NOW()");
            //$sql->andWhere("valid_from='0000-00-00' or valid_from<=NOW()");
        }
        //--
        
        
        if($max>0) {
            $sql->limit($max);
        }
        $xx=$sql->getSqlQuery();
        //print_r($xx);
        $table_array = $sql->fetchArray();


        $catrows=array();
        foreach ($table_array as $row) {
            $catrows[]=$this->aggregate_row($row, $catkey, $cat, $row['id']);
        }



        return $catrows;
    }

    /**
     * Convert db-row to model
     */
    function aggregate_row($row, $catkey, $cat, $prow, $for_export=false){
        $myrow = array('_id'=>$row['id'], '_pid'=>$prow['id']);
        foreach ($cat['cols'] as $col) {
            $myrow[$col['class']] = $row[$col["db"]];
            if(isset($col["encrypt"]) && $col["encrypt"]){
                $myrow[$col['class']]=Pms_CommonData::aesDecrypt($myrow[$col['class']]);
            }
            if($for_export){
                if(isset($col['flat_for_export'])){
                    $imap=$col['items'];
                    $myrow[$col['class']]=$imap[$myrow[$col['db']]];
                }
            }
        }
        if(is_array($prow)) {
            foreach ($cat['patientmapping']['addcols'] as $extracol) {
                $myrow[$extracol['class']] = $prow[$extracol["db"]];
                if (isset($extracol['prefill'])) {
                    if (strlen($myrow[$extracol['class']]) < 1) {
                        $myrow[$extracol['class']] = $myrow[$extracol['prefill']];
                    }
                    if (!$for_export) {
                        unset($myrow[$extracol['prefill']]);
                    }
                }
            }
        }

        if(isset($prow['create_date'])){
            $myrow['__pcreate_date'] = $prow['create_date'];
        }
        if(isset($prow['change_date'])){
            $myrow['__pchange_date'] = $prow['change_date'];
        }
        
        /*
         * @cla
         */
        if (isset($prow['create_date'])) {
            array_push($this->_change_date, $prow['create_date']);
        }
        if (isset($prow['change_date'])) {
            array_push($this->_change_date, $prow['change_date']);
        }
        if (isset($row['create_date'])) {
            array_push($this->_change_date, $row['create_date']);
        }
        if (isset($row['change_date'])) {
            array_push($this->_change_date, $row['change_date']);
        }
        
        $out=array('data'=>$myrow, 'extract'=>$this->getExtract($catkey, $myrow), 'address'=>$this->getAddress($catkey, $myrow),'meta'=>$cat);
        return $out;
    }

    /**
     * Provide a dataset and the category it belongs to.
     * Returns the most interesting fields with the corresponding labels
     * For Example: array(array("Name", "PflegedienstA"), array("Telefon", "012345.."))
     */
    function getExtract($catkey, $row){
        $extractmap=$this->categories[$catkey]['extract'];
        //$colsmap=$this->categories[$catkey]['cols'];
        //$classname_to_dbname=array_combine(array_column($colsmap, 'class'), array_column($colsmap, 'db'));
        $rows=array();
        foreach ($extractmap as $rowmap){
            foreach ($rowmap["cols"] as $cols){
                $parts=array();
                foreach ($cols as $col){
                    $parts[]=$row[$col];
                }
                $text=trim(implode(" ", $parts));
                if(strlen($text)>0){
                    $rows[]=array($rowmap["label"],$text);
                    break;
                }
            }
        }
        return $rows;
    }

    /**
     * Provide a dataset and the category it belongs to.
     * Returns the fields needed for postal address
     * For Example: array(array("Name", "PflegedienstA"), array("Telefon", "012345.."))
     */
    function getAddress($catkey, $row){
        $extractmap=$this->categories[$catkey]['address'];
        //$colsmap=$this->categories[$catkey]['cols'];
        //$classname_to_dbname=array_combine(array_column($colsmap, 'class'), array_column($colsmap, 'db'));
        $rows=array();
        foreach ($extractmap as $rowmap){
            foreach ($rowmap["cols"] as $cols){
                $parts=array();
                foreach ($cols as $col){
                    $parts[]=$row[$col];
                }
                $text=trim(implode(" ", $parts));
                if(strlen($text)>0){
                    $rows[]=array($rowmap["label"],$text);
                    break;
                }
            }
        }
        return $rows;
    }

    function newPatientEntry($ipid,$data,$updatetime=true){
        if($updatetime) {
            $this->updateTimestamp($ipid);
        }
        $catkey=$data['_category'];
        $cat=$this->categories[$catkey];

        $table_name=$cat['table'];
        $patmapping_table_name=$cat['patientmapping']['table'];
        $pid=intval($data['_pid']);

        $features=$cat['features'];
        $patientmaster_direct_mapping=false;
        if($cat['patientmapping']['table']=="PatientMaster"){
            $patientmaster_direct_mapping=true;
        }

        if($pid>0){
            $myentry = Doctrine::getTable($table_name)->findOneBy('id', $data["_id"]);

            if((!$data['__delete']) && (isset($data["_just_update"]) && $data["_just_update"]) && (in_array('indrop',$features) && $myentry->indrop==1)){
                //This is just an edit of an indrop-item
                $my_patmapping = Doctrine::getTable($patmapping_table_name)->findOneBy('id', $data["_pid"]);
            }else {

                if ((!$patientmaster_direct_mapping) && isset($cat['patientmapping']['isdelete']) && (in_array('indrop', $features) || $patmapping_table_name == $table_name)) {
                    unset($myentry);
                    $my_patmapping = Doctrine::getTable($patmapping_table_name)->findOneBy('id', $data["_pid"]);
                    
                    /*
                     * @cla
                     * we use on ispc record model a SoftdeleteListener, and on delete we also cascade
                     * also it's best practice to check if Doctrine_Record object or bool(false) before you call a method on it..
                     */
                    if ($my_patmapping && $listener = Doctrine_Core::getTable($patmapping_table_name)->getRecordListener()->get('SoftdeleteListener')) {
                        $my_patmapping->delete();
//                         $this->_logger->debug($patmapping_table_name . " has SoftdeleteListener, deleted " .print_r($my_patmapping->toArray(), true), 1);
                    } else { 
                        //original                    
                        $my_patmapping->isdelete = 1;
                        $my_patmapping->save();
//                         $this->_logger->debug($patmapping_table_name . " isdelete=1 " .print_r($my_patmapping->toArray(), true), 1);
                        
                    }
                    
                    unset($my_patmapping);
                }

                if ($patientmaster_direct_mapping) {
                    unset($myentry);
                    $my_patmapping = Doctrine::getTable($patmapping_table_name)->findOneBy('id', $data["_pid"]);
                    $key = $cat['patientmapping']['key'];
                    $my_patmapping->$key = null;
                    $my_patmapping->save();
                }
            }
        }

        if($data['__delete']){
            if((!in_array('isdelete',$cat['patientmapping']['features'])) && $patmapping_table_name == $table_name){
                $myentry = Doctrine::getTable($table_name)->findOneBy('id', $data["_id"]);
                $myentry->delete();
                //$myentry->save();
            }

            return array();
        }

        if(!isset($myentry)){
            $myentry=new $table_name();

            if(in_array('indrop',$features)){
                $myentry->indrop=1;
            }

            if(!$patientmaster_direct_mapping) {
                $my_patmapping = new $patmapping_table_name();
                $my_patmapping->ipid = $ipid;
            }else{
                if(!isset($my_patmapping)){
                    $my_patmapping = Doctrine::getTable($patmapping_table_name)->findOneBy('ipid', $ipid);
                }
            }

            if($patmapping_table_name == $table_name){
                unset($my_patmapping);
                $myentry->ipid=$ipid;
            }
        }

        foreach($cat['cols'] as $col) {
            $myentry->{$col['db']} = $data[$col['class']];
            if(isset($col['encrypt']) && $col['encrypt']) {
                $myentry->{$col['db']} = Pms_CommonData::aesEncrypt($data[$col['class']]);
            }
            }
        $myentry->save();

        if(isset($my_patmapping)){
            $key=$cat['patientmapping']['key'];
            $my_patmapping->$key=$myentry->id;
            foreach($cat['patientmapping']['addcols'] as $addcol){
                $my_patmapping->{$addcol['db']} = $data[$addcol['class']];
            }
            $my_patmapping->save();
            $x="_data";
            $patmapping_as_array=$my_patmapping->$x;
        }

        //$myentry->toArray does not work because e.g. hospice_association is a field and it has a method getHospiceassociation that is understood as getter by doctrine.
        $x="_data";
        $as_array=$myentry->$x;

        if($patmapping_table_name == $table_name){
            $patmapping_as_array=$as_array;
        }

        $out=$this->aggregate_row($as_array,$catkey, $cat, $patmapping_as_array );
        return $out;

    }


    /**
     * Place Versorger into a Sync-Packet
     */
    function generate_patient_exportpackage($ipid){
        $mydata=$this->getPatientData($ipid, true);

        //map versorger-categories to meet ispc ambulant needs
        $catmap=array(
            'specialists'=>'PatientSpecialists',
            'pflegedienst'=>'PatientPflegedienste',
            'supplies'=>'PatientSupplies',
            'homecare'=>'PatientHomecare',
            'physiotherapists'=>'PatientPhysiotherapist',
            'suppliers'=>'PatientSuppliers',
            'hospice_association'=>'PatientHospiceassociation',
            'pharmacy'=>'PatientPharmacy',
        );

        $data_inside=false;
        foreach($this->categories as $cat=>$catdet){
            if (count($mydata[$cat])>0){
                $data_inside=true;
                if(isset($catmap[$cat])){
                    $ncat=$catmap[$cat];
                    $mydata[$ncat]=$mydata[$cat];
                    unset($mydata[$cat]);
                    $cat=$ncat;
                }

                //map class-datakeys to db-datakeys
                foreach($mydata[$cat] as $rowno=>$rowdata){
                    $newdata=array();
                    //$newdata['_id']=$rowdata['data']['_id'];
                    //$newdata['_pid']=$rowdata['data']['_pid'];
                    $newdata['__pcreate_date']=$rowdata['data']['__pcreate_date'];

                    foreach ($catdet['cols'] as $col){
                        $newdata[$col['db']]=$rowdata['data'][$col['class']];
                    }
                    foreach ($catdet['patientmapping']['addcols'] as $col){
                        $newdata[$col['db']]=$rowdata['data'][$col['class']];
                    }

                    $mydata[$cat][$rowno]['data']=$newdata;
                }
            }
        }
        if($data_inside){
            SystemsSyncPackets::createPacket($ipid, array('versorger'=>$mydata, 'date'=>date('d.m.Y')), "versorger", 1);
        }
    }
    
    //@cla
    private function _get_data_last_change_date() 
    {
        $ispc__meta_timestamp = 0;
        
        if ( ! empty($this->_change_date) && is_array($this->_change_date)) {
            
            $timestamp = $this->_change_date;
            arsort($timestamp);
            $ispc__meta_timestamp = strtotime(reset($timestamp));
        }
        
        return $ispc__meta_timestamp;
    }

    /**
     * Grab the most recent SyncPacket and update the Patient with this dataset
     */
    function update_patient_from_exportpackage($ipid){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $sql = Doctrine_Query::create()
            ->select('*')
            ->from('SystemsSyncPackets')
            ->where('ipid=?',$ipid)
            ->andwhere('outgoing=0')
            ->andwhere('actionname=?','versorger')
            ->andWhere('clientid=?',$clientid)
            ->orderBy('id DESC')
            ->limit(1);     //all we are interested in is the most recent entry
        $vdata = $sql->fetchArray();

        if($vdata && $vdata[0]['done']==0) {
            
            //@cla we need this in contactphonelistener
            $last_ipid_session = new Zend_Session_Namespace('last_ipid');
            $last_ipid_session->ipid = $ipid;
            
            $payload = $vdata[0]['payload'];
            $versorger = json_decode($payload, 1);
            $this->updatePatientData($ipid, $versorger['versorger']);
            $myentry = Doctrine::getTable('SystemsSyncPackets')->findOneBy('id', $vdata[0]['id']);
            $myentry->done = 1;
            $myentry->save();
            
            $last_ipid_session->ipid = null;
            
        }
    }


    function process_tests($ipid){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;



        $fails=array();
        foreach ($this->categories as $cc=>$cat){
            $old_addressbook=$this->getAddressbook($cc, $clientid, 0);
            $old_addressbook=count($old_addressbook);
            $newarr=array('_category'=>$cc);
            $r=rand(1,100);
            foreach ($cat['cols'] as $i=>$col){
                $newarr[$col['class']]="1".$r.$i;
            }

            $new=$this->newPatientEntry($ipid, $newarr);
            $patdat_all = $this->getPatientData($ipid);
            $patdat=$patdat_all[$cc];
            $entry_found=0;
            foreach($patdat as $entry) {
                $data=$entry['data'];
                if($data['_id']==$new['data']['_id']) {
                    $entry_found=1;
                    foreach ($cat['cols'] as $i => $col) {
                        $val = $data[$col['class']];
                        if ($val != "1" . $r . $i) {
                            $fails[] = "t1:\t" . $cc . ":\t" . $col['class'];
                        }
                    }
                }
            }

            if(!$entry_found){
                $fails[] = "t1:\t" . $cc . ":\t" . "Entry NOT FOUND";
            }

            $deldata=$new['data'];
            $deldata['__delete']=1;
            $deldata['_category']=$cc;
            $new=$this->newPatientEntry($ipid, $deldata);
            if(count($new)>0){
                $fails[] = "t1:\t" . $cc . ":\t" . "Return with data after delete";
            }


            $patdat_all = $this->getPatientData($ipid);
            $patdat=$patdat_all[$cc];
            foreach($patdat as $entry) {
                $data=$entry['data'];
                if($data['_id']==$new['data']['_id']) {
                    $fails[] = "t1:\t" . $cc . ":\t" . "Entry NOT deleted";
                }

            }

            $new_addressbook=$this->getAddressbook($cc, $clientid, 0);
            $new_addressbook=count($new_addressbook);

            if($new_addressbook != $old_addressbook){
                $fails[] = "t1:\t" . $cc . ":\t" . "Addressbook Spam";
            }
        }

        return $fails;
    }


    public function getTimestamp($ipid)
    {
        /*
         * @cla
         */
        return $this-> _get_data_last_change_date();
        
        $ts=PatientVersorger::getEntry($ipid, '__timestamp');
        $time=0;
        if(count($ts)<1){

        }else{
            $time=$ts['time'];
        }
        return $time;
    }

    public function updateTimestamp($ipid, $time=0){
        if($time==0) {
            $d = time();
        }else{
            $d=$time;
        }
        PatientVersorger::updateEntry($ipid, '__timestamp', array('time'=>$d));
    }


    /**
     * Maria Migration from CISP
     * Copy of getPatientdataPrettyline used in patient versorer, before changes from CISPC
     * @param unknown $ipid
     * @param unknown $line
     * @return string[][]
     */
    public function getPatientdataPrettyline_old($ipid, $line){
        $out=array();
        $prettylineconfig=array(
            'name_phone_fax'=>array(
                array('cols'=>array(array('name'), array("first_name", "last_name"))),
                array('cols'=>array(array('phone')),   'prefix'=>' Tel:'),
                array('cols'=>array(array('fax')),     'prefix'=>' Fax:'),
            )
        );
        $map = $prettylineconfig[$line];
        $data=$this->getPatientData($ipid);

        foreach ($data as $datacat){
            foreach ($datacat as $entry) {
                $row = $entry['data'];
                $entryline="";
                foreach ($map as $rowmap) {
                    foreach ($rowmap['cols'] as $cols) {
                        $parts = array();
                        foreach ($cols as $col) {
                            $parts[] = $row[$col];
                        }
                        $text = trim(implode(" ", $parts));
                        if (strlen($text) > 0) {
                            $entryline = $entryline . $rowmap["prefix"] . $text;
                            break;
                        }
                    }
                }

                if(strlen($entryline)>0){
                    $out[]=$entryline;
                }
            }
        }
        return array('text'=>$out);
    }


    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $ipid
     * @param unknown $line
     * @return array|string
     */
    public function getPatientdataPrettyline($ipid, $line)
    {
        $out = array();
        $prettylineconfig = array(
            'name_phone_fax' => array(
                array('cols' => array(array('name'), array("first_name", "last_name"))),
                array('cols' => array(array('phone')), 'prefix' => ' Tel:'),
                array('cols' => array(array('fax')), 'prefix' => ' Fax:'),
            )
        );
        $map = $prettylineconfig[$line];
        $data = $this->getPatientData($ipid);

        foreach ($data as $key=>$datacat) {
            foreach ($datacat as $entry) {
                $cat = $entry['meta']['label'];
                $row = $entry['data'];
                $entryline = "";
                foreach ($map as $rowmap) {
                    foreach ($rowmap['cols'] as $cols) {
                        $parts = array();
                        foreach ($cols as $col) {
                            $parts[] = $row[$col];
                        }
                        $text = trim(implode(" ", $parts));
                        if (strlen($text) > 0) {
                            $entryline = $entryline . $rowmap["prefix"] . $text;
                            break;
                        }
                    }
                }

                if (strlen($entryline) > 0) {
                    $out[$key][] = $entryline;
                }
            }
        }
        return $out;
    }
    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $data
     * @return string
     */
    public function renderreportextract($data){
        $newview = new Zend_View();
        $newview->f_values=$data;
        $newview->setScriptPath(APPLICATION_PATH."/views/scripts/patientnew/");
        $out=$newview->render('versorger_report.html');;
        return $out;
    }

    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $decid
     * @return NULL|mixed|array|array[]
     */
    function getPatientData_with_extra_data($decid){
        $ipid = Pms_CommonData::getIpId($decid);
        $patientmaster = new PatientMaster();
        $patientmaster->getMasterData($decid, 1);

        $data = $patientmaster->get_patientMasterData();

        $patientmaster->getMasterData_extradata($ipid);

        //re-set
        $data = $patientmaster->get_patientMasterData();

        return $data;

    }


}