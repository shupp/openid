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
 * @link      http://github.com/shupp/openid
 */

$skipcache_value = '';
if ($skipcache) {
    $skipcache_value = ' checked';
}
?>
    <form action="./discovery.php" method="POST">
    <table>
    <tr>
        <td>OpenID URL to discover: </td>
        <td>
            <input type="text" name="identifier" value="<?php echo $identifier?>">
        </td>
    </tr>
    <tr>
        <td>Skip Cache?</td>
        <td>
            <input type="checkbox" name="skipcache" <?php echo $skipcache_value ?>">
        </td>
    </tr>
    </table>
    <input type="submit" name="start" value="submit">
    </form>
