<?php
/**
 * OpenID_Association 
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
require_once 'OpenID/Association/Exception.php';
require_once 'OpenID.php';
require_once 'OpenID/Message.php';
require_once 'Validate.php';

/**
 * OpenID_Association 
 * 
 * A class that represents an association.  This class can be serialized for 
 * storage.  It also allows you to add and check signatures of an OpenID_Message.
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 * @see       OpenID_Association_Request::buildRequest()
 */
class OpenID_Association
{
    /**
     * URI of the OP Endpoint
     * 
     * @var string
     */
    protected $uri = null;

    /**
     * expires_in paramater of the association.  Time is in seconds.
     * 
     * @var mixed
     */
    protected $expiresIn = null;

    /**
     * Unix timestamp of when this association was created.
     * 
     * @var int
     */
    protected $created = null;

    /**
     * assoc_type parameter of the association.  Should be one of HMAC-SHA1 or 
     * HMAC-SHA256
     * 
     * @var string
     */
    protected $assocType = null;

    /**
     * assoc_handle parameter of the association.
     * 
     * @var string
     */
    protected $assocHandle = null;

    /**
     * In the association response, this is also referred to as the "mac_key", or is
     * derived from the "enc_mac_key" if the session used encryption.
     * 
     * @var mixed
     */
    protected $sharedSecret = null;

    /**
     * Required parameters for storing an association.
     * 
     * @see __construct()
     * @var array
     */
    protected $requiredParams = array(
        'uri',
        'expiresIn',
        'created',
        'assocType',
        'assocHandle',
        'sharedSecret'
    );

    /**
     * Local list of supported association types.
     * 
     * @see $assocType
     * @see __construct()
     * @var array
     */
    protected $supportedTypes = array(
        OpenID::ASSOC_TYPE_HMAC_SHA1,
        OpenID::ASSOC_TYPE_HMAC_SHA256
    );

    /**
     * Validates some association values before setting them as member variables.
     * 
     * @param array $params Array of relevant parameters from the association
     *                      response
     * 
     * @throws OpenID_Association_Exception if the response is not valid
     * @return void
     */
    public function __construct(array $params)
    {
        // Make sure required params are present
        foreach ($this->requiredParams as $key) {
            if (!isset($params[$key])) {
                throw new OpenID_Association_Exception(
                    "Missing parameter: $key"
                );
            }
        }

        // Validate URI
        if (!Validate::uri($params['uri'])) {
            throw new OpenID_Association_Exception(
                "Invalid uri: " . $params['uri']
            );
        }

        // Validate assocType
        if (!in_array(strtoupper($params['assocType']), $this->supportedTypes)) {
            throw new OpenID_Association_Exception(
                "Invalid association type: " . $params['assocType']
            );
        }

        // Set values
        reset($this->requiredParams);
        foreach ($this->requiredParams as $key) {
            $this->$key = $params[$key];
        }
    }

    /**
     * Allows access to association data via $assoc->name
     * 
     * @param string $name Name of the item to get
     * 
     * @return mixed Value
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * Gets the algo part of the assoc_type (strips 'HMAC-')
     * 
     * @return string Algorithm part of the assoc_type handle
     */
    public function getAlgorithm()
    {
        return str_replace('HMAC-', '', $this->assocType);
    }

    /**
     * Checks the signature of an OpenID_Message using this association
     * 
     * @param OpenID_Message $message Instance of OpenID_Message
     * 
     * @throws OpenID_Association_Exception if the handles don't match
     * @return bool true if the signatures match, false otherwise
     */
    public function checkMessageSignature(OpenID_Message $message)
    {
        // Make sure the handles match for this OP and response
        if ($this->assocHandle != $message->get('openid.assoc_handle')) {

            throw new OpenID_Association_Exception(
                'Associaiton handles do not match'
            );
        }

        if (!strlen($message->get('openid.signed'))) {
            return false;
        }
        $list = explode(',', $message->get('openid.signed'));

        // Create a message with only keys in the signature
        $signedOnly = new OpenID_Message;
        foreach ($list as $key) {
            $signedOnly->set($key, $message->get('openid.' . $key));
        }

        $signedOnlyDigest = hash_hmac($this->getAlgorithm(),
                                      $signedOnly->getKVFormat(),
                                      base64_decode($this->sharedSecret),
                                      true);

        return base64_encode($signedOnlyDigest) == $message->get('openid.sig');
    }

    /**
     * Signs an OpenID_Message instance
     * 
     * @param OpenID_Message $message Message to be signed
     * 
     * @throws OpenID_Association_Exception if the message is already signed,
               or the association handles do not match
     * @return void
     */
    public function signMessage(OpenID_Message $message)
    {
        if ($message->get('openid.sig') !== null ||
            $message->get('openid.signed') !== null) {
            throw new OpenID_Association_Exception(
                'This message appears to be already signed'
            );
        }

        // Make sure the handles match for this OP and response
        if ($this->assocHandle != $message->get('openid.assoc_handle')) {

            throw new OpenID_Association_Exception(
                'Association handles do not match'
            );
        }

        $keys = array('signed');
        foreach ($message->getArrayFormat() as $key => $val) {
            if (strncmp('openid.', $key, 7) == 0) {
                $keys[] = substr($key, 7);
            }
        }
        sort($keys);
        $message->set('openid.signed', implode(',', $keys));

        $signedMessage = new OpenID_Message;

        foreach ($keys as $key) {
            $signedMessage->set($key, $message->get('openid.' . $key));
        }

        $rawSignature = hash_hmac($this->getAlgorithm(),
                                  $signedMessage->getKVFormat(),
                                  base64_decode($this->sharedSecret),
                                  true);

        $message->set('openid.sig', base64_encode($rawSignature));
    }
}
?>
