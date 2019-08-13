#!/bin/bash

# redirect std err since its a subset of std out
exec java -Djava.ext.dirs=lib -Xmx256m -jar bin/availdataexporter.jar $@ 2> /dev/null
