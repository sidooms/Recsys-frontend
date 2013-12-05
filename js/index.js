/*
*  Recsys-frontend, Copyright (c) 2013, Simon Dooms
*  http://github.com/sidooms/recsys-frontend
*  MIT License
*/
    var movies_sort_type = 'random';
    init_algo_weights = {};
    var algo_weights_initialized = false;
    var stats_loaded = false;
    var pie_chart_initialized = false;
    var load_weights_after_stats = false;
    
    $(document).ready(function() {
        var rpp_movies = get_results_per_page('movies');
        var rpp_hybrid = get_results_per_page('hybrid');
        get_movies(1,rpp_movies, 'movies');
        setTimeout(function(){get_movies(1,rpp_hybrid,'hybrid')}, 1000);
        setTimeout(function(){get_stats()}, 0);
    });

    
    function get_results_per_page(tab)
    {
        var rpp = $('#results-per-' + tab).find(":selected").first().text();
        if (rpp == '')
            //default is 10
            return 10;
        return rpp;
    }
    
    function change_results_per_page(selectbox, tab)
    {
        var res = selectbox.options[selectbox.selectedIndex].value;
        get_movies(1,res, tab);
    }
    
    
    function get_movies(page, movies_per_page, the_div)
    {
           if (the_div == 'movies'){
                var search_text = $('#input_movies_search').val();
                if (typeof(search_text) == 'undefined'){
                    search_text = '';
                }
                $.ajax({
                  url: "getmovies.php?p=" + page + "&ppage=" +  movies_per_page  +'&sort='+ movies_sort_type + '&search=' + search_text ,                  
                }).done(function ( data ) {
                    $("#movies").html(data);
                });
           }else if (the_div =='hybrid'){
                $.ajax({
                  url: "gethybrid.php?p=" + page + "&ppage=" +  movies_per_page ,
                }).done(function ( data ) {
                    $("#hybrid").html(data);            
                });
           }else{
               $.ajax({
                  url: "getrecommendations.php?p=" + page + "&ppage=" +  movies_per_page + "&a=" + the_div ,
                }).done(function ( data ) {
                    $("#" + the_div).html(data);
                });
           }
    }
    
    function get_stats()
    {
        $.ajax({
          url: "getstats.php?",
        }).done(function ( data ) {
            $("#stats").html(data);
            stats_loaded = true;
            if (load_weights_after_stats){                
                 init_algo_weights_handles();    
            }
        });
    }
    
        function toggle_extra_movie_data (movieid, title, year)
        {
            if ($("#extramoviedata-"+ movieid + ">.searched" ).text() == "false") {
                search_and_complete_movie_metadata(movieid, title, year);
            }
        }
        
        function  search_and_complete_movie_metadata(movieid, title, year)
        {
            title = $.trim(title);
            year = $.trim(year);
            
            imdbid = movieid.replace(/\D+/, '');
            
            //tt0133093/
            while (imdbid.length < 7){
                imdbid = '0' + imdbid
            }
            imdbid = 'tt' + imdbid
            $.ajax({
                url: "http://www.omdbapi.com/?i=" + imdbid,
            }).done(function ( data ) {
              var movie = JSON.parse(data);
              if (movie.Response == 'False')
              {
                //just hide the movie
                //$('#movie-' + movieid).hide();
                
                 var notfoundstring = '(No moviedata found.)'
                 
                
                  $("#cast-"+ movieid).text(notfoundstring);
                  
              }else{
                  $("#director-"+ movieid).text(movie.Director);
                  $("#cast-"+ movieid).text(movie.Actors);
                  $("#genre-"+ movieid).text(movie.Genre);
                  $("#runtime-"+ movieid).text(movie.Runtime);
                  $("#plot-"+ movieid).text(movie.Plot);
                  $("#imdbrating-"+ movieid).html('<a target=\'_blank\' href=\'http://www.imdb.com/title/' + movie.imdbID + '\'>' + movie.imdbRating + ' (' + movie.imdbVotes + ' votes)</a>' );
                  //prevent imdb hotlinking by downloading the poster and storing it on the webserver
                  $.ajax({
                    url: "getmovieposter.php?title=" + title + "&year=" + year + "&url=" + movie.Poster,
                  }).done(function ( data ) {
                    $("#poster-"+ movieid).attr('src', data);
                  });
              }
              
              //make sure this is downloaded only once!
              $("#extramoviedata-"+ movieid + ">.searched" ).text("true");
            });
        }
        
       
        
        function rate_movie(movieid, rating, event)
        {
            //make sure movieid is clean
            cleanmovieid = movieid.replace(/[A-Za-z-]/g, "");
            user = 999999;
            
            //remove rating buttons
            $("#likebtn-" + movieid).addClass('hide');
            $("#dislikebtn-" + movieid).addClass('hide');
            
            $.ajax({
              url: "actions.php?action=rate&mid=" + cleanmovieid + "&uid=" + user + '&r=' + rating,
            }).done(function ( data ) {
                //add rating status
                if (rating == 10){
                    $("#liked-status-" + movieid).removeClass('hide');
                }else{
                    $("#not-liked-status-" + movieid).removeClass('hide');
                }
            });
            event.preventDefault();
        }
        
        
        
        function cancel_rating(movieid, event)
        {
            //make sure movieid is clean
            cleanmovieid = movieid.replace(/[A-Za-z-]/g, "");
            user = 999999;
            //remove rating status
            $("#liked-status-" + movieid).addClass('hide');
            $("#not-liked-status-" + movieid).addClass('hide');
            $.ajax({
              url: "actions.php?action=cancelrating&mid=" + cleanmovieid + "&uid=" + user,
            }).done(function ( data ) {
                //show rating buttons
                $("#likebtn-" + movieid).removeClass('hide');
                $("#dislikebtn-" + movieid).removeClass('hide');
            });
             event.preventDefault();
        }
        
        
        function toggle_explanation(link, event, movieid)
        {    
          if (link.text == 'Show explanation'){
                link.textContent = 'Hide explanation';
            }else{
                link.textContent = 'Show explanation';
            }
            divid = 'explanation-' + movieid;
            MathJax.Hub.Queue(["Typeset",MathJax.Hub,divid]);
            $("#" + divid).slideToggle();
            event.preventDefault();
        }
        
        function show_rec_tabs(tab)
        {
            show_tabs(tab);
            $("#header-recommendationalgos" ).addClass('active');
        }
        
        function show_tabs(tab)
        {
            //change header active status
            $(".algo-header").removeClass('active');
            $("#tab-" + tab).addClass('active');
            //remove header recommendation status active
            $("#header-recommendationalgos" ).removeClass('active');
            
            //hide all tabs
            $(".tab").hide();
            $("#" + tab).fadeIn();
            
            if (tab == 'stats'){
                if (stats_loaded){
                    init_algo_weights_handles();       
                }else{
                    // Indicate that weights should be initialized as 
                    // soon as stats are loaded
                    load_weights_after_stats = true;
                }
            }            
        }
        
        function init_algo_weights_handles()
        {
            if (!algo_weights_initialized)
                {
                    for (var key in init_algo_weights) 
                    {
                        var val = init_algo_weights[key];
                        //http://stackoverflow.com/questions/7433824/how-to-get-current-loop-iteration-from-anonymous-function-within-the-for-loop
                        (function(key, val){
                            $('#algo-slider-handle-'+key).text(val.toFixed(4));                        
                            new Dragdealer('algo-slider-'+key, {x: val,
                                                            animationCallback : function(x, y){ algo_weights_moved(key, x); },
                                                           callback: function(x,y) {update_algo_weight(key, x);} });
                        
                        })(key, val);
                    }
                    algo_weights_initialized = true;
                }            
        }
        
        function algo_weights_moved(key, value)
        {
            $('#algo-slider-handle-'+key).text(value.toFixed(4));
            init_algo_weights[key] = value;
            piechart_data = [];
            for (var key in init_algo_weights) 
            {
                obj = new Object();
                obj.algo = key;
                obj.weight = init_algo_weights[key];
                 piechart_data.push(obj);
            }
             if (pie_chart_initialized){
                update_pie_chart(piechart_data); 
            }
        }
        
        function start_reccalc()
        {
            $.ajax({
              url: "actions.php?action=startreccalc",
            }).done(function ( data ) {
                alert('The recommendation calculation is done.');
            });
        }
        
        function start_hybrid_calc()
        {
             $.ajax({
               url: "actions.php?action=hybridcalc",
            }).done(function ( data ) {
                alert('The hybrid recommendations are refreshed.');
            });
        }
        
        function update_algo_weight(key, x)
        {
            user = 999999;
             $.ajax({
                url: "actions.php?action=updatealgoweight&algo=" + key +"&weight="+ x + "&user=" + user,
            }).done(function ( data ) {
               //alert('New weight saved');
            });
        }