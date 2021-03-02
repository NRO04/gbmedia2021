<?php

namespace App\Http\Controllers\Training;

use App\Models\Studios\Studios;
use App\Models\Tenancy\Tenant;
use App\Models\Training\Answer;
use App\Models\Training\TrainingStudios;
use App\Models\Training\TrainingUsers;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Training\Question;
use App\Models\Training\Training;
use App\Models\Training\Completed;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Settings\SettingRole;
use Illuminate\Support\Facades\Auth;
use App\Models\Training\Trainingfile;
use Intervention\Image\Facades\Image;
use App\Models\Training\Trainingroles;
use Illuminate\Support\Facades\Storage;
use MongoDB\Driver\Exception\Exception;

class TrainingController extends Controller
{
    public function index()
    {
        return view('adminModules.training.index');
    }

    public function getTrainings(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $tenant_id = tenant('id');
        $data = [];
        $pagination = [];
        $canShare = false;
        if (tenant('id') === 1){
            $canShare = true;
        }

        if ($search == ''){
            $trainings = TrainingStudios::join('training_users', 'training_users.training_id', 'training_studios.training_id')
                ->where([['training_studios.studio_id', $tenant_id], ['training_users.studio_id', $tenant_id]])
                ->where('training_users.user_id', auth()->user()->id)
                ->paginate(12);
            foreach($trainings as $training){
                $trainings_table = Training::where('id', $training->training_id)->orderBy('trainings.created_at', 'DESC')->first();

                $data[] = [
                    'id' => $trainings_table->id,
                    'title' => $trainings_table->title,
                    'description' => $trainings_table->description,
                    'image_url' => $trainings_table->image_url,
                    'canShare' => $canShare
                ];
            }
            $pagination = [
                'total' => $trainings->total(),
                'current_page' => $trainings->currentPage(),
                'per_page' => $trainings->perPage(),
                'last_page' => $trainings->lastPage(),
                'from' => $trainings->firstItem(),
                'to' => $trainings->lastItem()
            ];
        }
        else{
            $ts = Training::where('trainings.'.$filter, 'like', '%' . $search . '%')->orderBy('trainings.created_at', 'DESC')->paginate(12);
            foreach($ts as $t){
                $trainings = TrainingStudios::join('training_users', 'training_users.training_id', 'training_studios.training_id')
                    ->where([['training_studios.studio_id', $tenant_id], ['training_users.studio_id', $tenant_id]])
                    ->where('training_studios.training_id', $t->id)
                    ->where('training_users.user_id', auth()->user()->id)
                    ->orderBy('training_studios.id', 'DESC')
                    ->paginate(12);
                foreach($trainings as $training){
                    $trainings_table = Training::where('id', $training->training_id)->orderBy('trainings.created_at', 'DESC')->first();

                    $data[] = [
                        'id' => $trainings_table->id,
                        'title' => $trainings_table->title,
                        'description' => $trainings_table->description,
                        'image_url' => $trainings_table->image_url,
                        'canShare' => $canShare
                    ];
                }
                $pagination = [
                    'total' => $trainings->total(),
                    'current_page' => $trainings->currentPage(),
                    'per_page' => $trainings->perPage(),
                    'last_page' => $trainings->lastPage(),
                    'from' => $trainings->firstItem(),
                    'to' => $trainings->lastItem()
                ];
            }

        }


        return response()->json([
            'pagination' => $pagination,
            'trainings' => $data
        ]);

        /*if ($request->ajax()){
            if ($search == "") {
                $trainings = Training::join('training_files', 'training_files.training_id', '=', 'trainings.id')
                            ->select('trainings.id','trainings.title', 'trainings.description', 'trainings.cover', 'trainings.created_at')
                            ->orderBy('trainings.created_at', 'DESC')
                            ->paginate(15);

            }else{
                $trainings = Training::join('training_files', 'training_files.training_id', '=', 'trainings.id')
                            ->select('trainings.id','trainings.title', 'trainings.description', 'trainings.cover')
                            ->where('trainings.'.$filter, 'like', '%' . $search . '%')
                            ->orderBy('trainings.created_at', 'DESC')
                            ->paginate(15);
            }

            return [
                'pagination' => [
                    'total' => $trainings->total(),
                    'current_page' => $trainings->currentPage(),
                    'per_page' => $trainings->perPage(),
                    'last_page' => $trainings->lastPage(),
                    'from' => $trainings->firstItem(),
                    'to' => $trainings->lastItem()],
                "trainings" => $trainings,
            ];
        }else{
            return redirect()->route('dashboard');
        }*/
    }

