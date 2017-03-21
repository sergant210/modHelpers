<?php

class modHelpersMailer
{
    protected $modx;
    /** @var modPHPMailer $mailer */
    protected $mailer;
    protected $userRepository = array();
    protected $options = array();


    public function __construct($modx)
    {
        /** @var  modX $modx */
        $this->modx = $modx;
        $this->mailer = $modx->getService('mail', 'mail.modPHPMailer');
    }

    protected function init()
    {
        $this->mailer->set(modMail::MAIL_SENDER, $this->modx->getOption('emailsender'));
        $this->mailer->set(modMail::MAIL_FROM, $this->modx->getOption('emailsender'));
        $this->mailer->set(modMail::MAIL_FROM_NAME, $this->modx->getOption('site_name'));
        $this->mailer->setHTML(true);
    }

    protected function getUserRepository()
    {
        if (empty($this->userRepository)) {
            $users = users()->profile()->select('modUser.id, modUser.username, Profile.email')->toArray();
            foreach ($users as $user) {
                $this->userRepository[$user['id']] = $user;
//                $this->userRepository['usernames'][$user['username']] = $user['email'];
            }
        }
    }
    public function to($email)
    {
        if (is_array($email)) {
            foreach ($email as $recipient) {
                $this->options['to'][] = $recipient;
//                $this->mailer->address('to', $recipient);
            }
        } else {
            $this->options['to'][] = $email;
//            $this->mailer->address('to', $email);
        }
        //$this->mailer->address('to', $email);
        return $this;
    }

    public function toUser($user)
    {
        $this->getUserRepository();
        if (is_array($user)) {
            foreach ($user as $id) {
                if (is_numeric($id)) {
                    $email = $this->userRepository[intval($id)]['email'];
                } elseif (is_string($id)) {
                    $uarr = reset(array_filter($this->userRepository, function($data) use ($id) {
                        return $data['username'] == $id;
                    }));
                    $email = $uarr['email'];
                }
                if (!empty($email)) {
                    $this->options['to'][] = $email;
//                    $this->mailer->address('to', $email);
                }
            }
        } else {
            if (is_numeric($user)) {
                $email = $this->userRepository[intval($user)]['email'];
            } elseif (is_string($user)) {
                $uarr = reset(array_filter($this->userRepository, function($data) use ($user) {
                    return $data['username'] == $user;
                }));
                $email = $uarr['email'];
            }
            if (!empty($email)) {
                $this->options['to'][] = $email;
//                $this->mailer->address('to', $email);
            }
        }
        return $this;
    }

    public function cc($email)
    {
        if (is_array($email)) {
            foreach ($email as $recipient) {
                $this->options['cc'][] = $recipient;
//                $this->mailer->address('cc', $recipient);
            }
        } else {
            $this->options['cc'][] = $email;
//            $this->mailer->address('cc', $email);
        }
        return $this;
    }

    public function bcc($email)
    {
        if (is_array($email)) {
            foreach ($email as $recipient) {
                $this->options['bcc'][] = $recipient;
//                $this->mailer->address('bcc', $recipient);
            }
        } else {
            $this->options['bcc'][] = $email;
//            $this->mailer->address('bcc', $email);
        }
        return $this;
    }

    public function replyTo($email)
    {
        $this->options['reply-to'] = $email;
//        $this->mailer->address('reply-to', $email);
        return $this;
    }

    public function subject($subject)
    {
        $this->options['subject'] = $subject;
//        $this->mailer->set(modMail::MAIL_SUBJECT, $subject);
        return $this;
    }

    public function content($content)
    {
        $this->options['content'] = $content;
//        $this->mailer->set(modMail::MAIL_BODY, $content);
        return $this;
    }

    public function sender($email)
    {
        $this->options['sender'] = $email;
//        $this->mailer->set(modMail::MAIL_SENDER, $email);
        return $this;
    }

    public function from($name)
    {
        $this->options['from'] = $name;
//        $this->mailer->set(modMail::MAIL_FROM, $name);
        return $this;
    }

    public function fromName($name)
    {
        $this->options['fromName'] = $name;
//        $this->mailer->set(modMail::MAIL_FROM_NAME, $name);
        return $this;
    }

