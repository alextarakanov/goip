#!/bin/sh
cp -fRr /mnt/asterisk /etc

echo '[modules]
autoload=yes
noload => chan_alsa.so
noload => chan_console.so
noload => res_hep.so
noload => res_hep_pjsip.so
noload => res_hep_rtcp.so
noload => chan_sip.so
noload => chan_skinny.so
noload => pbx_config.so
noload => cdr_csv.so
noload => cdr_sqlite3_custom.so
noload => cel_sqlite3_custom.so
noload => res_config_sqlite3.so
noload => chan_iax2.so
noload => res_fax_spandsp.so
noload => res_fax.so
' > /etc/asterisk/modules.conf

########################################################################################################################################
#######################################               run odbc module           ########################################################
########################################################################################################################################

echo "[MySQL]
Description        = ODBC for MySQL
Driver             = /lib/odbc/libmyodbc5a.so
FileUsage          = 1
UsageCount         = 5
" > /etc/odbcinst.ini

echo "[MySQL-asterisk_cdr]
Driver          = MySQL
Description     = MySQL connection to '${ASTERISK_MYSQL_DATABASE}' database
Server          = ${IP_MYSQL}
Port            = 3306
Database        = ${ASTERISK_MYSQL_DATABASE}
Option          = 3
" > /etc/odbc.ini

echo "[odbc_asterisk]
enabled => yes
dsn => MySQL-asterisk_cdr
pre-connect => yes
username => ${ASTERISK_MYSQL_USER}
password => ${ASTERISK_MYSQL_PASSWORD}
max_connections => 20
forcecommit => no
" > /etc/asterisk/res_odbc.conf

########################################################################################################################################
#######################################               run cdr module            ########################################################
########################################################################################################################################

if [ "${ASTERISK_ENABLE_CDR}" = "yes" ] ; then
echo "[cdr_mysql]
connection=odbc_asterisk
table=cdr
alias filename=>filename
alias start=>calldate
" > /etc/asterisk/cdr_adaptive_odbc.conf
else 
echo ""  > /etc/asterisk/cdr_adaptive_odbc.conf
# echo "" > /etc/asterisk/res_odbc.conf
echo "noload => cdr_adaptive_odbc.so" >> /etc/asterisk/modules.conf
fi
########################################################################################################################################
#######################################               run cel module            ########################################################
########################################################################################################################################
if [ "${ASTERISK_ENABLE_CEL}" = "yes" ] ; then
echo "[general]
enable=yes
apps=all
events=all
dateformat = %F %T
"  > /etc/asterisk/cel.conf

echo "[cel_mysql]
connection=odbc_asterisk 
table=cel
loguniqueid=yes
" > /etc/asterisk/cel_odbc.conf

echo '
[mappings]
Master.csv => ${CSV_QUOTE(${eventtype})},${CSV_QUOTE(${eventtime})},${CSV_QUOTE(${CALLERID(name)})},${CSV_QUOTE(${CALLERID(num)})},${CSV_QUOTE(${CALLERID(ANI)})},${CSV_QUOTE(${CALLERID(RDNIS)})},${CSV_QUOTE(${CALLERID(DNID)})},${CSV_QUOTE(${CHANNEL(exten)})},${CSV_QUOTE(${CHANNEL(context)})},${CSV_QUOTE(${CHANNEL(channame)})},${CSV_QUOTE(${CHANNEL(appname)})},${CSV_QUOTE(${CHANNEL(appdata)})},${CSV_QUOTE(${CHANNEL(amaflags)})},${CSV_QUOTE(${CHANNEL(accountcode)})},${CSV_QUOTE(${CHANNEL(uniqueid)})},${CSV_QUOTE(${CHANNEL(linkedid)})},${CSV_QUOTE(${BRIDGEPEER})},${CSV_QUOTE(${CHANNEL(userfield)})},${CSV_QUOTE(${userdeftype})},${CSV_QUOTE(${eventextra})}
' > /etc/asterisk/cel_custom.conf

else 
echo "" > /etc/asterisk/cel.conf
echo "" > /etc/asterisk/cel_odbc.conf
echo "" > /etc/asterisk/cel_custom.conf
echo "noload => cel_custom.so
noload => cel_manager.so
noload => cel_odbc.so
noload => app_celgenuserevent.so
" >> /etc/asterisk/modules.conf

fi
########################################################################################################################################
#######################################               run asterisk              ########################################################
########################################################################################################################################



# run as user asterisk by default
ASTERISK_USER=${ASTERISK_USER:-asterisk}

if [ "$1" = "" ]; then
  COMMAND="/usr/sbin/asterisk -T -W -U ${ASTERISK_USER} -p -vvvdddf"
else
  COMMAND="$@"
fi

if [ "${ASTERISK_UID}" != "" ] && [ "${ASTERISK_GID}" != "" ]; then
  # recreate user and group for asterisk
  # if they've sent as env variables (i.e. to macth with host user to fix permissions for mounted folders

  deluser asterisk && \
  adduser --gecos "" --no-create-home --uid ${ASTERISK_UID} --disabled-password ${ASTERISK_USER} || exit

  chown -R ${ASTERISK_UID}:${ASTERISK_UID} /etc/asterisk \
                                           /var/*/asterisk \
                                           /usr/*/asterisk
fi

exec ${COMMAND}
