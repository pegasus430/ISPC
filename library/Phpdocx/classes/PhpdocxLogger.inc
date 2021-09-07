<?php

/**
 * Logger
 *
 * @category   Phpdocx
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @version    2016.05.01
 * @link       http://www.phpdocx.com
 */
class PhpdocxLogger
{

    /**
     *
     * @access private
     * @static
     * @var string
     */
    private static $_disableLogger = false;

    /**
     *
     * @access private
     * @static
     * @var string
     */
    private static $_log = NULL;

    /**
     * Singleton, return instance of class
     *
     * @access public
     * @param $message Message to send to logging framework
     * @param $level Allowed values: trace, debug, info, warn, error, fatal
     * @static
     */
    public static function logger($message, $level)
    {
        $levels = array(
            'debug',
            'info',
            'notice',
            'warning',
            'error',
            'fatal',
        );

        if (self::$_disableLogger === false) {
            if (!self::$_log) {
                Logger::configure(dirname(__FILE__) . '/../config/log4php.php');
                self::$_log = Logger::getLogger('phpdocx_logger');
            }

            // only some levels are valid
            if (in_array($level, $levels)) {
                $stringLevel = strtolower($level);
                self::$_log->$stringLevel($message);

                // stop phpdocx if fatal level
                if ($level == 'fatal') {
                    throw new Exception($message);
                }
            }
        }
    }

    /**
     * Disable the logger.
     *
     * @access public
     * @static
     */
    public static function disableLogger()
    {
        self::$_disableLogger = true;
    }

    /**
     * Enable the logger.
     *
     * @access public
     * @static
     */
    public static function enableLogger()
    {
        self::$_disableLogger = false;
    }

    /**
     * Set a custom logger. It must follow the PSR3 messages.
     *
     * @access public
     * @param mixed $logger Custom logger
     * @static
     */
    public static function setLogger($logger)
    {
        self::$_log = $logger;
    }

}
