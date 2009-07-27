<?php
/**
 * OpenID_Association_RequestTest 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

require_once 'PHPUnit/Framework.php';
require_once 'OpenID/Association/Request.php';
require_once 'HTTP/Request.php';
require_once 'OpenID/Message.php';
require_once 'Crypt/DiffieHellman.php';

/**
 * OpenID_Association_RequestTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Association_RequestTest extends PHPUnit_Framework_TestCase
{
    protected $URI          = 'https://example.com';
    protected $handle       = '1234567890';
    protected $sessionType  = null;
    protected $httpRequest  = null;
    protected $assocRequest = null;
    protected $rpDH         = null;
    protected $opDH         = null;
    protected $message      = null;
    protected $macKey       = '12345';

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->sessionType = 'sha256';

        $this->message = new OpenID_Message();
        $this->message->set('ns', OpenID::NS_2_0);
        $this->message->set('session_type', 'sha256');
        $this->message->set('assoc_handle', $this->handle);
        $this->message->set('expires_in', '10');

        $this->rpDH = new Crypt_DiffieHellman(563, 5, 9);
        $this->rpDH->generateKeys();
        $this->opDH = new Crypt_DiffieHellman(563, 5, 13);
        $this->opDH->generateKeys();

        $this->httpRequest  = $this->getMock('HTTP_Request',
                                             array('sendRequest', 'getResponseBody'),
                                             array($this->URI));
        $this->assocRequest = $this->getMock('OpenID_Association_Request',
                                             array('directRequest'),
                                             array($this->URI,
                                                   OpenID::SERVICE_2_0_SERVER,
                                                   $this->rpDH));

        $this->message->set('dh_server_public',
                            base64_encode($this
                                ->opDH
                                ->getPublicKey(Crypt_DiffieHellman::BTWOC)));
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $array = array('httpRequest',
                       'assocRequest',
                       'sessionType',
                       'opDH',
                       'rpDH',
                       'message');

        foreach ($array as $item) {
            $this->{$item} = null;
        }
    }

    /**
     * testAssociate 
     * 
     * @return void
     */
    public function testAssociate()
    {
        // generate mac key
        $assocType = str_replace('HMAC-', '', $this->assocRequest
                                                   ->getAssociationType());
        $xorSecret = $this->xorSecret($this->rpDH->getPublicKey(),
                                      $this->macKey,
                                      $assocType);
        $this->message->set('enc_mac_key', base64_encode($xorSecret));
        $this->httpRequest->expects($this->any())
                          ->method('getResponseBody')
                          ->will($this->returnValue($this->message->getKVFormat()));
        $this->assocRequest->expects($this->any())
                           ->method('directRequest')
                           ->will($this->returnValue($this->httpRequest));

        $this->assocRequest->associate();
    }

    /**
     * testDefaultDH 
     * 
     * @return void
     */
    public function testDefaultDH()
    {
        $this->assocRequest = $this->getMock('OpenID_Association_Request',
                                             array('directRequest'),
                                             array($this->URI,
                                                   OpenID::SERVICE_2_0_SERVER));
        $this->testAssociate();
    }

    /**
     * xorSecret 
     * 
     * @param mixed $pubKey Public key
     * @param mixed $secret Secret
     * @param mixed $algo   Algorithm
     * 
     * @return string The mac_key
     */
    protected function xorSecret($pubKey, $secret, $algo)
    {
        $this->opDH->computeSecretKey($pubKey, Crypt_DiffieHellman::BINARY);
        $sharedSecret   = $this->opDH
                                ->getSharedSecretKey(Crypt_DiffieHellman::BTWOC);
        $bytes          = mb_strlen(bin2hex($secret), '8bit') / 2;
        $hash_dh_shared = hash($algo, $sharedSecret, true);

        $xsecret = '';
        for ($i = 0; $i < $bytes; $i++) {
            $xsecret .= chr(ord($secret[$i]) ^ ord($hash_dh_shared[$i]));
        }
        return $xsecret;
    }

    /**
     * testGetResponse 
     * 
     * @return void
     */
    public function testGetResponse()
    {
        $this->message->set('enc_mac_key', 'foo');
        $this->httpRequest->expects($this->any())
                          ->method('getResponseBody')
                          ->will($this->returnValue($this->message->getKVFormat()));
        $this->assocRequest->expects($this->any())
                           ->method('directRequest')
                           ->will($this->returnValue($this->httpRequest));

        $this->assocRequest->associate();
        $this->assertSame($this->message->getArrayFormat(),
                          $this->assocRequest->getResponse());
    }

    /**
     * testConstructFail 
     * 
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testConstructFail()
    {
        $ar = new OpenID_Association_Request($this->URI, 'http://example.com');
    }

    /**
     * testConstructWithOpenID1 
     * 
     * @return void
     */
    public function testConstructWithOpenID1()
    {
        $ar = new OpenID_Association_Request($this->URI, OpenID::SERVICE_1_1_SIGNON);
    }

    /**
     * testGetOPEndpointURL 
     * 
     * @return void
     */
    public function testGetOPEndpointURL()
    {
        $this->assertSame($this->URI, $this->assocRequest->getEndpointURL());
    }

    /**
     * testAssociateMultipleRequests 
     * 
     * @return void
     */
    public function testAssociateMultipleRequests()
    {
        $this->message = new OpenID_Message();
        $this->message->set('ns', OpenID::NS_2_0);
        $this->message->set('mode', OpenID::MODE_ERROR);
        $this->message->set('error_code', 'unsupported-type');
        $this->message->set('session_type',
                            OpenID::SESSION_TYPE_NO_ENCRYPTION);
        $this->message->set('dh_server_public',
                            base64_encode($this
                                ->opDH
                                ->getPublicKey(Crypt_DiffieHellman::BTWOC)));

        $this->httpRequest->expects($this->any())
                          ->method('getResponseBody')
                          ->will($this->returnValue($this->message->getKVFormat()));
        $this->assocRequest->expects($this->any())
                           ->method('directRequest')
                           ->will($this->returnValue($this->httpRequest));

        $this->assocRequest->associate();
    }

    /**
     * testBuildAssociationNoEncryption 
     * 
     * @return void
     */
    public function testBuildAssociationNoEncryption()
    {
        $this->message->set('mac_key', $this->macKey);
        $this->assocRequest
             ->setSessionType(OpenID::SESSION_TYPE_NO_ENCRYPTION);

        $this->httpRequest->expects($this->any())
                          ->method('getResponseBody')
                          ->will($this->returnValue($this->message->getKVFormat()));
        $this->assocRequest->expects($this->any())
                           ->method('directRequest')
                           ->will($this->returnValue($this->httpRequest));

        $this->assocRequest->associate();
    }

    /**
     * testBuildAssociationFailPublicKey 
     * 
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testBuildAssociationFailNoPublicKey()
    {
        $this->message->delete('dh_server_public');
        $this->httpRequest->expects($this->any())
                          ->method('getResponseBody')
                          ->will($this->returnValue($this->message->getKVFormat()));
        $this->assocRequest->expects($this->any())
                           ->method('directRequest')
                           ->will($this->returnValue($this->httpRequest));

        $this->assocRequest->associate();
    }

    /**
     * testBuildAssociationFailNoMacKey 
     * 
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testBuildAssociationFailNoMacKey()
    {
        $this->assocRequest
             ->setSessionType(OpenID::SESSION_TYPE_NO_ENCRYPTION);

        $this->httpRequest->expects($this->any())
                          ->method('getResponseBody')
                          ->will($this->returnValue($this->message->getKVFormat()));
        $this->assocRequest->expects($this->any())
                           ->method('directRequest')
                           ->will($this->returnValue($this->httpRequest));

        $this->assocRequest->associate();
    }

    /**
     * testSetSessionTypeFailNoEncryption 
     * 
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testSetSessionTypeFailNoEncryption()
    {
        $this->URI = 'http://example.com';
        $this->setUP();
        $this->testAssociateMultipleRequests();
    }

    /**
     * testSetSessionTypeFailInvalidType 
     * 
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testSetSessionTypeFailInvalidType()
    {
        $this->assocRequest->setSessionType('foo');
    }

    /**
     * testSetAssociationTypeFail 
     * 
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testSetAssociationTypeFail()
    {
        $this->assocRequest->setAssociationType('foo');
    }

    /**
     * testAssociateMultipleRequestsSha1 
     * 
     * @return void
     */
    public function testAssociateMultipleRequestsSha1()
    {
        $this->message = new OpenID_Message();
        $this->message->set('ns', OpenID::NS_2_0);
        $this->message->set('mode', OpenID::MODE_ERROR);
        $this->message->set('error_code', 'unsupported-type');
        $this->message->set('session_type',
                            OpenID::SESSION_TYPE_DH_SHA1);
        $this->message->set('assoc_type',
                            OpenID::ASSOC_TYPE_HMAC_SHA1);
        $this->message->set('dh_server_public',
                            base64_encode($this
                                ->opDH
                                ->getPublicKey(Crypt_DiffieHellman::BTWOC)));

        $this->httpRequest->expects($this->any())
                          ->method('getResponseBody')
                          ->will($this->returnValue($this->message->getKVFormat()));
        $this->assocRequest->expects($this->any())
                           ->method('directRequest')
                           ->will($this->returnValue($this->httpRequest));

        $this->assocRequest->associate();
    }
}
?>
