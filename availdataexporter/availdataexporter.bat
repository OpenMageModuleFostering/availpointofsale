@echo off
rem Suppress standard error since its a subset of whats output to stdout.
java -jar -Djava.ext.dirs=lib -Xmx256m bin\availdataexporter.jar %1 2> NUL
