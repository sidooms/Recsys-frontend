<?php
/**  
*  Recsys-frontend, Copyright (c) 2013, Simon Dooms
*  http://github.com/sidooms/recsys-frontend
*  MIT License
*/
    require_once "general.php";
    
    $user = 999999;
    
    if (empty($_REQUEST['p']) or empty($_REQUEST['ppage']))
        exit;

    $page = $_REQUEST['p'];
    $recs_per_page = $_REQUEST['ppage'];
    

    $tot_recs = get_number_of_hybridrecommendations($user);
    
    if (!valid_page($page, $recs_per_page, $tot_recs))
    {
        print 'Invalid page request! page=' . $page . ', recs_per_page=' . $recs_per_page . ', tot_recs=' . $tot_recs;
        exit;
    }   
    
   //pager begin
   print_paging('hybrid', $page, $recs_per_page,$tot_recs, 'hybrid');
    
    
    $start_rec_number = ($page - 1) * $recs_per_page;
    
    $sql = "SELECT * FROM h_recommendations r INNER JOIN movies m ON r.movieid = m.movieid WHERE r.userid=? ORDER BY r.value DESC, m.year DESC, m.movieid ASC LIMIT $start_rec_number , $recs_per_page";

    $stat = prepareStatement($sql);
    $stat->bindParam(1, $user);
    $stat->execute();
    $rows = $stat->fetchAll();
    
    foreach ($rows as $row)
    {
        $movieid = $row['movieid'];
        $title = $row['title'];
        $year = $row['year'];
        $recvalue = $row['value'];
        $explanation = $row['explanation'];
        $algo = 'hybrid';
        $recid = $row['recommendationid'];
        
        $sql = "SELECT rating FROM ratings WHERE movieid=? and userid=?";
    
        $stat = prepareStatement($sql);
        $stat->bindParam(1, $movieid);
        $stat->bindParam(2, $user);
        $stat->execute();
        $res = $stat->fetchAll();
        
        $data = array('tab' => 'hybrid');
        $data['recvalue'] = $recvalue;
        $data['explanation'] = $explanation;
        $data['recid'] = $recid;
        $data['algo'] = $algo;
        $data['type'] = 'hybrid';
        
        
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
    //---
    
    print "</div>";
    
    //pager bottom
   print_paging('hybrid', $page, $recs_per_page,$tot_recs, 'hybrid');
                
                
?>
