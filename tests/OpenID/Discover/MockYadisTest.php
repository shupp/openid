<?php
/**
 * OpenID_Discover_MockYadisTest 
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
require_once 'OpenID/Discover/MockYadis.php';
require_once 'PHPUnit/Framework.php';
require_once 'Services/Yadis.php';

/**
 * OpenID_Discover_MockYadisTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Discover_MockYadisTest extends PHPUnit_Framework_TestCase
{
    protected $yadis  = null;
    protected $object = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->yadis = $this->getMock('Services_Yadis');

        OpenID_Discover_MockYadis::$servicesYadisInstance = $this->yadis;

        $this->object = new OpenID_Discover_Yadis('http://example.com');
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->yadis  = null;
        $this->object = null;
    }

    /**
     * testDiscoverFailNotValid 
     * 
     * @return void
     */
    public function testDiscoverFailNotValid()
    {
        $this->yadis->expects($this->any())
                    ->method('valid')
                    ->will($this->returnValue(false));

        $this->assertFalse($this->object->discover());
    }
}
?>
