<?php

namespace Admin\Http\Controllers;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MessagesStack;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessArtisanController extends Controller
{
    public function down(Request $request)
    {
        if ( ! ($down_until = $request->get('down_until'))) {
            MessagesStack::addError("Incorrect 'down_until' date!");

            return redirect()->back();
        }
        $down_until = Carbon::parse(str_replace("_", ' ', $down_until));
        if ( $down_until <= Carbon::now()) {
            MessagesStack::addError("Incorrect 'down_until' date!");

            return redirect()->back();
        }

        $downUntilSetting = Setting::where('key', 'ui_unblocking_time')->first();
        if ( ! $downUntilSetting) {
            $downUntilSetting      = new Setting();
            $downUntilSetting->key = 'ui_unblocking_time';
        }
        $downUntilSetting->value = $down_until;
        $downUntilSetting->save();

        $command = $this->getCommandString('down');

        $this->executeCommand($command, 'Application is in maintenance mode');

        return redirect()->back();
    }

    public function up(Request $request)
    {
        $command = $this->getCommandString('up');

        $this->executeCommand($command, 'Application is now live');

        return redirect()->back();
    }

    protected function getCommandString(string $scriptName): string
    {
        $prefix = base_path();

        switch (PHP_OS_FAMILY) {
            case 'Windows':
                $command = sprintf(
                    '%s/scripts/bat/%s.bat 2>&1',
                    $prefix,
                    $scriptName
                );
                break;

            default:
            case 'Linux':
                $command = sprintf(
                    'sh %s/scripts/sh/%s.sh 2>&1',
                    $prefix,
                    $scriptName
                );
                break;
        }


        return str_replace('\\', '/', $command);
    }

    protected function executeCommand(
        string $command,
        ?string $successText = null
    ) {
        $process = new Process($command);

        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        MessagesStack::addSuccess($process->getOutput() ?: $successText);

        return redirect()->back();
    }
}
