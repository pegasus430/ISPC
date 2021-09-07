<?php
/**
 * extension made from http://github.com/zendframework/zf2
 * https://github.com/zendframework/zend-validator/blob/master/src/Iban.php
 * 
 * regex should be replaced according to this:
 * https://en.wikipedia.org/wiki/International_Bank_Account_Number#Algorithms
 */
/*
 * SEPA (Single Euro Payments Area)
 * https://github.com/SpainHoliday/sepawriter
 */
class Pms_SepaIbanValidator extends Zend_Validate_Iban
{
	const SEPANOTSUPPORTED = 'ibanSepaNotSupported';
	const CHECKSUMERROR = 'ibanChecksumError';
	
	protected $_iban = NULL;
    /**
     * Optional country code by ISO 3166-1
     *
     * @var string|null
     */
    protected $countryCode;

    /**
     * Optionally allow IBAN codes from non-SEPA countries. Defaults to true
     *
     * @var bool
     */
    protected $allowNonSepa = true;

    /**
     * The SEPA country codes
     *
     * @var array<ISO 3166-1>
     */
    protected static $sepaCountries = array(
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DK', 'FO', 'GL', 'EE', 'FI', 'FR', 'DE',
        'GI', 'GR', 'HU', 'IS', 'IE', 'IT', 'LV', 'LI', 'LT', 'LU', 'MT', 'MC',
        'NL', 'NO', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'CH', 'GB'
    );
    /**
     * IBAN regexes by country code
     *
     * @var array
     */
    protected static $ibanRegex = array(
    		'AD' => 'AD[0-9]{2}[0-9]{4}[0-9]{4}[A-Z0-9]{12}',
    		'AE' => 'AE[0-9]{2}[0-9]{3}[0-9]{16}',
    		'AL' => 'AL[0-9]{2}[0-9]{8}[A-Z0-9]{16}',
    		'AT' => 'AT[0-9]{2}[0-9]{5}[0-9]{11}',
    		'AZ' => 'AZ[0-9]{2}[A-Z]{4}[A-Z0-9]{20}',
    		'BA' => 'BA[0-9]{2}[0-9]{3}[0-9]{3}[0-9]{8}[0-9]{2}',
    		'BE' => 'BE[0-9]{2}[0-9]{3}[0-9]{7}[0-9]{2}',
    		'BG' => 'BG[0-9]{2}[A-Z]{4}[0-9]{4}[0-9]{2}[A-Z0-9]{8}',
    		'BH' => 'BH[0-9]{2}[A-Z]{4}[A-Z0-9]{14}',
    		'BR' => 'BR[0-9]{2}[0-9]{8}[0-9]{5}[0-9]{10}[A-Z][A-Z0-9]',
    		'CH' => 'CH[0-9]{2}[0-9]{5}[A-Z0-9]{12}',
    		'CR' => 'CR[0-9]{2}[0-9]{3}[0-9]{14}',
    		'CY' => 'CY[0-9]{2}[0-9]{3}[0-9]{5}[A-Z0-9]{16}',
    		'CZ' => 'CZ[0-9]{2}[0-9]{20}',
    		'DE' => 'DE[0-9]{2}[0-9]{8}[0-9]{10}',
    		'DO' => 'DO[0-9]{2}[A-Z0-9]{4}[0-9]{20}',
    		'DK' => 'DK[0-9]{2}[0-9]{14}',
    		'EE' => 'EE[0-9]{2}[0-9]{2}[0-9]{2}[0-9]{11}[0-9]{1}',
    		'ES' => 'ES[0-9]{2}[0-9]{4}[0-9]{4}[0-9]{1}[0-9]{1}[0-9]{10}',
    		'FI' => 'FI[0-9]{2}[0-9]{6}[0-9]{7}[0-9]{1}',
    		'FO' => 'FO[0-9]{2}[0-9]{4}[0-9]{9}[0-9]{1}',
    		'FR' => 'FR[0-9]{2}[0-9]{5}[0-9]{5}[A-Z0-9]{11}[0-9]{2}',
    		'GB' => 'GB[0-9]{2}[A-Z]{4}[0-9]{6}[0-9]{8}',
    		'GE' => 'GE[0-9]{2}[A-Z]{2}[0-9]{16}',
    		'GI' => 'GI[0-9]{2}[A-Z]{4}[A-Z0-9]{15}',
    		'GL' => 'GL[0-9]{2}[0-9]{4}[0-9]{9}[0-9]{1}',
    		'GR' => 'GR[0-9]{2}[0-9]{3}[0-9]{4}[A-Z0-9]{16}',
    		'GT' => 'GT[0-9]{2}[A-Z0-9]{4}[A-Z0-9]{20}',
    		'HR' => 'HR[0-9]{2}[0-9]{7}[0-9]{10}',
    		'HU' => 'HU[0-9]{2}[0-9]{3}[0-9]{4}[0-9]{1}[0-9]{15}[0-9]{1}',
    		'IE' => 'IE[0-9]{2}[A-Z]{4}[0-9]{6}[0-9]{8}',
    		'IL' => 'IL[0-9]{2}[0-9]{3}[0-9]{3}[0-9]{13}',
    		'IS' => 'IS[0-9]{2}[0-9]{4}[0-9]{2}[0-9]{6}[0-9]{10}',
    		'IT' => 'IT[0-9]{2}[A-Z]{1}[0-9]{5}[0-9]{5}[A-Z0-9]{12}',
    		'KW' => 'KW[0-9]{2}[A-Z]{4}[0-9]{22}',
    		'KZ' => 'KZ[0-9]{2}[0-9]{3}[A-Z0-9]{13}',
    		'LB' => 'LB[0-9]{2}[0-9]{4}[A-Z0-9]{20}',
    		'LI' => 'LI[0-9]{2}[0-9]{5}[A-Z0-9]{12}',
    		'LT' => 'LT[0-9]{2}[0-9]{5}[0-9]{11}',
    		'LU' => 'LU[0-9]{2}[0-9]{3}[A-Z0-9]{13}',
    		'LV' => 'LV[0-9]{2}[A-Z]{4}[A-Z0-9]{13}',
    		'MC' => 'MC[0-9]{2}[0-9]{5}[0-9]{5}[A-Z0-9]{11}[0-9]{2}',
    		'MD' => 'MD[0-9]{2}[A-Z0-9]{20}',
    		'ME' => 'ME[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}',
    		'MK' => 'MK[0-9]{2}[0-9]{3}[A-Z0-9]{10}[0-9]{2}',
    		'MR' => 'MR13[0-9]{5}[0-9]{5}[0-9]{11}[0-9]{2}',
    		'MT' => 'MT[0-9]{2}[A-Z]{4}[0-9]{5}[A-Z0-9]{18}',
    		'MU' => 'MU[0-9]{2}[A-Z]{4}[0-9]{2}[0-9]{2}[0-9]{12}[0-9]{3}[A-Z]{3}',
    		'NL' => 'NL[0-9]{2}[A-Z]{4}[0-9]{10}',
    		'NO' => 'NO[0-9]{2}[0-9]{4}[0-9]{6}[0-9]{1}',
    		'PK' => 'PK[0-9]{2}[A-Z]{4}[A-Z0-9]{16}',
    		'PL' => 'PL[0-9]{2}[0-9]{8}[0-9]{16}',
    		'PS' => 'PS[0-9]{2}[A-Z]{4}[A-Z0-9]{21}',
    		'PT' => 'PT[0-9]{2}[0-9]{4}[0-9]{4}[0-9]{11}[0-9]{2}',
    		'RO' => 'RO[0-9]{2}[A-Z]{4}[A-Z0-9]{16}',
    		'RS' => 'RS[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}',
    		'SA' => 'SA[0-9]{2}[0-9]{2}[A-Z0-9]{18}',
    		'SE' => 'SE[0-9]{2}[0-9]{3}[0-9]{16}[0-9]{1}',
    		'SI' => 'SI[0-9]{2}[0-9]{5}[0-9]{8}[0-9]{2}',
    		'SK' => 'SK[0-9]{2}[0-9]{4}[0-9]{6}[0-9]{10}',
    		'SM' => 'SM[0-9]{2}[A-Z]{1}[0-9]{5}[0-9]{5}[A-Z0-9]{12}',
    		'TN' => 'TN59[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}',
    		'TR' => 'TR[0-9]{2}[0-9]{5}[A-Z0-9]{1}[A-Z0-9]{16}',
    		'VG' => 'VG[0-9]{2}[A-Z]{4}[0-9]{16}',
    );

