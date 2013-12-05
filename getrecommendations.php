<?php
/**  
*  Recsys-frontend, Copyright (c) 2013, Simon Dooms
*  http://github.com/sidooms/recsys-frontend
*  MIT License
*/
    require_once "general.php";
    
    $user = 999999;
    
    if (empty($_REQUEST['p']) or empty($_REQUEST['ppage']) or empty($_REQUEST['a']))
        exit;

    $page = $_REQUEST['p'];
    $recs_per_page = $_REQUEST['ppage'];
    $algo = $_REQUEST['a'];
    $tot_recs = get_number_of_recommendations($algo);
    if (!empty($_REQUEST['show_rated']))
        $show_rated = $_REQUEST['show_rated'];
    else
        $show_rated = 'yes';
    
    
    if (!valid_page($page, $recs_per_page, $tot_recs))
    {
        print 'Invalid page request: ' . $page;
        exit;
    }   
    
    if ($show_rated == 'yes'){
        $class_show_rated = '';
    }else{
        $class_show_rated = 'active';
    }
    
    ?>
    
   
    <?php
    
   //pager begin
   print_paging('recs-' . $algo, $page, $recs_per_page,$tot_recs, $algo);
    
    
    $start_rec_number = ($page - 1) * $recs_per_page;
    
    //with movies that have been rated 
    $sql = "SELECT r.recommendationid as recid,m.movieid,m.title,m.year,r.value FROM movies as m INNER JOIN recommendations as r ON m.movieid=r.movieid WHERE userid=? AND r.algorithm=? ORDER BY r.value DESC, m.year DESC , m.movieid ASC LIMIT $start_rec_number , $recs_per_page";
    
    //without movies that have been rated
    #$sql = "SELECT t.recid, t.movieid, t.title, t.year, t.value, ratings.rating FROM (SELECT r.recommendationid as recid,m.movieid,m.title,m.year,r.value FROM movies as m INNER JOIN recommendations as r ON m.movieid=r.movieid WHERE userid=? AND r.algorithm=?) t LEFT JOIN ratings ON t.movieid = ratings.movieid WHERE rating IS NULL ORDER BY t.value DESC, t.year DESC ,t.movieid ASC LIMIT $start_rec_number , $recs_per_page";

    $stat = prepareStatement($sql);
    $stat->bindParam(1, $user);
    $stat->bindParam(2, $algo);
    
    $stat->execute();
    $rows = $stat->fetchAll();
    
    foreach ($rows as $row)
    {
        $movieid = $row['movieid'];
        $title = trim($row['title']);
        $year = trim($row['year']);
        $recvalue = $row['value'];
        $recid = $row['recid'];
        
        $sql = "SELECT rating FROM ratings WHERE movieid=? and userid=?";
    
        $stat = prepareStatement($sql);
        $stat->bindParam(1, $movieid);
        $stat->bindParam(2, $user);
        $stat->execute();
        $res = $stat->fetchAll();
        
        $data = array('tab' => $algo);
        $data['type'] = 'rec';
        $data['recvalue'] = $recvalue;
        $data['recid'] = $recid;
        $data['algo'] = $algo;
        
        
        if (empty($res)){
            $data['rated']  = FALSE;
        }else{
            $data['rated'] = TRUE;
            $data['rating'] = $res[0]['rating'];
        }
        
       //relic from old code, here for backwards compatibility
        $data['relevancefeedback'] = FALSE;
        
        print_movie($movieid, $title, $year, $data);
    }
    
    print "</div>";
    
    //pager bottom
   print_paging('recs-' . $algo, $page, $recs_per_page,$tot_recs, $algo);
                
                
?>