<?php
/**
 * OpenID_Discover_HTMLTest 
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
require_once 'OpenID/Discover/HTML.php';

/**
 * OpenID_Discover_HTMLTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_Discover_HTMLTest extends PHPUnit_Framework_TestCase
{
    /**
     * testDiscoverSuccess 
     * 
     * @return void
     */
    public function testDiscoverSuccess()
    {
        $html = '<html>
                 <head>
                     <link rel="openid.server" href="http://www.example.com/server">
                     <link rel="openid.delegate" href="http://user.example.com">
                 </head>
                 </html>';

        $object = $this->getMock('OpenID_Discover_HTML',
                                 array('sendRequest', 'getExpiresHeader'),
                                 array('http://example.com'));

        $object->expects($this->once())
               ->method('sendRequest')
               ->will($this->returnValue($html));

        $date = new DateTime(date('c', (time() + (3600 * 8))));
        $object->expects($this->once())
               ->method('getExpiresHeader')
               ->will($this->returnValue($date->format(DATE_RFC1123)));

        $serviceEndpoints = $object->discover();
        $this->assertType('OpenID_ServiceEndpoints', $serviceEndpoints);

        // Version 2.0
        $html = '<html>
                 <head>
                     <link rel="openid2.provider" href="http://example.com/server">
                     <link rel="openid2.local_id" href="http://user.example.com">
                 </head>
                 </html>';

        $object = $this->getMock('OpenID_Discover_HTML',
                                 array('sendRequest', 'getExpiresHeader'),
                                 array('http://example.com'));

        $object->expects($this->once())
               ->method('sendRequest')
               ->will($this->returnValue($html));
        $date = new DateTime(date('c', (time() + (3600 * 8))));
        $object->expects($this->once())
               ->method('getExpiresHeader')
               ->will($this->returnValue($date->format(DATE_RFC1123)));

        $serviceEndpoints = $object->discover();
        $this->assertType('OpenID_ServiceEndpoints', $serviceEndpoints);

        // Directed Identity
        $html = '<html>
                 <head>
                     <link rel="openid2.provider" href="http://example.com/server">
                 </head>
                 </html>';

        $object = $this->getMock('OpenID_Discover_HTML',
                                 array('sendRequest', 'getExpiresHeader'),
                                 array('http://example.com'));

        $object->expects($this->once())
               ->method('sendRequest')
               ->will($this->returnValue($html));

        $date = new DateTime(date('c', (time() + (3600 * 8))));
        $object->expects($this->once())
               ->method('getExpiresHeader')
               ->will($this->returnValue($date->format(DATE_RFC1123)));

        $serviceEndpoints = $object->discover();
        $this->assertType('OpenID_ServiceEndpoints', $serviceEndpoints);
    }

    /**
     * testDiscoverFail 
     * 
     * @expectedException OpenID_Discover_Exception
     * @return void
     */
    public function testDiscoverFail()
    {
        $html = '<html>
                 <head>
                 </head>
                 </html>';

        $object = $this->getMock('OpenID_Discover_HTML',
                                 array('sendRequest', 'getExpiresHeader'),
                                 array('http://example.com'));

        $object->expects($this->once())
               ->method('sendRequest')
               ->will($this->returnValue($html));

        $date = new DateTime(date('c', (time() + (3600 * 8))));
        $object->expects($this->any())
               ->method('getExpiresHeader')
               ->will($this->returnValue($date->format(DATE_RFC1123)));

        $serviceEndpoints = $object->discover();
        $this->assertType('OpenID_ServiceEndpoints', $serviceEndpoints);
    }
}
?>
