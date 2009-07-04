<?php
/**
 * OpenID_DiscoverTest 
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

/**
 * Required files 
 */
require_once 'OpenID/Discover.php';
require_once 'OpenID/Discover/Mock.php';
require_once 'OpenID/Store/Mock.php';
require_once 'PHPUnit/Framework.php';

/**
 * OpenID_DiscoverTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_DiscoverTest extends PHPUnit_Framework_TestCase
{
    protected $discover = null;
    protected $id       = 'http://user.example.com';

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->discover = new OpenID_Discover($this->id);
    }

    /**
     * testSetRequestOptions 
     * 
     * @return void
     */
    public function testSetRequestOptions()
    {
        $options = array('allowRedirects' => true);
        $this->assertType('OpenID_Discover',
                          $this->discover->setRequestOptions($options));
    }

    /**
     * testGetFail 
     * 
     * @return void
     */
    public function testGetFail()
    {
        $this->assertSame(null, $this->discover->foobar);
    }

    /**
     * testDiscoverFail 
     * 
     * @return void
     */
    public function testDiscoverFail()
    {
        $oldTypes = OpenID_Discover::$discoveryOrder;

        OpenID_Discover::$discoveryOrder = array(0 => 'MockFail');

        $discover = new OpenID_Discover('http://yahoo.com');
        $this->assertFalse($discover->discover());

        OpenID_Discover::$discoveryOrder = $oldTypes;
    }

    /**
     * testDiscoverFactoryFailNoClassOrNoInterface 
     * 
     * @return void
     */
    public function testDiscoverFactoryFailNoClassOrNoInterface()
    {
        $oldTypes = OpenID_Discover::$discoveryOrder;

        OpenID_Discover::$discoveryOrder = array(0 => 'MockNoClass');

        $discover = new OpenID_Discover('http://yahoo.com');
        $this->assertFalse($discover->discover());

        OpenID_Discover::$discoveryOrder = array(0 => 'MockNoInterface');

        $discover = new OpenID_Discover('http://yahoo.com');
        $this->assertFalse($discover->discover());

        OpenID_Discover::$discoveryOrder = $oldTypes;
    }

    /**
     * testGetDiscover 
     * 
     * @return void
     */
    public function testGetDiscover()
    {
        $opEndpoint = new OpenID_ServiceEndpoint;
        $opEndpoint->setURIs(array('http://op.example.com'));
        $opEndpoint->setVersion(OpenID::SERVICE_2_0_SERVER);

        OpenID_Discover_Mock::$opEndpoint = $opEndpoint;

        $oldTypes = OpenID_Discover::$discoveryOrder;

        OpenID_Discover::$discoveryOrder = array(0 => 'Mock');

        $store = $this->getMock('OpenID_Store_Mock',
                                array('getDiscover'));
        $store->expects($this->any())
              ->method('getDiscover')
              ->will($this->returnValue(false));

        $discover = OpenID_Discover::getDiscover('http://yahoo.com', $store);

        OpenID_Discover::$discoveryOrder = $oldTypes;
    }
}
?>
