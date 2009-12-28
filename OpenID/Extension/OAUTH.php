<?php
/**
 * OpenID_Extension_OAUTH
 *
 * PHP Version 5.2.0+
 *
 * @uses      OpenID_Extension
 * @category  Auth
 * @package   OpenID
 * @author    Jeff Hodsdon <jeffhodsdon@gmail.com> 
 * @copyright 2009 Jeff Hodsdon
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

require_once 'OpenID/Extension.php';

/**
 * Provides support for the OAuth extension
 *
 * @uses      OpenID_Extension
 * @category  Auth
 * @package   OpenID
 * @author    Jeff Hodsdon <jeffhodsdon@gmail.com> 
 * @copyright 2009 Jeff Hodsdon
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Extension_OAUTH extends OpenID_Extension
{
    /**
     * URI of the UI namespace
     * 
     * @var string
     */
    protected $namespace ='http://specs.openid.net/extensions/oauth/1.0';

    /**
     * Alias to use
     * 
     * @var string
     */
    protected $alias = 'oauth';

}
?>
