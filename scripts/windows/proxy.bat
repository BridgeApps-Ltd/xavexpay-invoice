WINDOWS script to inject proxy config in Internet Settings
---------------------------------------------

@echo off
set username=<add proxy username>
set password=<add proxy password>
set proxy=<add proxy hostname:port>

reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Internet Settings" /v ProxyServer /t REG_SZ /d %proxy% /f
reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Internet Settings" /v ProxyEnable /t REG_DWORD /d 1 /f
reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Internet Settings" /v ProxyUser /t REG_SZ /d %username% /f
reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Internet Settings" /v ProxyPass /t REG_SZ /d %password% /f

net stop winhttp
net start winhttp
