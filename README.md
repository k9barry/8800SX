Docker compose project to take to output files from a Viavi 8800SX service monitor
and parse the information into a mySql database. Additionally a copy of the 
service record is stored in the DB as a BLOB

TO USE: 
- Copy repo to your environment
- rename .env-public to .env
- Change the root password in the .env file and docker-compose.yml
- docker compose up -d
- After all containers start (may take a minute) goto http://localhost:8080
- On the Multiple File Upload select all the files from the Viavi service monitor 
you want to import and press "Submit"
- Once the upload completes you can press the phpMyAdmin button to open the DB
- Navigate to the viavi database
- DB user is "viavi" and password is "8800SX" and then to the alignments table and browse the records

See the little documentation written into script.

