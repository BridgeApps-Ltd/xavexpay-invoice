#!/bin/bash
red=`tput setaf 1`
green=`tput setaf 2`
yellow=`tput setaf 3`
white=`tput setaf 7`
#To check tomcat is running or not
 echo ' '
 TOMCAT=`service tomcat status | grep -o "running"`
 if [[ $TOMCAT == "running" ]]
 then
 echo "${white}1. Tomcat for UATX1 is ${green}active (running)"
 echo "${white}Health status = ${green}[ok]"
 else
 echo "${white}1. Tomcat for UATX1 is not running"
 echo "Health status = ${red}[failure]"
 echo "${white}Restarting tomcat"
       service tomcat start
 echo "Tomcat restarted"
 fi
 echo "${yellow}-------------------------------------"
#To check Nginx is running or not
 NGINX=`service nginx status | grep -o "running"`
 if [[ $NGINX == "running" ]]
 then
 echo "${white}2. Nginx is running"
 echo "Health status = ${green}[ok]"
 else
 echo "${white}2. Nginx is not running"
 echo "Health status = ${red}[failure]"
 echo "${white}Restarting nginx webserver"
       service nginx start
 echo "nginx restarted"

 fi
 echo "${yellow}-------------------------------------"

#To check PHP_FPM running or not
 PHP_FPM=`service php7.2-fpm status | grep -o "running"`
 if [[ $PHP_FPM == "running" ]]
 then
 echo "${white}3. PHP-FPM is running"
 echo "Health status = ${green}[ok]"
 else
 echo "${white}3. PHP-FPM is not running"
 echo "Health status = ${red}[failure]"
 echo "${white}Restarting php7.2-fpm"
       service php7.2-fpm start
 echo "php7.2-fpm restarted"
 fi
 echo "${yellow}-------------------------------------"

