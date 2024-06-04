<?php
	include ('./conf/config.php');
	session_start();
   
	if (!isset($_SESSION['auth_token'])) {
	    header('Location: login.php');
	    exit();
	}
	
	//prepare mysql db connection
	try {
    $conn = new PDO("mysql:host=$server;dbname=$db", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Failed " . $e->getMessage();
}
	
	//Prepare query
	function prepareQuery($connection,$sql, $param, $value)
	{
	$result_data = "";	
	$exeute_sql = $connection->prepare($sql); 
	if ($param != null && $value != null){
	$exeute_sql->bindParam($param, $value);
	}
	$exeute_sql->execute();	
	
	return $exeute_sql;
	}
	
	
	// Get user type
	function getUserType($connection)
	{
	$usertype_data = "";	
	$usertype_sql = "
	SELECT CASE WHEN r.type = 3 THEN 'Admin' ELSE 'User' END AS role
	FROM users u
	INNER JOIN role r ON r.roleid = u.roleid
	WHERE u.username = :username"; 
	$user_sql = prepareQuery($connection,$usertype_sql,':username', $_SESSION['username']);
	
	if ($user_sql->rowCount() > 0) {
	    $row = $user_sql->fetch(); 
	    $usertype_data = $row['role'];
	}
	return 	$usertype_data;
	}
	
	
	//Get hostgoup
	function getHostGroup($connection,$usertype)
	{
		if ($usertype == "Admin") {
	 $sysname_sql = "SELECT * FROM `hstgrp`";
	 $system_sql = prepareQuery($connection,$sysname_sql,null, null);
	 } else {
	$sysname_sql = "SELECT hg.name
	    FROM users u
	    INNER JOIN users_groups ug ON u.userid = ug.userid
	    INNER JOIN usrgrp g ON ug.usrgrpid = g.usrgrpid
	    INNER JOIN rights r ON g.usrgrpid = r.groupid
	    INNER JOIN hstgrp hg ON r.id = hg.groupid
	    WHERE u.username = :username AND r.id = hg.groupid";
	 $system_sql = prepareQuery($connection,$sysname_sql,':username', $_SESSION['username']);	 
	 }
	 
	 return $system_sql;
	}
	
	
	$usertype = getUserType($conn);
	$sysname_sql = getHostGroup($conn, $usertype);
	$system_name = $sysname_sql->fetchAll();
	
	//get host name
	function getHost($connection,$sysnameValue)
	{
		$nodename_sql = "
            SELECT h.name
            FROM hosts h 
            INNER JOIN hosts_groups hg ON h.hostid = hg.hostid
            INNER JOIN hstgrp g ON hg.groupid = g.groupid
            WHERE g.name = :name AND h.status = 0";
		$host_sql = prepareQuery($connection,$nodename_sql,':name', $sysnameValue);	 
		return $host_sql;
	}
	
	//get job name
	function getJob($connection, $jobnameValue)
	{
		$jobname_sql = "
            SELECT DISTINCT jnc.jobnet_id, jnc.jobnet_name
            FROM ja_jobnet_control_table jnc
            INNER JOIN ja_icon_job_table jij ON jnc.jobnet_id = jij.jobnet_id
            WHERE jij.host_name = :host_name AND jnc.valid_flag = 1 AND jnc.multiple_start_up = 1";
		$job_sql = prepareQuery($connection,$jobname_sql,':host_name', $jobnameValue);
		return $job_sql;
	}
	
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle AJAX requests
    if (isset($_POST['sysnameValue'])) {
        // Fetch nodes based on system name
        $sysnameValue = $_POST['sysnameValue'];
       
        $nodename_sql = getHost($conn,$sysnameValue);
        $node_name = $nodename_sql->fetchAll();

        foreach($node_name as $row){ 
            $nodename = $row['name'];
            echo '<option value="'.$nodename.'">'.$nodename.'</option>';
        }
    }
    elseif (isset($_POST['jobnameValue'])) {
        // Fetch jobs based on node name
        $jobnameValue = $_POST['jobnameValue'];
       

        $jobname_sql = getJob($conn,$jobnameValue);
        $job_name = $jobname_sql->fetchAll();
		
		
        foreach($job_name as $row){ 
            $jobname = $row['jobnet_name'];
			$jobid = $row['jobnet_id'];
            echo '<option value="'.$jobid.'">'.$jobname.'</option>';
        }
    }
}

