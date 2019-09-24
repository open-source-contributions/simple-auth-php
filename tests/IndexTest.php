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
                'password' => 'invalid_password',
            ],
        ];
        $response = $client->request('POST', 'http://localhost:5000', $formParams);
        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        $this->assertSame('Auth is successful.', $response['result']);
        $this->assertArrayHasKey('token', $response);
    }
}
