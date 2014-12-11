#Recsys-frontend

Recsys-frontend is an easy to set up frontend for recommender systems (recsys) put together by [Simon Dooms](http://twitter.com/sidooms). 

It offers an HTML-based front that allows to browse through a catalog of (the latest) movies, provide ratings and with the click of a button calculate and visualize recommendation results of multiple recommendation algorithms. Check out the screenshots folder for screenshots. 

A live (sandboxed) demo is [available here](http://www.themoviebrain.com/otherprojects/live/).

This project is meant to be installed on a linux-based webserver. It uses a MySQL database and integrates the [MyMediaLite](https://github.com/zenogantner/MyMediaLite) recommendation algorithms and the [MovieTweetings](https://github.com/sidooms/MovieTweetings) dataset. Other integrated projects are [MathJax](http://www.mathjax.org/), [jQuery](http://jquery.com/), [Bootstrap](https://github.com/twbs/bootstrap), [The OMDB API](http://www.omdbapi.com/), [Dragdealer JS](http://code.ovidiu.ch/dragdealer/) and [D3](http://d3js.org/).

Note that this project is meant for research, fun and science. *Security and scalability aspects are not considered.* This framework should be used for quick testing of algorithms and experimentation only. Not on production servers (or servers exposed to the evil internet).

##Installation

Start with a LAMP server (Linux, Apache, MySQL and PHP) with Python and [mono](http://www.mono-project.com) installed.

1. Clone the recsys-frontend github project into the web folder.
2. Make sure the following folders are writable by the Apache user: `db`, `config`, `calc_recs`, `img/movieposters`.
3. Setup an empty MySQL database and a db user account with sufficient priviliges (READ/WRITE/DROP/...)
4. Edit `config/settings.ini` with the correct database credentials.
6. Download the latest [MovieTweetings](https://github.com/sidooms/MovieTweetings) dataset and overwrite the old `ratings.dat`, `movies.dat` and `users.dat` files in the MovieTweetings folder.
5. In a bash shell, execute `db/start.sh`. The database structure will be created and movies contained in the MovieTweetings dataset will be imported into the system.

You might want to edit your `php.ini` file to make sure the `max_execution_time` is set to an appropriate number (depending on how many recommendation algorithms you'll run).

##Usage

So how do you use this? What can it do?

###Rating movies

The 'Movies' tab offers the entire movie catalog, i.e. all the movies contained in the [MovieTweetings](https://github.com/sidooms/MovieTweetings) dataset. Browse random, by year or search for a specific movie title using the search tool.

Rate movies by clicking the `Like` or `Don't like` button. Ratings will be stored in the database as either 10 (10/10) or 1 (1/10). In the future I might add in your typical 5-star feedback tool here, but for now it's thumbs up/down.

###Calculating recommendations

When some ratings have been provided, it's time to bring out the recommendation algorithms. You can set what algorithms must be run in the `config/settings.ini` file. Set the `algorithms` variable to e.g. 

    algorithms="MatrixFactorization UserAverage"

To run both the MatrixFactorization algorithm and the UserAverage algorithm. Check the [MyMediaLite documentation](http://mymedialite.net/documentation/rating_prediction.html) for a complete list of all possible recommendation algorithms.

Run the recommendation calculation by clicking the wrench icon on the right of the front page and choose the option `Calculate recommendations`. Be patient, PHP will run a shell on your webserver that runs mono that runs MyMediaLite. Depending on the algorithms you run, this may take some time. When finished, the recommendations will be imported into the database and a notification will appear on the HTML frontend. Refresh and you should find the 'Recommendation Algos' tab populated with the recommendation algorithms you set in the config file. Click each algorithm for its specific recommendation list (sorted by rating prediction value).

Click the `Calculate recommendations` button every time you want the recommendation lists to be refreshed (e.g. after new or changed ratings).

###Combining hybrid recommendations

I'm specifically doing [research on hybrid recommendation algorithms](http://scholar.google.be/citations?user=owaD8qkAAAAJ&hl=en) and so I've integrated a basic hybrid algorithm for you to play with. After you have calculated the individual recommendation algorithms (see previous section), visit the 'Stats' tab. Here you'll find a list of weights set for every individual algorithm. Move the sliders next to the algorithm names to change the weights and be amazed by the realtime updating circle visualization (you need at least two individual algorithms to be amazed).

Set the weights as you like and hit the `Combine hybrid recommendations` option under the wrench icon on the right of the front page. PHP will then run a shell on your webserver that runs a python script that uses the recommendation results stored in the database and combines them using a simple weighted average formula into a new hybrid recommendation list. A notification should appear when finished.

Refresh and a hybrid recommendation list should now be available under the 'Hybrid' tab. Every recommended movie has a `Show explanation` option to explicitly show how the final hybrid result were obtained.

The hybrid recommendation list will only be refreshed after clicking the `Combine hybrid recommendations` option, so don't forget this when you are changing the individual algorithm weights.

That's it! Remember to [follow me on Twitter](http://twitter.com/sidooms) and tell me how much you like this.
