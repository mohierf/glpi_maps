#Plugin Maps : A Glpi plugin to display computers on a Google map

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

* There's no such thing as a free lunch
* Consequently, when adding many metrics to a single dashboard, and particularly when metrics have many data points and
  series, the experience might get sluggish. With great power comes great responsibility. Design your dashboards with care.

##Configuration

####Profiles
Each Glpi profile may be configured to allow/disallow :
* log file for the plugin (the file is maps.log in Glpi logs directory)
* map visualization in a tab of the Glpi home page
* map visualization from the Glpi plugins menu

##Development
n/a

##License
Giraffe is distributed under the MIT license. All 3rd party libraries and components are distributed under their
respective license terms.

The Giraffe icon and image were produced using Rickshaw :)

```
Copyright (C) 2013 Frédéric MOHIER

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit
persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```
