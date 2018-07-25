#!/bin/bash
if [ -f /var/minecraft_server_manager/.shutdown-server ]; then
  rm -f /var/minecraft_server_manager/.shutdown-server
if [ -f /var/minecraft_server_manager/.shutdown-server ]; then
   echo "Can't remove file .shutdown-server"
else
   /sbin/shutdown -r now
fi
fi
