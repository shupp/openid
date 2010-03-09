<?php
/**
 * OpenID_Assertion_Exception_NoClaimedID 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Assertion_Exception
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

/**
 * Required files
 */
require_once 'OpenID/Assertion/Exception.php';

/**
 * Identify cases where a claimed id is not present.
 * 
 * @uses      OpenID_Assertion_Exception
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_Assertion_Exception_NoClaimedID extends OpenID_Assertion_Exception
{
}
?>
