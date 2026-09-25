<?php

declare(strict_types=1);

namespace PhpClass\Mail;

final class SendMail
{
    public function __construct(
        private readonly string $sender,
        private readonly string $receiver,
        private readonly string $subject,
        private readonly string $message,
        private readonly string $messageBack,
    ) {
    }

    /**
     * Sends the message to the sender address, and a copy (messageBack) to
     * the receiver address, each with the other party set as From.
     *
     * Returns true only if both messages were accepted for delivery.
     */
    public function send(): bool
    {
        $headers = "MIME-Version: 1.0\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n";

        $sender = $this->sanitizeHeader($this->sender);
        $receiver = $this->sanitizeHeader($this->receiver);
        $subject = $this->sanitizeHeader($this->subject);

        $headersToSender = $headers . 'From: ' . $sender . "\r\n";
        $headersToReceiver = $headers . 'From: ' . $receiver . "\r\n";

        $sentToSender = mail($sender, $subject, $this->message, $headersToSender);
        $sentToReceiver = mail($receiver, $subject, $this->messageBack, $headersToReceiver);

        return $sentToSender && $sentToReceiver;
    }

    /**
     * Strips CR/LF so header values can't be used to inject extra mail
     * headers (e.g. additional Bcc: recipients).
     */
    private function sanitizeHeader(string $value): string
    {
        return str_replace(["\r", "\n"], '', $value);
    }
}
