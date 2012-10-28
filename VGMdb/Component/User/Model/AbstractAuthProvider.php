<?php

namespace VGMdb\Component\User\Model;

/**
 * Storage agnostic auth provider
 */
abstract class AbstractAuthProvider implements \Serializable
{
    /**
     * Serializes the auth provider.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->user_id,
            $this->provider,
            $this->provider_id,
            $this->access_token,
            $this->enabled,
        ));
    }

    /**
     * Unserializes the auth provider.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        list(
            $this->id,
            $this->user_id,
            $this->provider,
            $this->provider_id,
            $this->access_token,
            $this->enabled,
        ) = $data;
    }
}
