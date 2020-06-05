<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhoneResource;
use App\Phone;
use App\PhoneType;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PhoneController extends Controller
{
    /**
     * @var Phone
     */
    protected $phone;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var PhoneType
     */
    protected $phoneType;

    /**
     * Create a new controller instance.
     *
     * @param Phone $phone
     * @param User $user
     * @param PhoneType $phoneType
     */
    public function __construct(Phone $phone, User $user, PhoneType $phoneType)
    {
        $this->phone = $phone;
        $this->user = $user;
        $this->phoneType = $phoneType;
    }

    /**
     * Create a new phone.
     *
     * @param Request $request
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, string $userId)
    {
        $user = $this->user->findOrFail($userId);
        $this->authorize('update', $user);
        $data = $this->validate($request, Phone::rules('create'));
        $type = $this->phoneType->findOrFail($data['type']);
        $phone = new Phone($data);
        $phone->user()->associate($user);
        $phone->type()->associate($type);
        $phone->save();
        return PhoneResource::make($phone)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a user's phones.
     *
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $userId)
    {
        /* @var User $user */
        $user = $this->user->findOrFail($userId);
        $this->authorize('view', $user);
        return PhoneResource::collection($user->phones)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a phone.
     *
     * @param string $phoneId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $phoneId)
    {
        /* @var Phone $phone */
        $phone = $this->phone->findOrFail($phoneId);
        $this->authorize($phone->user);
        return PhoneResource::make($phone)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a phone.
     *
     * @param Request $request
     * @param string $phoneId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $phoneId)
    {
        /* @var Phone $phone */
        $phone = $this->phone->findOrFail($phoneId);
        $this->authorize($phone->user);
        $data = $this->validate($request, Phone::rules('update'));
        if (array_key_exists('type', $data)) {
            $type = $this->phoneType->findOrFail($data['type']);
            $phone->type()->associate($type);
        }
        $phone->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a phone.
     *
     * @param string $phoneId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $phoneId)
    {
        /* @var Phone $phone */
        $phone = $this->phone->findOrFail($phoneId);
        $this->authorize($phone->user);
        $phone->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }
}
