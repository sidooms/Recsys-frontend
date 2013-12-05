#!/usr/bin/python
#  Recsys-frontend, Copyright (c) 2013, Simon Dooms
#  http://github.com/sidooms/recsys-frontend
#  MIT License
# 
import settings
import MySQLdb
import MySQLdb.cursors 

class HybridoDb:

    dbo = None
    cur = None
    
    def __init__(self):
        self.dbo = MySQLdb.connect(host=settings.db_hostname, 
                                    user=settings.db_username, 
                                    passwd=settings.db_password, 
                                    db=settings.db_db,
                                    charset='utf8',
                                    init_command='SET NAMES UTF8',
                                    cursorclass=MySQLdb.cursors.DictCursor)
        self.cur = self.dbo.cursor()

    #http://stackoverflow.com/questions/865115/
    def __enter__(self):
        return self
        
    def __exit__(self, type, value, traceback):
        self.dbo.commit()
        # Close all cursors        
        self.cur.close()
        # Close all databases
        self.dbo.close()
        
        
    def test(self):
        # Use all the SQL you like
        self.cur.execute("SELECT userid FROM users LIMIT 0,5")

        # print all the first cell of all the rows
        for row in self.cur.fetchall() :
            print row
            
    def get_users(self):
        self.cur.execute('SELECT userid FROM users')
        data = self.cur.fetchall()
        users = [x['userid'] for x in data]
        return users
       
    def get_items(self):
        self.cur.execute('SELECT movieid FROM movies')
        data = self.cur.fetchall()
        items = [x['movieid'] for x in data]
        return items
        
    def get_algorithm_weights(self, user):
        sql = 'SELECT r.algorithm, w.weight FROM (SELECT DISTINCT algorithm FROM recommendations WHERE userid = %s) r LEFT JOIN algorithmweights w ON r.algorithm = w.algorithm'
        params = (user)
        self.cur.execute(sql, params)
        data = self.cur.fetchall()
        algos = dict()
        nonzeroweights = 0
        for the_data in data:
            weight = the_data['weight']
            if weight == None:
                weight = 0
            algos[the_data['algorithm']] = weight
            if weight > 0 :
                nonzeroweights += 1
        #if all algo weights are zero, than all algo weights become 1
        if nonzeroweights <= 0:
            for algo in algos:
                algos[algo] = 1.0
        #save the new weights
        self.save_algorithm_weights(algos, user)

        return algos
        
    def get_algorithms(self):
        sql = 'SELECT DISTINCT algorithm FROM recommendations'
        params = ()
        self.cur.execute(sql, params)
        data = self.cur.fetchall()
        algos = list()
        for the_data in data:
            algos.append(the_data['algorithm'])
        return algos

    def get_relevance_feedback(self, user):
        sql = 'SELECT movieid, feedback FROM relevancefeedback WHERE userid=%s AND synced=0'
        params = (user)
        self.cur.execute(sql, params)
        data = self.cur.fetchall()
        all_feedback = dict()
        for the_data in data:
            all_feedback[the_data['movieid']] = float(the_data['feedback'])
        return all_feedback
        
    def get_recommendation_value(self, user, algo, movieid):
        sql = 'SELECT value FROM recommendations WHERE userid=%s and algorithm=%s and movieid=%s'
        params = (user, algo, movieid)
        self.cur.execute(sql, params)
        data = self.cur.fetchone()
        if data == None:
            return -1
        else:
            return data['value']
    
    def save_algorithm_weights(self, algos, user):
        # Clear old weights
        sql = 'DELETE FROM algorithmweights WHERE userid=%s'
        params = (user)
        self.cur.execute(sql, params)
        
        for (algo, score) in algos.items():
            # Insert new weights
            try:
                sql = 'INSERT INTO algorithmweights (algorithm, weight, userid) VALUES (%s,%s,%s)'
                params = (algo, score, user)
                self.cur.execute(sql, params)
            except MySQLdb.Error, e:
                try:
                    print "MySQL Error [%d]: %s" % (e.args[0], e.args[1])
                except IndexError:
                    print "MySQL Error: %s" % str(e)
                    

    def get_algos_items_rec_title(self, user):
        sql = 'SELECT r.algorithm, r.movieid, r.value, m.title, m.year FROM recommendations r JOIN movies m ON r.movieid = m.movieid WHERE userid=%s'
        params = (user)
        self.cur.execute(sql, params)
        data = self.cur.fetchall()
        algos_items_rec = dict()
        # Build the data structure
        for the_data in data:
            algo = the_data['algorithm']
            item = the_data['movieid']
            value = the_data['value']
            title = unicode(the_data['title'], 'utf8')
            year = the_data['year']
            titleyear = title + ' (' + str(year) + ')'
            values = dict()
            values['value'] =  value
            values['titleyear'] = titleyear
            values['year'] = year
            
            try:
                algos_items_rec[algo].append(values)
            except:
                algos_items_rec[algo] = list()
                algos_items_rec[algo].append(values)
                
        return algos_items_rec
    
    def get_algos_items_rec_title_and_hybrid(self, user):
        algos_items_rec = self.get_algos_items_rec_title(user)
        sql = 'SELECT r.movieid, r.value, m.title, m.year FROM h_recommendations r JOIN movies m ON r.movieid = m.movieid WHERE userid=%s'
        params = (user)
        self.cur.execute(sql, params)
        data = self.cur.fetchall()
        # Add the hybrid recommendation list
        for the_data in data:
            algo = '--- Hybrid ---'
            item = the_data['movieid']
            value = the_data['value']
            title = unicode(the_data['title'], 'utf8')
            year = the_data['year']
            titleyear = title + ' (' + str(year) + ')'
            values = dict()
            values['value'] =  value
            values['titleyear'] = titleyear
            values['year'] = year
            try:
                algos_items_rec[algo].append(values)
            except:
                algos_items_rec[algo] = list()
                algos_items_rec[algo].append(values)
        return algos_items_rec
    
    def get_algos_rec(self, user, movieid):
        sql = 'SELECT algorithm, value FROM recommendations WHERE userid=%s and movieid=%s'
        params = (user, movieid)
        self.cur.execute(sql, params)
        data = self.cur.fetchall()
        algos_rec = dict()
        # Build the data structure
        for the_data in data:
            algo = the_data['algorithm']
            value = the_data['value']
            algos_rec[algo] = value
        return algos_rec

    
    def get_items_algos_rec(self, user):
        sql = 'SELECT algorithm, movieid, value FROM recommendations WHERE userid=%s'
        params = (user)
        self.cur.execute(sql, params)
        data = self.cur.fetchall()
        items_algos_rec = dict()
        # Build the data structure
        for the_data in data:
            algo = the_data['algorithm']
            item = the_data['movieid']
            value = the_data['value']
            try:
                items_algos_rec[item][algo] = value
            except:
                items_algos_rec[item] = {algo: value}
        return items_algos_rec

    def clear_hybrid_recs(self, user):
        sql = 'DELETE FROM h_recommendations WHERE userid=%s'
        params = (user)
        self.cur.execute(sql, params)
        
    def save_hybrid_recs(self, user, itemrecs):
        for (item, rec, expl) in itemrecs:
            sql = 'INSERT INTO h_recommendations (userid, movieid, value, explanation) VALUES(%s,%s,%s,%s)'
            params = (user, item, rec, expl)
            self.cur.execute(sql, params)
            
    def get_ratings(self, user):
        sql = 'SELECT movieid, rating FROM ratings WHERE userid=%s'
        params = (user)
        self.cur.execute(sql, params)
        data = self.cur.fetchall()
        ratings = dict()
        for the_data in data:
            ratings[the_data['movieid']] = float(the_data['rating'])
        return ratings
        
    def get_hybrid_recommendations(self, user):
        sql = 'SELECT movieid, value FROM h_recommendations WHERE userid=%s'
        params = (user)
        self.cur.execute(sql, params)
        data = self.cur.fetchall()
        ratings = dict()
        for the_data in data:
            ratings[the_data['movieid']] = float(the_data['value'])
        return ratings