<?php
/**
 * OpenID_Association_Common 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

/**
 * Required files
 */
require_once 'OpenID.php';
require_once 'OpenID/Association/Exception.php';
require_once 'Crypt/DiffieHellman.php';

/**
 * OpenID_Association_Common 
 * 
 * Base association class.  Contains session and association constants, default DH
 * parameters, and central storage for a custom DH instance.
 * 
 * @uses      OpenID
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
abstract class OpenID_Association_Common extends OpenID
{

    const SESSION_TYPE_NO_ENCRYPTION = 'no-encryption';
    const SESSION_TYPE_DH_SHA1       = 'DH-SHA1';
    const SESSION_TYPE_DH_SHA256     = 'DH-SHA256';

    const ASSOC_TYPE_HMAC_SHA1   = 'HMAC-SHA1';
    const ASSOC_TYPE_HMAC_SHA256 = 'HMAC-SHA256';

    const DH_DEFAULT_MODULUS = '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443';

    const DH_DEFAULT_GENERATOR = '2';

    /**
     * Stores the Crypt_DiffieHellman instance
     * 
     * @var Crypt_DiffieHellman
     */
    protected $dh = null;

    /**
     * Sets a custom Crypt_DiffieHellman instance.  Use this if you want to use your
     * own private key, for instance.
     * 
     * @param Crypt_DiffieHellman $dh Instance of Crypt_DiffieHellman
     * 
     * @return void
     */
    public function setCryptDiffieHellman(Crypt_DiffieHellman $dh)
    {
        $this->dh = $dh;
    }
}
?>
