<?php
/**
 * OpenID_Auth_RequestTest 
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
require_once 'OpenID/Auth/Request.php';
require_once 'OpenID/Discover.php';
require_once 'OpenID/Discover/Mock.php';
require_once 'OpenID/ServiceEndpoint.php';
require_once 'OpenID/Extension.php';
require_once 'OpenID/Extension/UI.php';
require_once 'OpenID/Store/Mock.php';
require_once 'OpenID/Nonce.php';

/**
 * OpenID_Auth_RequestTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Auth_RequestTest extends PHPUnit_Framework_TestCase
{
    protected $authRequest = null;
    protected $identifier  = 'http://user.example.com';
    protected $returnTo    = 'http://examplerp.com';
    protected $opURL       = 'http://exampleop.com';
    protected $realm       = 'http://example.com';
    protected $discover    = null;
    protected $assocHandle = '12345';

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        OpenID_Discover::$discoveryOrder = array(0 => 'Mock');

        $opEndpoint = new OpenID_ServiceEndpoint();
        $opEndpoint->setVersion(OpenID::SERVICE_2_0_SERVER);
        $opEndpoint->setTypes(array(OpenID::SERVICE_2_0_SERVER));
        $opEndpoint->setURIs(array($this->opURL));

        OpenID_Discover_Mock::$opEndpoint = $opEndpoint;

        $this->setObjects();
    }

    /**
     * setObjects 
     * 
     * @return void
     */
    protected function setObjects()
    {
        $this->discover = new OpenID_Discover($this->identifier);
        $this->discover->discover();

        $this->authRequest = new OpenID_Auth_Request($this->discover,
                                                     $this->returnTo,
                                                     $this->realm,
                                                     $this->assocHandle);
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        OpenID_Discover::$discoveryOrder = array(
            0  => OpenID_Discover::TYPE_YADIS,
            10 => OpenID_Discover::TYPE_HTML
        );

        OpenID_Discover_Mock::$opEndpoint = null;

        $this->discover    = null;
        $this->authRequest = null;
    }

    /**
     * testAddExtension 
     * 
     * @return void
     */
    public function testAddExtension()
    {
        $ui = new OpenID_Extension_UI(OpenID_Extension::REQUEST);
        $this->authRequest->addExtension($ui);
    }

    /**
     * testSetModeFail 
     * 
     * @expectedException OpenID_Auth_Exception
     * @return void
     */
    public function testSetModeFail()
    {
        $this->authRequest->setMode('foo');
    }

    /**
     * testSetModeSuccess 
     * 
     * @return void
     */
    public function testSetModeSuccess()
    {
        $mode = OpenID::MODE_CHECKID_IMMEDIATE;
        $this->authRequest->setMode($mode);
        $this->assertSame($mode, $this->authRequest->getMode());
    }

    /**
     * testGetAuthorizeURL 
     * 
     * @return void
     */
    public function testGetAuthorizeURL()
    {
        $url     = $this->authRequest->getAuthorizeURL();
        $split   = preg_split('/\?/', $url);
        $message = new OpenID_Message($split[1], OpenID_Message::FORMAT_HTTP);
        $this->assertSame($this->returnTo, $message->get('openid.return_to'));
        $this->assertSame(OpenID::NS_2_0_ID_SELECT,
                          $message->get('openid.identity'));
        $this->assertSame(OpenID::NS_2_0_ID_SELECT,
                          $message->get('openid.claimed_id'));
        $this->assertSame($this->opURL, $split[0]);
    }

    /**
     * testGetAuthorizeURLSignon 
     * 
     * @return void
     */
    public function testGetAuthorizeURLSignon()
    {
        $opEndpoint = new OpenID_ServiceEndpoint();
        $opEndpoint->setVersion(OpenID::SERVICE_2_0_SIGNON);
        $opEndpoint->setTypes(array(OpenID::SERVICE_2_0_SIGNON));
        $opEndpoint->setURIs(array($this->opURL));

        OpenID_Discover_Mock::$opEndpoint = $opEndpoint;

        $this->setObjects();

        $url     = $this->authRequest->getAuthorizeURL();
        $split   = preg_split('/\?/', $url);
        $message = new OpenID_Message($split[1], OpenID_Message::FORMAT_HTTP);
        $this->assertSame($this->returnTo, $message->get('openid.return_to'));
        $this->assertSame($this->identifier, $message->get('openid.identity'));
        $this->assertSame($this->identifier, $message->get('openid.claimed_id'));
        $this->assertSame($this->opURL, $split[0]);
    }

    /**
     * testGetAuthorizeURLSignonLocalID 
     * 
     * @return void
     */
    public function testGetAuthorizeURLSignonLocalID()
    {
        $opEndpoint = new OpenID_ServiceEndpoint();
        $opEndpoint->setVersion(OpenID::SERVICE_2_0_SIGNON);
        $opEndpoint->setTypes(array(OpenID::SERVICE_2_0_SIGNON));
        $opEndpoint->setLocalID($this->identifier);
        $opEndpoint->setURIs(array($this->opURL));

        OpenID_Discover_Mock::$opEndpoint = $opEndpoint;

        $this->setObjects();

        $url     = $this->authRequest->getAuthorizeURL();
        $split   = preg_split('/\?/', $url);
        $message = new OpenID_Message($split[1], OpenID_Message::FORMAT_HTTP);
        $this->assertSame($this->returnTo, $message->get('openid.return_to'));
        $this->assertSame($this->identifier, $message->get('openid.identity'));
        $this->assertSame($this->identifier, $message->get('openid.claimed_id'));
        $this->assertSame($this->opURL, $split[0]);
    }

    /**
     * testGetAuthorizeURLSignonLocalIDOneOne 
     * 
     * @return void
     */
    public function testGetAuthorizeURLSignonLocalIDOneOne()
    {
        $opEndpoint = new OpenID_ServiceEndpoint();
        $opEndpoint->setVersion(OpenID::SERVICE_1_1_SIGNON);
        $opEndpoint->setTypes(array(OpenID::SERVICE_1_1_SIGNON));
        $opEndpoint->setLocalID($this->identifier);
        $opEndpoint->setURIs(array($this->opURL));

        OpenID_Discover_Mock::$opEndpoint = $opEndpoint;


        $this->setObjects();

        $url     = $this->authRequest->getAuthorizeURL();
        $split   = preg_split('/\?/', $url);
        $message = new OpenID_Message($split[1], OpenID_Message::FORMAT_HTTP);
        $this->assertNotSame($this->returnTo, $message->get('openid.return_to'));
        $this->assertSame($this->identifier, $message->get('openid.identity'));
        $this->assertSame(null, $message->get('openid.claimed_id'));
        $this->assertSame($this->opURL, $split[0]);

        // Mock nonce/store rather than have a new one created
        $store = new OpenID_Store_Mock();
        $nonce = new OpenID_Nonce($this->opURL, null, $store);
        $this->authRequest->setNonce($nonce);

        $url = $this->authRequest->getAuthorizeURL();
    }
}
?>
