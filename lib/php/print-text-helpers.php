<?php
//----------------------------------------------------------------------
// Copyright (c) 2012-2016 Raytheon BBN Technologies
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
require_once("am_map.php");

function print_agg_list( $list ){
  print_list( $list, $use_nickname=True );
}


function print_list( $list, $use_nickname=False ){
  $num_items = count($list);
  print "<ul class='list'>";
  if (count($list) == 0) {
    echo "<li><i>No aggregates.</i></li>\n";    
  } else {
    foreach ($list as $item) {
      if ($use_nickname) {
	$name=am_name($item);
      } else {
	$name = $item;
      }
      echo "<li>$name</li>\n";
    }
  }
  print "</ul>\n";
}


function print_xml( $xml ){
    // Use libxml to format XML prettily, but suppress any XML parsing errors
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $wasVal = libxml_use_internal_errors(true);
    $dom->loadXML($xml);
    if (count(libxml_get_errors()) == 0) {
      $xml = $dom->saveXML();
    } else {
      //error_log("Cannot prettify malformed XML. " . print_r(libxml_get_errors()) . "; " . $xml);
      // Add a newline after any close tag that doesn't already have one
      $xml = preg_replace("/>([ \t\r]*<)/", ">\n$1", $xml);
    }
    libxml_clear_errors();
    if (! $wasVal) {
      libxml_use_internal_errors($wasVal);
    }

    $xml2 = explode("\n",$xml);
    print "<div class='xml'>";
    foreach ($xml2 as $line_num => $line) {
      if (trim($line) == "") {
	continue;
      }
      echo htmlspecialchars(rtrim($line)) . "\n";
    }
    print "</div>\n";
}


function get_name_from_urn( $urn ){
  $urn_pieces = explode( "+", $urn );
  $name = end($urn_pieces);
  // In the case of EXOGeni, we want the name of the node, 
  // which is located as the subauthority of the URN
  // E.g. for component ID 
  // urn:publicid:IDN+exogeni.net:osfvmsite+node+orca-vm-cloud
  // We want to return the name 'osfvmsite', not 'orca-vm-cloud'
  if(strpos($urn, "+exogeni.net:")) {
    $authority = $urn_pieces[1];
    $authority_pieces = explode(":", $authority);
    $name = $authority_pieces[1] . ":" . $name;
  }
  return $name;
}

function get_auth_from_urn( $urn ){
  if (! isset($urn) or $urn == "") {
    return $urn;
  }
  if (strpos($urn, "urn:publicid:IDN+") < 0) {
    return $urn;
  }
  // Exclude the usual prefix with trailing +
  $temp = substr($urn, strlen("urn:publicid:IDN+"));
  if (! $temp or $temp == "") {
    return $urn;
  }
  // grap all up to the next +
  $auth = substr($temp, 0, strpos($temp, "+"));
  if (! $auth or $auth == "") {
    return $urn;
  }
  return $auth;
}

