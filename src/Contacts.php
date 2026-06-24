<?php

namespace Loops;

use Loops\LoopsClient;

class Contacts
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function create(string $email, ?array $properties = [], ?array $mailing_lists = []): mixed
    {
        $payload = array_merge(
            [
                'email' => $email,
                'mailingLists' => $mailing_lists
            ], 
            $properties
        );

        return $this->client->query(method: 'POST', endpoint: 'v1/contacts/create', options: [
            'json' => $payload
        ]);
    }

    public function update(?string $email = null, ?string $user_id = null, ?array $properties = [], ?array $mailing_lists = []): mixed
    {
        if (!$email && !$user_id) {
            throw new \InvalidArgumentException(message: 'You must provide an email or user_id value.');
        }

        $payload = array_merge(
            Util::omitNull(['email' => $email, 'userId' => $user_id]),
            ['mailingLists' => $mailing_lists],
            $properties
        );

        return $this->client->query(method: 'PUT', endpoint: 'v1/contacts/update', options: [
            'json' => $payload
        ]);
    }

    public function find(?string $email = null, ?string $user_id = null): mixed
    {
        if ($email && $user_id) {
            throw new \InvalidArgumentException(message: 'Only one parameter is permitted.');
        }
        if (!$email && !$user_id) {
            throw new \InvalidArgumentException(message: 'You must provide an email or user_id value.');
        }

        return $this->client->query(method: 'GET', endpoint: 'v1/contacts/find', options: [
            'query' => Util::omitNull([
                'email' => $email,
                'userId' => $user_id,
            ])
        ]);
    }

    public function delete(?string $email = null, ?string $user_id = null): mixed
    {
        if ($email && $user_id) {
            throw new \InvalidArgumentException(message: 'Only one parameter is permitted.');
        }
        if (!$email && !$user_id) {
            throw new \InvalidArgumentException(message: 'You must provide an email or user_id value.');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/contacts/delete', options: [
            'json' => Util::omitNull([
                'email' => $email,
                'userId' => $user_id,
            ])
        ]);
    }

    public function checkSuppression(?string $email = null, ?string $user_id = null): mixed
    {
        if ($email && $user_id) {
            throw new \InvalidArgumentException(message: 'Only one parameter is permitted.');
        }
        if (!$email && !$user_id) {
            throw new \InvalidArgumentException(message: 'You must provide an email or user_id value.');
        }

        return $this->client->query(method: 'GET', endpoint: 'v1/contacts/suppression', options: [
            'query' => Util::omitNull([
                'email' => $email,
                'userId' => $user_id,
            ])
        ]);
    }

    public function removeSuppression(?string $email = null, ?string $user_id = null): mixed
    {
        if ($email && $user_id) {
            throw new \InvalidArgumentException(message: 'Only one parameter is permitted.');
        }
        if (!$email && !$user_id) {
            throw new \InvalidArgumentException(message: 'You must provide an email or user_id value.');
        }

        return $this->client->query(method: 'DELETE', endpoint: 'v1/contacts/suppression', options: [
            'query' => Util::omitNull([
                'email' => $email,
                'userId' => $user_id,
            ])
        ]);
    }
}
