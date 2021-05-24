<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\EmailCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\PhoneCollectionType;
use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\ContactBundle\Form\Type\ContactType;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Form\Type\OroBirthdayType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Oro\Bundle\UserBundle\Form\Type\GenderType;
use Oro\Bundle\UserBundle\Form\Type\OrganizationUserAclSelectType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new ContactType();
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->configureOptions($resolver);
    }

    public function testBuildForm()
    {
        $fields = [
            ['namePrefix', TextType::class],
            ['firstName', TextType::class],
            ['middleName', TextType::class],
            ['lastName', TextType::class],
            ['nameSuffix', TextType::class],
            ['gender', GenderType::class],
            ['birthday', OroBirthdayType::class],
            ['description', OroResizeableRichTextType::class],
            ['jobTitle', TextType::class],
            ['fax', TextType::class],
            ['skype', TextType::class],
            ['twitter', TextType::class],
            ['facebook', TextType::class],
            ['googlePlus', TextType::class],
            ['linkedIn', TextType::class],
            ['picture', ImageType::class],
            ['source', TranslatableEntityType::class],
            ['assignedTo', OrganizationUserAclSelectType::class],
            ['reportsTo', ContactSelectType::class],
            ['method', TranslatableEntityType::class],
            ['addresses', AddressCollectionType::class],
            ['emails', EmailCollectionType::class],
            ['phones', PhoneCollectionType::class],
            ['groups', EntityType::class],
            ['appendAccounts', EntityIdentifierType::class],
            ['removeAccounts', EntityIdentifierType::class]
        ];

        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->exactly(count($fields)))
            ->method('add')
            ->withConsecutive(...$fields)
            ->willReturnSelf();

        $this->type->buildForm($builder, []);
    }
}
