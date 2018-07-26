#!/bin/bash
if [ -f ./.shutdown-server ]; then
  rm -f ./.shutdown-server
if [ -f ./.shutdown-server ]; then
   echo "Can't remove file .shutdown-server"
else
   /sbin/shutdown now
fi
fi
