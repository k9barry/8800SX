Powershell script to read the output from the Viavi 8800SX service monitor
and create service tickets in OS Ticket for each radio aligned.
Output is then directed to a MySQL database for archiving.
Then finally, an entry is entered in to Snipe-IT for the asset in the history tab

See the little documentation written into script.

REQUIREMENTS
* Powershell 5 (or higher - not tested)
* New-osTicket (https://github.com/AndyDLP/New-OSTicket)
* MySQL Connector (https://dev.mysql.com/downloads/connector/net/)
* Snipe-IT (github  https://github.com/snipe/snipe-it)
