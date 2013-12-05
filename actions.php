<?php
/**  
*  Recsys-frontend, Copyright (c) 2013, Simon Dooms
*  http://github.com/sidooms/recsys-frontend
*  MIT License
*/
require_once('general.php');

$action = $_REQUEST['action'];

if ($action == 'rate'){
    $movieid=$_REQUEST['mid'];
    $userid=$_REQUEST['uid'];
    $rating=$_REQUEST['r'];
    
    $synced = 0;
    
    //if exists
    $sql = "SELECT COUNT(*) from ratings WHERE userid=? and movieid=?";
    $stat = prepareStatement($sql);
    $stat->bindParam(1, $userid);
    $stat->bindParam(2, $movieid);
    $stat->execute();
    $res = $stat->fetchAll();
    
    if ($res[0][0] > 0){
        //update required
        $sql = "UPDATE ratings SET rating=? WHERE userid=? and  movieid=?";		
        $stat = prepareStatement($sql);
        $stat->bindParam(1, $rating);
        $stat->bindParam(2, $userid);
        $stat->bindParam(3, $movieid);
        $stat->execute();
    }else{
        //insert required
        $sql = "INSERT INTO ratings (userid,movieid,rating,synced) VALUES (?,?,?,?)";		
        $stat = prepareStatement($sql);
        $stat->bindParam(1, $userid);
        $stat->bindParam(2, $movieid);
        $stat->bindParam(3, $rating);
        $stat->bindParam(4, $synced);
        $stat->execute();
    }
    echo 'done.';
}else if ($action == 'cancelrating' ){
    $movieid=$_REQUEST['mid'];
    $userid=$_REQUEST['uid'];
    
    $sql = "DELETE FROM ratings WHERE userid=? and movieid=?";
    $stat = prepareStatement($sql);
    $stat->bindParam(1, $userid);
    $stat->bindParam(2, $movieid);
    $stat->execute();
}else if ($action == 'updatealgoweight'){
    $algo= $_REQUEST['algo'];
    $weight = $_REQUEST['weight'];
    $user = $_REQUEST['user'];
    
    update_algo_weight($user, $algo, $weight);
}else if ($action == 'startreccalc'){
    sync_ratings();
    $path = str_replace('actions.php', '', __FILE__) . 'calc_recs/';
    $command = $path . 'start.sh';
    $output = exec($command);
    echo $output;
    
}else if ($action == 'hybridcalc'){
    $pythonpath = str_replace('actions.php', '', __FILE__) . 'combine_hybrid_recs/';
    exec('python ' . $pythonpath . 'start.py');
}

function update_algo_weight($user, $algo, $weight)
{
    #Check if weight exists
    $sql = "SELECT COUNT(*) from algorithmweights WHERE userid=? and algorithm=?";
    $stat = prepareStatement($sql);
    $stat->bindParam(1, $user);
    $stat->bindParam(2, $algo);
    $stat->execute();
    $res = $stat->fetchAll();
    if ($res[0][0] == 0){
        insert_algo_weight($user, $algo, $weight);
    }else{
        $sql = "UPDATE algorithmweights SET weight=? WHERE algorithm=? AND userid=?";
        $stat = prepareStatement($sql);
        $stat->bindParam(1, $weight);
        $stat->bindParam(2, $algo);
        $stat->bindParam(3, $user);
        $stat->execute();
    }
} 

function insert_algo_weight($user,$algo,$weight)
{
    $sql = "INSERT INTO algorithmweights (userid,algorithm,weight) VALUES (?,?,?)";		
    $stat = prepareStatement($sql);
    $stat->bindParam(1, $user);
    $stat->bindParam(2, $algo);
    $stat->bindParam(3, $weight);
    $stat->execute();
}

function sync_ratings()
{
    $sql = "SELECT ratings.userid, ratings.movieid, ratings.rating, UNIX_TIMESTAMP(ratings.time) as time, movies.title, movies.year from ratings INNER JOIN movies ON ratings.movieid = movies.movieid";
    $stat = prepareStatement($sql);
    $stat->execute();
    $rows = $stat->fetchAll();
    
    $local_path = str_replace('actions.php', '', __FILE__) . 'calc_recs';
    $rating_file = 'ratings.dat';
    
    $fh = fopen("$local_path/$rating_file", 'w') or die("can't open file");
    
    foreach($rows as $row){
        //999999::1::4::1357741027
        $userid = $row['userid'];
        $movieid = $row['movieid'];
        
        #IMDb ID must be 7 chars long
        while (strlen($movieid) < 7)
        {
            $movieid = '0'. $movieid;
        }
        
        $rating = $row['rating'];
        //0000-00-00 00:00:00
        $thetime = $row['time'];
        $title = trim($row['title']);
        $year = trim($row['year']);
        
        fwrite($fh, $userid . '::' . $movieid . '::' . $rating . '::' . $thetime . "\n");
    }
    fclose($fh);
}
?>