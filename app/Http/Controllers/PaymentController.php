<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use App\Models\Product;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function index()
    {

        $data = Product::all();
        return view('barang.index',compact('data'));
    }

    public function Buyproduct($id)
    {
        $data = Product::find($id);
        return view('payment.index',compact('data'));
    }

    public function sendPayment(Request $request)
    {
        $va           = '0000007863301421'; //get on iPaymu dashboard
        $secret       = 'SANDBOXB5B6042A-2DFF-4DAF-B5F2-371400573D17-20220323152047'; //get on iPaymu dashboard
    
        $url          = 'https://sandbox.ipaymu.com/api/v2/payment'; //url
        $method       = 'POST'; //method
        $input = $request->all();
        //Request Body//
        $body['product']    = array($input['name']);
        $body['qty']        = array('1');
        $body['price']      = array($input['harga']);
        $body['returnUrl']  = 'https://mywebsite.com/thankyou';
        $body['cancelUrl']  = 'https://mywebsite.com/cancel';
        $body['notifyUrl']  = 'https://mywebsite.com/notify';
        //End Request Body//
    
        //Generate Signature
        // *Don't change this
        $jsonBody     = json_encode($body, JSON_UNESCAPED_SLASHES);
        $requestBody  = strtolower(hash('sha256', $jsonBody));
        $stringToSign = strtoupper($method) . ':' . $va . ':' . $requestBody . ':' . $secret;
        $signature    = hash_hmac('sha256', $stringToSign, $secret);
        $timestamp    = Date('YmdHis');
        //End Generate Signature
    
    
        $ch = curl_init($url);
    
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'va: ' . $va,
            'signature: ' . $signature,
            'timestamp: ' . $timestamp
        );
    
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        curl_setopt($ch, CURLOPT_POST, count($body));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $err = curl_error($ch);
        $ret = curl_exec($ch);
        curl_close($ch);
        if($err) {
            echo $err;
        } else {
    
            //Response
            $ret = json_decode($ret);
            if($ret->Status == 200) {
                $sessionId  = $ret->Data->SessionID;
                $url        =  $ret->Data->Url;
                return Redirect::to($url);
            } else {
                echo $ret;
            }
            //End Response
        }
    
    }
}
