<?php
/**
 * OpenID_Store_Mock 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Store_Interface
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

require_once 'OpenID/Store/Interface.php';

/**
 * OpenID_Store_Mock 
 * 
 * @uses      OpenID_Store_Interface
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Store_Mock implements OpenID_Store_Interface
{
    /**
     * getDiscover 
     * 
     * @param mixed $identifier Identifier
     * 
     * @return void
     */
    public function getDiscover($identifier)
    {
    }

    /**
     * setDiscover 
     * 
     * @param OpenID_Discover $discover object
     * @param int             $expire   expire in seconds
     * 
     * @return void
     */
    public function setDiscover(OpenID_Discover $discover, $expire = null)
    {
    }

    /**
     * getAssociation 
     * 
     * @param string $uri    The OP Endpoint URI
     * @param string $handle The Association Handle (optional)
     * 
     * @return void
     */
    public function getAssociation($uri, $handle = null)
    {
    }

    /**
     * setAssociation 
     * 
     * @param OpenID_Association $association Association
     * 
     * @return void
     */
    public function setAssociation(OpenID_Association $association)
    {
    }

    /**
     * deleteAssociation 
     * 
     * @param mixed $uri URI
     * 
     * @return void
     */
    public function deleteAssociation($uri)
    {
    }

    /**
     * getNonce 
     * 
     * @param mixed $nonce nonce
     * @param mixed $opURL opURL
     * 
     * @return void
     */
    public function getNonce($nonce, $opURL)
    {
    }

    /**
     * setNonce 
     * 
     * @param mixed $nonce nonce
     * @param mixed $opURL opURL
     * 
     * @return void
     */
    public function setNonce($nonce, $opURL)
    {
    }

    /**
     * deleteNonce 
     * 
     * @param mixed $nonce nonce
     * @param mixed $opURL opURL
     * 
     * @return void
     */
    public function deleteNonce($nonce, $opURL)
    {
    }
}
?>
