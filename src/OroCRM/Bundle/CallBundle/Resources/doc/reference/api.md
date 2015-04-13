Call API
--------

### Rest API

#### Create new call

`POST /api/rest/{version}/calls.{_format}`

###### Requirements:
  `N/A`

###### Filters:
  `N/A`

It is possible to associate new calls with other entities by passing them in the "associations" field.
Data is passed in array with keys:
- "entityName" - fully qualified entity name
- "entityId" - entity record Id in the database
- "type" - (optional) association type

###### Sample request:

URL:
  `POST /api/rest/latest/calls.json`

Content:
 - PHP
```php
$request = [
    "call" => [
        "subject"      => 'Subject',
        "owner"        => 1,
        "duration"     => '00:00:05',
        "direction"    => 'outgoing', // can be 'outgoing' or 'incoming'
        "callDateTime" => '2015-01-01T12:00:00',
        "phoneNumber"  => '123-123-123',
        "callStatus"   => 'completed', // can be 'completed' or 'in_progress'
        "associations" => [
            [
                "entityName" => 'Oro\Bundle\UserBundle\Entity\User',
                "entityId"   => 1,
                "type"       => 'activity'
            ],
            [
                "entityName" => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
                "entityId"   => 2,
                "type"       => 'activity'
            ],
        ]
    ]
];

```
 - URL encoded

```
call[subject]=Subject&call[owner]=1&call[callDateTime]=2015-01-01T12:00:00&call[phoneNumber]=123-123-123&call[direction]=outgoing&call[duration]=00:00:05&call[callStatus]=completed&call[associations][0][entityName]=Oro%5CBundle%5CUserBundle%5CEntity%5CUser&call[associations][0][entityId]=1&call[associations][0][type]=activity&call[associations][1][entityName]=OroCRM%5CBundle%5CContactBundle%5CEntity%5CContact&call[associations][1][entityId]=2
```

###### Sample response:

Status:
  `201 Created`

Content:
```
 {
   "id": 110
 }
```


#### Receive the list of calls

`GET /api/rest/{version}/calls.{_format}`

###### Requirements:
  `N/A`

###### Filters:
  `page`:  Page number, starting from 1. Defaults to 1.
  `limit`: Number of items per page. defaults to 10.

###### Sample request:

URL:
  `GET /api/rest/latest/calls.json`

Content:
  `N/A`

###### Sample response:

Status:
  `200 OK`

Content:
```json
[
  {
    "id": 1,
    "subject": "The lease of office space",
    "phoneNumber": "548-146-4418",
    "notes": null,
    "callDateTime": "2015-04-09T13:47:43+00:00",
    "duration": "1970-01-01T01:26:07+00:00",
    "createdAt": "2015-04-09T13:47:43+00:00",
    "updatedAt": "2015-04-09T13:47:43+00:00",
    "owner": "michael.wagner_39d92",
    "callStatus": null,
    "direction": "Incoming",
    "organization": "OroCRM"
  },
  {
    "id": 2,
    "subject": "Happy Birthday",
    "phoneNumber": "307-204-8559",
    "notes": null,
    "callDateTime": "2015-04-09T13:47:43+00:00",
    "duration": "1970-01-01T00:18:05+00:00",
    "createdAt": "2015-04-09T13:47:43+00:00",
    "updatedAt": "2015-04-09T13:47:43+00:00",
    "owner": "michael.hodges_97152",
    "callStatus": null,
    "direction": "Outgoing",
    "organization": "OroCRM"
  }
]
```


#### Receive call item

`GET /api/rest/{version}/calls/{id}.{_format}`

###### Requirements:
 `id`: call record Id in the database

###### Filters:
 `N/A`

###### Sample request:

URL:
  `GET /api/rest/latest/calls/50.json`

Content:
  `N/A`

###### Sample response:

Status:
 `200 OK`

Content:
 ```json
 {
   "id": 50,
   "subject": "The lease of office space",
   "phoneNumber": "969-943-7424",
   "notes": null,
   "callDateTime": "2015-04-09T13:47:43+00:00",
   "duration": "1970-01-01T01:06:43+00:00",
   "createdAt": "2015-04-09T13:47:43+00:00",
   "updatedAt": "2015-04-09T13:47:43+00:00",
   "owner": "william.jacobs_9108d",
   "callStatus": null,
   "direction": "Incoming",
   "organization": "OroCRM"
 }
 ```



#### Update call item

`PUT /api/rest/{version}/calls/{id}.{_format}`

###### Requirements:
 `id`: call record Id in the database

###### Filters:
 `N/A`

Update the provided fields in the call record. Request format is identical to call creation API.

###### Sample request:

URL:
  `PUT /api/rest/latest/calls/51.json`

Content:
- PHP
```php
$request = [
    "call" => [
        "callStatus" => 'completed'
    ]
];

```
- URL encoded

```
call[callStatus]=completed
```

###### Sample response:

Status:
  `204 No Content`

Content:
  `<empty>`


#### Delete call item

`GET /api/rest/{version}/calls/{id}.{_format}`

###### Requirements:
  `id`: call record Id in the database

###### Filters:
  `N/A`

###### Sample request:

URL:
  `DELETE /api/rest/latest/calls/100.json`

Content:
  `N/A`

###### Sample response:

Status:
  `204 No Content`

Content:
  `<empty>`
