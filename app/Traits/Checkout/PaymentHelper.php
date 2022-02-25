<?php

namespace App\Traits\Checkout;

use App\Customer;
use App\CustomerCard;
use App\Helpers\CommonHelper;
use App\MadaCardsBin;
use Illuminate\Support\Facades\URL;

trait PaymentHelper
{
    public static function paymentParams($slug,$params){
       $bankParams = '';
       $customer = '';
       if(isset($params['customer_uuid'])){
           $customer = Customer::where('customer_uuid',$params['customer_uuid'])->with('token')->first()->toArray();
       }

        switch ($slug) {
            case 'payments':
                $bankParams = self::bookingParams($params,$customer);
                break;

            case 'paymentDetail':
                $bankParams = self::paymentDetailParams($params);
                break;

             case 'instruments':
                $bankParams = self::instrumentsParams($params,$customer);
                break;

            case 'capture':
                $bankParams = self::capturePaymentParams($params,$customer);
                break;

            case 'topUp':
                $bankParams = self::topPaymentParams($params,$customer);
                break;

            default:
                dd('no default');
        }

        return $bankParams;
    }
    public static function paymentDetailParams($params){
      return  [
            'source' =>[
                'type'=>'id',
                'id'=>$params
            ],
          'currency'=>self::currencyCheck($params['currency']),

       ];
    }

    public static function capturePaymentParams($params){
        return  [
            'source' =>[
                'type'=>'id',
                'id'=>'sid_oo7t5olevnuelnhltilfookcrm'
            ],
            'amount'=>10000,
            'reference'=>$params['payment_id']


        ];
    }
    public static function bookingParams($params,$customer){

        return [
            'source' =>[
                'type'=>'id',
                'id'=>$params['card_id']
            ],
          'amount'=>$params['paid_amount'] * 100,
          'currency'=>self::currencyCheck($params['currency']),
          'capture'=>false,
          'customer'=>[
               'email'=>$customer['email'],
               'name'=>$customer['first_name'].' '.$customer['last_name']
           ],
           'success_url'=>self::redirectUrl($params,$customer)['success_url'],
           'failure_url'=>self::redirectUrl($params,$customer)['failure_url'],
           "3ds"=> [
            "enabled"=> true,
            "version"=> "2.0.1"
            ]
        ];
    }
    public static function currencyCheck($currency){
        if($currency == 'Pound'){
            return 'GBP';
        }

        if($currency == 'SAR'){
            return 'SAR';
        }

    }
    public static function topPaymentParams($params,$customer){
        return [
            'source' =>[
                'type'=>'id',
                'id'=>$params['card_id']
            ],
            'amount'=>$params['paid_amount'] * 100,
            'currency'=>self::currencyCheck($params['currency']),
            'capture'=>true,
            'customer'=>[
                'email'=>$customer['email'],
                'name'=>$customer['first_name'].' '.$customer['last_name']
            ],
            'success_url'=>self::redirectUrl($params,$customer)['success_url'],
            'failure_url'=>self::redirectUrl($params,$customer)['failure_url'],
            "3ds"=> [
                "enabled"=> true,
                "version"=> "2.0.1"
            ]
        ];
    }

    public static function redirectUrl($params,$customer){

        if(isset($params['topup'])){
            return  [
                'success_url'=>URL::to('/')."/paymentSuccessFotTopUp?customer_id={$customer['id']}&wallet_id={$params['wallet_id']}",
                'failure_url'=>URL::to('/')."/paymentFailForTopUp?customer_id={$customer['id']}&wallet_id={$params['wallet_id']}",
            ];
        }else{
            return  [
                'success_url'=>URL::to('/').'/paymentSuccess?purchase_transition_id='.$params['purchase_transition']['purchase_transition']['id'],
                'failure_url'=>URL::to('/').'/paymentFail?purchase_transition_id='.$params['purchase_transition']['purchase_transition']['id'],
            ];
        }


     }
    public static function checkMadaCard($customer,$params){

        $customerCard = CustomerCard::where('customer_id',$customer['id'])->where('card_id',$params['card_id'])->first();
        return MadaCardsBin::where('number',$customerCard->bin)->exists();
    }
    public static function instrumentsParams($params,$customer){
        return [
            'type'=>'token',
            'token'=>$params['token'],
            'customer'=>[
                'email'=>$customer['email'],
                'name'=>$customer['first_name'].' '.$customer['last_name']
            ]
           ];
    }
}
