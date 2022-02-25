<?php

namespace App\Http\Controllers;

use App\Helpers\AfterPayment\Transition\Transition;
use App\Helpers\AppointmentValidationHelper;
use App\Helpers\CommonHelper;
use App\Helpers\PaymentRequestHelper;
use App\Helpers\ExceptionHelper;
use App\payment\checkout\Checkout;
use App\PurchasesTransition;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Log;

class PaymentRequestController extends Controller {

    /**
     * Description of PaymentRequestController
     *
     * @author ILSA Interactive
     */
    public function addCustomerCard(Request $request) {

        try {
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            $inputs = $request->all();
            return Checkout::addCustomerToken($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function saveAuthorizationData(Request $request) {

        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return PaymentRequestHelper::saveAuthorizationData($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function deleteCard(Request $request) {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = (!isset($inputs['lang']) && empty($inputs['lang'])) ? 'EN' : $inputs['lang'];
            return PaymentRequestHelper::deleteCard($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollback();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function getCardsList(Request $request) {
        try {
            $inputs = $request->all();
            $inputs['lang'] = (!isset($inputs['lang']) && empty($inputs['lang'])) ? 'EN' : $inputs['lang'];
            return PaymentRequestHelper::getCardsList($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function getCardDetail(Request $request) {
        try {
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            $inputs = $request->all();
            return Checkout::getCardDetail($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function getToken() {
        return Checkout::getToken();
    }

    public function deleteUserCards(Request $request) {
        try {
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            $inputs = $request->all();
            return Checkout::deleteUserCard($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function paymentSuccess(Request $request) {
        try {
            $inputs = $request->all();
            $inputs['lang'] = 'EN';
            $records = Checkout::getPaymentDetail($inputs['cko-session-id'], 'payments');
            $updateStatus = PurchasesTransition::where('id', $inputs['purchase_transition_id'])->update(Transition::updatePurchaseTransition($records));
            if ($updateStatus) {
                return CommonHelper::jsonSuccessResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['save_appointment_success']);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function paymentFail(Request $request) {
        try {
            $inputs = $request->all();
            $inputs['lang'] = 'EN';
            $records = Checkout::getPaymentDetail($inputs['cko-session-id'], 'payments');
            $updateStatus = PurchasesTransition::where('id', $inputs['purchase_transition_id'])->update(Transition::updatePurchaseTransition($records));
            if ($updateStatus) {
                return CommonHelper::jsonErrorResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['save_appointment_error']);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function capturePayment(Request $request) {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            $record = Checkout::capturePayment($inputs);
            echo "<pre>";
            print_r($record);
            exit;
            $updateStatus = PurchasesTransition::where('id', $inputs['purchase_transition_id'])->update(Transition::updatePurchaseTransition($record));
            if ($updateStatus) {
                return CommonHelper::jsonSuccessResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['payment_capture_success']);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function getRequestToken(Request $request) {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return PaymentRequestHelper::getRequestToken($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function processPaymentRequest(Request $request) {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return PaymentRequestHelper::processPaymentRequest($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function splitOrderNotificationHook(Request $request) {

        Log::info("start split order webhook logs");
        Log::info($request->all());

        $key_from_configuration = "99B100800B8F598682855275AF0758470BACCB3FCE68A12E5DB9726D04C4BEAB";
        $iv_from_http_header = "000000000000000000000000";
        $auth_tag_from_http_header = "CE573FB7A41AB78E743180DC83FF09BD";
        $http_body = "0A3471C72D9BE49A8520F79C66BBD9A12FF9";

        $key = hex2bin($key_from_configuration);
        $iv = hex2bin($iv_from_http_header);
        $auth_tag = hex2bin($auth_tag_from_http_header);
        $cipher_text = hex2bin($http_body);

        $result = openssl_decrypt($cipher_text, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $auth_tag);

        Log::info($result);

        Log::info("end split order webhook logs");
    }

}
