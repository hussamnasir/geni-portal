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
require_once("header.php");
require_once("settings.php");
require_once("user.php");
require_once("file_utils.php");
require_once("sr_client.php");
require_once("sr_constants.php");
require_once("am_client.php");
require_once("sa_client.php");
$user = geni_loadUser();
if (! $user->privSlice() || ! $user->isActive()) {
  relative_redirect('home.php');
}
?>

<?php
function no_slice_error() {
  header('HTTP/1.1 404 Not Found');
  print 'No slice id specified.';
  exit();
}

 if (! count($_GET)) {
  // No parameters. Return an error result?
  // For now, return nothing.
  no_slice_error();
}
unset($slice);
include("tool-lookupids.php");
if (! isset($slice)) {
  no_slice_error();
}


include("query-sliverstatus.php");

$header = "Status of Slivers on slice: $slice_name";
// include("print-text.php");

?>

<?php

function print_sliver_status( $obj ) {
  print "<table>";
  $args = array_keys( $obj );
  foreach ($args as $arg){
    $arg_obj = $obj[$arg];
    /* ignore aggregates which returned nothing */
    if (!is_array($arg_obj)){
      continue;
    }

    $geni_urn = $arg_obj['geni_urn'];
    $geni_status = $arg_obj['geni_status'];
    $geni_status = strtolower( $geni_status );
    $geni_resources = $arg_obj['geni_resources'];


    print "<tr class='aggregate'><th>Status</th><th colspan='2'>Aggregate</th></tr>";
    print "<tr class='aggregate'><td class='$geni_status'>$geni_status</td><td colspan='2'>$arg</td></tr>";
    $firstRow = True;
    $num_rsc = count($geni_resources);
    foreach ($geni_resources as $rsc){
	$rsc_urn = $rsc['geni_urn'];
	$rsc_status = $rsc['geni_status'];
	$rsc_error = $rsc['geni_error'];
	if ($firstRow) {
	  $colspan = "colspan='$num_rsc'";
	  $firstRow = False;
	  print "<tr class='resource'><th class='notapply'></th><th>Status</th><th>Resource</th></tr>";
	} else {
	  $colspan = "";
	}
	
	print "<tr  class='resource'>";
	if ($rsc_status == "failed"){
	  print "<td rowspan=$num_rsc>";
	} else {
	  print "<td rowspan=$num_rsc class='notapply'>";
	}
	print "</td><td class='$rsc_status'>$rsc_status</td><td>$rsc_urn</td></tr>";
	if ($rsc_status == "failed"){
	  print "<tr><td></td><td>$rsc_error</td></tr>";
	}
      }    
  }
  print "</table>";
}

show_header('GENI Portal: Slices',  $TAB_SLICES);
include("tool-breadcrumbs.php");
print "<h2>$header</h2>\n";

if (isset($msg) and isset($obj)){
print "<pre>$msg</pre>";
print_sliver_status( $obj );
} else {
  print "<p><i>Failed to determine status of resources.</i></p>";
}

print "<a href='slices.php'>Back to All slices</a>";
print "<br/>";
print "<a href='slice.php?slice_id=$slice_id'>Back to Slice $slice_name</a>";
include("footer.php");

?>
