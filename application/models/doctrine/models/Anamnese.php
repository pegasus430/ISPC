<?php
//ISPC-2694, elena, 11.12.2020

/**
 * Class Anamnese
 */
class Anamnese extends BaseAnamnese
{
    /**
     * for table generating purposes only
     * helpful if the table have more than 10 fields
     *
     * @return string
     * @throws Doctrine_Export_Exception
     * @throws Doctrine_Table_Exception
     */
    public function getSql()
    {

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql);
    }

    public function getLastBlockValues ( $ipid)
    {

        $block_sql = Doctrine_Query::create()
            ->select('*')
            ->from('Anamnese')
            ->where('ipid LIKE "' . $ipid . '"')
            ->andWhere('isdelete = 0')
            ->orderBy('id DESC')
            ->limit(1); //maybe we have forms with more than 100 entries

        $block_array = $block_sql->fetchArray();

        return $block_array;

    }


    public static function getGroups()
    {
        $retValue = [
            /*
            'childhood_diseases' => [
                'Rotaviren' => [
                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (3/3 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (2/3 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/3 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (3/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Varizellen' => [
                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (2/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Masern' => [
                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (2/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/2 Impfungen erhalten)' => 'checkbox',
                    'Standardimpfung (ab 18. Lebensjahr) erhalten' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Mumps' => [
                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (2/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Röteln' => [
                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (2/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Pertussis' => [
                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (4/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (2/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (3/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Diphterie' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (4/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (2/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (3/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Auffrischimpfung (5.-6. Lebensjahr) erhalten' => 'checkbox',
                    'Auffrischimpfung (9.-16. Lebensjahr) erhalten' => 'checkbox',
                    'Auffrischimpfung (ab 18. Lebensjahr) erhalten' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Tetanus' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (4/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (2/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (3/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Auffrischimpfung (5.-6. Lebensjahr) erhalten' => 'checkbox',
                    'Auffrischimpfung (9.-16. Lebensjahr) erhalten' => 'checkbox',
                    'Auffrischimpfung (ab 18. Lebensjahr) erhalten' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],

                'HIB' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (4/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (2/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (3/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Polio' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (4/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (2/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (3/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Pneumokokken' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (3/3 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/3 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (2/3 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Meningokokken' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (1/1 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Hepatitis A' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (2/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],

                'Hepatitis B' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (4/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (2/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (3/4 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Tuberkulose' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Sonstiges' => 'textarea'
                ],
                'HPV - Humane Papillomviren' => [

                    'Aktuell erkrankt' => 'checkbox',
                    'In Vergangenheit erkrankt' => 'checkbox_details',
                    'Nicht/noch nie erkrankt' => 'checkbox',
                    'Grundimmunisierung vollständig (2/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisierung unvollständig (1/2 Impfungen erhalten)' => 'checkbox',
                    'Grundimmunisieurung noch nicht begonnen' => 'checkbox',
                    'Impfung wird abgelehnt' => 'checkbox',
                    'Impfung nicht möglich' => 'checkbox',
                    'Letzte Impfung' => 'datepicker',
                    'Nächste Impfung' => 'datepicker',
                    'Sonstiges' => 'textarea'
                ],
                'Eigener Text' => 'textarea'
            ],*/
            'birth_anamnese' => [
                'Länge bei Geburt' => ['field' => 'text', 'measure' => 'cm'],
                'Gewicht bei Geburt' => ['field' => 'text', 'measure' => 'g'],
                'Kopfumfang bei Geburt' => ['field' => 'text', 'measure' => 'cm'],
                'Schwangerschaftsverlauf' => ['field' => 'textarea'],
                'Schwangerschaftsdauer' => ['field' => 'text', 'measure' => 'SSW'],
                'Geburtsverlauf' => ['field' => 'textarea'],
                'Apgar' => ['field' => 'textarea'],
                'Nabelschnur-pH' => ['field' => 'textarea'],
                'Mehrling' => ['field' => 'radio', 'options' => [0 => ['label' => 'Ja'], 1 => ['label' => 'Nein'], 2 => ['label' => 'Sonstiges', 'action' => 'textarea']]]

            ],
            'development_anamnese' => [
                'Allgemeiner Entwicklungsstand' => ['field' => 'radio', 'options' => [0 => ['label' => ' Altersentsprechend'], 1 => ['label' => 'Verzögert'], 2 => ['label' => 'Sonstiges', 'action' => 'textarea']]],

                'Besonderheiten/ Bemerkungen' => ['field' => 'textarea'],

                'Fixieren' => ['field' => 'textarea'],

                'Greifen' => ['field' => 'textarea'],
                'Kopfhalten' => ['field' => 'textarea'],
                'Sitzen' => ['field' => 'textarea'],
                'Sprechen' => ['field' => 'textarea'],
                'Laufen' => ['field' => 'textarea'],
                'Sauber' => ['field' => 'radio', 'options' => [0 => ['label' => 'Ja'], 1 => ['label' => 'Nein'], 2 => ['label' => 'Sonstiges', 'action' => 'textarea']]],
                'Zahnungsbeginn' => ['field' => 'text'],

            ],


        ];
        return $retValue;
    }

}