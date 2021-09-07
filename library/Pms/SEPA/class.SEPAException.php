<?php
/**
 * Our custom exception handler.
 *
 * @author     Johannes Feichtner <johannes@web-wack.at>
 * @copyright  http://www.web-wack.at web wack creations
 * @license    http://creativecommons.org/licenses/by-nc/3.0/ CC Attribution-NonCommercial 3.0 license
 * For commercial use please contact sales@web-wack.at
 */

class SEPAException extends Exception
{
	
	static public $enable = true;
	
	public function __construct($message = null, $code = null, $previous = null)
	{
		if (self::$enable) {
			parent::__construct($message, $code, $previous);
		}
	}
	
}
