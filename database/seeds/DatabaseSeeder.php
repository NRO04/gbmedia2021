<?php

use App\Models\Attendance\AttendanceSummary;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(GlobalCountriesSeeder::class);
        $this->call(GlobalDepartmentSeeder::class);
        $this->call(GlobalCitiesSeeder::class);
        $this->call(GlobalBankSeeder::class);
        $this->call(GlobalTypeContractSeeder::class);
        $this->call(GlobalDocumentSeeder::class);
        $this->call(GlobalBloodTypesSeeder::class);
        $this->call(EPSSeeder::class);
        $this->call(SettingPageSeeder::class);
        $this->call(SatelliteUserDocumentsTypesSeeder::class);
        $this->call(SatelliteTemplatePageSeeder::class);

        $this->call(ModulesSeeder::class);
        $this->call(ModulePermissionSeeder::class);
        $this->call(LocationTableSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(RhExtraValueSeeder::class);
        $this->call(RhExtraStateSeeder::class);
        $this->call(RhVacationStatuRequestSeeder::class);
        $this->call(ScheduleTypeSeeder::class);
        $this->call(PayrollTypesSeeder::class);
        $this->call(PayrollsSeeder::class);
        $this->call(PayrollPaymentMethodSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(BookingTypeSeeder::class);
        $this->call(HeadquarterSeeder::class);
        $this->call(ScheduleSeeder::class);

        factory(App\User::class)->times(150)->create();
        factory(App\Models\Satellite\SatelliteOwner::class)->times(40)->create();
        factory(App\Models\Satellite\SatelliteAccount::class)->times(150)->create();

        $this->call(SatelliteSeeder::class);

        $this->call(AlarmsSeeder::class);
        $this->call(RoomsControlSeeder::class);
        $this->call(CafeteriaTypesSeeder::class);
        $this->call(CafeteriaMenusSeeder::class);
        $this->call(CafeteriaOrdersSeeder::class);
        $this->call(CafeteriaBreakfastCategoriesSeeder::class);
        $this->call(CafeteriaBreakfastTypesSeeder::class);
        $this->call(BoutiqueProductsSeeder::class);
        $this->call(ContactSeeder::class);
        $this->call(StudiosSeeder::class);
        $this->call(TenantsSeeder::class);
        $this->call(AttendanceStatusSeeder::class);
        $this->call(scheduleShiftSeeder::class);
        $this->call(MaintenanceSeeder::class);
        $this->call(MonitoringSeeder::class);
        $this->call(AccountsSeeder::class);
        $this->call(UserContractsSeeder::class);
        $this->call(SettingPageTaskTypesSeeder::class);
        $this->call(ModuleRolehasPermissionsSeeder::class);
    }
}
