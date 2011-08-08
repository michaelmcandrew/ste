
Resource Module
---------------

Initial development by Darrel O'Pry
January 2009

---------------

The Resource module abstracts file resources, using PHP Stream Wrappers, so
that the file system can handle files from systems other than the default.
For instance, resources can be local or remote, such as from Flickr.

Two stream wrappers are included by default: Public and Private, coorresponding
to Drupal's notion of public and private file systems.

File resources are stored in the file system as wrapper://location.to.file,
such as public://sites/example.com/files/images/my-pic.jpg,
private://home/private/files/docs/secret.pdf, or youtube://v/22ckem397ds.

A Stream Wrapper will register itself with the
