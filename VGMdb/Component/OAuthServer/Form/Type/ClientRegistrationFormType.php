<?php

namespace VGMdb\Component\OAuthServer\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Client registration form.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ClientRegistrationFormType extends AbstractType
{
    private $class;

    /**
     * @param string $class The Client class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, array('label' => 'Name'))
                ->add('redirect_uris', null, array('label' => 'Redirect URI'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'  => 'client_registration',
        ));
    }

    public function getName()
    {
        return 'client_registration';
    }
}
