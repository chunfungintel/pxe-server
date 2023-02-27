echo %DATE%-%TIME% Entering setupcomplete.cmd >> C:\Users\Public\setupcomplete.log
echo %DATE%-%TIME% Entering setupcomplete.cmd >> C:\WINDOWS\setupcomplete.log


$url = "https://raw.githubusercontent.com/ansible/ansible/devel/examples/scripts/ConfigureRemotingForAnsible.ps1"
$file = "$env:temp\ConfigureRemotingForAnsible.ps1"

(New-Object -TypeName System.Net.WebClient).DownloadFile($url, $file)

powershell.exe -ExecutionPolicy ByPass -File $file

echo %DATE%-%TIME% Exiting setupcomplete.cmd >> C:\Users\Public\setupcomplete.log
echo %DATE%-%TIME% Exiting setupcomplete.cmd >> C:\WINDOWS\setupcomplete.log
