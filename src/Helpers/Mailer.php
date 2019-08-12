<?php

class Mailer {
    protected $view;
    protected $mailer;
    protected $logger;

    public function __construct($view, $mailer, $logger) {
        $this->view = $view;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function to($address) {
        $this->mailer->addAddress($address);
    }

    public function subject($subject) {
        $this->mailer->Subject = $subject;
    }

    public function body($body) {
        $this->mailer->Body = $body;
    }

    public function from($from) {
        $this->mailer->From = $from;
    }

    public function fromName($fromName) {
        $this->mailer->FromName = $fromName;
    }

    public function attachment($attachment) {
        $this->mailer->addAttachment($attachment);
    }

    public function send($view, $data) {
        $data['host'] = $_SERVER['SERVER_NAME'];

        if ($data['to']) {
            $this->to($data['to']);
        }

        if ($data['subject']) {
            $this->subject($data['subject']);
        }

        if ($data['from']) {
            $this->from($data['from']);
        }

        if ($data['fromName']) {
            $this->fromName($data['fromName']);
        }

        if ($data['attachment']) {
            $this->attachment($data['attachment']);
        }

        $this->body($this->view->fetch('Mails/'.$view, $data));

        if (!$this->mailer->send()) {
            $this->logger->error($this->mailer->ErrorInfo);
        }

        $this->mailer->ClearAllRecipients();
    }
}
