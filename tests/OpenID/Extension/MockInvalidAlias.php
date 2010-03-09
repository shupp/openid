<?php
/**
 * OpenID_Extension_MockInvalidAlias 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Extension_Mock
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

require_once 'OpenID/Extension/Mock.php';

/**
 * OpenID_Extension_MockInvalidAlias 
 * 
 * @uses      OpenID_Extension_Mock
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_Extension_MockInvalidAlias extends OpenID_Extension_Mock
{
    protected $alias = 'error';
}
?>
