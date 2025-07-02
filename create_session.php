<?php 
session_start();

include('connect.php');
 
 if ( ! empty( $_POST ) ) {
      if ( isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
       
	   //if form is submitted, define the username and password
	 //  $passusername = 'wrlc';
	 
	 
	 /////Get Staff information	  
$sql = "SELECT * FROM Staff";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{				
					
		if ($row['name'] == $_POST['username'] AND $row['pw'] == $_POST['password']) {
	
            $staffkey = $row['staffkey'];
            $username = $row['name'];
	$passpassword = $row['pw'];
	$admin = $row['admin'];
	$temp = $row['temp'];
	
		}
	
}			
	mysqli_close($conn);	 
	 
	 
	 
	  // $passpassword = 'wrlc';
	   
	 //  $username = $_POST['username'];
	   
	   // Verify username and password and set $_SESSION
	   if (isset($_POST['username']) and $passpassword == $_POST['password'] ) {
	   
        $_SESSION['staffkey'] = $staffkey;
        $_SESSION['user_id'] = $username;
	   $_SESSION['admin'] = $admin;
	   $_SESSION['temp'] = $temp;
	   $_SESSION['start'] = time(); // Taking now logged in time.
            // Ending a session in 30 minutes from the starting time.
            $_SESSION['expire'] = $_SESSION['start'] + 986400;
	   header("Location: index.php");
	   }
	  	
    	else 
		
		 header("Location: login.php?login=false");
    	
    }
}
?>