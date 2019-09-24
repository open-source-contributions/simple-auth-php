<?php

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    public function testMethodIsNotAccepted()
    {
        $client = new Client();
        $response = $client->request('GET', 'http://localhost:5000');
        $response = (string) $response->getBody();
        $response = json_decode($response, true)['result'];

        $this->assertSame('Sorry. This method is not accepted.', $response);
    }

    public function testActionFieldIsMissing()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'field_name' => 'invalid',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true)['result'];

        $this->assertSame('Sorry. The action is missing.', $response);
    }

    public function testInvalidActions()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'invalid_action',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true)['result'];

        $this->assertSame('Invalid actions.', $response);
    }

    public function testLoginActionOnFailedAccountAuthentication()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'login',
                'account' => 'invalid_account',
                'password' => 'test_ci',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true)['result'];

        $this->assertSame('Account Auth is failed.', $response);
    }

    public function testLoginActionOnFailedPasswordAuthentication()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'login',
                'account' => 'test',
                'password' => 'invalid_password',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true)['result'];

        $this->assertSame('Password Auth is failed.', $response);
    }

    public function testLoginActionOnSuccesfulAuthentication()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'login',
                'account' => 'test',
                'password' => 'test_ci',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('Auth is successful.', $response['result']);
        $this->assertArrayHasKey('token', $response);
    }

    public function testLogoutActionOnEmptyAccount()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'logout',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('Account is missing.', $response['result']);
    }

    public function testLogoutActionOnEmptyToken()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'logout',
                'account' => 'test',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('Token is missing.', $response['result']);
    }

    public function testLogoutActionOnInvalidToken()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'logout',
                'account' => 'test',
                'token' => 'invalid_token',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('Token is invalid.', $response['result']);
    }

    public function testLogoutActionOnSuccessfulLogout()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'login',
                'account' => 'test',
                'password' => 'test_ci',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);
        $token = $response['token'];

        $formParams = [
            'form_params' => [
                'action' => 'logout',
                'account' => 'test',
                'token' => $token,
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('Logout is done.', $response['result']);
    }

    public function testStatusAction()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'login',
                'account' => 'test',
                'password' => 'test_ci',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);
        $token = $response['token'];

        $formParams = [
            'form_params' => [
                'action' => 'status',
                'account' => 'test',
                'token' => $token,
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('Token is live.', $response['result']);
    }

    public function testStatusActionOnNonVerified()
    {
        $client = new Client();
        $formParams = [
            'form_params' => [
                'action' => 'login',
                'account' => 'test',
                'password' => 'test_ci',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $formParams = [
            'form_params' => [
                'action' => 'status',
                'account' => 'test',
                'token' => 'invalid_token',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('It is not verified and should run logout action.', $response['result']);
    }

    public function testStatusActionOnMissingAccount()
    {
        $client = new Client();

        $formParams = [
            'form_params' => [
                'action' => 'status',
                'token' => 'token',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('Account is missing.', $response['result']);

    }

    public function testStatusActionOnMissingToken()
    {
        $client = new Client();

        $formParams = [
            'form_params' => [
                'action' => 'status',
                'account' => 'test',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('Token is missing.', $response['result']);
    }
}
