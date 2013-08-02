<?php
//----------------------------------------------------------------------
// Copyright (c) 2011 Raytheon BBN Technologies
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
?>
<?php
require_once("user.php");
require_once("cert_utils.php");
require_once("rq_client.php");
?>

<?php
function js_delete_ssh_key() {
  /*
   *    * A javascript function to confirm the delete.
   */
  echo <<< END
  <script type="text/javascript">
function deleteSshKey(dest){
  var r=confirm("Are you sure you want to delete this ssh key?");
    if (r==true) {
      window.location = dest;
  }
}
</script>
END;
}
?>

<h1>Profile</h1>

<?php include "tabs.js"; ?>

  <div id='tablist'>
		<ul class='tabs'>
			<li><a href='#ssh'>SSH Keys</a></li>
			<li><a href='#accountdetails'>Account Details</a></li>
			<li><a href='#outstandingrequests'>Outstanding Requests</a></li>
			<li><a href='#accountsummary'>Account Summary</a></li>
			<li><a href='#rspecs'title="Resource Specifications">RSpecs</a></li>
			<li><a href='#omni'>Configure <code>omni</code></a></li>
			<li style="border-right: none"><a href='#other'>Other</a></li>
		</ul>
  </div>
		
<?php

  // BEGIN the tabContent class
  // this makes a fixed height box with scrolling for overflow
  echo "<div class='tabContent'>";

?>

<?php
/*----------------------------------------------------------------------
 * SSH key management
 *----------------------------------------------------------------------
 */
// BEGIN SSH tab
echo "<div id='ssh'>";
print "<h2>SSH Keys</h2>\n";
$keys = $user->sshKeys();

$disable_ssh_keys = "";
if ($in_lockdown_mode) $disable_ssh_keys = "disabled";

if (count($keys) == 0)
  {
    // No ssh keys are present.
    print "<p>No SSH keys have been uploaded. ";
    print "SSH keys are required to log in to reserved compute resources.</p>\n";
    print "<p>You can <button $disable_ssh_keys onClick=\"window.location='generatesshkey.php'\">generate and download an SSH keypair</button> ";
    print "or <button $disable_ssh_keys onClick=\"window.location='uploadsshkey.php'\">upload an SSH public key</button>, if you have one you want to use.</p>\n";
    print "<p>If you're not sure what to do, choose 'Generate'.</p>\n";

  }
else
  {
    $download_pkey_url = relative_url('downloadsshkey.php?');
    $download_public_key_url = relative_url('downloadsshpublickey.php?');
    $edit_sshkey_url = relative_url('sshkeyedit.php?');
    $delete_sshkey_url = relative_url('deletesshkey.php?');
    js_delete_ssh_key();  // javascript for delete key confirmation
    print "\n<table>\n";
    print "<tr><th>Name</th><th>Description</th><th>Public Key</th><th>Private Key</th>"
          . "<th>Edit</th><th>Delete</th></tr>\n";
    foreach ($keys as $key) {
      $args['id'] = $key['id'];
      $query = http_build_query($args);
      if (is_null($key['private_key'])) {
        $pkey_cell = 'N/A';
      } else {
        $pkey_cell = ("<button onClick=\"window.location='"
                . $download_pkey_url . $query
                . "'\">Download Private Key</button>");
      }
      $public_key_download_cell = ("<button $disable_ssh_keys onClick=\"window.location='"
                . $download_public_key_url . $query
                . "'\">Download Public Key</button>");
      $edit_cell = ("<button $disable_ssh_keys onClick=\"window.location='"
                . $edit_sshkey_url . $query
                . "'\">Edit</button>");
      $delete_cell = ("<button $disable_ssh_keys onClick=\"deleteSshKey('"
                . $delete_sshkey_url . $query
                . "')\">Delete</button>");
      print "<tr>"
      . "<td>" . htmlentities($key['filename']) . "</td>"
      . "<td>" . htmlentities($key['description']) . "</td>"
      . '<td>' . $public_key_download_cell . '</td>'
      . '<td>' . $pkey_cell . '</td>'
      . '<td>' . $edit_cell . '</td>'
      . '<td>' . $delete_cell . '</td>'
      . "</tr>\n";
    }
    print "</table>\n";
    print "<p><b>Note</b>: You will need your SSH private key on your local machine. </p>\n<p>If you generated your SSH keypair on this portal and have not already done so, be sure to:</p>
     <ol>
     <li>Download your SSH key.</li>
     <li>After you download your key, be sure to set local permissions on that file appropriately. On Linux and Mac, do <pre>chmod 0600 [path-to-SSH-private-key]</pre></li>
     <li>When you invoke SSH to log in to reserved resources, you will need to remember the path to that file.</li>
     <li>Your SSH command will be something like: <pre>ssh -i path-to-SSH-key-you-downloaded [username]@[hostname]</pre>\n";
    print "</ol>\n";
    print "<p><button $disable_ssh_keys onClick=\"window.location='uploadsshkey.php'\">Upload another SSH public key</button></p>\n";
  }

