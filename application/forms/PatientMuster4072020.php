<?php


/**
 *
 * @author carmen
 *
 * 11.04.2019
 *
 * ISPC-2370 // Maria:: Migration ISPC to CISPC 08.08.2020
 * Maria:: Migration CISPC to ISPC 02.09.2020
 * elena, ISPC-2627 ISPC: new form Krankenbeförderung 2020 20.08.2020, version from carmen with changed
 */
class Application_Form_PatientMuster4072020 extends Pms_Form
{
    protected $_phealthinsurance = null;

    protected $_user = null;

    protected $_multiple_stamps = null;

    protected $_user_stamps = null;

    protected $title_center = null;

    protected $_group_but1 = null;

    protected $_group_but2 = null;

    protected $_group_but3 = null;

    protected $_group_but4 = null;

    protected $_group_but5 = null;

    protected $_group_oth1 = null;

    protected $_group_but6 = null;

    protected $_group_but7 = null;

    protected $_group_oth2 = null;

    protected $_group_but8 = null;

    protected $_group_oth3 = null;

    protected $_group_oth4 = null;

    protected $_group_but9 = null;

    protected $_group_oth5 = null;

    protected $_group_but10 = null;

    protected $_group_oth6 = null;
    protected $_group_oth6_1 = null;

    protected $_group_oth7 = null;

    protected $_group_oth8 = null;

    protected $_munster4_lang = null;

    protected $_forme_mode = null;

