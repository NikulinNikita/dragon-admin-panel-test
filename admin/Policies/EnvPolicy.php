<?php

namespace Admin\Policies;

use App\Models\Staff;

class EnvPolicy
{

    public function before(Staff $adminUser, $envKey)
    {
        return false;
    }

    public function display(Staff $adminUser, $envKey)
    {
        return $adminUser->isAbleTo(['manage_everything']);
    }

    public function create(Staff $adminUser, $envKey)
    {
        return $adminUser->isAbleTo(['manage_everything']);
    }

    public function edit(Staff $adminUser, $envKey)
    {
        return $adminUser->isAbleTo(['manage_everything']);
    }

    public function delete(Staff $adminUser, $envKey)
    {
        return $adminUser->isAbleTo(['manage_everything']);
    }
}
