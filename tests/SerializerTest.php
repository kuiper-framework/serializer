<?php

/*
 * This file is part of the Kuiper package.
 *
 * (c) Ye Wenbin <wenbinye@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace kuiper\serializer;

use kuiper\helper\Enum;
use kuiper\reflection\ReflectionDocBlockFactory;
use kuiper\serializer\fixtures\Company;
use kuiper\serializer\fixtures\Customer;
use kuiper\serializer\fixtures\Gender;
use kuiper\serializer\fixtures\Member;
use kuiper\serializer\fixtures\Organization;
use kuiper\serializer\normalizer\DateTimeNormalizer;
use kuiper\serializer\normalizer\EnumNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * TestCase for Serializer.
 */
class SerializerTest extends TestCase
{
    public function createSerializer()
    {
        return new Serializer(ReflectionDocBlockFactory::getInstance(), [
            \DateTime::class => new DateTimeNormalizer(),
            Enum::class => new EnumNormalizer(),
        ]);
    }

    public function testDeserializeType(): void
    {
        $serializer = $this->createSerializer();
        $json = '{"name": "Les-Tilleuls.coop","members":[{"name":"K\\u00e9vin"}]}';
        $org = $serializer->fromJson($json, Organization::class);
        // print_r($org);

        $this->assertInstanceOf(Organization::class, $org);
        $this->assertEquals('Les-Tilleuls.coop', $org->getName());
        $members = $org->getMembers();
        $this->assertIsArray($members);
        $this->assertInstanceOf(Member::class, $members[0]);
        $this->assertEquals('Kévin', $members[0]->getName());
    }

    public function testDeserializeName(): void
    {
        $serializer = $this->createSerializer();
        $json = '{"org_name": "Acme Inc.", "org_address": "123 Main Street, Big City", "employers": "abc"}';
        $obj = $serializer->fromJson($json, Company::class);
        // print_r($obj);
        $this->assertEquals($obj->name, 'Acme Inc.');
        $this->assertNull($obj->employers);
    }

    public function testSerializeName(): void
    {
        $serializer = $this->createSerializer();
        $obj = new Company();
        $obj->name = 'Acme Inc.';
        $obj->address = '123 Main Street, Big City';
        $obj->employers = ['a', 'b'];
        $this->assertEquals($serializer->toJson($obj), '{"org_name":"Acme Inc.","org_address":"123 Main Street, Big City"}');
    }

    public function testSerializeType(): void
    {
        $serializer = $this->createSerializer();
        $org = new Organization();
        $org->setName('Les-Tilleuls.coop');
        $member = new Member();
        $member->setName('Kevin');
        $org->setMembers([$member]);

        $this->assertEquals(
            $serializer->toJson($org),
            '{"name":"Les-Tilleuls.coop","members":[{"name":"Kevin"}]}'
        );
    }

    public function testUnserializeArray(): void
    {
        $serializer = $this->createSerializer();
        $data = $serializer->fromJson('[{"name":"Les-Tilleuls.coop","members":[{"name":"Kevin"}]}]', Organization::class.'[]');
        $this->assertTrue(is_array($data));
        $this->assertInstanceOf(Organization::class, $data[0]);
        $this->assertEquals('Les-Tilleuls.coop', $data[0]->getName());
    }

    public function testTypeOverriderByProperty(): void
    {
        $serializer = $this->createSerializer();
        $result = $serializer->denormalize([
            'values' => [
                ['name' => 'john'],
            ],
        ], fixtures\TypeOverrideByProperty::class);
        $this->assertInstanceOf(Member::class, $result->getValues()[0]);
    }

    public function testIntTypeAutoconvert(): void
    {
        $serializer = $this->createSerializer();
        $result = $serializer->denormalize(['id' => 1], fixtures\User::class);
        $this->assertInstanceOf(fixtures\User::class, $result);
        // var_export($result);
        $this->assertEquals(1, $result->getId());
    }

    public function testIntTypeAutoconvertNull(): void
    {
        $serializer = $this->createSerializer();
        $result = $serializer->denormalize(['id' => null], fixtures\User::class);
        $this->assertInstanceOf(fixtures\User::class, $result);
        // var_export($result);
        $this->assertNull($result->getId());
    }

    public function testIntTypeSerialize(): void
    {
        $serializer = $this->createSerializer();
        $user = new fixtures\User();
        $user->setId(1);
        $result = $serializer->normalize($user);
        // var_export($result);
        $this->assertEquals(['id' => 1], $result);
    }

    public function testDateTimeSerialize(): void
    {
        $serializer = $this->createSerializer();
        $user = $this->createUser();
        $str = $serializer->toJson($user);
        $obj = $serializer->fromJson($str, fixtures\User::class);
        // print_r($obj);
        $this->assertInstanceOf(\DateTime::class, $obj->getBirthday());
    }

    public function testDateTimeJsonSerialize(): void
    {
        $serializer = $this->createSerializer();
        $user = $this->createUser();
        $str = json_encode($user);
        // echo $str;
        /** @var fixtures\User $obj */
        $obj = $serializer->fromJson($str, fixtures\User::class);
        // print_r($obj);
        $this->assertInstanceOf(\DateTime::class, $obj->getBirthday());
        $this->assertEquals(Gender::MALE(), $obj->getGender());
    }

    private function createUser(): fixtures\User
    {
        $user = new fixtures\User();
        $user->setId(1)
            ->setBirthday(new \DateTime())
            ->setGender(Gender::MALE());

        return $user;
    }

    public function testStrictType(): void
    {
        $serializer = $this->createSerializer();
        $customer = $serializer->denormalize(['id' => '1'], Customer::class);
        // print_r($customer);
        $this->assertInstanceOf(Customer::class, $customer);
    }
}
