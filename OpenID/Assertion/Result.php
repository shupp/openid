<?php
/**
 * OpenID_Assertion_Result 
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

/**
 * Required files
 */
require_once 'OpenID.php';
require_once 'OpenID/Assertion/Exception.php';

/**
 * A class that represents the result of verifying an assertion.
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Assertion_Result
{
    /**
     * The check_authentication response
     * 
     * @var OpenID_Message
     */
    protected $checkAuthResponse = null;

    /**
     * What assertion method was used (association, check_authentication)
     * 
     * @var string
     */
    protected $assertionMethod = null;

    /**
     * Whether the assertion was positive or negative
     * 
     * @var bool
     */
    protected $assertion = false;

    /**
     * Sets the check_authentication response in the form of an OpenID_Message 
     * instance
     * 
     * @param OpenID_Message $message The response message
     * 
     * @see getCheckAuthResponse()
     * @return void
     */
    public function setCheckAuthResponse(OpenID_Message $message)
    {
        $this->checkAuthResponse = $message;
    }

    /**
     * Gets the check_authentication response
     * 
     * @see setCheckAuthResponse()
     * @return OpenID_Message
     */
    public function getCheckAuthResponse()
    {
        return $this->checkAuthResponse;
    }

    /**
     * Indicates if the assertion was successful (positive) or not (negative)
     *
     * @return bool true on if a positive assertion was verified, false otherwise
     */
    public function success()
    {
        return $this->assertion;
    }

    /**
     * Sets the result of verifying the assertion.
     * 
     * @param bool $value true if successful, false otherwise
     * 
     * @return void
     */
    public function setAssertionResult($value)
    {
        $this->assertion = (bool)$value;
    }

    /**
     * Gets the method used to verify the assertion
     * 
     * @return string
     */
    public function getAssertionMethod()
    {
        return $this->assertionMethod;
    }

    /**
     * Sets the assertion method used to verify the assertion
     * 
     * @param string $method Method used
     * 
     * @throws OpenID_Assertion_Exception on invalid assertion mode
     * @return void
     */
    public function setAssertionMethod($method)
    {
        switch ($method) {
        case OpenID::MODE_ASSOCIATE:
        case OpenID::MODE_CHECKID_SETUP:
        case OpenID::MODE_CHECKID_IMMEDIATE:
        case OpenID::MODE_CHECK_AUTHENTICATION:
        case OpenID::MODE_CANCEL:
        case OpenID::MODE_SETUP_NEEDED:
            $this->assertionMethod = $method;
            break;
        default:
            throw new OpenID_Assertion_Exception('Invalid assertion method');
        }
    }
}
?>
