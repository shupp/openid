<?php
/**
 * OpenID_Assertion_ResultTest 
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
require_once 'OpenID/Assertion/Result.php';
require_once 'OpenID/Message.php';
require_once 'OpenID.php';

/**
 * OpenID_Assertion_ResultTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Assertion_ResultTest extends PHPUnit_Framework_TestCase
{
    protected $result = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->result = new OpenID_Assertion_Result;
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->result = null;
    }

    /**
     * testSetAndGetCheckAuthResponse 
     * 
     * @return void
     */
    public function testSetAndGetCheckAuthResponse()
    {
        $message = new OpenID_Message;
        $message->set('openid.id_res', 'true');
        $this->result->setCheckAuthResponse($message);
        $this->assertSame($message, $this->result->getCheckAuthResponse());
    }

    /**
     * testSuccess 
     * 
     * @return void
     */
    public function testSuccess()
    {
        $this->result->setAssertionResult(true);
        $this->assertTrue($this->result->success());
        $this->result->setAssertionResult(false);
        $this->assertFalse($this->result->success());
    }

    /**
     * testSetAndGetAssertionMethod 
     * 
     * @return void
     */
    public function testSetAndGetAssertionMethod()
    {
        $this->result->setAssertionMethod(OpenID::MODE_ASSOCIATE);
        $this->assertSame(OpenID::MODE_ASSOCIATE,
                          $this->result->getAssertionMethod());

        $this->result->setAssertionMethod(OpenID::MODE_ASSOCIATE);
        $this->assertSame(OpenID::MODE_ASSOCIATE,
                          $this->result->getAssertionMethod());
    }

    /**
     * testSetAssertionMethodFail 
     * 
     * @expectedException OpenID_Assertion_Exception
     * @return void
     */
    public function testSetAssertionMethodFail()
    {
        $this->result->setAssertionMethod('foo');
    }

    /**
     * testSetGetUserSetupURL 
     * 
     * @return void
     */
    public function testSetGetUserSetupURL()
    {
        $url = 'http://example.com';
        $this->result->setUserSetupURL($url);
        $this->assertSame($url, $this->result->getUserSetupURL());
    }
}
?>
