#!/usr/bin/env python

# example basictreeview.py

import pygtk
pygtk.require('2.0')
import gtk
import sys
import fileinput
import string

class BasicTreeViewExample:

    # close the window and quit
    def delete_event(self, widget, event, data=None):
        gtk.main_quit()
        return False

    def __init__(self):
        # Create a new window
        self.window = gtk.Window(gtk.WINDOW_TOPLEVEL)

        self.window.set_title("Basic TreeView Example")

        self.window.set_size_request(200, 200)

        self.window.connect("delete_event", self.delete_event)

        # create a TreeStore with one string column to use as the model
        self.treestore = gtk.TreeStore(str)

        # we'll add some data now - 4 rows with 3 child rows each
        for parent in range(4):
            piter = self.treestore.append(None, ['parent %i' % parent])
            for child in range(3):
                self.treestore.append(piter, ['child %i of parent %i' %
                                              (child, parent)])

        # create the TreeView using treestore
        self.treeview = gtk.TreeView(self.treestore)

        # create the TreeViewColumn to display the data
        self.tvcolumn = gtk.TreeViewColumn('Column 0')

        # add tvcolumn to treeview
        self.treeview.append_column(self.tvcolumn)

        # create a CellRendererText to render the data
        self.cell = gtk.CellRendererText()

        # add the cell to the tvcolumn and allow it to expand
        self.tvcolumn.pack_start(self.cell, True)

        # set the cell "text" attribute to column 0 - retrieve text
        # from that column in treestore
        self.tvcolumn.add_attribute(self.cell, 'text', 0)

        # make it searchable
        self.treeview.set_search_column(0)

        # Allow sorting on the column
        self.tvcolumn.set_sort_column_id(0)

        # Allow drag and drop reordering of rows
        self.treeview.set_reorderable(True)

        self.window.add(self.treeview)

        self.window.show_all()

# id number is key, smallest name is value
parents = dict()
rows = []

def main():
  # require at least one argument
  if (len(sys.argv) < 2):
    print("Specify some file(s)")
    exit()
  
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
  gtk.main()

if __name__ == "__main__":
    tvexample = BasicTreeViewExample()
    main()
