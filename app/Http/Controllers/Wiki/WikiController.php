<?php

namespace App\Http\Controllers\Wiki;

use App\Models\Tenancy\Tenant;
use App\Models\Wiki\WikiCategoryStudios;
use App\User;
use App\Models\Wiki\Wiki;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Wiki\WikiRole;
use App\Models\Wiki\WikiUser;
use App\Models\Wiki\WikiStudios;
use App\Models\Wiki\WikiCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Settings\SettingRole;
use Illuminate\Support\Facades\Storage;

/**
 * @method validate(Request $request, array $array, array $array1)
 */
class WikiController extends Controller
{
    function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        return view('adminModules.wiki.index', compact('user_permission'));
    }

    public function wikiData(Request $request)
    {
        $search = $request->search;
        $filter = $request->filter;
        $data = [];
        $pagination = [];

        if ($search == '')
        {
            $tenant_id = tenant('id');
            $wikis = WikiStudios::join('wiki_users', 'wiki_users.wiki_id', 'wiki_studios.wiki_id')
                ->where([['wiki_studios.studio_id', $tenant_id], ['wiki_users.studio_id', $tenant_id]])
                ->where('wiki_users.user_id', auth()->user()->id)
                ->orderBy('wiki_studios.created_at', 'DESC')
                ->paginate(12);

            $pagination = [
                'total' => $wikis->total(),
                'current_page' => $wikis->currentPage(),
                'per_page' => $wikis->perPage(),
                'last_page' => $wikis->lastPage(),
                'from' => $wikis->firstItem(),
                'to' => $wikis->lastItem()
            ];

            $canShare = false;
            if (tenant('id') === 1){
                $canShare = true;
            }

            foreach ($wikis as $post){

                $roles_count = WikiRole::where([['wiki_id', $post->wiki_id], ['studio_id', tenant('id')]])->count();
                $shares = WikiStudios::where([['wiki_id', $post->wiki_id], ['studio_id', '!=', tenant('id')]])->count();
                $wikis_table = Wiki::find($post->wiki_id);
                $created_by = "";
                $tenant = Tenant::find($post->studio_id);
                $user_id = $post->created_by;
                if ($user_id !== 0){
                    $created_by = $tenant->run(function () use ($tenant, $user_id) {
                        $user = User::where('id', $user_id)->first();
                        return $user->first_name." ".$user->last_name;
                    });
                }

                $wiki_studio_categories = WikiCategoryStudios::where([['wiki_category_id', $post->wiki_category_id],['studio_id', tenant('id')]])->first();
                $category_id = $wiki_studio_categories->wiki_category_id;
                $category_name = $tenant->run(function () use ($tenant, $category_id) {
                    $category = WikiCategory::where('id', $category_id)->first();
                    return $category->name;
                });

                $data[] = [
                    'id' => $wikis_table->id,
                    'title' => $wikis_table->title,
                    'body' => $wikis_table->body,
                    'author' => $created_by,
                    'published' => Carbon::parse($wikis_table->created_at)->format('d M Y'),
                    'roles' => $roles_count,
                    'shares' => $shares,
                    'category' => $category_name,
                    'canShare' => $canShare,
                ];
            }
        }
        else
        {
            $wiki = Wiki::where('wikis.'.$filter, 'like', '%' . $search . '%')->orderBy('wikis.created_at', 'DESC')->get();
            foreach($wiki as $w){
                $tenant_id = tenant('id');
                $wikis = WikiStudios::join('wiki_users', 'wiki_users.wiki_id', 'wiki_studios.wiki_id')
                    ->where([['wiki_studios.studio_id', $tenant_id], ['wiki_users.studio_id', $tenant_id]])
                    ->where('wiki_studios.wiki_id', $w->id)
                    ->where('wiki_users.user_id', auth()->user()->id)
                    ->orderBy('wiki_studios.created_at', 'DESC')
                    ->paginate(12);

                $pagination = [
                    'total' => $wikis->total(),
                    'current_page' => $wikis->currentPage(),
                    'per_page' => $wikis->perPage(),
                    'last_page' => $wikis->lastPage(),
                    'from' => $wikis->firstItem(),
                    'to' => $wikis->lastItem()
                ];

                foreach ($wikis as $post){

                    $roles_count = WikiRole::where([['wiki_id', $post->wiki_id], ['studio_id', tenant('id')]])->count();
                    $shares = WikiStudios::where([['wiki_id', $post->wiki_id], ['studio_id', '!=', tenant('id')]])->count();
                    $wikis_table = Wiki::find($post->wiki_id);
                    $created_by = "";
                    $tenant = Tenant::find($post->studio_id);
                    $user_id = $post->created_by;
                    if ($user_id !== 0){
                        $created_by = $tenant->run(function () use ($tenant, $user_id) {
                            $user = User::where('id', $user_id)->first();
                            return $user->first_name." ".$user->last_name;
                        });
                    }

                    $wiki_studio_categories = WikiCategoryStudios::where([['wiki_category_id', $post->wiki_category_id],['studio_id', tenant('id')]])->first();
                    $category_id = $wiki_studio_categories->wiki_category_id;
                    $category_name = $tenant->run(function () use ($tenant, $category_id) {
                        $category = WikiCategory::where('id', $category_id)->first();
                        return $category->name;
                    });


                    $data[] = [
                        'id' => $wikis_table->id,
                        'title' => $wikis_table->title,
                        'body' => $wikis_table->body,
                        'author' => $created_by,
                        'published' => Carbon::parse($wikis_table->created_at)->format('d M Y'),
                        'roles' => $roles_count,
                        'shares' => $shares,
                        'category' => $category_name,
                    ];
                }
            }
        }

        return response()->json([
            'pagination' => $pagination,
            'posts' => $data
        ]);


        /*if ($request->ajax()) {
            if ($search == '') {
                $posts = Wiki::orderBy('wikis.created_at', 'DESC')->paginate(6);
            } else {
                $posts = Wiki::where('wikis.'.$filter, 'like', '%' . $search . '%')->orderBy('wikis.created_at', 'DESC')->paginate(6);
            }

            $data = [];
            $pagination = [
                'total' => $posts->total(),
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem()
            ];

            foreach ($posts as $post){
                $roles = WikiRole::where('wiki_id', $post->id)->count();
                $shares = WikiStudios::where('wiki_id', $post->id)->count();
                $category = WikiCategory::where('id', $post->wiki_category_id)->first();
                $author = User::where('id', $post->user_id)->first();

                $data[] = [
                    'id' => $post->id,
                    'title' => $post->title,
                    'body' => $post->body,
                    'author' => $author->first_name." ".$author->last_name,
                    'author_id' => $author->id,
                    'published' => Carbon::parse($post->created_at)->format('d M Y'),
                    'roles' => $roles,
                    'shares' => $shares,
                    'category' => $category->name,
                ];
            }

                return response()->json([
                    'pagination' => $pagination,
                    'posts' => $data
                ]);
        } else {
            return redirect()->route('home.dashboard');
        }*/
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');
        $post = "";

        $this->validate($request,
            [
                'title' => 'required|string|max:255',
                'body' => 'required',
                'roles' => 'required',
                'wiki_category_id' => 'sometimes|nullable',
                'category_name' => 'sometimes|nullable',
                'tag' => 'nullable',
            ],
            [
                'title.required' => 'Por favor, escriba un titulo para su post',
                'body.required' => 'Por favor, escriba un contenido para su post',
                'roles.required' => 'Por favor, escoja los roles que veran este post',
                'wiki_category_id.required' => 'Por favor, escoja o cree la categoria a la que pertenece este post',
                'category_name.required' => 'Por favor, escoja o cree la categoria a la que pertenece este post',
            ]
        );

        try {
            DB::beginTransaction();

            $error = false;
            $title = $request->input('title');
            $body = $request->input('body');
            $is_shared = 0;
            $roles = $request->input('roles');
            $tag = $request->input('tag');

            if ($request->has('category_name') && $request->input('wiki_category_id') === null){
               $category_name = $request->input('category_name');
               $category = WikiCategory::create([
                   'name' => $category_name
               ]);
               $wiki_category_id = $category->id;

               $wiki_category_studio = new WikiCategoryStudios();
               $wiki_category_studio->wiki_category_id = $wiki_category_id;
               $wiki_category_studio->studio_id = tenant('id');
               $wiki_category_studio->save();

            }else{
                $wiki_category_id = $request->input('wiki_category_id');
            }

            $post = Wiki::create([
                'title' => $title,
                'body' => $body,
                'is_shared' => $is_shared,
                'wiki_category_id' => $wiki_category_id,
                'tag' => $tag
            ]);

            $wiki_studio = new WikiStudios();
            $wiki_studio->wiki_id = $post->id;
            $wiki_studio->studio_id = tenant('id');
            $wiki_studio->wiki_category_id = $wiki_category_id;
            $wiki_studio->created_by = auth()->user()->id;
            $wiki_studio->save();

            $manager = 3;
            if (!array_search($manager,$roles)){
               array_push($roles, $manager);
            }

            foreach($roles as $role_id)
            {
                $role = new WikiRole();
                $role->wiki_id = $post->id;
                $role->setting_role_id = $role_id;
                $role->studio_id = tenant('id');
                $role->save();

                $users = $this->getUsersFromRol($role_id, tenant('id'));

                foreach ($users as $value){
                    $user = new WikiUser();
                    $user->wiki_id = $post->id;
                    $user->user_id = $value->id;
                    $user->studio_id = tenant('id');
                    $user->save();
                }
            }

            DB::commit();

            if ($post){
                $msg = "El post se creó correctamente!";
                $icon = "success";
                $code = 200;
            }else{
                $msg = "El post no se creó correctamente!";
                $icon = "error";
                $code = 403;
            }

        } catch (\Exception $e) {
            $msg = "Ha ocurrido un error por favor, comunicarse con el admin".$e;
            $icon = "error";
            $code = 500;

            DB::rollback();
        }

        return response()->json([
            'error' => $error,
            'data' => $post,
            'icon' => $icon,
            'msg' => $msg,
            'code' => $code
        ]);
    }

    public function show($id, Request $request)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

        $post = Wiki::findOrFail($id);
        $category = WikiCategory::where('id', $post->wiki_category_id)->first();

        $tenant = Tenant::find(tenant('id'));
        $roles = WikiRole::select('setting_role_id')->where('wiki_id', $id)->where('studio_id', tenant('id'))->get();
        $roles = $roles->toArray();
        $array_roles = array_slice( $roles, 0, 3);
        $roles_name = $tenant->run(function () use ($tenant, $array_roles) {
            $roles_name = "";
            foreach ($array_roles as $role)
            {
                $setting_role = SettingRole::select('name')->where('id', $role['setting_role_id'])->first();
                $roles_name .= ($roles_name == "")? $setting_role->name : ", ".$setting_role->name;
            }
            return $roles_name;
        });

        $all_roles = $tenant->run(function () use ($tenant, $roles) {
            $roles_name = "";
            foreach ($roles as $role)
            {
                $setting_role = SettingRole::select('name')->where('id', $role['setting_role_id'])->first();
                $roles_name .= ($roles_name == "")? $setting_role->name : ", ".$setting_role->name;
            }
            return $roles_name;
        });

        $studio = WikiStudios::where('wiki_id', $id)->first();
        $tenant = Tenant::find($studio->studio_id);
        $user_id = $studio->created_by;
        $created_by = $tenant->run(function () use ($tenant, $user_id) {
            $user = User::where('id', $user_id)->first();
            return $user->first_name." ".$user->last_name;
        });

        $studios = WikiStudios::where('wiki_id', $id)->where('studio_id', '!=', tenant('id'))->get();
        $studios = $studios->toArray();
        $array_studios = array_slice( $studios, 0, 3);
        $studios_name = $tenant->run(function () use ($tenant, $array_studios) {
            $studios_name = "";
            foreach ($array_studios as $studio)
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

        $data['post'] = [
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'category' => $category->name,
            'category_id' => $category->id,
            'roles' => $roles_name,
            'all_roles' => $all_roles,
            'studios' => $studios_name,
            'all_studios' => $all_studios,
            'published' => Carbon::parse($post->created_at)->format('d M Y'),
            'author' => $created_by,
            'is_shared' => $post->is_shared,
            'tags' => $post->tag,
            'roles_count' => count($roles) - 3,
            'shares_count' =>count($studios) - 3,
        ];

        return response()->json(collect($data));
    }

    public function editPost($id, Request $request)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

        $post = Wiki::findOrFail($id);
        $category = WikiCategory::select('id', 'name')->where('id', $post->wiki_category_id)->first();
        $roles = WikiRole::select('setting_role_id as id')->where('wiki_id', $id)->where('studio_id', tenant('id'))->get();
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'is_shared' => $post->is_shared,
            'roles' => $roles,
            'wiki_category_id' => $post->wiki_category_id,
            'tags' => $post->tag,
            'category_name' => $category,
        ];

        return response()->json(collect($data));
    }

    public function updatePost(Request $request, $id)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');
        $updated = "";
        $post = "";
        $this->validate($request,
            [
            'title' => 'required|string|max:255',
            'body' => 'required',
            'roles' => 'required',
            'wiki_category_id' => 'required'
            ],
            [
                'title.required' => 'Por favor, escriba un titulo para su post',
                'body.required' => 'Por favor, escriba un contenido para su post',
                'roles.required' => 'Por favor, escoja los roles que veran este post',
                'wiki_category_id.required' => 'Por favor, escoja o cree la categoria a la que pertenece este post'
            ]
        );

        try {

            DB::beginTransaction();

            $post = Wiki::findOrFail($id);

            $title = $request->input("title");
            $body = $request->input("body");
            $is_shared = 0;
            $wiki_category_id = $request->input("wiki_category_id");
            $tag = $request->input("tag");

            $updated = $post->update([
                'title' => $title,
                'tag' => $tag,
                'body' => $body,
                'is_shared' => $is_shared,
                'wiki_category_id' => $wiki_category_id
            ]);

            DB::table("wiki_studios")->where("wiki_id", "=", $post->id)->delete();
            $wiki_studio = wikiStudios::where([
                ['wiki_id', $id],
                ['studio_id', tenant('id')]
            ])->first();

            $wiki_studio->update([
                "wiki_id" => $id,
                "studio_id" => tenant('id'),
                "wiki_category_id" => $wiki_category_id
            ]);

            DB::table("wiki_roles")->where("wiki_id", "=", $post->id)->delete();
            DB::table("wiki_users")->where("wiki_id", "=", $post->id)->delete();

            $roles = $request->roles;
            $manager = 3;
            if (!array_search($manager,$roles)){
                array_push($roles, $manager);
            }

            foreach($roles as $role_id)
            {
                $role = new wikiRole();
                $role->wiki_id = $post->id;
                $role->setting_role_id = $role_id;
                $role->studio_id = tenant('id');
                $role->save();

                $users = $this->getUsersFromRol($role_id, tenant('id'));

                foreach ($users as $value){
                    $user = new WikiUser();
                    $user->wiki_id = $post->id;
                    $user->user_id = $value->id;
                    $user->studio_id = tenant('id');
                    $user->save();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
        }

        if ($updated){
            $msg = "El post se actualizó correctamente!";
            $icon = "success";
            $code = 200;
        }else{
            $msg = "El post no se actualizó correctamente!";
            $icon = "error";
            $code = 403;
        }

        return response()->json([
            'post' => $post,
            'icon' => $icon,
            'msg' => $msg,
            'code' => $code
        ]);
    }

    public function deletePost(Request $request, $id)
    {
        if ($request->ajax()){
            $wiki = Wiki::findOrFail($id);
            $wiki->delete();
        }else{
            return redirect()->route('home.dashboard');
        }
    }

    public function uploadImages(Request $request)
    {
        $folder = "GB/wiki/";
        if (!Storage::exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $imgpath = Storage::disk('public')->put($folder, request()->file('file'));
        return response()->json([
            'folder' => $folder,
            'location' => 'https://laravel.gbmediagroup.com/storage/app/public/'.$imgpath
        ]);
    }

    public function deleteSharePost(Request $request, $id)
    {
        if ($request->ajax()){
            $wikiStudios = WikiStudios::where('wiki_id', '=', $id);
           foreach ($wikiStudios as $wikiStudio)
           {
               $wikiStudio->delete();
           }
        }else{
            return redirect()->route('home.dashboard');
        }
    }

    public function updateShareStatus(Request $request, $id)
    {
       if ($request->ajax()){
           $is_share = 1;
           $error = false;
           $post = Wiki::findOrFail($id);

           $post->update(['is_shared' => $is_share]);

           return response()->json([
               'error' => $error,
               'data' => $post
           ]);
       }else{
           return redirect()->route('home.dashboard');
       }
    }

    public function removeShareStatus(Request $request, $id)
    {
        if ($request->ajax()){
            $is_share = 0;
            $error = false;
            $post = Wiki::findOrFail($id);

            $post->update(['is_shared' => $is_share]);

            return response()->json([
                'error' => $error,
                'data' => $post
            ]);
        }else{
            return redirect()->route('home.dashboard');
        }
    }

    //categories
    public function storeCategory(Request $request)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

        $this->validate($request,
            [ 'name' => 'required|string|max:100' ],
            [
                'name.required' => '¡Por favor, escriba un nombre para la categoría!',
            ]
        );

        $name = htmlentities($request->name);
        $name = strtolower($name);
        $category = "";
        
        $exists = WikiCategory::where('name', '=', $name)->exists();
        if ($exists){
            $msg = "Categoria ya existe!";
            $icon = "warning";
            $code = 403;
        }else{
            $category = WikiCategory::create([
                'name' => $name
            ]);

            $wiki_category_studio = new WikiCategoryStudios();
            $wiki_category_studio->wiki_category_id = $category->id;
            $wiki_category_studio->studio_id = tenant('id');
            $wiki_category_studio->save();

            if ($category){
                $msg = "Categoria creada exitosamente!";
                $icon = "success";
                $code = 200;
            }else{
                $msg = "Categoria no fue creada exitosamente!";
                $icon = "error";
                $code = 422;
            }
        }

        return response()->json([
            'data' => $category,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ]);
    }

    public function editCategory(Request $request, $id)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

       $category = WikiCategory::findOrFail($id);
       $data['category'] = [
           'id' => $category->id,
           'name' => $category->name
       ];

       return response()->json($data);
    }

    public function updateCategory(Request $request)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

        $this->validate($request,
            [ 'name' => 'required|unique:wiki_categories|string|max:100' ],
            [
                'name.required' => '¡Por favor, escriba un nombre para la categoría!',
                'name.unique' => '¡La categoria ya existe!'
            ]
        );

        $category = WikiCategory::findOrFail($request->id);
        $name = $request->name;
        $name = strtolower($name);

        $exists = WikiCategory::where('name', '=', $name)->exists();
        if ($exists){
            $msg = "Categoria ya existe!";
            $icon = "warning";
            $code = 403;
        }else{
            $updated = $category->update(['name' => $name]);

            if ($updated){
                $msg = "Categoria actualizada exitosamente!";
                $icon = "success";
                $code = 200;
            }else{
                $msg = "Categoria no fue actualizada!";
                $icon = "error";
                $code = 422;
            }
        }

        return response()->json([
            'data' => $category,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ]);
    }

    public function categoryData(Request $request)
    {
        $categories = WikiCategoryStudios::join('wiki_categories', 'wiki_categories.id', 'wiki_categories_studios.wiki_category_id')
            ->orderBy('wiki_categories.name', 'ASC')->get();
        return $request->ajax() ? ['categories' => $categories] : redirect()->route('home.dashboard');
    }

    public function deleteCategory(Request $request, $id)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

        $category = WikiCategory::findOrFail($id);
        $deleted = $category->delete();

        if ($deleted){
            $msg = "El post se actualizó correctamente!";
            $icon = "success";
            $code = 200;
        }else{
            $msg = "El post no se actualizó correctamente!";
            $icon = "error";
            $code = 403;
        }

        return response()->json([
            'data' => $deleted,
            'icon' => $icon,
            'msg' => $msg,
            'code' => $code
        ]);
    }

    public function categoryPosts(Request $request)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

        $categories = WikiCategory::with("posts")
            ->join('wiki_categories_studios', 'wiki_categories_studios.wiki_category_id', 'wiki_categories.id')
            ->orderBy('name', 'ASC')->get();

        $categories = WikiCategory::all();

        return $request->ajax() ? ['categories' => $categories] : redirect()->route('home.dashboard');
    }

    /*public function categoryWithPosts(Request $request)
    {
            if (!$request->ajax()) return redirect()->route('home.dashboard');

            $categories = WikiCategory::join('wiki_categories_studios', 'wiki_categories_studios.wiki_category_id', 'wiki_categories.id')->get();
            $data = [];

           foreach ($categories as $i => $category){
               $posts = Wiki::select('id', 'title')->where('wiki_category_id', $category->id)->get();

               foreach ($posts as $k => $post){
                   $data['catposts'][$i] = [
                       'category' => $category->name,
                       'title' => $posts
                   ];
               }
           }

           return response()->json($data);
    }*/

    public function categoryWithPosts(Request $request)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

        $categories = WikiCategory::select('wiki_categories.id', 'wiki_categories.name')->orderBy('name', 'ASC')->get();
        $userposts = WikiUser::where('user_id', auth()->user()->id)->where('studio_id', tenant('id'))->get();
        $data = [];

        foreach ($categories as $i => $category){
            $posts = Wiki::select('wikis.id', 'wikis.title', 'wikis.wiki_category_id')
                ->where('wikis.wiki_category_id', $category->id)
                ->get();

            $my_posts = [];
            foreach ($posts as $k => $post){
                $exists = WikiUser::where('wiki_id', $post->id)
                    ->where('user_id', auth()->user()->id)
                    ->where('studio_id', tenant('id'))
                    ->first();

                if (!is_null($exists)) {
                    $my_posts[] = [
                        'id' => $post->id,
                        'title' => $post->title,
                    ];
                    $data['catposts'][$category->id] = [
                        'category' => $category->name,
                        'title' => $my_posts
                    ];
                }
            }
        }

        return response()->json($data);
    }

    // sharing
    public function share(Request $request)
    {
        if (!$request->ajax()) return redirect()->route('home.dashboard');

        DB::beginTransaction();

        try {
            $role_id = 1;
            $studio_ids = $request->studios;

            foreach ($studio_ids as $studio_id)
            {

                $exists = WikiStudios::where('studio_id', $studio_id)->where('wiki_id', $request->id)->exists();

                if (!$exists){
                    WikiStudios::updateOrCreate([
                        "wiki_id" => $request->id,
                        "studio_id" => $studio_id,
                        "wiki_category_id" => $request->wiki_category_id,
                        "created_by" => 0,
                    ]);

                    WikiRole::updateOrCreate([
                        "wiki_id" => $request->id,
                        "setting_role_id" => $role_id,
                        "studio_id" => $studio_id,
                    ]);

                    WikiCategoryStudios::updateOrCreate([
                        "wiki_category_id" => $request->wiki_category_id,
                        "studio_id" => $studio_id,
                    ]);

                    $users = $this->getUsersFromRol($role_id, $studio_id);

                    foreach ($users as $value){
                        WikiUser::updateOrCreate([
                            "wiki_id" => $request->id,
                            "user_id" => $value->id,
                            "studio_id" => $studio_id,
                        ]);
                    }
                }
            }

            DB::commit();

            $msg = "Post compartido con estudios";
        }
        catch (\Exception $e) {
            $msg = $e->getPrevious()->getMessage();
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

    public function getUsersFromRol($role_id, $tenant_id)
    {
        $tenant = Tenant::find($tenant_id);
        $users = $tenant->run(function () use ($tenant, $role_id) {
            $users = User::where('setting_role_id', $role_id)->get();
            return $users;
        });

        return $users;
    }

    public function execute()
    {
        $min_id = 301;
        $max_id = 1100;
        $posts = DB::connection('gbmedia')->table('wiki')->whereBetween('wiki_id', [$min_id, $max_id])->get();
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
        $msg = "nothing bitch!";
       /* foreach ($posts as $post)
        {
             $category = DB::connection('gbmedia')->table('wiki_category_post')->where('wiki_post_id', $post->wiki_id)->first();
             $new_cat = WikiCategory::where('old_cat_id', $category->wiki_category_id)->first();
             if ($new_cat == null){
                 continue;
             }
             
             $wiki = Wiki::updateOrCreate([
                 'old_wiki_id' => $post->wiki_id,
                 'title' => utf8_decode($post->wiki_title),
                 'tag' => $post->wiki_tags,
                 'body' => $post->wiki_content,
                 'wiki_category_id' => $new_cat->id,
                 'is_shared' => $post->auto_share === NULL ? 0 : $post->auto_share,
                 'status' => $post->status,
                 'created_at' => $post->published_on,
                 'updated_at' => $post->updated_on,
             ]);

             $exists = WikiStudios::where([
                 ['studio_id', 1],
                 ['wiki_id', $wiki->id],
                 ['wiki_category_id', $new_cat->id],
             ])->exists();

             
            $is_cat = WikiCategoryStudios::where([
                ['studio_id', 1],
                ['wiki_category_id', $wiki->wiki_category_id],
            ])->exists();

            if (!$is_cat){
                WikiCategoryStudios::updateOrCreate([
                    'wiki_category_id' => $wiki->wiki_category_id,
                    'studio_id' => 1
                ]);
            }

             if (!$exists){
                 $user = User::where('old_user_id', $post->published_by)->first();
                 WikiStudios::updateOrCreate([
                     'studio_id' => 1,
                     'wiki_id' => $wiki->id,
                     'wiki_category_id' => $new_cat->id,
                     'created_by' => $user->id,
                     'created_at' => Carbon::now(),
                     'updated_at' => Carbon::now(),
                 ]);
                 $msg = "Done";
             }else{
                 $msg = "exists";
             }

            $roles = DB::connection('gbmedia')->table('wiki_rol_post')->where('wiki_post_id', $post->wiki_id)->get();
            foreach ($roles as $role)
            {
                $wikis = Wiki::all();
                $r = $GB_roles[$role->role_id];
                foreach ($wikis as $wiki){
                    $exists = WikiRole::where([
                        ['studio_id', 1],
                        ['wiki_id', $wiki->id],
                        ['setting_role_id', $r],
                    ])->exists();
                    if (!$exists){
                        WikiRole::updateOrCreate([
                            'setting_role_id' => $r,
                            'wiki_id' => $wiki->id,
                            'studio_id' => 1,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }
            }

        }*/

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

        /*$wikis = Wiki::whereBetween('id', [$min_id, $max_id])->get();
        foreach ($wikis as $wiki)
        {
            $roles = DB::connection('gbmedia')->table('wiki_rol_post')->where('wiki_post_id', $wiki->old_wiki_id)->get();
           foreach ($roles as $rol)
           {
               $r = $GB_roles[$rol->role_id];
               $exists = WikiRole::where([
                   ['studio_id', 1],
                   ['wiki_id', $wiki->id],
                   ['setting_role_id', $r],
               ])->exists();
               if (!$exists){
                   WikiRole::updateOrCreate([
                       'setting_role_id' => $r,
                       'wiki_id' => $wiki->id,
                       'studio_id' => 1,
                       'created_at' => '2020-12-31 11:07:12',
                       'updated_at' => '2020-12-31 11:07:12',
                   ]);
                   $msg = "done";
               }
           }
        }*/

       $roles = WikiRole::whereBetween('wiki_id', [$min_id, $max_id])->get();
       foreach ($roles as $role)
        {
            $users = User::where('setting_role_id', $role->setting_role_id)->get();

            foreach ($users as $user)
            {
                $exists = WikiUser::where([
                    ['studio_id', 1],
                    ['wiki_id', $role->wiki_id],
                    ['user_id', $user->id],
                ])->exists();

                if (!$exists){
                    WikiUser::updateOrCreate([
                         'user_id' => $user->id,
                         'wiki_id' => $role->wiki_id,
                         'studio_id' => 1,
                         'status' => 1,
                    ]);
                    $msg = "Done bitch";
                }else{
                    $msg = "exists bitch";
                }
            }

            /*$studio_cat = Wiki::where('id', $role->wiki_id)->first();

            $is_cat = WikiCategoryStudios::where([
                ['studio_id', 1],
                ['wiki_category_id', $studio_cat->wiki_category_id],
            ])->exists();

            if (!$is_cat){
                WikiCategoryStudios::updateOrCreate([
                    'wiki_category_id' => $studio_cat->wiki_category_id,
                    'studio_id' => 1
                ]);
            }*/
            
            $is_user = WikiUser::where([
                ['studio_id', 1],
                ['wiki_id', $role->wiki_id],
                ['user_id', 677],
            ])->exists();

            if (!$is_user){
                WikiUser::updateOrCreate([
                    'user_id' => 677,
                    'wiki_id' => $role->wiki_id,
                    'studio_id' => 1,
                    'status' => 1,
                ]);
            }else{
                $msg = "exists bitch";
            }
        }

        /*$wikis = WikiRole::all();
        foreach ($wikis as $wiki){
            WikiStudios::updateOrCreate([
                'studio_id' => 1,
                'wiki_id' => $wiki->id,
                'wiki_category_id' => $wiki->wiki_category_id,
                'created_by' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $msg = "Created";
        }*/
        return response()->json($msg);
    }

    public function execute2()
    {
        $min_id = 201;
        $max_id = 300;
        $msg = "nothing";
        
       $wikis = Wiki::whereBetween('id', [$min_id, $max_id])->get();
       foreach ($wikis as $wiki){
           $roles = WikiRole::where('wiki_id', $wiki->id)->get();
           foreach($roles as $role){
               $users = User::where('setting_role_id', $role->setting_role_id)->get();
               foreach ($users as $user){
                   $exists = WikiUser::where('wiki_id', $wiki->id)->where('user_id', $user->id)->where('studio_id', 1)->exists();
                   if (!$exists){
                       WikiUser::updateOrCreate([
                           'user_id' => $user->id,
                           'wiki_id' => $wiki->id,
                           'studio_id' => 1,
                           'status' => 1,
                           'created_at' => '2020-12-31 11:07:12',
                           'updated_at' => '2020-12-31 11:07:12'
                       ]);
                   }
               }
           }

           /*WikiUser::updateOrCreate([
               'user_id' => 677,
               'wiki_id' => $wiki->id,
               'studio_id' => 1,
               'status' => 1,
               'created_at' => '2020-12-31 11:07:12',
               'updated_at' => '2020-12-31 11:07:12'
           ]);*/

           $msg = "Finished";
       }


       return response()->json($msg);
    }
}
