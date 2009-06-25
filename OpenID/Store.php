<?php
/**
 * OpenID_Store 
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

/**
 * Required files
 */
require_once 'OpenID/Store/Exception.php';

/**
 * Provides a factory for creating storage classes.
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
abstract class OpenID_Store
{
    /**
     * Creates an instance of a storage driver
     * 
     * @param string $driver  Driver name
     * @param array  $options Any options the driver needs
     * 
     * @return void
     */
    static public function factory($driver = 'CacheLite', array $options = array())
    {
        $file  = 'OpenID/Store/' . str_replace('_', '/', $driver . '.php');
        $class = 'OpenID_Store_' . $driver;

        include_once $file;
        if (!class_exists($class)) {
            throw new OpenID_Store_Exception(
                'Invalid storage driver: ' . $driver
            );
        }

        $instance = new $class($options);
        if (!$instance instanceof OpenID_Store_Interface) {
            throw new OpenID_Store_Exception(
                $class . ' does not implement OpenID_Store_Interface'
            );
        }
        return new $class($options);
    }
}
?>
