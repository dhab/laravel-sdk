<?php

namespace DreamHack\SDK\Mail;

use DreamHack\SDK\Facades\Guzzle;
use Log;
use MailchimpTransactional\ApiClient;
use MailchimpTransactional\ApiException;

/*
 * READ THIS BEFORE USING!
 *
 * The plan with this is to keep most of the settings (subject, content, from-address etc)
 * in mandrill web UI. Only override if really needed, but should pretty much be never.
 * Just create a new template and use that if you need.
 *
 * To send:
 *
 * $mail = new SendTemplate('id-reset-password', 'alex@dreamhack.com');
 * $mail->setVars(["reset_key" => 'ca1b9a0eee28f872e721a9ba2591320b']);
 * $mail->send();
 *
 * Override subject:
 * $mail->subject('Hello my dudes');
 *
 * Set name of recipient to make it more personal:
 * $mail->name('Alexander');
 *
 */

class SendTemplate
{
    public function __construct($template, $to, $data = [])
    {
        $this->template = $template;
        $this->toAddress = $to;
        $this->vars = $data;
    }

    public function setVars($data)
    {
        $this->vars = $data;
    }

    public function name($name)
    {
        $this->toName = $name;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
    }

    private function getTo()
    {
        $ret['email'] = $this->toAddress;
        $ret['type'] = 'to';

        if (isset($this->toName)) {
            $ret['name'] = $this->toName;
        }

        return [$ret];
    }

    private function getVars()
    {
        $ret = [];
        foreach ($this->vars ?? [] as $name => $content) {
            $ret[] = [
                "name" => $name,
                "content" => $content,
            ];
        }
        return $ret;
    }

    private function getMergeVars()
    {
        return [[
            'rcpt' => $this->toAddress,
            'vars' => $this->getVars(),
        ]];
    }

    public function send()
    {
        try {
            $mailchimp = new ApiClient();
            $mailchimp->setApiKey(config('mandrill.api_key'));

            // To find out more about the format of the message-array check this one out:
            // https://mandrillapp.com/api/docs/messages.php.html
            $message = [];

            // Format of to is an array of arrays with the keys email, name, type
            // We're just going to use address and possibly name for one recipient at a time
            $message['to'] = $this->getTo();

            // Maybe override subject?
            if (isset($this->subject)) {
                $message['subject'] = $this->subject;
            }

            $message['merge_vars'] = $this->getMergeVars();
            $message['merge_language'] = 'handlebars';

            // Call mandrill API
            $result = $mailchimp->messages->sendTemplate([
                "template_name" => $this->template,
                "template_content" => [$this->getVars()],
                "message" => $message,
            ]);

            if ($result[0]->status !== 'sent') {
                Log::error('Failed to send via mandrill', $result);
                return false;
            }

            return true;
        } catch (ApiException $e) {
            Log::error('A mandrill error occurred', $e);
            return false;
        }
    }
}