    public function attach($file, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
    {
        $this->options['attach'][] = compact('file', 'name', 'encoding', 'type');
//        $this->mailer->attach($file, $name, $encoding, $type);
        return $this;
    }

    public function setHTML($toggle)
    {
        $this->options['setHTML'] = $toggle;
//        $this->mailer->setHTML($toggle);
        return $this;
    }

    protected function prepareEmail() {
        if (!empty($this->options)) {
            if (isset($this->options['to']) && !empty($this->options['to'])) {
                foreach ($this->options['to'] as $email) {
                    if (!empty($email)) $this->mailer->address('to', $email);
                }
            } else {
                return '[Email Helper] No addressed to send.';
            }
            if (isset($this->options['cc']) && !empty($this->options['cc'])) {
                foreach ($this->options['cc'] as $email) {
                    if (!empty($email)) $this->mailer->address('cc', $email);
                }
            }
            if (isset($this->options['bcc']) && !empty($this->options['bcc'])) {
                foreach ($this->options['bcc'] as $email) {
                    if (!empty($email)) $this->mailer->address('bcc', $email);
                }
            }
            if (isset($this->options['reply-to'])) $this->mailer->address('reply-to', $this->options['reply-to']);
            if (isset($this->options['subject'])) $this->mailer->set(modMail::MAIL_SUBJECT, $this->options['subject']);
            if (isset($this->options['content'])) $this->mailer->set(modMail::MAIL_BODY, $this->options['content']);
            if (isset($this->options['sender'])) $this->mailer->set(modMail::MAIL_SENDER, $this->options['sender']);
            if (isset($this->options['from'])) $this->mailer->set(modMail::MAIL_FROM, $this->options['from']);
            if (isset($this->options['fromName'])) $this->mailer->set(modMail::MAIL_FROM_NAME, $this->options['fromName']);
            if (isset($this->options['setHTML'])) $this->mailer->setHTML($this->options['setHTML']);
            if (isset($this->options['attach']) && !empty($this->options['attach'])) {
                foreach ($this->options['attach'] as $data) {
                    list($file, $name, $encoding, $type) = $data;
                    $this->mailer->attach($file, $name, $encoding, $type);
                }
            }
        }
        return '';
    }

    public function send()
    {
        $this->init();
        if ($errormsg = $this->prepareEmail()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $errormsg);
            $this->reset();
            return false;
        }

        if (!$this->mailer->send()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ' . $this->mailer->mailer->ErrorInfo);
            $this->reset();
            return false;
        }
        $this->reset();
        return true;
    }

    public function reset()
    {
        $this->mailer->reset();
        $this->options = array();

        return $this;
    }

    /**
     * Send the stored emails.
     * @param string $name Name of the queue
     * @return bool
     */
    public function sendFromQueue($name = 'emails')
    {
        $emails = cache($name, 'queue');
        if (!empty($emails) && is_array($emails)) {
            cache()->delete($name, 'queue');
            foreach ($emails as $data) {
                $this->options = $data;
                if (!$this->send()) {
                    $this->toQueue($name);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Store the email to the cache.
     * @param string $name Name of the queue
     * @param bool $clear Replace the existing queue
     * @return $this
     */
    public function toQueue($name = 'emails', $clear = false)
    {
        if (is_bool($name) && $name) {
            $clear = $name;
            $name = 'emails';
        }
        $emails = cache($name, 'queue');
        if (empty($emails) || !is_array($emails) || $clear) {
            $emails = array($this->options);
        } else {
            $emails[] = $this->options;
        }
        cache()->set($name, $emails, 'queue');

        return $this;
    }

    /**
     * Store the email to the cache.
     * @param string $name Name of the queue
     * @param bool $clear Replace the existing queue
     * @return modHelpersMailer
     */
    public function save($name = 'emails', $clear = false)
    {
        return $this->toQueue($name, $clear);
    }

    /**
     * Send the stored emails.
     * @param string $name Name of the queue
     * @return bool
     */
    public function saved($name = 'emails')
    {
        return $this->sendFromQueue($name);
    }
}