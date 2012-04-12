<?php
//----------------------------------------------------------------------
// Copyright (c) 2012 Raytheon BBN Technologies
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

// Client-side interface to GENI Clearinghouse Slice Authority (SA)
//
// Consists of these methods:
//   get_slice_credential(slice_id, user_id)
//   slice_id <= create_slice(project_id, slice_name, urn, owner_id);
//   slice_ids <= lookup_slices(project_id);
//   slice_details <= lookup_slice(slice_id);
//   renew_slice(slice_id);

require_once('sa_constants.php');

/* Create a slice credential for given SLICE ID and user */
function get_slice_credential($sa_url, $slice_id, $user_id)
{
  $row = db_fetch_inside_private_key_cert($user_id);
  $cert = $row['certificate'];
  $message['operation'] = 'get_slice_credential';
  $message[SA_ARGUMENT::SLICE_ID] = $slice_id;
  $message[SA_ARGUMENT::EXP_CERT] = $cert;

  $result = put_message($sa_url, $message);
  return $result['slice_credential'];
}

/* Create a new slice record in database, return slice_id */
function create_slice($sa_url, $project_id, $slice_name, $owner_id)
{
  $create_slice_message['operation'] = 'create_slice';
  $create_slice_message[SA_ARGUMENT::PROJECT_ID] = $project_id;
  $create_slice_message[SA_ARGUMENT::SLICE_NAME] = $slice_name;
  $create_slice_message[SA_ARGUMENT::OWNER_ID] = $owner_id;
  $slice_id = put_message($sa_url, $create_slice_message);
  return $slice_id;
}

/* Lookup slice ids for given project */
function lookup_slices($sa_url, $project_id)
{
  $lookup_slices_message['operation'] = 'lookup_slices';
  $lookup_slices_message[SA_ARGUMENT::PROJECT_ID] = $project_id;
  $slice_ids = put_message($sa_url, $lookup_slices_message);
  return $slice_ids;
}

/* Lookup slice ids for given project and owner */
function lookup_slices_by_project_and_owner($sa_url, $project_id, $owner_id)
{
  $lookup_slices_message['operation'] = 'lookup_slices';
  $lookup_slices_message[SA_ARGUMENT::PROJECT_ID] = $project_id;
  $lookup_slices_message[SA_ARGUMENT::OWNER_ID] = $owner_id;
  $slice_ids = put_message($sa_url, $lookup_slices_message);
  return $slice_ids;
}

/* Lookup slice ids for given owner */
function lookup_slices_by_owner($sa_url, $owner_id)
{
  $lookup_slices_message['operation'] = 'lookup_slices';
  $lookup_slices_message[SA_ARGUMENT::OWNER_ID] = $owner_id;
  $slice_ids = put_message($sa_url, $lookup_slices_message);
  return $slice_ids;
}

/* lookup slices by slice name, project ID */
function lookup_slices_by_project_and_name($sa_url, $project_id, $slice_name)
{
  $lookup_slice_message['operation'] = 'lookup_slices';
  $lookup_slices_message[SA_ARGUMENT::PROJECT_ID] = $project_id;
  $lookup_slice_message[SA_ARGUMENT::SLICE_NAME] = $slice_name;
  $slice = put_message($sa_url, $lookup_slices_message);
  return $slice_ids;
}

/* lookup details of slice of given id */
// Return array(id, name, project_id, expiration, owner_id, urn)
function lookup_slice($sa_url, $slice_id)
{
  $lookup_slice_message['operation'] = 'lookup_slice';
  $lookup_slice_message[SA_ARGUMENT::SLICE_ID] = $slice_id;
  $slice = put_message($sa_url, $lookup_slice_message);
  return $slice;
}

/* Renew slice of given id */
function renew_slice($sa_url, $slice_id)
{
  $renew_slice_message['operation'] = 'renew_slice';
  $renew_slice_message[SA_ARGUMENT::SLICE_ID] = $slice_id;
  $result = put_message($sa_url, $renew_slice_message);
  return $result;
}

?>
