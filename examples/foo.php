<html>
    <head>
        <script type="text/javascript" src="selector/js/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="test.js"></script>
    </head>
    <body>

    <form id="rp_form" onSubmit="javascript:billShupp();return false;" action="./bar.php" method="POST">
    <table border="0">
        <tr><td>OpenID URL: </td><td><input type="text" name="identifier"></td></tr>
        <tr><td>checkid_immediate: </td><td><input type="checkbox" name="checkid_immediate"></td></tr>
        <tr><td>Disable Associations: </td><td><input type="checkbox" name="disable_associations"></td></tr>
        <tr><td>Simple Reg: </td><td><input type="checkbox" name="sreg"></td></tr>
        <tr><td>AX: </td><td><input type="checkbox" name="ax"> </td></tr>
        <tr><td>UI: </td><td><input type="checkbox" name="ui"> </td></tr>
        <tr><td>Debug: </td><td><input type="checkbox" name="debug"></td></tr>
        <tr><td></td><td><input type="submit" name="start" value="submit"></td></tr>
    </table>
    </form>

</body>
</html>
