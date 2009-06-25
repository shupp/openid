<?php
/**
 * OpenIDTest 
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
require_once 'OpenID.php';
require_once 'OpenID/Store.php';
require_once 'OpenID/Message.php';
require_once 'OpenID/Observer/Mock.php';

/**
 * OpenIDTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenIDTest extends PHPUnit_Framework_TestCase
{
    /**
     * testSetAndGetStore 
     * 
     * @return void
     */
    public function testSetAndGetStore()
    {
        $this->assertType('OpenID_Store_CacheLite', OpenID::getStore());
        OpenID::setStore(OpenID_Store::factory('Mock'));
        $this->assertType('OpenID_Store_Mock', OpenID::getStore());
    }

    /**
     * testGetXRIGlobalSymbols 
     * 
     * @return void
     */
    public function testGetXRIGlobalSymbols()
    {
        $this->assertTrue(in_array('=', OpenID::getXRIGlobalSymbols()));
    }

    /**
     * testNormalizeIdentifierSuccess 
     * 
     * @return void
     */
    public function testNormalizeIdentifierSuccess()
    {
        $this->assertSame('=example', OpenID::normalizeIdentifier('xri://=example'));
        $this->assertSame('=example', OpenID::normalizeIdentifier('=example'));
        $this->assertSame('http://example.com',
                          OpenID::normalizeIdentifier('example.com'));
    }

    /**
     * testNormalizeIdentifierFail 
     * 
     * @expectedException OpenID_Exception
     * @return void
     */
    public function testNormalizeIdentifierFail()
    {
        OpenID::normalizeIdentifier('&example');
    }

    /**
     * testDirectRequest 
     * 
     * @expectedException OpenID_Exception
     * @return void
     */
    public function testDirectRequest()
    {
        $options = array('timeout' => 1, 'readTimeout' => 1);
        $openid  = new OpenID;
        $message = new OpenID_Message;
        $message->set('foo', 'bar');
        // Hoping this port if free to force a failure.  Need HTTP_Request2 mocking
        $openid->directRequest('http://localhost:2112', $message, $options);
    }

    /**
     * testObservers 
     * 
     * @return void
     */
    public function testObservers()
    {
        $event1 = array('name' => 'foo1', 'data' => 'bar1');
        $event2 = array('name' => 'foo2', 'data' => 'bar2');
        $mock   = new OpenID_Observer_Mock;
        OpenID::attach($mock);
        // Test skipping existing observers
        OpenID::attach($mock);
        try {
            OpenID::setLastEvent($event1['name'], $event1['data']);
            // should not execute
            $this->assertTrue(false);
        } catch (OpenID_Exception $e) {
        }
        $this->assertSame($event1, OpenID::getLastEvent());
        OpenID::detach($mock);
        // Test skipping missing observers
        OpenID::detach($mock);
        OpenID::setLastEvent($event2['name'], $event2['data']);
        $this->assertSame($event2, OpenID::getLastEvent());
    }
}
?>