    /**
     * BIC regex taken from 
     * @var string
     */
    protected static $bicRegex = '/^[a-z]{6}[2-9a-z][0-9a-np-z]([a-z0-9]{3}|x{3})?$/i';
 
    
    public function __construct($options = null)
    {
    	if ($options instanceof Zend_Config) {
    		$options = $options->toArray();
    	}
  
    	if (array_key_exists('country_code', $options)) {
    		$this->setCountryCode($options['country_code']);
    	}
    
    	if (array_key_exists('allow_non_sepa', $options)) {
    		$this->setAllowNonSepa($options['allow_non_sepa']);
    	}
    	if (array_key_exists('iban', $options)) {
    		$this->_iban = $options['iban'];

    		$setCountryCode = false;
    		$cc = $this->iban_get_country_part($options['iban']);
    		if (!array_key_exists('country_code', $options) && $cc!==false) {
    			$setCountryCode = $this->setCountryCode($cc);
    		}
    		if (!array_key_exists('locale', $options) && $cc!==false && $setCountryCode !== false) {
    			$options['locale'] = $cc;
    		}
    	}
    	
    	if (array_key_exists('locale', $options)) {
    		parent::__construct($options);
    	}
    }
    
    /**
     * Returns the optional country code by ISO 3166-1
     *
     * @return string|null
     */
    public function getCountryCode()
    {
    	return $this->countryCode;
    }
    
