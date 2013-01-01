<?php

namespace VGMdb\Component\OAuthServer\Model;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
interface ClientManagerInterface
{
    /**
     * @return ClientInterface
     */
    function createClient();

    /**
     * @return string
     */
    function getClass();

    /**
     * @return ClientInterface
     */
    function findClientBy(array $criteria);

    /**
     * @return ClientInterface
     */
    function findClientByPublicId($publicId);

    /**
     * @param ClientInterface
     */
    function updateClient(ClientInterface $client);

    /**
     * @param ClientInterface
     */
    function deleteClient(ClientInterface $client);
}
