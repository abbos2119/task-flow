<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\OtpSenderInterface;
use App\Events\WorkflowTransitionEnded;
use App\Listeners\AddCheckpointAfterTransitionListener;
use App\Listeners\WorkflowTransitionEndedListener;
use App\Services\Otp\EmailOtpSender;
use App\Services\WorkflowSetupService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Workflow\Registry;

class AppServiceProvider extends ServiceProvider
{
  
    public function register(): void
    {
        $this->app->singleton(Registry::class, function () {
            return new Registry();
        });

        $this->app->bind(OtpSenderInterface::class, EmailOtpSender::class);
    }


    public function boot(WorkflowSetupService $workflowSetup): void
    {
        $workflowSetup->setup();

        Event::listen(WorkflowTransitionEnded::class, AddCheckpointAfterTransitionListener::class);
        Event::listen(WorkflowTransitionEnded::class, WorkflowTransitionEndedListener::class);
    }
}