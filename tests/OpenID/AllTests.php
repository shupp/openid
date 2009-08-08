<?php
/**
 * OpenID_AllTests 
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

require_once 'PHPUnit/Framework.php';
require_once 'OpenIDTest.php';
require_once 'OpenID/MessageTest.php';
require_once 'OpenID/Auth/RequestTest.php';
require_once 'OpenID/ExtensionTest.php';
require_once 'OpenID/Extension/AXTest.php';
require_once 'OpenID/Extension/SREGTest.php';
require_once 'OpenID/Extension/UITest.php';
require_once 'OpenID/AssociationTest.php';
require_once 'OpenID/Association/RequestTest.php';
require_once 'OpenID/StoreTest.php';
require_once 'OpenID/ServiceEndpointTest.php';
require_once 'OpenID/ServiceEndpointsTest.php';
require_once 'OpenID/Observer/LogTest.php';
require_once 'OpenID/NonceTest.php';
require_once 'OpenID/AssertionTest.php';
require_once 'OpenID/Assertion/ResultTest.php';
require_once 'OpenID/RelyingPartyTest.php';
require_once 'OpenID/DiscoverTest.php';
require_once 'OpenID/Discover/HTMLTest.php';
require_once 'OpenID/Store/CacheLiteTest.php';

/**
 * OpenID_AllTests 
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_AllTests
{
    /**
     * suite 
     * 
     * @return PHPUnit_Framework_TestSuite
     */
    static public function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('OpenID Unit Test Suite');
        $suite->addTestSuite('OpenIDTest');
        $suite->addTestSuite('OpenID_MessageTest');
        $suite->addTestSuite('OpenID_Auth_RequestTest');
        $suite->addTestSuite('OpenID_ExtensionTest');
        $suite->addTestSuite('OpenID_Extension_AXTest');
        $suite->addTestSuite('OpenID_Extension_SREGTest');
        $suite->addTestSuite('OpenID_Extension_UITest');
        $suite->addTestSuite('OpenID_AssociationTest');
        $suite->addTestSuite('OpenID_Association_RequestTest');
        $suite->addTestSuite('OpenID_StoreTest');
        $suite->addTestSuite('OpenID_ServiceEndpointTest');
        $suite->addTestSuite('OpenID_ServiceEndpointsTest');
        $suite->addTestSuite('OpenID_Observer_LogTest');
        $suite->addTestSuite('OpenID_NonceTest');
        $suite->addTestSuite('OpenID_AssertionTest');
        $suite->addTestSuite('OpenID_Assertion_ResultTest');
        $suite->addTestSuite('OpenID_RelyingPartyTest');
        $suite->addTestSuite('OpenID_DiscoverTest');
        $suite->addTestSuite('OpenID_Discover_HTMLTest');
        $suite->addTestSuite('OpenID_Store_CacheLiteTest');
        return $suite;
    }
}

?>
