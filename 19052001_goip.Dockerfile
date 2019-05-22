FROM daald/ubuntu32:trusty
LABEL site="nosite.net" \
	version="190522_01" \
	description="GoIP Docker image"	\
	source_goip="http://118.142.51.162/update/"
RUN apt update && apt-get install -y libgssapi-krb5-2 mysql-client mc tcpdump
COPY distrib/bin/ /usr/local/bin/
COPY distrib/lib/ /lib_goip/
WORKDIR /
