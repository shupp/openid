<?php
/**
 * OpenID_Store_Interface 
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Bill Shupp, Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

/**
 * Defines the OpenID storage interface.
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Bill Shupp, Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
interface OpenID_Store_Interface
{
    /**
     *  Constants used for setting which type of storage is being used.
     */
    const TYPE_ASSOCIATION = 1;
    const TYPE_DISCOVER    = 2;
    const TYPE_NONCE       = 3;

    /**
     * Gets an OpenID_Discover object from storage
     * 
     * @param string $identifier The normalized identifier that discovery was 
     *                           performed on
     * 
     * @return OpenID_Discover
     */
    public function getDiscover($identifier);

    /**
     * Stores an instance of OpenID_Discover
     * 
     * @param OpenID_Discover $discover Instance of OpenID_Discover
     * @param int             $expire   How long to cache it for, in seconds
     * 
     * @return void
     */
    public function setDiscover(OpenID_Discover $discover, $expire = null);

    /**
     * Gets an OpenID_Assocation instance from storage
     * 
     * @param string $uri    The OP endpoint URI to get an association for
     * @param string $handle The association handle if available
     * 
     * @return OpenID_Association
     */
    public function getAssociation($uri, $handle = null);

    /**
     * Stores an OpenID_Association instance.  Details (such as endpoint url and 
     * exiration) are retrieved from the object itself.
     * 
     * @param OpenID_Association $association Instance of OpenID_Association
     * 
     * @return void
     */
    public function setAssociation(OpenID_Association $association);

    /**
     * Deletes an association from storage
     * 
     * @param string $uri OP Endpoint URI
     * 
     * @return void
     */
    public function deleteAssociation($uri);

    /**
     * Gets a nonce from storage
     * 
     * @param string $nonce The nonce itself
     * @param string $opURL The OP Endpoint URL it was used with
     * 
     * @return string
     */
    public function getNonce($nonce, $opURL);

    /**
     * Stores a nonce for an OP endpoint URL
     * 
     * @param string $nonce The nonce itself
     * @param string $opURL The OP endpoint URL it was associated with
     * 
     * @return void
     */
    public function setNonce($nonce, $opURL);

    /**
     * Deletes a nonce from storage
     * 
     * @param string $nonce The nonce to delete
     * @param string $opURL The OP endpoint URL it is associated with
     * 
     * @return void
     */
    public function deleteNonce($nonce, $opURL);
}
?>
