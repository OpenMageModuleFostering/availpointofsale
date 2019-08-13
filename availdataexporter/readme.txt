The Avail Data Exporter is used to transfer data from either a supported database or from properly formatted text files to Avail.

To see which version you are currently using, see the first line of standard out output.

To use the data exporter you have to set the customer id, password and the data source(s) in a configuration file. The customer id and password are given to you by Avail. The default configuration file is upload.conf but you can also explicitly specify the configuration file as the first argument to the data exporter start files (or by editing the start files and passing the filename to the java program). An example configuration file is exampleupload.conf. To use that file you start the exporter with:

availdataexporter.bat exampleupload.conf

To define an upload from a database you must specify the sql connection string, the sql driver to use and the sql query used to retrieve the data. Additionally you must specify which data type (*) you are uploading. Each separate upload must be enclosed within curly brackets ('{' and '}'). The values of all settings must be within double quotes (").

To define an upload from a text file you must specify the file name and the file format string. The format string consists of six parts separated with an underscore '_'. See exampleupload.conf for examples.

Format string syntax:
<column delimiter>_<row delimiter>_<start text qualifier>_<end text qualifier>_<encoding>_<escaped/unescaped>.

Note that the string inside the outer double quotes is interpreted as 
a C/Java string and therefore certain characters must be escaped. A non
complete list is:
tab -> \t
" -> \"
\ -> \\
unix line break -> \n
windows line break -> \r\n

Multiple uploads can be defined in the same file. See exampleupload.conf for examples.

If, while processing a configuration file, anything goes wrong all data uploaded so far will be disregarded and the entire upload will have to be performed again.

When used, the data exporter will generate log files in the sub directory log/. By default up to six log files in total can exists, each having a maximum size of 10Mb. 

To setup the data exporter as a scheduled task in Windows 2003 Server:

1. Click Start->Control Panel->Scheduled Tasks->Add Scheduled Task
2. Click Next in the wizard and click Browse
3. Browse to the availdataexporter.bat file and select it
4. Specify when you want to run it

On an average desktop with a 10Mbit/s upstream connection typical upload times for categorydata are:
1M rows - 12 seconds
5M rows - 75 seconds
10M rows - 140 seconds

(*)
The valid data types are (see Avail SaaS documentation for more info):
transactiondata - The data used for product and user recommendations.
productdata - The data used to filter and display product data.
categorydata - The data used for category filters.
categorynamesdata - Alternative category names.
validdata - The data used for valid products filters.
