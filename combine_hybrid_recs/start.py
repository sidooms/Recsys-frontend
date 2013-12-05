#!/usr/bin/python
#  Recsys-frontend, Copyright (c) 2013, Simon Dooms
#  http://github.com/sidooms/recsys-frontend
#  MIT License
# 
from db import HybridoDb
from hybridcalculator import HybridCalculator

with HybridoDb() as the_db:            

    # Calculate the hybrid recommendation list
    # Results are saved in DB automatically
    calc = HybridCalculator(the_db)
    calc.calculate()