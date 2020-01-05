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
          $abilities = ['manage_resident','view_cards','manage_users','delete_card','review_card','approve_card','cancel_card','print_card','instead_lost_card','community_represent_card'];
            // Is supervisor
            if ($user->is_supervisor == 1) {
              return true;
            }elseif(in_array($ability,$abilities)){
              if ($ability == 'view_cards' && user()->admin_group != 'manage_users'){
                return true;
              }else {
                if ($ability == 'manage_resident' && user()->admin_group == 'requester') {
                  return true;
                }elseif ($ability == 'manage_users' && user()->admin_group == 'manage_users') {
                  return true;
                }elseif ($ability == 'review_card' && user()->admin_group == 'reviewer') {
                  return true;
                }elseif ($ability == 'approve_card' && user()->admin_group == 'approval') {
                  return true;
                }elseif ($ability == 'print_card' && user()->admin_group == 'printer') {
                  return true;
                }elseif ($ability == 'instead_lost_card' && user()->admin_group == 'requester') {
                  return true;
                }elseif ($ability == 'community_represent_card' && user()->admin_group == 'community_representative') {
                  return true;
                }else {
                  return false;
                }
              }
            }
        });

        $this->registerPolicies();

        //
    }
}
