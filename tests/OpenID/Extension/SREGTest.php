<?php
/**
 * OpenID_Extension_SREGTest 
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
require_once 'OpenID/Extension/SREG11.php';

/**
 * OpenID_Extension_SREGTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Extension_SREGTest extends PHPUnit_Framework_TestCase
{
    protected $sreg = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->sreg = new OpenID_Extension_SREG11(OpenID_Extension::REQUEST);
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->sreg = null;
    }

    /**
     * testSetFailInvalidKey 
     * 
     * @expectedException OpenID_Extension_Exception
     * @return void
     */
    public function testSetFailInvalidKey()
    {
        $this->sreg->set('foo', 'bar');
    }

    /**
     * testSetFailInvalidKeyReponse
     * 
     * @expectedException OpenID_Extension_Exception
     * @return void
     */
    public function testSetFailInvalidKeyResponse()
    {
        $this->sreg = new OpenID_Extension_SREG11(OpenID_Extension::RESPONSE);
        $this->sreg->set('foo', 'bar');
    }

    /**
     * testSetSuccess 
     * 
     * @return void
     */
    public function testSetSuccess()
    {
        $this->sreg->set('required', 'nickname');
    }
}
?>
