<?php
/**
 * OpenID_Extension_OAuth
 *
 * PHP Version 5.2.0+
 *
 * @uses      OpenID_Extension
 * @category  Auth
 * @package   OpenID
 * @author    Jeff Hodsdon <jeffhodsdon@gmail.com> 
 * @copyright 2009 Jeff Hodsdon
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

require_once 'OpenID/Extension.php';
require_once 'HTTP/OAuth/Consumer.php';

/**
 * Provides support for the OAuth extension
 *
 * @uses      OpenID_Extension
 * @category  Auth
 * @package   OpenID
 * @author    Jeff Hodsdon <jeffhodsdon@gmail.com> 
 * @copyright 2009 Jeff Hodsdon
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 * @link      http://step2.googlecode.com/svn/spec/openid_oauth_extension/latest/openid_oauth_extension.html
 */
class OpenID_Extension_OAuth extends OpenID_Extension
{
    /**
     * URI of the OAuth namespace
     * 
     * @var string $namespace
     */
    protected $namespace ='http://specs.openid.net/extensions/oauth/1.0';

    /**
     * Alias to use
     * 
     * @var string $alias
     */
    protected $alias = 'oauth';

    /**
     * Supported keys in a request
     * 
     * @var array $requestKeys
     */
    protected $requestKeys = array('consumer', 'scope');

     /**
     * Supported keys in a response
     * 
     * @var array $responseKeys
     */
    protected $responseKeys = array('request_token', 'scope');
 
    /**
     * Fetch an OAuth access token
     *
     * Requires an request_token to be present in self::$values
     *
     * @param string $consumerKey    OAuth consumer application key
     * @param string $consumerSecret OAuth consumer secret key
     * @param string $url            Access token url
     * @param array  $params         Paramters to include in the request
     *                               for the access token
     * @param string $method         HTTP Method to use
     *
     * @return array Key => Value array of token and token secret
     *
     * @throws OpenID_Exception     On no request_token in response message
     * @throws HTTP_OAuth_Exception On issue with getting the request token
     *
     * @see http://step2.googlecode.com/svn/spec/openid_oauth_extension/latest/openid_oauth_extension.html
     */
    public function getAccessToken($consumerKey,
                                   $consumerSecret,
                                   $url,
                                   array $params = array(),
                                   $method = 'GET')
    {
        $requestToken = $this->get('request_token');
        if ($requestToken === null) {
            throw new OpenID_Exception('No oauth request token in OpenID message');
        }

        $consumer = $this->getConsumer($consumerKey, $consumerSecret);
        $consumer->setToken($requestToken);

        // Token secret is blank per spec
        $consumer->setTokenSecret('');

        // Blank verifier
        $consumer->getAccessToken($url, '', $params, $method);

        return array('oauth_token' => $consumer->getToken(),
            'oauth_token_secret' => $consumer->getTokenSecret());
    }

    /**
     * Returns a new HTTP_OAuth_Consumer instance.  Mocked for testing.
     * 
     * @param string $consumerKey    Consumer Key
     * @param string $consumerSecret Consumer Secret
     * 
     * @return HTTP_OAuth_Consumer
     */
    protected function getConsumer($consumerKey, $consumerSecret)
    {
        return new HTTP_OAuth_Consumer($consumerKey, $consumerSecret);
    }
}

?>
