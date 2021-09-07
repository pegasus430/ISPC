<?php
/**
 * 
 * @author claudiu
 * 
 * initial example with values in /SmartqBundle/3rdParty/SMQ_README.pdf
 * 
 * bmp_V2.4.1.xsd translated as array with english(google) description
 * 
 *
 */
$mpData = [
		/* 
		 * required
		Name: Version number
		Description: Version number of the German National Drug Administration (BMP)
		Filling / Format: xxy
		 */
		'v'=>"021" , //Version number 241 fixed by the xsd
		
		/* 
		 * prohibited
		Name:  Patchnummer ,
		Description: Pathnumber of the underlying BMP (in addition to the version attribute)
		Filling / Format: For each new version, the patch version is initially empty. With the first patch, the patch is set to 0 and incremented by 1 with each additional patch.
		 */
		'p'=>"1",
		
		/* 
		 * required
		Name: Instance ID
		Description: Worldwide unique instance ID for the identification of a BMP expression
		Filling / Format: GUID - Global Unique Identifier (same for all pages of the BMP)
		 */
		'U'=>"B544B6976AB84E3498AA96D8E6FA29C1" ,
		
		/* 
		 * required
		Name: Language / Country Indicator
		Description: Language and country code
		Filling / Format: Language and country codes are indicated with ISO values ​​separated by hyphen ("-")
		 */
		'l'=>"de-DE", 
		
		/*
		 * optional
		 Name: Number of pages
		 Description: Current page of the BMP
		 Validation Criteria: starting with 1; Must be used for multi-page layouts. For a one-sided plan, it must be omitted.
		 */
		'a'=>"1" , 
		
		/*
		 * optional
		 Name: Total number of guests
		 Description: Total number of BMP
		 Validation criteria: Must be used for multi-page plans. For a one-sided plan, it must be omitted.
		 */
		'z'=>"1",
		
		
		
		/*
		 * 1 occurence
		 * Patient
		 */
		'P'=>[
				/* 
				 * required
				Name first Name
				Description: First name of the insured
				 */
				'g'=>"Max",
				
				/* 
				 * required
				Name surname
				Description: Last name of the insured
				 */
				'f'=>"Mustermann" ,
				
				/* 
				 * required
				Name: Date of birth
				Description: Date of birth of the insured - The year of birth must always be filled. In the case of nationals, a logically correct date of birth must always be given. The following applies to foreigners: At least the year of birth must always be indicated. In the case of a foreigner, "00" or "0000" is permissible on the birthday or in the birthday and in the month of birth, if the birthday and the birth month can not be determined.
				Filling / Format: analog VSDM, YYYYMMDD (ISO-8601)
				 */
				
				'b'=>"1940-12-30" ,
				/* 
				 * optional
				Name: Insured ID
				Description: The insured ID is the 10-digit invariable part of the 30-digit health insurance number.
				Filling / Format: analog VSDM, A000000000
				 */
				'egk'=>"P123456789" ,
				
				/* 
				 * optional
				Name: Sex
				Description: Administrative sex of the insured
				Filling / Format: analog VSDM, "M" = male, "W" = female, "X" = undefined
				 */
				's'=>"M",
				
				/* 
				 * optional
				Name: Title
				Description: Indicates the academic degrees of the insured. Multiple titles are separated by spaces.
				Filling / Format: analog VSDM
				 */
				't'=>"Dr.",
				
				/* 
				 * optional
				Name: Preface
				Description: Specifies the prefix words of the insured. Multiple prefix words are separated by spaces.
				Filling / Format: similar to VSDM, Annex 6 (table of the valid prefix words) to DEUEV, see www.gkv-datenaushandel.de/arbeiter/deuev/gemeinsame_rundschreiben/gemeinsame_rundschreiben.jsp
				 */
				'v'=>"",
				
				/* 
				 * optional
				Name: Name sufix
				Description. Specifies the name of the insured person, For example: Freiherr. Multiple naming propositions are separated by spaces.
				Filling / Format: similar to VSDM, Annex 7 (table of valid name additions) to DEUEV, see www.gkv-datenaushandel.de/arbeiter/deuev/gemeinsame_rundschreiben/gemeinsame_rundschreiben.jsp
				 */
				'z'=>"",
				
				
		] ,
		
		
		
		
		/*
		 * 1 occurence
		 * Printout of the drug plan 
		 * for Arzt|Apotheke|Krankenhaus = doctor|pharmacy|hospital
		 */
		'A'=>[
				/*
				 * required
				 Name: Name
				 Description: Name of the authority which has printed the BMP, service provider or organizational unit, (doctor's office, hospital / ward, dental practice, psychotherapeutic practice, pharmacy)
				 Filling / Format: Freitext
				 */
				'n'=>"Dr. med. Pflege, Test" ,
				
				/*
				 * required
				 Name: Print date
				 Description: Print date - the date and time of the last change of the medication plan
				 Filling / Format: YYYY-MM-DDThh: mm: ss
				 */
				't'=>"2017-08-29" ,
				
				/* 
				 * optional
				Name: Lifetime doctor's number
				Description: Lifetime identification number of a physician
				Filling / Format: 9-digit number
				Validity criterion: A maximum of one of the three attributes lanr, idf and kik can be present.
				 */
				'lanr'=>"012345678",
				
				/* 
				 * optional
				Name: Apothken-IDF
				Description: Identification number of a pharmacy
				Filling / Format: 7-digit number
				Validity criterion: A maximum of one of the three attributes lanr, idf and kik can be present.
				 */
				'idf'=>"0123456",
				
				/* 
				 * optional
				Name: Hospital-IK
				Description: Institutional label hospital
				Filling / Format: 9-digit number
				Validity criterion: A maximum of one of the three attributes lanr, idf and kik can be present.
				 */
				'kik'=>"012345678",
				
				/* 
				 * optional
				Name: Street
				Description: Street with house number as part of the address of the instance that has the BMP printed
				Filling / Format: Freitext
				 */
				's'=>"27028 Hilll Creek" ,
				
				/* 
				 * optional
				Name: Postal code
				Description: ZIP as part of the address of the instance that has printed the BMP
				Filling / Format: 5-digit number (values ​​of the postal code catalog of the Deutsche Post)
				 */
				'z'=>"12101",
				
				/* 
				 * optional
				Name: Place
				Description: Location as part of the address of the instance that has printed the BMP
				Filling / Format: Freitext
				 */
				'c'=>"Schöneberg Ort" ,
				
				/* 
				 * optional
				Name: Telephone
				Description: Phone number of the instance that has printed the BMP
				Filling / Format: Freitext
				 */
				'p'=>"401.324.3973 x959" ,
				
				/* 
				 * optional
				Name: E-mail
				Description: E-mail of the instance that has printed the BMP
				Filling / Format: Freitext
				 */
				'e'=>"doctor@originalware.com" ,
				
        ] ,
		
		
		
		/*
		 * 1 occurence
		 * Parameters, the 3 pressure parameter lines are filled from sex as well as the optional attributes given here meaningfully in the order: Allerg./Unv.:, Pregnant, breastfeeding, Weight:, Size :, Krea.:, Geschl .:, Freitext. 
		 * After each max. 25 characters per line must be broken. 
		 * If the 3 lines for the display are not enough, the 3rd line ends with "..."
		 */
		'O'=>[
				/* 
				 * optional
				Name: allergies and intolerances
				Description: Additional allergies and intolerances
				Filling / Format: Freitext
				 */
				'ai'=>'Laktose',
				
				/* 
				 * optional
				Name: Pregnant
				Description: Signs of pregnancy
				Filling / Format: When a pregnancy occurs, the attribute is specified (p = "1"). Otherwise, the attribute is omitted. This means that no statement about pregnancy is possible.
				 */
				'p'=>"1",
				
				/* 	
				 * optional			
				Name: Breastfeeding
				Description: Characteristics breastfeeding
				Filling / Format: If the insured person is breastfeeding, the attribute is indicated (b = "1"). Otherwise, the attribute is omitted. This means that there is no possibility to breastfeed.
				 */
				'b'=>"1",
				
				/* 
				 * optional
				Name: Weight
				Description: Weight of the insured
				Filling / Format: kg
				 */
				'w'=>"100",
				
				/* 
				 * optional
				Name: Size
				Description: Body size of the insured
				Filling / Format: cm
				 */
				'h'=>"180",
				
				/* 
				 * optional
				Name: creatinine
				Description: Creatinine value of the insured
				Filling / Format: mg / dl
				 */
				'c'=>"0.5",
				
				/* 
				 * optional
				Name: Parameter free text
				Description: Addition of parameters
				Filling / Format: Freitext, a maximum of 2 manual breaks can be marked with a tilde ("~")
				Validity criterion: The attribute x can not contain more than 2 tilde characters ("~").
				 */
				'x'=>"List of all operations",
				
				
		] ,
		
		
		
		
		/*
		 * 0..23 occurences
		 * Blocks with headings (groupings of medication entries)		 
		 */
		'S'=>[
				[
						/* 
						 * optional
						Free text intermediate heading: @t and @c MAY NOT be specified at the same time.
						From the second block, you must select either @t or @c.
						 */
						't'=>"Einnahmezeiten Parkinsonmedikation: 8:30 = 1 Tabl.; 12:30 = 2 Tabl.; 16:00 = 1 Tabl.; 18:30 = 1 Tabl.",
						
						/* 
						 * optional
						Intermediate heading (see documentation for codes); @t and @c CAN NOT be specified at the same time
						 */
						'c'=>"411",//411=Bedarfsmedikation
						
						/* 
						Medication
						Minimum requirement: at least one attribute or active substance must be indicated.
						Validity criterion: If a PZN is indicated for a medication, the attributes (including active substances) derivable from a drug database can not be specified, unless you are manually entered by the user.
						 */
						'M' => [
								/* 
								 * optional
								Name: Modified PZN
								Description: Pharmacentral number of a finished drug package
								Name: Modified PZN
								 */
								'p'=>"4432570",
								
								/* 
								 * optional
								Name: Drug Name
								Description: Freely expressive name of a finished medicinal product (trade name)
								Name: Drug name
								 */
								'a',
								
								/* 
								 * optional
								Name: Pharmaceutical code
								Description: Dosage form as IFA code
								Filling / Format: according to Annex 3
								Validity criterion: The attributes f and fd can not be specified simultaneously.
								 */
								'f',
								
								/* 
								 * optional
								Name: Dosage form Free text
								Description: Dosage form as free text, either defined or missing (then derived from PZN in the case of expression)
								Filling / Format: Freitext
								Validity criterion: The attributes f and fd can not be specified simultaneously.
								 */
								'fd',
								
								/*
								 * optional 
								Name: Dosing schedule in the morning
								Description: Dosage as 4-part scheme (morning)
								Filling / Format: If not specified = "0"
								Validity criterion: The attributes m and t can not be specified simultaneously.
								 */
								'm',
								
								/* 
								 * optional
								Name: Dosing schedule at noon
								Description: 4-part dosage (noon)
								Filling / Format: If not specified = "0"
								Validation criterion: The attributes d and t can not be specified at the same time.
								 */
								'd',

								/*
								 * optional 
								Name: Dosage scheme in the evening
								Description: 4-part dosage (evening)
								Filling / Format: If not specified = "0"
								Validity criterion: The attributes v and t can not be specified simultaneously.
								 */
								'v',
								
								/* 
								 * optional
								Name: Dosage scheme for the night
								Description: Dosage as 4-part scheme (at night)
								Filling / Format: If not specified = "0"
								Validation criterion: The attributes h and t can not be specified at the same time.
								 */
								'h',

								/* 
								 * optional
								Name: Dosing Scheme Freitext
								Description: Freitext dosing
								Filling / Format: Freitext
								Validity criterion: The attribute t can not be specified simultaneously with the attributes m, d, v, and h.
								 */
								't',
								
								/* 
								 * optional
								Name: Dosing unit coded
								Description: Dosing unit as code
								Filling / Format: according to Annex 4
								Validity criterion: The attributes du and dud must not be specified at the same time. If a dosage (as a schema or a free text) is specified, one of the two attributes du and dud must also be specified.
								 */
								'du',
								
								/* 
								 * optional
								Name: Dosing unit Freitext
								Description: Dosing unit as free text
								Filling / Format: Freitext
								Validity criterion: The attributes du and dud must not be specified at the same time. If a dosage (as a schema or a free text) is specified, one of the two attributes du and dud must also be specified.
								 */
								'dud',
								
								/* 
								 * optional
								Name: Notes
								Description: Instructions for use, storage, ingestion, etc.
								Filling / Format: Freitext, maximum of a manual break can be marked with a tilde ("~")
								Validity Criterion: The i attribute must not contain more than one tilde character ("~").
								 */
								'i'=>"Kommentar",
								
								/* 
								 * optional
								Name: Reason for treatment
								Description: Reason for the treatment with the medication in patientauglich form
								Filling / Format: Freitext, maximum of a manual break can be marked with a tilde ("~")
								Validity criterion: The attribute r must not contain more than one tilde character ("~").
								 */
								'r',
								
								/* 
								 * optional
								Name: bound additional line
								Description: Freitex line, which refers to this medication, supplemental information on dosage or further indications, which can not be accommodated in the fields of the medication entry
								Filling / Format: Freitext, maximum of a manual break can be marked with a tilde ("~")
								Validity Criterion: The attribute x can not contain more than one tilde character ("~").
								 */
								'x',
								
								/* 
								 * 0-3 occurences
								Active ingredient; Wirkstoff
								When a change is made to the active substances, no derivation from the PZN is carried out, but the active substances are written directly into the carrier.
								 */
								'W'=>[
										/*
										 * required 
										Name: Active ingredient
										Description: Name of an active substance
										Filling / Format: Freitext
										 */
										'w',
										
										/* 
										 * optional
										Name: Effective Thickness
										Description: Description of the active strength
										Filling / Format: Freitext
										 */
										's',
								]
								
								
						],
						
						/* 
						Free text line (Wichtige Angaben)
						 */
						'X' => [
								/* 
								 * required
								Name: Freitext
								Description: Text without reference to a medication entry
								Filling / Format: Freitext, maximum of a manual break can be marked with a tilde ("~")
								Validity Criterion: The t attribute must not contain more than 1 tilde character ("~").
								 */
								't'=>"Bitte messen Sie Ihren Blutdruck täglich!"
						],
						
						/* 
						Recipe
						 */
						'R' => [
								/* 
								 * required
								Name: Freitext
								Description: Text without reference to a medication entry
								Filling / Format: Freitext, maximum of a manual break can be marked with a tilde ("~")
								Validity Criterion: The t attribute must not contain more than 1 tilde character ("~").
								 */
								't',
								
								/* 
								 * optional
								Name: bound additional line
								Description: Freitex line, which relates to this medication, supplementary information on dosage or further indications, which can not be accommodated in the fields of the medication entry
								Filling / Format: Freitext, maximum of a manual break can be marked with a tilde ("~")
								Validity Criterion: The attribute x can not contain more than one tilde character ("~").
								 */
								'x',
						],
				],
			
				//another S-element table
				[
						'c'=>"411",
						'M'=>[]
				],
		]
    ];