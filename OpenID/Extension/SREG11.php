<?php
/**
 * OpenID_Extension_SREG11 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Extension_SREG10
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

/**
 * Required files
 */
require_once 'OpenID/Extension/SREG10.php';

/**
 * Implementation of the Simple Registration Extension, version 1.1 Draft 1.
 * 
 * See 
 * {@link http://openid.net/specs/openid-simple-registration-extension-1_1-01.html}
 * for more information.
 * 
 * Example usage:
 * 
 * <code>
 *  $sreg = new OpenID_Extension_SREG11(OpenID_Extension::REQUEST);
 *  $sreg->set('required', 'email');
 *  $sreg->set('optional', 'nickname,gender,dob');
 *  // Add to an existing instance of OpenID_Auth_Request
 *  $authRequest->addExtension($sreg);
 * </code>
 * 
 * @uses      OpenID_Extension_SREG10
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 * @see       OpenID_Extension_SREG10
 */
class OpenID_Extension_SREG11 extends OpenID_Extension_SREG10
{
    /**
     * Enables namespaces.  The only differnce I can see in the specs.
     * 
     * @var bool
     */
    protected $useNamespaceAlias = true;

    /**
     * Sets the URI of the spec for alias assignment
     * 
     * @var string
     */
    protected $namespace = 'http://openid.net/extensions/sreg/1.1';
}
?>
