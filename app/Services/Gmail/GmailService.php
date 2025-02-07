<?php
namespace App\Services\Gmail;

use App\Models\Google\GoogleToken;
use Google\Client as GoogleClient;
use Google\Service\Gmail;
use ZipArchive;

class GmailService
{
    protected $client;
    protected $gmailService;

    public function __construct()
    {
        $this->initializeClient();
    }

    private function initializeClient()
    {
        $tokenData = GoogleToken::first();

        $this->client = new GoogleClient();
        $this->client->setClientId(env('GMAIL_CLIENT_ID'));
        $this->client->setClientSecret(env('GMAIL_SECRET_ID'));
        $this->client->setRedirectUri(env('GMAIL_REDIRECT'));

        if (now()->gte($tokenData->expires_at)) {
            $this->client->refreshToken($tokenData->refresh_token);
            $newToken = $this->client->getAccessToken();
            $tokenData->update([
                'access_token' => $newToken['access_token'],
                'expires_at' => now()->addSeconds($newToken['expires_in']),
            ]);
        } else {
            $this->client->setAccessToken($tokenData->access_token);
        }

        $this->gmailService = new Gmail($this->client);
    }

    public function getMails($subjectFilter = 'Отчет СКАП')
    {
        $messages = $this->gmailService->users_messages->listUsersMessages('me', ['maxResults' => 10]);
        $zipFiles = [];

        foreach ($messages->getMessages() as $message) {
            $msg = $this->gmailService->users_messages->get('me', $message->getId());

            $subject = collect($msg->getPayload()->getHeaders())
                ->firstWhere('name', 'Subject')
                ->value;

            if (str_contains($subject, $subjectFilter)) {
                foreach ($msg->getPayload()->getParts() as $part) {
                    if ($part->getFilename() && $part->getBody()->getAttachmentId()) {
                        if (pathinfo($part->getFilename(), PATHINFO_EXTENSION) === 'zip') {
                            $attachmentId = $part->getBody()->getAttachmentId();
                            $attachment = $this->gmailService->users_messages_attachments
                                ->get('me', $message->getId(), $attachmentId);

                            $fileData = base64_decode($attachment->getData());

                            $zipFiles[] = [
                                'filename' => $part->getFilename(),
                                'content' => $fileData
                            ];
                        }
                    }
                }
            }
        }

        return $zipFiles;
    }

    public function combineZipFiles(array $zipFiles)
    {
        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'combined_zip');

        if ($zip->open($tempFile, ZipArchive::CREATE) === TRUE) {
            foreach ($zipFiles as $file) {
                $zip->addFromString($file['filename'], $file['content']);
            }
            $zip->close();

            $combinedZipContent = file_get_contents($tempFile);
            unlink($tempFile);

            return $combinedZipContent;
        }

        return null;
    }
}

