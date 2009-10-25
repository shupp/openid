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
?>
<html>
    <head>
        <title>PEAR OpenID Examples</title>
        <link rel="stylesheet" href="example.css" type="text/css" />
        <script type="text/javascript" src="selector/js/jquery-1.3.2.min.js">
    </head>
    <body>
<script type="text/javascript">
function newWindow() {
    var url = $.post('./relyingparty.php',
                     $("#rp_form").serialize());
     window.open(url,
           'rp_popup',
           'toolbar=no,location=yes,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=450,height=500,top=20,left=50');
     return false;
}
</script>
<?php
if (isset($contents)) {
    echo $contents;
}
?>
    <h3>
    <a href="./discovery.php">Discovery Example</a> 
        &nbsp | &nbsp 
    <a href="./relyingparty.php">Relying Party Example</a>
        &nbsp | &nbsp 
    <a href="./selector/demo.html">Relying Party Selector Example</a>
    </h3>
    </body>
</html>