    public function store(Request $request)
    {
        $error = false;
        $training = "";
        $msg = "";
        $icon = "";
        $code = "";
        $error = "";

        $this->validate($request, [
            'title' => 'required|string|max:100',
            'description' => 'required',
            'cover' => 'required|mimes:jpeg,png,gif,jpg|max:2048',
            'role_ids' => 'required',
            'questions.question' => 'sometimes|required|max:100',
            'questions.options' => 'sometimes|required|array|min:4',
            'questions.correctAnswer' => 'sometimes|required|numeric'],
            [
                'title.required' => 'Por favor, escriba un titulo',
                'description.required' => 'Por favor, escriba un contenido',
                'role_ids.required' => 'Por favor, escoja los roles que veran este capacitacion',
                'cover.required' => 'Por favor, cargue un cover',
                'cover.mimes' => 'Por favor, cargue una imagen con formato .jpg, .png, .gif, .svg'
            ]);

        if ($request->ajax()) {
            try {
                DB::beginTransaction();

                $title = $request->input("title");
                $description = $request->input("description");
                $is_shared = $request->input("is_shared");
                $want_qustions = $request->input("want_questions");
                $has_test = 0;
                
                if ($want_qustions){
                    $has_test = 1;
                }
                
                $currentDate = Carbon::now()->toDateString();
                $currentDate = str_replace("-", "", $currentDate);

                if ($is_shared){
                    $is_shared = 1;
                }else{
                    $is_shared = 0;
                }

                $cover = $request->file("cover");
                $slug = Str::slug($title);
                $slug = str_replace("-", "", $slug);
                $path = tenant('id')."/trainings/images/";

                if(!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->makeDirectory($path);
                }

                $filename = $slug.$currentDate.uniqid().".".$cover->getClientOriginalExtension();
                $resizedImage = Image::make($cover)->resize(1500, 1000)->stream();
                Storage::disk('public')->put(tenant('id')."/trainings/images/".$filename, $resizedImage);

                $training = Training::create([
                    'title' => $title,
                    'description' => $description,
                    'user_id' => auth()->user()->id,
                    'cover' => $filename,
                    'image_url' => $path.$filename,
                    'is_shared' => $is_shared,
                    'has_test' => $has_test,
                ]);

                $training_studio = new TrainingStudios();
                $training_studio->training_id = $training->id;
                $training_studio->studio_id = tenant('id');
                $training_studio->created_by = auth()->user()->id;
                $training_studio->save();

                $role_ids = json_decode($request->input("role_ids"), true);
                foreach($role_ids as $role_id){
                    $roles = SettingRole::select('name')->where('id', '=', $role_id)->get();
                    foreach ($roles as $role){
                        Trainingroles::create([
                            'role_name' => $role->name,
                            'training_id' => $training->id,
                            'setting_role_id' => $role_id,
                            'studio_id' => tenant('id'),
                        ]);

                        $users = $this->getUsersFromRol($role_id, tenant('id'));

                        foreach ($users as $value){
                            $user = new TrainingUsers();
                            $user->training_id = $training->id;
                            $user->user_id = $value->id;
                            $user->studio_id = tenant('id');
                            $user->save();
                        }
                    }
                }

               if ($request->hasFile('video')){
                   $video = $request->file("video");
                   $slug = Str::slug($title);
                   $slug = str_replace("-", "", $slug);
                   $path = "GB/trainings/videos/";

                   if(!Storage::disk('public')->exists($path)) {
                       Storage::disk('public')->makeDirectory($path);
                   }

                   $videoname = $slug.$currentDate.uniqid().".".$video->getClientOriginalExtension();
                   Storage::disk('public')->put("GB/trainings/videos/".$videoname, file_get_contents($video));

                   Trainingfile::create([
                       'training_id' => $training->id,
                       'video' => $videoname,
                       'video_url' => $path.$videoname
                   ]);
               }

                if ($want_qustions){
                    $questions = json_decode($request->input("questions"),  true);
                    foreach ($questions as $key => $question){
                        $quest = (!empty($question['question']) ? $question['question'] : NULL);
                        if (!is_null($question)){
                            $q = Question::create([
                                'training_id' => $training->id,
                                'question_title' => $quest
                            ]);

                            $index = 1;
                            foreach ($question['options'] as $choice) {
                                if ($index == $question['correctAnswer']){
                                    $rightChoice = 1;
                                }else{
                                    $rightChoice = 0;
                                }
                                $option = (isset($choice) ? $choice : NULL);
                                if (!is_null($option)){
                                    Answer::create([
                                        'training_id' => $training->id,
                                        'training_question_id' => $q->id,
                                        "option_title" => $option,
                                        "correct_answer" => $rightChoice
                                    ]);
                                }
                                $index++;
                            }
                        }
                    }
                }

                DB::commit();

                $msg = "¡Capacitacion creada exitosamente!";
                $code = 200;
                $icon = "success";

            } catch (Exception $e) {
                $msg = "Ha ocurrido un error comuniquese con el admin". $e->getMessage();
                $code = 500;
                $icon = "error";
                DB::rollback();
            }
        } else {
            return redirect()->route('dashboard');
        }

        return response()->json([
            'data' => $training,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code,
            'error' => $error
        ]);
    }

