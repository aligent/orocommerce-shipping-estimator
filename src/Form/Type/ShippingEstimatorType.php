<?php
/**
 * @category  Aligent
 * @package
 * @author    Chris Rossi <chris.rossi@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */
namespace Aligent\ShippingEstimatorBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\RegionType;
use Oro\Bundle\FrontendBundle\Form\Type\CountryType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ShippingEstimatorType extends AbstractType
{
    protected EventSubscriberInterface $subscriber;

    public function __construct(EventSubscriberInterface $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string,mixed> $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber($this->subscriber);

        $builder
            ->add(
                'country',
                CountryType::class,
                [
                    'required' => true,
                    'label' => 'oro.order.orderaddress.country.label',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'region',
                RegionType::class,
                [
                    'required' => false,
                    'label' => 'oro.order.orderaddress.region.label'
                ]
            )
            ->add(
                'region_text',
                HiddenType::class,
                [
                    'required' => false,
                    'random_id' => true,
                    'label' => 'oro.order.orderaddress.region_text.label'
                ]
            )
            ->add(
                'postcode',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'oro.order.orderaddress.postal_code.label',
                    'trim' => true,
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add('shoppingListId', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('region_route', 'oro_api_frontend_country_get_regions');
    }

    /**
     * @param FormView $view
     * @param FormInterface<array<int,mixed>> $form
     * @param array<string,mixed> $options
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (isset($options['region_route'])) {
            $view->vars['region_route'] = $options['region_route'];
        }
    }
}
