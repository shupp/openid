<?php
/**
 * OpenID_Auth_Request 
 * 
 * PHP Version 5.2.0+
 * 
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
require_once 'Net/URL2.php';
require_once 'OpenID.php';
require_once 'OpenID/Auth/Exception.php';
require_once 'OpenID/Message.php';
require_once 'OpenID/Nonce.php';

/**
 * Creates an OpenID authorization request of type "checkid_setup" or 
 * "checkid_immediate".
 * 
 * Example:
 * <code>
 * // First perform discovery on the user supplied identifier
 * $discover = new OpenID_Discover($identifier);
 * $discover->discover();
 * 
 * // Optionally get association (from cache in this example)
 * $opEndpointURL = array_shift($discover->services[0]->getURIs());
 * $assocHandle   = OpenID::getStore()->getAssociation($opEndpointURL)->assocHandle;
 * 
 * // Now create the auth request object
 * $auth = new OpenID_Auth_Request($discover,     // OpenID_Discover object
 *                                 $returnTo,     // openid.return_to
 *                                 $realm,        // openid.realm
 *                                 $assocHandle); // openid.assoc_handle
 * 
 * // Optionally add an extension
 *  $sreg = new OpenID_Extension_SREG11(OpenID_Extension::REQUEST);
 *  $sreg->set('required', 'email');
 *  $sreg->set('optional', 'nickname,gender,dob');
 *
 *  // Add it to an existing instance of OpenID_Auth_Request
 *  $auth->addExtension($sreg);
 * 
 * // Optionally make this a checkid_immediate request
 * $auth->setMode(OpenID::MODE_CHECKID_IMMEDIATE);
 * 
 * // Send user to the OP
 * header('Location: ' . $auth->getAuthorizeURL());
 * </code>
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_Auth_Request
{
    /**
     * The normalized identifier
     * 
     * @var string
     */
    protected $identifier = null;

    /**
     * The request message
     * 
     * @var OpenID_Message
     */
    protected $message = null;

    /**
     * The OP Endpoint we are communicating with
     * 
     * @var OpenID_ServiceEndpoint
     */
    protected $serviceEndpoint = null;

    /**
     * Nonce class in case we are in 1.1 mode and need to embed it in the return_to
     * 
     * @var OpenID_Nonce
     */
    protected $nonce = null;

    /**
     * The original OpenID_Discover object.  Useful for detecting extension support
     * 
     * @see getDiscover()
     * @var OpenID_Discover|null
     */
    protected $discover = null;


    /**
     * Sets the basic information used in the message.
     * 
     * @param OpenID_Discover $discover    Discover object
     * @param string          $returnTo    The return_to URL
     * @param string          $realm       The realm
     * @param string          $assocHandle The optional association handle
     * 
     * @return void
     */
    public function __construct(OpenID_Discover $discover,
                                $returnTo,
                                $realm,
                                $assocHandle = null)
    {
        $this->identifier      = $discover->identifier;
        $this->serviceEndpoint = $discover->services[0];
        $this->message         = new OpenID_Message();
        $this->discover        = $discover;

        // Only set NS for 2.0
        $versionFromMap = OpenID::$versionMap[$this->serviceEndpoint->getVersion()];
        if ($versionFromMap == OpenID::NS_2_0) {
            $this->message->set('openid.ns', $versionFromMap);
        }
        $this->message->set('openid.return_to', $returnTo);
        $this->message->set('openid.realm', $realm);

        if (!empty($assocHandle)) {
            $this->message->set('openid.assoc_handle', $assocHandle);
        }

        // Default to checkid_setup
        $this->setMode(OpenID::MODE_CHECKID_SETUP);
    }

    /**
     * Adds an extension to the message.
     * 
     * @param OpenID_Extension $extension Extension instance
     * 
     * @return void
     */
    public function addExtension(OpenID_Extension $extension)
    {
        $this->message->addExtension($extension);
    }

    /**
     * Sets the openid.mode parameter.  Can be either "checkid_setup" or 
     * "checkid_immediate"
     * 
     * @param mixed $mode Value for 'openid.mode'
     * 
     * @throws OpenID_Auth_Exception on an invalid mode
     * @return void
     */
    public function setMode($mode)
    {
        switch ($mode) {
        case OpenID::MODE_CHECKID_SETUP:
        case OpenID::MODE_CHECKID_IMMEDIATE:
            $this->message->set('openid.mode', $mode);
            break;
        default:
            throw new OpenID_Auth_Exception(
                'Invalid openid.mode: ' . $mode
            );
        }
    }

    /**
     * Gets the current openid.mode value
     * 
     * @return string
     */
    public function getMode()
    {
        return $this->message->get('openid.mode');
    }

    /**
     * Gets the auth request message in a URL format suitable for redirection.  The
     * decision about whether to use directed identity or not id done here.
     * 
     * @return string The URL to redirect the User-Agent to
     */
    public function getAuthorizeURL()
    {
        $version = OpenID::$versionMap[$this->serviceEndpoint->getVersion()];

        if ($this->serviceEndpoint->getVersion() == OpenID::SERVICE_2_0_SERVER) {
            $this->message->set('openid.claimed_id', OpenID::NS_2_0_ID_SELECT);
            $this->message->set('openid.identity', OpenID::NS_2_0_ID_SELECT);
        } else {
            $localID = $this->serviceEndpoint->getLocalID();
            if (!empty($localID)) {
                if ($version == OpenID::NS_2_0) {
                    $this->message->set('openid.claimed_id', $this->identifier);
                }
                $this->message->set('openid.identity', $localID);
            } else {
                if ($version == OpenID::NS_2_0) {
                    $this->message->set('openid.claimed_id', $this->identifier);
                }
                $this->message->set('openid.identity', $this->identifier);
            }
        }

        if ($version == OpenID::NS_1_1) {
            $this->addNonce();
        }

        $urls = $this->serviceEndpoint->getURIs();
        if (strstr($urls[0], '?')) {
            $url = $urls[0] . '&' . $this->message->getHTTPFormat();
        } else {
            $url = $urls[0] . '?' . $this->message->getHTTPFormat();
        }
        $netURL = new Net_URL2($url);

        return $netURL->getURL();
    }

    /**
     * Sets the instance of OpenID_Nonce for use with 1.1 return_to nonces
     * 
     * @param OpenID_Nonce $nonce Custom instance of OpenID_Nonce
     * 
     * @return void
     */
    public function setNonce(OpenID_Nonce $nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * Gets the OpenID_Nonce instance if set, otherwise instantiates one.
     * 
     * @return OpenID_Nonce
     */
    protected function getNonce()
    {
        if ($this->nonce instanceof OpenID_Nonce) {
            return $this->nonce;
        }
        return new OpenID_Nonce(array_shift($this->serviceEndpoint->getURIs()));
    }

    /**
     * Adds a nonce to the openid.return_to URL parameter.  Only used in OpenID 1.1
     * 
     * @return void
     */
    protected function addNonce()
    {
        $nonce       = $this->getNonce()->createNonceAndStore();
        $returnToURL = new Net_URL2($this->message->get('openid.return_to'));
        $returnToURL->setQueryVariable(OpenID_Nonce::RETURN_TO_NONCE,
                                       urlencode($nonce));

        $this->message->set('openid.return_to', $returnToURL->getURL());

        // Observing
        $logMessage  = "Nonce: $nonce\n";
        $logMessage  = 'New ReturnTo: ' . $returnToURL->getURL() . "\n";
        $logMessage .= 'OP URIs: ' . print_r($this->serviceEndpoint->getURIs(),
                                             true);
        OpenID::setLastEvent(__METHOD__, $logMessage);
    }

    /**
     * Returns the discovered information about the identifer
     * 
     * @see $discover
     * @return OpenID_Discover|null
     */
    public function getDiscover()
    {
        return $this->discover;
    }
}
?>
