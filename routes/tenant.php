<?php

declare(strict_types=1);

use App\Models\Tenancy\Tenant;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Features\UserImpersonation;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::get('/aquiestoy', function () {
    return 'aqui estoy';
});

Route::get('/mylogin', function () {
    return view('auth.login');
});

Route::middleware(['web' ,InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class,])->group(function () {

    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });


    Route::get('unauthorized', function () {
        return view('layouts.unauthorized');
    })->name('unauthorized');

    Route::get('/dashboard', 'HomeController@index')->name('home.dashboard');

    // Dashboard
    Route::group(['prefix' => 'home', 'as' => 'home.', 'middleware' => ['auth']], function () {
        Route::get('/getUserLastNew', 'HomeController@getUserLastNew')->name('get_user_last_new');
        Route::post('/createAgendaEvent', 'HomeController@createAgendaEvent')->name('create_agenda_event');
        Route::get('/getAgendaEvents', 'HomeController@getAgendaEvents')->name('get_agenda_events');
        Route::get('/getUserLatestTasks', 'HomeController@getUserLatestTasks')->name('get_user_latest_tasks');
        Route::get('/getAttendanceStats', 'HomeController@getAttendanceStats')->name('getAttendanceStats');
    });

    //satellite
    Route::group(['prefix' => 'satellite', 'as' => 'satellite.', 'middleware' => ['auth']], function () {
        Route::get('/script/execute'              , "Satellite\SatelliteController@executeScript")->name('execute_script');
        Route::get('/owner/create'                , "Satellite\SatelliteController@createOwner")->name('create_owner');
        Route::get('/owner/edit/{id}'             , "Satellite\SatelliteController@editOwner")->name('edit_owner');
        Route::get('/owners'                      , "Satellite\SatelliteController@listOwners")->name('owners');
        Route::get('/owners/get'                  , "Satellite\SatelliteController@getOwners")->name('get_owners');
        Route::get('/owners/models'               , "Satellite\SatelliteController@getOwnersModels")->name('owners_models');
        Route::get('/owners/managed'              , "Satellite\SatelliteController@getOwnersManaged")->name('owners_managed');
        Route::get('/owner/accounts'              , "Satellite\SatelliteController@getOwnerAccounts")->name('get_owner_accounts');
        Route::get('/owner/getDocuments'          , "Satellite\SatelliteController@getDocuments")->name('get_documents');
        Route::get('/owner/getCommissions'        , "Satellite\SatelliteController@getCommissions")->name('get_commissions');
        Route::get('/owner/getCities'             , "Satellite\SatelliteController@getCities")->name('get_cities');
        Route::get('/owner/coincidenceBanned'     , "Satellite\SatelliteController@coincidenceBanned")->name('coincidence_banned');
        Route::post('/owner/store'                , "Satellite\SatelliteController@storeOwner")->name('store_owner');
        Route::post('/owner/updatePersonalInfo'   , "Satellite\SatelliteController@updatePersonalInfo")->name('update_personal_info');
        Route::post('/owner/updatePaymentMethod'  , "Satellite\SatelliteController@updatePaymentMethod")->name('update_payment_method');
        Route::post('/owner/updateCommission'     , "Satellite\SatelliteController@updateCommission")->name('update_commission');
        Route::post('/owner/updateApi'            , "Satellite\SatelliteController@updateApi")->name('update_api');
        Route::post('/owner/storeCommission'      , "Satellite\SatelliteController@storeCommission")->name('store_commission');
        Route::post('/owner/removeCommission'     , "Satellite\SatelliteController@removeCommission")->name('remove_commission');
        Route::post('/owner/updatePercent'        , "Satellite\SatelliteController@updatePercent")->name('update_percent');
        Route::post('/owner/updateStatus'         , "Satellite\SatelliteController@updateStatus")->name('update_status');
        Route::post('/owner/limit/store'          , "Satellite\SatelliteController@storeLimit")->name('store_limit');

        Route::get('/studios'                     , "Satellite\SatelliteController@getStudios")->name('studios');

        Route::get('/prospects'                   , "Satellite\SatelliteController@viewProspects")->name('prospects');
        Route::post('/prospect/store'             , "Satellite\SatelliteController@storeProspect")->name('store_prospect');

        Route::get('/contracts'                   , "Satellite\SatelliteController@viewContracts")->name('contracts');
        Route::get('/contracts/get'               , "Satellite\SatelliteController@getContracts")->name('get_contracts');
        Route::post('/contract/store'             , "Satellite\SatelliteController@storeContract")->name('store_contract');
        Route::post('/contract/update'            , "Satellite\SatelliteController@updateContract")->name('update_contract');
        Route::get('/contract/pdf/{id}'           , "Satellite\SatelliteController@pdfContract")->name('pdf_contract');

        Route::get('/api'                         , "Satellite\SatelliteController@viewApi")->name('api');
        Route::get('/accounting'                  , "Satellite\SatelliteController@viewAccounting")->name('accounting');
        Route::get('/accounting/dates'            , "Satellite\SatelliteController@getAcocountingDates")->name('accounting_dates');
        Route::get('/accounting/export/siigo'    , "Satellite\SatelliteController@exportSiigo")->name('export_siigo');

        Route::get('/user/list'                   , "Satellite\SatelliteController@listUsers")->name('list_users');
        Route::get('/user/getUsers'               , "Satellite\SatelliteController@getUsers2")->name('get_satellite_users');
        Route::get('/user/getUsers2'               , "Satellite\SatelliteController@getUsers2")->name('get_satellite_users_2');
        Route::get('/user/create'                 , "Satellite\SatelliteController@createUser")->name('create_user');
        Route::get('/user/exists'                 , "Satellite\SatelliteController@existsUser")->name('exists_user');
        Route::get('/user/edit'                   , "Satellite\SatelliteController@editUser")->name('edit_user');
        Route::post('/user/store'                 , "Satellite\SatelliteController@storeUser")->name('store_user');
        Route::post('/user/update'                , "Satellite\SatelliteController@updateUser")->name('update_user');
        Route::post('/user/remove'                , "Satellite\SatelliteController@removeUser")->name('remove_user');

        Route::get('/account/list/{owner_id?}'                , "Satellite\SatelliteController@listAccounts")->name('list_accounts');
        Route::get('/account/getAccounts'         , "Satellite\SatelliteController@getAccounts")->name('get_accounts');
        Route::get('/account/create'              , "Satellite\SatelliteController@createAccount")->name('create_account');
        Route::get('/account/searchOwner'         , "Satellite\SatelliteController@searchOwner")->name('search_owner');
        Route::get('/account/edit'                , "Satellite\SatelliteController@editAccount")->name('edit_account');
        Route::get('/account/getLogs'             , "Satellite\SatelliteController@getLogs")->name('get_logs');
        Route::get('/account/getNotes'            , "Satellite\SatelliteController@getNotes")->name('get_notes');
        Route::get('/account/summary'             , "Satellite\SatelliteController@statisticSummary")->name('statistic_summary');
        Route::post('/account/store'              , "Satellite\SatelliteController@storeAccount")->name('store_account');

        Route::post('/account/note/store'         , "Satellite\SatelliteController@storeNote")->name('store_note');
        Route::post('/account/update'             , "Satellite\SatelliteController@updateAccount")->name('update_account');
        Route::post('/account/activating'         , "Satellite\SatelliteController@activatingAccount")->name('activating_account');
        Route::post('/account/created/email'      , "Satellite\SatelliteController@sendEmailCreatedAccount")->name('send_email_created_account');

        Route::get('/template/list'               , "Satellite\SatelliteController@listTemplates")->name('list_templates');
        Route::get('/template/config/{id}'        , "Satellite\SatelliteController@configTemplate")->name('config_template');
        Route::post('/template/update'            , "Satellite\SatelliteController@updateConfig")->name('update_config');
        Route::post('/template/upload-image'      , "Satellite\SatelliteController@uploadImage")->name('upload_image');

        Route::get('/template/statistic'           , "Satellite\SatelliteController@viewPaymentTemplate")->name('statistic_template');
        Route::get('/template/statistic/get'      , "Satellite\SatelliteController@getStatisticEmail")->name('get_statistic_email');
        Route::post('/template/statistic/upload-image'      , "Satellite\SatelliteController@uploadImageEmail")->name('upload_image_mail');
        Route::post('/template/statistic/update'      , "Satellite\SatelliteController@updateStatisticEmail")->name('update_statistic_email');

        Route::get('/debts'                       , "Satellite\SatelliteController@viewDebts")->name('debts');
        Route::get('/debts/get'                   , "Satellite\SatelliteController@getDebts")->name('get_debts');
        Route::get('/debt/block/boutique'         , "Satellite\SatelliteController@blockBoutique")->name('block_boutique');
        Route::get('/accumulations'               , "Satellite\SatelliteController@viewAccumulations")->name('accumulations');
        Route::get('/accumulations/get'           , "Satellite\SatelliteController@getAccumulations")->name('get_accumulations');
        Route::get('/earnings'                    , "Satellite\SatelliteController@viewEarnings")->name('earnings');
        Route::get('/earnings/get'                , "Satellite\SatelliteController@getEarnings")->name('get_earnings');
        Route::get('/earnings/export'             , "Satellite\SatelliteController@exportEarnings")->name('export_earnings');
        Route::get('/earnings/payreceived'        , "Satellite\SatelliteController@getEarningPayReceived")->name('pay_received');
        Route::get('/earnings/graphic'            , "Satellite\SatelliteController@getEarningsGraphic")->name('earning_graphic');

        Route::post('/apiChaturbate'               , "Satellite\SatelliteController@apiChaturbate")->name('apiChaturbate');
        Route::post('/apiJasmin'                   , "Satellite\SatelliteController@apiJasmin")->name('apiJasmin');
        Route::post('/SaveApi'                   , "Satellite\SatelliteController@SaveApi")->name('SaveApi');
        Route::get('/GetApis/{id}'                   , "Satellite\SatelliteController@getApis")->name('getApis');

        Route::post('/buildAndSend'                   , "Satellite\SatelliteController@buildAndSend")->name('buildAndSend');

        Route::group(['prefix' => 'payment', 'as' => 'payment.'], function () {
            Route::get('/create'              , "Satellite\SatelliteController@createPayment")->name('create');
            Route::get('/page/list'           , "Satellite\SatelliteController@getPagesUpload")->name('get_pages');
            Route::get('/uploaded/list'       , "Satellite\SatelliteController@listPayments")->name('list');
            Route::get('/uploaded/get'        , "Satellite\SatelliteController@getPayments")->name('get_payments');
            Route::get('/uploaded/owner/get'  , "Satellite\SatelliteController@getOwnerPayments")->name('get_owner_payments');
            Route::get('/payroll/owner/{id}'  , "Satellite\SatelliteController@viewPayrollOwner")->name('view_payroll_owner');
            Route::get('/owner/dates'         , "Satellite\SatelliteController@getOwnerPaymentDates")->name('get_owner_payment_dates');
            Route::get('/owner/payroll'       , "Satellite\SatelliteController@getOwnerPaymentPayroll")->name('get_owner_payment_payroll');
            Route::get('/owner/payrolls'       , "Satellite\SatelliteController@getOwnerPaymentPayrolls")->name('get_owner_payment_payrolls');
            Route::get('/owner/accounts'      , "Satellite\SatelliteController@getOwnerPaymentAccounts")->name('get_owner_payment_accounts');
            Route::get('/commission/notAssigned' , "Satellite\SatelliteController@getNotAssignedCommission")->name('get_not_assigned_commission');
            Route::get('/commission/assigned' , "Satellite\SatelliteController@getAssignedCommission")->name('get_assigned_commission');
            Route::get('/deductions'          , "Satellite\SatelliteController@getOwnerDeductions")->name('get_owner_deductions');
            Route::get('/paydeductions'       , "Satellite\SatelliteController@getPayDeductions")->name('get_paydeductions');
            Route::get('/paymentDates'        , "Satellite\SatelliteController@getPaymentDates")->name('get_payment_dates');
            Route::get('/owner/details'       , "Satellite\SatelliteController@getOwnerDetails")->name('get_owner_details');
            Route::get('/generate'            , "Satellite\SatelliteController@generatePayment")->name('generate');
            Route::get('/generate/dates'      , "Satellite\SatelliteController@getPayrollDates")->name('payroll_dates');
            Route::get('/payrolls'            , "Satellite\SatelliteController@getPayrolls")->name('payrolls');
            Route::get('/payrolls/export'     , "Satellite\SatelliteController@payrollsExport")->name('payrolls_export');

            Route::post('/upload'             , "Satellite\SatelliteController@uploadPayment")->name('upload_payment');
            Route::post('/create/commission'  , "Satellite\SatelliteController@createAllCommission")->name('create_all_commission');
            Route::post('/change/trm'         , "Satellite\SatelliteController@changeTRM")->name('change_trm');
            Route::post('/commission/create'  , "Satellite\SatelliteController@createCommission")->name('create_commission');
            Route::post('/deduction/create'   , "Satellite\SatelliteController@createDeduction")->name('create_deduction');
            Route::post('/paydeduction/create', "Satellite\SatelliteController@createPayDeduction")->name('create_paydeduction');
            Route::post('/assign/commission', "Satellite\SatelliteController@assignCommission")->name('assign_commission');
            Route::post('/assign/massive/commission'  , "Satellite\SatelliteController@assignMassiveCommission")->name('assign_massive_commission');
            Route::post('accumulate', "Satellite\SatelliteController@acumulatePayment")->name('acumulate_payment');
            Route::post('/payroll/create', "Satellite\SatelliteController@createPayroll")->name('create_payroll');
            Route::post('/statistic/email/send', "Satellite\SatelliteController@sendStatisticEmails")->name('send_statistic_emails');
            Route::post('/statistic/owner/send'     , "Satellite\SatelliteController@sendStatisticEmail")->name('send_statistic_email');
            Route::post('/payroll/calculate'     , "Satellite\SatelliteController@calculatePayroll")->name('calculate_payroll');
            Route::get('/statistic/owner/export'     , "Satellite\SatelliteController@exportOwnerPayroll")->name('export_statistic');

        });

    });

    //contact
    Route::group(['prefix' => 'contacts', 'as' => 'contact.', 'middleware' => ['auth']], function () {
        Route::group(['middleware' => ['auth']], function () {
            Route::get('/', 'Contacts\ContactController@index')->name('index');
            Route::get('/GetAllContacts', 'Contacts\ContactController@GetAllContacts')->name('GetAllContacts');
            Route::post('/search', 'Contacts\ContactController@searchContact')->name('searchContact');
        });
    });

    //wiki
    Route::group(['prefix' => 'wiki', 'as' => 'wiki.', 'middleware' => ['auth']], function () {
        Route::group(['middleware' => ['auth']], function () {
            Route::get('/', 'Wiki\WikiController@index')->name('index');

            Route::get('/wikidata',                 'Wiki\WikiController@wikiData')->name('wikidata');
            Route::get('/categorydata',             'Wiki\WikiController@categoryData')->name('categorydata');
            Route::get('/getPostCategories',             'Wiki\WikiController@categoryWithPosts')->name('categoryWithPosts');
            Route::get('/show/{id}',             'Wiki\WikiController@show')->name('show');

            Route::post('/store',               'Wiki\WikiController@store')->name('store');
            Route::post('/share',               'Wiki\WikiController@share')->name('share');
            Route::post('/uploadImages',            'Wiki\WikiController@uploadImages')->name('uploadImages');
            Route::put('/updatepost/{id}',          'Wiki\WikiController@updatePost')->name('updatepost');
            Route::put('/updatesharestatus/{id}',   'Wiki\WikiController@updateShareStatus')->name('updatesharestatus');
            Route::put('/removesharestatus/{id}',   'Wiki\WikiController@removeShareStatus')->name('removesharestatus');
            Route::delete('/deletepost/{id}',       'Wiki\WikiController@deletePost')->name('deletepost');
            Route::post('/storecategory',           'Wiki\WikiController@storeCategory')->name('storecategory');
            Route::delete('/deletecategory/{id}',   'Wiki\WikiController@deleteCategory')->name('deletecategory');
            Route::put('/updatecategory/{id}',      'Wiki\WikiController@updateCategory')->name('updatecategory');
            Route::get('/editcategory/{id}',      'Wiki\WikiController@editCategory')->name('editCategory');
            Route::get('/editpost/{id}',      'Wiki\WikiController@editPost')->name('editPost');

            Route::get('/categoryposts',      'Wiki\WikiController@categoryWithPosts')->name('categoryPosts');
            Route::get('/studios',      'Wiki\WikiController@getStudios')->name('getStudios');
        });
    });

    //emails
    Route::group(['prefix' => 'emails', 'as' => 'emails.', 'middleware' => ['auth']], function () {
        Route::get('/list', function () {
            return view('adminModules.setting.emails.list');
        })->name('list');
    });

    //contracts
    Route::group(['prefix' => 'contracts', 'as' => 'contracts.', 'middleware' => ['auth']], function () {
        Route::group(['middleware' => ['auth']], function () {
            Route::get('/', 'Contracts\ContractController@index')->name('index');
            Route::get('/getContracts', 'Contracts\ContractController@getContracts')->name('get_contracts');
            Route::get('/info', 'Contracts\ContractController@info')->name('info');
            Route::get('/assign-studios', 'Contracts\ContractController@assignStudios')->name('assign_studios');

            Route::post('/edit', 'Contracts\ContractController@edit')->name('edit');
            Route::post('/editConfiguration', 'Contracts\ContractController@editConfiguration')->name('edit_configuration');
            Route::get('/getConfiguration', 'Contracts\ContractController@getConfiguration')->name('get_configuration');
            Route::post('/changeContractStatus', 'Contracts\ContractController@changeContractStatus')->name('change_contract_status');
            Route::post('/changeStudioStatus', 'Contracts\ContractController@changeStudioStatus')->name('change_studio_status');
            Route::post('/changeStudioStatusBulk', 'Contracts\ContractController@changeStudioStatusBulk')->name('change_studio_status_bulk');
            Route::get('/getModuleInfo', 'Contracts\ContractController@getModuleInfo')->name('get_module_info');
            Route::post('/editModuleInfo', 'Contracts\ContractController@editModuleInfo')->name('edit_module_info');
            Route::post('/getRole', 'Contracts\ContractController@getRole')->name('get_role');
            Route::post('/editRolesAndFunction', 'Contracts\ContractController@editRolesAndFunction')->name('edit_roles_and_functions');
            Route::get('/getStudios', 'Contracts\ContractController@getStudios')->name('get_studios');

            // Export Contracts
            Route::get('/ExportContrato/{id}'   , 'Contracts\ContractController@PDF_ExportContrato')->name('PDF_ExportContrato');
            Route::get('/ExportContratoAT/{id}' , 'Contracts\ContractController@PDF_ExportContratoAT')->name('PDF_ExportContratoAT');
            Route::get('/ExportContratoMKP/{id}', 'Contracts\ContractController@PDF_ExportContratoMKP')->name('PDF_ExportContratoMKP');
            Route::get('/ExportContratoTD/{id}' , 'Contracts\ContractController@PDF_ExportContratoTD')->name('PDF_ExportContratoTD');
            Route::get('/ExportContratoCP/{id}' , 'Contracts\ContractController@PDF_ExportContratoCP')->name('PDF_ExportContratoCP');
            Route::get('/ExportContratoCM/{id}' , 'Contracts\ContractController@PDF_ExportContratoCM')->name('PDF_ExportContratoCM');
            Route::get('/ExportContratoCD/{id}' , 'Contracts\ContractController@PDF_ExportContratoCD')->name('PDF_ExportContratoCD');
            Route::get('/ExportContratoAC/{id}' , 'Contracts\ContractController@PDF_ExportContratoAC')->name('PDF_ExportContratoAC');
            Route::get('/Pagare/{id}'           , 'Contracts\ContractController@PDF_Pagare')->name('PDF_Pagare');
            Route::get('/ExportContratoTI/{id}' , 'Contracts\ContractController@PDF_ExportContratoTI')->name('PDF_ExportContratoTI');
            Route::get('/ExportContratoPS/{id}' , 'Contracts\ContractController@PDF_ExportContratoPS')->name('PDF_ExportContratoPS');
        });
    });

    //news
    Route::group(['prefix' => 'news', 'as' => 'news.', 'middleware' => ['auth']], function () {
        Route::group(['middleware' => ['auth']], function () {
            Route::get('/',                 'News\NewsController@index')->name('index');
            Route::get('/getNews',          'News\NewsController@getNews')->name('getNews');
            Route::get('/getRoles',         'News\NewsController@getRoles')->name('roles');
            Route::get('/show/{id}',        'News\NewsController@show')->name('show');

            Route::post('/store',           'News\NewsController@store')->name('store');
            Route::put('/update/{id}',      'News\NewsController@update')->name('update');
            Route::delete('/delete/{id}',   'News\NewsController@delete')->name('delete');

            Route::post('/storeComment',    'News\NewsController@storeComment')->name('storeComment');
            Route::get('/getComments/{id}', 'News\NewsController@getComments')->name('getComments');

            Route::post('/storeLike',      'News\NewsController@storeLike')->name('storeLike');
            Route::get('/getLikes/{id}',    'News\NewsController@getLikes')->name('getLikes');
        });
    });

    //alarms
    Route::group(['prefix' => 'alarms', 'as' => 'alarms.', 'middleware' => ['auth']], function () {
        Route::get('',         'Alarms\AlarmController@alarms')->name('alarms');
        Route::get('list',     'Alarms\AlarmController@list')->name('list');
        Route::get('finished', 'Alarms\AlarmController@finished')->name('finished');

        // ACTIONS
        Route::post('/create',           'Alarms\AlarmController@create')->name('create');
        Route::post('/edit',             'Alarms\AlarmController@edit')->name('edit');
        Route::post('/finish',           'Alarms\AlarmController@finish')->name('finish');
        Route::get('/alarms' ,           'Alarms\AlarmController@getAlarms')->name('get_alarms');
        Route::post('/delete',           'Alarms\AlarmController@delete')->name('delete');
        Route::post('/getAlarm',         'Alarms\AlarmController@getAlarm')->name('get_alarm');
        Route::get('/getFinishedAlarms', 'Alarms\AlarmController@getFinishedAlarms')->name('get_finished_alarms');
    });

    //tasks
    Route::group(['prefix' => 'tasks', 'as' => 'tasks.', 'middleware' => ['auth']], function () {
        Route::get('list'                       ,'Tasks\TaskController@list')->name('list');
        Route::post('store'                     ,'Tasks\TaskController@store')->name('store');
        Route::post('store_folder'              ,'Tasks\TaskController@storeFolder')->name('store_folder');
        Route::get('getTasks'                   ,'Tasks\TaskController@getTasks')->name('get_tasks');
        Route::get('getFolders'                 ,'Tasks\TaskController@getFolders')->name('get_folders');
        Route::get('updateFolderDestination'    ,'Tasks\TaskController@updateFolderDestination')->name('update_folder_destination');
        Route::get('updateFolderName'           ,'Tasks\TaskController@updateFolderName')->name('update_folder_name');
        Route::get('adminFolders'               ,'Tasks\TaskController@getAdminFolders')->name('admin_folders');
        Route::get('deleteFolder'               ,'Tasks\TaskController@deleteFolder')->name('delete_folder');
        Route::get('titleComment'               ,'Tasks\TaskController@titleComment')->name('title_comments');
        Route::get('receiversComments'          ,'Tasks\TaskController@receiversComments')->name('receivers_comments');
        Route::get('contentComments'            ,'Tasks\TaskController@contentComments')->name('content_comments');
        Route::post('createComments'            ,'Tasks\TaskController@createComments')->name('create_comments');
        Route::get('notifications'              ,'Tasks\TaskController@notifications')->name('task_notifications');
        Route::post('updatePulsing'             ,'Tasks\TaskController@updatePulsing')->name('update_pulsing');
        Route::post('signOutTask'               ,'Tasks\TaskController@signOutTask')->name('sign_out_task');
        Route::get('noInRolesReceivers'         ,'Tasks\TaskController@noInRolesReceivers')->name('no_in_roles_receivers');
        Route::get('noInUsersReceivers'         ,'Tasks\TaskController@noInUsersReceivers')->name('no_in_users_receivers');
        Route::post('addReceivers'              ,'Tasks\TaskController@addReceivers')->name('add_receivers');
        Route::get('getReceiversJson'           ,'Tasks\TaskController@getReceiversJson')->name('get_receivers');
        Route::post('removeReceivers'           ,'Tasks\TaskController@removeReceivers')->name('remove_receivers');
        Route::post('extendTime'                ,'Tasks\TaskController@extendTime')->name('extend_time');
        Route::post('finishTask'                ,'Tasks\TaskController@finishTask')->name('finish_task');
    });

    //chats
    Route::group(['prefix' => 'chat', 'as' => 'chat.', 'middleware' => ['auth']], function () {
        Route::get('index'                  ,'Chats\ChatsController@index')->name('index');
        Route::get('/fetchMessage'          ,'Chats\ChatsController@fetchMessage')->name('fetch');
        Route::post('/sendMessage'          ,'Chats\ChatsController@sendMessage')->name('send');
        Route::get('/getFriends'            ,'Chats\ChatsController@getFriends')->name('friends');
        Route::get('/searchContact'         ,'Chats\ChatsController@searchContact')->name('search_contacts');
        Route::post('/addContact'           ,'Chats\ChatsController@addContact')->name('add_contacts');
        Route::get('/chatInCommon'          ,'Chats\ChatsController@chatInCommon')->name('chat_in_common');
        Route::get('/changeStatusMessage'   ,'Chats\ChatsController@changeStatusMessage')->name('change_status_message');
        Route::get('/notifications'         ,'Chats\ChatsController@notifications')->name('notifications');
    });

    //training
    Route::group(['prefix' => 'trainings', 'as' => 'training.', 'middleware' => ['auth']], function () {
        Route::get('/',                                         'Training\TrainingController@index')->name('index');

        Route::get('/training/{training}',                      'Training\TrainingController@training')->name('view_training');
        Route::get('/questionnaire/{training}',                 'Training\TrainingController@questionnaire')->name('questionnaire');
        Route::get('/editQuestions/{training}',                 'Training\TrainingController@editQuestions')->name('editQuestions');
        Route::get('/editRoles/{training}',                     'Training\TrainingController@editQuestions')->name('editRoles');
        Route::post('/finishTest/{training}',                   'Training\TrainingController@finishTest')->name('finishTest');
        Route::get('/edit/{training}',                          'Training\TrainingController@edit')->name('edit');

        Route::get('/getTrainings',                             'Training\TrainingController@getTrainings')->name('get_trainings');
        Route::get('/show/{training}',                          'Training\TrainingController@show')->name('show');
        Route::get('/completedUser/{training}',                 'Training\TrainingController@userCompleted')->name('completed_user');
        Route::get('/completedTraining/{training}',             'Training\TrainingController@trainingCompleted')->name('completed_training');

        Route::post('/training/finish/{training}',              'Training\TrainingController@finish')->name('finish_training');
        Route::post('/store',                                   'Training\TrainingController@store')->name('store_training');
        Route::post('/update/{training}',                       'Training\TrainingController@update')->name('update');
        Route::delete('/delete/{training}',                     'Training\TrainingController@deleteTraining')->name('deleteTraining');
    });

    //gbook
    Route::group(['prefix' => 'gbook', 'as' => 'gbook.', 'middleware' => ['auth']], function () {
        Route::get('/list', function () {
            return view('adminModules.setting.gbook.list');
        })->name('list');
    });

    //support
    Route::group(['prefix' => 'support', 'as' => 'support.', 'middleware' => ['auth']], function () {
        Route::get('/login', 'Support\SupportController@login')->name('login');
    });

    //human_resources
    Route::group(['prefix' => 'human_resources', 'as' => 'human_resources.', 'middleware' => ['auth']], function () {
        Route::get('/list', function () {
            return view('adminModules.setting.human_resources.list');
        })->name('list');
    });

    //loans
    Route::group(['prefix' => 'loans', 'as' => 'loans.', 'middleware' => ['auth']], function () {
        Route::get('list/{status}'              ,"Loans\LoanController@list")->name('list');
        Route::get('getLoans'                   ,"Loans\LoanController@getLoans")->name('get_loans');
        Route::get('getLoanInstallment'         ,"Loans\LoanController@getLoanInstallment")->name('get_loan_installments');
        Route::post('store'                     ,"Loans\LoanController@store")->name('store');
        Route::post('storeInstallment'          ,"Loans\LoanController@storeInstallment")->name('store_installment');
    });

    //payroll
    Route::group(['prefix' => 'payroll', 'as' => 'payroll.', 'middleware' => ['auth']], function () {
        Route::get('list'                                       ,"Payrolls\PayrollController@viewPayrolls")->name('list');
        Route::get('/list/get/{quarter?}/{month?}/{year?}'      ,"Payrolls\PayrollController@getPayrolls")->name('get_payrolls');
        Route::post('/days/update'                              ,"Payrolls\PayrollController@updateWorkedDays")->name('update_worked_days');
        Route::post('/movements/info'                           ,"Payrolls\PayrollController@infoMovements")->name('info_movements');
        Route::post('/movements/save'                           ,"Payrolls\PayrollController@saveMovements")->name('save_movements');
        Route::get('/ranges'                                    ,"Payrolls\PayrollController@getRanges")->name('get_ranges');
        Route::get('increases/export/{quarter}/{month}/{year}'  ,"Payrolls\PayrollController@increasesExport")->name('increases_export');
        Route::get('pab/export/{quarter}/{month}/{year}'        ,"Payrolls\PayrollController@pabExport")->name('pab_export');
        Route::get('staff/export/{quarter}/{month}/{year}'      ,"Payrolls\PayrollController@staffExport")->name('staff_export');
        Route::get('pab/export/inactive/{quarter}/{month}/{year}'        ,"Payrolls\PayrollController@pabExportInactive")->name('pab_export_inactive');

        Route::group(['prefix' => 'boutique', 'as' => 'boutique.'], function () {
            Route::get('list'                                       ,"Payrolls\PayrollController@viewPayrollBoutique")->name('payroll_boutique');
            Route::get('/list/get'                                  ,"Payrolls\PayrollController@getPayrollsBoutique")->name('get_payrolls_boutique');
            Route::post('/installments'                             ,"Payrolls\PayrollController@getBoutiqueInstallments")->name('get_boutique_installments');
            Route::post('installment/store'                         ,"Payrolls\PayrollController@storeBoutiqueInstallment")->name('store_payrolls_boutique');
        });

    });

    //user
    Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['auth']], function () {

        Route::get('list'                  , 'Users\UserController@viewUsers')->name('list');
        Route::get('modelsList'            , 'Users\UserController@viewModels')->name('models.list');
        Route::get('/users'                , 'Users\UserController@getUsers')->name('users');
        Route::get('/models'               , 'Users\UserController@getAllModels')->name('models');
        Route::get('/model/accounts'       , 'Users\UserController@getModelAccounts')->name('model_accounts');
        Route::get('/getUsers'             , 'Users\UserController@getUsers')->name('get_users');
        Route::post('users/create'         , 'Users\UserController@create')->name('create');
        Route::post('users/changetheme'    , 'Users\UserController@changeTheme')->name('change_theme');
        Route::post('/model/account/update', 'Users\UserController@updateAccount')->name('update_accounts');
        Route::post('/model/account/store' , 'Users\UserController@storeAccount')->name('store_accounts');
        Route::get('/birthday'             , 'Users\UserController@viewBirthday')->name('birthday');
        Route::get('/birthday/get/{month}' , 'Users\UserController@getUsersBirthday')->name('users_birthday');

        Route::get('/getModels/{id}'       , 'Users\UserController@getModels')->name('getModels');
        Route::get('/getPhotographers/{id}', 'Users\UserController@getPhotographers')->name('getPhotographers');
        Route::get('/getFilmakers/{id}'    , 'Users\UserController@getFilmakers')->name('getFilmakers');
        Route::get('edit/{user_id}'        , 'Users\UserController@edit')->name('edit');
        Route::get('/getInactives'         , 'Users\UserController@getInactives')->name('get_inactives');

        Route::get('/getDepartmentCities'  , 'Users\UserController@getDepartmentCities')->name('get_department_cities');
        Route::post('/editPersonalInfo'    , 'Users\UserController@editPersonalInfo')->name('edit_personal_info');
        Route::post('/getRetirementHistory', 'Users\UserController@getRetirementHistory')->name('get_retirement_history');
        Route::post('/editAccessInfo'      , 'Users\UserController@editAccessInfo')->name('edit_access_info');
        Route::post('/editPayroll'         , 'Users\UserController@editPayroll')->name('edit_payroll');
        Route::post('/editBankAccountInfo' , 'Users\UserController@editBankAccountInfo')->name('edit_bank_account_info');
        Route::post('/editExtraInfo'       , 'Users\UserController@editExtraInfo')->name('edit_extra_info');

        Route::get('/modelPageAccess'       , 'Users\UserController@modelPageAccess')->name('model_page_access');
    });

    //review
    Route::group(['prefix' => 'review', 'as' => 'review.', 'middleware' => ['auth']], function () {
        Route::get('/list', function () {
            return view('adminModules.setting.review.list');
        })->name('list');
    });

    //roomcontrol
    Route::group(['prefix' => 'roomscontrol', 'as' => 'roomscontrol.', 'middleware' => ['auth']], function () {
        Route::group(['middleware' => ['auth']], function () {
            Route::get('inventory'                            , 'RoomsControl\RoomsControlController@inventory')->name('inventory');
            Route::get('info'                                 , 'RoomsControl\RoomsControlController@info')->name('info');
            Route::get('history'                              , 'RoomsControl\RoomsControlController@history')->name('history');
            Route::get('room-history/{location}/{room_number}', 'RoomsControl\RoomsControlController@roomHistory')->name('room_history');
            Route::get('status'                               , 'RoomsControl\RoomsControlController@status')->name('status');

            // ACTIONS
            Route::get('/getRoomInventory'         , 'RoomsControl\RoomsControlController@getRoomInventory')->name('get_room_inventory');
            Route::get('/getHistory'               , 'RoomsControl\RoomsControlController@getHistory')->name('get_history');
            Route::post('/saveRoomInventory'       , 'RoomsControl\RoomsControlController@saveRoomInventory')->name('save_room_inventory');
            Route::post('/getInventoryItem'        , 'RoomsControl\RoomsControlController@getInventoryItem')->name('get_inventory_item');
            Route::post('/editInventoryItem'       , 'RoomsControl\RoomsControlController@editInventoryItem')->name('edit_inventory_item');
            Route::post('/delete'                  , 'RoomsControl\RoomsControlController@delete')->name('delete');
            Route::post('/getItemDuplicatedInRooms', 'RoomsControl\RoomsControlController@getItemDuplicatedInRooms')->name('get_item_duplicated_in_rooms');
            Route::post('/duplicateItemInRooms'    , 'RoomsControl\RoomsControlController@duplicateItemInRooms')->name('duplicate_inventory_item');
            Route::post('/setInventoryOrder'       , 'RoomsControl\RoomsControlController@setInventoryOrder')->name('set_inventory_order');
            Route::get('/getRoomHistory'           , 'RoomsControl\RoomsControlController@getRoomHistory')->name('get_room_history');
            Route::post('/getRoomControl'          , 'RoomsControl\RoomsControlController@getRoomControl')->name('get_room_control');
            Route::post('/getLocationRoomsStatus'  , 'RoomsControl\RoomsControlController@getLocationRoomsStatus')->name('get_location_rooms_status');
            Route::post('/getLocation'             , 'RoomsControl\RoomsControlController@getLocation')->name('get_location');
        });
    });

    //statistics
    Route::group(['prefix' => 'statistics', 'as' => 'statistics.', 'middleware' => ['auth']], function () {
        Route::get('/list', function () {
            return view('adminModules.setting.statistics.list');
        })->name('list');
    });

    //schedule
    Route::group(['prefix' => 'schedule', 'as' => 'schedule.', 'middleware' => ['auth']], function () {
        Route::get('create',                    'Schedule\ScheduleController@create')->name('create');
        Route::get('list',                      'Schedule\ScheduleController@list')->name('list');
        Route::get('edit/{location}/{session}', 'Schedule\ScheduleController@edit')->name('edit');
        Route::get('getSchedules',              'Schedule\ScheduleController@getSchedules')->name('getSchedules');
        Route::get('getModelsLocation/{id}',    'Schedule\ScheduleController@getModelsLocation')->name('get_models_location');
        Route::post('store',                    'Schedule\ScheduleController@store')->name('store');
        Route::post('update',                   'Schedule\ScheduleController@update')->name('update');
        Route::post('updateWorkingDay',         'Schedule\ScheduleController@updateWorkingDay')->name('updateWorkingDay');
    });

    //bookings
    Route::group(['prefix' => 'bookings', 'as' => 'bookings.', 'middleware' => ['auth']], function () {
        Route::get('/{id}/audiovisuals',                'Booking\BookingController@index')->name('audiovisuals');
        Route::get('/{id}/english',                     'Booking\BookingController@english')->name('english');
        Route::get('/{id}/makeup',                      'Booking\BookingController@makeup')->name('makeup');
        Route::get('/{id}/psychology',                  'Booking\BookingController@psychology')->name('psychology');

        Route::get('/model',                            'Booking\BookingController@bookingModelView')->name('bookingModelView');
        Route::get('/show/{id}',                        'Booking\BookingController@show')->name('show');
        Route::get('/getSchedule/',                     'Booking\BookingController@getSchedule')->name('getSchedule');
        Route::get('/getSchedule/{id}',                 'Booking\BookingController@getScheduleById')->name('getScheduleById');
        Route::get('/getProcesses/{id}',                'Booking\BookingController@getProcessesById')->name('getProcessesById');
        Route::get('/editSchedule/{id}',                'Booking\BookingController@editSchedule')->name('editSchedule');
        Route::put('/updateSchedule/{id}',              'Booking\BookingController@updateSchedule')->name('updateSchedule');

        Route::get('/agenda/{id}',                      'Booking\BookingController@agenda')->name('agenda');
        Route::get('/agendaModel/{id}',                 'Booking\BookingController@agendaModel')->name('agendaModel');
        Route::post('/getAllBookings/{id}',             'Booking\BookingController@getBookingsByDate')->name('getBookingsByDate');
        Route::get('/getAllFinishedBookings',           'Booking\BookingController@getBookingFinished')->name('getBookingFinished');
        Route::get('/getExonerated/{id}',               'Booking\BookingController@getExonerated')->name('getExonerated');

        Route::get('/getModelSchedule/{id}',            'Booking\BookingController@getModelSchedule')->name('getModelSchedule');
        Route::get('/getBookingTypes',                  'Booking\BookingController@getBookingTypes')->name('getBookingTypes');
        Route::get('/seedDays/{id}',                    'Booking\BookingController@seedDays')->name('seedDays');

        Route::post('/store',                           'Booking\BookingController@store')->name('store');
        Route::post('/exonerate',                       'Booking\BookingController@exonerate')->name('exonerate');
        Route::put('/update/{id}',                      'Booking\BookingController@update')->name('update');
        Route::put('/updateprocess/{id}',               'Booking\BookingController@updateProcess')->name('updateProcess');

        Route::post('/createSchedule',                  'Booking\BookingController@createSchedule')->name('createSchedule');
        Route::delete('/deleteBooking/{id}',            'Booking\BookingController@destroy')->name('destroy');
        Route::delete('/deleteExonerate/{id}',          'Booking\BookingController@deleteExonerate')->name('deleteExonerate');
        Route::post('/updateAllocateDays/{id}',         'Booking\BookingController@updateAllocateDays')->name('updateAllocateDays');
        Route::post('/addMoreModels',                   'Booking\BookingController@addMoreModels')->name('addMoreModels');

        //processes
        Route::get('/getProcesses',                     'Booking\BookingController@getProcessesByDate')->name('getProcessesByDate');
        Route::put('/updateProcess/{id}',               'Booking\BookingController@updateProcess')->name('updateProcess');
        Route::put('/deleteProcess/{id}',               'Booking\BookingController@deleteProcess')->name('deleteProcess');
        Route::put('/reschedule/{id}',                  'Booking\BookingController@reschedule')->name('reschedule');
        Route::post('/block',                           'Booking\BookingController@blockDate')->name('blockDate');
        Route::post('/allocateDays',                    'Booking\BookingController@allocateDays')->name('allocateDays');
        Route::post('/daysBySeed/{id}',                 'Booking\BookingController@daysBySeed')->name('daysBySeed');

        //get days
        Route::get('/days',                             'Booking\BookingController@getDays')->name('getDays');
        Route::get('/locations',                        'Booking\BookingController@getLocations')->name('getLocations');

        //get users
        Route::get('/getUsers',                         'Booking\BookingController@getUsers')->name('getUsers');
    });

    //monitoting
    Route::group(['prefix' => 'monitoring', 'as' => 'monitoring.', 'middleware' => ['auth']], function () {
        Route::group(['middleware' => ['auth']], function () {
            Route::get('/',                         'Monitoring\MonitoringController@index')->name('index');
            Route::get('/locations',                'Monitoring\MonitoringController@locations')->name('locations');
            Route::get('/rooms/{id}',               'Monitoring\MonitoringController@rooms')->name('rooms');
            Route::get('/sessions',                 'Monitoring\MonitoringController@sessions')->name('sessions');
            Route::get('/monitors/{id}',            'Monitoring\MonitoringController@getMonitors')->name('getMonitors');

            Route::post('/reports',                 'Monitoring\MonitoringController@allReports')->name('allReports');
            Route::post('/assign',                  'Monitoring\MonitoringController@assignReport')->name('assignReport');
            Route::post('/archive',                 'Monitoring\MonitoringController@archiveReport')->name('archiveReport');
            Route::post('/getunjusfiedday',         'Monitoring\MonitoringController@searchUnjustifiedDate')->name('searchUnjustifiedDate');
            Route::post('/summary',                 'Monitoring\MonitoringController@summary')->name('summary');
            Route::post('/attendances',             'Monitoring\MonitoringController@allAttendances')->name('allAttendances');
            Route::post('/saveStep',                'Monitoring\MonitoringController@saveStep')->name('saveStep');
            Route::post('/saveReportImages',        'Monitoring\MonitoringController@saveReportImages')->name('saveReportImages');
            Route::post('/finalizeReport',        'Monitoring\MonitoringController@finalizeReport')->name('finalizeReport');

            Route::post('/storeAttendance',         'Monitoring\MonitoringController@storeAttendance')->name('store_attendance');
            Route::post('/saveconnection/{id}',     'Monitoring\MonitoringController@saveConnection')->name('saveConnection');
            Route::post('/pauseDisconnection',      'Monitoring\MonitoringController@pauseDisconnection')->name('pauseDisconnection');
            Route::put('/update/{id}',              'Monitoring\MonitoringController@update')->name('update');
            Route::delete('/delete/{id}',           'Monitoring\MonitoringController@destroy')->name('destroy');

            Route::get('/streamate',                'Statistics\StatisticsController@streamate')->name('streamate');
            Route::get('/imlive',                   'Statistics\StatisticsController@imlive')->name('imlive');
            Route::get('/cams',                     'Statistics\StatisticsController@cams')->name('cams');
            Route::get('/pages',                    'Statistics\StatisticsController@getPages')->name('getPages');
            Route::post('/statistics',               'Statistics\StatisticsController@getStatistics')->name('getStatistics');
            Route::post('/modelPages',               'Statistics\StatisticsController@getPages')->name('getPages');
            Route::post('/saveStatistics',               'Statistics\StatisticsController@saveStatistics')->name('saveStatistics');
            Route::get('/getModels/{id}',               'Statistics\StatisticsController@getModels')->name('getModels');
            Route::get('/allPages/{id}',               'Statistics\StatisticsController@AllPages')->name('AllPages');
            Route::get('/executeCrawler',               'Statistics\StatisticsController@executeCrawler')->name('executeCrawler');
        });
    });

    //multimedia
    Route::group(['prefix' => 'multimedia', 'as' => 'multimedia.', 'middleware' => ['auth']], function () {
        Route::get('/list', function () {
            return view('adminModules.setting.multimedia.list');
        })->name('list');
    });

    //cafeteria
    Route::group(['prefix' => 'cafeteria', 'as' => 'cafeteria.', 'middleware' => ['auth']], function () {
        Route::get('/list', function () {
            return view('adminModules.setting.cafeteria.list');
        })->name('list');
        Route::group(['middleware' => ['auth']], function () {
            Route::get('menu'  , 'Cafeteria\CafeteriaController@menu')->name('menu');
            Route::get('orders', 'Cafeteria\CafeteriaController@orders')->name('orders');

            // ACTIONS
            Route::get('/getMenus'           , 'Cafeteria\CafeteriaController@getMenus')->name('get_menus');
            Route::get('/getOrders'          , 'Cafeteria\CafeteriaController@getOrders')->name('get_orders');
            Route::get('/getCafeteriaTypes'  , 'Cafeteria\CafeteriaController@getCafeteriaTypes')->name('get_cafeteria_types');
            Route::post('/saveMenu'          , 'Cafeteria\CafeteriaController@saveMenu')->name('save_menu');
            Route::post('/editMenu'          , 'Cafeteria\CafeteriaController@editMenu')->name('edit_menu');
            Route::post('/getDayMenu'        , 'Cafeteria\CafeteriaController@getDayMenu')->name('get_day_menu');
            Route::post('/saveOrder'         , 'Cafeteria\CafeteriaController@saveOrder')->name('save_order');
            Route::post('/editMaxOrderTime'  , 'Cafeteria\CafeteriaController@editMaxOrderTime')->name('edit_max_order_time');
            Route::post('/getWeekTotalSales' , 'Cafeteria\CafeteriaController@getWeekTotalSales')->name('get_week_total_sales');
        });
    });

    //boutique
    Route::group(['prefix' => 'boutique', 'as' => 'boutique.', 'middleware' => ['auth']], function () {
        Route::group(['middleware' => ['auth']], function () {
            Route::get('products'                   , 'Boutique\BoutiqueController@products')->name('products');
            Route::get('categories'                 , 'Boutique\BoutiqueController@categories')->name('categories');
            Route::get('purchases'                  , 'Boutique\BoutiqueController@purchases')->name('purchases');
            Route::get('sales'                      , 'Boutique\BoutiqueController@sales')->name('sales');
            Route::get('satellite-sales'            , 'Boutique\BoutiqueController@satelliteSales')->name('satellite_sales');
            Route::get('blocks'                     , 'Boutique\BoutiqueController@blocks')->name('blocks');
            Route::get('massive'                    , 'Boutique\BoutiqueController@massive')->name('massive');
            Route::get('inventory'                  , 'Boutique\BoutiqueController@inventory')->name('inventory');
            Route::get('inventory-ingresses/{date?}', 'Boutique\BoutiqueController@inventoryIngresses')->name('inventory_ingresses');

            //ACTIONS
            Route::post('/saveProduct'                          , 'Boutique\BoutiqueController@saveProduct')->name('save_product');
            Route::post('/saveCategory'                         , 'Boutique\BoutiqueController@saveCategory')->name('save_category');
            Route::post('/editCategory'                         , 'Boutique\BoutiqueController@editCategory')->name('edit_category');
            Route::post('/deleteCategory'                       , 'Boutique\BoutiqueController@deleteCategory')->name('delete_category');
            Route::post('getProductLocationQuantity'            , 'Boutique\BoutiqueController@getProductLocationQuantity')->name('get_product_location_quantity');
            Route::post('getProduct'                            , 'Boutique\BoutiqueController@getProduct')->name('get_product');
            Route::get('getProducts'                            , 'Boutique\BoutiqueController@getProducts')->name('get_products');
            Route::get('getCategories'                          , 'Boutique\BoutiqueController@getCategories')->name('get_categories');
            Route::post('/sellProduct'                          , 'Boutique\BoutiqueController@sellProduct')->name('sell_product');
            Route::post('/editProduct'                          , 'Boutique\BoutiqueController@editProduct')->name('edit_product');
            Route::get('/getLogs'                               , 'Boutique\BoutiqueController@getLogs')->name('get_logs');
            Route::get('/getProductLogs'                        , 'Boutique\BoutiqueController@getProductLogs')->name('get_product_logs');
            Route::post('/deleteProduct'                        , 'Boutique\BoutiqueController@deleteProduct')->name('delete_product');
            Route::get('/getMyPurchases'                        , 'Boutique\BoutiqueController@getMyPurchases')->name('get_my_purchases');
            Route::get('/getSales'                              , 'Boutique\BoutiqueController@getSales')->name('get_sales');
            Route::get('/getSatelliteSales'                     , 'Boutique\BoutiqueController@getSatelliteSales')->name('get_satellite_sales');
            Route::post('/deleteSale/{satellite?}'              , 'Boutique\BoutiqueController@deleteSale')->name('delete_sale');
            Route::get('/exportWeekSales/{start}/{end}'         , 'Boutique\BoutiqueController@exportWeekSales')->name('export_week_sales');
            Route::get('/exportSatelliteWeekSales/{start}/{end}', 'Boutique\BoutiqueController@exportSatelliteWeekSales')->name('export_satellite_week_sales');
            Route::get('/exportInventory'                       , 'Boutique\BoutiqueController@exportInventory')->name('export_inventory');
            Route::get('/getBlockedUsers'                       , 'Boutique\BoutiqueController@getBlockedUsers')->name('get_blocked_users');
            Route::post('/blockUser'                            , 'Boutique\BoutiqueController@blockUser')->name('block_user');
            Route::post('/saveBlockValue'                       , 'Boutique\BoutiqueController@saveBlockValue')->name('save_block_value');
            Route::post('/deleteUserBlock'                      , 'Boutique\BoutiqueController@deleteUserBlock')->name('delete_user_block');
            Route::post('/returnProduct'                        , 'Boutique\BoutiqueController@returnProduct')->name('return_product');
            Route::post('/transferProduct'                      , 'Boutique\BoutiqueController@transferProduct')->name('transfer_product');
            Route::post('/massiveSell'                          , 'Boutique\BoutiqueController@massiveSell')->name('massive_sell');
            Route::post('/insertInventory'                      , 'Boutique\BoutiqueController@insertInventory')->name('insert_inventory');
        });
    });

    //maintenance
    Route::group(['prefix' => 'maintenance', 'as' => 'maintenance.', 'middleware' => ['auth']], function () {
        Route::group(['middleware' => ['auth']], function () {
            Route::get('/', 'Maintenance\MaintenanceController@index')->name('index');

            Route::get('/getMaintenanceTasks', 'Maintenance\MaintenanceController@getMaintenanceTasks')->name('get_maintenance_tasks');
            Route::get('/getFinishedMaintenanceTasks', 'Maintenance\MaintenanceController@getFinishedMaintenanceTasks')->name('get_finished_maintenance_tasks');
            Route::post('/createMaintenanceTask', 'Maintenance\MaintenanceController@createMaintenanceTask')->name('create_maintenance_task');
            Route::post('/markAsDone', 'Maintenance\MaintenanceController@markAsDone')->name('mark_as_done');
            Route::post('/markAsVerified', 'Maintenance\MaintenanceController@markAsVerified')->name('mark_as_verified');
            Route::post('/markAsRejected', 'Maintenance\MaintenanceController@markAsRejected')->name('mark_as_rejected');
            Route::post('/markAsViewed', 'Maintenance\MaintenanceController@markAsViewed')->name('mark_as_viewed');
        });
    });

    // MODULE PERMISSIONS (Before: Tasks)
    Route::group(['prefix' => 'permission', 'as' => 'permission.', 'middleware' => ['auth']], function () {
        Route::get('/list', 'Settings\ModulePermissionController@list')->name('list');

        // ACTIONS
        Route::get('/getPermissions/{id?}',         'Settings\ModulePermissionController@getPermissions')->name('get_permissions');
        Route::post('/savePermission',              'Settings\ModulePermissionController@savePermission')->name('save_permission');
        Route::post('/getPermission',               'Settings\ModulePermissionController@getPermission')->name('get_permission');
        Route::post('/editPermission',              'Settings\ModulePermissionController@editPermission')->name('edit_permission');
        Route::get('/list/{id}',                    'Settings\ModulePermissionController@list')->where('id', '[0-9]+')->name('module_permission');
        Route::post('/assignPermission',            'Settings\ModulePermissionController@assignPermission')->name('assign_permission');
        Route::post('/editPermissionDescription',   'Settings\ModulePermissionController@editPermissionDescription')->name('edit_permission_description');
    });

    // ROLES
    Route::group(['prefix' => 'role', 'as' => 'role.', 'middleware' => ['auth']], function () {
        Route::get('/list',         'Settings\RoleController@list')->name('list');

        // ACTIONS
        Route::get('/AllRoles',     'Settings\RoleController@AllRoles')->name('all_roles');
        Route::get('/getRoles',     'Settings\RoleController@getRoles')->name('get_roles');
        Route::post('/saveRole',    'Settings\RoleController@saveRole')->name('save_role');
        Route::post('/getRole',     'Settings\RoleController@getRole')->name('get_role');
        Route::post('/editRole',    'Settings\RoleController@editRole')->name('edit_role');
        Route::post('/deleteRole',  'Settings\RoleController@deleteRole')->name('delete_role');
        Route::get('/permissions/{permission}', 'Settings\RoleController@getUserPermission')->name('getUserPermission');
    });

    //location
    Route::group(['prefix' => 'location', 'as' => 'location.', 'middleware' => ['auth']], function () {
        Route::get('/list', function () {
            return view('adminModules.setting.location.list');
        })->name('list');
        Route::get('/getLocations',     'Settings\LocationController@getLocations')->name('get_locations');
        Route::get('/infoEdit/{id}',    'Settings\LocationController@infoEdit')->name('edit');
        Route::post('/create',          'Settings\LocationController@create')->name('create');
        Route::post('/update',          'Settings\LocationController@update')->name('update');
    });

    //pages
    Route::group(['prefix' => 'setting', 'as' => 'setting.', 'middleware' => ['auth']], function () {

        Route::group(['prefix' => 'page', 'as' => 'page.'], function () {
            Route::get('/'                  ,'Settings\PageController@viewPages')->name('list');
            Route::get('/get'                  ,'Settings\PageController@getPages')->name('get_pages');
        });
    });

    Route::group(['prefix' => 'studio', 'as' => 'studio.', 'middleware' => ['auth']], function () {
        Route::get('/list', 'Studios\StudioController@list')->name('list');
        Route::get('/assign-users', 'Studios\StudioController@assignUsers')->name('assign_users');
        Route::get('/generate-impersonate-token/{tenant_id}', 'Studios\StudioController@generateImpersonateToken')->name('generate-impersonate-token');

        // ACTIONS
        Route::get('/getStudios'       ,'Studios\StudioController@getStudios')->name('get_studios');
        Route::post('/createStudio'    ,'Studios\StudioController@createStudio')->name('create_studio');
        Route::post('/changeStudioStatus','Studios\StudioController@changeStudioStatus')->name('change_studio_status');
        Route::post('/changeStudioLogo','Studios\StudioController@changeStudioLogo')->name('change_studio_logo');
        Route::post('/checkIfStudioExists','Studios\StudioController@checkIfStudioExists')->name('check_if_studio_exists');
        Route::post('/changeToStudio','Studios\StudioController@changeToStudio')->name('change_to_studio');
        Route::post('/assignStudio','Studios\StudioController@assignStudio')->name('assigns_studio');
        Route::post('/getStudioAssignments','Studios\StudioController@getStudioAssignments')->name('get_studio_assignments');
        Route::post('/getStudioUsers','Studios\StudioController@getStudioUsers')->name('get_studio_users');
        Route::post('/assignUserToTenant','Studios\StudioController@assignUserToTenant')->name('assign_user_to_tenant');
        Route::get('/getAssignedUsers','Studios\StudioController@getAssignedUsers')->name('get_assigned_users');
        Route::post('/removeAssignments','Studios\StudioController@removeAssignments')->name('remove_assignments');
    });

    Route::group(['prefix' => 'module', 'as' => 'module.', 'middleware' => ['auth']], function () {
        Route::get('/getModules',   'Settings\ModuleController@getModules')->name('getModules');
        Route::get('/getModule',    'Settings\ModuleController@getModule')->name('getModule');
        Route::post('/create',      'Settings\ModuleController@create')->name('create');
        Route::post('/update',      'Settings\ModuleController@update')->name('update');

        Route::get('/list', function () {
            return view('adminModules.setting.module.list');
        })->name('list');
    });

    Route::group(['prefix' => 'rh', 'as' => 'rh.', 'middleware' => ['auth']], function () {
        //##INTERVIEWS---------------------------------------------------------------------------------------------------------
        Route::get('/interview/create/{id?}',               'HumanResources\HumanResourceController@storeInterview')->name('interview.storeInterview');
        Route::post('/interview/createInterview',           'HumanResources\HumanResourceController@createInterview')->name('interview.create');
        Route::post('/interview/user/editPersonal',         'HumanResources\HumanResourceController@updateInterviewPersonal')->name('interview.editPersonal');
        Route::post('/interview/user/editEducation',        'HumanResources\HumanResourceController@updateInterviewEducation')->name('interview.editEducation');
        Route::post('/interview/user/editWorking',          'HumanResources\HumanResourceController@updateInterviewWorking')->name('interview.editWorking');
        Route::post('/interview/user/editAdditional',       'HumanResources\HumanResourceController@updateInterviewAdditional')->name('interview.editAdditional');
        Route::post('/interview/delete',                    'HumanResources\HumanResourceController@deleteInterview')->name('interview.deleteInterview');
        //->Other interview
        Route::get('/interview/other/list',                 'HumanResources\HumanResourceController@listInterviewOther')->name('interview.user.list');
        Route::get('/interview/getInterviewsOther',         'HumanResources\HumanResourceController@getInterviewsOther')->name('interview.listInterviewsOther');
        Route::get('/interview/other/edit/{id}',            'HumanResources\HumanResourceController@editOtherInterview')->name('interview.others.edit');
        //->Model interview
        Route::get('/interview/model/list',                 'HumanResources\HumanResourceController@listInterviewModel')->name('interview.model.list');
        Route::get('/interview/getInterviewsModel',         'HumanResources\HumanResourceController@getInterviewsModel')->name('interview.listInterviewsModel');
        Route::get('/interview/model/edit/{id}',            'HumanResources\HumanResourceController@editModelInterview')->name('interview.model.edit');
        //->Model->IMG
        Route::post('/interview/model/getImg',              'HumanResources\HumanResourceController@getInterviewModelImg')->name('interview.getInterviewimg');
        Route::post('/interview/model/getExistImg',         'HumanResources\HumanResourceController@getInterviewModelExistImg')->name('interviewImg.getExistImg');
        Route::post('/interview/model/create',              'HumanResources\HumanResourceController@createInterviewModelIMG')->name('interviewImg.create');
        Route::post('/interview/model/update',              'HumanResources\HumanResourceController@updateInterviewModelIMG')->name('interviewImg.update');
        Route::post('/interview/getInterview',              'HumanResources\HumanResourceController@getInterview')->name('interview.getInterview');
        //->Otrher interview->Function
        Route::get('/interview/getInterviewHistory',        'HumanResources\HumanResourceController@getInterviewHistory')->name('interview.getInterviewHistory');
        Route::post('/interview/getInterviewID',            'HumanResources\HumanResourceController@getInterviewID')->name('interview.getInterviewFullName');
        Route::post('/interview/actionInterviewToUser',     'HumanResources\HumanResourceController@actionInterviewToUser')->name('interview.actionInterviewToUser');
        Route::get('/interview/getCities',                  'HumanResources\HumanResourceController@getCities')->name('interview.getCities');
        Route::post('/interview/updateCite',                'HumanResources\HumanResourceController@updateCite')->name('interview.updateCite');
        //##VACATION REQUEST---------------------------------------------------------------------------------------------------
        Route::get('/vacationRequest/list',                 'HumanResources\HumanResourceController@listVacationRequest')->name('vacationRequest.list');
        Route::get('/vacationRequest/getVacationsRequest',  'HumanResources\HumanResourceController@getVacationRequest')->name('vacationRequest.get_vacationRequest');
        Route::post('/vacationRequest/create',              'HumanResources\HumanResourceController@createVacationRequest')->name('vacationRequest.create');
        Route::post('/vacationRequest/update',              'HumanResources\HumanResourceController@updateVacationRequest')->name('vacationRequest.update');
        Route::get('/vacationRequest/staffVacations',       'HumanResources\HumanResourceController@staffVacations')->name('vacationRequest.staffVacations');
        //##EXTRA HOURS---------------------------------------------------------------------------------------------------------
        Route::get('/extraHours/list',                      'HumanResources\HumanResourceController@listExtraHour')->name('extraHours.list');
        Route::get('/extraHours/listProcess',               'HumanResources\HumanResourceController@listExtraHourProcess')->name('extraHours.listProcess');
        Route::get('/extraHours/edit',                      'HumanResources\HumanResourceController@editExtraHour')->name('extraHours.edit');
        Route::post('/extraHours/create',                   'HumanResources\HumanResourceController@createExtraHour')->name('extraHours.create');
        Route::get('/extraHours/getOvertimeValue',          'HumanResources\HumanResourceController@getOvertimeValue')->name('getOvertimeValue');
        Route::get('/extraHours/getRHExtraHourRange',       'HumanResources\HumanResourceController@getRHExtraHourRange')->name('geOvertimeValue.getRHExtraHourRange');
        Route::get('/extraHours/getExtraHourValue',         'HumanResources\HumanResourceController@getExtraValue')->name('extraHours.getExtraValue');
        Route::post('/extraHours/UpdateExtraValue',         'HumanResources\HumanResourceController@UpdateExtraValue')->name('extraHours.UpdateExtraValue');
        Route::get('/extraHours/getExtraHourHistory',       'HumanResources\HumanResourceController@getExtraHourHistory')->name('extraHours.getExtraHourHistory');
        Route::get('/extraHours/getExtraHourHistoryProcess','HumanResources\HumanResourceController@getExtraHourHistoryProcess')->name('extraHours.getExtraHourHistoryProcess');
        Route::get('/extraHours/getExtraHourProcess',       'HumanResources\HumanResourceController@getExtraHourProcess')->name('extraHours.getExtraHourProcess');
        Route::post('/extraHours/updateHourRequest',        'HumanResources\HumanResourceController@updateHourRequest')->name('extraHours.updateHourRequest');

        Route::get('/referred',             'HumanResources\HumanResourceController@referred')->name('referred.index');
        Route::get('/getReferredModels',             'HumanResources\HumanResourceController@getReferredModels')->name('get_referred_models');
        Route::post('/createReferredModel',             'HumanResources\HumanResourceController@createReferredModel')->name('create_referred_model');
        Route::post('/editReferredModel',             'HumanResources\HumanResourceController@editReferredModel')->name('edit_referred_model');
        Route::post('/seenReferredModel',             'HumanResources\HumanResourceController@seenReferredModel')->name('seen_referred_model');
        Route::post('/deleteReferredModel',             'HumanResources\HumanResourceController@deleteReferredModel')->name('delete_referred_model');
        Route::post('/referModel',             'HumanResources\HumanResourceController@referModel')->name('refer_model');
        Route::post('/referModelProspect',             'HumanResources\HumanResourceController@referModelProspect')->name('refer_model_prospect');

    });

    Route::get('/', function () {
        return view('auth.login');
    });

    Route::get('/token/{user_id}', function ($user_id) {
        $redirectUrl = '/dashboard';
        $tenant = Tenant::find(1);
        $token = tenancy()->impersonate($tenant, $user_id, $redirectUrl);

        return redirect("https://laravel.gbmediagroup.com/public/impersonate/$token->token");
    });

    Route::get('/page/Create', function () {
        return view('adminModules.page.Create');
    });

    Route::get('/home/main', function () {
        return view('adminModules.home.main');
    });

    Route::get('/plantilla', function () {
        return view('adminCoreUi.app');
    });

    //Route::get('/impersonate/{token}', 'Studios\StudioController@impersonate')->name('impersonate');
    Route::get('/impersonate/{token}', function ($token) {
        return UserImpersonation::makeResponse($token);
    })->name('impersonate');

    Auth::routes(['register' => false]);


});

