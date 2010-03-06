<?php

require_once("livemunin.php");

if( isset( $_GET['service'] ) ) {
    $m = new LiveMunin();
    $_GET['service'] = 'libvirt_cputime';
    echo $m->graphValues( str_replace('_','-', $_GET['service']) );
    die();
}

?>
<html>
<head>
    <title>LiveMunin</title>
    
    <script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.stack.js"></script>
    <script language='javascript'>

    $(document).ready(function() {
        
        var options = {
                lines: { show: true },
                points: { show: false },
                xaxis: { mode: 'time' }
        };
        
        
         function fetchGraph(service) {
              
             function onDataReceived(series) {
                 $.plot($("#" + service), series, options);
             }
             
             $.ajax({url: "?service=" + service, method: 'GET', dataType: 'json', success: onDataReceived });
             
        }
        
        fetchGraph('libvirt_cputime');
        
        setInterval( function() { fetchGraph('libvirt_cputime'); }, 4000 );
        
    });
    
    </script>
    <style>
        #libvirt_cputime {
            width: 800px;
            height: 500px;
        }
    </style>
</head>
<body>
    <h1>libvirt_cputime</h1>
    <div id='libvirt_cputime'></div>
</body>
</html>

