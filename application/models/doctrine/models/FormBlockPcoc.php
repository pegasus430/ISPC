<?php
/**
 * Class FormBlockPcoc
 * IM-147
 * Maria:: Migration CISPC to ISPC 22.07.2020
 * Update whole File for TODO-4163
 */
Doctrine_Manager::getInstance()->bindComponent('FormBlockPcoc', 'MDAT');

class FormBlockPcoc extends BaseFormBlockPcoc
{
    public static $sections = [
        'problems' => 'Integrated Palliative Care Outcome Scale - IPOS Hauptprobleme',
        'ipos' => 'Integrated Palliative Care Outcome Scale - IPOS Symptombelastung',
        'pcpss' => 'Palliative Problem- und Symptomstärke - PCPSS',
        'nps' => 'Neurologisch/psychiatrische Symptome (Fremdeinschätzung), in den letzten 24 Stunden oder seit der letzten Einschätzung',
        'phase' => 'Palliativphase',
        'psysoz' => 'Integrated Palliative Care Outcome Scale - IPOS - Psychosoziale Items',
        'akps' => 'Australian-modified Karnofsky Performance Status - AKPS',
        'barthel' => 'Barthel Index',
    ];


    public static $itemclasses = [
        'phase' => [
            1 => ['disp' => 'stabil', 'color' => 'green', 'icon' => 'icons/patient_status_icon_green.png', 'shortdisp2' => 'stabil'],
            2 => ['disp' => 'sich verschlechternd', 'color' => 'yellow', 'icon' => 'icons/patient_status_icon_yellow.png', 'shortdisp2' => 'ver&shy;schlech&shy;ternd'],
            3 => ['disp' => 'instabil', 'color' => 'red', 'icon' => 'icons/patient_status_icon_red.png', 'shortdisp2' => 'instabil'],
            4 => ['disp' => 'sterbend', 'color' => 'black', 'icon' => 'icons_system/patient_status_icon_black.png', 'shortdisp2' => 'sterbend'],
            5 => ['disp' => 'verstorben/trauer', 'color' => 'grey', 'icon' => 'icons/is_dead_icon.png', 'shortdisp2' => 'trauer'],
        ],
        'akps' => [
            '' => ['disp' => 'bitte auswählen', 'icon' => 'icons_system/karnofsky_100.png', 'color' => 'grey'],
            '100' => ['disp' => '100 - Keine Beschwerden', 'icon' => 'icons_system/karnofsky_100.png', 'color' => 'darkgreen'],
            '90' => ['disp' => '90 - Normale Aktivität möglich, kaum Symptome', 'icon' => 'icons_system/karnofsky_90.png', 'color' => 'lightgreen'],
            '80' => ['disp' => '80 - Normale Aktivität möglich, deutliche Symptome', 'icon' => 'icons_system/karnofsky_80.png', 'color' => 'yellowgreen'],
            '70' => ['disp' => '70 - Selbstversorgung', 'icon' => 'icons_system/karnofsky_70.png', 'color' => 'yellow'],
            '60' => ['disp' => '60 - Einige Hilfestellung nötig', 'icon' => 'icons_system/karnofsky_60.png', 'color' => 'yellow'],
            '50' => ['disp' => '50 - Häufige Hilfe', 'icon' => 'icons_system/karnofsky_50.png', 'color' => 'orange'],
            '40' => ['disp' => '40 - Mehr als 50% bettlägerig', 'icon' => 'icons_system/karnofsky_40.png', 'color' => 'darkorange'],
            '30' => ['disp' => '30 - Fast komplett bettlägerig', 'icon' => 'icons_system/karnofsky_30.png', 'color' => 'orangered'],
            '20' => ['disp' => '20 - Komplett bettlägerig', 'icon' => 'icons_system/karnofsky_20.png', 'color' => 'red'],
            '10' => ['disp' => '10 - Komatös', 'icon' => 'icons_system/karnofsky_10.png', 'color' => 'deeppink'],
            '1' => ['disp' => '0 - Tod', 'icon' => 'icons_system/karnofsky_0.png', 'color' => 'black'],
        ],
        'ipos' => [
            1 => ['disp' => 'gar nicht', 'color' => 'green', 'shortdisp' => '0', 'shortdisp2' => 'gar nicht'],
            2 => ['disp' => 'ein wenig', 'color' => 'yellow', 'shortdisp' => '1', 'shortdisp2' => 'ein wenig'],
            3 => ['disp' => 'mäßig', 'color' => 'orange', 'shortdisp' => '2', 'shortdisp2' => 'mäßig'],
            4 => ['disp' => 'stark', 'color' => 'red', 'shortdisp' => '3', 'shortdisp2' => 'stark'],
            5 => ['disp' => 'extrem stark', 'color' => 'deeppink', 'shortdisp' => '4', 'shortdisp2' => 'extrem stark'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.'],
        ],
        'iposb' => [
            1 => ['disp' => 'immer', 'color' => 'green', 'shortdisp' => '0', 'shortdisp2' => 'immer'],
            2 => ['disp' => 'meistens', 'color' => 'yellow', 'shortdisp' => '1', 'shortdisp2' => 'meistens'],
            3 => ['disp' => 'manchmal', 'color' => 'orange', 'shortdisp' => '2', 'shortdisp2' => 'manchmal'],
            4 => ['disp' => 'selten', 'color' => 'red', 'shortdisp' => '3', 'shortdisp2' => 'selten'],
            5 => ['disp' => 'gar nicht', 'color' => 'deeppink', 'shortdisp' => '4', 'shortdisp2' => 'gar nicht'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.'],
        ],
        'iposc' => [
            1 => ['disp' => 'Probleme angegangen / Keine Probleme', 'color' => 'green', 'shortdisp' => '0', 'shortdisp2' => 'keine Probleme'],
            2 => ['disp' => 'Probleme größtenteils angegangen', 'color' => 'yellow', 'shortdisp' => '1', 'shortdisp2' => 'größtenteils angegangen'],
            3 => ['disp' => 'Probleme teilweise angegangen', 'color' => 'orange', 'shortdisp' => '2', 'shortdisp2' => 'teilw. angegangen'],
            4 => ['disp' => 'Probleme kaum angegangen', 'color' => 'red', 'shortdisp' => '3', 'shortdisp2' => 'kaum angegangen'],
            5 => ['disp' => 'Probleme nicht angegangen', 'color' => 'deeppink', 'shortdisp' => '4', 'shortdisp2' => 'nicht angegangen'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.'],
        ],
        'pcpss' => [
            1 => ['disp' => 'Problem nicht vorhanden', 'color' => 'green', 'shortdisp' => '0', 'shortdisp2' => 'nicht vorh.'],
            2 => ['disp' => 'leicht', 'color' => 'yellow', 'shortdisp' => '1', 'shortdisp2' => 'leicht'],
            3 => ['disp' => 'mäßig', 'color' => 'orange', 'shortdisp' => '2', 'shortdisp2' => 'mäßig'],
            4 => ['disp' => 'stark', 'color' => 'red', 'shortdisp' => '3', 'shortdisp2' => 'stark'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.'],
        ],
        'nps' => [
            1 => ['disp' => 'Problem nicht vorhanden', 'color' => 'green', 'shortdisp' => '0', 'shortdisp2' => 'nicht vorh.'],
            2 => ['disp' => 'leicht', 'color' => 'yellow', 'shortdisp' => '1', 'shortdisp2' => 'leicht'],
            3 => ['disp' => 'mäßig', 'color' => 'orange', 'shortdisp' => '2', 'shortdisp2' => 'mäßig'],
            4 => ['disp' => 'stark', 'color' => 'red', 'shortdisp' => '3', 'shortdisp2' => 'stark'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.'],
        ],
        'barthel1' => [
            0 => ['disp' => '', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Inkontinent (oder ist auf die Gabe von Einläufen angewiesen)', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Inkontinent'],
            2 => ['disp' => '1 - Gelegentlich inkontinent (höchstens 1 mal pro Woche)', 'color' => 'yellow', 'shortdisp' => 'o', 'shortdisp2' => 'Gelegentlich inkontinent'],
            3 => ['disp' => '2 - Kontinent', 'color' => 'green', 'shortdisp' => '+', 'shortdisp2' => 'Kontinent'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
        'barthel2' => [
            0 => ['disp' => '', 'color' => 'green', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Inkontinent oder unfähig einen liegenden Blasenkatheter selbst zu versorgen', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Inkontinent'],
            2 => ['disp' => '1 - Gelegentlich inkontinent (höchstens 1 mal pro 24 Stunden)', 'color' => 'yellow', 'shortdisp' => 'o', 'shortdisp2' => 'Gelegentlich inkontinent'],
            3 => ['disp' => '2 - Kontinent (über mindestens 7 Tage)', 'color' => 'green', 'shortdisp' => '+', 'shortdisp2' => 'Kontinent'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
        'barthel3' => [
            0 => ['disp' => '', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Benötigt Hilfe bei der eigenen Körperpflege', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Benötigt Hilfe'],
            2 => ['disp' => '1 - Unabhängig beim Gesicht waschen, Haare kämmen, Zähne putzen und Rasieren', 'color' => 'green', 'shortdisp' => '+', 'shortdisp2' => 'Unabhängig'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
        'barthel4' => [
            0 => ['disp' => '', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Abhängig', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Abhängig'],
            2 => ['disp' => '1 - Benötigt einige Hilfe, kann aber einige Tätigkeiten alleine ausführen', 'color' => 'yellow', 'shortdisp' => 'o', 'shortdisp2' => 'Benötigt Hilfe'],
            3 => ['disp' => '2 - Unabhängig (beim Hinsetzen und Aufstehen, Aus- und Anziehen und Abwischen)', 'color' => 'green', 'shortdisp' => '+', 'shortdisp2' => 'Unabhängig'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
        'barthel5' => [
            0 => ['disp' => '', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Kann nicht essen', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Kann nicht essen'],
            2 => ['disp' => '1 - Benötigt Hilfe beim Butter aufstreichen etc.', 'color' => 'yellow', 'shortdisp' => 'o', 'shortdisp2' => 'Benötigt Hilfe'],
            3 => ['disp' => '2 - Selbstständig (Essen steht in Reichweite)', 'color' => 'green', 'shortdisp' => '+', 'shortdisp2' => 'Selbstständig'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
        'barthel6' => [
            0 => ['disp' => '', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Kann Lagewechsel nicht durchführen - kein Gleichgewicht beim Sitzen', 'color' => 'red', 'shortdisp' => '--', 'shortdisp2' => 'kann nicht sitzen'],
            2 => ['disp' => '1 - Benötigt große körperliche Unterstützung (von einer oder zwei Personen), kann sitzen', 'color' => 'orange', 'shortdisp' => '-', 'shortdisp2' => 'Benötigt große Unterstützung'],
            3 => ['disp' => '2 - Benötigt geringe körperliche oder verbale Unterstützung', 'color' => 'yellow', 'shortdisp' => 'o', 'shortdisp2' => 'Benötigt geringe Unterstützung'],
            4 => ['disp' => '3 - Unabhängig', 'color' => 'green', 'shortdisp' => '+', 'shortdisp2' => 'Unabhängig'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
        'barthel7' => [
            0 => ['disp' => '', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Nicht mobil', 'color' => 'red', 'shortdisp' => '--', 'shortdisp2' => 'Nicht mobil'],
            2 => ['disp' => '1 - Unabhängig im Rollstuhl (einschließlich Manövrieren um Ecken etc.)', 'color' => 'orange', 'shortdisp' => '-', 'shortdisp2' => 'Unabhängig im Rollstuhl'],
            3 => ['disp' => '2 - Geht mit der Hilfe einer Person (verbale oder körperliche Unterstützung)', 'color' => 'yellow', 'shortdisp' => 'o', 'shortdisp2' => 'mit Hilfe'],
            4 => ['disp' => '3 - Unabhängig (kann aber Hilfsmittel, z.B. Stock, benutzen)', 'color' => 'green', 'shortdisp' => '+', 'shortdisp2' => 'Unabhängig'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
        'barthel8' => [
            0 => ['disp' => '', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Abhängig', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Abhängig'],
            2 => ['disp' => '1 - Benötigt Hilfe, kann sich jedoch etwa zur Hälfte an- und ausziehen', 'color' => 'yellow', 'shortdisp' => 'o', 'shortdisp2' => 'Benötigt Hilfe'],
            3 => ['disp' => '2 - Unabhängig (einschließlich Knöpfe, Reißverschlüsse und Schnürsenkel etc.)', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Unabhängig'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
        'barthel9' => [
            0 => ['disp' => '', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Kann keine Treppen steigen', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Kann keine Treppen steigen'],
            2 => ['disp' => '1 - Benötigt Hilfe (verbale oder körperliche Unterstützung oder Unterstützung duch Hilfsmittel)', 'color' => 'yellow', 'shortdisp' => 'o', 'shortdisp2' => 'Benötigt Hilfe'],
            3 => ['disp' => '2 - Unabhängig beim Treppauf- und absteigen', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Unabhängig'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
        'barthel10' => [
            0 => ['disp' => '', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => '?'],
            1 => ['disp' => '0 - Abhängig', 'color' => 'red', 'shortdisp' => '-', 'shortdisp2' => 'Abhängig'],
            2 => ['disp' => '1 - Unabhängig (schließt auch das Duschen ein)', 'color' => 'green', 'shortdisp' => '+', 'shortdisp2' => 'Unabhängig'],
            -1 => ['disp' => 'nicht beurteilbar', 'color' => 'grey', 'shortdisp' => '?', 'shortdisp2' => 'n.b.']
        ],
    ];


    public static $items = [
        'phase' => [
            'phase_phase' => ['long' => 'Palliativphase', 'short' => 'Phase', 'itemclass' => 'phase'],
        ],
        'akps' => [
            'akps_akps' => ['long' => 'AKPS', 'short' => 'AKPS', 'itemclass' => 'akps'],
        ],
        'ipos' => [
            'ipos2a' => ['long' => 'Schmerzen', 'short' => 'Schmerz', 'itemclass' => 'ipos'],
            'ipos2b' => ['long' => 'Atemnot', 'short' => 'Atemnot', 'itemclass' => 'ipos'],
            'ipos2c' => ['long' => 'Schwäche oder fehlende Energie', 'short' => 'Schw.', 'itemclass' => 'ipos'],
            'ipos2d' => ['long' => 'Übelkeit', 'short' => 'Übelk.', 'itemclass' => 'ipos'],
            'ipos2e' => ['long' => 'Erbrechen', 'short' => 'Erbr.', 'itemclass' => 'ipos'],
            'ipos2f' => ['long' => 'Appetitlosigkeit', 'short' => 'Appetitl.', 'itemclass' => 'ipos'],
            'ipos2g' => ['long' => 'Verstopfung', 'short' => 'Obstip.', 'itemclass' => 'ipos'],
            'ipos2h' => ['long' => 'Mundtrockenheit oder schmerzhafter Mund', 'short' => 'Mund', 'itemclass' => 'ipos'],
            'ipos2i' => ['long' => 'Schläfrigkeit', 'short' => 'Schläfr.', 'itemclass' => 'ipos'],
            'ipos2j' => ['long' => 'eingeschränkte Mobilität', 'short' => 'Mobilität', 'itemclass' => 'ipos'],
        ],
        'nps' => [
            'nps_verwirrtheit' => ['long' => 'Verwirrtheit', 'short' => 'Verw.', 'itemclass' => 'nps'],
            'nps_unruhe' => ['long' => 'Unruhe', 'short' => 'Unruhe', 'itemclass' => 'nps'],
        ],
        'pcpss' => [
            'pcpss_pain' => ['long' => 'Schmerzen', 'short' => 'Schmerz', 'itemclass' => 'pcpss'],
            'pcpss_other' => ['long' => 'Andere Symptome', 'short' => 'Sympt', 'itemclass' => 'pcpss'],
            'pcpss_psy' => ['long' => 'Psychisch/Spirituell', 'short' => 'Psych', 'itemclass' => 'pcpss'],
            'pcpss_rel' => ['long' => 'Angehörige', 'short' => 'Angeh.', 'itemclass' => 'pcpss'],
        ],
        'psysoz' => [
            'ipos3' => ['long' => 'War der Patient wegen seiner Erkrankung oder Behandlung besorgt oder beunruhigt?', 'short' => 'Patient besorgt', 'itemclass' => 'ipos'],
            'ipos4' => ['long' => 'Waren die Familie/ Freunde des Patienten seinetwegen besorgt oder beunruhigt?', 'short' => 'Umfeld besorgt', 'itemclass' => 'ipos'],
            'ipos5' => ['long' => 'Denken Sie, dass er traurig bedrückt war?', 'short' => 'Patient traurig', 'itemclass' => 'ipos'],
            'ipos6' => ['long' => 'Denken Sie, dass er im Frieden mit sich selbst war?', 'short' => 'Patient im Frieden mit sich selbst', 'itemclass' => 'iposb'],
            'ipos7' => ['long' => 'Konnte der Patient seine Gefühle mit seiner Familie oder seinen Freunden teilen, so viel wie er wollte?', 'short' => 'Patient konnte Gefühle teilen', 'itemclass' => 'iposb'],
            'ipos8' => ['long' => 'Hat der Patient so viele Informationen erhalten, wie er wollte?', 'short' => 'Patient hat Informationen', 'itemclass' => 'iposb'],
            'ipos9' => ['long' => 'Wurden in den letzten 3 Tagen praktische Probleme angegangen, die Folge seiner Erkrankung sind (z.B. finanzieller oder persönlicher Art)?', 'short' => 'Persönliche Probleme wurden angegangen', 'itemclass' => 'iposc'],
        ],
        'barthel' => [
            'barthel1' => ['long' => '1. Kontrolle des Stuhlgangs', 'short' => 'Stuhlgangkontrolle', 'itemclass' => 'barthel1'],
            'barthel2' => ['long' => '2. Blasenkontrolle', 'short' => 'Blasenkontrolle', 'itemclass' => 'barthel2'],
            'barthel3' => ['long' => '3. Körperpflege', 'short' => 'Körperpflege', 'itemclass' => 'barthel3'],
            'barthel4' => ['long' => '4. Toilettenbenutzung', 'short' => 'Toilettenbenutzung', 'itemclass' => 'barthel4'],
            'barthel5' => ['long' => '5. Essen', 'short' => 'Essen', 'itemclass' => 'barthel5'],
            'barthel6' => ['long' => '6. Lagewechsel', 'short' => 'Lagewechsel', 'itemclass' => 'barthel6'],
            'barthel7' => ['long' => '7. Fortbewegung', 'short' => 'Fortbewegung', 'itemclass' => 'barthel7'],
            'barthel8' => ['long' => '8. An- und Ausziehen', 'short' => 'An- und Ausziehen', 'itemclass' => 'barthel8'],
            'barthel9' => ['long' => '9. Treppensteigen', 'short' => 'Treppensteigen', 'itemclass' => 'barthel9'],
            'barthel10' => ['long' => '10. Baden', 'short' => 'Baden', 'itemclass' => 'barthel10'],
        ]

    ];


    public static $charts_icons = [
        '' => ['disp' => 'Kreis (Voreinstellung)'],
        't1' => ['disp' => 'Dreieck', 'shape' => 'triangle', 'size' => 7],
        't2' => ['disp' => 'Dreieck blau', 'shape' => 'triangle', 'size' => 5, 'color' => 'blue'],
        't3' => ['disp' => 'Dreieck schwarz', 'shape' => 'triangle', 'size' => 5, 'color' => 'black'],
        'q1' => ['disp' => 'Quadrat', 'shape' => 'square', 'size' => 7],
        'q2' => ['disp' => 'Quadrat blau', 'shape' => 'square', 'size' => 5, 'color' => 'blue'],
        'q3' => ['disp' => 'Quadrat schwarz', 'shape' => 'square', 'size' => 5, 'color' => 'black'],
        'd1' => ['disp' => 'Raute', 'shape' => 'diamond', 'size' => 7],
        'd2' => ['disp' => 'Raute blau', 'shape' => 'diamond', 'size' => 5, 'color' => 'blue'],
        'd3' => ['disp' => 'Raute schwarz', 'shape' => 'diamond', 'size' => 5, 'color' => 'black'],
    ];


    public static function get_last_ipos_adds($ipid)
    {
        $SQL = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockPcoc')
            ->where('ipid=?', $ipid)
            ->andWhere('isdelete=0')
            ->andwhere('ipos_enabled=1')
            ->orderBy('misc_date DESC')
            ->limit(1);

        $block = $SQL->fetchArray();
        if ($block) {
            $out = $block[0]['ipos_add'];
            $out = json_decode($out, 1);
            return $out;
        } else {
            return [];
        }

    }

    //IM-153
    public static function get_actual_status($ipid)
    {
        $SQL = Doctrine_Query::create()
            ->select('phase_phase, misc_date')
            ->from('FormBlockPcoc')
            ->where('ipid=?', $ipid)
            ->andWhere('isdelete=0')
            ->orderBy('misc_date DESC')
            ->limit(1);
        $block = $SQL->fetchArray();
        if ($block) {
            $out = $block[0];
            $out['date_verb'] = date('d.m.Y H:i', strtotime($out['misc_date']));
            return $out;
        } else {
            $out = ['phase_phase' => 1, 'misc_date' => '2000-01-01 00:00:00', 'date_verb' => 'bisher noch keine Erhebung'];
            return $out;
        }
    }

    //IM-153
    public static function get_patients_chart($ipid, $period)
    {

        //$always_all=ClientConfig::getConfigOrDefault(0, 'config_pcoc_allchart');
        $config_pcoc_charticon = ClientConfig::getConfigOrDefault(0, 'config_pcoc_charticon');
        $config_pcoc_chartmode = ClientConfig::getConfigOrDefault(0, 'config_pcoc_chartmode');


        $iconmap = [];
        if (isset($config_pcoc_charticon) && count($config_pcoc_charticon)) {
            foreach ($config_pcoc_charticon as $k => $v) {
                $iconmap[$k] = FormBlockPcoc::$charts_icons[$v];

            }
        }


        $sql_period_params = array();

        if ($period) {
            $sql_period = ' (DATE(misc_date) != "0000-00-00" AND misc_date BETWEEN ? AND ? ) ';
            $sql_period2 = ' (DATE(misc_date) != "0000-00-00" AND misc_date < ? ) ';

            $sql_period_params = array($period['start'], $period['end']);
            $sql_period_params2 = array($period['start']);
        } else {
            $sql_period = ' DATE(misc_date) != "0000-00-00"  ';
        }

        $SQL = Doctrine_Query::create()
            ->select('id, misc_date')
            ->from('FormBlockPcoc')
            ->where('ipid=?', $ipid)
            ->andWhere('isdelete=0')
            ->andWhere('pcoc_full=1')
            ->orderBy('misc_date DESC')
            ->limit(1);
        if (!empty($sql_period)) {
            $SQL->andWhere($sql_period2, $sql_period_params2);
        }

        $first = $SQL->fetchArray();

        if ($first) {
            $first = $first[0];
            $sql_period_params[0] = $first['misc_date'];
            //go further back to have a start with an entry with pcoc_full

        }

        $SQL = Doctrine_Query::create()
            //->select('phase_phase, misc_date, ipos2a, ipos2b, ipos2c, ipos2d, ipos2e, ipos2f, ipos2g, ipos2h, ipos2i, ipos2j,pcoc_full, ipos_add')
            ->select('*')
            ->from('FormBlockPcoc')
            ->where('ipid=?', $ipid)
            ->andWhere('isdelete=0')
            ->orderBy('misc_date ASC');

        if (!empty($sql_period)) {
            $SQL->andWhere($sql_period, $sql_period_params);
        }

        $block = $SQL->fetchArray();


        if (count($block) && count($iconmap)) {
            $formids = array_column($block, 'contact_form_id');

            $SQL = Doctrine_Query::create()
                ->select('c.id, c.form_type')
                ->from('ContactForms c')
                ->whereIn('c.id', $formids);
            $cfid_to_info = $SQL->fetchArray();

            $form_to_type = array_combine(array_column($cfid_to_info, 'id'), array_column($cfid_to_info, 'form_type'));
        }

        $more_items = [];
        foreach ($block as $k => $hv) {
            $more = $hv['ipos_add'];
            if (strlen($more)) {
                $more = json_decode($more, 1);
                if (is_array($more) && count($more)) {
                    foreach ($more as $pair) {
                        if (count($pair) == 2 && strlen($pair['key'])) {
                            if (!in_array($pair['key'], $more_items)) {
                                $more_items[] = $pair['key'];
                            }
                            $block[$k]['more'][$pair['key']] = $pair['value'];
                        }
                    }
                }
            }
        }

        foreach ($block as $k => $hv) {
            foreach ($more_items as $i => $item) {
                if (!isset($block[$k]['more'][$item])) {
                    $block[$k]['more'][$item] = 0;
                }

                $iposkey = 'ipos_add_' . $i;
                $block[$k][$iposkey] = $block[$k]['more'][$item];
                $block[$k]['more'][$item] = $iposkey;
            }
        }

        $out = [];
        $full = [];
        if ($block) {
            foreach ($block as $row) {
                $type = $form_to_type[$row['contact_form_id']];
                if ($iconmap[$type]) {
                    $row['icon'] = $iconmap[$type];
                }

                $insert = 1;
                if (isset($config_pcoc_chartmode[$type]) && $config_pcoc_chartmode[$type] == 1) {
                    //this form is set to only show changes if nothing else like pcoc_full makes this special
                    $insert = 0;
                }

                if ($insert || $row['pcoc_full'] || !($row['shortstatus']) || count($out) < 1) {
                    $out[] = $row;
                } else {
                    $last = end($full);

                    $new = [];
                    foreach ($row as $k => $v) {
                        if ($k == 'misc_date' || !($row[$k] == $last[$k])) {
                            $new[$k] = $row[$k];
                        }
                    }

                    $out[] = $new;
                }
                $full[] = $row;
            }
        }

        return $out;

    }


    /**
     * @param $ipid
     * @param int $count
     * @return array|mixed
     * Returns the last valid status entry from formblockpcoc
     * if count>1 returns last $count many entries inside _more-Key
     */
    public static function get_last_values($ipid, $count = 1, $only = "")
    {
        $SQL = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockPcoc')
            ->where('ipid=?', $ipid)
            ->andWhere('isdelete=0')
            ->orderBy('misc_date DESC, id DESC');
        //->limit($count);

        if ($only == 'full + assessment') {
            $SQL->andWhere('(pcoc_assessment=1 OR pcoc_full=1)');
        }
        if ($only == 'no_short + assessment') {
            $SQL->andWhere('(pcoc_assessment=1 OR pcoc_full=1 OR shortstatus=0)');
        }
        $blocks = $SQL->fetchArray();


        //Make sure, the most recent entry is always in
        $SQL = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockPcoc')
            ->where('ipid=?', $ipid)
            ->andWhere('isdelete=0')
            ->orderBy('misc_date DESC, id DESC')
            ->limit(1);
        $lastblock = $SQL->fetchArray();

        if ($lastblock) {
            if (count($blocks)) {
                if ($lastblock[0]['id'] == $blocks[0]['id']) {
                    unset($blocks[0]);
                }
                array_unshift($blocks, $lastblock[0]);
            } else {
                $blocks = $lastblock;
            }
        }

        if ($blocks && count($blocks)) {
            if ($count === 1) {
                return $blocks[0];
            } else {
                $logininfo = new Zend_Session_Namespace('Login_Info');
                $opsconfig = ClientConfig::getConfig($logininfo->clientid, 'opsconfig');
                $ba_form_id = $opsconfig['ba_formid'];

                $ba_query = Doctrine_Query::create()
                    ->select('id, date')
                    ->from('ContactForms t')
                    ->where('t.ipid = ?', $ipid)
                    ->andWhere('t.form_type = ?', $ba_form_id);
                $ba_days = $ba_query->fetchArray();
                $ba_ids = array('-1');
                foreach ($ba_days as $ba) {
                    $ba_ids[] = $ba['id'];
                }

                $last_ba = count($blocks);
                $last_change = count($blocks);
                $more_blocks = [];
                //find out phase_change and BA
                foreach ($blocks as $bk => $block) {   //start with newest block
                    $blocks[$bk]['is_ba'] = 0;
                    $blocks[$bk]['phase_change'] = 0;
                    if (($bk + 1) < count($blocks)) {
                        if ($block['phase_phase'] !== $blocks[$bk + 1]['phase_phase']) {
                            $blocks[$bk]['phase_change'] = 1;
                            if ($last_change > $bk) {
                                $last_change = $bk;
                            }
                        }
                    }
                    if (in_array($block['contact_form_id'], $ba_ids)) {
                        $blocks[$bk]['is_ba'] = 1;
                        if ($last_ba > $bk) {
                            $last_ba = $bk;
                        }
                    }

                    if ($bk < $count) {
                        $more_blocks[] = $blocks[$bk];
                    } else {
                        if ($last_ba == $bk) {
                            $more_blocks[] = $blocks[$bk];
                        }
                        if ($last_change == $bk) {
                            $more_blocks[] = $blocks[$bk];
                        }
                    }

                    if ($bk >= $count) {
                        if ($last_change < count($blocks) && $last_ba < count($blocks)) {
                            break;
                        }
                    }
                }

                $all = $blocks[0];
                $all['_more'] = $more_blocks;


                return $all;
            }
        }


        return [];
    }

    public static function get_actual_karnofsky($ipid)
    {
        $SQL = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockPcoc')
            ->where('ipid=?', $ipid)
            ->andWhere('isdelete=0')
            ->andWhere('misc_date>=?', date('Y-m-d 00:00:00'))
            ->andWhere('misc_date<=?', date('Y-m-d 23:59:59'))
            ->orderBy('misc_date DESC');
        $block = $SQL->fetchArray();
        if (count($block)) {
            foreach ($block as $b) {
                if ($b['akps_akps'] > 0) {
                    $b = $b['akps_akps'];
                    if ($b === 1) {
                        $b = 0;
                    }
                    return $b;
                }
            }
        }

        return -1;


    }

    public static function render_iconstatus($data)
    {
        $newview = new Zend_View();
        $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
        $newview->data = $data;
        $html = $newview->render('form_block_pcoc_status.html');

        return $html;
    }

}