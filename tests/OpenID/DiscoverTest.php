<?php
/**
 * OpenID_DiscoverTest 
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

require_once 'OpenID/Discover.php';
require_once 'PHPUnit/Framework.php';

/**
 * OpenID_DiscoverTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_DiscoverTest extends PHPUnit_Framework_TestCase
{
    protected $discover = null;
    protected $id       = 'http://user.example.com';

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->discover = new OpenID_Discover($this->id);
    }

    /**
     * testSetRequestOptions 
     * 
     * @return void
     */
    public function testSetRequestOptions()
    {
        $options = array('allowRedirects' => true);
        $this->assertType('OpenID_Discover',
                          $this->discover->setRequestOptions($options));
    }

    /**
     * testGetFail 
     * 
     * @return void
     */
    public function testGetFail()
    {
        $this->assertSame(null, $this->discover->foobar);
    }
}
?>
