<?php

namespace App\Services;

use App\ParticipantExtension;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ParticipantExtensionService extends ExtensionService
{
    /**
     * @var ParticipantExtension
     */
    protected $participantExtension;

    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new controller instance.
     *
     * @param ParticipantExtension $participantExtension
     * @param User $user
     */
    public function __construct(ParticipantExtension $participantExtension, User $user)
    {
        $this->participantExtension = $participantExtension;
        $this->user = $user;
    }

    /**
     * Create a new participant extension.
     *
     * @param Request $request
     * @param array $data
     * @return ParticipantExtension
     */
    public function create(Request $request, array $data)
    {
        $data = $this->validateArray($request, $data, ParticipantExtension::rules('create'));
        $extension = new ParticipantExtension($data);
        if (array_key_exists('userId', $data) and !is_null($data['userId'])) {
            $user = $this->user->findOrFail($data['userId']);
            $this->authorize('update', $user);
            $extension->user()->associate($user);
        }
        return $extension;
    }

    /**
     * Update a participant extension.
     *
     * @param Request $request
     * @param array $data
     * @param Model $extension
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, array $data, Model $extension)
    {
        /** @var ParticipantExtension $extension */
        $data = $this->validateArray($request, $data, ParticipantExtension::rules('update'));
        $extension->update($data);
        if (array_key_exists('userId', $data)) {
            $oldUser = $extension->user;
            $this->authorize('update', $oldUser);
            if (is_null($data['userId'])) {
                $extension->user()->dissociate();
            } else {
                $newUser = $this->user->findOrFail($data['userId']);
                $this->authorize('update', $newUser);
                $extension->user()->associate($newUser);
            }
        }
    }
}
