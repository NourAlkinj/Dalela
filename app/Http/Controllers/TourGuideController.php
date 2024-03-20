<?php

namespace App\Http\Controllers;

use App\Http\Requests\TourGuideRequest;
use App\Models\TourGuide;
use App\Models\TourismAgency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TourGuideController extends Controller
{

    public function Login(Request $request)
    {
        $tourGuide = TourGuide::where(['email'=>$request->email])->first();
        if($tourGuide && Hash::check($request->password, $tourGuide->password))
        {
            $responseData = [
                'id' => $tourGuide->id,
                'name' => $tourGuide->name,
                'city' => $tourGuide->city,
                'phone_number' => $tourGuide->phone_number,
                'email' => $tourGuide->email,
                'gender_guide' => $tourGuide->gender_guide,
                'age_guide' => $tourGuide->age_guide,
                'price_guide' => $tourGuide->price_guide,
                'language_guide' => $tourGuide->language_guide,
//                'guide_languages' => $tourGuide->guideLanguages->map(function ($guideLanguage) {
//                    return [
//                        'language_id' => $guideLanguage->language->id,
//                        'language_name' => $guideLanguage->language->language_name
//                    ];
//                }),
                'tour_guide_type' => $tourGuide->tourGuideType,
                'created_at'=> $tourGuide->created_at ,
                'updated_at'=> $tourGuide->updated_at ,
                'averageRate'=> $this->getAvgGuideRates( $tourGuide->id)->original,
            ];
            return response()->json($responseData, 200);
        }

        return response()->json(['message' => "Unauthorized"], 401);
    }

    public function create(TourGuideRequest $request)    //register
    {
            if ($request->password !== $request->password_confirmation) {
                return response()->json(['message' => "Password and password confirmation do not match"], 400);
            }

            $tourGuide = TourGuide::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'password_confirmation' => Hash::make($request->password_confirmation),
                'city' => $request->city,
                'phone_number' => $request->phone_number,
                'type_id'=>2,
                'gender_guide' => $request->gender_guide,
                'age_guide' => $request->age_guide,
                'price_guide' => $request->price_guide,
                'language_guide' => $request->language_guide,
            ]);
            $tourGuide->tourGuideType()->associate($tourGuide->type_id);
        $tourGuide->save();

        // Loop through the languages and associate them with the TourGuide
//        foreach ($request->languages as $languageId) {
//            $tourGuide->guideLanguages()->create([
//                'language_id' => $languageId
//            ]);
//        }

        //output
        $responseData = [
            'id' => $tourGuide->id,
            'name' => $tourGuide->name,
            'city' => $tourGuide->city,
            'phone_number' => $tourGuide->phone_number,
            'email' => $tourGuide->email,
            'gender_guide' => $tourGuide->gender_guide,
            'age_guide' => $tourGuide->age_guide,
            'price_guide' => $tourGuide->price_guide,
            'language_guide' => $tourGuide->language_guide,
//            'guide_languages' => $tourGuide->guideLanguages->map(function ($guideLanguage) {
//                return [
//                    'language_id' => $guideLanguage->language->id,
//                    'language_name' => $guideLanguage->language->language_name
//                ];
//            }),
            'tour_guide_type' => $tourGuide->tourGuideType,
            'created_at'=> $tourGuide->created_at ,
            'updated_at'=> $tourGuide->updated_at ,
            'averageRate'=> $this->getAvgGuideRates( $tourGuide->id)->original,

        ];

        return response()->json($responseData, 200);
    }
    public function update(Request $request, $id)
    {
        $tourGuide = TourGuide::find($id);
        $tourGuide->update($request->all());
        return response()->json(['message' => "updated successfully"], 200);
    }

    public function getAllGuides()  //index
    {
        $allGuides = TourGuide::get();
        $formattedGuides = [];
        foreach ($allGuides as $guide) {
            $formattedGuides[] = [
                'id' => $guide->id,
                'name' => $guide->name,
                'city' => $guide->city,
                'phone_number' => $guide->phone_number,
                'email' => $guide->email,
                'gender_guide' => $guide->gender_guide,
                'age_guide' => $guide->age_guide,
                'price_guide' => $guide->price_guide,
                'language_guide' => $guide->language_guide,
                'tour_guide_type' => $guide->tourGuideType,
                'created_at' => $guide->created_at,
                'updated_at' => $guide->updated_at,
                'averageRate'=> $this->getAvgGuideRates( $guide->id)->original,
            ];
        }
        return response()->json($formattedGuides, 200);
    }

    public function getGuide($id)  //show
    {
        $guide = TourGuide::find($id);
        $guide = [
            'id' => $guide->id,
            'name' => $guide->name,
            'city' => $guide->city,
            'phone_number' => $guide->phone_number,
            'email' => $guide->email,
            'gender_guide' => $guide->gender_guide,
            'age_guide' => $guide->age_guide,
            'price_guide' => $guide->price_guide,
            'language_guide' => $guide->language_guide,
//            'guide_languages' => $tourGuide->guideLanguages->map(function ($guideLanguage) {
//                return [
//                    'language_id' => $guideLanguage->language->id,
//                    'language_name' => $guideLanguage->language->language_name
//                ];
//            }),
            'tour_guide_type' => $guide->tourGuideType,
            'created_at'=> $guide->created_at ,
            'updated_at'=> $guide->updated_at ,
            'averageRate'=> $this->getAvgGuideRates( $guide->id)->original,
        ];
        return response()->json($guide, 200);
    }

    public function getGuideRequests($guideId)
    {
        $guide = TourGuide::find($guideId);
        $guideRequests=$guide->guideRequests;
        $formattedGuideRequests = [];
        foreach ($guideRequests as $guideRequest) {
            $formattedGuideRequests[] = [
                'id'=> $guideRequest->id,
                'user_id' => User::find($guideRequest->user_id) ? (new UserController())->getUser($guideRequest->user_id)->original: null,
                'status'=> $guideRequest->status,
                'guide_id'=> TourGuide::find($guideRequest->guide_id) ? (new TourGuideController())->getGuide($guideRequest->guide_id)->original: null,
                'agency_id'=> TourismAgency::find($guideRequest->agency_id) ? (new TourismAgencyController())->getAgency($guideRequest->agency_id)->original: null,
                'request_date'=>$guideRequest->request_date,
                'created_at'=>$guideRequest->created_at,
            ];
        }
        return response()->json($formattedGuideRequests, 200);
    }

    public function getGuideRates($guideId)
    {
        $guide = TourGuide::find($guideId);
        $guideRates=$guide->guideRequests->pluck('rate');
        return response()->json($guideRates, 200);
    }

    public function getAvgGuideRates($guideId)
    {
        $guide = TourGuide::find($guideId);
        $avgGuideRates = $this->getGuideRates($guide->id)->original;
        $totalValue = 0;
        $numRates = count($avgGuideRates);

        foreach ($avgGuideRates as $avgGuideRate) {
            $totalValue += $avgGuideRate->value;
        }
        if ($numRates > 0) {
            $averageValue = $totalValue / $numRates;
        } else {
            $averageValue = 0;
        }
        return response()->json($averageValue, 200);
    }



}
