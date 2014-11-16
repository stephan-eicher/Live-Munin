<?php

require_once("../livemunin.php");

$m = new LiveMunin();

if (isset( $_GET['action'])) {
    
    switch ($_GET['action']) {
        case 'list':
            echo json_encode($m->services());
            break;
        
        case 'data':
            if( isset( $_GET['service'] ) ) {
                echo $m->graphValues( str_replace('_','-', $_GET['service']) );
            }
            break;
    }
    
    die();
    
}

?>
<html>
<head>
    <title>LiveMunin</title>
    
    <script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
    <link href="jquery-ui-1.11.2/jquery-ui.css" rel="stylesheet">
    <script src="jquery-ui-1.11.2/jquery-ui.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.stack.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.time.js"></script>
    <script language='javascript'>
        
    var interval = [];

    $(document).ready(function() {
        
        var options = {
            lines: { show: true },
            points: { show: false },
            xaxis: { mode: 'time' }
        };

         function fetchGraph() {

             $.ajax({
                 url: "?action=data&service=" + $('select[name="service"]').val(),
                 method: 'GET',
                 dataType: 'json',
                 success: function(series) {
                     $.plot($("#container"), series, options);
                }
            });

        }
        
        function updateInterval() {
            for(i = 0; i < interval.length; i++) {
                clearInterval(interval.pop());
            }
            fetchGraph();
            interval.push(setInterval( function() { fetchGraph(); }, $('select[name="interval"]').val()));
        }
        
        $('select[name="interval"]').selectmenu({
            width: '200px',
            change: function() {
                updateInterval();
            }
        });
        
        $('select[name="service"]').selectmenu({
            width: '200px',
            change: function () {
                updateInterval();
            }
        });
        
        $.ajax({
            url: "?action=list",
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                
                $('select[name="service"]').selectmenu('destroy');
                
                $.each(data, function(index, value) {
                    $('select[name="service"]').append(new Option(value, value));
                });
                
                $('select[name="service"]').selectmenu({
                    width: '200px',
                    select: function () {
                        updateInterval(); 
                    }
                });
                
                updateInterval();
                
            }
            
        }); 
        
    });
    
    </script>
    <style>
        #container {
            width: 800px;
            height: 500px;
        }
    </style>
</head>
<body>
    <select name="service">
        
    </select>
    <select name="interval">
        <option value="1000">1 sec.</option>
        <option value="2000">2 sec.</option>
        <option value="3000">3 sec.</option>
        <option value="4000">4 sec.</option>
        <option value="5000">5 sec.</option>
        <option value="10000" selected>10 sec.</option>
    </select>
    <div id="container">
        
    </div>
</body>
</html>

