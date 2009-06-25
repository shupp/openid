<?php
/**
 * OpenID_StoreTest 
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
require_once 'OpenID/Store.php';
require_once 'OpenID/Store/NoClass.php';
require_once 'OpenID/Store/Mock.php';
require_once 'OpenID/Store/NotInterface.php';

/**
 * OpenID_StoreTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_StoreTest extends PHPUnit_Framework_TestCase
{
    /**
     * testFactorySuccess 
     * 
     * @return void
     */
    public function testFactorySuccess()
    {
        $object = OpenID_Store::factory('Mock');
    }

    /**
     * testFactoryFailNoClass 
     * 
     * @expectedException OpenID_Store_Exception
     * @return void
     */
    public function testFactoryFailNoClass()
    {
        $object = OpenID_Store::factory('NoClass');
    }

    /**
     * testFactoryFailNotInterface 
     * 
     * @expectedException OpenID_Store_Exception
     * @return void
     */
    public function testFactoryFailNotInterface()
    {
        $object = OpenID_Store::factory('NotInterface');
    }
}
?>
