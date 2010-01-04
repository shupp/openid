<script type="text/javascript" src="selector/js/jquery-1.2.6.min.js"></script>
<script type="text/javascript">
function toggleOAuth() {
    $('#oauth-scope').toggle();
    $('#oauth-consumer-key').toggle();
    $('#oauth-consumer-secret').toggle();
    $('#oauth-access-token-url').toggle();
    $('#oauth-access-token-method').toggle();
}
</script>

    <form action="./relyingparty.php" method="POST">
    <table border="0">
        <tr><td>OpenID URL: </td><td><input type="text" name="identifier"></td></tr>
        <tr><td>checkid_immediate: </td><td><input type="checkbox" name="checkid_immediate"></td></tr>
        <tr><td>Disable Associations: </td><td><input type="checkbox" name="disable_associations"></td></tr>
        <tr><td>Simple Reg: </td><td><input type="checkbox" name="sreg"></td></tr>
        <tr><td>AX: </td><td><input type="checkbox" name="ax"> </td></tr>
        <!-- <tr><td>UI: </td><td><input type="checkbox" name="ui"> </td></tr> -->
        <tr><td>OAuth: </td><td><input type="checkbox" name="oauth" onclick="toggleOAuth()"> </td></tr>
        <tr style="display: none;" id="oauth-scope"><td>OAuth Scope: </td><td><input type="text" name="oauth_scope"> </td></tr>
        <tr style="display: none;" id="oauth-consumer-key"><td>OAuth Consumer Key: </td><td><input type="text" name="oauth_consumer_key"> </td></tr>
        <tr style="display: none;" id="oauth-consumer-secret"><td>OAuth Consumer Secret: </td><td><input type="text" name="oauth_consumer_secret"> </td></tr>
        <tr style="display: none;" id="oauth-access-token-url"><td>OAuth Access Token URL: </td><td><input type="text" name="oauth_access_token_url"> </td></tr>
        <tr style="display: none;" id="oauth-access-token-method"><td>OAuth Access Token Method: </td><td><select name="oauth_access_token_method"><option value="GET">GET</option><option value="POST">POST</option></select></td></tr>
        <tr><td>Debug: </td><td><input type="checkbox" name="debug"></td></tr>
        <tr><td></td><td><input type="submit" name="start" value="submit"></td></tr>
    </table>
    </form>
