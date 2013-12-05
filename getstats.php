<?php
/**  
*  Recsys-frontend, Copyright (c) 2013, Simon Dooms
*  http://github.com/sidooms/recsys-frontend
*  MIT License
*/
    require_once "general.php";
    require_once "dbobject.php";
    
    $user=999999;
    
    $db = new Db();
    $algos = array();
    $counts = array();
            
    $db->getAlgorithmWeights($algos, $counts, $user);
    
    ?>
    
    <div class="tabbable"> <!-- Only required for left/right tabs -->
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab1" data-toggle="tab">Algorithm weights</a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="tab1">
      <?php
            print '<center><h3>Recommendation algorithm weights visualization</h3></center>';
            show_pie_chart($algos);
            show_algo_weight_handles($algos);
      ?>
    </div>
  </div>
</div>

    
   
    
    <?php
    
    
    function show_algo_weight_handles($algos)
    {
        print '<div class="container span10">';
        foreach ($algos as $key => $value)
        {
            print_algo_weight_handle($key, $value);
        }
        print '</div>';
    }
    
    function print_algo_weight_handle($key, $value)
    {
        ?>
        
            <div class='row'>
                <div class='span4 centerlabel'>
                    <h5><?php echo $key; ?></h5>
                </div>
                <div class='span4'>
                    <div id="algo-slider-<?php echo $key; ?>" class="dragdealer rounded-cornered" style='background:white;width:400px;'>
                        <div id='algo-slider-handle-<?php echo $key; ?>' class="red-bar handle">drag me</div>
                    </div>
                    <script>
                      
                        init_algo_weights['<?php echo $key; ?>'] = <?php echo $value; ?>;

                    </script>
                </div>
            </div>
                
        <?php
    }
    
    function show_pie_chart($algos)
    {      
        print '<script>var piechart_data = [];';
        foreach ($algos as $algo => $weight)
        {
           print "obj = new Object(); obj.algo = \"$algo\"; obj.weight = $weight;
                   piechart_data.push(obj);";
        }
        print '</script>';
        
        include 'pie_chart.html';
    }    
?>
