<?php
/**
 * OpenID_Store_CacheLiteTest 
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

require_once 'OpenID/Store/CacheLite.php';
require_once 'OpenID/Association.php';
require_once 'OpenID/Discover.php';
require_once 'OpenID/Nonce.php';
require_once 'PHPUnit/Framework.php';

/**
 * OpenID_Store_CacheLiteTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Store_CacheLiteTest extends PHPUnit_Framework_TestCase
{
    /**
     * cache 
     * 
     * @var OpenID_Store_CacheLite
     */
    protected $cache = null;

    /**
     * setUp 
     * 
     * @return void
     */
    public function setUp()
    {
        $this->cache = new OpenID_Store_CacheLite;
    }

    /**
     * tearDown 
     * 
     * @return void
     */
    public function tearDown()
    {
        $this->cache = null;
    }

    /**
     * testAssociations 
     * 
     * @return void
     */
    public function testAssociations()
    {
        $uri = 'http://exampleop.com';

        $args = array(
            'uri' => $uri,
            'expiresIn'    => 14400,
            'created'      => time(),
            'assocType'    => 'HMAC-SHA256',
            'assocHandle'  => '123',
            'sharedSecret' => '4567890'
        );

        $assoc = new OpenID_Association($args);
        $this->cache->deleteAssociation($uri);

        $this->assertFalse($this->cache->getAssociation($uri));
        $this->cache->setAssociation($assoc);
        $this->assertType('OpenID_Association', $this->cache->getAssociation($uri));
        $this->assertType('OpenID_Association',
                          $this->cache->getAssociation($uri, $args['assocHandle']));
        $this->cache->deleteAssociation($uri);
        $this->assertFalse($this->cache->getAssociation($uri));
    }

    /**
     * testDiscover 
     * 
     * @return void
     */
    public function testDiscover()
    {
        $identifier = 'http://example.com';

        $discover = new OpenID_Discover($identifier);
        $this->cache->deleteDiscover($identifier);

        $this->assertFalse($this->cache->getDiscover($identifier));
        $this->cache->setDiscover($discover);
        $this->assertType('OpenID_Discover', $this->cache->getDiscover($identifier));
        $this->cache->deleteDiscover($identifier);
        $this->assertFalse($this->cache->getDiscover($identifier));
    }

    /**
     * testNonce 
     * 
     * @return void
     */
    public function testNonce()
    {
        $uri    = 'http://exampleop.com';
        $object = new OpenID_Nonce($uri);
        $nonce  = $object->createNonce();

        $this->cache->deleteNonce($nonce, $uri);

        $this->assertFalse($this->cache->getNonce($nonce, $uri));
        $this->cache->setNonce($nonce, $uri);
        $this->assertSame($nonce, $this->cache->getNonce($nonce, $uri));
        $this->cache->deleteNonce($nonce, $uri);
        $this->assertFalse($this->cache->getNonce($nonce, $uri));
    }
}
?>