    public function __construct($options = null)
    {
        if (isset($options['_phealthinsurance'])) {
            $this->_phealthinsurance = $options['_phealthinsurance'];
            unset($options['_phealthinsurance']);
        }

        if (isset($options['_forme_mode'])) {
            $this->_forme_mode = $options['_forme_mode'];
            unset($options['_forme_mode']);
        }

        $this->_group_but1 = array(
            'name' => 'fees_type',
            'type' => 'radio',
            //'alignment' => 'vertical',
            'butnr' => '2',
            'first_but_pos_left' => '2',
            'first_but_pos_top' => '10',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'x_offset' => array('0', '0'),
            'y_offset' => array('0', '54'),
            'width_dummy' => '37',
            'height_dummy' => '37',
            //'text_dummy' => array('Zuzah-lungs-pflicht', 'Zuzah-lungs-frei'),
            'text_dummy' => array('', ''),
            'font_dummy_text' => '11',
            'has_external_obj' => true,
            'label' => false,
            'has_dummy' => true,
            //'class' => 'rcbyleft'
        );

        $this->_group_but2 = array(
            'name' => 'sick_reason',
            'type' => 'checkbox',
            //'alignment' => 'vertical',
            'butnr' => '3',
            'first_but_pos_left' => '19',
            'first_but_pos_top' => '76',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'x_offset' => array('0', '0', '0'),
            'y_offset' => array('0', '46', '92'),
            'width_dummy' => '26',
            'height_dummy' => '26',
            'label_text' => array('Unfall, Unfallfolge', 'Arbeitsunfall, Berufskrankheit', 'Versorgungsleiden(z.B. BVG)'),
            'label_wrap' => array(false, false, false),
            'x_offset_label' => array('10', '10', '10'),
            'label_width' => array('250', '250', '250'),
            'label_height' => array('25', '25', '25'),
            'has_external_obj' => false,
            'label' => true,
            'has_dummy' => true,
            //'class' => 'rcbyrightv'
        );

        $this->_group_but3 = array(
            'name' => 'travel',
            'type' => 'checkbox',
            'butnr' => '2',
            'first_but_pos_left' => '19',
            'first_but_pos_top' => '210',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'x_offset' => array('0', '138'),
            'y_offset' => array('0', '0'),
            'width_dummy' => '26',
            'height_dummy' => '26',
            'label_text' => array('Hinfahrt', 'Rückfahrt'),
            'x_offset_label' => array('10', '10'),
            'label_width' => array('250', '250'),
            'label_height' => array('25', '25'),
            'font_dummy_text' => '11',
            'has_external_obj' => false,
            'label' => true,
            'has_dummy' => true,
            //'class' => 'rcbyrighto'
        );

        $this->_group_but4 = array(
            'name' => 'treatmentinclinic',
            'type' => 'checkbox',
            //'alignment' => 'horizontal',
            'butnr' => '3',
            'first_but_pos_left' => '68',
            'first_but_pos_top' => '56',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'x_offset' => array('0', '275', '10'),
            'y_offset' => array('0', '0', '0'),
            'width_dummy' => '26',
            'height_dummy' => '26',
            'label_text' => array('voll-/teilstationäre Krankenhausbehandlung', 'vor-/nachstationäre Behandlung', 'ambulante Behandlung bei Merkzeichen "aG", "Bl", '),
            'label_wrap' => array(true, false, false),
            'x_offset_label' => array('10', '10', '10'),
            'label_width' => array('250', '250', '250'),
            'label_height' => array('25', '25', '25'),
            'font_dummy_text' => '11',
            'has_external_obj' => false,
            'label' => true,
            'has_dummy' => true,
            //'class' => 'treatmentinclinic'
        );

        $this->_group_but5 = array(
            'name' => 'otherreason',
            'type' => 'checkbox',
            //'alignment' => 'horizontal',
            'butnr' => '1',
            'first_but_pos_left' => '68',
            'first_but_pos_top' => '95',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'width_dummy' => '26',
            'height_dummy' => '26',
            'label_text' => array('anderer Grund, z.B.'),
            'label_wrap' => array(true),
            'x_offset_label' => array('10'),
            'label_width' => array('250'),
            'label_height' => array('25'),
            'font_dummy_text' => '11',
            'has_external_obj' => false,
            'label' => true,
            'has_dummy' => true,
            //'class' => 'otherreason'
        );

        $this->_group_oth1 = array(
            'name' => array('otherreason'),
            'types' => array('text'),
            /* 'position' => 'absolute',
            'first_but_pos_left' => '348',
            'first_but_pos_top' => '96',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0', */
            'label' => array('Fahrten zu stationären Hospizen'),
            'label_placement' => array(PREPEND),
            'label_wrap' => array(true),
            'x_offset' => array('0'),
            'y_offset' => '0',
        );

        $this->_group_but6 = array(
            'name' => 'high_frequency_treatment',
            'type' => 'checkbox',
            //'alignment' => 'horizontal',
            'butnr' => '2',
            'first_but_pos_left' => '205',
            'first_but_pos_top' => '158',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'x_offset' => array('0', '303'),
            'y_offset' => array('0', '0'),
            'width_dummy' => '26',
            'height_dummy' => '26',
            'label_text' => array('Dialyse, onkol. Chemo oder Strahlentherapie', 'vergleichbarer Ausnahmefall (Begründung erforderlich)'),
            'label_wrap' => array(true, true),
            'x_offset_label' => array('10', '10'),
            'label_width' => array('250', '250'),
            'label_height' => array('25', '25'),
            'font_dummy_text' => '11',
            'has_external_obj' => false,
            'label' => true,
            'has_dummy' => true,
            //'class' => 'high_frequency_treatment'
        );

        $this->_group_but7 = array(
            'name' => 'durable_limited_mobility',
            'type' => 'checkbox',
            //'alignment' => 'horizontal',
            'butnr' => '1',
            'first_but_pos_left' => '205',
            'first_but_pos_top' => '197',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'x_offset' => array('0', '303'),
            'y_offset' => array('0', '0'),
            'width_dummy' => '26',
            'height_dummy' => '26',
            'label_text' => array('Merkzeichen „aG“, „BI“, „H“, Pflege-grad mit dauerhafter Mobilitäts-beeinträchtigung, Pflegegrad 4 oder 5', 'vergleichbare Mobilitätsbeein-trächtigung und Behandlungs-dauer mindestens 6 Monate (Begründung erforderlich)'),
            'label_wrap' => array(true, true),
            'x_offset_label' => array('10', '10'),
            'label_width' => array('250', '250'),
            'label_height' => array('25', '25'),
            'font_dummy_text' => '11',
            'has_external_obj' => false,
            'label' => true,
            'has_dummy' => true,
            //'class' => 'durable_limited_mobility'
        );

        $this->_group_oth2 = array(
            'name' => array('reason_needed_for_transfer'),
            'types' => array('text'),
            /* 'position' => 'absolute',
            'first_but_pos_left' => '138',
            'first_but_pos_top' => '252',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0', */
            'label' => array('Begründung'),
            'label_placement' => array(PREPEND),
            'label_wrap' => array(true),
            'x_offset' => array('0'),
            'y_offset' => '0',
        );

        $this->_group_but8 = array(
            'name' => 'otherreason_needing_approval',
            'type' => 'checkbox',
            //'alignment' => 'horizontal',
            'butnr' => '1',
            'first_but_pos_left' => '68',
            'first_but_pos_top' => '283',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'width_dummy' => '26',
            'height_dummy' => '26',
            'label_text' => array('anderer Grund, der Fahrt mit KTW erfordert(z.b. fachgerechtes Lagern, Tragen, Heben, Begründung unter 3. angeben'),
            'label_wrap' => array(true),
            'x_offset_label' => array('10'),
            'label_width' => array('450'),
            'label_height' => array('25'),
            'font_dummy_text' => '11',
            'has_external_obj' => false,
            'label' => true,
            'has_dummy' => true,
            //'class' => 'otherreason_needing_approval'
        );

        $this->_group_oth3 = array(
            'name' => array('from', 'per_week', 'till'),
            'types' => array('text', 'text', 'text'),
            /* 'position' => 'absolute',
            'first_but_pos_left' => '138',
            'first_but_pos_top' => '250',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0', */
            'label' => array('', '', ''),
            'label_placement' => array('PREPEND', 'APPEND', 'PREPEND'),
            'label_wrap' => array(true, true, true),
            'x_offset' => array('0', '0', '0'),
            'y_offset' => '0',
            'maxlength' => array('6', '1', '6'),
        );

        $this->_group_oth4 = array(
            'name' => array('treatment_location'),
            'types' => array('text'),
            /* 'position' => 'absolute',
            'first_but_pos_left' => '8',
            'first_but_pos_top' => '250',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0', */
            'label' => array(''),
            'label_placement' => array(PREPEND),
            'label_wrap' => array(true),
            'x_offset' => array('0'),
            'y_offset' => '0',
        );

        $this->_group_but9 = array(
            'name' => 'transport_type',
            'type' => 'checkbox',
            //'alignment' => 'horizontal',
            'butnr' => '5',
            'first_but_pos_left' => '40',
            'first_but_pos_top' => '482',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'x_offset' => array('0', '166', '262', '358', '0'),
            'y_offset' => array('0', '0', '0', '0', '46'),
            'width_dummy' => '26',
            'height_dummy' => '26',
            'label_text' => array('Taxi/Mietwagen', 'Roll-stuhll', 'Trage-stuhll', 'liegend', 'KTW da medicinische...'),
            'label_wrap' => array(false, true, true, false, true),
            'label_width' => array('150', '40', '40', '40', '250'),
            'label_height' => array('25', '25', '25', '25', '25'),
            'x_offset_label' => array('10', '10', '10', '10', '10'),
            'font_dummy_text' => '11',
            'has_external_obj' => false,
            'label' => true,
            'has_dummy' => true,
            //'class' => 'high_frequency_treatment'
        );

        $this->_group_oth5 = array(
            'name' => array('transport_type'),
            'types' => array('text'),
            /* 'position' => 'absolute',
            'first_but_pos_left' => '8',
            'first_but_pos_top' => '250',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0', */
            'label' => array(''),
            'label_placement' => array(PREPEND),
            'label_wrap' => array(true),
            'x_offset' => array('0'),
            'y_offset' => '0',
        );
        $this->_group_oth6_1 = array(
            'name' => array('transport_type_more'),
            'types' => array('text'),
            /* 'position' => 'absolute',
            'first_but_pos_left' => '8',
            'first_but_pos_top' => '250',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0', */
            'label' => array(''),
            'label_placement' => array(PREPEND),
            'label_wrap' => array(true),
            'x_offset' => array('0'),
            'y_offset' => '0',
        );


        $this->_group_but10 = array(
            'name' => 'special_transport_type',
            'type' => 'checkbox',
            //'alignment' => 'horizontal',
            'butnr' => '3',
            'first_but_pos_left' => '40',
            'first_but_pos_top' => '585',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0',
            'x_offset' => array('0', '82', '166'),
            'y_offset' => array('0', '0', '0'),
            'width_dummy' => '26',
            'height_dummy' => '26',
            'label_text' => array('RTW', 'NAW/ NEF', 'andere',),
            'label_wrap' => array(false, true, false),
            'label_width' => array('20', '20', '20'),
            'label_height' => array('25', '25', '25'),
            'x_offset_label' => array('10', '10', '10'),
            'font_dummy_text' => '11',
            'has_external_obj' => false,
            'label' => true,
            'has_dummy' => true,
            //'class' => 'high_frequency_treatment'
        );

        $this->_group_oth6 = array(
            'name' => array('special_transport_type'),
            'types' => array('text'),
            /* 'position' => 'absolute',
            'first_but_pos_left' => '8',
            'first_but_pos_top' => '250',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0', */
            'label' => array(''),
            'label_placement' => array(PREPEND),
            'label_wrap' => array(true),
            'x_offset' => array('0'),
            'y_offset' => '0',
        );

        $this->_group_oth7 = array(
            'name' => array('special_transport_type_more'),
            'types' => array('text'),
            /* 'position' => 'absolute',
            'first_but_pos_left' => '8',
            'first_but_pos_top' => '250',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0', */
            'label' => array(''),
            'label_placement' => array(PREPEND),
            'label_wrap' => array(true),
            'x_offset' => array('0'),
            'y_offset' => '0',
        );

        $this->_group_oth8 = array(
            'name' => array('special_transport_type_othermore'),
            'types' => array('text'),
            /* 'position' => 'absolute',
            'first_but_pos_left' => '8',
            'first_but_pos_top' => '250',
            'first_but_pos_right' => '0',
            'first_but_pos_bottom' => '0', */
            'label' => array(''),
            'label_placement' => array(PREPEND),
            'label_wrap' => array(true),
            'x_offset' => array('0'),
            'y_offset' => '0',
        );

        //var_dump($this->_setgrup_but1['name']); exit;
        parent::__construct($options);
        $this->title_center = '<h2></h2>';

        $this->_munster4_lang = $this->translate('munster4_lang');

    }

