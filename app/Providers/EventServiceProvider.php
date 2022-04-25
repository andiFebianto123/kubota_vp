<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\RolesHasPermission;
use Venturecraft\Revisionable\Revision;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        RolesHasPermission::deleted(function ($model) {
            // Insert into 'revisions' (calling getTable probably not necessary, but to be safe).
            \DB::table((new Revision())->getTable())->insert([
                [
                    'revisionable_type' => $model->getMorphClass(),
                    'revisionable_id' => $model->permission_id,
                    'key' => 'deleted_at',
                    'old_value' => $model->get_inner_value(),
                    'new_value' => null,
                    'user_id' => (backpack_auth()->check() ? backpack_user()->id : null),
                    'created_at' => new \DateTime(),
                    'updated_at' => new \DateTime(),
                ]
            ]);
        });

        RolesHasPermission::saved(function ($model) {
            // Insert into 'revisions' (calling getTable probably not necessary, but to be safe).
            \DB::table((new Revision())->getTable())->insert([
                [
                    'revisionable_type' => $model->getMorphClass(),
                    'revisionable_id' => $model->permission_id,
                    'key' => 'created_at',
                    'old_value' => null,
                    'new_value' => $model->get_inner_value(),
                    'user_id' => (backpack_auth()->check() ? backpack_user()->id : null),
                    'created_at' => new \DateTime(),
                    'updated_at' => new \DateTime(),
                ]
            ]);
        });
    }
}
