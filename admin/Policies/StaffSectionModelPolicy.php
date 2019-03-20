<?php

namespace Admin\Policies;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Section;

class StaffSectionModelPolicy extends BaseSectionModelPolicy
{
    public function before(Staff $adminUser, $ability, Section $section, Model $item)
    {
        $table  = $section->getModel()->getTable();
        $result = $adminUser->isAbleTo(['manage_everything', "manage_{$table}"]);

        if($item->exists && $item->hasAnyRole(['superadmin', 'general', 'accountant']) && !$adminUser->isAbleTo(['manage_everything'])) {
            return false;
        }

        if ( ! $item->exists || $adminUser->isAbleTo(['manage_everything']) || $adminUser->hasRole('general')) {
            return $result;
        }

        $result = $adminUser->hasRole('manager') && $item->hasRole('dealer');

        return $result;
    }
}
