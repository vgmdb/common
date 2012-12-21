<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Aura\Marshal\Manager;
use Aura\Marshal\Type\Builder as TypeBuilder;
use Aura\Marshal\Relation\Builder as RelationBuilder;

/**
 * Provides domain object marshalling.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DomainObjectServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['object_manager'] = $app->share(function () use ($app) {
            // the object manager is a factory that gives you domain objects
            // it contains a data object marshal that aggregates multiple entities from different sources
            // a dispatcher propagates events to listeners
        });

        $app['object_marshal'] = $app->share(function () use ($app) {
            $manager = new Manager(new TypeBuilder, new RelationBuilder, array(
                'users' => array(
                    'identity_field' => 'id',
                    'relation_names' => array(
                        'auth_providers' => array(
                            'relationship' => 'has_many',
                            'native_field' => 'id',
                            'foreign_field' => 'user_id'
                        ),
                        'roles' => array(
                            'relationship' => 'has_many',
                            'native_field' => 'id',
                            'foreign_field' => 'user_id'
                        ),
                        'last_login' => array(
                            'relationship' => 'has_one',
                            'foreign_type' => 'last_logins',
                            'native_field' => 'id',
                            'foreign_field' => 'id'
                        )
                    )
                ),
                'auth_providers' => array(
                    'identity_field' => 'id',
                    'index_fields' => array('user_id'),
                    'relation_names' => array(
                        'user' => array(
                            'relationship' => 'belongs_to',
                            'foreign_type' => 'users',
                            'native_field' => 'user_id',
                            'foreign_field' => 'id'
                        )
                    )
                ),
                'roles' => array(
                    'identity_field' => 'id',
                    'index_fields' => array('user_id'),
                    'relation_names' => array(
                        'user' => array(
                            'relationship' => 'belongs_to',
                            'foreign_type' => 'users',
                            'native_field' => 'user_id',
                            'foreign_field' => 'id'
                        )
                    )
                ),
                'last_logins' => array(
                    'identity_field' => 'id',
                    'relation_names' => array(
                        'user' => array(
                            'relationship' => 'belongs_to',
                            'foreign_type' => 'users',
                            'native_field' => 'id',
                            'foreign_field' => 'id'
                        )
                    )
                )
            ));

            return $manager;
        });
    }

    public function boot(Application $app)
    {
    }
}
