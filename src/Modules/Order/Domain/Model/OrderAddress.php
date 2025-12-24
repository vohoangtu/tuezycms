<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Model;

class OrderAddress
{
    private string $fullName;
    private string $phone;
    private string $email;
    private string $address;
    private string $ward;
    private string $district;
    private string $province;
    private ?string $postalCode = null;

    public function __construct(
        string $fullName,
        string $phone,
        string $email,
        string $address,
        string $ward,
        string $district,
        string $province,
        ?string $postalCode = null
    ) {
        $this->fullName = $fullName;
        $this->phone = $phone;
        $this->email = $email;
        $this->address = $address;
        $this->ward = $ward;
        $this->district = $district;
        $this->province = $province;
        $this->postalCode = $postalCode;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getWard(): string
    {
        return $this->ward;
    }

    public function getDistrict(): string
    {
        return $this->district;
    }

    public function getProvince(): string
    {
        return $this->province;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getFullAddress(): string
    {
        return sprintf(
            '%s, %s, %s, %s',
            $this->address,
            $this->ward,
            $this->district,
            $this->province
        );
    }
}

