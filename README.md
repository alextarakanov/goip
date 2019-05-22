###goip, smb, radmin service in docker container


create container
`
docker build  -f ./19052001_goip.Dockerfile -t cr80/goip:v.0.1.1  .
`

edit docker-compose.yaml 
service "install" and exec
`
docker-compose up install
docker-compose down
`
then change docker-compose.yaml again and exec
`
docker-compose up 
`