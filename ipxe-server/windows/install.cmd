@echo off
title Installing Windows
echo Currently doing something

set sambaserver=10.226.76.19\Windows
set sambauser=installer
set sambapw=1234

set sambafile=10.226.76.19\Windows-install
set sambafileuser=installer
set sambafilepw=1234

wpeinit
wpeutil WaitForNetwork

net start dnscache
net use m: \\%sambaserver% /user:%sambauser% %sambapw%
dir m:
net use n: \\%sambafile% /user:%sambafileuser% %sambafilepw%
dir n:

wmic logicaldisk get caption
rem set drive=D:
rem mountvol %drive% /p


rem \\10.226.76.19\Windows\setup.exe
\\%sambaserver%\setup.exe /Unattend:\\%sambafile%\autounattend.xml /noreboot

mkdir e:\Windows\Setup\ansible
rem xcopy /s /i /Y \\%sambaserver%\Windows\SetupComplete.cmd e:\Windows\Setup\Scripts
xcopy /s /i /Y \\%sambafile%\SetupComplete.ps1 e:\Windows\Setup\ansible

net use m: /delete
rem pause
