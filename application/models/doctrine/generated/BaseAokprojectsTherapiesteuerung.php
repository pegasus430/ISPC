<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 
abstract class BaseAokprojectsTherapiesteuerung extends Doctrine_Record {

    function setTableDefinition()
    {
        $this->setTableName('aokprojects_therapiesteuerung');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('gewicht_erhoeht', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('gewicht_erhoehung', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('appetitlos', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('length', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('weight', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('bmi', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('geschwollen', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('wasser_bauch', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('oefter_wasser_lassen', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('trinken_mehr', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('trinken_mehr_wieviel', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('trinken_weniger', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('trinken_weniger_wieviel', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('medikamente_regelmaessig', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('medikamente_alle', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('medikamente_dosierung', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('medikamente_frei', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('medikamente_frei_text', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('medikamente_bedarf_oefter', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('medikamente_weglassen', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('freitext_medikamente', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('husten', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2'), 'default' => ''));
        $this->hasColumn('atemnot', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2'), 'default' => ''));
        $this->hasColumn('auswurf', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2'), 'default' => ''));

        $this->hasColumn('druck_syst', 'integer', 4, array('type' => 'integer','length' => 4));
        $this->hasColumn('druck_diast', 'integer', 4, array('type' => 'integer', 'length' => 4));

        $this->hasColumn('puls', 'integer', 4, array('type' => 'integer', 'length' => 4));

        $this->hasColumn('herzrhythmus', 'integer', 4, array('type' => 'integer', 'length' => 4));
        $this->hasColumn('sauerstoffssaettigung', 'integer', 4, array('type' => 'integer', 'length' => 4));

        $this->hasColumn('herzkatheter', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('schrittmacher', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('defibrilator', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('sauerstoff', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('stent', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('dev_termin', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));


        $this->hasColumn('dev_termin_date', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('dmp_khk', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('dmp_copd', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('facharzt_termin', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('facharzt_termin_date', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('tagebuch_regelmaessig', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

        $this->hasColumn('sauerstoff_termin', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('sauerstoff_termin_date', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('pneumo_termin', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('pneumo_termin_date', 'string', 255, array('type' => 'string', 'length' => 255));


    }

    function setUp()
    {
        $this->actAs(new Createtimestamp());

    }


}

?>