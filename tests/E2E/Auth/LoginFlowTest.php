<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class LoginFlowTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        // Create a Guzzle client for HTTP requests
        $this->client = new Client([
            'base_uri' => 'http://localhost',
        ]);
    }

    public function testLoginPageLoads(): void
    {
        $response = $this->client->get('/login.php');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('<form', (string) $response->getBody());
    }

    public function testLoginWithValidCredentials(): void
    {
        $response = $this->client->post('/login.php', [
            'form_params' => [
                'email' => 'user@example.com',
                'password' => 'secret123',
            ],
        ]);

        $this->assertEquals(302, $response->getStatusCode()); // Redirect after login
        $this->assertEquals('/dashboard.php', $response->getHeaderLine('Location'));
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $response = $this->client->post('/login.php', [
            'form_params' => [
                'email' => 'user@example.com',
                'password' => 'wrongpassword',
            ],
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('Invalid credentials', (string) $response->getBody());
    }

    public function testLoginWithEmptyFields(): void
    {
        $response = $this->client->post('/login.php', [
            'form_params' => [
                'email' => '',
                'password' => '',
            ],
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('All fields are required', (string) $response->getBody());
    }
}
