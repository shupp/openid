<?php
/**
 * OpenID_RelyingPartyTest 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

require_once 'PHPUnit/Framework.php';
require_once 'OpenID/RelyingParty.php';
require_once 'OpenID/RelyingParty/Mock.php';
require_once 'OpenID/Store/Mock.php';
require_once 'OpenID/Observer/Log.php';
require_once 'OpenID/Discover.php';
require_once 'OpenID/Association.php';
require_once 'OpenID/Association/Request.php';
require_once 'OpenID.php';
require_once 'OpenID/Nonce.php';
require_once 'Net/URL2.php';

/**
 * OpenID_RelyingPartyTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_RelyingPartyTest extends PHPUnit_Framework_TestCase
{
    protected $id            = 'http://user.example.com';
    protected $returnTo      = 'http://openid.examplerp.com';
    protected $realm         = 'http://examplerp.com';
    protected $rp            = null;
    protected $opEndpointURL = 'http://exampleop.com';
    protected $discover      = null;
    protected $store         = null;
    protected $association   = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->rp = $this->getMock('OpenID_RelyingParty',
                                   array('getAssociationRequestObject',
                                         'getAssertionObject'),
                                   array($this->returnTo, $this->realm, $this->id));

        $this->store = $this->getMock('OpenID_Store_Mock',
                                      array('getDiscover',
                                            'getAssociation',
                                            'getNonce'));

        OpenID::setStore($this->store);

        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->id));

        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array($this->opEndpointURL));
        $opEndpoint->setVersion(OpenID::SERVICE_2_0_SERVER);
        $opEndpoints = new OpenID_ServiceEndpoints($this->id, $opEndpoint);

        $this->discover->expects($this->any())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $params = array(
            'uri'          => 'http://example.com',
            'expiresIn'    => 600,
            'created'      => 1240980848,
            'assocType'    => 'HMAC-SHA256',
            'assocHandle'  => 'foobar{}',
            'sharedSecret' => '12345qwerty'
        );

        $this->association = $this->getMock('OpenID_Association',
                                            array('checkMessageSignature'), 
                                            array($params));
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->rp          = null;
        $this->store       = null;
        $this->association = null;
    }

    /**
     * testEnableDisableAssociations 
     * 
     * @return void
     */
    public function testEnableDisableAssociations()
    {
        $this->rp->enableAssociations();
        $this->rp->disableAssociations();
    }

    /**
     * testSetClockSkew 
     * 
     * @return void
     */
    public function testSetClockSkew()
    {
        $this->rp->setClockSkew(50);
    }

    /**
     * testSetClockSkewFail 
     * 
     * @expectedException OpenID_Exception
     * @return void
     */
    public function testSetClockSkewFail()
    {
        $this->rp->setClockSkew('foo');
    }

    /**
     * testPrepare 
     * 
     * @return void
     */
    public function testPrepare()
    {
        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getAssociation')
                    ->will($this->returnValue($this->association));

        $auth = $this->rp->prepare();
        $this->assertType('OpenID_Auth_Request', $auth);
    }

    /**
     * testPrepareFail 
     * 
     * @expectedException OpenID_Exception
     * @return void
     */
    public function testPrepareFail()
    {
        $rp = new OpenID_RelyingParty($this->returnTo, $this->realm);
        $rp->prepare();
    }

    /**
     * testGetAssociationFail 
     * 
     * @return void
     */
    public function testGetAssociationFail()
    {
        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getAssociation')
                    ->will($this->returnValue(false));

        $assocRequest = $this->getMock('OpenID_Association_Request',
                                       array('associate'),
                                       array($this->opEndpointURL,
                                             OpenID::SERVICE_2_0_SERVER));

        $assocRequest->expects($this->once())
                     ->method('associate')
                     ->will($this->returnValue($this->association));

        $this->rp->expects($this->once())
                 ->method('getAssociationRequestObject')
                 ->will($this->returnValue($assocRequest));


        $auth = $this->rp->prepare();
        $this->assertType('OpenID_Auth_Request', $auth);
    }

    /**
     * testGetAssociation 
     * 
     * @return void
     */
    public function testGetAssociation()
    {
        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getAssociation')
                    ->will($this->returnValue(false));

        $assocRequest = $this->getMock('OpenID_Association_Request',
                                       array('associate'),
                                       array($this->opEndpointURL,
                                             OpenID::SERVICE_2_0_SERVER));

        $assocRequest->expects($this->once())
                     ->method('associate')
                     ->will($this->returnValue(false));

        $this->rp->expects($this->once())
                 ->method('getAssociationRequestObject')
                 ->will($this->returnValue($assocRequest));

        $auth = $this->rp->prepare();
        $this->assertType('OpenID_Auth_Request', $auth);
    }

    /**
     * testGetAssociationRequestObject 
     * 
     * @return void
     */
    public function testGetAssociationRequestObject()
    {
        $rp = new OpenID_RelyingParty_Mock($this->returnTo,
                                           $this->realm,
                                           $this->id);

        $a = $rp->returnGetAssociationRequestObject($this->opEndpointURL,
                                                    OpenID::SERVICE_2_0_SERVER);
        $this->assertType('OpenID_Association_Request', $a);
    }

    /**
     * Converts an OpenID_Message instance to a Net_URL2 instance based on 
     * $this->returnTo.  This was added to ease the transition from the old
     * verify() signature to the new one.
     * 
     * @param OpenID_Message $message Instance of OpenID_Message
     * 
     * @return Net_URL2
     */
    protected function messageToNetURL2(OpenID_Message $message)
    {
        return new Net_URL2($this->returnTo . '?' . $message->getHTTPFormat());
    }

    /**
     * testVerifyCancel 
     * 
     * @return void
     */
    public function testVerifyCancel()
    {
        $message = new OpenID_Message();
        $message->set('openid.mode', OpenID::MODE_CANCEL);

        $result = $this->rp->verify($this->messageToNetURL2($message), $message);
        $this->assertType('OpenID_Assertion_Result', $result);
        $this->assertFalse($result->success());
        $this->assertSame(OpenID::MODE_CANCEL, $result->getAssertionMethod());
    }

    /**
     * testVerifyOneOneImmediateFail 
     * 
     * @return void
     */
    public function testVerifyOneOneImmediateFail()
    {
        $url     = 'http://examplerp.com/';
        $message = new OpenID_Message();
        $message->set('openid.mode', OpenID::MODE_ID_RES);
        $message->set('openid.user_setup_url', $url);

        $result = $this->rp->verify($this->messageToNetURL2($message), $message);
        $this->assertType('OpenID_Assertion_Result', $result);
        $this->assertFalse($result->success());
        $this->assertSame(OpenID::MODE_ID_RES, $result->getAssertionMethod());
        $this->assertSame($url, $result->getUserSetupURL());
    }

    /**
     * testVerifyError 
     * 
     * @expectedException OpenID_Exception
     * @return void
     */
    public function testVerifyError()
    {
        $message = new OpenID_Message();
        $message->set('openid.mode', OpenID::MODE_ERROR);

        $result = $this->rp->verify($this->messageToNetURL2($message), $message);
    }

    /**
     * testVerifyInvalidMode 
     * 
     * @expectedException OpenID_Exception
     * @return void
     */
    public function testVerifyInvalidMode()
    {
        $message = new OpenID_Message();
        $message->set('openid.mode', 'foo');

        $result = $this->rp->verify($this->messageToNetURL2($message), $message);
    }

    /**
     * testVerifyAssociation 
     * 
     * @return void
     */
    public function testVerifyAssociation()
    {
        $this->store->expects($this->any())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getAssociation')
                    ->will($this->returnValue($this->association));

        $this->association->expects($this->once())
                          ->method('checkMessageSignature')
                          ->will($this->returnValue(true));

        $nonceObj = new OpenID_Nonce($this->opEndpointURL);
        $nonce    = $nonceObj->createNonce();

        $message = new OpenID_Message();
        $message->set('openid.mode', 'id_res');
        $message->set('openid.ns', OpenID::NS_2_0);
        $message->set('openid.return_to', $this->returnTo);
        $message->set('openid.claimed_id', $this->id);
        $message->set('openid.identity', $this->id);
        $message->set('openid.op_endpoint', $this->opEndpointURL);
        $message->set('openid.assoc_handle', '12345qwerty');
        $message->set('openid.response_nonce', $nonce);


        $this->assertType('OpenID_Assertion_Result',
                          $this->rp->verify($this->messageToNetURL2($message),
                          $message));
    }

    /**
     * testVerifyUnsolicited 
     * 
     * @return void
     */
    public function testVerifyUnsolicited()
    {
        $log = new OpenID_Observer_Log;
        OpenID::attach($log);
        $this->rp = $this->getMock('OpenID_RelyingParty',
                                   array('getAssociationRequestObject',
                                         'getAssertionObject'),
                                   array($this->returnTo, $this->realm));

        $assertion = $this->getMock('OpenID_Assertion',
                                    array('checkAuthentication'),
                                    array(),
                                    '',
                                    false);

        $authMessage = new OpenID_Message;
        $authMessage->set('is_valid', 'true');

        $assertion->expects($this->once())
                  ->method('checkAuthentication')
                  ->will($this->returnValue($authMessage));

        $this->rp->expects($this->once())
                 ->method('getAssertionObject')
                 ->will($this->returnValue($assertion));

        $this->store->expects($this->any())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getAssociation')
                    ->will($this->returnValue($this->association));

        $this->association->expects($this->once())
                          ->method('checkMessageSignature')
                          ->will($this->returnValue(true));

        $nonceObj = new OpenID_Nonce($this->opEndpointURL);
        $nonce    = $nonceObj->createNonce();

        $message = new OpenID_Message();
        $message->set('openid.mode', 'id_res');
        $message->set('openid.ns', OpenID::NS_2_0);
        $message->set('openid.return_to', $this->returnTo);
        $message->set('openid.claimed_id', $this->id);
        $message->set('openid.identity', $this->id);
        $message->set('openid.op_endpoint', $this->opEndpointURL);
        $message->set('openid.assoc_handle', '12345qwerty');
        $message->set('openid.response_nonce', $nonce);


        $this->assertType('OpenID_Assertion_Result',
                          $this->rp->verify($this->messageToNetURL2($message),
                                            $message));
    }

    /**
     * testVerifyCheckAuthentication 
     * 
     * @return void
     */
    public function testVerifyCheckAuthentication()
    {
        $this->store->expects($this->any())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getNonce')
                    ->will($this->returnValue(false));

        $nonceObj = new OpenID_Nonce($this->opEndpointURL);
        $nonce    = $nonceObj->createNonce();

        $message = new OpenID_Message();
        $message->set('openid.mode', 'id_res');
        $message->set('openid.ns', OpenID::NS_2_0);
        $message->set('openid.return_to', $this->returnTo);
        $message->set('openid.claimed_id', $this->id);
        $message->set('openid.identity', $this->id);
        $message->set('openid.op_endpoint', $this->opEndpointURL);
        $message->set('openid.invalidate_handle', '12345qwerty');
        $message->set('openid.response_nonce', $nonce);

        $assertion = $this->getMock('OpenID_Assertion',
                                    array('checkAuthentication'),
                                    array($message, new Net_URL2($this->returnTo)));

        $authMessage = new OpenID_Message;
        $authMessage->set('is_valid', 'true');

        $assertion->expects($this->once())
                  ->method('checkAuthentication')
                  ->will($this->returnValue($authMessage));

        $this->rp->expects($this->once())
                 ->method('getAssertionObject')
                 ->will($this->returnValue($assertion));

        $this->assertType('OpenID_Assertion_Result',
                          $this->rp->verify($this->messageToNetURL2($message),
                                            $message));
    }

    /**
     * testGetAssertionObject 
     * 
     * @return void
     */
    public function testGetAssertionObject()
    {
        $this->store->expects($this->any())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getNonce')
                    ->will($this->returnValue(false));

        $nonceObj = new OpenID_Nonce($this->opEndpointURL);
        $nonce    = $nonceObj->createNonce();

        $message = new OpenID_Message();
        $message->set('openid.mode', 'id_res');
        $message->set('openid.ns', OpenID::NS_2_0);
        $message->set('openid.return_to', $this->returnTo);
        $message->set('openid.claimed_id', $this->id);
        $message->set('openid.identity', $this->id);
        $message->set('openid.op_endpoint', $this->opEndpointURL);
        $message->set('openid.invalidate_handle', '12345qwerty');
        $message->set('openid.response_nonce', $nonce);

        $rp = new OpenID_RelyingParty_Mock($this->id, $this->returnTo, $this->realm);
        $this->assertType('OpenID_Assertion',
                          $rp->returnGetAssertionObject($message,
                          new Net_URL2($this->returnTo)));
    }
}
?>