function print_rspec_pretty( $xml, $manifestOnly=True, $filterToAM=False,
                            $componentMgrURN="", $am_id=null){
  $err_str = "<p><i>Resource Specification returned was not valid XML.</i></p>";
  try {
    $rspec = new SimpleXMLElement($xml);
    if (!$rspec) {
      error_log("Call to print_rspec_pretty() FAILED to parse xml: " . substr((string)($xml), 0, 40) . "...");
      echo $err_str;
      return ;
    }
  } catch (Exception $e) {
    error_log("Call to print_rspec_pretty() FAILED to parse xml: " . substr((string)($xml), 0, 40) . "... : " . (string)($e));
    echo $err_str;
    return;
  }

  $rspec->registerXPathNamespace("def", "http://www.geni.net/resources/rspec/3/manifest.xsd");
  $nodes = $rspec->node;
// $nodes = $rspec->xpath('//def:node[@component_manager_id=$componentMgrURN]');
// $nodes = $rspec->xpath('/def:node');
  $links = $rspec->link;
  $stitching = $rspec->stitching;
  $num_nodes = $nodes->count();
//  $num_nodes = count($nodes);
  $num_links = $links->count();
  $num_stitching = $stitching->count();

  if ($num_nodes + $num_links == 0) {
    error_log("print-rspec-pretty got RSpec with 0 nodes and links: " . substr((string)($xml), 0, 40));
    print_xml($xml);
    return;
  }

  $nodes_text = "<b>".$num_nodes."</b> node";
  if ($num_nodes!=1) {
    $nodes_text = $nodes_text."s";
  }
  $links_text = "<b>".$num_links."</b> link";
  if ($num_links!=1) {
    $links_text = $links_text."s";
  }
//COUNT ONLY NODES FOR THIS AM  echo "<p>There are ",$nodes_text," and ",$links_text," at this aggregate.</p>";
  
  print "<div class='xml'>";

  $node_num = 0;
  foreach ($nodes as $node) {

    $num_ifs = $node->interface->count();
    $client_id = $node['client_id'];
    $comp_id = $node['component_id'];
    $comp_mgr_id = $node['component_manager_id'];
    
    // if no sliver id, then skip
    $sliver_id = $node['sliver_id'];
    if (! isset($sliver_id) or is_null($sliver_id) or $sliver_id === '') {
        continue;
    }
    
    if ($filterToAM and ($comp_mgr_id!=$componentMgrURN)){
      $sliver_id = $node['sliver_id'];
      if (! isset($sliver_id) or is_null($sliver_id) or $sliver_id === '') {
	// This is expected if this node was not for the AM that we submitted it to.
	// And the converse is true: We absolutly expect this to be filled in if this node is for this AM
	//	error_log("print-rspec-pretty skipping node '" . $comp_id . "' (client_id '" . $client_id . "') with no sliver_id, and comp_mgr_id $comp_mgr_id != AM URN $componentMgrURN");
	continue;
      }
      $sliver_auth = get_auth_from_urn($sliver_id);
      $compMgrAuth = get_auth_from_urn($componentMgrURN);
      if ($sliver_auth == $compMgrAuth) {
	// For debugging
	// This is expected for node reservations through the ExoSM
	// error_log("Node '" . $comp_id . "' is part of desired AM " . $componentMgrURN . " based on sliver_id " . $sliver_id);
      } else {
	error_log("print-rspec-pretty skipping node '" . $comp_id . "' (client_id '" . $client_id . "'): its comp_mgr " . $comp_mgr_id . " != requested " . $componentMgrURN . " and sliver auth doesnt match either. RSpec " . $sliver_auth . " != " . $compMgrAuth);
	continue;
      }
    }
    $node_num = $node_num+1;
    $comp_name = get_name_from_urn($comp_id);
    $sliver_type=$node->sliver_type;
    $sliver_status = "Unknown";
    $sliver_expiration = expires_from_manifest($node, $xml);
    $host=$node->host;
    $logins = array();
    foreach ($node->services as $service) {
      foreach($service->login as $login) {
	$logins[] = $login;
      }
    }
    // don't display authority info if filtering by AM since it's assumed that
    // the AM info will be mentioned elsewhere
    if($filterToAM) {
        echo "<b>Node #",$node_num,":</b>";
    }
    else {
        echo "<b>Node #",$node_num," (at ",am_name_from_urn((string)$comp_mgr_id),"):</b>";
    }
    echo "<table><tr>\n";
    // This is for Selenium testing
    $client_id_id = 'client_id_' . $node_num;
    echo "<th>Status</th>";
    echo "<th id=\"$client_id_id\">Client ID</th>\n";
    echo "<th>Component ID</th>\n";
    echo "<th>Expiration</th>\n";
    echo "<th>Type</th>\n";
    echo "<th>Hostname</th>\n";
    echo "</tr>\n";
    echo "<tr>\n"; 
    // Replace +, : and . in sliver ID for later jQuery lookup
    $adjusted_sliver_id = str_replace('.', '_', $sliver_id);
    $adjusted_sliver_id = str_replace('+', '_', $adjusted_sliver_id);
    $adjusted_sliver_id = str_replace(':', '_', $adjusted_sliver_id);
    //    error_log("SID = " . $sliver_id . " ASI " . $adjusted_sliver_id);

    echo "<td id='status-$adjusted_sliver_id'>$sliver_status</td>\n";
    //    echo "<td><div id='status-$adjusted_sliver_id'>$sliver_status</div</td>\n";
    echo "<td>",$client_id,"</td>\n";
    echo "<td>",$comp_name,"</td>";
    echo "<td id='expiration-$adjusted_sliver_id'>$sliver_expiration</td>";
    //    echo "<td><div id='expiration-$adjusted_sliver_id'></div></td>";
    if ($sliver_type){
      echo "<td>",$sliver_type['name'],"</td>\n";
    } else {
      echo "<td>(not specified)</td>\n";
    }
    if ($host){
      echo "<td>",$host['name'],"</td>\n";
    } else {
      echo "<td>(not specified)</td>\n";
    }
    echo "</tr>\n";
    $hadLogins = false;
    foreach ($logins as $login) {	
      $ssh_user = $login['username'];
      $ssh_host = $login['hostname'];
      $ssh_port = $login['port'];
      $ssh_url = "ssh://$ssh_user@$ssh_host";
      if ($ssh_port and $ssh_port != 22) {
        $ssh_url .= ":$ssh_port";
      }
      if (! $hadLogins) {
	$hadLogins = true;
	echo "<tr>\n";    
	echo "<th colspan='1'>Login</th>\n";
	echo "<td colspan='5' class='login' id='login_".$client_id."'>";
        if ($am_id) {
          echo "<script>";
          echo "ansibleInventory.add($am_id, '$client_id', '$ssh_host', $ssh_port)";
          echo "</script>";
        }
      } else {
      }
      echo "<a href='$ssh_url' target='_blank'>";
      echo "ssh ", $login['username'],"@",$login['hostname'];
      if ($ssh_port and $ssh_port != 22) {
	echo " -p ", $login['port'];
      }
      echo "</a>\n";
    }
    if ($hadLogins) {
      echo "</td>\n";
      echo "</tr>\n";
    }

    $interfaces = $node->interface;
    /* Add interface header if relevant */
    if ($interfaces->count() > 0) {
      echo "<tr>\n";
      echo "<th colspan='2'>Interfaces</th>";
      echo "<th colspan='2'>MAC</th>\n";
      echo "<th colspan='2'>Layer 3</th>\n";
      echo "</tr>\n";
    }
    foreach ($interfaces as $interface){
      $comp_id = $interface['component_id'];
      $comp_name = get_name_from_urn($comp_id);
      echo "<tr>\n";
      echo "<td>",$interface['client_id'],"</td>";
      echo "<td>",$comp_name,"</td>";
      echo "<td colspan='2'>",$interface['mac_address'],"</td>";
      foreach ($interface as $ip){
	if ($ip->getName() == "ip") {
	  echo "<td colspan='2'>";
	  if ($ip['type'] ){
	     echo $ip['type'],": ";
	  }	  
	  echo $ip['address'],"</td>";
	}
      }
      echo "</tr>\n";
    }
    echo "</table>";
  }
  
  $link_num = 1;
  
  $hadStitch = False;
  foreach ($links as $link) {
  
    // for stitching, skip links that don't have component managers
    if($num_stitching) {
        if(!isset($link->component_manager)) {
            continue;
        }
    }
    
    $comp_mgrs = $link->component_manager;
    $client_id = $link['client_id'];
    
    // There may be multiple component managers
    $link_has_this_cm = False;
    foreach ($comp_mgrs as $cm) {
        if ($cm['name'] == $componentMgrURN) {
	        $link_has_this_cm = True;
	        //	error_log("Link is for this CM based on array of CMs. " . $client_id . " has cm name " . $cm['name'] . " that matches AM URN");
	        break;
	        //      } else {
	        //	error_log("CM not this AM: " . $cm['name'] . " != " . $componentMgrURN);
        }
    }
    
    if ($filterToAM and !$link_has_this_cm) {
        $sliver_id = $link['sliver_id'];
        $sliver_auth = get_auth_from_urn($sliver_id);
        $compMgrAuth = get_auth_from_urn($componentMgrURN);
        if ($sliver_auth == $compMgrAuth) {
	        //	error_log("Link '" . $client_id . "' is part of desired AM " . $componentMgrURN . " based on sliver_id " . $sliver_id);
        } 
        else if (! isset($sliver_id) or is_null($sliver_id) or $sliver_id === '') {
	        // Links often don't have a sliver_id
	        //	error_log("print-rspec-pretty skipping link '" . $client_id . "': its comp_mgrs (" . $comp_mgrs->count() . " of them) != requested " . $componentMgrURN . " and sliver id not given");
	        continue;
        }
        else {
	        error_log("print-rspec-pretty skipping link '" . $client_id . "': its comp_mgrs (" . $comp_mgrs->count() . " of them) != requested " . $componentMgrURN . " and sliver auth doesnt match either. RSpec " . $sliver_auth . " != " . $compMgrAuth);
	        continue;
        }
    }
    
    echo "<b>Link #",$link_num,":</b>";
    $link_num = $link_num+1;
    echo "<table style='margin-bottom:0px'><tr>\n";
    $num=0;
    $num_endpts = $link->interface_ref->count();
    echo "<th>Client ID</th>\n";
    while ($num < $num_endpts) {
      echo "<th>Endpoint #",$num,"</th>\n";
      $num = $num + 1;
    }
    echo "</tr>\n";
    echo "<tr>\n";
    echo "<td>",$link['client_id'],"</td>\n";
    $interface_refs = $link->interface_ref;
    foreach ($interface_refs as $interface_ref) {
      echo "<td>",$interface_ref['client_id'],"</td>";
      /* $comp_id = $interface_ref['component_id']; */
      /* $comp_name = get_name_from_urn($comp_id); */
      /* echo "<td>Component ID: ",$comp_name,"</td>"; */
    }
    print "</tr></table>\n";
    
    /* foreach ($link as $property) { */
    /* 	if ($property->getName() == "property") { */
    /* 	  echo "<li><b>",$property['source_id']," --> ",$property['dest_id'],"</b></li>"; */
    /* 	  //	  echo "<li><b>Source ID: </b>",$property['source_id'],"</li>"; */
    /* 	  //	  echo "<li><b>Destination ID: </b>",$property['dest_id'],"</li>"; */
    /* 	  print "<ul>\n"; */
    /* 	  echo "<li>Capacity: ",$property['capacity'],"</li>"; */
    /* 	  echo "<li>Latency: ",$property['latency'],"</li>"; */
    /* 	  echo "<li>Packet Loss: ",$property['packet_loss'],"</li>"; */
    /* 	  print "</ul>\n"; */
    /* 	} */
    /* } */
    /* print "</ul>\n"; */
    
    /* Do stitching stuff */
    foreach ($stitching as $stitch) {

        foreach ($stitch->path as $path) {
        
            // look for x where <stitching><path id="x"> matches <link client_id="x">
            if((string)$path['id'] == (string)$client_id) {
	      $hadStitch = True;
                print "<table style='margin-top:0px;margin-bottom:0px'><tr>\n";
                print "<th>Hop #</th>\n";
                print "<th>Authority</th>\n";
                print "<th>Interface</th>\n";
                print "<th>Capacity</th>\n";
                print "<th>VLAN</th>\n";
                print "</tr>\n";
            
                foreach ($path->hop as $hop) {
                    
                    print "<tr>\n";
                    print "<td>" . $hop['id'] . "</td>\n";
                    
                    foreach ($hop->link as $link) {
                        print "<td>" . get_auth_from_urn($link['id']) . "</td>\n";
                        print "<td>" . get_name_from_urn($link['id']) . "</td>\n";
                        print "<td>" . trim($link->capacity) . "</td>\n";
                        print "<td>";
                        
                        /* VLAN logic:
                            Show if no aggregates are specified (combined manifest)
                            Show if authority for this hop == the authority for the requested AM
                        */
                        if(($componentMgrURN == "") || (get_auth_from_urn($componentMgrURN) == get_auth_from_urn($link['id']))) {
                            print trim($link->switchingCapabilityDescriptor->
                                        switchingCapabilitySpecificInfo->
                                        switchingCapabilitySpecificInfo_L2sc->
                                        suggestedVLANRange);
                        }
                        else {
                            print "N/A";
                        }
                        
                        print "</td>\n";
                    }
                    
                    print "</tr>\n";
                    
                }
                
                print "</table>\n";
                break;
            }
        }
    
    }
  }
  if ($hadStitch == False && $link_num <= 1 && $node_num == 0) {
    //      print_xml($xml);
    print "<p><i>No resources at this aggregate.</i></p>";
  }
  
  print "</div>\n";
}

