<?php

namespace App\Repositories\Eloquent;

use App\Models\UserProfile;
use App\Repositories\Interfaces\ProfileRepositoryInterface;

class ProfileRepository extends BaseRepository implements ProfileRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(UserProfile $model)
    {
        parent::__construct($model);
    }
}
