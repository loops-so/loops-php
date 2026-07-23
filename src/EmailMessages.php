<?php

namespace Loops;

use Loops\LoopsClient;

class EmailMessages
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function get(string $email_message_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/email-messages/' . $email_message_id);
    }

    public function update(
        string $email_message_id,
        ?string $expected_revision_id = null,
        ?string $subject = null,
        ?string $preview_text = null,
        ?string $from_name = null,
        ?string $from_email = null,
        ?string $reply_to_email = null,
        ?string $cc_email = null,
        ?string $bcc_email = null,
        ?string $language_code = null,
        ?string $email_format = null,
        ?string $lmx = null,
        ?array $contact_properties_fallbacks = null,
        ?array $event_properties_fallbacks = null,
        ?array $data_variables_fallbacks = null
    ): mixed {
        $payload = Util::omitNull([
            'expectedRevisionId' => $expected_revision_id,
            'subject' => $subject,
            'previewText' => $preview_text,
            'fromName' => $from_name,
            'fromEmail' => $from_email,
            'replyToEmail' => $reply_to_email,
            'ccEmail' => $cc_email,
            'bccEmail' => $bcc_email,
            'languageCode' => $language_code,
            'emailFormat' => $email_format,
            'lmx' => $lmx,
            'contactPropertiesFallbacks' => $contact_properties_fallbacks,
            'eventPropertiesFallbacks' => $event_properties_fallbacks,
            'dataVariablesFallbacks' => $data_variables_fallbacks,
        ]);
        if ($payload === []) {
            throw new \InvalidArgumentException(message: 'At least one field must be provided.');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/email-messages/' . $email_message_id, options: [
            'json' => $payload
        ]);
    }

    public function preview(
        string $email_message_id,
        array $emails,
        ?array $contact_properties = null,
        ?array $event_properties = null,
        ?array $data_variables = null
    ): mixed {
        $payload = array_merge(
            ['emails' => $emails],
            Util::omitNull([
                'contactProperties' => $contact_properties,
                'eventProperties' => $event_properties,
                'dataVariables' => $data_variables,
            ])
        );

        return $this->client->query(method: 'POST', endpoint: 'v1/email-messages/' . $email_message_id . '/preview', options: [
            'json' => $payload
        ]);
    }

    public function guardian(string $email_message_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/email-messages/' . $email_message_id . '/guardian');
    }
}