function print_rspec( $obj, $pretty, $filterToAM ) {
  $args = array_keys( $obj );

  // How many AMs reported actual results
  $amc = 0;
  foreach ($args as $arg) {
    if (is_array($obj[$arg]) and array_key_exists('value', $obj[$arg]) and array_key_exists('code', $obj[$arg]) and is_array($obj[$arg]['code']) and array_key_exists('geni_code', $obj[$arg]['code']) and $obj[$arg]['code']['geni_code'] == 0) {
      $amc = $amc + 1;
    }
  }

  foreach ($args as $arg) {
    $arg_url = $arg;
    $am_id = am_id( $arg_url );
    $arg_name = am_name($arg_url);
    $arg_urn = am_urn($arg_url);
    if (is_array($obj[$arg]) and array_key_exists('value', $obj[$arg])) {
        $xml = $obj[$arg]['value'];
    } else {
        $xml = "";
    }
    $code = -1;
    if (is_array($obj[$arg]) and array_key_exists('code', $obj[$arg]) and is_array($obj[$arg]['code']) and array_key_exists('geni_code', $obj[$arg]['code'])) {
      $code = $obj[$arg]['code']['geni_code'];
    }
    if (is_array($obj[$arg]) and array_key_exists('output', $obj[$arg])) {
      $output = $obj[$arg]['output'];
    } else if (! is_array($obj[$arg]) or ! array_key_exists('code', $obj[$arg])) {
      $output = (string)($obj[$arg]);
    } else {
      $output = "";
    }

    /* If pretty, keep output clean by only printing RSpec for
       aggregates which have a slice (ie code!=12 or code !==2).
       Also don't print if no code was returned (ie code!=-1) because
       something catastrophic happened.
       -- unless there are no aggregates with resources, in which case
       we print the error.
    */
    // error_log("Aggregate listresources code is " . $code); 
    if (!(($code == -1 or $code == 12 or $code == 2) and $pretty)){
      if ($pretty) {
	print "<div class='aggregate' id='aggT_" . $am_id . "'>Aggregate <b>".$arg_name."'s</b> Resources:</div>";
      } else {
	print "<div class='aggregate' id='aggT_" . $am_id . "'>Aggregate <b>".$arg_name."'s</b> Raw Resources:</div>";
      }
      print "<div class='resources' id='agg_" . $am_id ."'>";
      if ($code == 0){
	if ($pretty){
	  /* Parsed into a table */
	  print_rspec_pretty($xml, False, $filterToAM, $arg_urn, $am_id);
	} else {
	  //	  /* As plain XML but with newlines after each block */
	  //	  $xml = str_replace(">", ">\n", $xml);
	  print_xml($xml);
	}
      } else {
	if ($output == "") {
	  $output = "[None. Return code: " . $code . "]";
	}
	echo "<p>Returned: <i>$output</i></p>";
      }

      print "</div>\n";
    }
  }
}

