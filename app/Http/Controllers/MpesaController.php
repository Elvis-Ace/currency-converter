<?php

namespace App\Http\Controllers;

use App\Models\Mpesacredential;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
    public $consumerKey = 'gctRilqTJ8Wpw0qSmSlBRtpScd3xP256'; //Fill with your app Consumer Key
    public $consumerSecret = 'Axsbcs0uCdyGSKTb'; // Fill with your app Secret
    public $BusinessShortCode = '4107993';
    public $Passkey = '32d1e0ba621e5904e872171d2966e716415fd6706f93625521a0fe5362013720';


    public $PartyA; // This is your phone number,
    public $AccountReference, $TransactionDesc, $Amount, $Timestamp, $Password, $headers, $access_token_url, $initiate_url, $json, $user;
    public $source, $app;
    //mpesa receive
    public $stkCallbackResponse;

    public $CallBackURL = 'https://pay.kmarketlimited.co.ke/api/callback';
    public $response = array();
    public $access_token;
    public $TransactionType = 'CustomerPayBillOnline';

    public function __construct()
    {
        $credentials = Mpesacredential::find(1);
        if ($credentials != null) {
            $this->consumerKey = $credentials->consumerkey; //Fill with your app Consumer Key
            $this->consumerSecret = $credentials->consumersecret; // Fill with your app Secret
            $this->BusinessShortCode = $credentials->shortcode;
            $this->Passkey = $credentials->passkey;
            $this->CallBackURL = $credentials->callback;
            $this->TransactionType = $credentials->transactiontype;
        }
    }

    public function datapreview()
    {
        $data = [
            'consumerkey' => $this->consumerKey,
            'consumersecret' => $this->consumerSecret,
            'shortcode' => $this->BusinessShortCode,
            'passkey' => $this->Passkey,
            'callback' => $this->CallBackURL,
            'transactiontype' => $this->TransactionType,
        ];
        return view('mpesa.mpesa', $data);
    }


    //simulate
    public function simulate(Request $mrequest)
    {
        $this->Amount = $mrequest->amount;
        $this->AccountReference = 'Demo reference';
        $this->TransactionDesc = "Purchase";
        $this->PartyA = $mrequest->phone;
        $this->AddData();
        $this->RequestPayment();
        $data['response'] = $this->json;
        return redirect()->back()->with('message', 'Trannsacrion Initiated Successfully');
    }
    public function addcredentials(Request $request)
    {
        $credentials = Mpesacredential::find(1);
        if ($credentials != null) {
            $credentials->consumerkey = $request->consumerkey; //Fill with your app Consumer Key
            $credentials->consumersecret = $request->consumersecret; // Fill with your app Secret
            $credentials->shortcode = $request->shortcode;
            $credentials->passkey = $request->passkey;
            $credentials->callback = $request->callback;
            $credentials->transactiontype = $request->transactiontype;
            $credentials->update();
        } else {
            $credentials = new Mpesacredential();
            $credentials->consumerkey = $request->consumerkey; //Fill with your app Consumer Key
            $credentials->consumersecret = $request->consumersecret; // Fill with your app Secret
            $credentials->shortcode = $request->shortcode;
            $credentials->passkey = $request->passkey;
            $credentials->callback = $request->callback;
            $credentials->transactiontype = $request->transactiontype;
            $credentials->save();
        }

        return redirect()->back()->with('message', 'Data Saved Successfully');
    }

    public function AccessToken()
    {
        date_default_timezone_set('Africa/Nairobi');
        $headers = ['Content-Type:application/json; charset=utf8'];

        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_USERPWD, $this->consumerKey . ':' . $this->consumerSecret);
        $results = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result = json_decode($results);
        $this->access_token = $result->access_token;
        curl_close($curl);
    }
    public function AddData()
    {

        $this->Timestamp = date('YmdHis');
        $this->Password = base64_encode($this->BusinessShortCode . $this->Passkey . $this->Timestamp);

        $this->headers = ['Content-Type:application/json; charset=utf8'];
        $this->initiate_url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    }

    public function RequestPayment()
    {
        $this->AccessToken();

        # header for stk push
        $stkheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $this->access_token];

        # initiating the transaction
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->initiate_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header

        $curl_post_data = array(
            //Fill in the request parameters with valid values
            'BusinessShortCode' => $this->BusinessShortCode,
            'Password' => $this->Password,
            'Timestamp' => $this->Timestamp,
            'TransactionType' => $this->TransactionType,
            'Amount' => $this->Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => $this->BusinessShortCode,
            'PhoneNumber' => $this->PartyA,
            'CallBackURL' => $this->CallBackURL,
            'AccountReference' => $this->AccountReference,
            'TransactionDesc' => $this->TransactionDesc
        );

        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);

        $this->json = $curl_response;
        //dd($this->json);
        curl_close($curl);
        $this->FeedData();
    }

    private function FeedData()
    {
        $data = json_decode($this->json, true);
        $i = 0;
        $MerchantRequestID = "";
        $CheckoutRequestID = "";
        $ResponseCode = "";
        $ResponseDescription = "";
        $CustomerMessage = "";
        foreach ($data as $value) {
            $bl = $i++;
            switch ($bl) {
                case 0;
                    $MerchantRequestID = $value;
                    break;
                case 1;
                    $CheckoutRequestID = $value;
                    break;
                case 2;
                    $ResponseCode = $value;
                    break;
                case 3;
                    $ResponseDescription = $value;
                    break;
                case 4;
                    $CustomerMessage = $value;
                    break;
                default:
                    break;
            }
        }
        $payment = new Payment();
        $payment->payment = $this->user;
        $payment->MerchantRequestID = $MerchantRequestID;
        $payment->CheckoutRequestID = $CheckoutRequestID;
        $payment->ResponseCodeRe = $ResponseCode;
        $payment->ResponseDescription = $ResponseDescription;
        $payment->CustomerMessage = $CustomerMessage;
        $payment->amount = $this->Amount;
        $payment->phone = $this->PartyA;
        $payment->save();
        $response['status'] = "success";
        $response['response'] = "Please Accept When Propted";
        // return json_encode($response);
    }


    public function ReceiveMpesa()
    {
        $value = json_decode($this->stkCallbackResponse, true);
        if ($value["Body"]["stkCallback"]["ResultCode"] == "0") {
            $MerchantRequestID = $value["Body"]["stkCallback"]["MerchantRequestID"];
            $CheckoutRequestID = $value["Body"]["stkCallback"]["CheckoutRequestID"];
            $ResponseCode = $value["Body"]["stkCallback"]["ResultCode"];
            $desc  = $value["Body"]["stkCallback"]["ResultDesc"];
            $amount = $value["Body"]["stkCallback"]["CallbackMetadata"]["Item"][0]["Value"];
            $confirm = $value["Body"]["stkCallback"]["CallbackMetadata"]["Item"][1]["Value"];
            $date = date("Y-m-d h:i:sa", $value["Body"]["stkCallback"]["CallbackMetadata"]["Item"][3]["Value"]);
            //$phone = $value["Body"]["stkCallback"]["CallbackMetadata"]["Item"][4]["Value"];

            $payment = Payment::where('MerchantRequestID', $MerchantRequestID)->where('CheckoutRequestID', $CheckoutRequestID)->first();
            if ($payment != null) {
                $payment->ResponseCodeCo = $ResponseCode;
                $payment->description = $desc;
                $payment->confirm = $confirm;
                $payment->update();
                $order = Order::find($payment->payment);
                $order->status = "paid";
                $order->update();
                $sms = new SmsController();
                $sms->sendSms($payment->phone, "Your Payment Has been processed Successfully");
            }
        }
    }
}
