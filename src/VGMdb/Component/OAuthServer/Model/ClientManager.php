<?php

namespace VGMdb\Component\OAuthServer\Model;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
abstract class ClientManager implements ClientManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createClient()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     * {@inheritdoc}
     */
    public function findClientByPublicId($publicId)
    {
        if (false === $pos = strpos($publicId, '.')) {
            return null;
        }

        $id       = substr($publicId, 0, $pos);
        $randomId = substr($publicId, $pos + 1);

        return $this->findClientBy(array(
            'id'        => $id,
            'random_id' => $randomId,
        ));
    }
}
