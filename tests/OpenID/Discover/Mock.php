<?php
/**
 * OpenID_Discover_Mock 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Discover_Interface
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

require_once 'OpenID/Discover/Interface.php';
require_once 'OpenID/ServiceEndpoints.php';

/**
 * OpenID_Discover_Mock 
 * 
 * @uses      OpenID_Discover_Interface
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Discover_Mock implements OpenID_Discover_Interface
{
    static public $opEndpoint = null;

    /**
     * __construct 
     * 
     * @param mixed $identifier Identifier
     * 
     * @return void
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * discover 
     * 
     * @return void
     */
    public function discover()
    {
        $service = new OpenID_ServiceEndpoints($this->identifier);
        $service->addService(self::$opEndpoint);
        return $service;

    }
}

?>
