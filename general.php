<?php
/**  
*  Recsys-frontend, Copyright (c) 2013, Simon Dooms
*  http://github.com/sidooms/recsys-frontend
*  MIT License
*/
    //read the variables from the config/settings.ini file
    $settingspath = str_replace('general.php', '', __FILE__) . 'config/';
    $ini_array = parse_ini_file($settingspath . "settings.ini", true);
	
    $DB_username =  $ini_array['database']['db_username'];
	$DB_password = $ini_array['database']['db_password'];
	$DB_DB = $ini_array['database']['db_db'];
	$DB_hostname = $ini_array['database']['db_hostname'];
	$DB_port = $ini_array['database']['db_port'];
    
    $project_title = $ini_array['general']['project_title'];
    $project_subtitle = $ini_array['general']['project_subtitle'];

		
	/*
	* Prepares the given SQL instruction and returns a $statement variable
	* bind_param() can be used to pass on some other parameters. Like
	* 	$statement->bindParam(1, $id);
	*	$statement->execute();
	*/
	function prepareStatement($sql)
	{
		global $DB_hostname;
		global $DB_DB;
		global $DB_username;
		global $DB_password;
		try {
			$dbh = new PDO("mysql:host=$DB_hostname;dbname=$DB_DB", $DB_username,$DB_password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$stat = $dbh->prepare($sql);
			return $stat;
		}catch(PDOException $e){
			echo "Could not connect to database.";
            return false;
		}
	}
	
	function execStatement($sql)
	{
		global $DB_hostname;
		global $DB_DB;
		global $DB_username;
		global $DB_password;
		try {
			$dbh = new PDO("mysql:host=$DB_hostname;dbname=$DB_DB", $DB_username,$DB_password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$stat = $dbh->prepare($sql);
			$stat->execute();
			return true;
		}catch(PDOException $e){
			echo'Could not connect to database.';
			return false;
		}
		
	}
    
    function print_movie($movieid, $title, $year, $data)
    {
        $searchstring = 'Searching moviedata...';
        
        $tab = $data['tab']; 
        
        $cleanmovieid = $movieid;
        $movieid = $tab . $movieid;
        if (!empty($data['explanation']))
            $explanationtrigger = "<a href='#'  onclick='toggle_explanation(this, event,\"$movieid\")'>Show explanation</a>";
        else
            $explanationtrigger = '';
       
        
        ?>
        <script>
            $(function() {
                toggle_extra_movie_data("<?php print $movieid; ?>","<?php print $title; ?>","<?php print $year; ?>");
            });
        </script>
    <div class='well well-small' id='movie-<?php print $movieid; ?>' onclick='toggle_extra_movie_data("<?php print $movieid; ?>","<?php print $title; ?>","<?php print $year; ?>")'>
   
        
                <article>
                    <header>
                        <h4><?php print $title; ?> (<?php print $year; ?>)
                        </h4> 
                          
                    </header>
                     
                   <div id='extramoviedata-<?php print $movieid; ?>'>
                         <div class='hidden searched'>false</div>
                         <div class='row-fluid'>
                         <div class="span12">
                            <div class='span2'>
                                <img id='poster-<?php print $movieid; ?>' src='http://placehold.it/113x150' class='img-polaroid poster'>
                            </div>
                            <div class='span8'>
     <?php if (!empty ($data['recvalue'])){ ?>
                                <div class='row-fluid'>
                                    <div class='span2 ' >
                                       <strong>Rec value:</strong>
                                    </div>
                                    <div class='span10'><?php print $data['recvalue']; ?> (<?php print $data['algo']; ?>) <?php print $explanationtrigger; ?>    </div>
                                 </div>
    <?php if (!empty ($data['explanation'])){ ?>
                                 <div class='row-fluid hidden' id='explanation-<?php print $movieid; ?>'>
                                    <div class='span2 ' >
                                       <strong></strong>
                                    </div>
                                    <div class='span10'><?php print str_replace("\n",'<br>',$data['explanation']); ?></div>
                                 </div>
    <?php } ?> 
    <?php } ?>                             
                                <div class='row-fluid'>
                                    <div class='span2' >
                                       <strong>Imdb:</strong>
                                    </div>
                                    <div class='span10' id='imdbrating-<?php print $movieid; ?>'></div>
                                 </div>
                                 <div class='row-fluid'>
                                    <div class='span2'>
                                       <strong> Director: </strong>
                                    </div>
                                    <div class='span10' id='director-<?php print $movieid; ?>'></div>
                                 </div>
                                 <div class='row-fluid'>
                                    <div class='span2'>
                                        <strong> Cast:</strong>
                                    </div>
                                    <div class='span10' id='cast-<?php print $movieid; ?>'><?php print $searchstring; ?></div>
                                 </div>
                                 <div class='row-fluid'>
                                    <div class='span2'>
                                        <strong> Genre:</strong>
                                    </div>
                                    <div class='span10' id='genre-<?php print $movieid; ?>'></div>
                                 </div>
                                 <div class='row-fluid'>
                                    <div class='span2'>
                                        <strong> Runtime:</strong>
                                    </div>
                                    <div class='span10' id='runtime-<?php print $movieid; ?>'></div>
                                 </div>
                                 <div class='row-fluid'>
                                    <div class='span2'>
                                       <strong>  Plot:</strong>
                                    </div>
                                    <div class='span10' id='plot-<?php print $movieid; ?>'></div>
                                 </div>
                            </div>
                            <div class='span2'>
                            <?php 
                                $hide_not_liked_status = '';
                                $hide_rating_buttons = '';
                                $hide_liked_status = '';
                                $hide_relevance_buttons = '';
                                $hide_goodrec_status = '';
                                $hide_badrec_status = '';
                                if (!$data['rated']){  
                                    //if movie has NOT been rated yet
                                    $hide_not_liked_status = 'hide';
                                    $hide_liked_status = 'hide';
                                }else{ 
                                    //if movie has been rated
                                    $hide_rating_buttons = 'hide';
                                    if ($data['rating'] == 10){ 
                                        //positive rating!
                                        $hide_not_liked_status = 'hide';
                                    }elseif ($data['rating'] == 1){    
                                        //negative rating
                                        $hide_liked_status = 'hide';
                                    }
                                }
                                if ($data['type'] == 'rec' || $data['type'] == 'hybrid' ){
                                    if (!$data['relevancefeedback']){
                                        //if NO relevance feedback has been given
                                        $hide_goodrec_status = 'hide';
                                        $hide_badrec_status = 'hide';
                                    }else{
                                        //relevance feedback has been given
                                        $hide_relevance_buttons = 'hide';
                                        if ($data['feedback'] == '5'){
                                            //good feedback
                                             $hide_badrec_status = 'hide';
                                        }else if ($data['feedback'] == '1'){
                                            //bad feedback
                                            $hide_goodrec_status = 'hide';
                                        }
                                    }
                                }else{
                                    //don't show relevance feedback buttons
                                    $hide_relevance_buttons = 'hide';
                                    $hide_goodrec_status = 'hide';
                                    $hide_badrec_status = 'hide';
                                }
                            ?>
                                <!-- movie rating buttons -->
                                
                                                             <center id='liked-status-<?php print $movieid; ?>' class='<?php print $hide_liked_status; ?>'><span>Liked </span>  <a href='#' onclick='cancel_rating("<?php print $movieid; ?>",event)'>(Cancel)</a></center>
                            
                                <center id='not-liked-status-<?php print $movieid; ?>' class='<?php print $hide_not_liked_status; ?>'><span>Didn't Like</span> <a href='#' onclick='cancel_rating("<?php print $movieid; ?>", event)'>(Cancel)</a></center>      
                             
                                                                 <button class='btn btn-block pull-right btn-success <?php print $hide_rating_buttons; ?>' id='likebtn-<?php print $movieid; ?>' onclick='rate_movie("<?php print $movieid; ?>", "10", event)'>Like</button>
                                    <button class='btn btn-block pull-right btn-danger <?php print $hide_rating_buttons; ?>' id='dislikebtn-<?php print $movieid; ?>' onclick='rate_movie("<?php print $movieid; ?>", "1", event)'> Don't like</button> 
                            
                            </div>
                            </div>
                    </div> <!-- extra movie data div -->
                </article>
               </div>               
       <?php
               
               
    }
    
    function print_paging($unique, $page, $movies_per_page,$tot_movies, $dive)
    {
        $num_buttons = 5;
        $disabled_prev = "";
        $disabled_next = "";
        $max_page = ceil($tot_movies / $movies_per_page) ;
        
        if ($page <= 1){
            $disabled_prev = "disabled";
        }
        if ($page >= ceil($tot_movies / $movies_per_page)){
            $disabled_next = "disabled";
        }
        ?>
        <div class="pagination  pagination-centered">
          <ul>          
            <li class='<?php echo $unique; ?> pager-button <?php echo $disabled_prev;?>'><a href="#" >Home</a></li>
            <li class='<?php echo $unique; ?> pager-button <?php echo $disabled_prev;?>'><a href="#" >Prev</a></li>
            <?php
                //generate a number of buttons
                
                $stop = $page ;                
                while ($stop % $num_buttons != 0)
                    $stop += 1;
                $start = $stop - $num_buttons + 1;
                
                
                for ($i = $start; $i <= $stop ; $i++){
                    if ($i <= 0)
                        continue;
                    if ($i > $max_page)
                        continue;
                    if ($i != $page)
                        print "<li class='" . $unique . " pager-button'><a href='#'>$i</a></li>";
                    else
                        print "<li class='" . $unique . " active pager-button'><a href='#'>$i</a></li>";
                }
                
                
                $selects = array(5,10,50,100);
            ?>
            <li class='<?php echo $unique; ?> pager-button <?php echo $disabled_next;?>'><a href="#" >Next</a></li>
            <li class='<?php echo $unique; ?> pager-button <?php echo $disabled_next;?>'><a href="#" >End</a></li>
          </ul>
          
            <span class="input-append">
                <select id='results-per-<?php echo $unique; ?>' class='resultsperpage' onchange="change_results_per_page(this,'<?php echo $dive; ?>')">
                <?php
                    foreach ($selects as $key)
                    {
                        if ($movies_per_page == $key)
                            $selected = 'selected="selected"';
                        else
                            $selected = '';
                        echo '<option '.$selected.' >' . $key . '</option>';
                    }
                ?>
                </select>
                <span class="add-on">results per page</span>
            </span>
        </div>
        
        
        <script>
            $(document).ready(function() { 
                //remove previously added events
                $("li.pager-button.<?php echo $unique; ?>").unbind();
                //add pager button events
                $("li.pager-button.<?php echo $unique; ?>").click(function(e) {
                    if (!$(this).hasClass('disabled')){
                        //get the page from clicked button                    
                        var goal_page = $(e.target).text();
                        //get the movies per page from button group
                        var goal_movies_per_page = get_results_per_page('<?php echo $unique; ?>');
                        var current_page = parseInt($("li.pager-button.<?php echo $unique; ?>.active:first").text());
                        if (goal_page.toLowerCase() == 'prev'){
                            goal_page = parseInt(current_page - 1);
                        }else if (goal_page.toLowerCase() == 'next'){
                            goal_page = parseInt(current_page + 1);
                        }else if (goal_page.toLowerCase() == 'home' ){
                            goal_page = 1;
                        }else if (goal_page.toLowerCase() == "end" ){
                            goal_page = <?php echo $max_page; ?>;
                        }
                        if (current_page != goal_page){   
                            get_movies(goal_page, goal_movies_per_page, '<?php echo $dive; ?>');
                        }
                    }
                    });
            });
         </script>
    
        
        <?php
        
        
        
    }
	
    function get_number_of_movies($search)
    {
        $SQL_FILTER = '';
        if ($search != ''){
             $SQL_FILTER = "WHERE  `title` COLLATE UTF8_GENERAL_CI LIKE '%" . $search . "%' ";
        }
        
        $sql = "SELECT COUNT(*) from movies " . $SQL_FILTER;
        $stat = prepareStatement($sql);
        $stat->execute();
        $res = $stat->fetchAll();
    
        return $res[0][0] ;
    }
    
    function get_number_of_recommendations($algo)
    {
        $sql = "SELECT COUNT(*) FROM movies as m INNER JOIN recommendations as r ON m.movieid=r.movieid WHERE userid=999999 AND r.algorithm=?";
        $stat = prepareStatement($sql);
        $stat->bindParam(1, $algo);
        $stat->execute();
        $res = $stat->fetchAll();
    
        return $res[0][0] ;
    }
    
    function get_number_of_hybridrecommendations($user)
    {
        $sql = "SELECT COUNT(*) FROM h_recommendations WHERE userid=?";
        $stat = prepareStatement($sql);
        $stat->bindParam(1, $user);
        $stat->execute();
        $res = $stat->fetchAll();
    
        return $res[0][0] ;
    }
    
     function valid_page($page, $movies_per_page, $tot_movies)
    {
        if ($movies_per_page <= 0)
            return false;
            
        if ($page > 0 and $page <= ceil($tot_movies / $movies_per_page))
            return true;
        else
            return false;
    }
    
	
?>