// END SSH tab
echo "</div>";

// BEGIN account details tab
echo "<div id='accountdetails'>";
$disable_account_details = "";
$disable_authorize_tools = "";
if($in_lockdown_mode) {
  $disable_account_details = "disabled";
  $disable_authorize_tools = "disabled";
}
print "<h2>Edit Account Details</h2>";
print "<p><button $disable_account_details onClick=\"window.location='modify.php'\">Modify user supplied account details </button> (e.g. to become a Project Lead).</p>";
print "<p><button $disable_authorize_tools onClick=\"window.location='kmhome.php'\">Authorize or De-authorize tools</button> to act on your behalf.</p>";

// END account details tab
echo "</div>";

// BEGIN outstand requests tab
echo "<div id='outstandingrequests'>";
print "<h2>Outstanding Requests</h2>";

// Show outstanding requests BY this user
if (! isset($sa_url)) {
  $sa_url = get_first_service_of_type(SR_SERVICE_TYPE::SLICE_AUTHORITY);
  if (! isset($sa_url) || is_null($sa_url) || $sa_url == '') {
    error_log("Found no SA in SR!'");
  }
}
if (! isset($ma_url)) {
  $ma_url = get_first_service_of_type(SR_SERVICE_TYPE::MEMBER_AUTHORITY);
  if (! isset($ma_url) || is_null($ma_url) || $ma_url == '') {
    error_log("Found no MA in SR!'");
  }
}

