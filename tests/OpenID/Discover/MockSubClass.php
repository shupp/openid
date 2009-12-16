<?php
/**
 * OpenID_Discover_MockSubClass 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Discover
 * @category  Authenticateion
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

require_once 'OpenID/Discover.php';

/**
 * OpenID_Discover_MockSubClass 
 * 
 * @uses      OpenID_Discover
 * @category  Authenticateion
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Discover_MockSubClass extends OpenID_Discover
{
    /**
     * setServices 
     * 
     * @param OpenID_ServiceEndpoints $services OpenID_ServiceEndpoints instance
     * 
     * @return void
     */
    public function setServices(OpenID_ServiceEndpoints $services)
    {
        $this->services = $services;
    }
}
?>