// Pull expiration from manifest 
// (only for EG: from ns4:geni_sliver_info extension)
function expires_from_manifest($manifest_node, $xml)
{
  $expires = "";

  $client_id = $manifest_node['client_id'];
  $component_id = $manifest_node['component_id'];
  $component_manager_id = $manifest_node['component_manager_id'];

  $doc = new DOMDocument('1.0', 'utf-8');
  $doc->loadXML($xml);
  $nodes = $doc->getElementsByTagName('node');

  for($i = 0; $i < $nodes->length; $i++) {
    $node = $nodes->item($i);
    if ($node->getAttribute('client_id') == $client_id &&
	$node->getAttribute('component_id') == $component_id && 
	$node->getAttribute('component_manager_id') == $component_manager_id) {
      $sliver_infos = $node->getElementsByTagName('geni_sliver_info');
      if ($sliver_infos->length > 0) {
	$sliver_info = $sliver_infos->item(0);
	//	if (!$sliver_info->hasAttributes()) {
	//	  error_log("NO ATTRIBS");
	//	} else {
	//  foreach($sliver_info->attributes as $attr) {
	//	    $name = $attr->nodeName;
	//	    $value = $attr->nodeValue;
	//	    error_log("NAME " . $name . " VAL " . $value);
	//	  }
	//	}
	$expires_orig = $sliver_info->getAttribute('expiration_time');
	$expires = rfc3339Format($expires_orig);
	break;
      }
    }
  }

  //  error_log("about to return: " . $expires);
  return $expires;
}

