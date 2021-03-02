<?php

namespace App\Http\Controllers\Contacts;

use App\Http\Controllers\Controller;
use App\Models\Contacts\Contact;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('adminModules.contact.index');
    }

    public function GetAllContacts(Request $request)
    {
        $from = $request->input("from");
        $data = [];

        if ($from == 1) {
            $contacts = Contact::all();
            $count = count($contacts);

            foreach ($contacts as $contact) {
                $user = User::select('first_name', 'last_name')->where('id', $contact->modified_by)->first();

                if ($contact->contact_from == 2) {
                    $contact_from = "Juicy Service";
                } elseif ($contact->contact_from == 3) {
                    $contact_from = "GBMedia";
                } elseif ($contact->contact_from == 4) {
                    $contact_from = "Grupo Bedoya";
                } else {
                    $contact_from = "Generico";
                }

                $data[] = [
                    'email' => $contact->contact_email,
                    'from' => $contact_from,
                    'modified_by' => $user->first_name . " " . $user->last_name,
                    'created_at' => Carbon::parse($contact->created_at)->format('d M Y')
                ];
            }
        } else {
            $contacts = Contact::where('contact_from', $from)->get();
            $count = count($contacts);

            foreach ($contacts as $contact) {
                $user = User::select('first_name', 'last_name')->where('id', $contact->modified_by)->first();

                if ($contact->contact_from == 2) {
                    $contact_from = "Juicy Service";
                } elseif ($contact->contact_from == 3) {
                    $contact_from = "GBMedia";
                } elseif ($contact->contact_from == 4) {
                    $contact_from = "Grupo Bedoya";
                } else {
                    $contact_from = "Generico";
                }

                $data[] = [
                    'email' => $contact->contact_email,
                    'from' => $contact_from,
                    'modified_by' => $user->first_name . " " . $user->last_name,
                    'created_at' => Carbon::parse($contact->created_at)->format('d M Y'), 'count' => count($contacts)
                ];
            }
        }

        return response()->json(['contacts' => $data, 'count' => $count]);
    }

    public function searchContact(Request $request)
    {
        $contact = "";
        $this->validate($request, 
            ['contact_email' => 'required|email|regex:/(.+)@(.+)\.(.+)/i|max:255'],
            ['contact_email.email' => 'Ingrese un email valido']
        );

        $email = $request->input("contact_email");
        $exist = Contact::where('contact_email', $email)->exists();
        
        if ($exist) {
            $msg = "El contacto ya existe";
            $code = 403;
            $icon = "warning";
        } else {
            $msg = "El contacto fue insertado";
            $code = 200;
            $icon = "success";

            $contact = Contact::create([
                'contact_email' => $email,
                'contact_from' => 1,
                'modified_by' => auth()->user()->id,
                'owner_id' => 3
            ]);
        }

        return response()->json(['contact' => $contact, 'msg' => $msg, 'icon' => $icon, 'code' => $code]);
    }
}
