<?php
/**
 * OpenID_ServiceEndpointTest
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

require_once 'PHPUnit/Framework.php';
require_once 'OpenID/ServiceEndpoint.php';

/**
 * Test class for the OpenID_ServiceEndpoint class
 *
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_ServiceEndpointTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var OpenID_ServiceEndpoint
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
        $this->object = new OpenID_ServiceEndpoint;
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
     * Tests that isValid returns false with no URIs
     *
     * @return void
     */
    public function testIsValidFalse()
    {
        $isValid = $this->object->isValid();

        $this->assertFalse($isValid);
    }

    /**
     * Tests that attempting to set an invalid URI fails
     *
     * @return void
     */
    public function testSetInvalidURI()
    {
        $invalid = array(
            'thisiswrong'
        );

        $uris = $this->object->getURIs();
        $this->assertEquals(array(), $uris);

        $this->object->setURIs($invalid);

        $uris = $this->object->getURIs();
        $this->assertEquals(array(), $uris);
    }

    /**
     * Tests that getting and setting the URIs variable works properly
     *
     * @return void
     */
    public function testGetSetURIs()
    {
        $testURIs = array(
            'http://example.com',
            'http://myopenid.com'
        );

        $uris = $this->object->getURIs();
        $this->assertEquals(array(), $uris);
        
        $this->object->setURIs($testURIs);

        $uris = $this->object->getURIs();
        $this->assertEquals($testURIs, $uris);
    }

    /**
     * Tests that getting and setting the types variable works properly
     *
     * @return void
     */
    public function testGetSetTypes()
    {
        $testTypes = array(
            'foo',
            'bar'
        );

        $types = $this->object->getTypes();
        $this->assertEquals(array(), $types);
    
        $this->object->setTypes($testTypes);

        $types = $this->object->getTypes();
        $this->assertEquals($testTypes, $types);
    }

    /**
     * Tests that getting and setting the local ID variable works properly
     *
     * @return void
     */
    public function testGetSetLocalID()
    {
        $testLocalID = 'foobar';

        $localID = $this->object->getLocalID();
        $this->assertNull($localID);
    
        $this->object->setLocalID($testLocalID);

        $localID = $this->object->getLocalID();
        $this->assertEquals($testLocalID, $localID);
    }

    /**
     * Tests that getting and setting the source variable works properly
     *
     * @return void
     */
    public function testGetSetSource()
    {
        $testSource = 'HTML';

        $source = $this->object->getSource();
        $this->assertNull($source);
    
        $this->object->setSource($testSource);

        $source = $this->object->getSource();
        $this->assertEquals($testSource, $source);
    }

    /**
     * Tests that getting and setting the version variable works properly
     *
     * @return void
     */
    public function testGetSetVersion()
    {
        $testVersion = 'http://specs.openid.net/auth/2.0/server';

        $version = $this->object->getVersion();
        $this->assertNull($version);
    
        $this->object->setVersion($testVersion);

        $version = $this->object->getVersion();
        $this->assertEquals($testVersion, $version);
    }

    /**
     * Tests the isValid() method
     *
     * @return void
     */
    public function testIsValidTrue()
    {
        $this->object->setURIs(array('http://example.com'));

        $isValid = $this->object->isValid();

        $this->assertTrue($isValid);
    }
}

?>
