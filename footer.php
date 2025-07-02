<?php
if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>
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
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.sidenav');
    var instances = M.Sidenav.init(elems, options);
  });

  // Or with jQuery

  $(document).ready(function(){
    $('.sidenav').sidenav();
  });
  
  </script>
  
  <script>
  $(".dropdown-trigger").dropdown();
  </script>
  
<script type="text/javascript" src="js/materialize.min.js"></script>