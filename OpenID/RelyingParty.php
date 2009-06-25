<?php
/**
 * OpenID_RelyingParty 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID
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
require_once 'OpenID.php';
require_once 'OpenID/Store.php';
require_once 'OpenID/Discover.php';
require_once 'OpenID/Association/Request.php';
require_once 'OpenID/Assertion.php';
require_once 'OpenID/Assertion/Result.php';
require_once 'OpenID/Auth/Request.php';

/**
 * OpenID_RelyingParty 
 * 
 * @uses      OpenID
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_RelyingParty extends OpenID
{
    /**
     * The user supplied identifier, normalized
     * 
     * @see OpenID::normalizeIdentifier()
     * @see __construct()
     * @var string
     */
    protected $normalizedID = null;

    /**
     * The URI used for the openid.return_to parameter
     * 
     * @see __construct()
     * @var string
     */
    protected $returnTo = null;

    /**
     * The URI used for the openid.realm paramater
     * 
     * @see __construct()
     * @var string
     */
    protected $realm = null;
    /**
     * Whether or not to use associations
     * 
     * @see __construct()
     * @var mixed
     */
    protected $useAssociations = true;

    /**
     * How far off of the current time to allow for nonce checking
     * 
     * @see setClockSkew()
     * @var int
     */
    protected $clockSkew = null;

    /**
     * Sets the identifier, returnTo, and realm to be used for messages.  The 
     * identifier is normalized before being set.
     * 
     * @param mixed $identifier The user supplied identifier
     * @param mixed $returnTo   The openid.return_to paramater value
     * @param mixed $realm      The openid.realm paramater value
     * 
     * @see OpenID::normalizeIdentifier
     * @return void
     */
    public function __construct($identifier, $returnTo, $realm)
    {
        $this->normalizedID = OpenID::normalizeIdentifier($identifier);
        $this->returnTo     = $returnTo;
        $this->realm        = $realm;
    }

    /**
     * Enables the use of associations (default)
     * 
     * @return void
     */
    public function enableAssociations()
    {
        $this->useAssociations = true;
    }

    /**
     * Disables the use if associations
     * 
     * @return void
     */
    public function disableAssociations()
    {
        $this->useAssociations = false;
    }

    /**
     * Sets the clock skew for nonce checking
     * 
     * @param int $skew Skew (or timeout) in seconds
     * 
     * @throws OpenID_Exception if $skew is not numeric
     * @return void
     */
    public function setClockSkew($skew)
    {
        if (!is_numeric($skew)) {
            throw new OpenID_Exception(
                'Invalid clock skew'
            );
        }
        $this->clockSkew = $skew;
    }

    /**
     * Prepares an OpenID_Auth_Request and returns it.  This process includes
     * performing discovery and optionally creating an association before preparing
     * the OpenID_Auth_Request object.
     * 
     * @return OpenID_Auth_Request
     */
    public function prepare()
    {
        // Discover
        $discover        = $this->getDiscover();
        $serviceEndpoint = $discover->services[0];

        // Associate
        $assocHandle = null;
        if ($this->useAssociations) {
            $opEndpointURL = array_shift($serviceEndpoint->getURIs());
            $assoc         = $this->getAssociation($opEndpointURL,
                                                   $serviceEndpoint->getVersion());

            if ($assoc instanceof OpenID_Association) {
                $assocHandle = $assoc->assocHandle;
            }
        }

        // Return OpenID_Auth_Request object
        return new OpenID_Auth_Request($discover,
                                       $this->returnTo,
                                       $this->realm,
                                       $assocHandle);
    }

    /**
     * Verifies an assertion response from the OP.  If the openid.mode is error, an
     * exception is thrown.
     * 
     * @param OpenID_Message $message The Assertion response from the OP
     * 
     * @throws OpenID_Exception on error or invalid openid.mode
     * @return OpenID_Assertion_Response
     */
    public function verify(OpenID_Message $message)
    {
        $mode   = $message->get('openid.mode');
        $result = new OpenID_Assertion_Result;

        switch ($mode) {
        case OpenID::MODE_ID_RES:
            break;
        case OpenID::MODE_CANCEL:
        case OpenID::MODE_SETUP_NEEDED:
            $result->setAssertionMethod($mode);
            return $result;
        case OpenID::MODE_ERROR:
            throw new OpenID_Exception($message->get('openid.error'));
        default:
            throw new OpenID_Exception('Unknown mode: ' . $mode);
        }

        $discover        = $this->getDiscover();
        $serviceEndpoint = $discover->services[0];
        $opEndpointURL   = array_shift($serviceEndpoint->getURIs());
        $assertion       = $this->getAssertionObject($message);

        // Check via associations
        if ($this->useAssociations) {
            if ($message->get('openid.invalidate_handle') === null) {
                // Don't fall back to check_authentication
                $result->setAssertionMethod(OpenID::MODE_ASSOCIATE);
                $assoc = $this->getStore()->getAssociation($opEndpointURL);

                if ($assoc instanceof OpenID_Association &&
                    $assoc->checkMessageSignature($message)) {

                    $result->setAssertionResult(true);
                }
                return $result;
            }

            // Invalidate handle requested. Delete it and fall back to 
            // check_authenticate
            $this->getStore()->deleteAssociation($opEndpointURL);
        }

        // Check via check_authenticate
        $result->setAssertionMethod(OpenID::MODE_CHECK_AUTHENTICATION);
        $result->setCheckAuthResponse($assertion->checkAuthentication());
        if ($result->getCheckAuthResponse()->get('is_valid') == 'true') {
            $result->setAssertionResult(true);
        }
        return $result;
    }

    /**
     * Gets discovered information from cache if it exists, otherwise performs
     * discovery.
     * 
     * @throws OpenID_Exception if discovery fails
     * @see OpenID_Discover::getDiscover()
     * @return OpenID_Discover
     */
    protected function getDiscover()
    {
        $discover = OpenID_Discover::getDiscover($this->normalizedID,
                                                 $this->getStore());
        if ($discover === false) {
            throw new OpenID_Exception('Unable to discover OP Endpoint URL');
        }

        return $discover;
    }

    /**
     * Gets an association from cache if it exists, otherwise, creates one.
     * 
     * @param string $opEndpointURL The OP Endpoint URL to communicate with
     * @param string $version       The version of OpenID being used
     * 
     * @return OpenID_Association on success, false on failure
     */
    protected function getAssociation($opEndpointURL, $version)
    {
        $assocCache = $this->getStore()->getAssociation($opEndpointURL);

        if ($assocCache instanceof OpenID_Association) {
            return $assocCache;
        }

        $assoc  = $this->getAssociationRequestObject($opEndpointURL, $version);
        $result = $assoc->associate();

        if (!$result instanceof OpenID_Association) {
            return false;
        }

        self::getStore()->setAssociation($result);

        return $result;
    }

    /**
     * Gets a new OpenID_Association_Request object.  Abstracted for testing.
     * 
     * @param string $opEndpointURL The OP endpoint URL to communicate with
     * @param string $version       The OpenID version being used
     * 
     * @see prepare()
     * @return OpenID_Association_Request
     */
    protected function getAssociationRequestObject($opEndpointURL, $version)
    {
        return new OpenID_Association_Request($opEndpointURL, $version);
    }

    /**
     * Gets an instance of OpenID_Assertion.  Abstracted for testing purposes.
     * 
     * @param OpenID_Message $message The message passed to {link verify()}
     * 
     * @see    verify()
     * @return OpenID_Assertion
     */
    protected function getAssertionObject($message)
    {
        return new OpenID_Assertion($message,
                                    $this->returnTo,
                                    $this->clockSkew);
    }
}
?>
