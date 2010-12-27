<?php
/**
 * OpenID_MessageTest 
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

require_once 'OpenID/Message.php';
require_once 'OpenID/Extension/AX.php';

/**
 * OpenID_MessageTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_MessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    OpenID_Message
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object = new OpenID_Message;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($this->object);
    }

    /**
     * testGet 
     * 
     * @return void
     */
    public function testGet()
    {
        $key   = 'openid.foo';
        $value = 'bar';

        $this->object->set($key, $value);
        $this->assertSame($value, $this->object->get($key));
    }

    /**
     * testSet 
     * 
     * @return void
     */
    public function testSet()
    {
        $key   = 'openid.foo';
        $value = 'bar';

        $this->assertSame(null, $this->object->get($key));

        $this->object->set($key, $value);
        $this->assertSame($value, $this->object->get($key));
    }

    /**
     * testSetFailure 
     * 
     * @expectedException OpenID_Message_Exception
     * @return void
     */
    public function testSetFailure()
    {
        $this->object->set('openid.ns', 'foo');
    }

    /**
     * testDelete 
     * 
     * @return void
     */
    public function testDelete()
    {
        $key   = 'openid.foo';
        $value = 'bar';

        $this->assertSame(null, $this->object->get($key));

        $this->object->set($key, $value);
        $this->assertSame($value, $this->object->get($key));

        $this->object->delete($key);
        $this->assertSame(null, $this->object->get($key));
    }

    /**
     * testGetKVFormat 
     * 
     * @return void
     */
    public function testGetKVFormat()
    {
        $kv = "openid.foo:bar\n";
        $this->object->set('openid.foo', 'bar');
        $this->assertSame($kv, $this->object->getKVFormat());
    }

    /**
     * testGetHTTPFormat 
     * 
     * @return void
     */
    public function testGetHTTPFormat()
    {
        $http = "openid.foo=foo+bar&openid.bar=foo+bar";
        $this->object->set('openid.foo', 'foo bar');
        $this->object->set('openid.bar', 'foo bar');
        $this->assertSame($http, $this->object->getHTTPFormat());
    }

    /**
     * testGetArrayFormat 
     * 
     * @return void
     */
    public function testGetArrayFormat()
    {
        $http = array('openid.foo' => 'foo bar',
                      'openid.bar' => 'foo bar');

        $this->object->set('openid.foo', 'foo bar');
        $this->object->set('openid.bar', 'foo bar');
        $this->assertSame($http, $this->object->getArrayFormat());
    }

    /**
     * testGetMessageFail 
     * 
     * @expectedException OpenID_Message_Exception
     * @return void
     */
    public function testGetMessageFail()
    {
        $this->object->getMessage('foo');
    }

    /**
     * testSetMessage 
     * 
     * @return void
     */
    public function testSetMessage()
    {
        // KV
        $kv = "openid.foo:foo bar\nopenid.bar:foo bar\n";

        // Test constructor setting
        $this->object = new OpenID_Message($kv, OpenID_Message::FORMAT_KV);
        $this->assertSame($kv, $this->object->getKVFormat());

        // HTTP
        $http = "openid.foo=foo+bar&openid.bar=foo+bar";

        $this->object->setMessage($http, OpenID_Message::FORMAT_HTTP);
        $this->assertSame($http, $this->object->getHTTPFormat());

        // Array
        $array = array('openid.foo' => 'foo bar',
                       'openid.bar' => 'foo bar');

        $this->object->setMessage($array, OpenID_Message::FORMAT_ARRAY);
        $this->assertSame($array, $this->object->getArrayFormat());
    }

    /**
     * testSetMessageFailure 
     * 
     * @expectedException OpenID_Message_Exception
     * @return void
     */
    public function testSetMessageFailure()
    {
        $this->object->setMessage("openid.foo:bar\n", 'foobar');
    }

    /**
     * testAddExtension 
     * 
     * @return void
     */
    public function testAddExtension()
    {
        $extension = new OpenID_Extension_AX(OpenID_Extension::REQUEST);
        $extension->set('foo', 'bar');
        $this->object->addExtension($extension);
        $this->assertSame('bar', $this->object->get('openid.ax.foo'));
    }
}
?>
