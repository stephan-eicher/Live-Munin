<?php

require_once("livemunin.php");


$services[] = 'libvirt-cputime';
$services[] = 'iostat';
$services[] = 'forks';
$services[] = 'load';
$services[] = 'open_inodes';


if( isset( $_GET['service'] ) ) {
    $m = new LiveMunin();
    if( in_array( $_GET['service'], $services ) ) {
        echo $m->graphValues( $_GET['service'] );
    }

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
                colors: ["#4BD37F", "#184B2E", "#1B3522", "#60FF62", "#60B923"],
                lines: { 
                        show: true
                        
                    },
                points: { show: false },
                xaxis: { 
                        mode: 'time',
                        ticks: 3
                        },
                legend: { 
                    position: 'nw',
                    backgroundColor: '#092211'
                },
                grid: {
                    color: "#415645",
                    tickColor: "#09351D"
                }
        };
        
        
         function fetchGraph(service) {
              
             function onDataReceived(series) {

                 $.plot($("#" + service.replace(/\./g,'_') ), series, options);
             }
             
             $.ajax({url: "?service=" + service, method: 'GET', dataType: 'json', success: onDataReceived });
             
        }
        
        function updateServices() {
        
        <?php foreach( $services as $s ) { ?>
            fetchGraph('<?=$s?>');
        <?php } ?>
        
        }
        
        updateServices();
        
        setInterval( function() { updateServices(); }, 5000 );
        
    });
    
    </script>
    <style>
        body {
            background-color: #000;
            color: #1E6A29;
        }
        h1 {
            font-size: 15pt;
            padding: 0px;
            margin: 0px;
        }
        <?php foreach( $services as $s ) { $s = str_replace(".", "_", $s); ?>        
        #<?=$s?> {
            width: 350px;
            height: 200px;
        }
        <?php } ?>

        div.l {
            float: left;
        }
        div.c {
            clear: both;
        }
    </style>
</head>
<body>

    <?php foreach( $services as $s ) { $s = str_replace(".", "_", $s); ?>        
    <div class='l'>
        <h1><?=$s?></h1>
        <div id='<?=$s?>'></div>
    </div>
    <?php } ?>
    
</body>
</html>

