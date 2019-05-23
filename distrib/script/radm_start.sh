#!/bin/bash

while true ; do
    CHECK_PROC=`ps aux |grep radmsrvd | grep -v grep |wc -l`
	if [  $CHECK_PROC -lt 2  ]; then
        kill `pidof radmsrvd` /dev/null 2>&1
		echo "radmin exit, try start."
        echo "/usr/local/bin/radmsrvd -nl -w ${RADM_RADM_WEBPORT} -u ${RADM_USERNAME} -P ${RADM_PASSWORD} -p ${RADM_CLIPORT} -k ${RADM_KEY} -r ${RADM_PORTRANGE}"
        /usr/local/bin/radmsrvd -nl -w ${RADM_RADM_WEBPORT} -u ${RADM_USERNAME} -P ${RADM_PASSWORD} -p ${RADM_CLIPORT} -k ${RADM_KEY} -r ${RADM_PORTRANGE}
        sleep 5
     else 
        date
        echo "radmin is runing";
	fi
	sleep 30
done