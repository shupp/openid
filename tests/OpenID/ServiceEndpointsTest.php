<?php
/**
 * OpenID_ServiceEndpointsTest
 *
 * PHP version 5.2.0+
 *
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

require_once 'OpenID/ServiceEndpoint.php';
require_once 'OpenID/ServiceEndpoints.php';

/**
 * Test class for the OpenID_ServiceEndpoints class
 *
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_ServiceEndpointsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var OpenID_ServiceEndpoints
     */
    protected $object;

    /**
     * Dummy identifier to use for testing
     *
     * @var string
     */
    protected $identifier = 'http://id.myopenidprovider.com';

    /**
     * A valid service endpoint object
     *
     * @var OpenID_ServiceEndpoint
     */
    protected $goodService = null;

    /**
     * An invalid service endpoint object
     *
     * @var OpenID_ServiceEndpoint
     */
    protected $badServce = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object      = new OpenID_ServiceEndpoints($this->identifier);
        $this->badService  = new OpenID_ServiceEndpoint();
        $this->goodService = new OpenID_ServiceEndpoint();
        $this->goodService->setURIs(array($this->identifier));
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
        unset($this->badService);
        unset($this->goodService);
    }

    /**
     * Tests that the standard constructor call works
     *
     * @return void
     */
    public function testConstructorNoEndpoint()
    {
        $this->assertInstanceOf('OpenID_ServiceEndpoints', $this->object);
        $this->assertEquals($this->identifier, $this->object->getIdentifier());
    }

    /**
     * Tests that passing a service endpoint object into the constructor works
     *
     * @return void
     */
    public function testConstructorWithEndpoint()
    {
        $services = new OpenID_ServiceEndpoints($this->identifier,
                                                $this->badService);

        $this->assertInstanceOf('OpenID_ServiceEndpoints', $this->object);
        $this->assertEquals($this->identifier, $this->object->getIdentifier());
    }

    /**
     * Tests that adding an invalid ServiceEndpoint object fails
     *
     * @return void
     */
    public function testAddServiceFail()
    {
        $this->assertNull($this->object[0]);
        $this->object->addService($this->badService);
        $this->assertNull($this->object[0]);
    }

    /**
     * Tests that a ServiceEndpoint object can be successfully added
     *
     * @return void
     */
    public function testAddServiceSuccess()
    {
        $this->object->addService($this->goodService);
        $this->assertInstanceOf('OpenID_ServiceEndpoint', $this->object[0]);
        $this->assertEquals($this->goodService, $this->object[0]);
    }

    /**
     * Tests that the getIterator() method works correctly
     *
     * @return void
     */
    public function testGetIterator()
    {
        $this->object->addService($this->goodService);
        $iterator = $this->object->getIterator();

        $this->assertInstanceOf('ArrayIterator', $iterator);
        $this->assertInternalType('bool', $iterator->valid());
        $this->assertTrue($iterator->valid());
        $this->assertInstanceOf('OpenID_ServiceEndpoint', $iterator->current());
        $this->assertEquals($this->goodService, $iterator->current());
        $iterator->next();
        $this->assertInternalType('bool', $iterator->valid());
        $this->assertFalse($iterator->valid());
    }

    /**
     * Tests that offsetSet and offsetUnset from ArrayAccess work correctly
     *
     * @return void
     */
    public function testOffsetSetAndUnset()
    {
        $index = 5;

        $this->assertNull($this->object[$index]);

        $this->object[$index] = $this->goodService;

        $this->assertInstanceOf('OpenID_ServiceEndpoint', $this->object[$index]);
        $this->assertEquals($this->goodService, $this->object[$index]);
    
        unset($this->object[$index]);

        $this->assertNull($this->object[$index]);
    }

    /**
     * Tests that the count method via the Countable interface works correctly 
     *
     * @return void
     */
    public function testCount()
    {
        $this->object->addService($this->goodService);
        $this->object->addService($this->goodService);
        $this->object->addService($this->goodService);
        $this->object->addService($this->goodService);

        $count = count($this->object);

        $this->assertInternalType('int', $count);
        $this->assertEquals(4, $count);
    }
}
?>
