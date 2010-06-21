#!/usr/bin/env python
"""
Author  : Kamil Slowikowski kslowikowski-at-luc-dot-edu
Date    : June 6, 2010
Version : 0.1

Usage
=====
./parseitis.py File

Description
===========
Parse an ITIS binary file and output the parent/child relations.
"""

import sys
import fileinput
import string
import pprint

import pygraphviz as pgv

# require at least one argument
if (len(sys.argv) < 2):
    print("Specify some file(s)")
    exit()

# id number is key, smallest name is value
parents = dict()
rows = []

for line in fileinput.input():
  # split with | delimiter
  fields  = line.rstrip().split('|')
  section = fields[0]
  
  if section == '[TU]':
    valid = fields[11]
    
    if valid == 'valid':
      id       = fields[1]
      parentid = fields[18]
      name     = ''
      # names stored hierarchically in indices 3-10
      for n in reversed(fields[3:10]):
        if n != '':
          name = n
          break
      parents[id] = [name, parentid]
      rows.append([id, name, parentid])
      #print("{0}\t{1}\t{2}".format(idnumber, name, parentid))

print("name\tparent")
for id, name, parentid in rows:
  if parentid != '0':
    print(name + '\t' + parents[parentid][0])

def getChildren(parentid):
  children = []
  for id, name, pid in rows:
    if pid == parentid:
      children.append(name)
  return children

#gr = pgv.AGraph()
#for id, name, pid in rows:
#  gr.add_node(name)

#for id, name, pid in rows:
#  if pid != '0':
#    gr.add_edge(name, parents[pid][0])

#gr.layout()
#gr.draw('Bryozoans.png')
print("`````````````````````")
children = getChildren('155470')
for c in children:
  print(c)
  print(getChildren(c))

#for id, name, pid in rows:
#  if pid != '0':
#    children = getChildren(id)
#    if len(children) > 0:
#      print(name, children)

