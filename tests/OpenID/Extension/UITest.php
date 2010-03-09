<?php
/**
 * OpenID_Extension_UITest 
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
require_once 'OpenID/Extension/UI.php';

/**
 * OpenID_Extension_UITest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_Extension_UITest extends PHPUnit_Framework_TestCase
{
    protected $ui = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->ui = new OpenID_Extension_UI(OpenID_Extension::REQUEST);
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->ui = null;
    }

    /**
     * testSetFailInvalidMode 
     * 
     * @expectedException OpenID_Extension_Exception
     * @return void
     */
    public function testSetFailInvalidMode()
    {
        $this->ui->set('mode', 'foo');
    }

    /**
     * testSetSuccess 
     * 
     * @return void
     */
    public function testSetSuccess()
    {
        $this->ui->set('foo', 'bar');
    }
}
?>
