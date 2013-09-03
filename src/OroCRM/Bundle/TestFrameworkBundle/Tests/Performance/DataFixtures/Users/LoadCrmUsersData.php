<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * TODO: This class should be refactored (BAP-975)
 */
class LoadCrmUsersData extends AbstractFixture implements ContainerAwareInterface
{
    const USERS_NUMBER = 200;
    const USER_PASSWORD = '123123q';
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $userRepository;

    protected $firstNamesDictionary = null;
    protected $lastNamesDictionary = null;
    protected $role;
    protected $businessUnit;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->userManager = $container->get('oro_user.manager');
        $this->userRepository = $this->userManager->getFlexibleRepository();
        $this->role = $this->userManager->getStorageManager()->getRepository('OroUserBundle:Role')
            ->findBy(array('role' => 'ROLE_USER'));
        $this->businessUnitManager = $container->get('oro_organization.business_unit_manager');
        $this->businessUnit = $this->businessUnitManager->getBusinessUnitRepo()->findAll();
        $this->businessUnit = $this->businessUnit[0];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadAttributes();
        $this->loadUsers();
    }

    /**
     * Load attributes
     *
     * @return void
     */
    public function loadAttributes()
    {
        $this->assertHasRequiredAttributes(array('company', 'gender'));

        if (!$this->findAttribute('website')) {
            $websiteAttribute = $this->createAttribute('oro_flexibleentity_url', 'website');
            $this->persist($websiteAttribute);
        }

        if (!$this->findAttribute('hobby')) {
            $hobbyAttribute = $this->createAttributeWithOptions(
                'oro_flexibleentity_multiselect',
                'hobby',
                self::getHobbies()
            );
            $this->persist($hobbyAttribute);
        }

        // if (!$this->findAttribute('last_visit')) {
        //     $lastVisitAttribute = $this->createAttribute(new DateTimeType(), 'last_visit');
        //     $this->persist($lastVisitAttribute);
        // }

        $this->flush();
    }

    /**
     * Asserts required attributes were created
     *
     * @param array $attributeCodes
     * @throws \LogicException
     */
    private function assertHasRequiredAttributes($attributeCodes)
    {
        foreach ($attributeCodes as $attributeCode) {
            if (!$this->findAttribute($attributeCode)) {
                throw new \LogicException(
                    sprintf(
                        'Attribute "%s" is missing, please load "%s" fixture before',
                        $attributeCode,
                        'Acme\Bundle\DemoBundle\DataFixtures\ORM\LoadUserAttrData'
                    )
                );
            }
        }
    }

    /**
     * Load users
     *
     * @return void
     */
    public function loadUsers()
    {
        for ($i = 0; $i < self::USERS_NUMBER; ++$i) {
            $firstName = $this->generateFirstName();
            $lastName = $this->generateLastName();
            $middleName = $this->generateMiddleName();
            $birthday = $this->generateBirthday();
            $salary = $this->generateSalary();
            $username = $this->generateUsername($firstName, $lastName);
            $email = $this->generateEmail($firstName, $lastName);
            $company = $this->generateCompany();
            $website = $this->generateWebsite($firstName, $lastName);
            $gender = $this->generateGender();
            $hobbies = $this->generateHobbies();
            $lastVisit = $this->generateLastVisit();

            $user = $this->createUser(
                $username,
                $email,
                $firstName,
                $lastName,
                $middleName,
                $birthday,
                $salary,
                $company,
                $website,
                $gender,
                $hobbies,
                $lastVisit
            );

            $user->setPlainPassword(self::USER_PASSWORD);
            $this->userManager->updatePassword($user);

            $this->persist($user);
        }
        $this->flush();
    }

    /**
     * Creates a user
     *
     * @param string $username
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $middleName
     * @param \DateTime $birthday
     * @param int $salary
     * @param string $company
     * @param string $website
     * @param string $gender
     * @param array $hobbies
     * @param \DateTime $lastVisit
     * @return User
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * TODO: This method should be refactored (BAP-975)
     */
    private function createUser(
        $username,
        $email,
        $firstName,
        $lastName,
        $middleName,
        $birthday,
        $salary,
        $company,
        $website,
        $gender,
        array $hobbies,
        $lastVisit
    ) {
        /** @var $user User */
        $user = $this->userManager->createFlexible();

        $user->setEmail($email);
        $user->setUsername($username);
        $user->setFirstname($firstName);
        //$user->setMiddlename($middleName);
        $user->setLastname($lastName);
        $user->setBirthday($birthday);
        $user->addRole($this->role[0]);
        $user->setOwner($this->businessUnit);

        $this->setFlexibleAttributeValue($user, 'company', $company);
        //$this->setFlexibleAttributeValue($user, 'salary', $salary);
        $this->setFlexibleAttributeValueOption($user, 'gender', $gender);
        //$this->setFlexibleAttributeValue($user, 'website', $website);
        //$this->addFlexibleAttributeValueOptions($user, 'hobby', $hobbies);
        // $this->setFlexibleAttributeValue($user, 'last_visit', $lastVisit);

        return $user;
    }

    /**
     * Sets a flexible attribute value
     *
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @return void
     * @throws \LogicException
     */
    private function setFlexibleAttributeValue(AbstractFlexible $flexibleEntity, $attributeCode, $value)
    {
        if ($attribute = $this->findAttribute($attributeCode)) {
            $this->getFlexibleValueForAttribute($flexibleEntity, $attribute)->setData($value);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Sets a flexible attribute value as option with given value
     *
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @return void
     * @throws \LogicException
     */
    private function setFlexibleAttributeValueOption(AbstractFlexible $flexibleEntity, $attributeCode, $value)
    {
        if ($attribute = $this->findAttribute($attributeCode)) {
            $option = $this->findAttributeOptionWithValue($attribute, $value);
            $this->getFlexibleValueForAttribute($flexibleEntity, $attribute)->setOption($option);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Adds option values to flexible attribute value
     *
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param array $values
     * @return void
     * @throws \LogicException
     */
    private function addFlexibleAttributeValueOptions(AbstractFlexible $flexibleEntity, $attributeCode, array $values)
    {
        if ($attribute = $this->findAttribute($attributeCode)) {
            $flexibleValue = $this->getFlexibleValueForAttribute($flexibleEntity, $attribute);
            foreach ($values as $value) {
                $option = $this->findAttributeOptionWithValue($attribute, $value);
                $flexibleValue->addOption($option);
            }
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Finds an attribute option with value
     *
     * @param AbstractAttribute $attribute
     * @param string $value
     * @return AbstractAttributeOption
     * @throws \LogicException
     */
    private function findAttributeOptionWithValue(AbstractAttribute $attribute, $value)
    {
        /** @var $options \Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption[] */
        $options = $this->userManager->getAttributeOptionRepository()->findBy(
            array('attribute' => $attribute)
        );

        $selectedOption = null;
        foreach ($options as $option) {
            if ($value == $option->getOptionValue()->getValue()) {
                return $option;
            }
        }

        throw new \LogicException(sprintf('Cannot find attribute option with value "%s"', $value));
    }

    /**
     * Gets or creates a flexible value for attribute
     *
     * @param AbstractFlexible $flexibleEntity
     * @param AbstractAttribute $attribute
     * @return FlexibleValueInterface
     */
    private function getFlexibleValueForAttribute(AbstractFlexible $flexibleEntity, AbstractAttribute $attribute)
    {
        $flexibleValue = $flexibleEntity->getValue($attribute->getCode());
        if (!$flexibleValue) {
            $flexibleValue = $this->userManager->createFlexibleValue();
            $flexibleValue->setAttribute($attribute);
            $flexibleEntity->addValue($flexibleValue);
        }
        return $flexibleValue;
    }

    /**
     * Finds an attribute
     *
     * @param string $attributeCode
     * @return AbstractAttribute
     */
    private function findAttribute($attributeCode)
    {
        return $this->userRepository->findAttributeByCode($attributeCode);
    }

    /**
     * Create an attribute
     *
     * @param string $attributeType
     * @param string $attributeCode
     * @return AbstractAttribute
     */
    private function createAttribute($attributeType, $attributeCode)
    {
        $result = $this->userManager->createAttribute($attributeType);
        $result->setCode($attributeCode);
        $result->setLabel($attributeCode);

        return $result;
    }

    /**
     * Create an attribute with options
     *
     * @param string $attributeType
     * @param string $attributeCode
     * @param array $optionValues
     * @return AbstractAttribute
     */
    private function createAttributeWithOptions(
        $attributeType,
        $attributeCode,
        array $optionValues
    ) {
        $attribute = $this->createAttribute($attributeType, $attributeCode);
        foreach ($optionValues as $value) {
            $attribute->addOption($this->createAttributeOptionWithValue($value));
        }
        return $attribute;
    }

    /**
     * Create an attribute option with value
     *
     * @param string $value
     * @return AbstractAttributeOption
     */
    private function createAttributeOptionWithValue($value)
    {
        $option = $this->userManager->createAttributeOption();
        $optionValue = $this->userManager->createAttributeOptionValue()->setValue($value);
        $option->addOptionValue($optionValue);
        return $option;
    }

    /**
     * Generates a username
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    private function generateUsername($firstName, $lastName)
    {
        return sprintf("%s.%s", strtolower($firstName), strtolower($lastName));
    }

    /**
     * Generates an email
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    private function generateEmail($firstName, $lastName)
    {
        $domains = array('yahoo.com', 'gmail.com', 'example.com', 'hotmail.com', 'aol.com', 'msn.com');
        $randomIndex = rand(0, count($domains) - 1);
        $domain = $domains[$randomIndex];
        return sprintf("%s.%s@%s", strtolower($firstName), strtolower($lastName), $domain);
    }

    /**
     * Generate a first name
     *
     * @return string
     */
    private function generateFirstName()
    {
        if (is_null($this->firstNamesDictionary)) {
            $this->firstNamesDictionary = $this->loadDictionary('first_names.txt');
            shuffle($this->firstNamesDictionary);
        }

        $randomIndex = rand(0, count($this->firstNamesDictionary) - 1);
        $randomName = $this->firstNamesDictionary[$randomIndex];
        unset($this->firstNamesDictionary[$randomIndex]);
        if (!is_null($this->firstNamesDictionary)) {
            $this->firstNamesDictionary = array_values($this->firstNamesDictionary);
        }
        return trim($randomName);
    }

    /**
     * Generate a middle name
     *
     * @return string
     */
    private function generateMiddleName()
    {
        return '';
    }

    /**
     * Loads dictionary from file by name
     *
     * @param string $name
     * @return array
     */
    private function loadDictionary($name)
    {
        static $dictionaries = array();

        if (!isset($dictionaries[$name])) {
            $dictionary = array();
            $fileName = __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
            foreach (file($fileName) as $item) {
                $dictionary[] = trim($item);
            }
            $dictionaries[$name] = $dictionary;
        }

        return $dictionaries[$name];
    }

    /**
     * Generates a last name
     *
     * @return string
     */
    private function generateLastName()
    {
        if (is_null($this->lastNamesDictionary)) {
            $this->lastNamesDictionary = $this->loadDictionary('last_names.txt');
            shuffle($this->lastNamesDictionary);
        }

        $randomIndex = rand(0, count($this->lastNamesDictionary) - 1);
        $randomName = $this->lastNamesDictionary[$randomIndex];
        unset($this->lastNamesDictionary[$randomIndex]);
        if (!is_null($this->lastNamesDictionary)) {
            $this->lastNamesDictionary = array_values($this->lastNamesDictionary);
        }
        return trim($randomName);
    }

    /**
     * Generates a salary
     *
     * @return int
     */
    private function generateSalary()
    {
        return 12 * rand(4000, 30000);
    }

    /**
     * Generates a company name
     *
     * @return string
     */
    private function generateCompany()
    {
        $companyNamesDictionary = $this->loadDictionary('company_names.txt');
        $randomIndex = rand(0, count($companyNamesDictionary) - 1);

        return trim($companyNamesDictionary[$randomIndex]);
    }

    /**
     * Generates a date of birth
     *
     * @return \DateTime
     */
    private function generateBirthday()
    {
        // Convert to timetamps
        $min = strtotime('1950-01-01');
        $max = strtotime('2000-01-01');

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return new \DateTime(date('Y-m-d', $val), new \DateTimeZone('UTC'));
    }

    /**
     * Generates a website
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    private function generateWebsite($firstName, $lastName)
    {
        $domain = 'example.com';
        return sprintf("http://%s%s.%s", strtolower($firstName), strtolower($lastName), $domain);
    }

    /**
     * Generates a gender
     *
     * @return string
     */
    private function generateGender()
    {
        $genders = array('Male', 'Female');
        return $genders[rand(0, 1)];
    }

    /**
     * Generates hobbies
     *
     * @return string
     */
    private function generateHobbies()
    {
        $hobbies = self::getHobbies();
        $randomCount = rand(1, count($hobbies));
        shuffle($hobbies);
        return array_slice($hobbies, 0, $randomCount);
    }

    /**
     * Generates hobbies
     *
     * @return \DateTime
     */
    private function generateLastVisit()
    {
        $lastVisit = new \DateTime('now', new \DateTimeZone('UTC'));
        $lastVisit->sub(new \DateInterval('P' . rand(1, 30) . 'D'));
        return $lastVisit;
    }

    /**
     * Get array of hobbies
     *
     * @return array
     */
    private static function getHobbies()
    {
        return array('Sport', 'Cooking', 'Read', 'Coding!');
    }

    /**
     * Persist object
     *
     * @param mixed $object
     * @return void
     */
    private function persist($object)
    {
        $this->userManager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     *
     * @return void
     */
    private function flush()
    {
        $this->userManager->getStorageManager()->flush();
    }
}
