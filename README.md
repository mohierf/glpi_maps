#Plugin Maps ...
A Glpi plugin to display computers on a Google map

##Don't know Glpi?

... then this plugin is probably not for you. But before walking away you should definitely [check out Glpi](http://www.glpi-project.org/)!

##Features

Maps plugin for Glpi allows to display Glpi managed computers on a Google map:

* some locations should have GPS coordinates (eg. 47.44809, -0.5624266) in 'Building number' field
* all computers attached to a location with GPS are displayed on the map
* map marker is blue for a GLPI computer which is not known in the Monitoring plugin
* for monitored hosts, the marker is green / orange / red depending on host and services states
* several markers on the same point are slightly moved to avoid markers overlapping
* markers are grouped in clusters on a grid basis
* clusters are green / orange / red depending on clustered hosts states

##Issues

* No known issues ...

##Configuration

####Profiles
Each Glpi profile may be configured to allow/disallow :
* log file for the plugin (the file is maps.log in Glpi logs directory)
* map visualization in a tab of the Glpi home page
* map visualization from the Glpi plugins menu

##Development
n/a

