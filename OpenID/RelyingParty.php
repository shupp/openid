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
 * @link      http://github.com/shupp/openid
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
require_once 'Net/URL2.php';

/**
 * OpenID_RelyingParty
 *
 * OpenID_RelyingParty implements all the steps required to verify a claim in two
 * step interface: {@link prepare() prepare} and {@link verify() verify}.
 * 
 * {@link prepare() prepare} sets up the request, which includes performing 
 * discovery on the identifier, establishing an association with the OpenID Provider
 * (optional), and then building an OpenID_Auth_Request object.  With this object, 
 * you can optionally add OpenID_Extension(s), and then perform the request.
 * 
 * {@link verify() verify} takes a Net_URL2 object as an argument, which represents 
 * the URL that the end user was redirected to after communicating with the the 
 * OpenID Provider.  It processes the URL, and if it was a positive response from 
 * the OP, tries to verify that assertion.
 * 
 * Example:
 * <code>
 * // First set up some things about your relying party:
 * $realm    = 'http://examplerp.com';
 * $returnTo = $realm . '/relyingparty.php';
 *
 * // Here is an example user supplied identifier
 * $identifier = $_POST['identifier'];
 *
 * // You might want to store it in session for use in verify()
 * $_SESSION['identifier'] = $identifier;
 * 
 * // Fire up the OpenID_RelyingParty object
 * $rp = new OpenID_RelyingParty($returnTo, $realm, $identifier);
 *
 * // Here's an example of prepare() usage ...
 * // First, grab your Auth_Request_Object
 * $authRequest = $rp->prepare();
 *
 * // Then, optionally add an extension
 *  $sreg = new OpenID_Extension_SREG11(OpenID_Extension::REQUEST);
 *  $sreg->set('required', 'email');
 *  $sreg->set('optional', 'nickname,gender,dob');
 *
 *  // You'll need to add it to OpenID_Auth_Request
 *  $authRequest->addExtension($sreg);
 * // Optionally get association (from cache in this example)
 * 
 * // Optionally make this a checkid_immediate request
 * $auth->setMode(OpenID::MODE_CHECKID_IMMEDIATE);
 * 
 * // Send user to the OP
 * header('Location: ' . $auth->getAuthorizeURL());
 * exit;
 *
 *
 *
 *
 * // Now, when they come back, you'll want to verify the claim ...
 *
 * // Assuming your $realm is the host which they came in to, build a Net_URL2 
 * // object from this request:
 * $request = new Net_URL2($realm . $_SERVER['REQUEST_URI']);
 * 
 * // Now verify:
 * $result = $rp->verify($request);
 * if ($result->success()) {
 *     echo "success! :)";
 * } else {
 *     echo "failure :(";
 * }
 * </code>
 *
 * @uses      OpenID
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
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
     * @param mixed $returnTo   The openid.return_to parameter value
     * @param mixed $realm      The openid.realm parameter value
     * @param mixed $identifier The user supplied identifier, defaults to null
     * 
     * @see OpenID::normalizeIdentifier
     *
     * @throws OpenID_Exception When the identifier is invalid
     */
    public function __construct($returnTo, $realm, $identifier = null)
    {
        $this->returnTo = $returnTo;
        $this->realm    = $realm;
        if ($identifier !== null) {
            $this->normalizedID = OpenID::normalizeIdentifier($identifier);
        }
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
     * @throws OpenID_Exception if no identifier was passed to the constructor
     */
    public function prepare()
    {
        if ($this->normalizedID === null) {
            throw new OpenID_Exception('No identifier provided');
        }

        // Discover
        $discover        = $this->getDiscover();
        $serviceEndpoint = $discover->services[0];

        // Associate
        $assocHandle = null;
        if ($this->useAssociations) {
            $uris          = $serviceEndpoint->getURIs();
            $opEndpointURL = array_shift($uris);
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
     * @param Net_URL2       $requestedURL The requested URL (that the user was 
     *                                     directed to by the OP) as a Net_URL2 
     *                                     object
     * @param OpenID_Message $message      The OpenID_Message instance, as extractd
     *                                     from the input (GET or POST)
     * 
     * @throws OpenID_Exception on error or invalid openid.mode
     * @return OpenID_Assertion_Response
     */
    public function verify(Net_URL2 $requestedURL, OpenID_Message $message)
    {
        // Unsolicited assertion?
        if ($this->normalizedID === null) {
            $unsolicitedID      = $message->get('openid.claimed_id');
            $this->normalizedID = OpenID::normalizeIdentifier($unsolicitedID);
        }

        $mode   = $message->get('openid.mode');
        $result = new OpenID_Assertion_Result;

        OpenID::setLastEvent(__METHOD__, print_r($message->getArrayFormat(), true));

        switch ($mode) {
        case OpenID::MODE_ID_RES:
            if ($message->get('openid.ns') === null
                && $message->get('openid.user_setup_url') !== null) {

                // Negative 1.1 checkid_immediate response
                $result->setAssertionMethod($mode);
                $result->setUserSetupURL($message->get('openid.user_setup_url'));
                return $result;
            }
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
        $URIs            = $serviceEndpoint->getURIs();
        $opEndpointURL   = array_shift($URIs);
        $assertion       = $this->getAssertionObject($message, $requestedURL);

        $result->setDiscover($discover);

        // Check via associations
        if ($this->useAssociations) {
            if ($message->get('openid.invalidate_handle') === null) {
                // Don't fall back to check_authentication
                $result->setAssertionMethod(OpenID::MODE_ASSOCIATE);
                $assoc = $this->getStore()
                              ->getAssociation($opEndpointURL,
                                               $message->get('openid.assoc_handle'));
                OpenID::setLastEvent(__METHOD__, print_r($assoc, true));

                if ($assoc instanceof OpenID_Association &&
                    $assoc->checkMessageSignature($message)) {

                    $result->setAssertionResult(true);
                }

                // If it's not an unsolicited assertion, just return
                if (!isset($unsolicitedID)) {
                    return $result;
                }
            } else {
                // Invalidate handle requested. Delete it and fall back to 
                // check_authenticate
                $this->getStore()->deleteAssociation($opEndpointURL);
            }
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
        if (!$discover instanceof OpenID_Discover) {
            // @codeCoverageIgnoreStart
            throw new OpenID_Exception('Unable to discover OP Endpoint URL');
            // @codeCoverageIgnoreEnd
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
     * @param OpenID_Message $message      The message passed to verify()
     * @param Net_URL2       $requestedURL The URL requested (redirect from OP)
     * 
     * @see    verify()
     * @return OpenID_Assertion
     */
    protected function getAssertionObject($message, $requestedURL)
    {
        return new OpenID_Assertion($message,
                                    $requestedURL,
                                    $this->clockSkew);
    }
}
?>
