<?php 
/**  
*  Recsys-frontend, Copyright (c) 2013, Simon Dooms
*  http://github.com/sidooms/recsys-frontend
*  MIT License
*/
    require_once "general.php"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $project_title; ?></title>
    <!-- JQuery -->
    <script src="js/jquery-1.10.2.min.js"></script>
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="css/style-original.css" rel="stylesheet" media="screen">
    <script src="js/bootstrap.min.js"></script>    
    <!-- for the dragging of the weights on the stats page -->
    <link href="css/dragdealer.css" rel="stylesheet" media="screen">
    <script type="text/javascript" src="js/dragdealer.js"></script>
    <!-- for the nice math formula visualization on the hybrid explanation-->
    <script type="text/javascript"  src="js/MathJax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"> </script>   
    <!-- for the fancy circle drawn by D3 -->
    <script src="js/d3.v3.min.js" charset="utf-8"></script>  
    <!-- include custom javascript code -->
    <script src="js/index.js"></script>    
</head>
<body class='thebackground'>
    
     
<div class="container">

  <div class='row-fluid'>
    <div class='span11 offset1'>
        <div class="page-header">
    <h1><?php echo $project_title; ?> <small> <?php echo $project_subtitle; ?></small></h1>
    </div>
    </div>
  </div>
  <div class="row"> 
    <div class="span11">
      <!--Body content-->
    <div class="navbar">
      <div class="navbar-inner">
              
        <a class="brand" href='?'><?php echo $project_title; ?></a>
           
        <!-- -->
        <ul class="nav">
          <li class='tab-header active algo-header' id='tab-movies'><a href="#" onclick='show_tabs("movies")'>Movies</a></li>
           <li class='dropdown tab-header' id='header-recommendationalgos'>
            <a class='dropdown-toggle' href="#" data-toggle="dropdown" id='tab-recommendationalgos' >Recommendation Algos <b class="caret"></b></a>
                <ul class="dropdown-menu" aria-labelledby="tab-recommendationalgos">
          <?php
          
            $sql = "SELECT DISTINCT algorithm FROM recommendations";
		
            $stat = prepareStatement($sql);
            $stat->execute();
            $rows = $stat->fetchAll();
            
            foreach ($rows as $row)
            {
                $algo = $row['algorithm'];
                print "<li id='tab-".$algo."' class='algo-header'><a href='#' onclick='show_rec_tabs(\"$algo\")'>$algo</a></li>";
            }
          ?>         
                   
                </ul>
            </li>
            <li class='tab-header algo-header' id='tab-hybrid'><a href="#" onclick='show_tabs("hybrid")'>Hybrid</a></li>
            <li class='tab-header algo-header' id='tab-stats'><a href="#" onclick='show_tabs("stats")'>Stats</a></li>
             <li class="divider-vertical"></li>
            <li class='dropdown'>
            <a class='dropdown-toggle' href="#" data-toggle="dropdown" id='tab-action' ><i class='icon-wrench'></i> <b class="caret"></b> </a>
                <ul class="dropdown-menu" aria-labelledby="tab-action">
 <li><a tabindex="1" href="#" onclick='start_reccalc()'><i class='icon-play'></i> Calculate recommendations</a></li>
 <li><a tabindex="2" href="#" onclick='start_hybrid_calc()'><i class='icon-play'></i> Combine hybrid recommendations</a></li>
 
                </ul>
            </li>
            
        </ul>
        
      </div>
    </div>
    
      </div>
  </div>
  <div class="container">
  <div class="row">
    <div class="span11">
        
        <div id='movies' class='tab'>    
        
        </div>
        
         <?php
          
            foreach ($rows as $row)
            {
                $algo = $row['algorithm'];
                
                print "<div id='" . $algo. "' class='tab hidden'>";
                print "</div>
                <script>
                 $(document).ready(function() {
                    var rpp_algo = get_results_per_page('".$algo."');
                    setTimeout(function(){get_movies(1,5,'" . $algo . "')}, 1500);
                 });
                </script>
                ";
            }
          ?>
        
        <div id='hybrid' class='tab hidden'>    
            
        </div>
        
        <div id='stats' class='tab hidden'>    
            Loading stats...
        </div>        
          
    </div>
   </div>
</div>
</div>
</body>
</html>
