<?php
//----------------------------------------------------------------------
// Copyright (c) 2011-2015 Raytheon BBN Technologies
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

// Make sure the URL is trimmed, is a valid URL and doesn't contain file:// protocol
// If the file_get_contents returns FALSE, return a 404 error

$url = $_GET['url'];
$trimmed_url = trim($url);

$has_error = false;

// Check that this has a URL protocol://path format
$url_pieces = split("\:\/\/", $trimmed_url);
if(count($url_pieces) != 2) {
  // Return 400: BAD REQUEST
  header("HTTP/1.0 400 Bad Request");
  return;
}

// Check that the protocol is not file
if(strtolower($url_pieces[0]) == "file") {
  // Return 400: BAD REQUEST
  header("HTTP/1.0 400 Bad Request");
  return;
}


$result = file_get_contents($trimmed_url);

if ($result == FALSE) {
  // Return 404: NOT FOUND
  header('HTTP/1.1 404 Not Found');
  return;
} else {
  // Return the contents of the file itself
  print $result;
}

?>