    /**
     * Sets an optional country code by ISO 3166-1
     *
     * @param  string|null $countryCode
     * @return Iban provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setCountryCode($countryCode = null)
    {
        if ($countryCode !== null) {
            $countryCode = (string) $countryCode;

            if (!isset(static::$ibanRegex[$countryCode])) {
            	return false;
            	//throw new Exception("Country code '{$countryCode}' invalid by ISO 3166-1 or not supported");
            }
        }

        $this->countryCode = $countryCode;
        return $this;
    }
    
    /**
     * Returns the optional allow non-sepa countries setting
     *
     * @return bool
     */
    public function allowNonSepa()
    {
    	return $this->allowNonSepa;
    }
    
    /**
     * Sets the optional allow non-sepa countries setting
     *
     * @param  bool $allowNonSepa
     * @return Iban provides a fluent interface
     */
    public function setAllowNonSepa($allowNonSepa)
    {
    	$this->allowNonSepa = (bool) $allowNonSepa;
    	return $this;
    }
    
    /**
     * Returns true if $value is a valid IBAN
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value = null){
    	
    	if ($value == null){
    		$value = $this->_iban;
    	}
    	
    	
    	$value = $this->iban_to_machine_format($value);
    	
    	if (!parent::isValid($value)){
    		return false;
    	}
    	

    	$countryCode = $this->getCountryCode();
    	if ($countryCode === null) {
    		$countryCode = substr($value, 0, 2);
    	}
    	
    	if (!$this->allowNonSepa && !in_array($countryCode, static::$sepaCountries)) {
    		$this->_setValue($countryCode);
    		$this->_error(self::SEPANOTSUPPORTED);
    		return false;
    	}
    	
    	if (!$this->iban_verify_checksum($value)){
    		$this->_error(self::CHECKSUMERROR);
    		return false;
    	}
    	
    	return true;
    	
    }
    

    /*
     * next functions are taken from here:
     * http://www.phpclasses.org/browse/file/47531.html
     */
    
