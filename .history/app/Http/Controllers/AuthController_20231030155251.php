<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\responder;

class AuthController extends Controller
{
    public function RequestPassword(Request $request)
    {
        $login = $request->data;
        $phone = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        if ($phone) {
            $login = $this->validatePhone($login);
        }

        $user = User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('phone', $login);
        })->first();
        if ($user != null) {
            $password = rand(10000, 99999);
            $user->password = Hash::make($password);
            $user->update();
            $msg = 'Your One Time Password is ' . $password;
            $message = new SmsController();
            $message->sendSms($user->phone, $msg);
            $message->sendEmail($user->email, $password);
            return responder()->success(['message' => 'Please Enter password Sent via Sms or Email']);
        } else {
            return responder()->error(401, 'Your credentials do not match any user');
        }
    }

    public function login(Request $request)
    {
        $login = $request->data;
        $login = request()->input('data');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        request()->merge([$fieldType => $login]);
        $data = filter_var($login, FILTER_VALIDATE_EMAIL) ? $request->data : $this->validatePhone($request->data);

        $cr = [$fieldType => $data, 'password' => $request->password];
        if (!Auth::attempt($cr)) {
            return responder()->error(401, 'Credentials Dont Match');
        }
        $user = User::where($fieldType, $data)->first();
        return responder()->success([
            'user' => $user,
            'token' => $user->createToken('Api Token of ' . $user->name)
        ]);
    }

    public function register(Request $request)
    {

        //extra validation and formation
        $phone = $this->validatePhone($request->phone);
        if ($this->checkuser($phone, $request->email)) {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
                'role' => 'user',
                'password' => $request->password,
            ]);
            $message = new SmsController();
            $mssg = "Hello ".$request->name."Welcome to Teketeke checkout";
            $message->sendSms($request->phone, $mssg);
            //$message->welcomeEmail($request->email);

            $msg = array('message' => 'Registration successful');

            return responder()->success($msg);
        } else {
            return responder()->error(401, 'You already have an account');
        }
    }

    private function checkuser($phone, $email)
    {
        $phones = User::where('phone', $phone)->get();
        $emails = User::where('email', $email)->get();

        if (count($phones) == 0 && count($emails) == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->success([
            'message' => 'You have successfully Loged out'
        ], 'You have successfully Loged out');
    }

    public function validatePhone($phone)
    {
        if (strpos($phone, '0') === 0 && strlen($phone) == 10) {
            $nphone = "+254" . substr($phone, 1);
        } elseif (strpos($phone, '+') === 0 && strlen($phone) == 13) {
            $nphone = $phone;
        } elseif (strpos($phone, '2') === 0 && strlen($phone) == 12) {
            $nphone = "+" . $phone;
        } else {
            $nphone = false;
        }
        return $nphone;
    }
}
