<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Setting;
use App\Models\UserAddress;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
    {
        use Responses;

        public function index(Request $request)
        {
            $user_id = $request->user_id ?? Auth::id();
            
            $addresses = UserAddress::where('user_id', $user_id)->get();
            
            return $this->success_response('Addresses retrieved successfully', $addresses);
        }

        /**
         * Store a newly created address in storage.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|exists:users,id',
                'name' => 'required|string|max:255',
                'address' => 'nullable',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }

            // If user_id is not provided, use authenticated user's ID
            if (!$request->has('user_id')) {
                $request->merge(['user_id' => Auth::id()]);
            }

            $address = UserAddress::create($request->only([
                'user_id', 'name','address', 'lat', 'lng'
            ]));

            return $this->success_response('Address created successfully', $address);
        }

        /**
         * Display the specified address.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function show($id)
        {
            $address = UserAddress::find($id);
            
            if (!$address) {
                return $this->error_response('Address not found', null);
            }

            return $this->success_response('Address retrieved successfully', $address);
        }

        /**
         * Update the specified address in storage.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function update(Request $request, $id)
        {
            $address = UserAddress::find($id);
            
            if (!$address) {
                return $this->error_response('Address not found', null);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'address' => 'nullable',
                'lat' => 'sometimes|numeric',
                'lng' => 'sometimes|numeric',
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }

            $address->update($request->only([
                'name', 'lat', 'lng','address'
            ]));

            return $this->success_response('Address updated successfully', $address);
        }

        /**
         * Remove the specified address from storage.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function destroy($id)
        {
            $address = UserAddress::find($id);
            
            if (!$address) {
                return $this->error_response('Address not found', null);
            }

            // Check if the authenticated user is authorized to delete this address
            if (Auth::id() != $address->user_id ) {
                return $this->error_response('Unauthorized access', null);
            }

            $address->delete();

            return $this->success_response('Address deleted successfully', null);
        }
    }