    # Convert an IBAN to machine format.  To do this, we
    # remove IBAN from the start, if present, and remove
    # non basic roman letter / digit characters
    public function iban_to_machine_format($iban = null) {
    	if($iban == null){
    		$iban = $this->_iban;
    	}
    	# Uppercase and trim spaces from left
    	$iban = ltrim(strtoupper($iban));
    	# Remove IBAN from start of string, if present
    	$iban = preg_replace('/^IBAN/','',$iban);
    	# Remove all non basic roman letter / digit characters
    	$iban = preg_replace('/[^a-zA-Z0-9]/','',$iban);
    	return $iban;
    }

    # Get the country part from an IBAN
    private function iban_get_country_part($iban) {
    	$iban = $this->iban_to_machine_format($iban);
    	$country_part = substr($iban,0,2);
    	return (strlen($country_part)==2) ? $country_part : false;
    }
    
    # Convert an IBAN to human format. To do this, we
    # simply insert spaces right now, as per the ECBS
    # (European Committee for Banking Standards)
    # recommendations available at:
    # http://www.europeanpaymentscouncil.eu/knowledge_bank_download.cfm?file=ECBS%20standard%20implementation%20guidelines%20SIG203V3.2.pdf
    public function iban_to_human_format($iban = null, $validate = null) {
    	if($iban == null){
    		$iban = $this->_iban;
    	}
    	# First verify validity, or return
    	if($validate!= null && !$this->isValid($iban)) { return false; }
    	
    	$iban = $this->iban_to_machine_format($iban);
    	# Add spaces every four characters
    	$human_iban = '';
    	for($i=0;$i<strlen($iban);$i++) {
    		$human_iban .= substr($iban,$i,1);
    		if(($i>0) && (($i+1)%4==0)) { $human_iban .= ' '; }
    	}
    	return $human_iban;
    }
        
    
    # Perform MOD97-10 checksum calculation ('Germanic-level effiency' version - thanks Chris!)
    private function iban_mod97_10($numeric_representation) {
    	# prefer php5 gmp extension if available
    	if(function_exists('gmp_intval')) { return gmp_intval(gmp_mod(gmp_init($numeric_representation, 10),'97')) === 1; }

    	$length = strlen($numeric_representation);
    	$rest = "";
    	$position = 0;
    	while ($position < $length) {
    		$value = 9-strlen($rest);
    		$n = $rest . substr($numeric_representation,$position,$value);
    		$rest = $n % 97;
    		$position = $position + $value;
    	}
    	return ($rest === 1);
    }
    
    # Check the checksum of an IBAN - code modified from Validate_Finance PEAR class
    private function iban_verify_checksum($iban) {
    	# convert to machine format
    	$iban = $this->iban_to_machine_format($iban);
    	# move first 4 chars (countrycode and checksum) to the end of the string
    	$tempiban = substr($iban, 4).substr($iban, 0, 4);
    	# subsitutute chars
    	$tempiban = $this->iban_checksum_string_replace($tempiban);
    	# mod97-10
    	$result = $this->iban_mod97_10($tempiban);
    	# checkvalue of 1 indicates correct IBAN checksum
    	if ($result != 1) {
    		return false;
    	}
    	return true;
    }
    
    # Character substitution required for IBAN MOD97-10 checksum validation/generation
    #  $s  Input string (IBAN)
    private function iban_checksum_string_replace($s) {
    	$iban_replace_chars = range('A','Z');
    	foreach (range(10,35) as $tempvalue) { $iban_replace_values[]=strval($tempvalue); }
    	return str_replace($iban_replace_chars,$iban_replace_values,$s);
    }
    
    /*
     * validate bic with regex
     */
    public function bic_isValid($bic){
    	
    	$bic = $this->bic_to_machine_format($bic);
    	
	    if (preg_match( static::$bicRegex, $bic)) {
	        return true;
	    } else {
	        return false;
	    }
    }
    
    public function bic_to_machine_format($bic){   
    	# Uppercase and trim spaces from left
    	$bic = trim(strtoupper($bic));
    	# Remove all non basic roman letter / digit characters
    	$bic = preg_replace('/[^a-zA-Z0-9]/','',$bic);
    
    	return $bic;

    }
}