<?php

namespace modHelpers;

use modX;
use modPHPMailer;
use modMail;

class Mailer
{
    /** @var modX */
    protected $modx;
    /** @var modPHPMailer $mailer */
    protected $mailer;
    /** @var array $userRepository User collection */
    protected $userRepository = array();
    /** @var array $attributes Email attributes */
    protected $attributes = array();
    protected $initialized = false;


    public function __construct($modx)
    {
        /** @var  modX $modx */
        $this->modx = $modx;
        $this->mailer = $modx->getService('mail', 'mail.modPHPMailer');
        $this->attributes['sender'] = $this->modx->getOption('emailsender');
        $this->attributes['from'] = $this->modx->getOption('emailsender');
        $this->attributes['fromName'] = $this->modx->getOption('site_name');
        $this->attributes['setHTML'] = true;
    }

    protected function getUserRepository()
    {
        if (empty($this->userRepository)) {
            $users = users()->profile()->select('modUser.id, modUser.username, Profile.email')->toArray();
            foreach ($users as $user) {
                $this->userRepository[$user['id']] = $user;
            }
        }
        return $this->userRepository;
    }

    /**
     * Adds an address
     * @param string|array $email Email or an array of emails.
     * @return $this
     */
    public function to($email)
    {
        if (is_array($email)) {
            foreach ($email as $recipient) {
                if (is_email($recipient)) {
                    $this->attributes['to'][] = $recipient;
                }
            }
        } else {
            $this->attributes['to'][] = $email;
        }
        return $this;
    }

    /**
     * Add a user as recipient
     * @param integer|string|array $user Id, username or an array of these user attributes.
     * @return $this
     */
    public function toUser($user)
    {
        $this->getUserRepository();
        if (is_array($user)) {
            foreach ($user as $id) {
                $this->toUser($id);
            }
        } else {
            if (is_numeric($user)) {
                $email = $this->userRepository[(int)$user]['email'];
            } elseif (is_string($user)) {
                $uarr = array_filter($this->userRepository, function($data) use ($user) {
                    return $data['username'] == $user;
                });
                $email = $uarr['email'];
            }
            if (!empty($email) && is_email($email)) {
                $this->attributes['to'][] = $email;
            }
        }
        return $this;
    }

    /**
     * Set the cc address
     * @param string|array $email
     * @return $this
     */
    public function cc($email)
    {
        if (is_array($email)) {
            foreach ($email as $recipient) {
                if (is_email($recipient)) {
                    $this->attributes['cc'][] = $recipient;
                }
            }
        } elseif (is_email($email)) {
            $this->attributes['cc'][] = $email;
        }
        return $this;
    }

    /**
     * Set the bcc address
     * @param string|array $email
     * @return $this
     */
    public function bcc($email)
    {
        if (is_array($email)) {
            foreach ($email as $recipient) {
                if (is_email($recipient)) {
                    $this->attributes['bcc'][] = $recipient;
                }
            }
        } else {
            if (is_email($email)) {
                $this->attributes['bcc'][] = $email;
            }
        }
        return $this;
    }

    /**
     * Set the reply_to address
     * @param string $email
     * @return $this
     */
    public function replyTo($email)
    {
        if (is_email($email)) {
            $this->attributes['reply-to'] = $email;
        }
        return $this;
    }

    /**
     * Set the email subject
     * @param string $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->attributes['subject'] = $subject;
        return $this;
    }

    /**
     * Set the email content
     * @param string $content
     * @return $this
     */
    public function content($content)
    {
        $this->attributes['content'] = $content;
        return $this;
    }
    /**
     * Set a chunk with placeholders for the email content
     * @param string $name Chunk name.
     * @param array $data Placeholders for the chunk.
     * @return $this
     */
    public function tpl($name, array $data = array())
    {
        $this->attributes['tpl'] = array(
            'name' => $name,
            'data' => $data,
        );
        return $this;
    }

    /**
     * Set the sender to the mailer's "sender" option
     * @param string $email
     * @return $this
     */
    public function sender($email)
    {
        $this->attributes['sender'] = $email;
        return $this;
    }

    /**
     * Set the sender email to the "from" option
     * @param string $name
     * @return $this
     */
    public function from($name)
    {
        $this->attributes['from'] = $name;
        return $this;
    }

    /**
     * Set the sender name to the "fromName" option
     * @param string $name
     * @return $this
     */
    public function fromName($name)
    {
        $this->attributes['fromName'] = $name;
        return $this;
    }

