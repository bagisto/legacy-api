<?php

namespace Webkul\API\Models;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Webkul\Customer\Models\Customer as BaseCustomer;

class Customer extends BaseCustomer implements JWTSubject
{
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