    public function show(Training $training)
    {
        $training = Training::join('training_files', 'training_files.training_id', '=', 'trainings.id')
                    ->select('trainings.id', 'trainings.title', 'trainings.description', 'trainings.cover', 'trainings.has_test', 'trainings.created_at', 'training_files.video')
                    ->where('trainings.id', $training->id)->first();

        $completed = false;
        $test_completed = false;
        $completed_date = NULL;
        
        $complete =  Completed::where([
            ['training_id', $training->id],
            ['user_id', auth()->user()->id],
            ['studio_id', tenant('id')]
        ])->first();

        if ($complete){
            $completed = true;
            $completed_date = $complete->date_completed;
        }

        if ($training->has_test && $complete->test_completed === 1){
            $test_completed = true;
        }
        
        return response()->json([
            'training' => $training,
            'completed' => $completed,
            'completed_date' => $completed_date,
            'test_completed' => $test_completed,
        ]);
    }

    public function update(Request $request, Training $training)
    {
        $this->validate($request, [
            'title' => 'required|string|max:100',
            'description' => 'required',
            'cover' => 'required|mimes:jpeg,png,gif,jpg|max:2048',
            'role_ids' => 'required'],
            [
                'title.required' => 'Por favor, escriba un titulo',
                'description.required' => 'Por favor, escriba un contenido',
                'role_ids.required' => 'Por favor, escoja los roles que veran este capacitacion',
                'cover.required' => 'Por favor, cargue un cover',
                'cover.mimes' => 'Por favor, cargue una imagen con formato .jpg, .png, .gif, .svg',
            ]);

        if ($request->ajax()) {
            try {
                DB::beginTransaction();

                $edit = Training::findOrFail($training->id);

                $title = $request->input('title');
                $description = $request->input('description');
                $is_shared = $request->input('is_shared');
                $path = tenant('id')."/trainings/images/";

                if ($request->hasFile('cover')) {
                    $cover = $request->input('cover');
                    $slug = Str::slug($title);
                    $currentDate = Carbon::now()->toDateString();
                    $slug = str_replace("-", "", $slug);

                    if (!Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->makeDirectory($path);
                    }

                    // delete old photo
                    if (Storage::disk('public')->exists(tenant('id').'/trainings/images/' . $edit->cover)) {
                        Storage::disk('public')->delete(tenant('id').'/trainings/images/' . $edit->cover);
                    }

                    $filename = $slug.$currentDate.uniqid().".".$cover->getClientOriginalExtension();
                    $resizedImage = Image::make($cover)->resize(1500, 1000)->stream();
                    Storage::disk('public')->put(tenant('id')."/trainings/images/".$filename, $resizedImage);

                    if ($request->has('is_shared')){
                        $is_shared = 1;
                    }else{
                        $is_shared = $edit->is_shared;
                    }

                } else {

                    $filename = $edit->cover;
                }

                $edit->update([
                    'title' => $title,
                    'description' => $description,
                    'user_id' => $edit->user_id,
                    'cover' => $filename,
                    'image_url' => $path.$filename,
                    'is_shared' => $is_shared
                ]);

                DB::table("training_roles")->where("training_id", "=", $edit->id)->delete();

                $role_ids = json_decode($request->input("role_ids"), true);
                foreach($role_ids as $role_id){
                    $roles = SettingRole::select('name')->where('id', '=', $role_id)->get();
                    foreach ($roles as $role){
                        Trainingroles::create([
                            'role_name' => $role->name,
                            'training_id' => $training->id,
                            'setting_role_id' => $role_id,
                            'studio_id' => tenant('id'),
                        ]);

                        $users = $this->getUsersFromRol($role_id, tenant('id'));

                        foreach ($users as $value){
                            $user = new TrainingUsers();
                            $user->training_id = $training->id;
                            $user->user_id = $value->id;
                            $user->studio_id = tenant('id');
                            $user->save();
                        }
                    }
                }
                
                $video = $request->file("video");
                if ($request->hasFile('video')){
                    $slug = Str::slug($title);
                    $slug = str_replace("-", "", $slug);
                    $path = tenant('id')."/trainings/videos/";
                    $currentDate = Carbon::now()->toDateString();

                    $editVideo = Trainingfile::findOrFail($training->id);

                    if(!Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->makeDirectory($path);
                    }

                    // delete old photo
                    if (Storage::disk('public')->exists(tenant('id').'/trainings/videos/' . $edit->video)) {
                        Storage::disk('public')->delete(tenant('id').'/trainings/videos/' . $edit->video);
                    }

                    $videoname = $slug.$currentDate.uniqid().".".$video->getClientOriginalExtension();
                    Storage::disk('public')->put(tenant('id')."/trainings/videos/".$videoname, file_get_contents($video));

                    $editVideo->update([
                        'video' => $videoname,
                        'video_url' => $path.$videoname
                    ]);
                }

                $want_questions = $request->input('want_questions');
                if ($want_questions){
                    DB::table("training_questions")->where("training_id", "=", $edit->id)->delete();
                    DB::table("training_options")->where("training_id", "=", $edit->id)->delete();

                    $questions = $request->input("questions");
                    foreach ($questions as $key => $question){
                        $quest = (!empty($question['question_title']) ? $question['question_title'] : NULL);
                        if (!is_null($question)){
                            $q = Question::create([
                                'training_id' => $training->id,
                                'question_title' => $quest
                            ]);

                            $index = 1;
                            foreach ($question['answers'] as $choice) {
                                if ($index == $choice['correctAnswer']){
                                    $rightChoice = 1;
                                }else{
                                    $rightChoice = 0;
                                }
                                $option = (isset($choice['option_title']) ? $choice['option_title'] : NULL);
                                if (!is_null($option)){
                                    Answer::create([
                                        'training_id' => $training->id,
                                        'training_question_id' => $q->id,
                                        "option_title" => $option,
                                        "correct_answer" => $rightChoice
                                    ]);
                                }
                                $index++;
                            }
                        }
                    }
                }

                DB::commit();

                $msg = "¡Capacitacion actualizada exitosamente!";
                $code = 200;
                $icon = "success";

            } catch (Exception $e) {
                $msg = "Ha ocurrido un error comuniquese con el admin". $e->getMessage();
                $code = 500;
                $icon = "error";

                DB::rollback();
            }
        }
        else {
            return redirect()->route('dashboard');
        }

        return response()->json([
            'data' => $training,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code,
        ]);

    }

