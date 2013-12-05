<?php
/**  
*  Recsys-frontend, Copyright (c) 2013, Simon Dooms
*  http://github.com/sidooms/recsys-frontend
*  MIT License
*/

    $title = $_REQUEST['title'];
    
    //strip slashes in the title
    $title = stripslashes($title);
    
    $year = $_REQUEST['year'];
    $url = $_REQUEST['url'];
    
    $notfoundurl = 'img/movieposters/not_found.gif';
    
    $movieposterurl = "img/movieposters";
    $uri = $_SERVER["REQUEST_URI"];
    $pattern = '/(.*)getmovieposter.php\?.*/i';
    $replacement = '$1';
    $uri = preg_replace($pattern, $replacement, $uri);
    $onlinemovieposterurl = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('getmovieposter.php', '', $uri) . 'img/movieposters'; 
    
    
    $hash =  md5($title . "_" . $year);
    $movieposterfile = $movieposterurl . "/" . $hash . ".jpg";
    $onlinemovieposterfile = $onlinemovieposterurl . "/" . $hash . ".jpg";
    
    if ($url == 'N/A'){
       print googleImageSearch($title . ' '  . $year . ' poster');
       exit();
    }
    
    //check if this movie poster is available
    if (!file_exists($movieposterfile) || filesize($movieposterfile) == 0){
        //not available so download
        try{
            $content = file_get_contents($url);
            file_put_contents($movieposterfile, $content);
        }catch (Exception $e){
             print $notfoundurl;
            exit();
        }
    }
    
        
    if (file_exists($movieposterfile) && filesize($movieposterfile) != 0)
        //return movie poster
        print $onlinemovieposterfile;
    else
       print $notfoundurl;
       
    //http://mikefigueroa.com/blog/2011/08/get-first-google-image-search-result-with-php/
    function googleImageSearch($term){
        $q = urlencode($term);
        $jsonurl = "https://ajax.googleapis.com/ajax/services/search/images?v=1.0&q=".$q;
        $result = json_decode(file_get_contents($jsonurl), true);
        return $result['responseData']['results'][0]['tbUrl'];
    }
    
    
?>