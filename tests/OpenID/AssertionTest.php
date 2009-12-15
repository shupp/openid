<?php
/**
 * OpenID_AssertionTest 
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
require_once 'OpenID/Assertion.php';
require_once 'OpenID/Discover.php';
require_once 'OpenID/Store/Mock.php';
require_once 'OpenID/ServiceEndpoint.php';
require_once 'OpenID/ServiceEndpoints.php';
require_once 'OpenID/Nonce.php';
require_once 'OpenID/Association.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'HTTP/Request2.php';

/**
 * OpenID_AssertionTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_AssertionTest extends PHPUnit_Framework_TestCase
{
    protected $message       = null;
    protected $requestedURL  = 'http://examplerp.com';
    protected $claimedID     = 'http://user.example.com';
    protected $opEndpointURL = 'http://exampleop.com';
    protected $store         = null;
    protected $discover      = null;
    protected $assertion     = null;
    protected $clockSkew     = 600;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->store = $this->getMock('OpenID_Store_Mock',
                                      array('getDiscover', 'getNonce'));

        $nonce = new OpenID_Nonce($this->opEndpointURL);

        $this->message = new OpenID_Message;
        $this->message->set('openid.ns', OpenID::NS_2_0);
        $this->message->set('openid.return_to', $this->requestedURL);
        $this->message->set('openid.op_endpoint', $this->opEndpointURL);
        $this->message->set('openid.claimed_id', $this->claimedID);
        $this->message->set('openid.response_nonce', $nonce->createNonce());
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->message   = null;
        $this->store     = null;
        $this->assertion = null;
        $this->discover  = null;
    }

    /**
     * createObjects 
     * 
     * @return void
     */
    protected function createObjects()
    {
        OpenID::setStore($this->store);

        $this->assertion = $this->getMock('OpenID_Assertion',
                                          array('getHTTPRequest2Instance'),
                                          array($this->message,
                                                new Net_URL2($this->requestedURL),
                                                $this->clockSkew));
    }

    /**
     * testValidateReturnTo 
     * 
     * @return void
     */
    public function testValidateReturnTo()
    {
        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array($this->opEndpointURL));
        $opEndpoints = new OpenID_ServiceEndpoints($this->claimedID, $opEndpoint);

        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));
        $this->discover->expects($this->once())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getNonce')
                    ->will($this->returnValue(false));
        $this->createObjects();
    }

    /**
     * testValidateReturnToOneOneImmediateNegative 
     * 
     * @return void
     */
    public function testValidateReturnToOneOneImmediateNegative()
    {
        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array($this->opEndpointURL));
        $opEndpoints = new OpenID_ServiceEndpoints($this->claimedID, $opEndpoint);

        $nonce      = new OpenID_Nonce($this->opEndpointURL);
        $nonceValue = $nonce->createNonce();

        $rt = new Net_URL2('http://examplerp.com');
        $rt->setQueryVariable(OpenID_Nonce::RETURN_TO_NONCE, $nonceValue);

        $setupMessage = new OpenID_Message();
        $setupMessage->set('openid.identity', $this->claimedID);
        $setupMessage->set('openid.return_to', $rt->getURL());
        $setupMessage->set(OpenID_Nonce::RETURN_TO_NONCE, $nonceValue);

        $this->message = new OpenID_Message();
        $this->message->set('openid.mode', OpenID::MODE_ID_RES);
        $this->message->set(OpenID_Nonce::RETURN_TO_NONCE, $nonceValue);
        $this->message->set('openid.user_setup_url',
                          'http://examplerp.com/?' . $setupMessage->getHTTPFormat());

        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));
        $this->discover->expects($this->once())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->any())
                    ->method('getNonce')
                    ->will($this->returnValue($nonceValue));
        $this->createObjects();
    }

    /**
     * testValidateReturnToWithQueryStringParameters 
     * 
     * @return void
     */
    public function testValidateReturnToWithQueryStringParameters()
    {
        $this->requestedURL = $this->requestedURL . '?foo=bar';
        $this->setUp();

        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array($this->opEndpointURL));
        $opEndpoints = new OpenID_ServiceEndpoints($this->claimedID, $opEndpoint);

        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));
        $this->discover->expects($this->once())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getNonce')
                    ->will($this->returnValue(false));
        $this->createObjects();
    }

    /**
     * testValidateReturnToFailInvalidURI 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testValidateReturnToFailInvalidURI()
    {
        $this->message->set('openid.return_to', 'http:///foo&bar');
        $this->createObjects();
    }

    /**
     * testValidateReturnToFailDifferentURLs 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testValidateReturnToFailDifferentURLs()
    {
        $this->message->set('openid.return_to', 'http://foo.com');
        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));
        $this->createObjects();
    }

    /**
     * testValidateReturnToFailDifferentQueryStringParameters 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testValidateReturnToFailDifferentQueryStringParameters()
    {
        $this->message->set('openid.return_to', $this->requestedURL . '?foo=bar');
        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));
        $this->createObjects();
    }

    /**
     * testValidateReturnToNonce 
     * 
     * @return void
     */
    public function testValidateReturnToNonce()
    {
        $nonce      = new OpenID_Nonce($this->opEndpointURL);
        $nonceValue = $nonce->createNonce();

        $this->message->delete('openid.ns');
        $this->message->delete('openid.claimed_id');
        $this->message->set('openid.identity', $this->claimedID);
        $rtnonce = $this->requestedURL . '?' . OpenID_Nonce::RETURN_TO_NONCE 
                   . '=' . urlencode($nonceValue);
        $this->message->set('openid.return_to', $rtnonce);
        $this->requestedURL = $rtnonce;

        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));

        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array($this->opEndpointURL));
        $opEndpoints = new OpenID_ServiceEndpoints($this->claimedID, $opEndpoint);

        $this->discover->expects($this->once())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->any())
                    ->method('getNonce')
                    ->will($this->returnValue($rtnonce));

        $this->createObjects();
    }

    /**
     * testValidateReturnToNonceFailInvalid 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testValidateReturnToNonceFailInvalid()
    {
        $nonce      = new OpenID_Nonce($this->opEndpointURL);
        $nonceValue = $nonce->createNonce();

        $this->message->delete('openid.ns');
        $this->message->delete('openid.claimed_id');
        $this->message->set('openid.identity', $this->claimedID);
        $rtnonce = $this->requestedURL . '?' . OpenID_Nonce::RETURN_TO_NONCE 
                   . '=' . urlencode($nonceValue);
        $this->message->set('openid.return_to', $rtnonce);
        $this->requestedURL = $rtnonce;

        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));

        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array($this->opEndpointURL));
        $opEndpoints = new OpenID_ServiceEndpoints($this->claimedID, $opEndpoint);

        $this->discover->expects($this->once())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->any())
                    ->method('getNonce')
                    ->will($this->returnValue(false));

        $this->createObjects();
    }

    /**
     * testValidateReturnToNonceFailMissing 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testValidateReturnToNonceFailMissing()
    {
        $this->message->delete('openid.ns');
        $this->message->delete('openid.claimed_id');
        $this->message->set('openid.identity', $this->claimedID);

        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));

        $this->createObjects();
    }

    /**
     * testValidateDiscoverFailNoClaimedID 
     * 
     * @expectedException OpenID_Assertion_Exception_NoClaimedID
     * @return void
     */
    public function testValidateDiscoverFailNoClaimedID()
    {
        $this->message->delete('openid.claimed_id');
        $this->createObjects();
    }

    /**
     * testValidateDiscoverFailOPIdentifier 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testValidateDiscoverFailOPIdentifier()
    {
        $this->message->set('openid.claimed_id', OpenID::SERVICE_2_0_SERVER);
        $this->createObjects();
    }

    /**
     * testValidateDiscoverFail 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testValidateDiscoverFail()
    {
        OpenID::setStore($this->store);

        $this->assertion = $this->getMock('OpenID_Assertion',
                                          array('getHTTPRequest2Instance',
                                                'getDiscover'),
                                          array($this->message,
                                                new Net_URL2($this->requestedURL),
                                                $this->clockSkew));
    }

    /**
     * testValidateDiscoverFailOPNotAuthorized 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testValidateDiscoverFailOPNotAuthorized()
    {
        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));

        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array('http://exampleop2.com'));
        $opEndpoints = new OpenID_ServiceEndpoints($this->claimedID, $opEndpoint);

        $this->discover->expects($this->once())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));

        $this->createObjects();
    }

    /**
     * testValidateNonceFail 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testValidateNonceFail()
    {
        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));

        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array($this->opEndpointURL));
        $opEndpoints = new OpenID_ServiceEndpoints($this->claimedID, $opEndpoint);

        $this->discover->expects($this->once())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getNonce')
                    ->will($this->returnValue(true));
        $this->createObjects();
    }

    /**
     * testVerifySignature 
     * 
     * @return void
     */
    public function testVerifySignature()
    {
        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array($this->opEndpointURL));
        $opEndpoints = new OpenID_ServiceEndpoints($this->claimedID, $opEndpoint);

        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));
        $this->discover->expects($this->once())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getNonce')
                    ->will($this->returnValue(false));
        $this->createObjects();

        $association = new OpenID_Association(array(
                                              'uri'          => $this->opEndpointURL,
                                              'expiresIn'    => 600,
                                              'created'      => time(),
                                              'assocType'    => 'HMAC-SHA1',
                                              'assocHandle'  => '12345',
                                              'sharedSecret' => '6789'));

        $this->message->set('openid.assoc_handle', '12345');
        $association->signMessage($this->message);
        $this->assertTrue($this->assertion->verifySignature($association));

        $this->message->set('openid.sig', 'foo');
        $this->assertFalse($this->assertion->verifySignature($association));
    }

    /**
     * testCheckAuthentication 
     * 
     * @return void
     */
    public function testCheckAuthentication()
    {
        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array($this->opEndpointURL));
        $opEndpoints = new OpenID_ServiceEndpoints($this->claimedID, $opEndpoint);

        $this->discover = $this->getMock('OpenID_Discover',
                                         array('__get'),
                                         array($this->claimedID));
        $this->discover->expects($this->once())
                       ->method('__get')
                       ->will($this->returnValue($opEndpoints));

        $this->store->expects($this->once())
                    ->method('getDiscover')
                    ->will($this->returnValue($this->discover));
        $this->store->expects($this->once())
                    ->method('getNonce')
                    ->will($this->returnValue(false));
        $this->createObjects();

        $adapter  = new HTTP_Request2_Adapter_Mock;
        $content  = "HTTP/1.1 200\n";
        $content .= "Content-Type: text/html; charset=iso-8859-1\n\n\n";
        $content .= "foo:bar\n";
        $adapter->addResponse($content);

        $httpRequest = new HTTP_Request2;
        $httpRequest->setAdapter($adapter);
        
        $this->assertion->expects($this->once())
                        ->method('getHTTPRequest2Instance')
                        ->will($this->returnValue($httpRequest));

        $result = $this->assertion->checkAuthentication();
    }
}
?>
