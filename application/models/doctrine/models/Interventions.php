<?php
Doctrine_Manager::getInstance()->bindComponent('Interventions', 'MDAT');

/**
 * Class Interventions
 * ISPC-2530, Elsa: Interventionen
 * @elena, 04.08.2020
 */
class Interventions extends BaseInterventions
{

    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

    /**
     * @return string[]
     */
    public static function get_frequenz(){
        $retValue = ['mehrfach täglich',
            'täglich',
            'alle zwei Tage',
            '1x wöchentlich',
            '1x monatlich',
            'anderes'];
        return $retValue;

    }

    /**
     * @return string[][]
     */
    public static function get_leitsymptom(){
        $retValue = [
            'Schmerz' => [
                'Akuter Schmerz',
                'Chronischer Schmerz',
            ],

            '' => ['Unruhe'],
            'Gastrointestinaler Bereich' => [
                'Appetitmangel',
                'Schwäche',
                'Müdigkeit/Fatigue',
                'Schlafstörung',
                'Mundtrockenheit',
                'Schluckbeschwerden',
                'Karies',
                'Mucositis',
                'Hypersalivation',
                'Übelkeit',
                'Erbrechen'

        ],

        'Urogenitaler Bereich' => [
            'Obstipation',
            'Diarrhoe',
            'Harnverhalt',
            'Blasenspasmen',
            'Menstruationsbeschwerden',
            'Hämaturie',
            'Dysurie'

        ],

        'Haut' => [
            'Ödeme',
            'Juckreiz',
            'Schwitzen',
            'Ekzeme',
            'Dekubitus',
            'Wunden',
            'Hautblasen',
            'Infektionen',
            'Ikterus'

        ],

        'Neurologisch/Muskulär' => [
            'Parese',
            'Spastik',
            'Myoklonien',
            'Krampfanfälle',
            'Desorientiertheit/Verwirrtheit',
            'Sensibilitätsstörungen',
            'Hirndruckzeichen',
            'Sehstörungen',
            'Hörstörungen',
            'Sprechstörungen',
            'Tremor',
            'Schluckauf',
            'Kontrakturen',
            'Psychomotorische Beeinträchtigungen',
            'Dyspnoe',
            'Apnoe',
            'Husten',
            'Rasseln',
            'Hypersekretion',
            'Hämoptoe',

        ],

        'Hämatopoese und Blutgefäße' => [
            'Hämatome'
        ],
        'Atemwege' => [
            'Petechnien',
            'Anämie',
            'Leukozytopenie',
            'Thrombozytopenie',
            'Durchblutungsstörung',
            'Manifestierte Blutung',
            'Fieber',
            'Hypothermie',
            'Temperaturschwankungen',
            'Temperaturregulationsstörung'

        ],

        'Emotionen' => [
            'Aggressives Verhalten',
            'Angst',
            'Ansannung',
            'Depressive Verstimmung',
            'Eifersucht',
            'Hilflosigkeit',
            'Perspektivlosigkeit',
            'Schuld',
            'Traurigkeit',
            'Unsicherheit',
            'Unzufriedenheit',
            'Wut'

        ]

        ];
        return $retValue;
    }

    public static function get_verfahrengruppen(){
        $retValue = [
            'Physikalisch',
            'Psychologisch',
            'Komplementär'
        ];
        return $retValue;
    }


    public static function getPatientInterventionsByType($ipid, $type){
        $drop = Doctrine_Query::create()
            ->select('*')
            ->from('Interventions')
            ->where("ipid=?", $ipid)
            ->andWhere("typ=?", $type )
            ->andWhere("isdelete=0")
            ->orderBy('create_date', 'DESC')
        ;
        //print_r($drop->getSqlQuery());
        $droparray = $drop->fetchArray();
        return $droparray;

    }

    /**
     * @return mixed
     * i don't understand why $intervention->last returns not the field value but the whole interventions instance, that's why i wrote this method - elena
     */
    public function get_field_last(){
        $drop = Doctrine_Query::create()
            ->select('*')
            ->from('Interventions')
            ->where("id=?", $this->id)
            ->limit(1)
        ;
        $droparray = $drop->fetchArray();
        return $droparray[0]['last'];

    }

    public function deleteIntervention(){
        $doctrineOperation = Doctrine_Query::create()
            ->update("Interventions")
            ->set('isdelete',1)
            ->where("id=?", $this->id);
        $doctrineOperation->execute();

    }


}