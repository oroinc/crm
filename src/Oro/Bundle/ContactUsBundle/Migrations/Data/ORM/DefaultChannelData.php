<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\ContactUsBundle\Form\Type\ContactRequestType;
use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm;

class DefaultChannelData extends AbstractDefaultChannelDataFixture
{
    const PREFERABLE_CHANNEL_TYPE = 'custom';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $entity = 'Oro\Bundle\ContactUsBundle\Entity\ContactRequest';

        $forms = $this->em->getRepository('OroEmbeddedFormBundle:EmbeddedForm')
            ->findBy(['formType' => ContactRequestType::class]);

        $existingRecords =  $this->getRowCount($entity);
        $shouldBeCreated =  $existingRecords || !empty($forms);
        if ($shouldBeCreated) {
            /** @var Channel|null $channel */
            $channel = $this->em->getRepository('OroChannelBundle:Channel')
                ->findOneBy(['channelType' => self::PREFERABLE_CHANNEL_TYPE]);

            if (!$channel) {
                $builder = $this->container->get('oro_channel.builder.factory')->createBuilder();
            } else {
                $builder = $this->container->get('oro_channel.builder.factory')->createBuilderForChannel($channel);
            }

            $builder->setStatus(Channel::STATUS_ACTIVE);
            $builder->addEntity($entity);

            $channel = $builder->getChannel();

            /** @var EmbeddedForm|ChannelAwareInterface $form hack with interface because this is extended field*/
            foreach ($forms as $form) {
                if (!$form->getDataChannel()) {
                    $form->setDataChannel($channel);
                }
            }

            $this->em->persist($channel);
            $this->em->flush();

            if ($existingRecords) {
                $this->fillChannelToEntity($channel, $entity);
            }
        }
    }
}
