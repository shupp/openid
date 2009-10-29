<?php
/**
 * OpenID_Assertion 
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
require_once 'OpenID.php';
require_once 'OpenID/Discover.php';
require_once 'OpenID/Assertion/Exception.php';
require_once 'OpenID/Assertion/Exception/NoClaimedID.php';
require_once 'OpenID/Message.php';
require_once 'OpenID/Nonce.php';
require_once 'Validate.php';
require_once 'Net/URL2.php';

/**
 * Class for verifying assertions.  Does basic validation (nonce, return_to, etc), 
 * as well as signature verification and check_authentication.
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Assertion extends OpenID
{
    /**
     * Response message passed to the constructor
     * 
     * @var OpenID_Message
     */
    protected $message = null;

    /**
     * The URL of the current request (to compare with openid.return_to)
     * 
     * @var string
     */
    protected $requestedURL = null;

    /**
     * The clock skew limit for checking nonces.
     * 
     * @var int (in seconds)
     */
    protected $clockSkew = null;

    /**
     * Sets the request message, url, and clock skew.  Then does some basic 
     * validation (return_to, nonce, discover).
     * 
     * @param OpenID_Message $message      Message from the request
     * @param Net_URL2       $requestedURL The requested URL
     * @param int            $clockSkew    Nonce clock skew in seconds
     * 
     * @return void
     */
    public function __construct(OpenID_Message $message,
                                Net_URL2 $requestedURL,
                                $clockSkew = null)
    {
        $this->message      = $message;
        $this->requestedURL = $requestedURL;
        $this->clockSkew    = $clockSkew;

        // Don't check return_to for a negative checkid_immadiate 1.1 response
        if ($message->get('openid.ns') !== null
            || $message->get('openid.user_setup_url') === null) {

            $this->validateReturnTo();
        }

        if ($message->get('openid.ns') !== null) {
            $this->validateDiscover();
            $this->validateNonce();
        } else {
            $this->validateReturnToNonce();
        }
    }

    /**
     * Verifies the signature of this message association.
     * 
     * @param OpenID_Association $assoc Association to use for checking the signature
     * 
     * @return bool result of OpenID_Association::checkMessageSignature()
     * @see OpenID_Association::checkMessageSignature()
     */
    public function verifySignature(OpenID_Association $assoc)
    {
        return $assoc->checkMessageSignature($this->message);
    }
    
    /**
     * Performs a check_authentication request.
     * 
     * @param array $options Options to pass to HTTP_Request
     * 
     * @return OpenID_Message Reponse to the check_authentication request
     */
    public function checkAuthentication(array $options = array())
    {
        $this->message->set('openid.mode', OpenID::MODE_CHECK_AUTHENTICATION);

        $opURL    = $this->message->get('openid.op_endpoint');
        $response = $this->directRequest($opURL, $this->message, $options);

        return new OpenID_Message($response->getResponseBody(),
                                              OpenID_Message::FORMAT_KV);
    }

    /**
     * Validates the openid.return_to parameter in the response.
     * 
     * @return void
     * @throws OpenID_Assertion_Exception on failure
     */
    protected function validateReturnTo()
    {
        $returnTo = $this->message->get('openid.return_to');
        OpenID::setLastEvent(__METHOD__,
                             'openid.return_to: ' . var_export($returnTo, true));

        // Validate openid.return_to
        if (!Validate::uri($returnTo)) {
            throw new OpenID_Assertion_Exception(
                'openid.return_to parameter is invalid or missing'
            );
        }

        $obj1 = new Net_URL2($returnTo);
        $obj2 = $this->requestedURL;

        $queryString1 = $obj1->getQueryVariables();
        $queryString2 = $obj2->getQueryVariables();

        $obj1->setQueryVariables(array());
        $obj2->setQueryVariables(array());

        if ($obj1->getURL() != $obj2->getURL()) {
            throw new OpenID_Assertion_Exception(
                'openid.return_to does not match the requested URL'
            );
        }

        if (!count($queryString1) && !count($queryString2)) {
            return;
        }

        foreach ($queryString1 as $param => $value) {
            if (!isset($queryString2[$param])
                || $queryString2[$param] != $value) {

                throw new OpenID_Assertion_Exception(
                    'openid.return_to parameters do not match requested url'
                );
            }
        }
    }

    /**
     * Validates and performs discovery on the openid.claimed_id paramter.
     * 
     * @return void
     * @throws OpenID_Assertion_Exception on failure
     */
    protected function validateDiscover()
    {
        $claimedID = $this->message->get('openid.claimed_id');
        if ($claimedID === null) {
            throw new OpenID_Assertion_Exception_NoClaimedID(
                'No claimed_id in message'
            );
        }

        if ($claimedID === OpenID::SERVICE_2_0_SERVER) {
            throw new OpenID_Assertion_Exception(
                'Claimed identifier cannot be an OP identifier'
            );
        }

        $url = new Net_URL2($claimedID);
        // Remove the fragment, per the spec
        $url->setFragment(false);

        $discover = OpenID_Discover::getDiscover($url->getURL(), self::getStore());
        if ($discover === false) {
            throw new OpenID_Assertion_Exception(
                'Unable to discover claimed_id'
            );
        }

        $opURL = array_shift($discover->services[0]->getURIs());
        if ($opURL !== $this->message->get('openid.op_endpoint')) {
            throw new OpenID_Assertion_Exception(
                'This OP is not authorized to issue assertions for this claimed id'
            );
        }
    }

    /**
     * Validates the openid.response_nonce parameter.
     * 
     * @return void
     * @throws OpenID_Assertion_Exception on invalid or existing nonce
     */
    protected function validateNonce()
    {
        $opURL         = $this->message->get('openid.op_endpoint');
        $responseNonce = $this->message->get('openid.response_nonce');

        $nonce = new OpenID_Nonce($opURL, $this->clockSkew);
        if (!$nonce->verifyResponseNonce($responseNonce)) {
            throw new OpenID_Assertion_Exception(
                'Invalid or already existing response_nonce'
            );
        }
    }

    /**
     * Validates the nonce embedded in the openid.return_to paramater and deletes 
     * it from storage.. (For use with OpenID 1.1 only)
     * 
     * @return void
     * @throws OpenID_Assertion_Exception on invalid or non-existing nonce
     */
    protected function validateReturnToNonce()
    {
        $returnTo = $this->message->get('openid.return_to');
        if ($returnTo === null) {
            // Must be a checkid_immediate negative assertion.
            $rtURL2   = new Net_URL2($this->message->get('openid.user_setup_url'));
            $rtqs     = $rtURL2->getQueryVariables();
            $returnTo = $rtqs['openid.return_to'];
            $identity = $rtqs['openid.identity'];
        }
        $netURL = new Net_URL2($returnTo);
        $qs     = $netURL->getQueryVariables();
        if (!array_key_exists(OpenID_Nonce::RETURN_TO_NONCE, $qs)) {
            throw new OpenID_Assertion_Exception(
                'Missing OpenID 1.1 return_to nonce'
            );
        }

        if (!isset($identity)) {
            $identity = $this->message->get('openid.identity');
        }
        $nonce     = $qs[OpenID_Nonce::RETURN_TO_NONCE];
        $discover  = OpenID_Discover::getDiscover($identity, self::getStore());
        $endPoint  = $discover->services[0];
        $opURL     = array_shift($endPoint->getURIs());
        $fromStore = self::getStore()->getNonce(urldecode($nonce), $opURL);

        // Observing
        $logMessage  = "returnTo: $returnTo\n";
        $logMessage .= 'OP URIs: ' . print_r($endPoint->getURIs(), true) . "\n";
        $logMessage .= 'Nonce in storage?: ' . var_export($fromStore, true) . "\n";
        OpenID::setLastEvent(__METHOD__, $logMessage);

        if (!$fromStore) {
            throw new OpenID_Assertion_Exception(
                'Invalid OpenID 1.1 return_to nonce in response'
            );
        }

        self::getStore()->deleteNonce($nonce, $opURL);
    }
}
?>
