#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

absscript=$(cd ${0%/*} && echo $PWD/${0##*/})

if [ $# -lt 1 ]; then

  echo -e "${RED}Not enough arguments"
  echo -e "${RED}Production:${GREEN} nohup $absscript ${RED}port${GREEN} >/dev/null 2>&1 &"
  
  echo -e "${RED}port MUST be the same as ther one from HL7server.ini"
  echo -e "${RED}Development:${GREEN} $0 ${RED}port"
  echo -e "${RED}Development direct php file call: ${GREEN}php socketServerISPC.php --port ${RED}port${GREEN} >> socketServerISPC_$PORT.log"
  echo -e "${RED}To get pid for port:${GREEN} lsof -ti tcp:${RED}port"
  echo -e "${RED}php script logs are in : ${GREEN}socketServerISPC_${RED}port${GREEN}.log"
  echo -e "${RED}To close socket from php run:${GREEN}  kill -9 \$(lsof -ti tcp:${RED}port${GREEN})${NC}"
  
  exit 2
fi

# port MUST be the same as ther one from HL7server.ini
PORT=$1




# why this cd ? you need the pwd?
cd "$(dirname "$0")"

while [ 1 ]
do
  
  echo "$(date) [Starting socketServerISPC for HL7 on port $PORT]"
  echo "$(date) [See socketServerISPC_$PORT.log]"
  
  nohup php socketServerISPC.php --port $PORT >> socketServerISPC_$PORT.log 2>&1 &
  wait
  
  echo "$(date) [PROCESS $PROC_ID ENDED ON PORT $PORT]"
  
  echo "$(date) [SLEEPING 30s]"
  sleep 30s
  
done

echo "$(date)  how/why you here?"