<?php
/**
 * OpenID_Association_Request 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Association_Common
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
require_once 'OpenID/Association/Exception.php';
require_once 'OpenID/Association/Common.php';
require_once 'OpenID/Association.php';
require_once 'OpenID/Message.php';
require_once 'Crypt/DiffieHellman.php';

/**
 * OpenID_Association_Request 
 * 
 * Request object for establishing OpenID Associations.
 * 
 * @uses      OpenID_Association_Common
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Association_Request extends OpenID_Association_Common
{
    /**
     * OpenID provider endpoint URL
     * 
     * @var string
     */
    protected $opEndpointURL = null;

    /**
     * Contains contents of the association request
     * 
     * @var OpenID_Message
     */
    protected $message = null;

    /**
     * Version of OpenID in use.  This determines which algorithms we can use.
     * 
     * @var string
     */
    protected $version = null;

    /**
     * The association request response in array format
     * 
     * @var array
     * @see getResponse()
     */
    protected $response = array();

    /**
     * Whether or not the sharedSecretKey has been computed or not
     * 
     * @see getSharedSecretKey()
     * @var int
     */
    protected $sharedKeyComputed = 0;

    /**
     * Sets the arguments passed in, as well as creates the request message.
     * 
     * @param string              $opEndpointURL URL of OP Endpoint
     * @param string              $version       Version of OpenID in use
     * @param Crypt_DiffieHellman $dh            Custom Crypt_DiffieHellman
     *                                           instance
     * 
     * @return void
     */
    public function __construct($opEndpointURL,
                                $version,
                                Crypt_DiffieHellman $dh = null)
    {
        if (!array_key_exists($version, OpenID::$versionMap)) {
            throw new OpenID_Association_Exception(
                'Invalid version'
            );
        }
        $this->version       = $version.
        $this->opEndpointURL = $opEndpointURL;
        $this->message       = new OpenID_Message;

        if ($dh) {
            $this->setCryptDiffieHellman($dh);
        }

        // Set defaults
        $this->message->set('openid.mode', OpenID::MODE_ASSOCIATE);
        if (OpenID::$versionMap[$version] == OpenID::NS_2_0) {
            $this->message->set('openid.ns', OpenID::NS_2_0);
            $this->message->set('openid.assoc_type', self::ASSOC_TYPE_HMAC_SHA256);
            $this->message->set('openid.session_type', self::SESSION_TYPE_DH_SHA256);
        } else {
            $this->message->set('openid.assoc_type', self::ASSOC_TYPE_HMAC_SHA1);
            $this->message->set('openid.session_type', self::SESSION_TYPE_DH_SHA1);
        }
    }

    /**
     * Sends the association request.  Loops over errors and adapts to 
     * 'unsupported-type' responses.
     * 
     * @return mixed OpenID_Association on success, false on failure
     * @see buildAssociation()
     * @see sendAssociationRequest()
     */
    public function associate()
    {
        $count = 0;
        while ($count < 2) {
            // Easier to operate on array format here
            $response = $this->sendAssociationRequest()->getArrayFormat();

            if (isset($response['assoc_handle'])) {
                $this->response = $response;
                return $this->buildAssociation($response);
            }

            if (isset($response['mode'])
                && $response['mode'] == OpenID::MODE_ERROR
                && isset($response['error_code'])
                && $response['error_code'] == 'unsupported-type') {
    
                if (isset($response['assoc_type'])) {
                    $this->setAssociationType($response['assoc_type']);
                }
                if (isset($response['session_type'])) {
                    $this->setSessionType($response['session_type']);
                }
            }
            $count++;
        }
        return false;
    }

    /**
     * Build the OpenID_Association class based on the association response
     * 
     * @param array $response Association response in array format
     * 
     * @return OpenID_Association
     * @see associate()
     */
    protected function buildAssociation(array $response)
    {
        $params                = array();
        $params['created']     = time();
        $params['expiresIn']   = $response['expires_in'];
        $params['uri']         = $this->opEndpointURL;
        $params['assocType']   = $this->getAssociationType();
        $params['assocHandle'] = $response['assoc_handle'];

        if ($this->getSessionType() === self::SESSION_TYPE_NO_ENCRYPTION) {
            if (!isset($response['mac_key'])) {
                throw new OpenID_Association_Exception(
                    'Missing mac_key in association response'
                );
            }
            $params['sharedSecret'] = $response['mac_key'];
        } else {
            if (!isset($response['dh_server_public'])) {
                throw new OpenID_Association_Exception(
                    'Missing dh_server_public parameter in association response'
                );
            }


            $pubKey       = base64_decode($response['dh_server_public']);
            $sharedSecret = $this->getSharedSecretKey($pubKey);

            $opSecret       = base64_decode($response['enc_mac_key']);
            $bytes          = mb_strlen(bin2hex($opSecret), '8bit') / 2;
            $algo           = str_replace('HMAC-', '', $params['assocType']);
            $hash_dh_shared = hash($algo, $sharedSecret, true);

            $xsecret = '';
            for ($i = 0; $i < $bytes; $i++) {
                $xsecret .= chr(ord($opSecret[$i]) ^ ord($hash_dh_shared[$i]));
            }

            $params['sharedSecret'] = base64_encode($xsecret);
        }

        return new OpenID_Association($params);
    }

    /**
     * Actually sends the assocition request to the OP Endpoing URL.
     * 
     * @return OpenID_Message
     * @see associate()
     */
    protected function sendAssociationRequest()
    {
        $this->initDH();

        $response = $this->directRequest($this->opEndpointURL, $this->message);
        $message  = new OpenID_Message($response->getResponseBody(),
                                       OpenID_Message::FORMAT_KV);

        OpenID::setLastEvent(__METHOD__, print_r($message->getArrayFormat(), true));

        return $message;
    }

    /**
     * Gets the last association response
     * 
     * @return void
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Initialized the diffie-hellman parameters for the association request.
     * 
     * @return void
     */
    protected function initDH()
    {
        if ($this->message->get('openid.session_type')
            == self::SESSION_TYPE_NO_ENCRYPTION) {

            $this->message->delete('openid.dh_consumer_public');
            $this->message->delete('openid.dh_modulus');
            $this->message->delete('openid.dh_gen');
            return;
        }

        if ($this->dh === null) {
            $this->dh = new Crypt_DiffieHellman(self::DH_DEFAULT_MODULUS,
                                                self::DH_DEFAULT_GENERATOR);
            $this->dh->generateKeys();
        }

        // Set public key
        $this->message->set('openid.dh_consumer_public',
                            base64_encode($this->publicKey));

        // Set modulus
        $prime = $this->dh->getPrime(Crypt_DiffieHellman::BTWOC);
        $this->message->set('openid.dh_modulus', base64_encode($prime));

        // Set prime
        $gen = $this->dh->getGenerator(Crypt_DiffieHellman::BTWOC);
        $this->message->set('openid.dh_gen', base64_encode($gen));
    }

    /**
     * Gets privateKey and publicKey from $this->dh.  Just a shortcut.
     * 
     * @param string $name Can be publicKey or privateKey
     * 
     * @return BTWOC representation of the number
     */
    public function __get($name)
    {
        $keys = array('privateKey', 'publicKey');
        if (in_array($name, $keys)) {
            $func = 'get' . ucfirst($name);
            return $this->dh->$func(Crypt_DiffieHellman::BTWOC);
        }
    }

    /**
     * Gets the shared secret key in BTWOC format.  Computes the key if it has not 
     * been computed already.
     * 
     * @param string $publicKey Public key of the OP
     * 
     * @return BTWOC representation of the number
     */
    public function getSharedSecretKey($publicKey)
    {
        if ($this->sharedKeyComputed == 0) {
            $this->dh->computeSecretKey($publicKey, Crypt_DiffieHellman::BINARY);
            $this->sharedKeyComputed = 1;
        }
        return $this->dh->getSharedSecretKey(Crypt_DiffieHellman::BTWOC);
    } 

    /**
     * Sets he association type for the request.  Can be sha1 or sha256.
     * 
     * @param string $type sha1 or sha256
     * 
     * @throws OpenID_Association_Exception on invalid type
     * @return void
     */
    public function setAssociationType($type)
    {
        switch ($type) {
        case self::ASSOC_TYPE_HMAC_SHA1:
        case self::ASSOC_TYPE_HMAC_SHA256:
            $this->message->set('openid.assoc_type', $type);
            break;
        default:
            throw new OpenID_Association_Exception("Invalid assoc_type: $type");
        }
    }

    /**
     * Gets the current association type
     * 
     * @return void
     */
    public function getAssociationType()
    {
        return $this->message->get('openid.assoc_type');
    }

    /**
     * Sets the session type.  Can be sha1, sha256, or no-encryption
     * 
     * @param string $type sha1, sha256, or no-encryption
     * 
     * @throws OpenID_Association_Exception on invalid type, or if you set 
     *         no-encryption for an OP URL that doesn't support HTTPS
     * @return void
     */
    public function setSessionType($type)
    {
        switch ($type) {
        case self::SESSION_TYPE_NO_ENCRYPTION:
            // Make sure we're using SSL
            if (!preg_match('@^https://@i', $this->opEndpointURL)) {
                throw new OpenID_Association_Exception(
                    'Un-encrypted sessions require HTTPS'
                );
            }
            $this->message->set('openid.session_type',
                                self::SESSION_TYPE_NO_ENCRYPTION);
            break;
        case self::SESSION_TYPE_DH_SHA1:
        case self::SESSION_TYPE_DH_SHA256:
            $this->message->set('openid.session_type', $type);
            break;
        default:
            throw new OpenID_Association_Exception("Invalid session_type: $type");
        }
    }

    /**
     * Gets the current session type
     * 
     * @return string Current session type (sha1, sha256, or no-encryption)
     */
    public function getSessionType()
    {
        return $this->message->get('openid.session_type');
    }

    /**
     * Gets the OP Endpoint URL
     * 
     * @return string OP Endpoint URL
     */
    public function getEndpointURL()
    {
        return $this->opEndpointURL;
    }
}
?>
