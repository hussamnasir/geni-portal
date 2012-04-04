<?php
require_once("user.php");
/* $GENI_TITLE = "GENI Portal Home"; */
/* $ACTIVE_TAB = "Home"; */
require_once("header.php");
show_header('GENI Portal Home', $TAB_HOME);
?>
<div id="home-body">
<?php
$user = geni_loadUser();
if (is_null($user)) {
  // TODO: Handle unknown state
  print "Unable to load user record.<br/>";
} else {
  if ($user->isRequested()) {
    include("home-requested.php");
  } else if ($user->isDisabled()) {
    print "User $user->eppn has been disabled.";
  } else if ($user->isActive()) {
    include("home-active.php");
    // Uncomment below if you want jquery tabs example
    //include("home-active-tabs.php");
  } else {
    // TODO: Handle unknown state
    print "Unknown account state: $user->status<br/>";
  }
}
?>
</div>
<br/>
<?php
include("footer.php");
?>
