<?php

namespace JThink\Core\Support;

class Mailer {
    protected $config = [];
    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $from = [];
    protected $subject = '';
    protected $body = '';
    protected $attachments = [];
    protected $isHtml = true;
    protected $socket = null;
    protected $lastError = '';

    public function __construct($config = []) {
        $this->config = array_merge([
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'timeout' => 30,
            'from' => [
                'email' => 'no-reply@example.com',
                'name' => 'JThink',
            ],
        ], $config);
    }

    public function to($email, $name = null) {
        $this->to[] = [
            'email' => $email,
            'name' => $name,
        ];
        return $this;
    }

    public function cc($email, $name = null) {
        $this->cc[] = [
            'email' => $email,
            'name' => $name,
        ];
        return $this;
    }

    public function bcc($email, $name = null) {
        $this->bcc[] = [
            'email' => $email,
            'name' => $name,
        ];
        return $this;
    }

    public function from($email, $name = null) {
        $this->from = [
            'email' => $email,
            'name' => $name,
        ];
        return $this;
    }

    public function subject($subject) {
        $this->subject = $subject;
        return $this;
    }

    public function body($body, $isHtml = true) {
        $this->body = $body;
        $this->isHtml = $isHtml;
        return $this;
    }

    public function html($html) {
        $this->body = $html;
        $this->isHtml = true;
        return $this;
    }

    public function text($text) {
        $this->body = $text;
        $this->isHtml = false;
        return $this;
    }

    public function attach($path, $name = null) {
        if (file_exists($path)) {
            $this->attachments[] = [
                'path' => $path,
                'name' => $name ?? basename($path),
                'mime' => $this->getMimeType($path),
            ];
        }
        return $this;
    }