// FIXME: Also show rejected requests?
$preqs = get_requests_by_user($sa_url, $user, $user->account_id, CS_CONTEXT_TYPE::PROJECT, null, RQ_REQUEST_STATUS::PENDING);
$sreqs = get_requests_by_user($sa_url, $user, $user->account_id, CS_CONTEXT_TYPE::SLICE, null, RQ_REQUEST_STATUS::PENDING);
$reqs = array_merge($preqs, $sreqs);
if (isset($reqs) && count($reqs) > 0) {
  print "Found " . count($reqs) . " outstanding request(s) by you:<br/>\n";
  print "<table>\n";
  // Could add the lead and purpose?
  print "<tr><th>Request Type</th><th>Project/Slice</th><th>Request Created</th><th>Request Reason</th><th>Cancel Request?</th></tr>\n";
  $REQ_TYPE_NAMES = array();
  $REQ_TYPE_NAMES[] = 'Join';
  $REQ_TYPE_NAMES[] = 'Update Attributes';
  foreach ($reqs as $request) {
    $name = "";
    //error_log(print_r($request, true));
    $typestr = $REQ_TYPE_NAMES[$request[RQ_REQUEST_TABLE_FIELDNAME::REQUEST_TYPE]] . " " . $CS_CONTEXT_TYPE_NAME[$request[RQ_REQUEST_TABLE_FIELDNAME::CONTEXT_TYPE]];
    if ($request[RQ_REQUEST_TABLE_FIELDNAME::CONTEXT_TYPE] == CS_CONTEXT_TYPE::PROJECT) {
      //error_log("looking up project " . $request[RQ_REQUEST_TABLE_FIELDNAME::CONTEXT_ID]);
      $project = lookup_project($sa_url, $user, $request[RQ_REQUEST_TABLE_FIELDNAME::CONTEXT_ID]);
      $name = $project[PA_PROJECT_TABLE_FIELDNAME::PROJECT_NAME];
      $cancel_url="cancel-join-project.php?request_id=" . $request[RQ_REQUEST_TABLE_FIELDNAME::ID];
    } elseif ($request[RQ_REQUEST_TABLE_FIELDNAME::CONTEXT_TYPE] == CS_CONTEXT_TYPE::SLICE) {
      $slice = lookup_slice($sa_url, $request[RQ_REQUEST_TABLE_FIELDNAME::CONTEXT_ID]);
      $name = $slice[SA_SLICE_TABLE_FIELDNAME::SLICE_NAME];
      $cancel_url="cancel-join-slice.php?request_id=" . $request[RQ_REQUEST_TABLE_FIELDNAME::ID];
    } else {
      $name = "";
      $cancel_url="cancel-account-mod.php?request_id=" . $request[RQ_REQUEST_TABLE_FIELDNAME::ID];
    }

    $cancel_button = "<button style=\"\" onClick=\"window.location='" . $cancel_url . "'\"><b>Cancel Request</b></button>";
    $reason = $request[RQ_REQUEST_TABLE_FIELDNAME::REQUEST_TEXT];
    $req_date_db = $request[RQ_REQUEST_TABLE_FIELDNAME::CREATION_TIMESTAMP];
    $req_date = dateUIFormat($req_date_db);
    print "<tr><td>$typestr</td><td>$name</td><td>$req_date</td><td>$reason</td><td>$cancel_button</td></tr>\n";
  }
  print "</table>\n";
  print "<br/>\n";
} else {
  print "<p><i>No outstanding requests to join projects or slices or change your profile.</i></p>\n";
}

// END outstanding requests tab
echo "</div>";

// BEGIN account summary tab
echo "<div id='accountsummary'>";
print "<h2>Account Summary</h2>\n";
// Show username, email, affiliation, IdP, urn, prettyName, maybe project count and slice count
// Put this in a nice table
print "<table>\n";
print "<tr><th>Name</th><td>" . $user->prettyName() . "</td></tr>\n";
print "<tr><th>Email</th><td>" . $user->email() . "</td></tr>\n";
print "<tr><th>Home Institution</th><td>" . $user->idp_url . "</td></tr>\n";
print "<tr><th>Affiliation</th><td>" . $user->affiliation . "</td></tr>\n";
print "<tr><th>GENI URN</th><td>" . $user->urn() . "</td></tr>\n";
print "<tr><th>GENI Username</th><td>" . $user->username . "</td></tr>\n";
// FIXME: Project count? Slice count?
// FIXME: Other attributes?
// FIXME: Permissions
print "</table>\n";

// END account summary tab
echo "</div>";

//print "<h1>My Stuff</h1>\n";

// BEGIN rspecs tab
echo "<div id='rspecs'>";
/*----------------------------------------------------------------------
 * RSpecs
 *----------------------------------------------------------------------
 */
print "<h2>Manage Resource Specifications (RSpecs)</h2>\n";

$disable_manage_rspecs = "";
if ($in_lockdown_mode) $disable_manage_rspecs = "disabled";

print "<p><button $disable_manage_rspecs onClick=\"window.location='rspecs.php'\">"
  . "Manage RSpecs</button></p>\n";

// END rspecs tab
echo "</div>";
?>


