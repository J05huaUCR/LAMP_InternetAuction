<?

declare(ticks = 1);

echo "start ";

$max=10;
$child=0;

function sig_handler($signo) {
	global $child;
	
	echo "signo = ";
	echo $signo;
	
	switch ($signo) {
		case SIGCHLD:
			echo "SIGCHLD received ";
			// clean up zombies
			$pid = pcntl_waitpid(-1, $status, WNOHANG);
			$child -= 1;
		exit;
	}
}

echo "signal ";
//pcntl_signal(SIGCHLD, "sig_handler");


foreach($res as  $r){
	echo "for ";
	while ($child >= $max) {
		sleep(5); 
		echo " - sleep $child n";
		pcntl_waitpid(0,$status);
	}
	$child++;
	$pid = pcntl_fork();
	
	if ($pid==-1) {
		die("Could not fork:n");
	} elseif ($pid) {
		// we're in the parent fork, dont do anything
		echo "parent waiting";
		exit;
	} else {
		//example of what a child process could do:
		echo "child performing";
		exit;
	}
}

echo "done.";

?>