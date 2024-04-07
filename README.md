[![Docker Image CI](https://github.com/k9barry/8800SX/actions/workflows/docker-image.yml/badge.svg)](https://github.com/k9barry/8800SX/actions/workflows/docker-image.yml)
Docker compose project to take to output files from a Viavi 8800SX service monitor
and parse the information into a mySql database. Additionally a copy of the 
service record is stored in the DB as a BLOB.

TO USE: 
- Copy repo to your environment (docker pull ghcr.io/k9barry/8800sx)
- Edit /secrets/db_password.txt to add DB password
- docker compose up -d
- After all containers start (may take a minute) go to http://localhost:8080
- On the Viavi 8800SX Database page select all the files from the Viavi service monitor 
you want to import and press "Submit"
- To search for a record type and part of the serial number in the Seatch box.
- Once the upload completes you can press the phpMyAdmin button to open the DB
- Navigate to the viavi database
- DB user is "viavi" use the search button to search the DB serial numbers

See the  documentation written into script.

![index](https://github.com/k9barry/8800SX/assets/16656369/0c9ba0b5-dd22-4f9d-a76a-404b8d11aaaa)
