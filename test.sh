#!/bin/bash

set -e

# Dependencies
# sudo apt-get install jq

json2user () {
  echo $(echo "$1" | jq '.user')
}
json2id() {
  echo $(echo "$1" | jq '.id')
}
json2data() {
  echo $(echo "$1" | jq '.data')
}
assert_eq() {
  if [[ "$1" == "$2" ]]; then
    echo -e "\033[1;32mPASSED\033[0m"
    return 0
  else
    echo -e "\033[1;31mFAILED: $1 != $2\033[0m"
    return 1
  fi
}
assert_ne() {
  if [[ "$1" != "$2" ]]; then
    echo -e "\033[1;32mPASSED\033[0m"
    return 0
  else
    echo -e "\033[1;31mFAILED: $1 == $2\033[0m"
    return 1
  fi
}

# Get two user IDs 
echo "Acquiring user IDs..."
RETA=$(curl -s -X GET "shortbus.njoubert.com")
RETB=$(curl -s -X GET "shortbus.njoubert.com")
RETC=$(curl -s -X GET "shortbus.njoubert.com")
echo "... result: $RETA"
echo "... result: $RETB"
echo "... result: $RETC"
USERA=$(json2user "$RETA")
IDA=$(json2id "$RETA")
USERB=$(json2user "$RETB")
IDB=$(json2id "$RETB")
USERC=$(json2user "$RETC")
IDC=$(json2id "$RETC")

#Send two messages from each user
send_two_messages() {
  echo "Sending two messages from User $1"
  RET1=$(curl -s -X POST -d "{\"msg\":\"hello from User $1\"}" "shortbus.njoubert.com?user=$1")
  echo "... result: $RET1"
  RET2=$(curl -s -X POST -d "\"hello from User $1 again\"" "shortbus.njoubert.com?user=$1")
  echo "... result: $RET2"
}
send_two_messages $USERA 
send_two_messages $USERB 
send_two_messages $USERC 

#Now request messages from each user
assert_poll_for_user() {
  echo "Polling for User $1 from ID $2"
  ret=$(curl -s -X GET "shortbus.njoubert.com?user=$1&id=$2")
  echo "... result: $ret"
  users=$(echo $ret | jq -r '.[].user')
  expected="$3 $3 $4 $4"
  if [[ "${users//[$' \t\n\r']/}" == "${expected//[$' \t\n\r']/}" ]]; then
    echo -e "\033[1;32mPASSED\033[0m"
  else
    echo -e "\033[1;31mFAILED\033[0m"
  fi
}
assert_poll_for_user $USERA $IDA $USERB $USERC 
assert_poll_for_user $USERB $IDB $USERA $USERC
assert_poll_for_user $USERC $IDC $USERA $USERB

