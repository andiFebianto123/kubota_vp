<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DescriptionConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('configurations')->where('name', 'email_reminder_day')->update([
            'description' => 'Set the day differences between po creation date to sending po email reminder'
        ]);
        DB::table('configurations')->where('name', 'expired_otp')->update([
            'description' => 'Set the day interval for OTP login expiration'
        ]);
        DB::table('configurations')->where('name', 'attemp_failure_otp')->update([
            'description' => 'Set maximum attempt failure for OTP input before getting locked'
        ]);
        DB::table('configurations')->where('name', 'attemp_failure_login')->update([
            'description' => 'Set maximum attempt failure for login before getting locked'
        ]);
        DB::table('configurations')->where('name', 'locked_account_on_failure_otp')->update([
            'description' => 'Set duration in minutes for locking user after failed to input OTP several times'
        ]);
        DB::table('configurations')->where('name', 'locked_account_on_failure_login')->update([
            'description' => 'Set duration in minutes for locking user after failed to login several times'
        ]);
    }
}
