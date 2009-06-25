<?php
/**
 * OpenID_Extension_AXTest 
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
require_once 'OpenID/Extension/AX.php';

/**
 * OpenID_Extension_AXTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Extension_AXTest extends PHPUnit_Framework_TestCase
{
    protected $ax = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->ax = new OpenID_Extension_AX(OpenID_Extension::REQUEST);
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->ax = null;
    }

    /**
     * testSetFailInvalidMode 
     * 
     * @expectedException OpenID_Extension_Exception
     * @return void
     */
    public function testSetFailInvalidMode()
    {
        $this->ax->set('mode', 'foo');
    }

    /**
     * testSetFailInvalidURI 
     * 
     * @expectedException OpenID_Extension_Exception
     * @return void
     */
    public function testSetFailInvalidURI()
    {
        $this->ax->set('type.foo', 'http:///example.com');
    }

    /**
     * testSetSuccess 
     * 
     * @return void
     */
    public function testSetSuccess()
    {
        $this->ax->set('foo', 'bar');
    }
}
?>
