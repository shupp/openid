<?php
/**
 * OpenID_NonceTest 
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

require_once 'OpenID/Nonce.php';
require_once 'OpenID/Store/Mock.php';

/**
 * OpenID_NonceTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_NonceTest extends PHPUnit_Framework_TestCase
{
    protected $skew  = 600;
    protected $opURL = 'http://exampleop.com';
    protected $nonce = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->nonce = new OpenID_Nonce($this->opURL, $this->skew);
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->nonce = null;
    }

    /**
     * testValidate 
     * 
     * @return void
     */
    public function testValidate()
    {
        $nonce = gmstrftime('%Y-%m-%dT%H:%M:%SZ', time()). '12345abcde';
        $this->assertTrue($this->nonce->validate($nonce));
    }

    /**
     * testValidateFail 
     * 
     * @return void
     */
    public function testValidateFail()
    {
        $this->assertFalse($this->nonce->validate('foo'));
        $nonce = gmstrftime('%Y-%m-%dT%H:%M:%SZ',
                            time() - ($this->skew + 100)) . '12345abcde';
        $this->assertFalse($this->nonce->validate($nonce));
        $nonce = '5000-13-47T50:70:70Z&&&&&';
        $this->assertFalse($this->nonce->validate($nonce));
    }

    /**
     * testVerifyResponseNonce 
     * 
     * @return void
     */
    public function testVerifyResponseNonce()
    {
        $store = $this->getMock('OpenID_Store_Mock', array('getNonce'));
        OpenID::setStore($store);
        $this->nonce = new OpenID_Nonce($this->opURL, $this->skew, $store);
        $store->expects($this->any())
              ->method('getNonce')
              ->will($this->returnValue(false));
        $nonce = gmstrftime('%Y-%m-%dT%H:%M:%SZ', time()) . '12345abcde';
        $this->assertTrue($this->nonce->verifyResponseNonce($nonce));
    }

    /**
     * testVerifyResponseNonceFail 
     * 
     * @return void
     */
    public function testVerifyResponseNonceFail()
    {
        $store = $this->getMock('OpenID_Store_Mock', array('getNonce'));
        OpenID::setStore($store);
        $this->nonce = new OpenID_Nonce($this->opURL, $this->skew, $store);
        $store->expects($this->once())
              ->method('getNonce')
              ->will($this->returnValue(true));
        $nonce = gmstrftime('%Y-%m-%dT%H:%M:%SZ', time()). '12345abcde';
        $this->assertFalse($this->nonce->verifyResponseNonce($nonce));
    }

    /**
     * testCreateNonce 
     * 
     * @return void
     */
    public function testCreateNonce()
    {
        $nonce = $this->nonce->createNonce(4, time());
        $this->assertTrue($this->nonce->validate($nonce));

        $nonce = $this->nonce->createNonce(0, time());
        $this->assertTrue($this->nonce->validate($nonce));
    }

    /**
     * testCreateNonceAndStore 
     * 
     * @return void
     */
    public function testCreateNonceAndStore()
    {
        $store       = $this->getMock('OpenID_Store_Mock', array('setNonce'));
        $this->nonce = new OpenID_Nonce($this->opURL, $this->skew, $store);
        $nonce       = $this->nonce->createNonceAndStore();
        $this->assertTrue($this->nonce->validate($nonce));
    }

    /**
     * testValidateFailTooLong 
     * 
     * @return void
     */
    public function testValidateFailTooLong()
    {
        $this->nonce = new OpenID_Nonce($this->opURL, $this->skew);
        $nonce       = $this->nonce->createNonce('300');
        $this->assertFalse($this->nonce->validate($nonce));
    }
}
?>