    public function finish(Request $request, Training $training)
    {
        if ($request->ajax()) {

            $date_completed = Carbon::now();

            if(!Completed::where('training_id',$training->id)->exists()){

                Completed::create([
                    'training_id' => $training->id,
                    'user_id' => Auth::user()->id,
                    'studio_id' => tenant('id'),
                    'date_completed' => $date_completed
                ]);

                return response()->json([
                    'msg' => 'Capacitación completada exitosamente!',
                    'icon' => 'success'
                ], 200);

            }else{
                return response()->json([
                    'msg' => 'Ya has terminado esta capacitación!',
                    'icon' => 'success'
                ]);
            }

        }else{
            return redirect()->route('dashboard');
        }
    }

    public function trainingCompleted(Training  $training)
    {
        $completed = Completed::join('users', 'training_completed.user_id', '=', 'users.id')
                    ->where('training_id', '=', $training->id )
                    ->select('training_completed.id', 'training_completed.user_id', 'training_completed.date_completed', 'training_completed.test_completed', 'users.first_name', 'users.last_name', 'users.avatar')
                    ->get();

        return response()->json(['trainingComplete' => $completed]);
    }

    public function userCompleted(Training $training)
    {
        $training_id = $training->id;
        $user_id = Auth::user()->id;

        $completed = Completed::join('users', 'training_completed.user_id', '=', 'users.id')->where([
            ['user_id', '=', $user_id],
            ['training_id', '=', $training_id ]
        ])->select('training_completed.id', 'training_completed.training_id', 'training_completed.user_id', 'training_completed.date_completed', 'users.first_name', 'users.last_name')->first();
        
        return response()->json(['userComplete' => $completed]);

    }

