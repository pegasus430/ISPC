#!/bin/bash

cd "$(dirname "$0")"

sleep 2m

echo "starting Server for HL7-ADTs on 12002..."
while [ 1 != 2 ]
do
  date
  php socketserve.php 12002
  sleep 10s
done
