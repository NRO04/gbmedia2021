<?php

namespace App\Http\Controllers\News;

use App\Events\Activities;
use App\Exports\Satellite\PayrollStatistic;
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\News\NewsStudio;
use App\Models\News\Seen;
use App\Models\Tenancy\Tenant;
use App\Traits\TraitGlobal;
use Illuminate\Http\Request;
use App\Models\News\Comments;
use App\Models\News\Likes;
use App\Models\News\News;
use App\Models\News\NewsRoles;
use App\Models\News\NewsUsers;
use App\Models\Settings\SettingRole;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;
use Maatwebsite\Excel\Facades\Excel;

class NewsController extends Controller
{
    use TraitGlobal;

    function __construct()
    {
        $this->middleware('auth');
        
    }

    public function index()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        return view('adminModules.news.index', compact('user_permission'));
    }

    public function getNews(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $user = auth()->user()->id;
        $data = [];
        
        $canShare = false;
        if (tenant('id') === 1){
            $canShare = true;
        }

        if ($search == '') {
            $tenant_id = tenant('id');
            $created_by = "";
            $news = NewsStudio::join('news_users', 'news_users.news_id', 'news_studios.news_id')
                ->where('news_studios.studio_id', $tenant_id)
                ->where('news_users.user_id', auth()->user()->id)
                ->where('news_users.studio_id', $tenant_id)
                ->orderBy('news_studios.id', 'DESC')->paginate(20);
            foreach ($news as $item){
                $roles = newsRoles::where('news_id', $item->news_id)->where('studio_id', tenant('id'))->count();
                $likes = likes::where('news_id', $item->news_id)->count();
                $comments = Comments::where('news_id', $item->news_id)->count();
                $views = Seen::where('news_id', $item->news_id)->count();
                $news_table = News::find($item->news_id);

                $tenant = Tenant::find($item->studio_id);
                $user_id = $item->created_by;
                if ($user_id !== 0){
                    $created_by = $tenant->run(function () use ($tenant, $user_id) {
                        $user = User::where('id', $user_id)->first();
                        return $user->first_name." ".$user->last_name;
                    });
                }


                $data[] = [
                    'id' => $news_table->id,
                    'file' => "../storage/app/public/".$news_table->file,
                    'extension' => $news_table->extension,
                    'roles_count' => $roles,
                    'likes_count' => $likes,
                    'comments_count' => $comments,
                    'title' => $news_table->title,
                    'body' => $news_table->body,
                    'created_at' => $news_table->created_at,
                    'created_by' => $created_by,
                    'logged_user_id' => $user,
                    'views' => $views,
                    'canShare' => $canShare
                ];
            }
        }
        else{
            $ns = News::where('news.'.$filter, 'like', '%' . $search . '%')->orderBy('news.created_at', 'DESC')->paginate(20);
            foreach($ns as $n)
            {
                $tenant_id = tenant('id');
                $news = NewsStudio::join('news_users', 'news_users.news_id', 'news_studios.news_id')
                    ->where('news_studios.studio_id', $tenant_id)
                    ->where('news_users.user_id', auth()->user()->id)
                    ->where('news_users.studio_id', $tenant_id)
                    ->where('news_studios.news_id', $n->id)
                    ->orderBy('news_studios.id', 'DESC')->paginate(20);
                foreach ($news as $item){
                    $roles = newsRoles::where('news_id', $item->news_id)->where('studio_id', tenant('id'))->count();
                    $likes = likes::where('news_id', $item->news_id)->count();
                    $comments = Comments::where('news_id', $item->news_id)->count();
                    $views = Seen::where('news_id', $item->news_id)->count();
                    $news_table = News::find($item->news_id);

                    $tenant = Tenant::find($item->studio_id);
                    $user_id = $item->created_by;
                    if ($user_id !== 0){
                        $created_by = $tenant->run(function () use ($tenant, $user_id) {
                            $user = User::where('id', $user_id)->first();
                            return $user->first_name." ".$user->last_name;
                        });
                    }
                    $data[] = [
                        'id' => $news_table->id,
                        'file' => "../storage/app/public/".$news_table->file,
                        'extension' => $news_table->extension,
                        'roles_count' => $roles,
                        'likes_count' => $likes,
                        'comments_count' => $comments,
                        'title' => $news_table->title,
                        'body' => $news_table->body,
                        'created_at' => $news_table->created_at,
                        'created_by' => $created_by,
                        'logged_user_id' => $user,
                        'views' => $views,
                        'canShare' => $canShare
                    ];
                }
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'pagination' => [
                    'total' => $news->total(),
                    'current_page' => $news->currentPage(),
                    'per_page' => $news->perPage(),
                    'last_page' => $news->lastPage(),
                    'from' => $news->firstItem(),
                    'to' => $news->lastItem()
                ],
                'news' => $data
            ]);
        } else {
            return redirect()->route('dashboard');
        }
    }

    public function getRoles(Request $request)
    {
        $roles = SettingRole::select('name', 'id')->where('name', '<>', 'sin rol')->orderBy('name', 'ASC')->get();
        return $request->ajax() ? ['roles' => $roles] : redirect()->route('/news');
    }

    public function show($id)
    {
        $news = News::findOrFail($id);
        $tenant = Tenant::find(tenant('id'));
        $roles = NewsRoles::select('role_id')->where('news_id', $id)->where('studio_id', tenant('id'))->get();
        $roles = $roles->toArray();
        $array_roles = array_slice( $roles, 0, 3);
        $roles_name = $tenant->run(function () use ($tenant, $array_roles) {
            $roles_name = "";
            foreach ($array_roles as $role)
            {
                $setting_role = SettingRole::select('name')->where('id', $role['role_id'])->first();
                $roles_name .= ($roles_name == "")? $setting_role->name : ", ".$setting_role->name;
            }
            return $roles_name;
        });

        $all_roles = $tenant->run(function () use ($tenant, $roles) {
            $roles_name = "";
            foreach ($roles as $role)
            {
                $setting_role = SettingRole::select('name')->where('id', $role['role_id'])->first();
                $roles_name .= ($roles_name == "")? $setting_role->name : ", ".$setting_role->name;
            }
            return $roles_name;
        });

        $studios = NewsStudio::where('news_id', $id)->where('studio_id', '!=', tenant('id'))->get();
        $studios = $studios->toArray();
        $array = array_slice( $studios, 0, 3);
        $studios_name = $tenant->run(function () use ($tenant, $array) {
            $studios_name = "";
            foreach ($array as $studio)
            {
                $tenant = Tenant::where('id', $studio['studio_id'])->first();
                $studios_name .= ($studios_name == "")? $tenant->studio_name : ", ".$tenant->studio_name;
            }
            return $studios_name;
        });

        $all_studios = $tenant->run(function () use ($tenant, $studios) {
            $studios_name = "";
            foreach ($studios as $studio)
            {
                $tenant = Tenant::where('id', $studio['id'])->first();
                $studios_name .= ($studios_name == "")? $tenant->studio_name : ", ".$tenant->studio_name;
            }
            return $studios_name;
        });

        $share_count = NewsStudio::where('news_id', $id)->where('studio_id', '!=', tenant('id'))->count();
        $roles_count = NewsRoles::select('role_id')->where('news_id', $id)->where('studio_id', tenant('id'))->count();

        $views = Seen::where([['news_id', $id], ['user_id', auth()->user()->id], ['studio_id', tenant('id')]])->exists();
        if (!$views){
            Seen::create([
                'news_id' => $id,
                'studio_id' => tenant('id'),
                'user_id' => auth()->user()->id,
            ]);
        }

        $update_status = NewsUsers::where('news_id', $id)->where('studio_id', tenant('id'))->first();
        if (!is_null($update_status)){
            $update_status->update(['status' => 1]);
        }

        $now = Carbon::now();
        $created_at = Carbon::parse($news->created_at);
        $mins = $created_at->diffInMinutes($now);   // 180
        $time = $this->convertToMinsHours($mins);

        $studio = NewsStudio::where('news_id', $id)->first();
        $tenant = Tenant::find($studio->studio_id);
        $user_id = $studio->created_by;
        $created_by = $tenant->run(function () use ($tenant, $user_id) {
            $user = User::where('id', $user_id)->first();
            return $user->first_name." ".$user->last_name;
        });

        $data ['news'] = [
            'news_id' => $id,
            'roles_count' => $roles_count - 3,
            'share_count' => $share_count - 3,
            'all_studios' => $all_studios,
            'all_roles' => $all_roles,
            'title' => $news->title,
            'description' => $news->body,
            'file' => "../storage/app/public/".$news->file,
            'created_at' => $time,
            'roles_name' => $roles_name,
            'studios_name' => $studios_name,
            'created_by' => $created_by,
            'extension' => $news->extension
        ];

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $this->validate($request,
            [
            'title' => 'required|string|max:255',
            'body' => 'required',
            'file' => 'required'
            ],
            [
                'title.required' => 'Por favor, escriba un titulo para su noticia',
                'body.required' => 'Por favor, escriba un contenido para su noticia',
                'file.required' => 'Por favor, cargue una imagen',
            ]
        );
        $news = "";
        $studio = "";

        if ($request->ajax()){
            try {
                $title = htmlentities($request->input("title"));
                $description = htmlentities($request->input("body"));
                $is_shared = $request->input("is_shared");
                $studio_shared = $request->input("studio_shared");
                $created_by = auth()->user()->id;
                $role_ids = json_decode($request->role_ids);

                $cover = $request->file("file");
                $slug = Str::slug($title);
                $slug = str_replace("-", "", $slug);
                $path = tenant('studio_slug')."/news/files/";

                if(!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->makeDirectory($path);
                }

                $currentDate = Carbon::now()->toDateString();
                $currentDate = str_replace("-", "", $currentDate);
                $filename = $slug.$currentDate.uniqid().".".$cover->getClientOriginalExtension();

                $extension = $cover->getClientOriginalExtension();
                if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg'){
                    $extension = "IMG";
                    $resizedImage = \Intervention\Image\Facades\Image::make($cover)->resize(1500, 1000)->stream();
                    Storage::disk('public')->put("GB/news/files/".$filename, $resizedImage);
                }else{
                    $extension = "VID";
                    Storage::disk('public')->put("GB/news/files/".$filename, file_get_contents($cover));
                }

                DB::beginTransaction();

                    $news = News::create([
                        'title' => $title,
                        'body' => $description,
                        'file' => $path.$filename,
                        'extension' => $extension,
                        'created_by' => $created_by,
                        'is_shared' => $is_shared,
                        'studio_shared' => $studio_shared
                    ]);

                    $news_studio = new NewsStudio;
                    $news_studio->news_id = $news->id;
                    $news_studio->studio_id = tenant('id');
                    $news_studio->created_by = auth()->user()->id;
                    $news_studio->save();

                    $manager = 3;
                    if (!array_search($manager,$role_ids)){
                        array_push($roles, $manager);
                    }
                    
                    foreach($role_ids as $role_id)
                    {
                        $role = new NewsRoles;
                        $role->news_id = $news->id;
                        $role->role_id = $role_id;
                        $role->role_name = "";
                        $role->studio_id = tenant('id');
                        $role->save();

                        $users = $this->getUsersFromRol($role_id, tenant('id'));

                        foreach ($users as $value){
                            $user = new NewsUsers;
                            $user->news_id = $news->id;
                            $user->user_id = $value->id;
                            $user->studio_id = tenant('id');
                            $user->status = 0;
                            $user->save();
                        }
                    }

                DB::commit();

                if ($news){
                    $msg = "Noticia creada exitosamente!";
                    $code = 200;
                    $icon = "success";

                }else{
                    $msg = "Noticia no pudo ser creada!";
                    $code = 422;
                    $icon = "error";
                }

            }catch (\Exception $ex){
                $msg = $ex;
                $code = 500;
                $icon = "error";
                DB::rollback();
            }
        }else{
            return redirect()->route('dashboard');
        }



        return response()->json([
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon,
            'news' => $news,
            'ceo' => $studio,
        ]);
    }

    public function getUsersFromRol($role_id, $tenant_id)
    {
        $tenant = Tenant::find($tenant_id);
        $users = $tenant->run(function () use ($tenant, $role_id) {
            $users = User::where('setting_role_id', $role_id)->get();
            return $users;
        });
        return $users;
    }

    public function getComments($id)
    {
        $comments = Comments::where([['news_comments.news_id', '=', $id], ['news_comments.reply_id', '=', 0]])->latest()->get();
        $commentsData = [];

        foreach($comments as $comment){

            $tenant = Tenant::find($comment->studio_id);
            $user_id = $comment->user_id;
            $user = $tenant->run(function () use ($tenant, $user_id) {
                $user = User::where('id', $user_id)->first();
                return $user;
            });

            if ($user->nick == ''){
                $name = $user->first_name." ".$user->last_name;
            }else{
                $name = $user->nick;
            }

//            $avatar = global_asset("/storage/" . tenant('studio_slug') . "/avatars/" . $user->avatar);
            $avatar = global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $user->avatar);
            $replies = $this->replies($comment->id);

            $now = Carbon::now();
            $created_at = Carbon::parse($comment->created_at);
            $mins = $created_at->diffInMinutes($now);   // 180
            $time = $this->convertToMinsHours($mins);

            $studio_id = $comment->studio_id;
            $studio_name = $tenant->run(function () use ($tenant, $studio_id) {
                $studio = Tenant::where('id', $studio_id)->first();
                return $studio->studio_name;
            });

            $commentsData['comments'][] = [
                'name' => $name,
                'avatar' => $avatar,
                'comment_id' => $comment->id,
                'news_id' => $id,
                'comment' => $comment->body,
                'date' => $time,
                'replies' => $replies,
                'studio' => $studio_name,
            ];

        }

        return response()->json($commentsData);
    }

    public function storeComment(Request $request)
    {
        $comment = "";

        if (!$request->ajax()) {
            return redirect()->route('welcome');
        }

        $this->validate($request,
            ['body' => 'required', 'reply_id' => 'filled'],
            ['body.required' => 'Por favor, escriba un comentario']
        );

        try {
            $body = $request->input('body');
            $news_id = $request->input('news_id');
            $reply_id = $request->input('reply_id');

            DB::beginTransaction();
                $comment = Comments::create([
                    'body' => $body,
                    'user_id' => auth()->user()->id,
                    'news_id' => $news_id,
                    'studio_id' => tenant('id'),
                    'reply_id' => $reply_id
                ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }

        return response()->json([
            'comment' => $comment
        ]);
    }

    public function getLikes($id)
    {
        /*$likes = Likes::join('users', 'news_likes.user_id', '=', 'users.id')
            ->select('users.first_name')
            ->where('news_likes.news_id', '=', $id)->orderBy('news_likes.created_at')->take(3)->get();*/
        /*$likes_count = Likes::where('news_likes.news_id', '=', $id)->get()->count();*/


        /*$allLikes = Likes::join('users', 'news_likes.user_id', '=', 'users.id')
            ->select('users.first_name', 'users.last_name', 'users.avatar', 'users.nick')
            ->where('news_likes.news_id', '=', $id)->orderBy('news_likes.created_at', 'DESC')->get();*/

        $likes = Likes::where('news_id', $id)->orderBy('news_likes.created_at')->take(3)->get();
        $likes_count = Likes::where('news_id', $id)->count();
        $user_liked = Likes::where([['user_id', auth()->user()->id], ['news_id', '=', $id] , ['studio_id', '=', tenant('id')]])->exists();

        $data = [];
        $first_user = "";

        $tenant = Tenant::find(tenant('id'));
        $All_likes = Likes::where('news_id', $id)->orderBy('news_likes.created_at')->get();
        $AllLikes = $tenant->run(function () use ($tenant, $All_likes) {
            $likes_name = "";
            foreach ($All_likes as $eachlike)
            {
                $user_liked = User::select('first_name', 'last_name', 'nick')->where('id', $eachlike->user_id)->first();
                if ($user_liked->nick !== '')
                {
                    $name = $user_liked->nick;
                } else{
                    $name = $user_liked->first_name." ".$user_liked->last_name;
                }

                $likes_name .= ($likes_name == "")? $name : ", ".$name;
            }
            return $likes_name;
        });

        foreach ($likes as $like){

            $tenant = Tenant::find($like->studio_id);
            $user_id = $like->user_id;
            $user = $tenant->run(function () use ($tenant, $user_id) {
                $user = User::where('id', $user_id)->first();
                return $user;
            });

            if ($user->nick == null)
            {
                $created_by = $user->first_name;
            }
            else{
                $created_by = $user->nick;
            }

            $data[] = [
                'user' => $created_by,
                'avatar' => $user->avatar
            ];

            $first_user .= ($first_user == "")? $created_by : ", ".$created_by;
        }

        return response()->json([
            'likes' => $first_user,
            'count' => ($likes_count - 3),
            'show_count' => $likes_count,
            'allLikes' => $data,
            'likes_tip' => $AllLikes,
            'user_liked' => $user_liked,
            'data' => $data
        ]);
    }

    public function storeLike(Request $request)
    {
        $like = "";
        $msg = "";
//        return Excel::store(new UsersExport, 'statistics/users.xlsx');
        if (!$request->ajax()) {
            return redirect()->route('welcome');
        }

        try {
            $action = $request->input('action');
            $news_id = $request->input('news_id');

            $exist = Likes::where([['user_id', auth()->user()->id], ['news_likes.news_id', '=', $news_id]])->exists();
            if ($exist){
               $msg = "Ya le has dado 'me gusta' a esta noticia";
               $icon = "info";
               $code = 403;
            }else{
                $msg = "Le diste 'me gusta' a esta noticia";
                $icon = "success";
                $code = 200;

                DB::beginTransaction();
                $like = Likes::create([
                    'action' => $action,
                    'news_id' => $news_id,
                    'studio_id' => tenant('id'),
                    'user_id' => auth()->user()->id
                ]);

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollback();
        }

        return response()->json([
            'like' => $like,
            'icon' => $icon,
            'msg' => $msg,
            'code' => $code
        ]);
    }

    public function delete(Request $request, $id)
    {
        $news = News::findOrFail($id);
        if (Storage::disk('public')->exists('GB/news/files/' . $news->file)){
            Storage::disk('public')->delete('GB/news/files/' . $news->file);
        }

        $news->delete();
    }

    public function replies($commentId)
    {
        $comments = Comments::where('reply_id', $commentId)->orderBy('created_at', 'ASC')->get();
        $replies = [];

        foreach ($comments as $comment){

            $tenant = Tenant::find($comment->studio_id);
            $user_id = $comment->user_id;
            $user = $tenant->run(function () use ($tenant, $user_id) {
                $user = User::where('id', $user_id)->first();
                return $user;
            });

            $studio_id = $comment->studio_id;
            $studio_name = $tenant->run(function () use ($tenant, $studio_id) {
                $studio = Tenant::where('id', $studio_id)->first();
                return $studio->studio_name;

            });

            if ($user->nick == ''){
                $name = $user->first_name." ".$user->last_name;
            }else{
                $name = $user->nick;
            }
//            $avatar = global_asset("/storage/" . tenant('studio_slug') . "/avatars/" . $user->avatar);
            $avatar = global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $user->avatar);

            $now = Carbon::now();
            $created_at = Carbon::parse($comment->created_at);
            $mins = $created_at->diffInMinutes($now);   // 180
            $time = $this->convertToMinsHours($mins);

            $replies[] = [
              'name' => $name,
              'avatar' => $avatar,
              'logged_user_id' => auth()->user()->id,
              'comment_id' => $comment->id,
              'comment' => $comment->body,
              'date' => $time,
              'studio' => $studio_name,
            ];
        }

        return collect($replies);
    }


    // share news with studios
    public function share(Request $request)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

        DB::beginTransaction();

        try {
            $role_id = 1;
            $studio_ids = $request->studios;

            foreach ($studio_ids as $studio_id)
            {
                NewsStudio::updateOrCreate([
                    "news_id" => $request->news_id,
                    "studio_id" => $studio_id,
                    "created_by" => 0,
                ]);

                NewsRoles::updateOrCreate([
                    "news_id" => $request->news_id,
                    "role_id" => $role_id,
                    "role_name" => "",
                    "studio_id" => $studio_id,
                ]);

                $users = $this->getUsersFromRol($role_id, $studio_id);

                foreach ($users as $value){
                    NewsUsers::updateOrCreate([
                        "news_id" => $request->news_id,
                        "user_id" => $value->id,
                        "studio_id" => $studio_id,
                    ]);
                }
            }

            DB::commit();

            $msg = "Post compartido con estudios";
        }
        catch (\Exception $e) {
            $msg = $e->getMessage();
            DB::rollback();
        }

        return response()->json([
            'msg' => $msg,
        ]);
    }

    public function getStudios()
    {
        $studios = Tenant::where('id', '!=', 1)->where('should_share', 1)->get();
        return response()->json($studios);
    }


    //script to copy news
    public function execute()
    {
        $min_id = 1;
        $max_id = 10;
        $posts = DB::connection('gbmedia')->table('noticias')->whereBetween('id', [$min_id, $max_id])->get();
        $GB_roles = [
            1 => 1,
            11 => 4,
            12 => 6,
            13 => 9,
            14 => 1,
            15 => 2,
            16 => 10,
            17 => 7,
            18 => 5,
            19 => 14,
            20 => 8,
            21 => 13,
            22 => 11,
            39 => 15,
            41 => 16,
            44 => 3,
            45 => 12,
            46 => 17,
            47 => 18,
            48 => 19,
            49 => 20,
            50 => 21,
            51 => 22,
            52 => 23,
            53 => 24,
            54 => 38,
            55 => 26,
            56 => 27,
            57 => 28,
            58 => 29,
            59 => 30,
            60 => 31,
            61 => 32,
            62 => 33,
            63 => 34,
            64 => 35,
            65 => 36,
            66 => 39,
            67 => 37,
            68 => 40,
        ];

       foreach ($posts as $post)
       {
           
       }


        /*$min_id = 1;
        $max_id = 300;
        $msg = "nothing";
        $news = News::whereBetween('id', [$min_id, $max_id])->get();
       foreach ($news as $item){*/
            /*$roles_news = NewsRoles::where('news_id', $item->id)->get();
            foreach ($roles_news as $role)
            {
                $users = User::where('setting_role_id', $role->role_id)->get();

                foreach ($users as $user){
                    $exists = NewsUsers::where([
                        ['user_id', $user->id],
                        ['news_id', $item->id]
                    ])->exists();
                    if (!$exists){
                        NewsUsers::updateOrCreate([
                            'news_id' => $item->id,
                            'user_id' => $user->id,
                            'studio_id' => 1,
                            'created_at' => Carbon::now()->format('Y-m-d'),
                            'updated_at' => Carbon::now()->format('Y-m-d')
                        ]);
                        $msg = "todo mudado";
                    }else{
                        $msg = "Ya existe";
                    }
            }
        }*/
           /*$comments = DB::connection('gbmedia')->table('noticias_comentarios')->where('noticias_id', $item->id_news)->get();
           foreach ($comments as $comment){
                $comment_date = $comment->fecha_comentario;
                $comment_date = explode(',', $comment_date);
                $date = $comment_date[0];
                $time = $comment_date[1];

                $usuario = $comment->usuario;
                $usuario = explode(" ", $usuario);
                $first_name = $usuario[0];
                $last_name = $usuario[1];

                echo "<br>".$last_name. " ".$first_name;
                $user = User::where([['first_name', $first_name], ['last_name', $last_name]])->first();
                $com = $comment->comentarios_noticias;

                Comments::updateOrCreate([
                     'body' => $com,
                     'news_id' => $item->id,
                     'user_id' => $user->id,
                     'studio_id' => 1,
                     'reply_id' => 0,
                     'old_comment_id' => $comment->id,
                     'created_at' => $date."00:00:00",
                     'updated_at' => Carbon::now(),
                ]);

                $msg = "Todo mudado";
            }*/

            /*$likes = DB::connection('gbmedia')->table('noticias_rating')->where('noticia_id', $item->id_news)->get();
            foreach ($likes as $like)
            {
                $users = User::where('old_user_id', $like->user_id)->get();

               foreach($users as $user)
               {
                   $exists = Likes::where([
                       ['user_id', $user->id],
                       ['news_id', $item->id]
                   ])->exists();

                   if (!$exists){
                       Likes::create([
                           'action' => 'like',
                           'news_id' => $item->id,
                           'user_id' =>  $user->id,
                           'studio_id' => 1
                       ]);
                       $msg = "Todo mudado";
                   }else{
                       $msg = "Ya existe";
                   }
               }
            }*/

           /* $comments = DB::connection('gbmedia')->table('noticias_comentarios')->where('noticias_id', 170)->get();
            foreach ($comments as $comment)
            {
                $replies = DB::connection('gbmedia')->table('noticias_comment_reply')->where('comment_id', $comment->id)->get();
                dd($replies);
                foreach ($replies as $reply)
                {
                    $reply_comments = Comments::where('old_comment_id', $reply->comment_id)->get();

                    foreach($reply_comments as $rc)
                    {
                        $user = User::where('id', $reply->user_id)->first();
                        echo "<br>".$user->id;
                        Comments::updateOrCreate([
                            'body' => $reply->comment_body,
                            'news_id' => $item->id,
                            'user_id' => $user->id,
                            'studio_id' => 1,
                            'reply_id' => $rc->id,
                            'old_comment_id' => $comment->id,
                            'created_at' => $reply->created_at,
                            'updated_at' => Carbon::now(),
                        ]);

                        $msg = "Todo mudado";
                    }
                }
            }*/
      /*  }*/


       /* $comments = DB::connection('gbmedia')->table('noticias_comentarios')->whereBetween('noticias_id', [$min_id, $max_id])->get();
        foreach ($comments as $comment)
        {
            $replies = DB::connection('gbmedia')->table('noticias_comment_reply')->where('comment_id', $comment->id)->get();
            foreach ($replies as $reply)
            {
                $reply_comments = Comments::where('old_comment_id', $reply->comment_id)->get();
                
                foreach($reply_comments as $rc)
                {
                    $user = User::where('old_user_id', $reply->user_id)->first();

//                    dd($user->id);

                    echo "<br>".$user->id;
                    Comments::updateOrCreate([
                        'body' => $reply->comment_body,
                        'news_id' => 251,
                        'user_id' => $user->id,
                        'studio_id' => 1,
                        'reply_id' => $rc->id,
                        'old_comment_id' => $comment->id,
                        'created_at' => $reply->created_at,
                        'updated_at' => Carbon::now(),
                    ]);

                    $msg = "Todo mudado";
                }
            }
        }*/

       /* return response()->json($msg);*/
        /*$categories = DB::connection('gbmedia')->table('wiki_categories')->whereBetween('wiki_c_id', [$min_id, $max_id])->get();

        foreach ($categories as $category)
        {
            WikiCategory::updateOrCreate([
                'name' => $category->category_name,
                'old_cat_id' => $category->wiki_c_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }*/

        /* $min_id = 1;
         $max_id = 10;
         $posts = News::whereBetween('id', [$min_id, $max_id])->get();
         $GB_roles = [
             1 => 1,
             11 => 4,
             12 => 6,
             13 => 9,
             14 => 1,
             15 => 2,
             16 => 10,
             17 => 7,
             18 => 5,
             19 => 14,
             20 => 8,
             21 => 13,
             22 => 11,
             39 => 15,
             41 => 16,
             44 => 3,
             45 => 12,
             46 => 17,
             47 => 18,
             48 => 19,
             49 => 20,
             50 => 21,
             51 => 22,
             52 => 23,
             53 => 24,
             54 => 38,
             55 => 26,
             56 => 27,
             57 => 28,
             58 => 29,
             59 => 30,
             60 => 31,
             61 => 32,
             62 => 33,
             63 => 34,
             64 => 35,
             65 => 36,
             66 => 39,
             67 => 37,
             68 => 40,
         ];*/

        /*foreach ($posts as $post)
        {
            NewsStudio::updateOrCreate([
                'news_id' => $post->id,
                'studio_id' => 1,
                'created_by' => 3,
                'created_at' => Carbon::now()->format('Y-m-d'),
                'updated_at' => Carbon::now()->format('Y-m-d'),
            ]);

             $noticias = DB::connection('gbmedia')->table('noticias_rol')->where('noticias_id', $post->id_news)->get();
             foreach ($noticias as $noticia)
             {
                 $roles = DB::connection('gbmedia')->table('role')->where('id', $noticia->id_rol)->get();
                 foreach ($roles as $role){
                     $r = $GB_roles[$role->id];

                     NewsRoles::updateOrCreate([
                         'news_id' => $post->id,
                         'role_id' => $r,
                         'role_name' => "",
                         'studio_id' => 1,
                         'created_at' => Carbon::now()->format('Y-m-d'),
                         'updated_at' => Carbon::now()->format('Y-m-d'),
                     ]);
                 }
             }

            $roles_news = NewsRoles::where('news_id', $post->id)->get();
            foreach ($roles_news as $role)
            {
                $users = User::where('setting_role_id', $role->role_id)->get();
                dd($users);
            }
        }*/

        $msg = "nothing bitch!";
        dd($msg);
    }
}
