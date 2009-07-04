<?php
/**
 * OpenID_Discover_MockYadis 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Discover_Yadis
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
require_once 'OpenID/Discover/Yadis.php';

/**
 * OpenID_Discover_MockYadis 
 * 
 * @uses      OpenID_Discover_Yadis
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Discover_MockYadis extends OpenID_Discover_Yadis
{
    static public $servicesYadisInstance = null;

    /**
     * Returns the mocked Services_Yadis instance
     * 
     * @param string $identifier The user supplied identifier
     * 
     * @return Services_Yadis
     */
    protected function getServicesYadis($identifier)
    {
        return self::$servicesYadisInstance;
    }
}
?>
