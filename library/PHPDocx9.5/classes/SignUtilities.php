<?php

/**
 * Sign utilities
 *
 * @category   Phpdocx
 * @package    sign
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
class SignUtilities
{
    /**
     * Set SignatureValue
     * 
     * @access public
     * @param string $source Pfx filename
     * @param string $dest Pem filename
     * @param string $password Password access
     */
    public static function transformPfxToPem($source, $dest, $password = null)
    {
        $results = array();
        $worked = openssl_pkcs12_read(file_get_contents($source), $results, $password);
        if ($worked) {
            $worked = openssl_pkey_export($results['pkey'], $result, $password);
            if ($worked) {
                $pemFile = $result . $results['cert'];
                file_put_contents($dest, $pemFile);
            } else {
                exit(openssl_error_string());
            }
        } else {
            exit(openssl_error_string());
        }
    }

}
