# Shortbus - a short broadcasting messaging bus

Sick of importing socket.io? Missing the good days of dropping a PHP file on a blank webserver and it just works? Here's your one-file PHP implementation of a broadcasting message bus that speaks JSON.

* Single-server
* Single-store
* No-auth

## Features

* Message TTL of 1 minute (configurable)
* Message max size of 10Kb (configurable)
* Message is JSON
* Immutable messages
* Does not guarantee delivery
* Guarantees ordering (based on server arrival)
* HTTP verbs interface
* Requires only PHP and Memcached
* Poll for new messages

## API

```
GET - returns fresh user
GET?id=id&user=user - returns [(id: id, user: !user, data: data) ... (id: last_id, user: !user, data: data)]
POST?user=user[data] - stores (id: ++last_id, user:user, data:data]), returns ++last_id
```
## TODO

* Implement auth. Can use a simple [JWT](https://en.wikipedia.org/wiki/JSON_Web_Token) inspired approach.

## Design

Each user can send arbitrary messages to the bus. The bus queues all messages for the full TTL. Each user polls the bus for messages that has arrived after its last seen id. The bus returns all messages newer than the requested ID. The bus hands out user ids, and can filter the return stream that were sent by other users. 

Users has to track two stateful items: (user, last_id). 

Yes, users can easily impersonate each other by sending the other's user id. 

## Implementation

Each message is stored as a memcached `(prefix::id)->(user,data)` entry, leveraging memcached's TTL expiry feature.




