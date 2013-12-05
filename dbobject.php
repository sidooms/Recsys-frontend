<?php
/**  
*  Recsys-frontend, Copyright (c) 2013, Simon Dooms
*  http://github.com/sidooms/recsys-frontend
*  MIT License
*/
    require_once "general.php";
    
    class Db
    {
        public function clear_hybrid_recs_db($user)
        {
            $sql = "DELETE FROM h_recommendations WHERE userid=?";
            $stat = prepareStatement($sql);
            //insert into db
            $stat->bindParam(1, $user);
            $res = $stat->execute();
        }
        
        public function save_hybrid_recs_to_db($item_recs, $user)
        {
            foreach($item_recs as $item => $rec_value)
            {
                $sql = "INSERT INTO h_recommendations (userid, movieid, value) VALUES($user,$item,$rec_value)";
                $stat = prepareStatement($sql);
                //insert into db
                $stat->bindParam(1, $user);
                $stat->bindParam(2, $item);
                $stat->bindParam(3, $rec_value);
                $res = $stat->execute();
            }
        }   
        
        public function getAllUsers()
        {
            $users = array();
            $sql = "SELECT userid FROM users WHERE 1";
            $stat = prepareStatement($sql);
            $stat->execute();
            $rows = $stat->fetchAll();
            
            foreach ($rows as $row)
            {
                $users[] = $row['userid'];
            }
            return $users;
        }
        
        public function getAllItems()
        {
            $items = array();
            $sql = "SELECT movieid FROM movies WHERE 1";
            $stat = prepareStatement($sql);
            $stat->execute();
            $rows = $stat->fetchAll();
            
            foreach ($rows as $row)
            {
                $items[] = $row['movieid'];
            }
            return $items;
        }
        
        public function getItemsRecValue($user, $algo)
        {
            $sql = "SELECT y.value, y.recommendationid, y.movieid, y.position FROM (SELECT r.recommendationid, r.movieid, r.value, @rownum := @rownum + 1 AS position FROM recommendations r JOIN (SELECT @rownum := -1) x WHERE r.userid=? and r.algorithm=? ORDER BY r.value DESC, (SELECT year FROM movies m WHERE m.movieid=r.movieid) DESC) y";
             
            $stat = prepareStatement($sql);
            $stat->bindParam(1, $user);
            $stat->bindParam(2, $algo);
            $stat->execute();
            $rows = $stat->fetchAll();
            
            $items = array();
            
            foreach ($rows as $row)
            {
                $item = $row['movieid'];
                $recvalue = $row['value'];
                
                $items[$item] = $recvalue;
            }
            
            return $items;
        }
        
        public function getItemRecValue($user, $algo, $movieid)
        {
            $sql = "SELECT y.value, y.recommendationid, y.movieid, y.position FROM (SELECT r.recommendationid, r.movieid, r.value, @rownum := @rownum + 1 AS position FROM recommendations r JOIN (SELECT @rownum := -1) x WHERE r.userid=? and r.algorithm=? and r.movieid=?) y";
             
            $stat = prepareStatement($sql);
            $stat->bindParam(1, $user);
            $stat->bindParam(2, $algo);
            $stat->bindParam(3, $movieid);
            $stat->execute();
            $res = $stat->fetchAll();
            
            if (count($res) > 0 && count($res[0]) > 0){
                $value = $res[0][0];
            }else{
                $value = -1;
            }
            return $value;
        }
        
        public function getAlgorithmWeights(&$algos, &$counts, $user)
        {
            //get the algos and their scores
            
            $sql = "SELECT COUNT( * ) AS total, r.algorithm, w.weight FROM (SELECT * FROM recommendations WHERE userid = 999999) r LEFT JOIN algorithmweights w ON r.algorithm = w.algorithm  GROUP BY r.algorithm";
                
            $stat = prepareStatement($sql);
            $stat->bindParam(1, $user);
            $stat->execute();
            $rows = $stat->fetchAll();
            
            foreach ($rows as $row)
            {
                $algo = $row['algorithm'];
                $total = $row['total'];
                $weight = $row['weight'];
                if (empty($weight))
                    $weight = 0;
                $algos[$algo] = $weight;
                $counts[$algo] = $total;
            }    
        }
        
     
        
        public function getPositionItemRecList($user, $algo, $movieid)
        {
            //get the position of the movie in the reclist linked with the feedback
            $sql = "SELECT y.position FROM (SELECT r.recommendationid, r.movieid, @rownum := @rownum + 1 AS position FROM recommendations r JOIN (SELECT @rownum := -1) x WHERE r.userid=? and r.algorithm=? ORDER BY r.value DESC, r.movieid DESC) y WHERE y.movieid=?";
            $stat = prepareStatement($sql);
            $stat->bindParam(1, $user);
            $stat->bindParam(2, $algo);
            $stat->bindParam(3, $movieid);
            $stat->execute();
            $res = $stat->fetchAll();
            if (count($res) > 0 && count($res[0]) > 0){
                $position = $res[0][0];
            }else{
                /*print $user . ' ' . $algo . ' ' . $movieid;
                print_r ($res);
                exit();*/
                return -1;
            }   
            return $position;
        }
        
         
        
        public function saveAlgorithmWeights($algos, $user)
        {
            //clear old weights
            $sql = "DELETE FROM algorithmweights WHERE userid=?";
            $stat = prepareStatement($sql);
            $stat->bindParam(1, $user);
            $stat->execute();
            
            //insert new ones
            foreach ($algos as $algo => $score)
            {
                $sql = "INSERT INTO algorithmweights (algorithm, weight, userid) VALUES (?,?,?)";
                $stat = prepareStatement($sql);
                $stat->bindParam(1, $algo);
                $stat->bindParam(2, $score);
                $stat->bindParam(3, $user);
                $stat->execute();
            }
        }
        
       
    }
?>