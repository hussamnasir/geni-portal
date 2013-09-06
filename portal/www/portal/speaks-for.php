<?php
//----------------------------------------------------------------------
// Copyright (c) 2013 Raytheon BBN Technologies
//
// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and/or hardware specification (the "Work") to
// deal in the Work without restriction, including without limitation the
// rights to use, copy, modify, merge, publish, distribute, sublicense,
// and/or sell copies of the Work, and to permit persons to whom the Work
// is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Work.
//
// THE WORK IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE WORK OR THE USE OR OTHER DEALINGS
// IN THE WORK.
//----------------------------------------------------------------------

/*
 * Request a speaks-for credential from the user.
 */
require_once 'header.php';
require_once 'portal.php';
require_once 'cert_utils.php';

$portal = Portal::getInstance();
$toolcert = $portal->certificate();
$toolurn = pem_cert_geni_urn($toolcert);

/*
 * XXX FIXME: put the authorization service URL in a config file.
 */
$auth_svc_js = 'https://tabletop.gpolab.bbn.com/xml-signer/geni-auth.js';

/*------------------------------------------------------------
 * Page display starts here
 *------------------------------------------------------------
 */
show_header('GENI Portal: Authorization');
?>

<script src="<?php echo $auth_svc_js;?>"></script>
<script type="text/plain" id="toolcert"><?php echo $toolcert;?></script>
<script>
var portal = {};
portal.authorize = function()
{
  var tool_urn = '<?php echo $toolurn;?>';
  var tool_cert = document.getElementById('toolcert').innerHTML;
  genilib.authorize(tool_urn, tool_cert, portal.authZResponse);
  return false;
}
portal.authZResponse = function(speaks_for_cred)
{
  // Called if the user authorizes us in the signing tool
  alert('Response available from genilib.authorize');
  $("#cred").text(speaks_for_cred).html();
  var jqxhr = $.post('speaks-for-upload.php', speaks_for_cred);
  jqxhr.done(function(data, textStatus, jqxhr) {
      alert('got result: ' + textStatus);
    })
  .fail(function(data, textStatus, jqxhr) {
      alert('got fail result: ' + textStatus);
    });
}
portal.initialize = function()
{
  /* Add a click callback to the "authorize" button. */
  $('#authorize').click(portal.authorize);
}
$(document).ready(portal.initialize);
</script>

<?php
  /*
   * Note the 'onsubmit="return false;"' attribute on the form. It's
   * important that the form not actually submit itself, otherwise the
   * inter-window communication can't happen because the parent window
   * has disappeared.
   */
?>
<form onsubmit="return false;">
   <input id="authorize"
          type="submit"
          value="Click here to authorize"/>
</form>
<?php
  /* This div is for debugging only. */
?>
<div>
  <pre id="cred"/>
</div>

<?php
include 'footer.php';
?>
