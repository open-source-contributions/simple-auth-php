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
}
