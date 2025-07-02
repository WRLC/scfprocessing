<?php 
session_start();
 
 if ( ! empty( $_POST ) ) {
      if ( isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
       
	   //if form is submitted, define the username and password
	 //  $passusername = 'wrlc';
	 
	 
	 
	 
	 
	 
// Parsing this spreadsheet
$sheetid = '1Q3PcpfXhsEp4p38aV6n2uDfeZYNMBpp2ESIgFNiZixo';
$url = 'https://spreadsheets.google.com/feeds/list/'.$sheetid.'/1/public/values?alt=json';
$file= file_get_contents($url);

$json = json_decode($file);
$rows = $json->{'feed'}->{'entry'};

foreach ($rows as $row) {
    $name = $row->{'gsx$name'}->{'$t'};
	$pw = $row->{'gsx$pw'}->{'$t'};
	
	
	if ($name == $_POST['username'] AND $pw == $_POST['password']) {
	
	$username = $name;
	$passpassword = $pw;
	}
	
}


	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	  // $passpassword = 'wrlc';
	   
	 //  $username = $_POST['username'];
	   
	   // Verify username and password and set $_SESSION
	   if (isset($_POST['username']) and $passpassword == $_POST['password'] ) {
	   
	   $_SESSION['user_id'] = $username;
	   $_SESSION['start'] = time(); // Taking now logged in time.
            // Ending a session in 30 minutes from the starting time.
            $_SESSION['expire'] = $_SESSION['start'] + (60 * 60 * 24);
	   header("Location: index.php");
	   }
	  	
    	else 
		
		 header("Location: login.php?login=false");
    	
    }
}
?>