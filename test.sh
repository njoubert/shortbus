#!/bin/bash

# Get two user IDs 
IDA=$(curl -s -X GET "shortbus.njoubert.com")
IDB=$(curl -s -X GET "shortbus.njoubert.com")
echo $IDA

#Send two messages from each user
KEY=$(curl -s -X POST -d "hello_from_a_1" "shortbus.njoubert.com?user=$IDA")
echo $KEY
curl -s -X POST -d "hello_from_a_2" "shortbus.njoubert.com?user=$IDA"
curl -s -X POST -d "hello_from_b_1" "shortbus.njoubert.com?user=$IDB"
curl -s -X POST -d "hello_from_b_2" "shortbus.njoubert.com?user=$IDB"

#Now request messages from each user
RECA=$(curl -s -X GET "shortbus.njoubert.com?user=$IDA&id=$KEY")
RECB=$(curl -s -X GET "shortbus.njoubert.com?user=$IDA&id=$KEY")

echo "User A received:"
echo $RECA

echo "User B received:"
echo $RECB
