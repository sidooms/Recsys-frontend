#!/usr/bin/python
#  Recsys-frontend, Copyright (c) 2013, Simon Dooms
#  http://github.com/sidooms/recsys-frontend
#  MIT License
# 
import math
import sys

class HybridCalculator:
    
    the_db = None
    algos_rec_item = None
    
    def __init__(self, the_db):
        self.the_db = the_db

    # Rounds the value to x digits and returns int
    def r(self, value):
        return round(value, 3)

    def get_non_zero_algos(self, algos):
        non_zero = 0 # Haha, funny
        for (item, weight) in algos.items():
            if weight != None and float(weight) != 0.0:
                non_zero += 1
        return non_zero
        
    def calculate(self):
        db = self.the_db
        users = db.get_users()
        items = db.get_items()
        
        for user in users:
            algos = db.get_algorithm_weights(user)
            
            non_zero_algos = self.get_non_zero_algos(algos)
            itemrecs = list()
            # Get all rec values for all items for all algorithms for this user
            items_algos_rec = db.get_items_algos_rec(user)
            for item in items_algos_rec:
                top = 0
                bottom = 0
                
                explanation_list = list()
                
                # Calculate the total rec value for this item
                for (algo, rec_value) in items_algos_rec[item].items():
                    try:
                        weight = float(algos[algo])
                    except:
                        weight = 0.0 # weight == None
                    if weight != float(0.0):
                        top += rec_value * weight
                        bottom += weight
                
                        explanation_list.append((algo,str(self.r(rec_value)), str(self.r(weight))))
                if bottom == 0:
                    # No algorithm has a recommendation value for this item
                    continue
                item_rec_value = top / bottom
                
                explanation = self.get_explanation_string(explanation_list, item_rec_value, non_zero_algos)
                
                # Make sure is not below 1, above 10
                item_rec_value = max(1, item_rec_value)
                item_rec_value = min(10, item_rec_value)
                
                
                itemrecs.append((item,item_rec_value, explanation))
            
            db.clear_hybrid_recs(user)
            db.save_hybrid_recs(user, itemrecs)
            
    def get_explanation_string(self, explanation_list, item_rec_value, num_algos):
        expl = '<table class="table table-condensed table-hover table-striped"><thead><tr><th>Algorithm</th><th>Value</th><th>Weight</th></tr>'
        num_used_algos = len(explanation_list)
        for (algo, rec, weight) in explanation_list:
            expl += '<tr><td>' + algo + '</td><td>' + rec + '</td><td>' + weight + '</td></tr>'
        expl += '</table>'
        
        expl += '$$\\frac{\sum algo\_rec\_value * algo\_weight}{\sum algo\_weight} = ' + str(self.r(item_rec_value)) + '$$'
        expl +=  '$$rec\_value = max(1,rec\_value)$$ $$rec\_value = min(10,rec\_value)$$'
        return expl