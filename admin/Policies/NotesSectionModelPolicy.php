<?php

namespace Admin\Policies;

use App\Models\Staff;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Section;

class NotesSectionModelPolicy extends BaseSectionModelPolicy
{
    public function display(Staff $adminUser, Section $section, Model $item)
    {
        $table = $section->getModel()->getTable();

        return $adminUser->isAbleTo(['manage_everything', "manage_staff_notes", "manage_user_notes"]);
    }

    public function create(Staff $adminUser, Section $section, Model $item)
    {
        return $adminUser->isAbleTo(['manage_everything', "manage_staff_notes", "manage_user_notes"]) &&
               ! (\Request::segment(2) === 'notes' && \Request::segment(3) === null);
    }

    public function edit(Staff $adminUser, Section $section, Model $item)
    {
        $table = $section->getModel()->getTable();

        return $adminUser->isAbleTo(['manage_everything', "manage_staff_notes", "manage_user_notes"]);
    }

    public function delete(Staff $adminUser, Section $section, Model $item)
    {
        $table = $section->getModel()->getTable();

        return $adminUser->isAbleTo(['manage_everything', "manage_staff_notes", "manage_user_notes"]);
    }
}
