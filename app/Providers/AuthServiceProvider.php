<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::before(function($user, $ability) {
          $abilities = ['admin_dashboard','admin_manage_surveys','admin_manage_users','admin_manage_users','admin_manage_gallery','admin_manage_presentations'];
            // Is supervisor
            if ($user->is_supervisor == 1) {
              return true;
            }elseif(in_array($ability,$abilities)){
              // switch ($ability) {
              //   case 'admin_manage_surveys':
              //     return (user()->Role->is_admin_allow_surveys) ? true : false;
              //   break;
              //   case 'admin_manage_presentations':
              //     return (user()->Role->is_admin_allow_presentations) ? true : false;
              //   break;
              //   case 'admin_manage_gallery':
              //     return (user()->Role->is_admin_allow_gallery) ? true : false;
              //   break;
              //   case 'admin_manage_users':
              //     return (user()->Role->is_admin_allow_users) ? true : false;
              //   break;
              // }
              return true;
            }
        });

        $this->registerPolicies();

        //
    }
}
