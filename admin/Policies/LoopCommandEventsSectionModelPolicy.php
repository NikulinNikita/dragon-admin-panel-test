<?php

namespace Admin\Policies;

use App\Models\Staff;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Section;

class LoopCommandEventsSectionModelPolicy extends BaseSectionModelPolicy
{
    public function display(Staff $adminUser, Section $section, Model $item)
    {
        $table = $section->getModel()->getTable();

        return $adminUser->isAbleTo(['manage_everything', "manage_baccarat_loop_command_events", "manage_roulette_loop_command_events"]);
    }

    public function create(Staff $adminUser, Section $section, Model $item)
    {
        return $adminUser->isAbleTo(['manage_everything', "manage_baccarat_loop_command_events", "manage_roulette_loop_command_events"]) &&
               ! (\Request::segment(2) === 'loop_command_events' && \Request::segment(3) === null);
    }

    public function edit(Staff $adminUser, Section $section, Model $item)
    {
        $table = $section->getModel()->getTable();

        return $adminUser->isAbleTo(['manage_everything', "manage_baccarat_loop_command_events", "manage_roulette_loop_command_events"]);
    }

    public function delete(Staff $adminUser, Section $section, Model $item)
    {
        $table = $section->getModel()->getTable();

        return $adminUser->isAbleTo(['manage_everything', "manage_baccarat_loop_command_events", "manage_roulette_loop_command_events"]);
    }
}
