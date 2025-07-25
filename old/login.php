<!DOCTYPE html>
<html>
<head>
<!-- https://materializecss.com/getting-started.html -->
<!--Import Google Icon Font-->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!--Import materialize.css-->
<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<!--Let browser know website is optimized for mobile-->
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body class="grey lighten-3">
<nav>
  <div class="nav-wrapper blue"> <img src="images/wrlc-logo-white.png" height="50px" style="margin:5px 0 0 20px; position:absolute;"> <a href="#" class="brand-logo" style="margin-left:90px;">SCF Processing</a> 

  </div>
</nav>
<div style="margin: auto;
  width: 50%;
  
  padding: 10px;
  ">
  <div class="row">
    <div class="col s12 m12">
      <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title">Staff Login</span>
          <?php
					
					if ($_GET['login'] =='false') echo '<h3 class="card-title" style="color:#ee6e73;">Login failed.  Please try again.</h3>';
					
					?>
          <div class="row"> 
            
            <!-- <form action="https://docs.google.com/forms/d/e/1FAIpQLSelZJBA1YQg4vJx6OTHotkmQ-TCZx24q2peSQ_BoMqKllqfDQ/formResponse" method="GET"> -->
            
            <form action="create_session.php" class="col s12" method="post">
              <div class="row">
                <div class="input-field col s10"> <i class="material-icons prefix">account_circle</i>
                  <select name="username">
                    <option value="" disabled selected>Select Name</option>
                    <?php
// Parsing this spreadsheet
$sheetid = '1Q3PcpfXhsEp4p38aV6n2uDfeZYNMBpp2ESIgFNiZixo';
$url = 'https://spreadsheets.google.com/feeds/list/'.$sheetid.'/1/public/values?alt=json';
$file= file_get_contents($url);

$json = json_decode($file);
$rows = $json->{'feed'}->{'entry'};

foreach ($rows as $row) {
    $name = $row->{'gsx$name'}->{'$t'};
	
	echo '<option value="'.$name.'">'.$name.'</option>';
	
}

?>
                  </select>
                  <label>Select Name</label>
                </div>
              </div>
              <div class="row">
                <div class="input-field col s10"> <i class="material-icons prefix">vpn_key</i>
                  <input id="password" name="password" type="password" class="validate">
                  <label for="password">Password</label>
                </div>
              </div>
              <button class="btn waves-effect waves-light right" type="submit" >Login <i class="material-icons right">lock</i> </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading--> 

<!--  Scripts--> 
<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script> 
<script src="js/materialize.js"></script> 
<script src="js/init.js"></script> 
<script>document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('select');
    var instances = M.FormSelect.init(elems, options);
  });

  // Or with jQuery

  $(document).ready(function(){
    $('select').formSelect();
  });
  
  </script> 
<script type="text/javascript" src="js/materialize.min.js"></script>
</body>
</html>