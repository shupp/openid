<?php
/**
 * OpenID 
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

// A tool for viewing OpenID messages from HTTP format

require_once 'common/config.php';

/**
 * uritoArray 
 * 
 * @param string $string The URI to parse
 * 
 * @return array
 */
function uritoArray($string)
{
    $exploded = explode('?', $string);
    if (count($exploded) > 1) {
        $queryString = $exploded[1];
    } else {
        $queryString = $exploded[0];
    }

    $message = new OpenID_Message($queryString, OpenID_Message::FORMAT_HTTP);
    return $message->getArrayFormat();
}

// CLI
if (isset($argv)) {
    if (!isset($argv[1])) {
        echo "Usage: " . $argv[0] . " <uri>\n";
        exit;
    }

    print_r(uriToArray($argv[1]));
    exit;
}

// WEB
$uri = '';
if (isset($_POST['uri'])) {
    $uri = uriToArray($_POST['uri']);
}
$contents = file_get_contents('common/message_form.php');

if (!empty($uri)) {
    $contents .= "
    <div class='relyingparty_results'>
    <p>
    <table>
    <tr colspan=2><td><p><br><b>Message Contents</b></td></tr>";

    foreach ($uri as $key => $value) {
        $contents .= "<tr><td align=left>$key</td><td>$value</td></tr>\n";
    }

    $contents .= "</table>\n</div>\n";
}

require_once 'common/wrapper.php';
exit;

?>