    public function training(Training $training)
    {
        $training = Training::find($training->id);
        return view('adminModules.training.show', compact('training'));
    }

    public function questionnaire(Training $training)
    {
        $questionnaire = Question::inRandomOrder()->with('answers')->where('training_id', $training->id)->take(5)->get();
        return response()->json(['questionnaire' => $questionnaire]);
    }

    public function editQuestions(Training $training)
    {
        $questionnaire = Question::with('answers')->where('training_id', $training->id)->get();
        return response()->json(['questionnaire' => $questionnaire]);
    }

    public function editRoles(Training $training)
    {
        $roles = Trainingroles::where('training_id', $training->id)->get();
        return response()->json(['roles' => $roles]);
    }

    public function finishTest(Training $training, Request $request)
    {
        if ($request->ajax()){
            try {
                DB::beginTransaction();

                $questionIds = $request->input('questions');
                $answerIds = $request->input('answers');
                $passing_score = 0.8;
                $correct_answers = 0;

                foreach ($questionIds as $questionId){
                    foreach ($answerIds as $answerId){
                        $check = Answer::where([
                            ['training_id', '=', $training->id],
                            ['training_question_id', '=', $questionId],
                            ['id', '=', $answerId],
                            ['correct_answer', '=', '1']
                        ])->exists();

                        if ($check){
                            $correct_answers++;
                        }
                    }
                }

                $total_questions = 5;
                $user_score = $correct_answers / $total_questions;
                $user_score = round($user_score, 2);

                if ($user_score >= $passing_score){
                    $test_completed = 1;
                    $training = Completed::where([
                        ['user_id', '=', auth()->user()->id],
                        ['training_id', '=', $training->id],
                        ['studio_id', '=', \tenant('id')]
                    ])->update(['test_completed' => $test_completed]);
                }

                DB::commit();

                return response()->json([
                    'msg' => 'Prueba terminada',
                    'training' => $training,
                    'user_score' => $user_score,
                    'passing_score' => $passing_score
                ]);

            }catch (Exception $e){

                DB::rollBack();
            }
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function deleteTraining(Training $training)
    {

        if (Storage::disk('public')->exists(\tenant('id').'/trainings/images/' . $training->cover)){
            Storage::disk('public')->delete(\tenant('id').'/trainings/images/' . $training->cover);
        }

        if (Storage::disk('public')->exists(\tenant('id').'/trainings/videos/' . $training->video)){
            Storage::disk('public')->delete(\tenant('id').'/trainings/videos/' . $training->video);
        }

        $deleting = $training->delete();

        return response()->json([
            'msg' => $deleting->title .' ha sido eliminado!'
        ]);
    }

    public function edit(Training $training)
    {
        $edit = Training::join('training_files', 'training_files.training_id', '=', 'trainings.id')
                        ->select('trainings.*', 'training_files.*')
                        ->where('trainings.id', '=', $training->id)->first();
        return response()->json([
            'training' => $edit
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

    // compartir training with studios
    public function getStudios()
    {
        $studios = Tenant::where('id', '!=', 1)->where('should_share', 1)->get();
        return response()->json($studios);
    }

    public function shareTraining(Request $request)
    {
         $training_id = $request->get('training_id');
         $studio_id = $request->get('studio_id');
         $created_by = auth()->user()->id;

         $exists = TrainingStudios::where('training_id', $training_id)->where('studio_id', $studio_id)->first();
         $msg = "";
         $icon = "";
         $code = "";
         
         if (is_null($exists)){
             TrainingStudios::updateOrCreate([
                 'training_id' => $training_id,
                 'studio_id' => $studio_id,
                 'created_by' => $created_by,
             ]);

             $msg = "Capacitacion compartida";
             $code = 200;
             $icon = "success";
         }

         return response()->json([
             'msg' => $msg,
             'icon' => $icon,
             'code' => $code,
         ]);
    }
}