    public function isValid($data)
    {

        return parent::isValid($data);
    }

    public function getColumnMapping($fieldName, $revers = false)
    {

        //             $fieldName => [ value => translation]
        $overwriteMapping = [
            'question1' => ['severe_anorexia' => 'schwere Anorexie (1)',
                'slight_anorexia' => 'leichte Anorexie (2)',
                'no_anorexia' => 'keine Anorexie (3)',
            ],

        ];


        $values = FormBlockAdverseeventsTable::getInstance()->getEnumValues($fieldName);


        $values = array_combine($values, array_map("self::translate", $values));

        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }

        return $values;

    }

    public function create_form_munster4($options = array(), $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");

        $this->mapSaveFunction($__fnName, "save_form_munster4");

        $this->clearDecorators();
        //$this->addDecorator('HtmlTag', array('tag' => 'table'));
        $this->addDecorator('FormElements');
        //$this->addDecorator('Fieldset', array());
        $this->addDecorator('Form');

        //$this->__setElementsBelongTo($this, $elementsBelongTo);

        $this->addElement('note', 'label_form_title_center', array(
            'value' => $this->title_center,
            'decorators' => array(
                'ViewHelper',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fulldiv center'))
            ),
        ));

        $this->addElement('hidden', 'form_id', array(
            'value' => $options['form_id'] ? $options['form_id'] : null,
            'required' => false,
            'decorators' => array(
                'ViewHelper',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fulldiv'))
            ),
        ));

        unset($options['ipid']);
        unset($options['create_date']);
        unset($options['create_user']);
        unset($options['change_date']);
        unset($options['change_user']);
        unset($options['isdelete']);

        $subtable = new Zend_Form_SubForm();
        $subtable->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
        ));

        //kvheader
        $sf_kvhead = new Application_Form_PatientKvheader(array(
            'elementsBelongTo' => 'header',
            '_patientMasterData' => $this->_patientMasterData,
            '_phealthinsurance' => $this->_phealthinsurance,
            '_user' => $this->_user
        ));

        $kvheader_details_form = $sf_kvhead->_create_form_kvheader()->__toString();

        $external_obj = null;
        $groupbutt = $this->_group_but1;
        if ($groupbutt['has_external_obj']) {
            $external_obj = $kvheader_details_form;
        }
        $elementsBelongTo = 'header';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $groupbutt['name']['value'], 'button', $external_obj);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'style' => 'width: 60%;')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '', 'openOnly' => true)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);

        $groupbutt = $this->_group_but2;
        //$elementsBelongTo = 'header';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $options['header']['header'][$groupbutt['name']]);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'openOnly' => true, 'style' => 'width: 40%;')),
            //array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'')),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);

        $groupbutt = $this->_group_but3;
        //$elementsBelongTo = 'munster4[header]';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $groupbutt['name']['value']);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'closeOnly' => true, 'style' => 'width: 40%;')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '', 'closeOnly' => true)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);


        $this->addSubForm($subtable, 'header');

        $subtable = new Zend_Form_SubForm();
        $subtable->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
        ));

        $groupbutt = $this->_group_but4;
        $elementsBelongTo = 'transfers_not_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']]);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'colspan' => '2')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '',)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);

        $groupbutt = $this->_group_but5;
        //$elementsBelongTo = 'transfers_not_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '',)),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '', 'openOnly' => true)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);

        $groupbutt = $this->_group_oth1;
        //$elementsBelongTo = 'transfers_not_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']], 'other');
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '',)),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '', 'closeOnly' => true)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name'][0] . '_other');

        $groupbutt = $this->_group_but6;
        $elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']]);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'colspan' => '2')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '',)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);

        $groupbutt = $this->_group_but7;
        //$elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']]);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'colspan' => '2')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '',)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);

        $groupbutt = $this->_group_oth2;
        //$elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']], 'other');
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'colspan' => '2')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '')),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name'][0] . '_other');

        $groupbutt = $this->_group_but8;
        //$elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']]);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'colspan' => '2')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '',)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);
        /*  $subtable->addElement('note', 'label_inchide', array(
                'value' => '&nbsp;',
                'decorators' => array(
                        'ViewHelper',
                        //array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class' => '')),
                        array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'', 'closeOnly' => true)),
                        array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
                ),
        )); */
        $this->addSubForm($subtable, 'transfers_reason');

        $subtable = new Zend_Form_SubForm();
        $subtable->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
        ));

        $groupbutt = $this->_group_oth3;
        $elementsBelongTo = 'treatment_info';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']], 'other');
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '')),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name'][0] . '_other');

        $groupbutt = $this->_group_oth4;
        //$elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']], 'other');
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '')),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name'][0] . '_other');

        $this->addSubForm($subtable, 'treatment_info');

        $subtable = new Zend_Form_SubForm();
        $subtable->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
        ));

        $groupbutt = $this->_group_but9;
        $elementsBelongTo = 'transport_info';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']]);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'colspan' => '3')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '',)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);

        $groupbutt = $this->_group_oth5;
        //$elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']], 'other');
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'colspan' => '2')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '', 'openOnly' => true)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name'][0] . '_other');

        $groupbutt = $this->_group_oth6_1;
        //$elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']], 'other');
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '',)),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '', 'closeOnly' => true)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name'][1] . '_other');


        $sf_stamps = new Application_Form_FormUsersStamps(array(
            '_clientModules' => $this->_clientModules,
        ));

        $groupbut_details_form = $sf_stamps->_create_form_userstamps($options['transport_info'][$elementsBelongTo]['users_stamps']);

        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('dtag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'users_stamps'),),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'rowspan' => '4')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '', 'closeOnly' => true)),
        ));

        $subtable->addSubForm($groupbut_details_form, 'users_stamps');

        /* $subtable->addElement('note', 'label_inchide', array(
                'value' => '&nbsp;',
                'decorators' => array(
                        'ViewHelper',
                        //array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class' => '')),
                        array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'', 'rowspan' => '4')),
                        array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
                ),
        )); */

        $groupbutt = $this->_group_but10;
        $elementsBelongTo = 'transport_info';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']]);
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '',)),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '', 'openOnly' => true)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name']);

        $groupbutt = $this->_group_oth6;
        //$elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']], 'other');
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '',)),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '', 'closeOnly' => true)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name'][0] . '_other');



        $groupbutt = $this->_group_oth7;
        //$elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']], 'other');
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'colspan' => '3')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr', 'class' => '',)),
        ));
        $subtable->addSubForm($groupbut_details_form, $groupbutt['name'][0] . '_other');

        $groupbutt = $this->_group_oth8;
        //$elementsBelongTo = 'transfers_needing_approvals';
        $groupbut_details_form = $this->_init_group($groupbutt, $elementsBelongTo, $elementsBelongTo['value'][$groupbutt['name']], 'other');
        $groupbut_details_form->clearDecorators()->setDecorators(array(
            'FormElements',
            array(array('ttag' => 'HtmlTag'), array('tag' => 'table')),
            array(array('tdtag' => 'HtmlTag'), array('tag' => 'td', 'class' => '', 'colspan' => '3')),
            array(array('trtag' => 'HtmlTag'), array('tag' => 'tr',)),
        ));
        //$subtable->addSubForm($groupbut_details_form, $groupbutt['name'][0] . '_other');

        $this->addSubForm($subtable, 'transport_info');

        //add action buttons
        $actions = $this->_create_formular_actions($options['formular'], 'formular');
        $this->addSubform($actions, 'form_actions');

        return $this;


    }

    private function _create_formular_actions($options = array(), $elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators()
            ->setDecorators(array(
                'FormElements',
                array('HtmlTag', array('tag' => 'div', 'class' => 'formular_actions')),
            ));


        if (!is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }

        /* $el = $this->createElement('button', 'button_action', array(
             'type'         => 'submit',
             'value'        => 'save',
             // 	        'content'      => $this->translate('submit'),
             'label'        => $this->translator->translate('kh_save'),
             // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
             'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
             'decorators'   => array('ViewHelper'),

        ));
        $subform->addElement($el, 'save');
     */

        $el = $this->createElement('button', 'button_action', array(
            'type' => 'submit',
            'value' => 'print_pdf',
            // 	        'content'      => $this->translate('submit'),
            'label' => $this->_munster4_lang['generate pdf'],
            // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
            'onclick' => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
            'decorators' => array('ViewHelper'),

        ));
        $subform->addElement($el, 'printpdf');

        $el = $this->createElement('button', 'button_action', array(
            'type' => 'submit',
            'value' => 'print_pdf_and_save',
            // 	        'content'      => $this->translate('submit'),
            'label' => $this->_munster4_lang['generate pdf and save'],
            // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
            'onclick' => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
            'decorators' => array('ViewHelper'),

        ));
        $subform->addElement($el, 'printpdfsave');

        $el = $this->createElement('button', 'button_action', array(
            'type' => 'submit',
            'value' => 'preprint_pdf_and_save',
            // 	        'content'      => $this->translate('submit'),
            'label' => $this->_munster4_lang['generate pre print pdf and save'],
            // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
            'onclick' => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
            'decorators' => array('ViewHelper'),

        ));
        $subform->addElement($el, 'preprintpdfsave');


        return $subform;

    }

    private function _init_group($groupbutt, $elementsBelongTo = null, $saved_values = array(), $type = 'button', $external_obj = null)
    {
        if ($type == 'button') {
            if ($this->_forme_mode) {
                $sf_groupbut = (new Application_Form_FormGroupButton(array(
                    'elementsBelongTo' => $elementsBelongTo,
                    '_setgroup_but' => $groupbutt,
                    '_external_obj' => $external_obj,
                    '_forme_mode' => "without_dummy",
                )));
            } else {
                $sf_groupbut = (new Application_Form_FormGroupButton(array(
                    'elementsBelongTo' => $elementsBelongTo,
                    '_setgroup_but' => $groupbutt,
                    '_external_obj' => $external_obj,
                )));
            }
            $creategroup_fn = '_create_form_groupbutton';
        } else {
            $sf_groupbut = (new Application_Form_FormGroupOther(array(
                'elementsBelongTo' => $elementsBelongTo,
                '_setgroup_oth' => $groupbutt,
                '_external_obj' => $external_obj,
            )));
            $creategroup_fn = '_create_form_groupother';
        }

        $groupbut_details_form = $sf_groupbut->{$creategroup_fn}();

        return $groupbut_details_form;
    }

    public function save_form_munster4($ipid = null, array $data = array())
    {
        /* 		if (empty($ipid)) {
                    throw new Exception('Contact Admin, formular cannot be saved.', 0);
                }
                //print_r($data); exit;
                if($data['form_id'] == '')
                {
                    $data['form_id'] = null;
                }

                //print_r($data); exit;
                $entity = PatientMunster4Table::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['form_id'], $ipid], $data);

                return $entity; */
    }

}

?>
