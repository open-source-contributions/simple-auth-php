# simple-auth-php

## Introduction

This is about the simple authentication API in PHP

## Usage

- Clone this repository via `git` command

```BASH
git clone https://github.com/open-source-contributions/simple-auth-php
```

- Docker image build

```BASH
docker build -t simple_auth_php .
```

- Docker container executing

```BASH
docker run --name=simple_auth_php -d simple_auth_php
```

## Auth API

We assume developers use the `Guzzle, HTTP client` to request this auth API.

- `Login` Action API, it's `login` action request.

```php
use GuzzleHttp\Client;

$client = new Client();
$formParams = [
    'form_params' => [
        'action' => 'login',
        'account' => 'the_account',
        'password' => 'the_password',
    ],
];

$response = $client->request('POST', 'http://localhost:5000', $formParams);
$response = (string) $response->getBody();
$response = json_decode($response, true);

// output: {"result":"Auth is successful.","token":"your_user_token"}
```

- `Logout` Action API, it's `logout` action request.

```php
use GuzzleHttp\Client;

$client = new Client();
$formParams = [
    'form_params' => [
        'action' => 'logout',
        'account' => 'test',
        'token' => 'your_user_token',
    ],
];

$response = $client->request('POST', 'http://localhost:5000', $formParams);
$response = (string) $response->getBody();
$response = json_decode($response, true);

// output: {"result":"Logout is done."}
```

- `Status` Action API, it's `status` action request.

```php
use GuzzleHttp\Client;

$client = new Client();
$formParams = [
    'form_params' => [
        'action' => 'status',
        'account' => 'test',
        'token' => 'your_user_token',
    ],
];
$response = $client->request('POST', 'http://localhost:5000', $formParams);
$response = (string) $response->getBody();
$response = json_decode($response, true);

// output: {"result":"Token is expired. It should be logout."}
// or
// output: {"result":"Token is live."}
```
