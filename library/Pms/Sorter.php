<?php
/**
 * 
 * @author claudiu  
 * Jul 7, 2017 
 *
 * http://php.net/manual/en/array.sorting.php
 *
 * php 5.2 _anonymous hits again
 * usort($drop_array, function($a, $b) use ($search_str) {
 * 	$adescription = $a['description'];
 * 	$bdescription = $b['description'];
 *  	$astrpos = mb_stripos($adescription, $search_str, 0);
 *  	$bstrpos = mb_stripos($bdescription, $search_str, 0);		
 * 	if (($astrpos === 0 && $bstrpos === 0) || ($astrpos === false && $bstrpos === false)) {
 * 		return strnatcmp ( $adescription , $bdescription ); *@todo: search strnatcmp(multibyte)
 * 	} elseif ($astrpos === 0) {
 * 		return -1;
 * 	} elseif ($bstrpos === 0) {
 * 		return 1;
 * 	}
 * });
 */
class Pms_Sorter
{
	
    /**
     * @since 22.08.2018, this is used as string or array (fn _customorder)
     * @var string|array
     */
	private $search_str = null; 
	
	/**
	 * the array_key to order by
	 * @var string
	 */
	private $key =  null; 
	
	private $encoding =  null;

	public function __construct( $key = null, $search_str = null , $encoding = null) 
	{
		$this->key = $key;
		
		$this->search_str = $search_str;
		
		if (null === $encoding) {
			$this->encoding = mb_internal_encoding(); 
		}
	}

	/**
	 * strnatcmp — String comparisons using a "natural order" algorithm
	 * altered to return first the search word
	 * @todo: strnatcmp(multibyte) - i didn't find the implementation for php
	 */
	public function _strnatcmp($a, $b) 
	{

		$aval = $this->key === null ? $a : $a[$this->key];
		$bval = $this->key === null ? $b : $b[$this->key];

		$astrpos = $this->search_str === null ? false : mb_stripos($aval, $this->search_str, 0);
		$bstrpos = $this->search_str === null ? false : mb_stripos($bval, $this->search_str, 0);
		
		if (($astrpos === 0 && $bstrpos === 0) || ($astrpos === false && $bstrpos === false)) {
			return strnatcmp ( $aval , $bval ); 
		} elseif ($astrpos === 0) {
			return -1;
		} elseif ($bstrpos === 0) {
			return 1;
		}

	}
	
	/**
	 * http://php.net/manual/en/function.strcasecmp.php#107016
	 * chris at cmbuckley dot co dot uk
	 * A simpler multibyte-safe case-insensitive string comparison
	 */
	public function _strcmp($a, $b)
	{	
		$aval = $this->key === null ? $a : $a[$this->key];
		$bval = $this->key === null ? $b : $b[$this->key];
		
		return strcmp(mb_strtoupper($aval, $this->encoding), mb_strtoupper($bval, $this->encoding));
	}
	
	
	public function _date_compare($a, $b)
	{
	    $aval = $this->key === null ? $a : $a[$this->key];
	    $bval = $this->key === null ? $b : $b[$this->key];
	    
	    $t1 = strtotime($aval);
	    $t2 = strtotime($bval);
	    return $t1 - $t2;
	}
	
	public function _number_asc($a, $b) {
	    $aval = $this->key === null ? $a : $a[$this->key];
	    $bval = $this->key === null ? $b : $b[$this->key];
	    
	    return $aval - $bval;
	}
	
	public function _number_desc($a, $b) {
	    $aval = $this->key === null ? $a : $a[$this->key];
	    $bval = $this->key === null ? $b : $b[$this->key];
	     
	    return $bval - $aval;
	}
	
	/** 
	 * strnatcasecmp — Case insensitive string comparisons using a "natural order" algorithm
	 * http://php.net/manual/ro/function.strnatcasecmp.php
	 */
	public function _strnatcasecmp($a, $b)
	{
	
	    $aval = $this->key === null ? $a : $a[$this->key];
	    $bval = $this->key === null ? $b : $b[$this->key];
	
	    $astrpos = $this->search_str === null ? false : mb_stripos($aval, $this->search_str, 0);
	    $bstrpos = $this->search_str === null ? false : mb_stripos($bval, $this->search_str, 0);
	
	    if (($astrpos === 0 && $bstrpos === 0) || ($astrpos === false && $bstrpos === false)) {
	        return strnatcasecmp ( $aval , $bval );
	    } elseif ($astrpos === 0) {
	        return -1;
	    } elseif ($bstrpos === 0) {
	        return 1;
	    }
	
	}
	
	/**
	 * sort by a custom ordered array
	 * @see PatientcourseController::_patientcourse_contactform_step2() , first time used
	 * @example : usort($top_cf, array(new Pms_Sorter('tabname', $search_order), "_customorder"));
	 */
	public function _customorder($a, $b)
	{
	
	    $aval = $this->key === null ? $a : $a[$this->key];
	    $bval = $this->key === null ? $b : $b[$this->key];
	
	    $apos = $this->search_str === null ? false : (is_array($this->search_str) ? array_search($aval, $this->search_str) : $aval == $this->search_str);
	    $bpos = $this->search_str === null ? false : (is_array($this->search_str) ? array_search($bval, $this->search_str) : $bval == $this->search_str);
	
	    
	    if ($apos !== false && $bpos !== false) {
	        return $apos - $bpos;
	    } elseif ($apos === false) {
	        return -1;
	    } elseif ($bpos === false) {
	        return 1;
	    }
	
	}
	
	
}

?>