<?php
/**
 * OpenID_Discover_MockFail 
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

/**
 * Required files 
 */
require_once 'OpenID/Discover/Interface.php';

/**
 * OpenID_Discover_MockFail 
 * 
 * @uses      OpenID_Discover_Interface
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Discover_MockFail implements OpenID_Discover_Interface
{
    /**
     * __construct 
     * 
     * @param mixed $identifier UCI
     * 
     * @return void
     */
    public function __construct($identifier)
    {
    }

    /**
     * discover 
     * 
     * @return void
     */
    public function discover()
    {
        return false;
    }
}
?>