    protected function getMimeType($path) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);
        return $mime ?: 'application/octet-stream';
    }

    public function send() {
        if (empty($this->to)) {
            throw new \Exception('Recipients are required');
        }

        if (empty($this->subject) || empty($this->body)) {
            throw new \Exception('Subject and body are required');
        }

        $host = $this->config['host'];
        $username = $this->config['username'];

        if (!empty($username) && !empty($this->config['password'])) {
            return $this->sendWithSmtp();
        }

        return $this->sendWithMail();
    }

    protected function sendWithMail() {
        $from = !empty($this->from) ? $this->from : $this->config['from'];

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: ' . ($this->isHtml ? 'text/html; charset=UTF-8' : 'text/plain; charset=UTF-8'),
            'From: ' . $this->formatAddress($from['email'], $from['name']),
            'Reply-To: ' . $this->formatAddress($from['email'], $from['name']),
            'X-Mailer: JThink-PHP-Mailer',
        ];

        if (!empty($this->cc)) {
            $headers[] = 'Cc: ' . $this->formatAddresses($this->cc);
        }

        if (!empty($this->bcc)) {
            $headers[] = 'Bcc: ' . $this->formatAddresses($this->bcc);
        }

        $to = $this->formatAddresses($this->to);

        $result = mail($to, $this->subject, $this->body, implode("\r\n", $headers));

        $this->reset();

        return $result;
    }

    protected function sendWithSmtp() {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $encryption = $this->config['encryption'];
        $timeout = $this->config['timeout'] ?? 30;

        $from = !empty($this->from) ? $this->from : $this->config['from'];

        $this->socket = @fsockopen(
            ($encryption === 'ssl' ? 'ssl://' : '') . $host,
            $port,
            $errno,
            $errstr,
            $timeout
        );

        if (!$this->socket) {
            $this->lastError = "Connection failed: {$errstr} ({$errno})";
            $this->reset();
            return false;
        }

        stream_set_timeout($this->socket, $timeout);

        $response = $this->readResponse();

        if ((int)substr($response, 0, 3) !== 220) {
            $this->lastError = "SMTP connection failed: {$response}";
            $this->disconnect();
            return false;
        }

        $this->sendCommand("EHLO {$host}");
        $response = $this->readResponse();

        if ((int)substr($response, 0, 3) !== 250) {
            $this->sendCommand("HELO {$host}");
            $response = $this->readResponse();
        }

        if ($encryption === 'tls') {
            $this->sendCommand("STARTTLS");
            $response = $this->readResponse();

            if ((int)substr($response, 0, 3) !== 220) {
                $this->lastError = "STARTTLS failed: {$response}";
                $this->disconnect();
                return false;
            }

            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            $this->sendCommand("EHLO {$host}");
            $this->readResponse();
        }

        $this->sendCommand("AUTH LOGIN");
        $this->readResponse();

        $this->sendCommand(base64_encode($username));
        $this->readResponse();

        $this->sendCommand(base64_encode($password));
        $response = $this->readResponse();

        if ((int)substr($response, 0, 3) !== 235) {
            $this->lastError = "Authentication failed: {$response}";
            $this->disconnect();
            return false;
        }

        $this->sendCommand("MAIL FROM: <{$from['email']}>");
        $this->readResponse();

        foreach ($this->to as $recipient) {
            $this->sendCommand("RCPT TO: <{$recipient['email']}>");
            $this->readResponse();
        }

        foreach ($this->cc as $recipient) {
            $this->sendCommand("RCPT TO: <{$recipient['email']}>");
            $this->readResponse();
        }

        foreach ($this->bcc as $recipient) {
            $this->sendCommand("RCPT TO: <{$recipient['email']}>");
            $this->readResponse();
        }

        $this->sendCommand("DATA");
        $this->readResponse();

        $message = $this->buildMessage($from);
        $this->sendCommand($message);
        $this->sendCommand(".");
        $response = $this->readResponse();

        $this->sendCommand("QUIT");
        $this->readResponse();

        $this->disconnect();
        $this->reset();

        if ((int)substr($response, 0, 3) !== 250) {
            $this->lastError = "Send failed: {$response}";
            return false;
        }

        return true;
    }

    protected function buildMessage($from) {
        $boundary = '----=_Part_' . md5(uniqid(time()));

        $headers = [];
        $headers[] = "From: " . $this->formatAddress($from['email'], $from['name']);
        $headers[] = "To: " . $this->formatAddresses($this->to);
        $headers[] = "Subject: " . $this->subject;
        $headers[] = "Date: " . date('r');
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "X-Mailer: JThink-PHP-Mailer";

        if (!empty($this->cc)) {
            $headers[] = "Cc: " . $this->formatAddresses($this->cc);
        }

        if (!empty($this->attachments)) {
            $headers[] = "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";
            $body = "--{$boundary}\r\n";
            $body .= "Content-Type: " . ($this->isHtml ? 'text/html' : 'text/plain') . "; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $body .= $this->body . "\r\n\r\n";

            foreach ($this->attachments as $attachment) {
                $content = chunk_split(base64_encode(file_get_contents($attachment['path'])));
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: {$attachment['mime']}; name=\"{$attachment['name']}\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= $content . "\r\n\r\n";
            }

            $body .= "--{$boundary}--\r\n";
        } else {
            $headers[] = "Content-Type: " . ($this->isHtml ? 'text/html' : 'text/plain') . "; charset=UTF-8";
            $headers[] = "Content-Transfer-Encoding: 7bit";
            $body = $this->body;
        }

        $message = implode("\r\n", $headers);
        $message .= "\r\n\r\n";
        $message .= $body;

        return $message;
    }

    protected function sendCommand($command) {
        if ($command !== "QUIT") {
            $command .= "\r\n";
        }
        fwrite($this->socket, $command);
    }

    protected function readResponse() {
        $response = '';
        while ($line = fgets($this->socket, 512)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    }

    protected function disconnect() {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    protected function formatAddress($email, $name = null) {
        if ($name) {
            return "{$name} <{$email}>";
        }
        return $email;
    }

    protected function formatAddresses($addresses) {
        $formatted = [];
        foreach ($addresses as $address) {
            $formatted[] = $this->formatAddress($address['email'], $address['name']);
        }
        return implode(', ', $formatted);
    }

    protected function reset() {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->from = [];
        $this->subject = '';
        $this->body = '';
        $this->attachments = [];
        $this->isHtml = true;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public static function sendSimple($to, $subject, $body, $isHtml = true) {
        $mailer = new self();
        return $mailer->to($to)
                     ->subject($subject)
                     ->body($body, $isHtml)
                     ->send();
    }
}
?>