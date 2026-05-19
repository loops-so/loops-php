<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class TransactionalTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    // Create a mock HTTP client with default configuration
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->mockHttpClient->method('getConfig')
      ->with('headers')
      ->willReturn([
        'Authorization' => 'Bearer test_api_key',
        'Content-Type' => 'application/json',
      ]);

    // Create the LoopsClient with the mock
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testSendTransactional(): void
  {
    $transactional_id = 'test_template_123';
    $email = 'test@example.com';
    $add_to_audience = true;
    $data_variables = ['name' => 'Test User'];
    $attachments = [
      [
        'filename' => 'test.pdf',
        'contentType' => 'application/pdf',
        'data' => 'base64_encoded_data'
      ]
    ];
    $custom_headers = ['X-Custom-Header' => 'value'];

    // Configure mock to expect the correct API call
    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/transactional',
        $this->callback(function ($options) use ($transactional_id, $email, $add_to_audience, $data_variables, $attachments, $custom_headers) {
          // Verify the request structure
          if (!isset($options['json']) || !isset($options['headers'])) {
            return false;
          }

          $payload = $options['json'];
          $expected_headers = array_merge([
            'Authorization' => 'Bearer test_api_key',
            'Content-Type' => 'application/json',
          ], $custom_headers);

          // Verify payload structure
          $has_required_fields = isset($payload['transactionalId'])
            && isset($payload['email'])
            && isset($payload['addToAudience'])
            && isset($payload['dataVariables'])
            && isset($payload['attachments']);

          // Verify payload values
          $has_correct_values = $payload['transactionalId'] === $transactional_id
            && $payload['email'] === $email
            && $payload['addToAudience'] === $add_to_audience
            && $payload['dataVariables'] === $data_variables
            && $payload['attachments'] === $attachments;

          // Verify headers
          $has_correct_headers = $options['headers'] === $expected_headers;

          return $has_required_fields && $has_correct_values && $has_correct_headers;
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
        ])
      ));

    // Make the API call
    $result = $this->client->transactional->send(
      transactional_id: $transactional_id,
      email: $email,
      add_to_audience: $add_to_audience,
      data_variables: $data_variables,
      attachments: $attachments,
      headers: $custom_headers
    );

    // Assert the response structure
    $this->assertIsArray($result);
    $this->assertArrayHasKey('success', $result);

    // Assert response values
    $this->assertTrue($result['success']);
  }

  public function testGetTransactionals(): void
  {
    $per_page = 20;
    $cursor = 'clyo0q4wo01p59fsecyxqsh38';

    // Configure mock to expect the correct API call
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with(
        'v1/transactional',
        $this->callback(function ($options) use ($per_page, $cursor) {
          // Verify the query parameters are passed correctly
          return isset($options['query'])
            && $options['query']['per_page'] === $per_page
            && $options['query']['cursor'] === $cursor;
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'pagination' => [
            'totalResults' => 23,
            'returnedResults' => 20,
            'perPage' => 20,
            'totalPages' => 2,
            'nextCursor' => 'clyo0q4wo01p59fsecyxqsh38',
            'nextPage' => 'https://app.loops.so/api/v1/transactional?cursor=clyo0q4wo01p59fsecyxqsh38&perPage=20'
          ],
          'data' => [
            [
              'id' => 'clfn0k1yg001imo0fdeqg30i8',
              'name' => 'Welcome email',
              'lastUpdated' => '2023-11-06T17:48:07.249Z',
              'dataVariables' => []
            ]
          ]
        ])
      ));

    // Make the API call
    $result = $this->client->transactional->list(
      per_page: $per_page,
      cursor: $cursor
    );

    // Assert the response structure
    $this->assertIsArray($result);
    $this->assertArrayHasKey('pagination', $result);
    $this->assertArrayHasKey('data', $result);

    // Assert pagination structure
    $this->assertArrayHasKey('totalResults', $result['pagination']);
    $this->assertArrayHasKey('returnedResults', $result['pagination']);
    $this->assertArrayHasKey('perPage', $result['pagination']);
    $this->assertArrayHasKey('totalPages', $result['pagination']);
    $this->assertArrayHasKey('nextCursor', $result['pagination']);
    $this->assertArrayHasKey('nextPage', $result['pagination']);

    // Assert data structure
    $this->assertIsArray($result['data']);
    $this->assertNotEmpty($result['data']);
    $this->assertArrayHasKey('id', $result['data'][0]);
    $this->assertArrayHasKey('name', $result['data'][0]);
    $this->assertArrayHasKey('lastUpdated', $result['data'][0]);
    $this->assertArrayHasKey('dataVariables', $result['data'][0]);
  }
}