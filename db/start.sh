#!/bin/bash 
#  Recsys-frontend, Copyright (c) 2013, Simon Dooms
#  http://github.com/sidooms/recsys-frontend
#  MIT License
# 
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source $DIR/../config/settings.ini

echo 'Read the config file. (just ignore the command not found messages)'

# imports the initial table structure 
mysql -u $db_username -p$db_password $db_db < $DIR/init_database_structure.sql

echo 'Initial DB structured imported.'

# Generates an SQL file from the MovieTweetings dataset that can be used to fill the movies table in the database
file_path="../MovieTweetings/movies.dat"
cat $file_path | awk -F '::' '{ print "#"$1"#" $2}'  | awk -F ', ' '{print $2 $1}'  | sed -e 's/^\(.*\)#\([0-9]*\)#\(.*\)$/\2#\1 \3/g'| sed -e 's/^\(.*\)(\([0-9][0-9][0-9][0-9]\))\(.*\)$/\1 \3@ \2/g' | grep @ | sed "s/^ *//;s/ *$//;s/ \{1,\}/ /g" | sed -r 's/^(.*)#(.*)@(.*)/INSERT INTO movies (movieid,title,year) VALUES ("\1","\2",\3);/g' > $DIR/movietweetings_movies.sql
mysql -u $db_username -p$db_password $db_db < $DIR/movietweetings_movies.sql

echo 'MovieTweetings movies imported in the database.'
echo 'All done.'