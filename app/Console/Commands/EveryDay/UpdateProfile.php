<?php

namespace App\Console\Commands\EveryDay;

use App\Models\News\NewsRoles;
use App\Models\News\NewsUsers;
use App\Models\Training\Trainingroles;
use App\Models\Training\TrainingUsers;
use App\Models\Wiki\WikiRole;
use App\Models\Wiki\WikiUser;
use App\User;
use Illuminate\Console\Command;

class UpdateProfile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:profile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('**************Started updating profile');
        $users = User::where('status', 1)->get();

        foreach($users as $user){
            $hasWikiRoles = WikiRole::where('setting_role_id', $user->setting_role_id)->get();
            $this->info($hasWikiRoles);
            foreach($hasWikiRoles as $wikiRole){
                if (!is_null($wikiRole)){
                    $hasWiki = WikiUser::where('user_id', $user->id)->exists();
                    if (!$hasWiki){
                        WikiUser::updateOrCreate([
                            'user_id' => $wikiRole->wiki_id,
                            'wiki_id' => $user->id,
                            'studio_id' => tenant('id'),
                            'status' => 0,
                        ]);
                    }else{
                        $this->info('**************User has wiki');
                    }
                }
                else{
                    $this->info('**************There is nothing in Wiki users');
                }
            }

            $hasNewsRoles = NewsRoles::where('role_id', $user->setting_role_id)->get();
            $this->info($hasNewsRoles);
            foreach ($hasNewsRoles as $newsRole){
                if (!is_null($newsRole)){
                    $hasNews = NewsUsers::where('user_id', $user->id)->exists();
                    if (!$hasNews){
                        NewsUsers::updateOrCreate([
                            'news_id' => $newsRole->news_id,
                            'user_id' => $user->id,
                            'studio_id' => tenant('id'),
                            'status' => 0,
                        ]);
                    }else{
                        $this->info('**************User has news');
                    }
                }
                else{
                    $this->info('**************There is nothing in News users');
                }
            }

            $trainingRoles = Trainingroles::where('setting_role_id', $user->setting_role_id)->get();
            foreach ($trainingRoles as $trainingRole){
                if (!is_null($trainingRole)){
                    $hasTraining = TrainingUsers::where('user_id', $user->id)->exists();
                    if (!$hasTraining){
                        TrainingUsers::updateOrCreate([
                            'news_id' => $trainingRole->training_id,
                            'user_id' => $user->id,
                            'studio_id' => tenant('id'),
                            'status' => 0,
                        ]);
                    }else{
                        $this->info('**************User has training');
                    }
                }
                else{
                    $this->info('**************There is nothing in Traning users');
                }
            }
        }

        $this->info('**************Ended updating profile');

    }
}
