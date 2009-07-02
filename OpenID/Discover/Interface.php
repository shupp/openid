<?php
/**
 * OpenID_Discover_Interface 
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

/**
 * Describes the discovery driver interface
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
interface OpenID_Discover_Interface
{
    /**
     * Constructor.  Sets the user supplied identifier.
     * 
     * @param string $identifier User supplied identifier
     * 
     * @return void
     */
    public function __construct($identifier);

    /**
     * Performs discovery on the user supplied identifier
     * 
     * @return bool true on success, false on failure
     */
    public function discover();
}

?>
