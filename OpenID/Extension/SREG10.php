<?php
/**
 * OpenID_Extension_SREG10 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Extension
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
require_once 'OpenID/Extension.php';

/**
 * Implementation of the Simple Registration Extension version 1.0.  See 
 * {@link http://openid.net/specs/openid-simple-registration-extension-1_0.html} for
 * more information on this extension.
 * 
 * Example usage:
 * 
 * <code>
 *  $sreg = new OpenID_Extension_SREG10(OpenID_Extension::REQUEST);
 *  $sreg->set('required', 'email');
 *  $sreg->set('optional', 'nickname,gender,dob');
 *  // Add to an existing instance of OpenID_Auth_Request
 *  $authRequest->addExtension($sreg);
 * </code>
 * 
 * @uses      OpenID_Extension
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Extension_SREG10 extends OpenID_Extension
{
    /**
     * Disables NS use, since this extension was done before OpenID 2.0
     * 
     * @var bool
     */
    protected $useNamespaceAlias = false;

    /**
     * The alias to use.
     * 
     * @var string
     */
    protected $alias = 'sreg';

    /**
     * Supported keys in a request
     * 
     * @var array
     */
    protected $requestKeys = array(
        'required',
        'optional',
        'policy_url'
    );

    /**
     * Supported keys in a response
     * 
     * @var array
     */
    protected $responseKeys = array(
        'nickname',
        'email',
        'fullname',
        'dob',
        'gender',
        'postcode',
        'country',
        'language',
        'timezone'
    );
}
?>
