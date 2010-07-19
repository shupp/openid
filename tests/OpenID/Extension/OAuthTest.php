<?php
/**
 * OpenID_Extension_OAuthTest 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2010 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github/shupp/openid
 */

require_once 'OpenID/Extension/OAuth.php';
require_once 'PHPUnit/Framework.php';

/**
 * OpenID_Extension_OAuthTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2010 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github/shupp/openid
 */
class OpenID_Extension_OAuthTest extends PHPUnit_Framework_TestCase
{
    protected $oauth = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->oauth = $this->getMock('OpenID_Extension_OAuth',
                                      array('getConsumer'),
                                      array(OpenID_Extension::REQUEST));
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->oauth = null;
    }
}

?>
