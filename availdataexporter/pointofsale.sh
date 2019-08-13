#!/bin/bash
#testing the existance of upload.conf.init
#Magento module : Pointofsale

FILE_INIT=upload.conf.init
FILE_AFTER=upload.conf.after
FILE=upload.conf
if [[ -f $FILE_INIT ]]
then 
		if [[ -f $FILE_AFTER ]]
                then
                mv $FILE_AFTER $FILE
                fi
        #Anyway execute the availdataexporter script
        ./availdataexporter.sh
else
        if [[ -f $FILE ]]
                then if [[ -f $FILE_AFTER ]]
                        then
                        #execute the script with transaction data before renaming the files
                        ./availdataexporter.sh
                        mv $FILE $FILE_INIT 
                        mv $FILE_AFTER $FILE
                        fi
        fi
        
fi

