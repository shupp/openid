<?php
/**
 * OpenID_RelyingParty_Mock 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_RelyingParty
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

require_once 'OpenID/RelyingParty.php';

/**
 * OpenID_RelyingParty_Mock 
 * 
 * @uses      OpenID_RelyingParty
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_RelyingParty_Mock extends OpenID_RelyingParty
{
    /**
     * Just returns an OpenID_Association_Request object, as instantiated by
     * getAssociationRequestObject().  This is just for testing it.
     * 
     * @param string $opEndpointURL The OP endpoint URL to communicate with
     * @param string $version       The OpenID version in use
     * 
     * @return OpenID_Association_Request
     */
    public function returnGetAssociationRequestObject($opEndpointURL, $version)
    {
        return $this->getAssociationRequestObject($opEndpointURL, $version);
    }

    /**
     * Just returns an OpenID_Assertion object, as instantiated by
     * getAssertionObject().  This is just for testing it.
     * 
     * @param OpenID_Message $message      The message passed to {link verify()}
     * @param string         $requestedURL The requested URL
     * 
     * @return OpenID_Assertion
     */
    public function returnGetAssertionObject(OpenID_Message $message, $requestedURL)
    {
        return $this->getAssertionObject($message, $requestedURL);
    }
}
?>
