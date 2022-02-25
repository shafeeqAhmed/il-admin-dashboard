<?php

namespace App\Broadcasting;

use App\User;

class SmsMessage
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct($content = '')
    {
        $this->content = $content;
    }
    /**
     * Set the message content.
     *
     * @param string $content
     *
     * @return $this
     */
    public function content(string $content)
    {
        $this->content = trim($content);
        return $this;
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\User  $user
     * @return array|bool
     */
    public function join(User $user)
    {
        //
    }
}
