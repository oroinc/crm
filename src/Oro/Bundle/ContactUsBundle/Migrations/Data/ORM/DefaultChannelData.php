<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\ContactUsBundle\Form\Type\ContactRequestType;
use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm;

/**
 * Loads "custom" default channel.
 */
class DefaultChannelData extends AbstractDefaultChannelDataFixture
{
    public const PREFERABLE_CHANNEL_TYPE = 'custom';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $forms = $manager->getRepository(EmbeddedForm::class)
            ->findBy(['formType' => ContactRequestType::class]);

        $existingRecords = $this->getRowCount($manager, ContactRequest::class);
        $shouldBeCreated = $existingRecords || !empty($forms);
        if ($shouldBeCreated) {
            /** @var Channel|null $channel */
            $channel = $manager->getRepository(Channel::class)
                ->findOneBy(['channelType' => self::PREFERABLE_CHANNEL_TYPE]);

            $factory = $this->container->get('oro_channel.builder.factory');
            $builder = $channel
                ? $factory->createBuilderForChannel($channel)
                : $factory->createBuilder();
            $builder->setStatus(Channel::STATUS_ACTIVE);
            $builder->addEntity(ContactRequest::class);

            $channel = $builder->getChannel();

            /** @var EmbeddedForm|ChannelAwareInterface $form hack with interface because this is extended field*/
            foreach ($forms as $form) {
                if (!$form->getDataChannel()) {
                    $form->setDataChannel($channel);
                }
            }

            $manager->persist($channel);
            $manager->flush();

            if ($existingRecords) {
                $this->fillChannelToEntity($manager, $channel, ContactRequest::class);
            }
        }
    }
}
