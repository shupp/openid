<?php
/**
 * OpenID_Discover_YadisTest 
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
require_once 'OpenID/Discover/Yadis.php';

/**
 * OpenID_Discover_YadisTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Discover_YadisTest extends PHPUnit_Framework_TestCase
{
    protected $sy      = null;
    protected $object  = null;
    protected $reponse = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->response = $this->getMock('HTTP_Request2_Response',
                                        array(),
                                        array(),
                                        '',
                                        false);
        $this->sy = $this->getMock('Services_Yadis',
                                   array('discover', 'getYadisId', 'getHTTPResponse'));

        $this->object = $this->getMock('OpenID_Discover_Yadis',
                                      array('getServicesYadis'),
                                      array(),
                                      '',
                                      false);
        $this->object->expects($this->any())
                     ->method('getServicesYadis')
                     ->will($this->returnValue($this->sy));
        $this->sy->expects($this->any())
                 ->method('getHTTPResponse')
                 ->will($this->returnValue($this->response));
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->sy       = null;
        $this->object   = null;
        $this->response = null;
    }

    /**
     * testDiscoverSuccess 
     * 
     * @return void
     */
    public function testDiscoverSuccess()
    {
        $file     = file_get_contents(dirname(__FILE__) . '/xrds.xml');
        $xrds     = new SimpleXMLElement($file);
        $ns       = new Services_Yadis_Xrds_Namespace;
        $services = new Services_Yadis_Xrds_Service($xrds, $ns);

        $this->sy->expects($this->any())
                 ->method('discover')
                 ->will($this->returnValue($services));

        $serviceEndpoints = $this->object->discover();
        $this->assertType('OpenID_ServiceEndpoints', $serviceEndpoints);
        $this->assertType('OpenID_ServiceEndpoints', $serviceEndpoints);
    }

    /**
     * testDiscoverSuccess2 
     * 
     * @return void
     */
    public function testDiscoverSuccess2()
    {
        $file     = file_get_contents(dirname(__FILE__) . '/xrds2.xml');
        $xrds     = new SimpleXMLElement($file);
        $ns       = new Services_Yadis_Xrds_Namespace;
        $services = new Services_Yadis_Xrds_Service($xrds, $ns);

        $this->sy->expects($this->any())
                 ->method('discover')
                 ->will($this->returnValue($services));

        $serviceEndpoints = $this->object->discover();
        $this->assertType('OpenID_ServiceEndpoints', $serviceEndpoints);
        $this->assertType('OpenID_ServiceEndpoints', $serviceEndpoints);
    }

    /**
     * testDiscoverFail 
     * 
     * @return void
     */
    public function testDiscoverFail()
    {
        $services = $this->getMock('Services_Yadis_Xrds_Service',
                                   array('valid'),
                                   array(),
                                   '',
                                   false);
        $services->expects($this->any())
                 ->method('valid')
                 ->will($this->returnValue(false));

        $this->sy->expects($this->any())
                 ->method('discover')
                 ->will($this->returnValue($services));

        $serviceEndpoints = $this->object->discover();
        $this->assertFalse($serviceEndpoints);
    }

    /**
     * testGetServicesYadis 
     * 
     * @return void
     */
    public function testGetServicesYadis()
    {
        $sy = new OpenID_Discover_Yadis('http://example.com');
        $this->assertType('Services_Yadis', $sy->getServicesYadis());
    }
}
?>