//execute job 
function executeJob($script, $server, $username, $password, $jobnet, $argus) {
    $escapedScript = escapeshellcmd($script);
    $escapedServer = escapeshellarg($server);
    $escapedUsername = escapeshellarg($username);
    $escapedPassword = escapeshellarg($password);
    $escapedJobnet = escapeshellarg($jobnet);
    $export_command = "export $jobnet='$argus'";
    $command = "$export_command && $escapedScript -z $escapedServer -U $escapedUsername -P $escapedPassword -j $escapedJobnet -E $escapedJobnet 2>&1";
    $output = shell_exec($command);
    if (preg_match('/.*\[.*\]\s*:\s*.*\[(.*)\]/', $output, $matches)) {
        return $matches[1];
    } else {
        return null;
    }
}

 //get registry number
 function getRegistryNumber($module, $server, $user, $password)
 {
	 $registry_num = null;
	 
	 if(array_key_exists('execBtn', $_POST)) { 
 	$registry_num = null;
 if($_SERVER["REQUEST_METHOD"] == "POST"){
 if (isset($_POST['Menuname']))
 {
	 $jobName = $_POST['Menuname'];
	if (isset($_POST['Argument'])) {
	 $argument = $_POST['Argument'];
         
	  $registry_num = executeJob($module, $server, $user, $password, $jobName, $argument);
	}
 }
 }
 }
 return $registry_num;
 }
 
 //get job result
 function getJobExecResult($module, $server, $user, $password, $registry_number, $conn){
    $jobnet_status = null;
    $job_status = null;
    $exec_result = null;

    #prepare to execute module
	$escapedModule = escapeshellcmd($module);
    $escapedServer = escapeshellarg($server);
    $escapedUsername = escapeshellarg($user);
    $escapedPassword = escapeshellarg($password);
	$escapedRegistrynumber = escapeshellarg($registry_number);
    $command = "$escapedModule -z $escapedServer -U $escapedUsername -P $escapedPassword -r $escapedRegistrynumber 2>&1";
   
    #job checking before the final result
    do {
        $exec_result = shell_exec($command);
        $exec_result = preg_replace("/^\s*\R/", "", $exec_result, 1);
        $lines = explode("\n", $exec_result);
        foreach ($lines as $line) {
            list($key, $value) = explode(":", $line);
            $key = trim($key);
            $value = trim($value);
            if ($key == "Status of a jobnet") {
                $jobnet_status = $value;
            }
            if ($key == "Status of a job") {
                $job_status = $value;
            }
            if ($value == "Error") {
                return $exec_result;
            }
            if ($key == "Time of a schedule" || $key == "Time of a start" || $key == "Time of a end"){
                $dateTime = DateTime::createFromFormat('YmdHis', $value);
                if ($dateTime !== false) {
                    $formattedDate = $dateTime->format('Y-m-d H:i:s');
                    $exec_result = str_replace($value, $formattedDate, $exec_result);
                }
            }
        }
        if ($job_status == "ERROR" & $jobnet_status == 'RUN'){
            $jobarg_msg_sql = $conn->prepare("
               SELECT after_value
               FROM ja_run_value_after_table
               WHERE inner_jobnet_id = ? AND value_name = 'JOBARG_MESSAGE'
           ");
   
           if ($jobarg_msg_sql === false) {
               die("Error preparing the statement: " . $conn->error);
            }
            $jobarg_msg_sql->bind_param("s", $registry_number);
            $jobarg_msg_sql->execute();
            $result = $jobarg_msg_sql->get_result();
            $jobarg_msg = $result->fetch_all(MYSQLI_ASSOC);
            $jobarg_message = 'Job Argument Message     : ' . $jobarg_msg[0]['after_value'];
            $exec_result .= $jobarg_message;
            $jobarg_msg_sql->close();
            break;
       }
        if ($jobnet_status != 'END') {
	    sleep(3);
        }  
    } while ($jobnet_status != 'END');
    //$conn->close();
    return $exec_result;
}
	//display job execute output
if($_SERVER["REQUEST_METHOD"] == "POST"){
	 if (isset($_POST['execBtn'])) {

$registry_num = getRegistryNumber($exec_module, $server, $_SESSION['username'], $_SESSION['password']);
sleep(5);
$jobExecResult = getJobExecResult($get_module, $server, $_SESSION['username'], $_SESSION['password'], $registry_num, $conn);
$jobExecResult =  "<pre>$jobExecResult</pre>";
 
	 }
 }

?>

<?php
// Start output buffering
ob_start();
// Include the HTML file
include './jobexecution_form.html';
// Get the content of the buffer
$form_content = ob_get_clean();
// Output the HTML content
echo $form_content;
?>
