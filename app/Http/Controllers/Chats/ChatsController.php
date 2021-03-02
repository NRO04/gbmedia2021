<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatRelation;
use App\Models\Chat\ChatMessage;
use App\User;
use Auth;
use DB;
use Carbon\Carbon;
use App\Events\ChatMessageSent;
use App\Notifications\ChatNotification;
use App\Notifications\ChatMessageNotification;
use App\Notifications\ChatMessageReceived;

class ChatsController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('adminModules.chat.index');
	}

	public function fetchMessage(Request $request)
	{
		$result["messages"] = [];
		$result["talking_with"] = [];
		$messages = ChatMessage::where('chat_id', $request->chat)->get();

		$chat = Chat::find($request->chat); 
		if ($chat->type == 1) 
		{
			$result["talking_with"] = [
				"full_name" => "(Grupo) ".$chat->title,
				"avatar" => "7.jpg",
				"type" => 1,
			];
		}
		else
		{
			$friend = ChatRelation::where('user_id',"!=" ,Auth::user()->id)->where('chat_id', $request->chat)->get();
			$full_name = $friend[0]->user->userFullName();
			$result["talking_with"] = [
				"full_name" => $full_name,
				"avatar" => $friend[0]->user->avatar,
				"type" => 0,
			];
		}

		foreach ($messages as $message) 
		{
			$date = Carbon::parse($message->created_at, 'UTC');
			if (Carbon::now()->subDays(1) < $date) {
				$created_at = $date->diffForHumans();
			}
			else{
				$created_at = $date->isoFormat('D MMM, h:mm a');
			}
			
			$full_name = $message->user->userFullName();
			$result["messages"][] = [
				"full_name" => $full_name,
				"avatar" => $message->user->avatar,
				"user_id" => $message->user->id,
				"message" => $message->message,
				"status" => $message->status,
				"created_at" => $created_at,
			];
		}
		return $result;
	}

	public function sendMessage(Request $request)
	{
		try {
			DB::beginTransaction();

			$message = new ChatMessage;
			$message->chat_id = $request->chat;
			$message->message = $request->message;
			$message->type = 0;
			$message->user_id = Auth::user()->id;
			$message->save();

			$chat = Chat::find($request->chat);

			/*$broadcast_message = [
				"full_name" => Auth::user()->userFullName(),
				"message" => $request->message,
				"avatar" => Auth::user()->avatar,
				"user_id" => Auth::user()->id,
			];
			broadcast(new ChatMessageSent($broadcast_message))->toOthers();*/

			$relations = ChatRelation::select('user_id')->where('chat_id' ,  $request->chat)->where('user_id', '!=', Auth::user()->id)->get();
	        foreach ($relations as $relation) {
	            $user = $relation->user;

	            $user->notify(new ChatMessageNotification($message));	

	            $notification_status = false;
	            $notifications = $relation->user->unreadNotifications->where('type', 'App\Notifications\ChatNotification')->all();
	            foreach ($notifications as $notification) {

	            	if ($notification["data"]["chat_id"] == $chat->id) {
	            		$notification_status = true;
	            		break;
	            	}
	            }
	            if ($notification_status == false) 
	            {
					$user->notify(new ChatNotification($chat));		            	
	            }

	            
	        }

			DB::commit();
			response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollback();
			response()->json(['success' => false]);
		}
	}

	public function getFriends()
	{
		$friends = [];
		$distinct = ChatRelation::where('user_id', Auth::user()->id)->distinct('chat_id')->get();
		foreach ($distinct as $chats) 
		{
			$chat = Chat::find($chats->chat_id); 
			$full_name = $chat->user->userFullName();
			if ($chat->type == 1) 
			{
				$friends[] = [
					"chat" => $chat->id,
					"full_name" => "(Grupo) ".$chat->title,
					"type" => 1,
					"admin" => $full_name,
					"admin_id" => $chat->user_id,
					"avatar" => "7.jpg",
					"whispering" => 0,
				];
			}
			else
			{
				$friend = ChatRelation::where('user_id',"!=" ,Auth::user()->id)->where('chat_id', $chats->chat_id)->get();
				$full_name = $friend[0]->user->userFullName();
				$friends[] = [
					"chat" => $chat->id,
					"full_name" => $full_name,
					"type" => 0,
					"admin" => $full_name,
					"admin_id" => $friend[0]->user_id,
					"avatar" => $friend[0]->user->avatar,
					"whispering" => 0,
				];
			}
		}
		return $friends;
	}

	public function searchContact(Request $request)
	{
		$search_contact = $request->search_contact;
		$result = [];

		$contacts = User::select('users.id','first_name','last_name','second_last_name','users.avatar',"setting_roles.name as role")
				->join('setting_roles', 'setting_roles.id', '=', 'users.setting_role_id')
				->where(DB::raw("CONCAT(users.first_name, ' ' ,users.last_name, ' ' ,users.second_last_name)") , 'LIKE', "%$search_contact%" )
				->orWhere('setting_roles.name' , 'LIKE', "%$search_contact%" )
				->get();

		foreach ($contacts as $contact) 
			{
				$user_id = $contact->id;

				if ($user_id == Auth::user()->id) {
					continue;
				}

				$avatar = $contact->avatar;
				$avatar = (is_file("assets/img/avatars/".$avatar)) ? $avatar : "avatar.png";
				$distinct = ChatRelation::where('user_id', $user_id)->distinct('chat_id')->get();
				$is_friend = false;
				foreach ($distinct as $chats) 
				{
					$chat = Chat::find($chats->chat_id);
					if ($chat->type == 1) 
					{
						continue;
					}
					else
					{
						$exists = ChatRelation::where('user_id',"!=" ,$user_id)
								->where('user_id',"=" ,Auth::user()->id)
								->where('chat_id', $chats->chat_id)->exists();
						if ($exists) {
							$result[] = [
								"user_id" => $user_id,
								"chat" => $chat->id,
								"first_name" => $contact->first_name,
								"last_name" => $contact->last_name,
								"second_last_name" => $contact->second_last_name,
								"role" => $contact->role,
								"is_friend" => true,
								"avatar" => $avatar,
							];
							$is_friend = true;
							break;
						}
					}
				}

				if ($is_friend == false) 
				{
					$result[] = [
						"user_id" => $user_id,
						"chat" => 0,
						"first_name" => $contact->first_name,
						"last_name" => $contact->last_name,
						"second_last_name" => $contact->second_last_name,
						"role" => $contact->role,
						"is_friend" => false,
						"avatar" => $avatar,
					];
				}
			}		

		return $result;
	}

	public function addContact(Request $request)
	{
        try {
        	DB::beginTransaction();

        	$chat = new Chat;
			$chat->title = "";
			$chat->type = 0;
			$chat->user_id = Auth::user()->id;
			$chat->save();
        	$this->createRelation($chat->id, $request->id);	
        	$this->createRelation($chat->id, Auth::user()->id);	
        	

        	DB::commit();
        	return response()->json(['success' => true]);


        } catch (Exception $e) {
        	DB::rollback();
        	return response()->json(['success' => false]);
        }
	}

	public function createRelation($chat_id, $user_id)
	{
		$relation = new ChatRelation;	
    	$relation->chat_id = $chat_id;
    	$relation->user_id = $user_id;
    	$relation->status = 0;
    	$relation->save();
	}

	public function chatInCommon(Request $request)
	{
		$result = [
			"chat_id" => 0,
			"exists" => false,
		];
		$distinct = ChatRelation::select()->where('user_id', Auth::user()->id)->distinct('chat_id')->get();
		foreach ($distinct as $chats) 
		{
			if ($chats->chat->type == 1) 
				continue;
			
			$exists = ChatRelation::where('user_id',"=" ,$request->user_id)->where('chat_id', $chats->chat_id)->exists();
			if ($exists) 
			{
				$result = [
					"chat_id" => $chats->chat_id,
					"exists" => true,
				];
				break;
			}	
			
		}
		return $result;
	}

	public function changeStatusMessage(Request $request)
	{
		if ($request->chat > 0) 
		{
			try {
				DB::beginTransaction();

				$chat_message = ChatMessage::where('chat_id', $request->chat)->where('user_id','!=' ,Auth::user()->id)->update(['status' => 1]);
				$notification = Auth::user()->notifications()->where('type', 'App\Notifications\ChatNotification')->where('data->chat_id', "=" ,$request->chat)->first();
				
				if ($notification != null) 
				{
					$user = User::find($notification->data['user_id']);
					/*Auth::user()->notifications()->where('type', 'App\Notifications\ChatNotification')->where('data->chat_id', "=" ,$request->chat)->delete();*/
					Auth::user()->notifications()->where('id', $notification->id)->delete();
					$user->notify(new ChatMessageReceived()); 
				}
				

				DB::commit();
				response()->json(['success' => true]);
			} catch (Exception $e) {
				DB::rollback();
				response()->json(['success' => false]);
			}
		}
		

		
	}

	public function notifications(Request $request)
    {
        $result = [];
        $notifications = $request->user()->unreadNotifications->where('type', 'App\Notifications\ChatNotification')->all();
        foreach ($notifications as $notification) {
            $result[] = $notification;
         }
        return $result;
    }

}
