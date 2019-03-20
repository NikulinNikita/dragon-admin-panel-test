<?php

namespace Admin\Policies;

use App\Models\Staff;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Section;

class BaseSectionModelPolicy
{
    use HandlesAuthorization;

    public function display(Staff $adminUser, Section $section, Model $item)
    {
        $table  = $section->getModel()->getTable();
        $result = $adminUser->isAbleTo(['manage_everything', "manage_{$table}"]);

        return $result;
    }

    public function create(Staff $adminUser, Section $section, Model $item)
    {
        $table  = $section->getModel()->getTable();
        $result = $adminUser->isAbleTo(['manage_everything', "manage_{$table}"]);

        return $result;
    }

    public function edit(Staff $adminUser, Section $section, Model $item)
    {
        $table  = $section->getModel()->getTable();
        $result = $adminUser->isAbleTo(['manage_everything', "manage_{$table}"]);

        return $result;
    }

    public function delete(Staff $adminUser, Section $section, Model $item)
    {
        $table  = $section->getModel()->getTable();
        $result = $adminUser->isAbleTo(['manage_everything', "manage_{$table}"]);

        return $result;
    }
}
