<?php

echo "Start ";
$pid = pcntl_fork();
echo "fork ";

if($pid) {
  // this is the parent process
  echo "parent ";
  // wait until the child has finished processing then end the script
  pcntl_waitpid($pid, $status, WUNTRACED);
  if($status > 0) {
    // an error occurred so do some processing to deal with it
  }
}
else {
  // the child process runs its stuff here
  echo "Child ";
  if($successful) {
    exit(0); // this indicates success
  } else {
    exit(1); // this indicates failure
  }
}


?>