    /**
     * Add a attached file to the options.
     * @param string|array $file Absolute path
     * @param string $name
     * @param string $encoding
     * @param string $type
     * @return $this
     */
    public function attach($file, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
    {
        if (is_array($file)) {
            $files = $file;
            foreach ($files as $file) {
                $this->attributes['attach'][] = compact('file', 'name', 'encoding', 'type');
            }
        } else {
            $this->attributes['attach'][] = compact('file', 'name', 'encoding', 'type');
        }
        return $this;
    }

    /**
     * Sets email to HTML or text-only.
     *
     * @param boolean $toggle True to set to HTML.
     * @return $this
     */
    public function setHTML($toggle)
    {
        $this->attributes['setHTML'] = $toggle;
        return $this;
    }

    /**
     * Set the mailer options
     * @return string
     */
    protected function prepareEmail() {
        if (!empty($this->attributes)) {
            if (!empty($this->attributes['to'])) {
                foreach (array_unique($this->attributes['to']) as $email) {
                    if (!empty($email)) {
                        $this->mailer->address('to', $email);
                    }
                }
            } else {
                return '[Email Helper] No addressed to send.';
            }
            if (!empty($this->attributes['cc'])) {
                foreach ($this->attributes['cc'] as $email) {
                    if (!empty($email)) {
                        $this->mailer->address('cc', $email);
                    }
                }
            }
            if (!empty($this->attributes['bcc'])) {
                foreach ($this->attributes['bcc'] as $email) {
                    if (!empty($email)) {
                        $this->mailer->address('bcc', $email);
                    }
                }
            }
            if (!empty($this->attributes['reply-to'])) {
                $this->mailer->address('reply-to', $this->attributes['reply-to']);
            }
            if (isset($this->attributes['subject'])) {
                $this->mailer->set(modMail::MAIL_SUBJECT, $this->attributes['subject']);
            }
            if (isset($this->attributes['content'])) {
                $this->mailer->set(modMail::MAIL_BODY, $this->attributes['content']);
            } elseif (isset($this->attributes['tpl']) && $chunk = $this->attributes['tpl']['name']) {
                $content = chunk($chunk, $this->attributes['tpl']['data']);
                unset($this->attributes['tpl']);
                $this->mailer->set(modMail::MAIL_BODY, $content);
            } else {
                return '[Email Helper] There is nothing to send.';
            }
            if (!empty($this->attributes['sender'])) {
                $this->mailer->set(modMail::MAIL_SENDER, $this->attributes['sender']);
            }
            if (!empty($this->attributes['from'])) {
                $this->mailer->set(modMail::MAIL_FROM, $this->attributes['from']);
            }
            if (!empty($this->attributes['fromName'])) {
                $this->mailer->set(modMail::MAIL_FROM_NAME, $this->attributes['fromName']);
            }
            if (isset($this->attributes['setHTML'])) {
                $this->mailer->setHTML($this->attributes['setHTML']);
            }
            if (!empty($this->attributes['attach'])) {
                foreach ($this->attributes['attach'] as $data) {
                    list($file, $name, $encoding, $type) = array_values($data);
                    $this->mailer->attach($file, $name, $encoding, $type);
                }
            }
        }
        return '';
    }

    /**
     * Send the email, applying the attributes to the mailer before sending.
     * @return bool
     */
    public function send()
    {
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

    /**
     * Resets all PHPMailer attributes, including recipients and attachments.
     * @return $this
     */
    public function reset()
    {
        $this->mailer->reset();
        $this->attributes = array();

        return $this;
    }

    /**
     * Send the stored emails from the queue.
     * @param string $name Name of the queue
     * @return bool
     */
    public function sendFromQueue($name = 'emails')
    {
        $emails = cache($name, 'queue');
        if (!empty($emails) && is_array($emails)) {
            cache()->delete($name, 'queue');
            foreach ($emails as $data) {
                $this->attributes = $data;
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
     * @param string|bool $name Name of the queue.
     * @param bool $clear Replace the existing queue.
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
            $emails = array($this->attributes);
        } else {
            $emails[] = $this->attributes;
        }
        cache()->set($name, $emails, 'queue');

        return $this;
    }
    /**
     * Store the email to the cache.
     * @param string $name Name of the queue
     * @param bool $clear Replace the existing queue
     * @return $this
     */
    public function queue($name = 'emails', $clear = false)
    {
        return $this->toQueue($name, $clear);
    }

    /**
     * Store the email to the cache.
     * @param string $name Name of the queue
     * @param bool $clear Replace the existing queue
     * @return Mailer
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
    /**
     * Send the stored emails.
     * @param string $name Name of the queue
     * @return bool
     */
    public function queued($name = 'emails')
    {
        return $this->sendFromQueue($name);
    }

    /**
     * Return an array of attributes.
     * @param bool $toString
     * @return array|string
     */
    public function toArray($toString = false)
    {
        if ( !isset($this->attributes['content']) && isset($this->attributes['tpl']) && $chunk = $this->attributes['tpl']['name'] ) {
            $this->attributes['content'] = $this->modx->getChunk($chunk, $this->attributes['tpl']['data']);
        }
        return $toString ? print_str($this->attributes, 1) : $this->attributes;
    }

    /**
     * Output the attributes to the log. Can be used for testing on local machine.
     * @param bool $json Output format - FALSE for print_r, TRUE for json.
     */
    public function log($json = false)
    {
        if ( !isset($this->attributes['content']) && isset($this->attributes['tpl']) && $chunk = $this->attributes['tpl']['name'] ) {
            $this->attributes['content'] = $this->modx->getChunk($chunk, $this->attributes['tpl']['data']);
        }
        if ($json) {
            log_debug(json_encode($this->attributes), true);
        } else {
            log_debug(print_r($this->attributes, 1), true);
        }
    }
}
