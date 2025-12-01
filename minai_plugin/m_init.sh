#!/bin/bash

start_time=$(date +%s%3N)  # start time in milliseconds
dt=$(date '+%F %T.%N');

if [ -f /var/www/html/HerikaServer/log/clean.log ] ; then
  rm /var/www/html/HerikaServer/log/clean.log
fi
echo "--- running m_init job at $dt " &>> /var/www/html/HerikaServer/log/clean.log

echo "-- removing context_for_NPC.txt files " &>> /var/www/html/HerikaServer/log/clean.log
find /var/www/html/HerikaServer/log -name "context_for_*" -exec rm {} \; &>> /var/www/html/HerikaServer/log/clean.log

echo "-- trimming apache log files " &>> /var/www/html/HerikaServer/log/clean.log

if [ -f /var/log/apache2/error.log ] ; then
  file_size=$(wc -l </var/log/apache2/error.log)
  if [ $file_size -ge 2049 ]; then
    echo "trimming /var/log/apache2/error.log size=$file_size " &>> /var/www/html/HerikaServer/log/clean.log
    sed -i 1,$(($(wc -l < /var/log/apache2/error.log)-2048))d /var/log/apache2/error.log
  else
    echo "not trimming /var/log/apache2/error.log size=$file_size " &>> /var/www/html/HerikaServer/log/clean.log
  fi
fi

if [ -f /var/log/apache2/other_vhosts_access.log ] ; then
	file_size=$(wc -l </var/log/apache2/other_vhosts_access.log)
	if [ $file_size -ge 1025 ]; then
		echo "trimming /var/log/apache2/other_vhosts_access.log size=$file_size " &>> /var/www/html/HerikaServer/log/clean.log
		sed -i 1,$(($(wc -l < /var/log/apache2/other_vhosts_access.log)-1024))d /var/log/apache2/other_vhosts_access.log
	else
		echo "not trimming /var/log/apache2/other_vhosts_access.log size=$file_size " &>> /var/www/html/HerikaServer/log/clean.log
	fi
fi

if [ -f /var/log/apache2/access.log ] ; then
	file_size=$(wc -l </var/log/apache2/access.log)
	if [ $file_size -ge 1025 ]; then
		echo "trimming /var/log/apache2/access.log size=$file_size " &>> /var/www/html/HerikaServer/log/clean.log
		sed -i 1,$(($(wc -l < /var/log/apache2/access.log)-1024))d /var/log/apache2/access.log
	else
		echo "not trimming /var/log/apache2/access.log size=$file_size " &>> /var/www/html/HerikaServer/log/clean.log
	fi
fi

echo "-- apache log files trimmed " &>> /var/www/html/HerikaServer/log/clean.log

echo "-- trimming chim log files " &>> /var/www/html/HerikaServer/log/clean.log

cd /var/www/html/HerikaServer/log
for lfile in *.log; do
    if test -f "$lfile" && test ! -L "$lfile"; then
        #is a regular file, not symlink
        file_size=$(wc -l <"$lfile")
        echo "- file: "$lfile" size=$file_size " &>> /var/www/html/HerikaServer/log/clean.log
        if [ $file_size -ge 1025 ]; then
            echo "trimming "$lfile" size=$file_size " &>> /var/www/html/HerikaServer/log/clean.log
            if tail -n 1024 "$lfile"> "$lfile.new"
            then
                mv -v "$lfile.new" "$lfile" &>> /var/www/html/HerikaServer/log/clean.log
            fi
        else
            echo "not trimming "$lfile" size=$file_size " &>> /var/www/html/HerikaServer/log/clean.log
        fi
    fi
done
    
echo "-- chim log files trimmed " &>> /var/www/html/HerikaServer/log/clean.log

dt=$(date '+%F %T.%N');
end_time=$(date +%s%3N)  # # end time in milliseconds
duration_ms=$((end_time - start_time))  # duration in milliseconds
echo "---- job done at $dt total: $duration_ms ms " &>> /var/www/html/HerikaServer/log/clean.log
