## goip, smb, radmin service in docker container
***

Download project

git clone https://github.com/alextarakanov/goip.git  
cd ./goip

create container  
docker build  -f ./19052001_goip.Dockerfile -t cr80/goip:v.0.1.1  . 


edit (set variables):  
___docker-compose.yaml___  
___.env___  
___security/db.env___  
___security/radmin.env___


In thish step, create databases and add tables with users  
uncomment string for install  
entrypoint: [ "/install.sh"]


exec from cli  
`docker-compose up install`

after  
`docker-compose down`

then change docker-compose.yaml again and exec  
`docker-compose up` 

***

enjoy
