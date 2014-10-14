<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;

class DefaultChannelData extends AbstractDefaultChannelDataFixture
{
    const PREFERABLE_CHANNEL_TYPE = 'custom';

    const FORM_TYPE = 'orocrm_contact_us.embedded_form';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $entity = 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest';

        $forms = $this->em->getRepository('OroEmbeddedFormBundle:EmbeddedForm')
            ->findBy(['formType' => self::FORM_TYPE]);

        $existingRecords =  $this->getRowCount($entity);
        $shouldBeCreated =  $existingRecords || !empty($forms);
        if ($shouldBeCreated) {
            /** @var Channel|null $channel */
            $channel = $this->em->getRepository('OroCRMChannelBundle:Channel')
                ->findOneBy(['channelType' => self::PREFERABLE_CHANNEL_TYPE]);

            if (!$channel) {
                $builder = $this->container->get('orocrm_channel.builder.factory')->createBuilder();
            } else {
                $builder = $this->container->get('orocrm_channel.builder.factory')->createBuilderForChannel($channel);
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
