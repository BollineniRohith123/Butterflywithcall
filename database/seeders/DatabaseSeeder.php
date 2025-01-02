<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RecordingsTableSeeder;
use Database\Seeders\LangTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\CurrencyTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\SettingTableSeeder;
use Database\Seeders\FormFieldNamesTableSeeder;
use Database\Seeders\EmailTemplatesTableSeeder;
use Database\Seeders\FormsTableSeeder;
use Database\Seeders\CampaignsTableSeeder;
use Database\Seeders\CampaignLeadSeeder;
use Database\Seeders\ConversationSeeder;
use Database\Seeders\ConversationStoreSeeder;
use Database\Seeders\SubscriptionPlansTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV') != 'envato') {

            if (app_type() == 'saas') {
                $this->call(SubscriptionPlansTableSeeder::class);
            }
            $this->call([
                LangTableSeeder::class,
                CompanyTableSeeder::class,
                CurrencyTableSeeder::class,
                RolesTableSeeder::class,
                UsersTableSeeder::class,
                SettingTableSeeder::class,
                FormFieldNamesTableSeeder::class,
                EmailTemplatesTableSeeder::class,
                FormsTableSeeder::class,
                CampaignsTableSeeder::class,
                CampaignLeadSeeder::class,
                ConversationSeeder::class,
            ]);

            $this->call([
                ConversationStoreSeeder::class,
                RecordingsTableSeeder::class,
            ]);

            // Creating SuperAdmin
            if (app_type() == 'saas') {
                \App\SuperAdmin\Classes\SuperAdminCommon::createSuperAdmin(true);
            }
        }
    }
}
