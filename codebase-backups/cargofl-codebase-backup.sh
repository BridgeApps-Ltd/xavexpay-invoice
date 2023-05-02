#!/bin/bash

# uplaod to platform-scripts in bridgeapps github codebase
for i in `cat codebase-list.ini` do
  echo $i;
  downloadcodebase;
  createzip;
  uploadtos3;

done

function downloadcodebase

function createzip

function uploadtos3
