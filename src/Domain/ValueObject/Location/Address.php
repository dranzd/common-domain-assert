<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Location;

use Dranzd\Common\EventSourcing\Domain\ValueObject\ArrayValueObjectTranslatorCapable;
use Dranzd\Common\EventSourcing\Domain\ValueObject\CompareSameValueTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Addressee;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as City;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Company;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Country;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Province;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Street;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as ZipCode;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ToStringUsingObjectHashTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;
use Dranzd\Common\EventSourcing\Util\ArrayUtil;

final class Address implements ValueObject, ArrayValueObjectTranslatorCapable
{
    use CompareSameValueTrait, ToStringUsingObjectHashTrait;

    /**
     * @var Company
     */
    private $company;

    /**
     * @var Addressee
     */
    private $addressee;

    /**
     * @var Street
     */
    private $street1;

    /**
     * @var Street
     */
    private $street2;

    /**
     * @var City
     */
    private $city;

    /**
     * @var Province
     */
    private $province;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var ZipCode
     */
    private $zipCode;

    private function __construct(
        Company $company,
        Addressee $addressee,
        Street $street1,
        Street $street2,
        City $city,
        Province $province,
        Country $country,
        ZipCode $zipCode
    ) {
        $this->company = $company;
        $this->addressee = $addressee;
        $this->street1 = $street1;
        $this->street2 = $street2;
        $this->city = $city;
        $this->province = $province;
        $this->country = $country;
        $this->zipCode = $zipCode;
    }

    final public static function notProvided(): Address
    {
        return self::fromArray(['', '', '', '', '', '', '', '']);
    }

    final public static function fromArray(array $array): Address
    {
        if (isset($array[0])) {
            return \call_user_func_array(['static', 'fromNative'], $array);
        }

        \extract(ArrayUtil::keyToCamelCase($array));

        return static::fromNative(
            $company,
            $addressee,
            $street1,
            $street2,
            $city,
            $province,
            $country,
            $zipCode
        );
    }

    final public function sameValueAs(ValueObject $object): bool
    {
        return $this->checkSameValueAs($object);
    }

    final public function toArray(): array
    {
        return [
            'company' => $this->company->toNative(),
            'addressee' => $this->addressee->toNative(),
            'street1' => $this->street1->toNative(),
            'street2' => $this->street2->toNative(),
            'city' => $this->city->toNative(),
            'province' => $this->province->toNative(),
            'country' => $this->country->toNative(),
            'zip_code' => $this->zipCode->toNative(),
        ];
    }

    private static function fromNative(): Address
    {
        $company = (string) \func_get_arg(0);
        $addressee = (string) \func_get_arg(1);
        $street1 = (string) \func_get_arg(2);
        $street2 = (string) \func_get_arg(3);
        $city = (string) \func_get_arg(4);
        $province = (string) \func_get_arg(5);
        $country = (string) \func_get_arg(6);
        $zipCode = (string) \func_get_arg(7);

        $company = Company::fromNative($company);
        $addressee = Addressee::fromNative($addressee);
        $street1 = Street::fromNative($street1);
        $street2 = Street::fromNative($street2);
        $city = City::fromNative($city);
        $province = Province::fromNative($province);
        $country = Country::fromNative($country);
        $zipCode = ZipCode::fromNative($zipCode);

        return new self(
            $company,
            $addressee,
            $street1,
            $street2,
            $city,
            $province,
            $country,
            $zipCode
        );
    }
}
