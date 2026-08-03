# Loops PHP Package

[![Packagist Total Downloads](https://img.shields.io/packagist/dt/loops-so/loops?style=social)](https://packagist.org/packages/loops-so/loops)

## Introduction

This is the official PHP package for [Loops](https://loops.so), an email platform for modern software companies.

## Installation

Install the Loops package [using Composer](https://packagist.org/packages/loops-so/loops):

```bash
composer require loops-so/loops
```

Requires PHP 8.1.

## Usage

You will need a Loops API key to use the package.

In your Loops account, go to the [API Settings page](https://app.loops.so/settings?page=api) and click **Generate key**.

Copy this key and save it in your application code (for example, in an environment variable).

See the API documentation to learn more about [rate limiting](https://loops.so/docs/api-reference#rate-limiting) and [error handling](https://loops.so/docs/api-reference#debugging).

To use the package, first initialise the client with your API key, then you can call one of the methods.

API rate limits can be handled with the included `RateLimitExceededError` exception.

```php
use Loops\LoopsClient;

$loops = new LoopsClient(env('LOOPS_API_KEY'));

// Test API key
$result = $loops->apiKey->test();

// Create a contact and catch errors
try {
    $result = $loops->contacts->create('user@example.com', [
      'firstName' => 'John'
    ]);
} catch (Loops\Exceptions\APIError $e) {
    // Handle API errors (400, 401, 403, etc)
    echo $e->getMessage();
    $returnedJson = $e->getJson(); // JSON returned by the API
    $statusCode = $e->getStatusCode(); // HTTP status code from the response
} catch (\Exception $e) {
    // Handle any other unexpected errors
    echo "Unexpected error: " . $e->getMessage();
}
```

## Handling rate limits

You can use the check for rate limit issues with your requests.

You can access details about the rate limits from the `getLimit` and `getRemaining` functions.

```php
try {
    $result = $loops->contacts->create('user@example.com', [
      'firstName' => 'John'
    ]);
} catch (Loops\Exceptions\RateLimitExceededError $e) {
    // Handle rate limiting
    echo "Rate limit hit. Limit: " . $e->getLimit() . ", requests remaining: " . $e->getRemaining();
} catch (Loops\Exceptions\APIError $e) {
    // Handle API errors (400, 401, 403, etc)
    echo $e->getMessage();
} catch (\Exception $e) {
    // Handle any other unexpected errors
    echo "Unexpected error: " . $e->getMessage();
}
```

## Default contact properties

Each contact in Loops has a set of default properties. These will always be returned in API results.

- `id`
- `email`
- `firstName`
- `lastName`
- `source`
- `subscribed`
- `userGroup`
- `userId`
- `optInStatus`

## Custom contact properties

You can use custom contact properties in API calls. Please make sure to [add custom properties](https://loops.so/docs/contacts/properties#custom-contact-properties) in your Loops account before using them with the SDK.

## Methods

- [apiKey->test()](#apikey-test)
- [contacts->create()](#contacts-create)
- [contacts->update()](#contacts-update)
- [contacts->find()](#contacts-find)
- [contacts->delete()](#contacts-delete)
- [contacts->checkSuppression()](#contacts-checksuppression)
- [contacts->removeSuppression()](#contacts-removesuppression)
- [contactProperties->create()](#contactproperties-create)
- [contactProperties->list()](#contactproperties-list)
- [mailingLists->list()](#mailinglists-list)
- [events->send()](#events-send)
- [transactional->send()](#transactional-send)
- [transactional->list()](#transactional-list)
- [transactional->create()](#transactional-create)
- [transactional->get()](#transactional-get)
- [transactional->update()](#transactional-update)
- [transactional->ensureDraft()](#transactional-ensuredraft)
- [transactional->publish()](#transactional-publish)
- [dedicatedSendingIps->list()](#dedicatedsendingips-list)
- [themes->list()](#themes-list)
- [themes->get()](#themes-get)
- [themes->create()](#themes-create)
- [themes->update()](#themes-update)
- [components->list()](#components-list)
- [components->get()](#components-get)
- [components->create()](#components-create)
- [components->update()](#components-update)
- [campaigns->list()](#campaigns-list)
- [campaigns->create()](#campaigns-create)
- [campaigns->get()](#campaigns-get)
- [campaigns->update()](#campaigns-update)
- [campaignGroups->list()](#campaigngroups-list)
- [campaignGroups->create()](#campaigngroups-create)
- [campaignGroups->get()](#campaigngroups-get)
- [campaignGroups->update()](#campaigngroups-update)
- [audienceSegments->list()](#audiencesegments-list)
- [audienceSegments->create()](#audiencesegments-create)
- [audienceSegments->get()](#audiencesegments-get)
- [workflows->list()](#workflows-list)
- [workflows->create()](#workflows-create)
- [workflows->get()](#workflows-get)
- [workflows->update()](#workflows-update)
- [workflows->changeMailingList()](#workflows-changemailinglist)
- [workflows->getNode()](#workflows-getnode)
- [workflows->createNode()](#workflows-createnode)
- [workflows->updateNode()](#workflows-updatenode)
- [workflows->deleteNode()](#workflows-deletenode)
- [workflows->addBranch()](#workflows-addbranch)
- [workflows->deleteNodeRecursive()](#workflows-deletenoderecursive)
- [eventPatterns->list()](#eventpatterns-list)
- [eventPatterns->get()](#eventpatterns-get)
- [eventPatterns->getByName()](#eventpatterns-getbyname)
- [emailMessages->get()](#emailmessages-get)
- [emailMessages->update()](#emailmessages-update)
- [emailMessages->preview()](#emailmessages-preview)
- [emailMessages->guardian()](#emailmessages-guardian)
- [transactionalGroups->list()](#transactionalgroups-list)
- [transactionalGroups->create()](#transactionalgroups-create)
- [transactionalGroups->get()](#transactionalgroups-get)
- [transactionalGroups->update()](#transactionalgroups-update)
- [uploads->upload()](#uploads-upload)

---

### apiKey->test()

Test if your API key is valid.

[API Reference](https://loops.so/docs/api-reference/api-key)

#### Parameters

None

#### Example

```php
$result = $loops->apiKey->test();
```

#### Response

This method will return a success or error message:

```json
{
  "success": true,
  "teamName": "Company name"
}
```

```json
{
  "error": "Invalid API key"
}
```

---

### contacts->create()

Create a new contact.

[API Reference](https://loops.so/docs/api-reference/create-contact)

#### Parameters

| Name             | Type   | Required | Notes                                                                                                                                                                                                                                                                                                                                                                                                               |
| ---------------- | ------ | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `$email`         | string | Yes      | If a contact already exists with this email address, an `APIError` will be thrown.                                                                                                                                                                                                                                                                                                                                  |
| `$properties`    | array  | No       | An array containing default and any custom properties for your contact.<br />Please [add custom properties](https://loops.so/docs/contacts/properties#custom-contact-properties) in your Loops account before using them with the SDK.<br />Values can be of type `string`, `number`, `null` (to reset a value), `boolean` or `date` ([see allowed date formats](https://loops.so/docs/contacts/properties#dates)). |
| `$mailing_lists` | array  | No       | An array of mailing list IDs and boolean subscription statuses.                                                                                                                                                                                                                                                                                                                                                     |

#### Examples

```php
$result = $loops->contacts->create("hello@gmail.com");

$contact_properties = [
  'firstName' => "Bob" /* Default property */,
  'favoriteColor' => "Red" /* Custom property */,
];
$mailing_lists = [
  'cm06f5v0e45nf0ml5754o9cix' => TRUE,
  'cm16k73gq014h0mmj5b6jdi9r' => FALSE,
];
$result = $loops->contacts->create(
  email: "hello@gmail.com",
  properties: $contact_properties,
  mailing_lists: $mailing_lists
);
```

#### Response

```json
{
  "success": true,
  "id": "cll6b3i8901a9jx0oyktl2m4u"
}
```

Error handling is done through the `APIError` class, which provides `getStatusCode()` and `getJson()` methods for retrieving the API's error response details. For implementation examples, see the [Usage section](#usage).

```json
// HTTP 400 Bad Request
{
  "success": false,
  "message": "An error message here."
}
```

---

### contacts->update()

Update a contact.

Note: To update a contact's email address, the contact requires a `$user_id` value. Then you can make a request with their `$user_id` and an updated email address.

[API Reference](https://loops.so/docs/api-reference/update-contact)

#### Parameters

| Name             | Type   | Required | Notes                                                                                                                                                                                                                                                                                                                                                                                                               |
| ---------------- | ------ | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `$email`         | string | No       | The email address of the contact to update. If there is no contact with this email address, a new contact will be created using the email and properties in this request. Required if `$user_id` is not present.                                                                                                                                                                                                    |
| `$user_id`       | string | No       | The contact's unique user ID. If you use `$user_id` without `$email`, this value must have already been added to your contact in Loops. Required if `$email` is not present.                                                                                                                                                                                                                                        |
| `$properties`    | array  | No       | An array containing default and any custom properties for your contact.<br />Please [add custom properties](https://loops.so/docs/contacts/properties#custom-contact-properties) in your Loops account before using them with the SDK.<br />Values can be of type `string`, `number`, `null` (to reset a value), `boolean` or `date` ([see allowed date formats](https://loops.so/docs/contacts/properties#dates)). |
| `$mailing_lists` | array  | No       | An array of mailing list IDs and boolean subscription statuses.                                                                                                                                                                                                                                                                                                                                                     |

#### Example

```php
$result = $loops->contacts->update(
  email: 'hello@gmail.com',
  properties: [
    'firstName' => 'Bob', /* Default property */
    'favoriteColor' => 'Blue' /* Custom property */
  ]
);

// Updating a contact's email address using $user_id
$result = $loops->contacts->update(
  user_id: '1234',
  email: 'newemail@gmail.com'
);

// Subscribe a contact to a mailing list
$result = $loops->contacts->update(
  email: 'hello@gmail.com',
  mailing_lists: [
    'cm06f5v0e45nf0ml5754o9cix' => true /* Subscribe */,
    'cm16k73gq014h0mmj5b6jdi9r' => false /* Unsubscribe */
  ]
);
```

#### Response

```json
{
  "success": true,
  "id": "cll6b3i8901a9jx0oyktl2m4u"
}
```

Error handling is done through the `APIError` class, which provides `getStatusCode()` and `getJson()` methods for retrieving the API's error response details. For implementation examples, see the [Usage section](#usage).

```json
// HTTP 400 Bad Request
{
  "success": false,
  "message": "An error message here."
}
```

---

### contacts->find()

Find a contact.

[API Reference](https://loops.so/docs/api-reference/find-contact)

#### Parameters

You must use one parameter in the request.

| Name       | Type   | Required | Notes |
| ---------- | ------ | -------- | ----- |
| `$email`   | string | No       |       |
| `$user_id` | string | No       |       |

#### Examples

```php
$result = $loops->contacts->find(email: 'hello@gmail.com');

$result = $loops->contacts->find(user_id: '12345');
```

#### Response

This method will return a list containing a single contact object, which will include all default properties and any custom properties.

If no contact is found, an empty list will be returned.

```json
[
  {
    "id": "cll6b3i8901a9jx0oyktl2m4u",
    "email": "hello@gmail.com",
    "firstName": "Bob",
    "lastName": null,
    "source": "API",
    "subscribed": true,
    "userGroup": "",
    "userId": "12345",
    "mailingLists": {
      "cm06f5v0e45nf0ml5754o9cix": true
    },
    "optInStatus": null,
    "favoriteColor": "Blue" /* Custom property */
  }
]
```

---

### contacts->delete()

Delete a contact.

[API Reference](https://loops.so/docs/api-reference/delete-contact)

#### Parameters

You must use one parameter in the request.

| Name       | Type   | Required | Notes |
| ---------- | ------ | -------- | ----- |
| `$email`   | string | No       |       |
| `$user_id` | string | No       |       |

#### Example

```php
$result = $loops->contacts->delete(email: 'hello@gmail.com')

$result = $loops->contacts->delete(user_id: '12345')
```

#### Response

```json
{
  "success": true,
  "message": "Contact deleted."
}
```

Error handling is done through the `APIError` class, which provides `getStatusCode()` and `getJson()` methods for retrieving the API's error response details. For implementation examples, see the [Usage section](#usage).

```json
// HTTP 400 Bad Reuqest
{
  "success": false,
  "message": "An error message here."
}
```

```json
// HTTP 404 Not Found
{
  "success": false,
  "message": "An error message here."
}
```

---

### contacts->checkSuppression()

Check if a contact is currently suppressed.

[API Reference](https://loops.so/docs/api-reference/check-contact-suppression)

#### Parameters

You must use one parameter in the request.

| Name       | Type   | Required | Notes |
| ---------- | ------ | -------- | ----- |
| `$email`   | string | No       |       |
| `$user_id` | string | No       |       |

#### Example

```php
$result = $loops->contacts->checkSuppression(email: 'hello@gmail.com');

$result = $loops->contacts->checkSuppression(user_id: '12345');
```

#### Response

```json
{
  "contact": {
    "id": "cll6b3i8901a9jx0oyktl2m4u",
    "email": "adam@loops.so",
    "userId": null
  },
  "isSuppressed": true,
  "removalQuota": {
    "limit": 100,
    "remaining": 10
  }
}
```

Error handling is done through the `APIError` class, which provides `getStatusCode()` and `getJson()` methods for retrieving the API's error response details. For implementation examples, see the [Usage section](#usage).

```json
// HTTP 400 Bad Request
{
  "success": false,
  "message": "An email or userId is required."
}
```

```json
// HTTP 404 Not Found
{
  "success": false,
  "message": "This contact was not found."
}
```

---

### contacts->removeSuppression()

Remove suppression for a contact.

[API Reference](https://loops.so/docs/api-reference/remove-contact-suppression)

#### Parameters

You must use one parameter in the request.

| Name       | Type   | Required | Notes |
| ---------- | ------ | -------- | ----- |
| `$email`   | string | No       |       |
| `$user_id` | string | No       |       |

#### Example

```php
$result = $loops->contacts->removeSuppression(email: 'hello@gmail.com');

$result = $loops->contacts->removeSuppression(user_id: '12345');
```

#### Response

```json
{
  "success": true,
  "message": "Email removed from suppression list.",
  "removalQuota": {
    "limit": 100,
    "remaining": 4
  }
}
```

Error handling is done through the `APIError` class, which provides `getStatusCode()` and `getJson()` methods for retrieving the API's error response details. For implementation examples, see the [Usage section](#usage).

```json
// HTTP 400 Bad Request
{
  "success": false,
  "message": "This contact is not suppressed."
}
```

```json
// HTTP 404 Not Found
{
  "success": false,
  "message": "This contact was not found."
}
```

---

### contactProperties->create()

Create a new contact property.

[API Reference](https://loops.so/docs/api-reference/create-contact-property)

#### Parameters

| Name    | Type   | Required | Notes                                                                                  |
| ------- | ------ | -------- | -------------------------------------------------------------------------------------- |
| `$name` | string | Yes      | The name of the property. Should be in camelCase, like `planName` or `favouriteColor`. |
| `$type` | string | Yes      | The property's value type.<br />Can be one of `string`, `number`, `boolean` or `date`. |

#### Examples

```php
$result = $loops->contactProperties->create("planName", "string");
```

#### Response

```json
{
  "success": true
}
```

Error handling is done through the `APIError` class, which provides `getStatusCode()` and `getJson()` methods for retrieving the API's error response details. For implementation examples, see the [Usage section](#usage).

```json
// HTTP 400 Bad Request
{
  "success": false,
  "message": "An error message here."
}
```

---

### contactProperties->list()

Get a list of your account's contact properties.

[API Reference](https://loops.so/docs/api-reference/list-contact-properties)

#### Parameters

| Name    | Type   | Required | Notes                                                           |
| ------- | ------ | -------- | --------------------------------------------------------------- |
| `$list` | string | No       | Use "custom" to retrieve only your account's custom properties. |

#### Example

```php
$result = $loops->contactProperties->list();

$result = $loops->contactProperties->list(list: "custom");
```

#### Response

This method will return a list of contact property objects containing `key`, `label` and `type` attributes.

```json
[
  {
    "key": "firstName",
    "label": "First Name",
    "type": "string"
  },
  {
    "key": "lastName",
    "label": "Last Name",
    "type": "string"
  },
  {
    "key": "email",
    "label": "Email",
    "type": "string"
  },
  {
    "key": "notes",
    "label": "Notes",
    "type": "string"
  },
  {
    "key": "source",
    "label": "Source",
    "type": "string"
  },
  {
    "key": "userGroup",
    "label": "User Group",
    "type": "string"
  },
  {
    "key": "userId",
    "label": "User Id",
    "type": "string"
  },
  {
    "key": "subscribed",
    "label": "Subscribed",
    "type": "boolean"
  },
  {
    "key": "createdAt",
    "label": "Created At",
    "type": "date"
  },
  {
    "key": "favoriteColor",
    "label": "Favorite Color",
    "type": "string"
  },
  {
    "key": "plan",
    "label": "Plan",
    "type": "string"
  }
]
```

---

### mailingLists->list()

Get a list of your account's mailing lists. [Read more about mailing lists](https://loops.so/docs/contacts/mailing-lists)

[API Reference](https://loops.so/docs/api-reference/list-mailing-lists)

#### Parameters

None

#### Example

```php
$result = $loops->mailingLists->list();
```

#### Response

This method will return a list of mailing list objects containing `id`, `name`, `description` and `isPublic` attributes.

If your account has no mailing lists, an empty list will be returned.

```json
[
  {
    "id": "cm06f5v0e45nf0ml5754o9cix",
    "name": "Main list",
    "description": "All customers.",
    "isPublic": true
  },
  {
    "id": "cm16k73gq014h0mmj5b6jdi9r",
    "name": "Investors",
    "description": null,
    "isPublic": false
  }
]
```

---

### events->send()

Send an event to trigger an email in Loops. [Read more about events](https://loops.so/docs/events)

[API Reference](https://loops.so/docs/api-reference/send-event)

#### Parameters

| Name                  | Type   | Required | Notes                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| --------------------- | ------ | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `$event_name`         | string | Yes      |                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| `$email`              | string | No       | The contact's email address. Required if `$user_id` is not present.                                                                                                                                                                                                                                                                                                                                                                                           |
| `$user_id`            | string | No       | The contact's unique user ID. If you use `$user_id` without `$email`, this value must have already been added to your contact in Loops. Required if `$email` is not present.                                                                                                                                                                                                                                                                                  |
| `$contact_properties` | array  | No       | An array containing contact properties, which will be updated or added to the contact when the event is received.<br />Please [add custom properties](https://loops.so/docs/contacts/properties#custom-contact-properties) in your Loops account before using them with the SDK.<br />Values can be of type `string`, `number`, `null` (to reset a value), `boolean` or `date` ([see allowed date formats](https://loops.so/docs/contacts/properties#dates)). |
| `$event_properties`   | array  | No       | An array containing event properties, which will be made available in emails that are triggered by this event.<br />Values can be of type `string`, `number`, `boolean` or `date` ([see allowed date formats](https://loops.so/docs/events/properties#important-information-about-event-properties)).                                                                                                                                                         |
| `$mailing_lists`      | array  | No       | An array of mailing list IDs and boolean subscription statuses.                                                                                                                                                                                                                                                                                                                                                                                               |
| `$headers`            | array  | No       | Additional headers to send with the request.                                                                                                                                                                                                                                                                                                                                                                                                                  |

#### Examples

```php
$result = $loops->events->send(
  event_name: 'signup',
  email: 'hello@gmail.com'
);

$result = $loops->events->send(
  event_name: 'signup',
  email: 'hello@gmail.com',
  event_properties: [
    'username' => 'user1234',
    'signupDate' => '2024-03-21T10:09:23Z'
  ],
  mailing_lists: [
    'cm06f5v0e45nf0ml5754o9cix' => true,
    'cm16k73gq014h0mmj5b6jdi9r' => false
  ]
;

# In this case with both email and user_id present, the system will look for a contact with either a
#  matching `email` or `user_id` value.
# If a contact is found for one of the values (e.g. `email`), the other value (e.g. `user_id`) will be updated.
# If a contact is not found, a new contact will be created using both `email` and `user_id` values.
# Any values added in `contact_properties` will also be updated on the contact.
$result = $loops->events->send(
  event_name: 'signup',
  email: 'hello@gmail.com',
  user_id: '1234567890',
  contact_properties: [
    'firstName' => 'Bob',
    'plan' => 'pro',
  }]
});

# Example with Idempotency-Key header
$result = $loops->events->send(
  event_name: 'signup',
  email: 'hello@gmail.com',
  headers: [
    'Idempotency-Key' => '550e8400-e29b-41d4-a716-446655440000'
  ]
);
```

#### Response

```json
{
  "success": true
}
```

Error handling is done through the `APIError` class, which provides `getStatusCode()` and `getJson()` methods for retrieving the API's error response details. For implementation examples, see the [Usage section](#usage).

```json
// HTTP 400 Bad Request
{
  "success": false,
  "message": "An error message here."
}
```

---

### transactional->send()

Send a transactional email to a contact. [Learn about sending transactional email](https://loops.so/docs/transactional/guide)

[API Reference](https://loops.so/docs/api-reference/send-transactional-email)

#### Parameters

| Name                             | Type    | Required | Notes                                                                                                                                                                                            |
| -------------------------------- | ------- | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `$transactional_id`              | string  | Yes      | The ID of the transactional email to send.                                                                                                                                                       |
| `$email`                         | string  | Yes      | The email address of the recipient.                                                                                                                                                              |
| `$add_to_audience`               | boolean | No       | If `true`, a contact will be created in your audience using the `$email` value (if a matching contact doesn’t already exist).                                                                    |
| `$data_variables`                | array   | No       | An array containing data as defined by the data variables added to the transactional email template.<br />Values can be of type `string` or `number`.                                            |
| `$attachments`                   | array[] | No       | A list of attachments objects.<br />**Please note**: Attachments need to be enabled on your account before using them with the API. [Read more](https://loops.so/docs/transactional/attachments) |
| `$attachments[]["filename"]`     | string  | No       | The name of the file, shown in email clients.                                                                                                                                                    |
| `$attachments[]["contentType"]` | string  | No       | The MIME type of the file.                                                                                                                                                                       |
| `$attachments[]["data"]`         | string  | No       | The base64-encoded content of the file.                                                                                                                                                          |
| `$headers`                       | array   | No       | Additional headers to send with the request.                                                                                                                                                     |

#### Examples

```php
$result = $loops->transactional->send(
  transactional_id: 'clfq6dinn000yl70fgwwyp82l',
  email: 'hello@gmail.com',
  data_variables: [
    'loginUrl' => 'https://myapp.com/login/',
  ]
);

# Example with Idempotency-Key header
$result = $loops->transactional->send(
  transactional_id: 'clfq6dinn000yl70fgwwyp82l',
  email: 'hello@gmail.com',
  data_variables: [
    'loginUrl' => 'https://myapp.com/login/',
  ],
  headers: [
    'Idempotency-Key' => '550e8400-e29b-41d4-a716-446655440000'
  ]
);

# Please contact us to enable attachments on your account.
$result = $loops->transactional->send(
  transactional_id: 'clfq6dinn000yl70fgwwyp82l',
  email: 'hello@gmail.com',
  data_variables: [
    'loginUrl' => 'https://myapp.com/login/',
  ],
  attachments: [
    [
      'filename' => 'presentation.pdf',
      'contentType' => 'application/pdf',
      'data' => base64_encode(file_get_contents('path/to/presentation.pdf'))
    ]
  ]
);
```

#### Response

```json
{
  "success": true
}
```

Error handling is done through the `APIError` class, which provides `getStatusCode()` and `getJson()` methods for retrieving the API's error response details. For implementation examples, see the [Usage section](#usage).

If there is a problem with the request, a descriptive error message will be returned:

```json
// HTTP 400 Bad Request
{
  "success": false,
  "path": "dataVariables",
  "message": "There are required fields for this email. You need to include a 'dataVariables' object with the required fields."
}
```

```json
// HTTP 400 Bad Request
{
  "success": false,
  "error": {
    "path": "dataVariables",
    "message": "Missing required fields: login_url"
  },
  "transactionalId": "clfq6dinn000yl70fgwwyp82l"
}
```

---

### transactional->list()

Get a paginated list of transactional emails, most recently created first.

[API Reference](https://loops.so/docs/api-reference/list-transactional-emails)

#### Parameters

| Name        | Type    | Required | Notes                                                                                                                         |
| ----------- | ------- | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `$per_page` | integer | No       | How many results to return per page. Must be between 10 and 50. Defaults to 20 if omitted.                                    |
| `$cursor`   | string  | No       | A cursor, to return a specific page of results. Cursors can be found from the `pagination.nextCursor` value in each response. |

#### Example

```php
$result = $loops->transactional->list();

$result = $loops->transactional->list(per_page: 15);
```

#### Response

```json
{
  "pagination": {
    "totalResults": 23,
    "returnedResults": 20,
    "perPage": 20,
    "totalPages": 2,
    "nextCursor": "clyo0q4wo01p59fsecyxqsh38",
    "nextPage": "https://app.loops.so/api/v1/transactional-emails?cursor=clyo0q4wo01p59fsecyxqsh38&perPage=20"
  },
  "data": [
    {
      "id": "clfn0k1yg001imo0fdeqg30i8",
      "name": "Welcome email",
      "draftEmailMessageId": null,
      "publishedEmailMessageId": "cly8k3m0n0044jpx2bghepq45",
      "createdAt": "2023-11-06T17:48:07.249Z",
      "updatedAt": "2023-11-06T17:48:07.249Z",
      "dataVariables": []
    },
    {
      "id": "cll42l54f20i1la0lfooe3z12",
      "name": "Password reset",
      "draftEmailMessageId": "cla3r8s9t0422ua56iqovab01",
      "publishedEmailMessageId": "clb4s9t0u0533vb67jrpwbc12",
      "createdAt": "2025-01-15T10:00:00.000Z",
      "updatedAt": "2025-02-02T02:56:28.845Z",
      "dataVariables": [
        "confirmationUrl"
      ]
    },
    {
      "id": "clw6rbuwp01rmeiyndm80155l",
      "name": "Team invite",
      "draftEmailMessageId": "clc5t0u1v0644wc78ksqxcd23",
      "publishedEmailMessageId": null,
      "createdAt": "2024-05-14T19:02:52.000Z",
      "updatedAt": "2024-05-14T19:02:52.000Z",
      "dataVariables": [
        "firstName",
        "lastName",
        "inviteLink"
      ]
    },
    ...
  ]
}
```

---

### transactional->create()

Create a new transactional email. An empty draft email message is created automatically.

[API Reference](https://loops.so/docs/api-reference/create-transactional-email)

#### Parameters

| Name                       | Type   | Required | Notes                                                                                                                        |
| -------------------------- | ------ | -------- | ---------------------------------------------------------------------------------------------------------------------------- |
| `$name`                    | string | Yes      | The name of the transactional email.                                                                                         |
| `$transactional_group_id`  | string | No       | The ID of the group to add this transactional email to. Defaults to the team's default group when omitted.                   |

#### Example

```php
$result = $loops->transactional->create(name: 'Welcome email');
```

#### Response

```json
{
  "id": "clfq6dinn000yl70fgwwyp82l",
  "name": "Welcome email",
  "draftEmailMessageId": "cly8k3m0n0044jpx2bghepq45",
  "draftEmailMessageContentRevisionId": "clm9n4o6p0088lrz4dijslt67",
  "publishedEmailMessageId": null,
  "createdAt": "2025-01-01T00:00:00.000Z",
  "updatedAt": "2025-01-01T00:00:00.000Z",
  "dataVariables": []
}
```

---

### transactional->get()

Get a single transactional email by ID.

[API Reference](https://loops.so/docs/api-reference/get-transactional-email)

#### Parameters

| Name                | Type   | Required | Notes                            |
| ------------------- | ------ | -------- | -------------------------------- |
| `$transactional_id` | string | Yes      | The ID of the transactional email. |

#### Example

```php
$result = $loops->transactional->get(transactional_id: 'clfq6dinn000yl70fgwwyp82l');
```

---

### transactional->update()

Update a transactional email.

At least one field alongside `transactional_id` must be provided.

[API Reference](https://loops.so/docs/api-reference/update-transactional-email)

#### Parameters


| Name                       | Type   | Required | Notes                                                                                      |
| -------------------------- | ------ | -------- | ------------------------------------------------------------------------------------------ |
| `$transactional_id`        | string | Yes      | The ID of the transactional email.                                                         |
| `$name`                    | string | No       | The updated name.   |
| `$transactional_group_id`  | string | No       | The ID of the group to move this transactional email to.  |

#### Example

```php
$result = $loops->transactional->update(
  transactional_id: 'clfq6dinn000yl70fgwwyp82l',
  name: 'Updated welcome email'
);
```

---

### transactional->ensureDraft()

Ensure a transactional email has a draft email message. If a draft already exists it is returned unchanged; otherwise a new empty draft is created.

[API Reference](https://loops.so/docs/api-reference/ensure-transactional-email-draft)

#### Parameters

| Name                | Type   | Required | Notes                            |
| ------------------- | ------ | -------- | -------------------------------- |
| `$transactional_id` | string | Yes      | The ID of the transactional email. |

#### Example

```php
$result = $loops->transactional->ensureDraft(transactional_id: 'clfq6dinn000yl70fgwwyp82l');
```

---

### transactional->publish()

Publish the transactional email's current draft email message.

[API Reference](https://loops.so/docs/api-reference/publish-transactional-email)

#### Parameters

| Name                | Type   | Required | Notes                            |
| ------------------- | ------ | -------- | -------------------------------- |
| `$transactional_id` | string | Yes      | The ID of the transactional email. |

#### Example

```php
$result = $loops->transactional->publish(transactional_id: 'clfq6dinn000yl70fgwwyp82l');
```

---

### uploads->upload()

Upload an image asset for use in LMX email content. The returned `finalUrl` can be used in an `<Image>` tag in your [LMX content](https://loops.so/docs/creating-emails/lmx).

[API Reference](https://loops.so/docs/api-reference/create-upload)

#### Parameters

| Name    | Type   | Required | Notes                                                                                                           |
| ------- | ------ | -------- | --------------------------------------------------------------------------------------------------------------- |
| `$path` | string | Yes      | Path to an image file. Supported types: JPEG, PNG, GIF, and WebP. Maximum file size is 4,000,000 bytes (4 MB). |

#### Example

```php
$result = $loops->uploads->upload(path: '/path/to/image.png');

$imageUrl = $result['finalUrl'];
```

#### Response

```json
{
  "emailAssetId": "clu1v4w6x0254tz42lrcwat45",
  "finalUrl": "https://cdn.example.com/image.png"
}
```

---

### dedicatedSendingIps->list()

Get a list of Loops' dedicated sending IP addresses.

[API Reference](https://loops.so/docs/api-reference/get-dedicated-sending-ips)

#### Parameters

None

#### Example

```php
$result = $loops->dedicatedSendingIps->list();
```

#### Response

```json
["1.2.3.4", "5.6.7.8"]
```

---

### themes->list()

List email themes.

[API Reference](https://loops.so/docs/api-reference/list-themes)

#### Parameters

| Name        | Type    | Required | Notes                                                                                                                         |
| ----------- | ------- | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `$per_page` | integer | No       | How many results to return per page. Must be between 10 and 50. Defaults to 20 if omitted.                                    |
| `$cursor`   | string  | No       | A cursor, to return a specific page of results. Cursors can be found from the `pagination.nextCursor` value in each response. |

#### Example

```php
$result = $loops->themes->list();

$result = $loops->themes->list(per_page: 15, cursor: 'clyo0q4wo01p59fsecyxqsh38');
```

---

### themes->get()

Get a single email theme by ID.

[API Reference](https://loops.so/docs/api-reference/get-theme)

#### Parameters

| Name        | Type   | Required | Notes              |
| ----------- | ------ | -------- | ------------------ |
| `$theme_id` | string | Yes      | The ID of the theme. |

#### Example

```php
$result = $loops->themes->get(theme_id: 'clo5p8q0r0132ntx6flkunw89');
```

---

### themes->create()

Create an email theme.

[API Reference](https://loops.so/docs/api-reference/create-theme)

#### Parameters

| Name      | Type   | Required | Notes                                                                 |
| --------- | ------ | -------- | --------------------------------------------------------------------- |
| `$name`   | string | Yes      | The theme name.                                                       |
| `$styles` | array  | No       | Style attributes matching LMX `<Style />` attribute names.            |

#### Example

```php
$result = $loops->themes->create(
  name: 'Dark mode',
  styles: ['backgroundColor' => '#111827', 'bodyColor' => '#1f2937']
);
```

---

### themes->update()

Update a theme's name and/or styles. Style changes cascade to emails using the theme.

At least one of `$name` or `$styles` must be provided.

[API Reference](https://loops.so/docs/api-reference/update-theme)

#### Parameters

| Name        | Type   | Required | Notes                      |
| ----------- | ------ | -------- | -------------------------- |
| `$theme_id` | string | Yes      | The ID of the theme.       |
| `$name`     | string | No       | The updated theme name.    |
| `$styles`   | array  | No       | Updated style attributes.  |

#### Example

```php
$result = $loops->themes->update(
  theme_id: 'clo5p8q0r0132ntx6flkunw89',
  name: 'Updated theme'
);
```

---

### components->list()

List email components.

[API Reference](https://loops.so/docs/api-reference/list-components)

#### Parameters

| Name        | Type    | Required | Notes                                                                                                                         |
| ----------- | ------- | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `$per_page` | integer | No       | How many results to return per page. Must be between 10 and 50. Defaults to 20 if omitted.                                    |
| `$cursor`   | string  | No       | A cursor, to return a specific page of results. Cursors can be found from the `pagination.nextCursor` value in each response. |

#### Example

```php
$result = $loops->components->list();
```

---

### components->get()

Get a single email component by ID.

[API Reference](https://loops.so/docs/api-reference/get-component)

#### Parameters

| Name            | Type   | Required | Notes                   |
| --------------- | ------ | -------- | ----------------------- |
| `$component_id` | string | Yes      | The ID of the component. |

#### Example

```php
$result = $loops->components->get(component_id: 'clp6q9r1s0154ouy7gmlovx90');
```

---

### components->create()

Create an email component from an LMX body.

[API Reference](https://loops.so/docs/api-reference/create-component)

#### Parameters

| Name    | Type   | Required | Notes                             |
| ------- | ------ | -------- | --------------------------------- |
| `$name` | string | Yes      | The component name.               |
| `$lmx`  | string | Yes      | The component body as LMX.        |

#### Example

```php
$result = $loops->components->create(
  name: 'Header',
  lmx: '<Paragraph>Welcome to Acme</Paragraph>'
);
```

---

### components->update()

Update a component's name and/or LMX body. Body changes cascade to emails using the component.

At least one of `$name` or `$lmx` must be provided.

[API Reference](https://loops.so/docs/api-reference/update-component)

#### Parameters

| Name            | Type   | Required | Notes                        |
| --------------- | ------ | -------- | ---------------------------- |
| `$component_id` | string | Yes      | The ID of the component.     |
| `$name`         | string | No       | The updated component name.  |
| `$lmx`          | string | No       | The updated LMX body.        |

#### Example

```php
$result = $loops->components->update(
  component_id: 'clp6q9r1s0154ouy7gmlovx90',
  lmx: '<Paragraph>Updated header</Paragraph>'
);
```

---

### campaigns->list()

List campaigns.

[API Reference](https://loops.so/docs/api-reference/list-campaigns)

#### Parameters

| Name        | Type    | Required | Notes                                                                                                                         |
| ----------- | ------- | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `$per_page` | integer | No       | How many results to return per page. Must be between 10 and 50. Defaults to 20 if omitted.                                    |
| `$cursor`   | string  | No       | A cursor, to return a specific page of results. Cursors can be found from the `pagination.nextCursor` value in each response. |

#### Example

```php
$result = $loops->campaigns->list();
```

---

### campaigns->create()

Create a new draft campaign.

[API Reference](https://loops.so/docs/api-reference/create-campaign)

#### Parameters

| Name                    | Type   | Required | Notes                                                                                                 |
| ----------------------- | ------ | -------- | ----------------------------------------------------------------------------------------------------- |
| `$name`                 | string | Yes      | The campaign name.                                                                                    |
| `$campaign_group_id`    | string | No       | The ID of the group to add this campaign to.                                                          |
| `$mailing_list_id`      | string | No       | The ID of the mailing list to send to.                                                                |
| `$audience_segment_id`  | string | No       | The ID of an audience segment. Setting this without also providing `audience_filter` clears any existing `audience_filter`. If both are provided, the filter is applied on top of the segment's filter. |
| `$audience_filter`      | array  | No       | A tree of audience conditions. See the API reference for the filter schema. Setting this without also providing `audience_segment_id` clears any existing `audience_segment_id`. |
| `$scheduling`           | array  | No       | When the campaign should send. Use `['method' => 'now']` or `['method' => 'schedule', 'timestamp' => '...']`. |

#### Example

```php
$result = $loops->campaigns->create(name: 'Spring announcement');

$result = $loops->campaigns->create(
  name: 'Spring announcement',
  mailing_list_id: 'cm06f5v0e45nf0ml5754o9cix',
  scheduling: ['method' => 'schedule', 'timestamp' => '2026-06-01T10:00:00Z']
);
```

#### Response

```json
{
  "success": true,
  "campaignId": "cln4o7p9q0110msw5ekjtmv78",
  "name": "Spring announcement",
  "status": "Draft",
  "createdAt": "2025-01-01T00:00:00.000Z",
  "updatedAt": "2025-01-01T00:00:00.000Z",
  "emailMessageId": "cly8k3m0n0044jpx2bghepq45",
  "emailMessageContentRevisionId": "clm9n4o6p0088lrz4dijslt67"
}
```

---

### campaigns->get()

Get a single campaign by ID.

[API Reference](https://loops.so/docs/api-reference/get-campaign)

#### Parameters

| Name           | Type   | Required | Notes                   |
| -------------- | ------ | -------- | ----------------------- |
| `$campaign_id` | string | Yes      | The ID of the campaign. |

#### Example

```php
$result = $loops->campaigns->get(campaign_id: 'cln4o7p9q0110msw5ekjtmv78');
```

---

### campaigns->update()

Update a draft campaign's name, group, audience, or scheduling.

At least one field must be provided.

[API Reference](https://loops.so/docs/api-reference/update-campaign)

#### Parameters

| Name                    | Type   | Required | Notes                                                                                                 |
| ----------------------- | ------ | -------- | ----------------------------------------------------------------------------------------------------- |
| `$campaign_id`          | string | Yes      | The ID of the campaign.                                                                               |
| `$name`                 | string | No       | The updated name.                                                                                     |
| `$campaign_group_id`    | string | No       | The ID of the group to move this campaign to.                                                         |
| `$scheduling`           | array  | No       | When the campaign should send. Use `['method' => 'now']` or `['method' => 'schedule', 'timestamp' => '...']`. |
| `$mailing_list_id`      | string | No       | The ID of the mailing list to send to. Pass `null` to clear.                                          |
| `$audience_segment_id`  | string | No       | The ID of an audience segment. Setting this without also providing `audience_filter` clears any existing `audience_filter`. If both are provided, the filter is applied on top of the segment's filter. Pass `null` to clear. |
| `$audience_filter`      | array  | No       | A tree of audience conditions. See the API reference for the filter schema. Setting this without also providing `audience_segment_id` clears any existing `audience_segment_id`. Pass `null` to clear. |


#### Example

```php
$result = $loops->campaigns->update(
  campaign_id: 'cln4o7p9q0110msw5ekjtmv78',
  name: 'Updated name'
);

// Clear the mailing list audience target
$result = $loops->campaigns->update(
  campaign_id: 'cln4o7p9q0110msw5ekjtmv78',
  mailing_list_id: null
);
```

---

### campaignGroups->list()

List campaign groups.

[API Reference](https://loops.so/docs/api-reference/list-campaign-groups)

#### Parameters

| Name        | Type    | Required | Notes                                                                                                                         |
| ----------- | ------- | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `$per_page` | integer | No       | How many results to return per page. Must be between 10 and 50. Defaults to 20 if omitted.                                    |
| `$cursor`   | string  | No       | A cursor, to return a specific page of results. Cursors can be found from the `pagination.nextCursor` value in each response. |

#### Example

```php
$result = $loops->campaignGroups->list();
```

---

### campaignGroups->create()

Create a campaign group.

[API Reference](https://loops.so/docs/api-reference/create-campaign-group)

#### Parameters

| Name            | Type   | Required | Notes                                   |
| --------------- | ------ | -------- | --------------------------------------- |
| `$name`         | string | Yes      | Cannot be the reserved name "Unsorted". |
| `$description`  | string | No       | An optional description for the group.  |

#### Example

```php
$result = $loops->campaignGroups->create(name: 'Newsletters', description: 'Monthly updates');
```

---

### campaignGroups->get()

Get a campaign group by ID.

[API Reference](https://loops.so/docs/api-reference/get-campaign-group)

#### Parameters

| Name  | Type   | Required | Notes                        |
| ----- | ------ | -------- | ---------------------------- |
| `$campaign_group_id` | string | Yes      | The ID of the campaign group. |

#### Example

```php
$result = $loops->campaignGroups->get(campaign_group_id: 'clq7r0s2t0176pvz8hnmpwy01');
```

---

### campaignGroups->update()

Update a campaign group's name or description.

At least one field alongside `campaign_group_id` must be provided.

[API Reference](https://loops.so/docs/api-reference/update-campaign-group)

#### Parameters

| Name            | Type   | Required | Notes                                   |
| --------------- | ------ | -------- | --------------------------------------- |
| `$campaign_group_id` | string | Yes      | The ID of the campaign group.           |
| `$name`         | string | No       | Cannot be the reserved name "Unsorted". |
| `$description`  | string | No       |                                         |

#### Example

```php
$result = $loops->campaignGroups->update(
  campaign_group_id: 'clq7r0s2t0176pvz8hnmpwy01',
  name: 'Updated name'
);
```

---

### audienceSegments->list()

List audience segments.

[API Reference](https://loops.so/docs/api-reference/list-audience-segments)

#### Parameters

| Name        | Type    | Required | Notes                                                                                                                         |
| ----------- | ------- | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `$per_page` | integer | No       | How many results to return per page. Must be between 10 and 50. Defaults to 20 if omitted.                                    |
| `$cursor`   | string  | No       | A cursor, to return a specific page of results. Cursors can be found from the `pagination.nextCursor` value in each response. |

#### Example

```php
$result = $loops->audienceSegments->list();
```

---

### audienceSegments->create()

Create an audience segment.

[API Reference](https://loops.so/docs/api-reference/create-audience-segment)

#### Parameters

| Name            | Type   | Required | Notes                                                                 |
| --------------- | ------ | -------- | --------------------------------------------------------------------- |
| `$name`         | string | Yes      | The segment name. Must be unique within the team.                     |
| `$filter`       | array  | Yes      | A tree of audience conditions with `match` and `conditions`.          |
| `$description`  | string | No       | An optional description of the audience segment.                      |

#### Example

```php
$result = $loops->audienceSegments->create(
  name: 'Power users',
  filter: [
    'match' => 'all',
    'conditions' => [
      [
        'type' => 'property',
        'key' => 'plan',
        'operator' => 'equals',
        'value' => 'pro',
      ],
    ],
  ],
  description: 'Contacts on the pro plan'
);
```

---

### audienceSegments->get()

Get an audience segment by ID.

[API Reference](https://loops.so/docs/api-reference/get-audience-segment)

#### Parameters

| Name  | Type   | Required | Notes                           |
| ----- | ------ | -------- | ------------------------------- |
| `$audience_segment_id` | string | Yes      | The ID of the audience segment. |

#### Example

```php
$result = $loops->audienceSegments->get(audience_segment_id: 'clr8s1t3u0198qw09iotqzx12');
```

---

### workflows->list()

List workflows.

[API Reference](https://loops.so/docs/api-reference/list-workflows)

#### Parameters

| Name        | Type    | Required | Notes                                                                                                                         |
| ----------- | ------- | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `$per_page` | integer | No       | How many results to return per page. Must be between 10 and 50. Defaults to 20 if omitted.                                    |
| `$cursor`   | string  | No       | A cursor, to return a specific page of results. Cursors can be found from the `pagination.nextCursor` value in each response. |

#### Example

```php
$result = $loops->workflows->list();
```

---

### workflows->create()

Create a draft workflow with a blank trigger and exit node.

[API Reference](https://loops.so/docs/api-reference/create-workflow)

#### Parameters

| Name               | Type   | Required | Notes                                              |
| ------------------ | ------ | -------- | -------------------------------------------------- |
| `$name`            | string | Yes      | The workflow name.                                 |
| `$description`     | string | No       | The workflow description.                          |
| `$mailing_list_id` | string | No       | The mailing list the workflow sends to.            |

#### Example

```php
$result = $loops->workflows->create(
  name: 'Welcome series',
  description: 'Onboarding emails for new signups'
);
```

---

### workflows->get()

Get a simplified workflow graph.

[API Reference](https://loops.so/docs/api-reference/get-workflow)

#### Parameters

| Name  | Type   | Required | Notes                  |
| ----- | ------ | -------- | ---------------------- |
| `$workflow_id` | string | Yes      | The ID of the workflow. |

#### Example

```php
$result = $loops->workflows->get(workflow_id: 'cls9t2u4v0210rx20jpuary23');
```

---

### workflows->update()

Update a workflow's display properties.

At least one of `$name` or `$description` must be provided. To change the mailing list, use `workflows->changeMailingList()`.

[API Reference](https://loops.so/docs/api-reference/update-workflow)

#### Parameters

| Name                      | Type   | Required | Notes                                                                 |
| ------------------------- | ------ | -------- | --------------------------------------------------------------------- |
| `$workflow_id`            | string | Yes      | The ID of the workflow.                                               |
| `$expected_revision_id`   | string\|null | Yes | The latest workflow revision token. Pass `null` for older workflows. |
| `$name`                   | string | No       | The updated workflow name.                                            |
| `$description`            | string | No       | The updated workflow description.                                     |

#### Example

```php
$result = $loops->workflows->update(
  workflow_id: 'cls9t2u4v0210rx20jpuary23',
  expected_revision_id: 'clrev1s10n2i3d4e5f6g7h8',
  name: 'Updated welcome series'
);
```

---

### workflows->changeMailingList()

Dry run or apply a workflow mailing list change.

If queued contacts would be removed, the API returns `"status": "queuedContactsFound"`. Retry with `queued_contact_policy: "discard"` to apply the change.

[API Reference](https://loops.so/docs/api-reference/change-workflow-mailing-list)

#### Parameters

| Name                      | Type    | Required | Notes                                                                 |
| ------------------------- | ------- | -------- | --------------------------------------------------------------------- |
| `$workflow_id`            | string  | Yes      | The ID of the workflow.                                               |
| `$expected_revision_id`   | string\|null | Yes | The latest workflow revision token. Pass `null` for older workflows. |
| `$mailing_list_id`        | string  | Yes      | The mailing list to use. Pass `null` to clear.                        |
| `$dry_run`                | boolean | No       | If `true`, validate without modifying the workflow.                   |
| `$queued_contact_policy`  | string  | No       | `fail` (default) or `discard`.                                        |

#### Example

```php
$result = $loops->workflows->changeMailingList(
  workflow_id: 'cls9t2u4v0210rx20jpuary23',
  expected_revision_id: 'clrev1s10n2i3d4e5f6g7h8',
  mailing_list_id: 'cm06f5v0e45nf0ml5754o9cix',
  dry_run: true
);
```

---

### workflows->getNode()

Get detailed data for a single workflow node.

[API Reference](https://loops.so/docs/api-reference/get-workflow-node)

#### Parameters

| Name            | Type   | Required | Notes                        |
| --------------- | ------ | -------- | ---------------------------- |
| `$workflow_id`  | string | Yes      | The ID of the workflow.      |
| `$node_id`      | string | Yes      | The ID of the workflow node. |

#### Example

```php
$result = $loops->workflows->getNode(
  workflow_id: 'cls9t2u4v0210rx20jpuary23',
  node_id: 'clt0u3v5w0232sy31kqvbzs34'
);
```

---

### workflows->createNode()

Create a new default workflow node.

Use `insert_mode: "between"` with `from_node_id` and `to_node_id`, or `insert_mode: "before"` with `before_node_id`.

[API Reference](https://loops.so/docs/api-reference/create-workflow-node)

#### Parameters

| Name                      | Type   | Required | Notes                                                                 |
| ------------------------- | ------ | -------- | --------------------------------------------------------------------- |
| `$workflow_id`            | string | Yes      | The ID of the workflow.                                               |
| `$expected_revision_id`   | string\|null | Yes | The latest workflow revision token. Pass `null` for older workflows. |
| `$insert_mode`            | string | Yes      | `between` or `before`.                                                |
| `$node_type_name`         | string | Yes      | One of `AudienceFilter`, `BranchNode`, `ExperimentBranchNode`, `TimerAction`, `SendEmailAction`, `VariantNode`. |
| `$from_node_id`           | string | No       | Required when `insert_mode` is `between`.                             |
| `$to_node_id`             | string | No       | Required when `insert_mode` is `between`.                             |
| `$before_node_id`         | string | No       | Required when `insert_mode` is `before`.                              |

#### Example

```php
$result = $loops->workflows->createNode(
  workflow_id: 'cls9t2u4v0210rx20jpuary23',
  expected_revision_id: 'clrev1s10n2i3d4e5f6g7h8',
  insert_mode: 'between',
  node_type_name: 'TimerAction',
  from_node_id: 'clt0u3v5w0232sy31kqvbzs34',
  to_node_id: 'clt0u3v5w0232sy31kqvbzs35'
);
```

---

### workflows->updateNode()

Update workflow-node-owned fields for a single node.

[API Reference](https://loops.so/docs/api-reference/update-workflow-node)

#### Parameters

| Name                      | Type   | Required | Notes                                                                 |
| ------------------------- | ------ | -------- | --------------------------------------------------------------------- |
| `$workflow_id`            | string | Yes      | The ID of the workflow.                                               |
| `$node_id`                | string | Yes      | The ID of the workflow node.                                          |
| `$expected_revision_id`   | string\|null | Yes | The latest workflow revision token. Pass `null` for older workflows. |
| `$payload`                | array  | Yes      | Node-type-specific fields to update.                                  |

#### Example

```php
$result = $loops->workflows->updateNode(
  workflow_id: 'cls9t2u4v0210rx20jpuary23',
  node_id: 'clt0u3v5w0232sy31kqvbzs34',
  expected_revision_id: 'clrev1s10n2i3d4e5f6g7h8',
  payload: ['amount' => 2, 'unit' => 'd']
);
```

---

### workflows->deleteNode()

Delete a single workflow node.

If contacts are queued at the node, the API returns `"status": "queuedContactsFound"`. Retry with `queued_contact_policy: "discard"` to delete.

[API Reference](https://loops.so/docs/api-reference/delete-workflow-node)

#### Parameters

| Name                      | Type    | Required | Notes                                                                 |
| ------------------------- | ------- | -------- | --------------------------------------------------------------------- |
| `$workflow_id`            | string  | Yes      | The ID of the workflow.                                               |
| `$node_id`                | string  | Yes      | The ID of the workflow node.                                          |
| `$expected_revision_id`   | string\|null | Yes | The latest workflow revision token. Pass `null` for older workflows. |
| `$dry_run`                | boolean | No       | If `true`, validate without modifying the workflow.                   |
| `$queued_contact_policy`  | string  | No       | `fail` (default) or `discard`.                                        |

#### Example

```php
$result = $loops->workflows->deleteNode(
  workflow_id: 'cls9t2u4v0210rx20jpuary23',
  node_id: 'clt0u3v5w0232sy31kqvbzs34',
  expected_revision_id: 'clrev1s10n2i3d4e5f6g7h8',
  dry_run: true
);
```

---

### workflows->addBranch()

Add a branch and child node under an existing `BranchNode` or `ExperimentBranchNode`.

[API Reference](https://loops.so/docs/api-reference/add-workflow-branch)

#### Parameters

| Name                      | Type   | Required | Notes                                                                 |
| ------------------------- | ------ | -------- | --------------------------------------------------------------------- |
| `$workflow_id`            | string | Yes      | The ID of the workflow.                                               |
| `$node_id`                | string | Yes      | The ID of the branch or experiment node.                              |
| `$expected_revision_id`   | string\|null | Yes | The latest workflow revision token. Pass `null` for older workflows. |

#### Example

```php
$result = $loops->workflows->addBranch(
  workflow_id: 'cls9t2u4v0210rx20jpuary23',
  node_id: 'clt0u3v5w0232sy31kqvbzs34',
  expected_revision_id: 'clrev1s10n2i3d4e5f6g7h8'
);
```

---

### workflows->deleteNodeRecursive()

Delete a node and its downstream subtree.

[API Reference](https://loops.so/docs/api-reference/delete-workflow-nodes)

#### Parameters

| Name                      | Type    | Required | Notes                                                                 |
| ------------------------- | ------- | -------- | --------------------------------------------------------------------- |
| `$workflow_id`            | string  | Yes      | The ID of the workflow.                                               |
| `$node_id`                | string  | Yes      | The root node ID of the subtree to delete.                            |
| `$expected_revision_id`   | string\|null | Yes | The latest workflow revision token. Pass `null` for older workflows. |
| `$dry_run`                | boolean | No       | If `true`, validate without modifying the workflow.                   |
| `$queued_contact_policy`  | string  | No       | `fail` (default) or `discard`.                                        |

#### Example

```php
$result = $loops->workflows->deleteNodeRecursive(
  workflow_id: 'cls9t2u4v0210rx20jpuary23',
  node_id: 'clt0u3v5w0232sy31kqvbzs34',
  expected_revision_id: 'clrev1s10n2i3d4e5f6g7h8',
  dry_run: true
);
```

---

### eventPatterns->list()

List event patterns available to workflow event trigger nodes.

[API Reference](https://loops.so/docs/api-reference/list-event-patterns)

#### Parameters

| Name        | Type    | Required | Notes                                                                                                                         |
| ----------- | ------- | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `$per_page` | integer | No       | How many results to return per page. Must be between 10 and 50. Defaults to 20 if omitted.                                    |
| `$cursor`   | string  | No       | A cursor, to return a specific page of results. Cursors can be found from the `pagination.nextCursor` value in each response. |

#### Example

```php
$result = $loops->eventPatterns->list();
```

---

### eventPatterns->get()

Get an event pattern by ID.

[API Reference](https://loops.so/docs/api-reference/get-event-pattern)

#### Parameters

| Name                 | Type   | Required | Notes                        |
| -------------------- | ------ | -------- | ---------------------------- |
| `$event_pattern_id`  | string | Yes      | The ID of the event pattern. |

#### Example

```php
$result = $loops->eventPatterns->get(event_pattern_id: 'cle1v2e3n4t5p6a7t8t9e0r1');
```

---

### eventPatterns->getByName()

Get an event pattern by event name. Event names are case-sensitive.

[API Reference](https://loops.so/docs/api-reference/get-event-pattern-by-name)

#### Parameters

| Name          | Type   | Required | Notes                |
| ------------- | ------ | -------- | -------------------- |
| `$event_name` | string | Yes      | The exact event name.|

#### Example

```php
$result = $loops->eventPatterns->getByName(event_name: 'signup');
```

---

### emailMessages->get()

Get an email message, including its compiled LMX content.

[API Reference](https://loops.so/docs/api-reference/get-email-message)

#### Parameters

| Name      | Type   | Required | Notes                       |
| --------- | ------ | -------- | --------------------------- |
| `$email_message_id` | string | Yes      | The ID of the email message. |

#### Example

```php
$result = $loops->emailMessages->get(email_message_id: 'cly8k3m0n0044jpx2bghepq45');
```

---

### emailMessages->update()

Update an email message.

At least one field must be provided.

[API Reference](https://loops.so/docs/api-reference/update-email-message)

#### Parameters

| Name      | Type  | Required | Notes                                                                                                                                                                                                 |
| --------- | ----- | -------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `$email_message_id` | string | Yes   | The ID of the email message.                                                                                                                                                                          |
| `$expected_revision_id` | string | No | Supply a value matching the current `contentRevisionId` to avoid 409 conflicts. |
| `$subject` | string | No | The email subject line. |
| `$preview_text` | string | No | The email preview text. |
| `$from_name` | string | No | The sender name. |
| `$from_email` | string | No | The sender email address (the name before the `@`; your sending domain will be automatically appended). |
| `$reply_to_email` | string | No | The reply-to email address. |
| `$cc_email` | string | No | CC email address. Requires the team to have CC/BCC enabled. |
| `$bcc_email` | string | No | BCC email address. Requires the team to have CC/BCC enabled. |
| `$language_code` | string | No | Language code for the email. Requires translation to be enabled for the team. |
| `$email_format` | string | No | `styled` or `plain`. |
| `$lmx` | string | No | The LMX content for the email message. |
| `$contact_properties_fallbacks` | array | No | Contact property fallback values. Pass `null` as a value to remove an individual fallback entry. |
| `$event_properties_fallbacks` | array | No | Event property fallback values. Pass `null` as a value to remove an individual fallback entry. |
| `$data_variables_fallbacks` | array | No | Data variable fallback values. Pass `null` as a value to remove an individual fallback entry. |


#### Example

```php
$result = $loops->emailMessages->update(
  email_message_id: 'cly8k3m0n0044jpx2bghepq45',
  expected_revision_id: 'clm9n4o6p0088lrz4dijslt67',
  subject: 'Updated subject',
  lmx: '<Email><Text>Hello</Text></Email>'
);

// Example with contact property fallbacks
$result = $loops->emailMessages->update(
  email_message_id: 'cly8k3m0n0044jpx2bghepq45',
  contact_properties_fallbacks: [
    'firstName' => 'there',      // If firstName is missing, use "there"
    'company' => 'your company', // If company is missing, use "your company"
    'planName' => null           // null removes the fallback for "planName"
  ]
);

```

---

### emailMessages->preview()

Send a test preview of an email message to one or more addresses.

[API Reference](https://loops.so/docs/api-reference/send-email-message-preview)

#### Parameters

| Name                   | Type     | Required | Notes                                                                             |
| ---------------------- | -------- | -------- | --------------------------------------------------------------------------------- |
| `$email_message_id`    | string   | Yes      | The ID of the email message.                                                      |
| `$emails`              | array    | Yes      | One or more addresses to send the preview to.                                     |
| `$contact_properties`  | array    | No       | Contact property values to render. Accepted for campaign and workflow previews.   |
| `$event_properties`    | array    | No       | Event property values to render. Accepted for workflow previews only.             |
| `$data_variables`      | array    | No       | Transactional data variables to render. Accepted for transactional previews only. |

#### Example

```php
$result = $loops->emailMessages->preview(
  email_message_id: 'cly8k3m0n0044jpx2bghepq45',
  emails: ['test@example.com'],
  contact_properties: ['firstName' => 'Alex']
);
```

---

### emailMessages->guardian()

Run Guardian content validation on an email message and return errors and warnings.

[API Reference](https://loops.so/docs/api-reference/run-guardian-checks)

#### Parameters

| Name                | Type   | Required | Notes                        |
| ------------------- | ------ | -------- | ---------------------------- |
| `$email_message_id` | string | Yes      | The ID of the email message. |

#### Example

```php
$result = $loops->emailMessages->guardian(email_message_id: 'cly8k3m0n0044jpx2bghepq45');
```

---

### transactionalGroups->list()

List transactional groups.

[API Reference](https://loops.so/docs/api-reference/list-transactional-groups)

#### Parameters

| Name        | Type    | Required | Notes                                                                                                                         |
| ----------- | ------- | -------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `$per_page` | integer | No       | How many results to return per page. Must be between 10 and 50. Defaults to 20 if omitted.                                    |
| `$cursor`   | string  | No       | A cursor, to return a specific page of results. Cursors can be found from the `pagination.nextCursor` value in each response. |

#### Example

```php
$result = $loops->transactionalGroups->list();
```

---

### transactionalGroups->create()

Create a transactional group.

[API Reference](https://loops.so/docs/api-reference/create-transactional-group)

#### Parameters

| Name            | Type   | Required | Notes                                   |
| --------------- | ------ | -------- | --------------------------------------- |
| `$name`         | string | Yes      | Cannot be the reserved name "Unsorted". |
| `$description`  | string | No       | An optional description for the group.  |

#### Example

```php
$result = $loops->transactionalGroups->create(name: 'Account emails');
```

---

### transactionalGroups->get()

Get a transactional group by ID.

[API Reference](https://loops.so/docs/api-reference/get-transactional-group)

#### Parameters

| Name  | Type   | Required | Notes                             |
| ----- | ------ | -------- | --------------------------------- |
| `$transactional_group_id` | string | Yes      | The ID of the transactional group. |

#### Example

```php
$result = $loops->transactionalGroups->get(transactional_group_id: 'clv2w3x4y0288xbb0kqrsuv67');
```

---

### transactionalGroups->update()

Update a transactional group's name or description.

At least one field alongside `transactional_group_id` must be provided.

[API Reference](https://loops.so/docs/api-reference/update-transactional-group)

#### Parameters

| Name            | Type   | Required | Notes                                   |
| --------------- | ------ | -------- | --------------------------------------- |
| `$transactional_group_id` | string | Yes      | The ID of the transactional group.      |
| `$name`         | string | No       | Cannot be the reserved name "Unsorted". |
| `$description`  | string | No       |                                         |


#### Example

```php
$result = $loops->transactionalGroups->update(
  transactional_group_id: 'clv2w3x4y0288xbb0kqrsuv67',
  name: 'Updated name'
);
```

---

## Testing

```bash
vendor/bin/phpunit
```

---

## Contributing

Bug reports and pull requests are welcome. Please read our [Contributing Guidelines](CONTRIBUTING.md).
