################################################################################
## Script created by James Cleaver - J.A.F.C.P.
## 2019-11-17
################################################################################
## File location for New-OSTicket PowerShell Module from github (https://github.com/AndyDLP/New-OSTicket)
$NewOSTLocation = "Z:\Dropbox\MY_GIT_REPO\WindowsPowerShell\Modules\New-OSTicket\New-OSTicket.psm1"
## Change folder path to mimic the file locations from the USB drive of the service monitor.  Had to assign the thumbdrive a fixed drive letter
$XTS = "P:\Aeroflex\results\MotoXTS"
$APX = "P:\Aeroflex\results\MotoAPX"
$ARC = "P:\Aeroflex\results\ARCHIVE\"

Function New-Audit {
param(
    [Parameter(Mandatory=$true)]
    [string]$tag,
    [Parameter(Mandatory=$false)]
    [string]$note,
#    [Parameter(Mandatory=$true)]
#    [int]$location_id,
    [Parameter(Mandatory=$true)]
    [string]$APIToken,
    [Parameter(Mandatory=$false)]
    [string]$URI
)
    $PostData = @{
        asset_tag = $tag
        note = $note ## "Audit entry via SX8800 Service Monitor"
       ##location_id = $location_id
        }

    $Authorization = "Bearer $APIToken"
    $Body = $PostData | ConvertTo-Json
    $PostParams = @{
        URI = $URI
        Header   = @{
            Authorization = $Authorization
            Accept = "application/json"
            'Content-Type' = 'application/json'
        }
        Method = "post"
        ContentType = "application/json; charset=utf-8"
        Body = [System.Text.Encoding]::UTF8.GetBytes($Body)
    }
    Invoke-WebRequest @PostParams
}

function AddFiles {
    param ([string]$Folder)
    Get-ChildItem -Path "$Folder" -ErrorAction Stop | ForEach-Object {
        $File = $_.FullName
        $this = $_.BaseName.split('-')
        $Model = $this[0]
        $Serial = $this[1]
        $Date = $this[2]
        $Time = $this[3]
        $Type = $this[4]
        $Last = $Folder.Split('\')[-1]
       
       
        #########################################
        ## Send information to create a case in 
        ## OSTicket via API using New-OSTicket 
        ## on your server
        #########################################
        If ((Get-Item $File).Length -eq 0kb) {
            Write-Output "File $File - WAS 0KB - NEXT"
            return
        }
        if ((Test-Path -Path $File) -AND (Test-Path -Path ($arc + $_.Name))) {
            Write-Output "File $File - ALREADY EXISTS IN ARCHIVE - NEXT"
            return
        }
        if ($Model -eq $Last) {
            Write-Output "File $File - SERVICE MONITOR DB FILE - NEXT"
            return
        }
        $Message = Get-Content $File -Delimiter ('-' * 80) | Select-Object -last 1
        Import-Module $NewOSTLocation
        $apiKey = "your_OSTicket_API_key"  // Your OS Ticket API token
        $emaiTitle = "your_Email_title_goes_here" // email title
        $ticketName = "Test and Align"  //Name of the ticket
        $server = "example.com/osticket"  // url of OST server.
        $subject = "Radio Tested and Aligned" // Subject of ticket
        $phone = "phone number" // Phone number you want on ticket 1-xxx-xxx-xxxx
        $topicID = "10" // ID from OST you want the ticket assigned to.  May have to go lookin for this one.
        New-OSTicket -ApiKey $apiKey -Email $emaiTitle -Message $Message -Name $ticketName -Server $server -Subject $subject -Alert $False -AutoRespond $False -MessageType text/html -PhoneNumber $PhoneNumber -Priority $OSTPriority -TicketSource API -TopicId $topicID -CustomFields @{"sn" = "$Serial" } -Attachments $File
       
        
        #########################################################
        ## Write information to mySql DB on your server
        ######################################################### 
        $Content = Get-Content $File -Raw
        $MySQLUserName = 'username_goes_here'
        $MySQLPassword = 'your_password_goes_here'
        $MySQLDatabase = 'motorola'  //change to fit your needs
        $MySQLHost = 'localhost'  //ip or server name of Mysql server
        $ConnectionString = "server=" + $MySQLHost + "; port=3306; uid=" + $MySQLUserName + "; pwd=" + $MySQLPassword + "; database=" + $MySQLDatabase + "; SslMode=none"
        $Query = "INSERT into alignment (serial, model, date, time, type, textfile) VALUES ('$Serial', '$Model', '$Date', '$Time', '$Type', '$Content');"

        ## Write $Query to mySql DB
        Try {
            [void][System.Reflection.Assembly]::LoadWithPartialName("MySql.Data")
            $Connection = New-Object MySql.Data.MySqlClient.MySqlConnection
            $Connection.ConnectionString = $ConnectionString
            $Connection.Open()
            $Command = New-Object MySql.Data.MySqlClient.MySqlCommand($Query, $Connection)
            $DataAdapter = New-Object MySql.Data.MySqlClient.MySqlDataAdapter($Command)
            $DataSet = New-Object System.Data.DataSet
            $RecordCount = $dataAdapter.Fill($dataSet, "data")
            $DataSet.Tables[0]
        }
        Catch {
            Write-Host "ERROR : Unable to run query : $Query `n$Error[0]"
        }
        Finally {
            $Connection.Close()
        }

        ##  Output $Query to powershell script
        $Query >$null 2>&1


        #########################################
        ##  Output to Snipe-IT
        ## One time only install: (requires an admin PowerShell window)
        # Install-Module SnipeitPS
        ## Check for updates occasionally:
        # Update-Module SnipeitPS
        ## To use each session:
        #########################################
        $apiKey = "your_snipe-it_api_key"
        Import-Module SnipeitPS
        Set-Info -URL 'http://example.com' -apiKey $apiKey //change the url to fit your needs
        $snipe = Get-Asset -search $Serial
        $snipeDate = $Date.Substring(4,4)
        $snipeDate+= "-"
        $snipeDate+= $Date.Substring(0,2)
        $snipeDate+= "-"
        $snipeDate+= $Date.Substring(2,2)
        New-AssetMaintenance -asset_id $snipe.id -asset_maintenance_type Calibration -start_date $snipeDate -supplier_id 2 -title "Subscriber Test and Align" -completion_date $snipeDate -notes "Auto import from 8800SX"
        Write-Output "MAINT for asset tag $snipe.ID - ENTERED"
        New-Audit -tag $snipe.asset_tag -note "Audit entry via SX8800 Service Monitor" -APIToken $apiKey -URI "http://example.com/api/v1/hardware/audit"
        Write-Output "AUDIT for asset tag $snipe.asset_tag - ENTERED" 


        #########################################
        ## Copy the file to the archive if NOT already there.
        #########################################
        If (-NOT (Test-Path -Path ($arc + $_.Name))) {
            Copy-Item -Path $File -Destination P:\Aeroflex\results\ARCHIVE
            Write-Output "File $File - SCRIPT SUCEEDED - MOVED" 
        }
        else {
            Write-Output "!!! File $File - SCRIPT SUCEEDED - NOT MOVED !!!"
        }
    }
}


#########################################
##  Call the function for the folder
#########################################
AddFiles -Folder $APX
AddFiles -Folder $XTS