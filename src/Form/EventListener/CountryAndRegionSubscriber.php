<?php
/**
 * Class CountryAndRegionSubscriber
 *
 * @category  Aligent
 * @package   Aligent\ShippingEstimatorBundle\Form\EventListener
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
namespace Aligent\ShippingEstimatorBundle\Form\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Repository\RegionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

class CountryAndRegionSubscriber implements EventSubscriberInterface
{
    protected ManagerRegistry $managerRegistry;
    protected FormFactoryInterface $factory;

    public function __construct(
        ManagerRegistry $managerRegistry,
        FormFactoryInterface $factory,
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->factory = $factory;
    }

    /**
     * @return array<string, mixed> The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * Removes or adds a region field based on the country set on submitted form.
     */
    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        /** @var Country $country */
        $country = $this->managerRegistry->getRepository(Country::class)
            ->find($data['country'] ?? false);

        if ($country != null && $country->hasRegions()) {
            $form = $event->getForm();

            $config = $form->get('region')->getConfig()->getOptions();
            unset($config['choices']);

            $config['country'] = $country;
            $config['query_builder'] = $this->getRegionClosure($country);

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $form->add(
                $this->factory->createNamed(
                    'region',
                    get_class($form->get('region')->getConfig()->getType()->getInnerType()),
                    null,
                    $config
                )
            );

            if (!$form->getData()
                || !$form->getData()->getRegionText()
                || !empty($data['region'])
            ) {
                // do not allow saving text region in case when region was checked from list
                // except when in base data region text existed
                unset($data['region_text']);
            }
        } else {
            // do not allow saving region select in case when region was filled as text
            unset($data['region']);
        }

        $event->setData($data);
    }

    protected function getRegionClosure(Country $country): callable
    {
        return function (RegionRepository $regionRepository) use ($country) {
            return $regionRepository->getCountryRegionsQueryBuilder($country);
        };
    }
}
