#!/bin/bash 
#
#  Recsys-frontend, Copyright (c) 2013, Simon Dooms
#  http://github.com/sidooms/recsys-frontend
#  MIT License
# 

#Calculates the recommendations from the combined ratings of the frontend AND the MovieTweetings dataset

#http://stackoverflow.com/questions/59895/can-a-bash-script-tell-what-directory-its-stored-in
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source $DIR/../config/settings.ini

totalratingsfile="combinedratings.dat"
cat $DIR/ratings.dat $DIR/../MovieTweetings/ratings.dat > $DIR/combinedratings.dat

#generate the test file (all frontend users and all items)
rm $DIR/combinedtest.dat 2>/dev/null
users=`cat $DIR/ratings.dat | cut -d ':' -f 1 | sort | uniq`
items=`cat $DIR/combinedratings.dat | cut -d ':' -f 3 | sort | uniq`
for user in $users
do
    for item in $items
    do
        #rating, and timestamp doesn't really matter since we're
        #just generating recommendations and not performing evaluation
        echo $user"::"$item"::"1"::"1386078668 >> $DIR/combinedtest.dat
    done
done

#recommend for all algorithms (set in config/settings.ini)
for algo in $algorithms
do
    mono $DIR/MyMediaLite/rating_prediction.exe --training-file=$DIR/combinedratings.dat --test-file=$DIR/combinedtest.dat --file-format=movielens_1m --prediction-file=$DIR/$algo"_"results.tsv --test-no-ratings --recommender=$algo
done

#generate SQL import file
rm $DIR/recommendations.sql 2>/dev/null
for algo in $algorithms
do
    cat $DIR/$algo"_"results.tsv | awk -F '\t' '{print "INSERT INTO recommendations (algorithm,userid,movieid,value) VALUES (\"'$algo'\","$1","$2","$3");"}' >> $DIR/recommendations.sql
done

#clearing existing recommendations
mysql -u $db_username -p$db_password -e "DELETE FROM recommendations WHERE movieid > -1" $db_db
#inserting new recommendations
mysql -u $db_username -p$db_password $db_db < $DIR/recommendations.sql