<?php
/*----------------------------------------------------------------------
 * SSL key management
 *----------------------------------------------------------------------
 */

// BEGIN omni tab
echo "<div id='omni'>";

// Does the user have an outside certificate?
$result = ma_lookup_certificate($ma_url, $user, $user->account_id);
$has_certificate = ! is_null($result);
// FIXME: hardcoded paths
$create_url = "https://" . $_SERVER['SERVER_NAME'] . "/secure/kmcert.php?close=1";
$download_url = "https://" . $_SERVER['SERVER_NAME'] . "/secure/kmcert.php?close=1";
?>

<h2>Configure <code>omni</code></h2>
<p><a href='http://trac.gpolab.bbn.com/gcf/wiki/Omni'><code>omni</code></a> is a command line tool.
It is intended for more advanced users. In order to use <code>omni</code> or other command line tools you will need to
<?php if ($has_certificate): ?>
<a href="<?php print $download_url?>" target="_blank">download your SSL certificate</a>.
<?php else: ?>
<a href="<?php print $create_url?>" target="_blank">create an SSL certificate</a>.
<?php endif; ?>
</p>

<h3>Option 1: Automatic <code>omni</code> configuration</h3>
<p>Use <a href='http://trac.gpolab.bbn.com/gcf/wiki/OmniConfigure/Automatic'><code>omni-configure</code></a>
to generate a configuration file for you:</p>
  <ol>
    <li>Make sure you are running <b>omni 2.3.1</b> or later. 
       <ul>
         <li>To determine the version of an existing <code>omni</code> installation, run:
	<pre>omni.py --version</pre>
	 </li>
         <li>If necessary, <a href="http://trac.gpolab.bbn.com/gcf/wiki#GettingStarted" target='_blank'>download</a> and <a href="http://trac.gpolab.bbn.com/gcf/wiki/QuickStart" target='_blank'>install</a> the latest version of <code>omni</code>.</li>

       </ul>
    </li>
    <li>Download your <a href='omni-bundle.php'>customized configuration data</a>.</li>
    <li>Follow the <a href='http://trac.gpolab.bbn.com/gcf/wiki/OmniConfigure/Automatic'><code>omni-configure</code> instructions</a>.</li>
  </ol>

<h3>Option 2: Manual <code>omni</code> configuration</h3>
<p><a href='tool-omniconfig.php'>Download and customize a template <code>omni</code> configuration file</a>.</p>

<?php
// END omni tab
echo "</div>";
?>

<!--
<table>
<tr><th>Tool</th><th>Description</th><th>Configuration File</th></tr>
<tr>
  <td><a href='http://trac.gpolab.bbn.com/gcf/wiki'>Omni</a></td>
  <td>command line resource allocation tool</td>
  <td><a href='tool-omniconfig.php'>Get omni_config</a></td>
</tr>
<tr>
  <td>omni_configure.py</td>
  <td>omni configuration tool</td>
  <td><a href='omni-bundle.php'>Get omni-bundle.zip</a></td>
</tr>
</table>
-->
<?php
// BEGIN other tab
echo "<div id='other'>";
/*----------------------------------------------------------------------
 * ABAC (if enabled)
 *----------------------------------------------------------------------
 */
if ($portal_enable_abac)
  {
    print '<h2>ABAC</h2>\n';
    print "<button onClick=\"window.location='abac-id.php'\">Download your ABAC ID</button><br/>\n";
    print "<button onClick=\"window.location='abac-key.php'\">Download your ABAC private key</button>\n";
  }


     print '<h2>iRODS</h2>';
$irodsdisabled="disabled";
if ($user->hasAttribute('enable_irods'))
  $irodsdisabled = "";
print "<p><button onClick=\"window.location='irods.php'\" $irodsdisabled><b>Create iRODS Account</b></button></p>\n";
// END other tab
echo "</div>";

  // END the tabContent class
  echo "</div>";

?>
