<?php
/**
 * OpenID_Observer_LogTest 
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
require_once 'OpenID/Observer/Log.php';
require_once 'Log.php';

/**
 * OpenID_Observer_LogTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_Observer_LogTest extends PHPUnit_Framework_TestCase
{
    protected $log = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->log = new OpenID_Observer_Log(Log::factory('null'), array('foo'));
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->log = null;
    }

    /**
     * testConstructorNoLogInstance 
     * 
     * @return void
     */
    public function testConstructorNoLogInstance()
    {
        $foo = new OpenID_Observer_Log();
    }

    /**
     * testUpdate 
     * 
     * @return void
     */
    public function testUpdate()
    {
        $this->log->update(array('name' => 'foo', 'data' => 'bar'));
    }

    /**
     * testUpdateUnknownEvent 
     * 
     * @return void
     */
    public function testUpdateUnknownEvent()
    {
        $this->log->update(array('name' => 'foobar', 'data' => 'bar'));
    }

    /**
     * testGetEvents 
     * 
     * @return void
     */
    public function testGetEvents()
    {
        $this->assertSame(array('foo'), $this->log->getEvents());
    }
}
?>
