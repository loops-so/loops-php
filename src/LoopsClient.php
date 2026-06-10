<?php

namespace Loops;

class LoopsClient
{
  private const BASE_URI = 'https://app.loops.so/api/';

  private \GuzzleHttp\Client $httpClient;
  public ApiKey $apiKey;
  public Contacts $contacts;
  public Events $events;
  public MailingLists $mailingLists;
  public Transactional $transactional;
  public ContactProperties $contactProperties;
  public DedicatedSendingIps $dedicatedSendingIps;
  public Themes $themes;
  public Components $components;
  public Campaigns $campaigns;
  public EmailMessages $emailMessages;
  public Uploads $uploads;

  public function __construct(string $api_key)
  {
    $this->httpClient = new \GuzzleHttp\Client(config: [
      'base_uri' => self::BASE_URI,
      'headers' => [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
      ],
      'http_errors' => false
    ]);

    $this->apiKey = new ApiKey(client: $this);
    $this->contacts = new Contacts(client: $this);
    $this->events = new Events(client: $this);
    $this->mailingLists = new MailingLists(client: $this);
    $this->transactional = new Transactional(client: $this);
    $this->contactProperties = new ContactProperties(client: $this);
    $this->dedicatedSendingIps = new DedicatedSendingIps(client: $this);
    $this->themes = new Themes(client: $this);
    $this->components = new Components(client: $this);
    $this->campaigns = new Campaigns(client: $this);
    $this->emailMessages = new EmailMessages(client: $this);
    $this->uploads = new Uploads(client: $this);
  }

  /**
   * Sets the HTTP client. This method is primarily for testing purposes.
   *
   * @param \GuzzleHttp\Client $client
   * @return void
   */
  public function setHttpClient(\GuzzleHttp\Client $client): void
  {
    $this->httpClient = $client;
  }

  /**
   * Performs an HTTP request to the Loops API
   *
   * @param string $method The HTTP method to use
   * @param string $endpoint The API endpoint to call
   * @param array $options Additional request options
   * @return mixed The decoded JSON response
   * @throws Exceptions\RateLimitExceededError When rate limit is exceeded
   * @throws Exceptions\APIError When API returns an error
   * @throws \Exception When other errors occur
   */
  public function query(string $method, string $endpoint, array $options = []): mixed
  {
    try {
      // Merge custom headers with default headers if headers are provided
      if (isset($options['headers']) && is_array($options['headers'])) {
        $options['headers'] = array_merge(
          $this->httpClient->getConfig(option: 'headers'),
          $options['headers']
        );
      }

      $response = $this->httpClient->$method($endpoint, $options);
      if ($response->getStatusCode() === 429) {
        // Handle rate limiting
        $limit = (int) ($response->getHeader('x-ratelimit-limit')[0] ?? 10);
        $remaining = (int) ($response->getHeader('x-ratelimit-remaining')[0] ?? 10);
        throw new Exceptions\RateLimitExceededError(limit: $limit, remaining: $remaining);
      }

      if ($response->getStatusCode() >= 400) {
        // All other error status codes from API
        $json = json_decode(json: $response->getBody()->getContents(), associative: true);
        throw new Exceptions\APIError(statusCode: $response->getStatusCode(), json: $json);
      }

      return json_decode(json: $response->getBody()->getContents(), associative: true);
    } catch (\Exception $e) {
      // Pass through any exceptions
      throw $e;
    }
  }
}