function print_return( $obj, $topLevel ) {
  if (!$topLevel){
    print "<ul>";
  }
  $keys = array_keys( $obj );
  foreach ($keys as $key){
    $value = $obj[$key];
    if ($topLevel){
      print "<li><b>Aggregate</b>: ".$key."</li>";      
    } elseif (!is_integer($key) and !is_array($value)){
      print "<li><b>".$key."</b>: ".$value."</li>";
    } elseif (!is_integer($key)){
      print "<li><b>".$key."</b></li>";
    }
    if (is_array($value)){
//      print "This is an array!\n";
      print_return( $value, False );
    }
  }
  if (!$topLevel){
    print "</ul>";
  }
}

function get_rspec_xml( $obj, $pretty, $filterToAM ) {
  $args = array_keys( $obj );

  // How many AMs reported actual results
  $amc = 0;
  foreach ($args as $arg) {
    if (is_array($obj[$arg]) and array_key_exists('value', $obj[$arg]) and array_key_exists('code', $obj[$arg]) and is_array($obj[$arg]['code']) and array_key_exists('geni_code', $obj[$arg]['code']) and $obj[$arg]['code']['geni_code'] == 0) {
      $amc = $amc + 1;
    }
  }

  foreach ($args as $arg) {
    if (is_array($obj[$arg]) and array_key_exists('value', $obj[$arg])) {
        $xml = $obj[$arg]['value'];
    } else {
        $xml = "";
    }
    $code = -1;
    if (is_array($obj[$arg]) and array_key_exists('code', $obj[$arg]) and is_array($obj[$arg]['code']) and array_key_exists('geni_code', $obj[$arg]['code'])) {
      $code = $obj[$arg]['code']['geni_code'];
    }
    if (is_array($obj[$arg]) and array_key_exists('output', $obj[$arg])) {
      $output = $obj[$arg]['output'];
    } else if (! is_array($obj[$arg]) or ! array_key_exists('code', $obj[$arg])) {
      $output = (string)($obj[$arg]);
    } else {
      $output = "";
    }

    /* If pretty, keep output clean by only printing RSpec for
       aggregates which have a slice (ie code!=12 or code !==2).
       Also don't print if no code was returned (ie code!=-1) because
       something catastrophic happened.
       -- unless there are no aggregates with resources, in which case
       we print the error.
    */
    // error_log("Aggregate listresources code is " . $code); 
    if (!(($code == -1 or $code == 12 or $code == 2) and $pretty)){ 

      if ($code == 0){
	      /* Ensure xml is in parsable format,  */
        $xml = str_replace(array("\n", "\r", "\t"), '', $xml);
        $xml = trim(str_replace('"', "'", $xml));

	  	  return $xml;
      } else {
		    return "null";
      }

    }
  }
}

?>
