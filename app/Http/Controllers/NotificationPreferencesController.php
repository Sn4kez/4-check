<?php

namespace App\Http\Controllers;

use App\NotificationPreferences;
use App\Http\Resources\NotificationPreferencesResource;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class NotificationPreferencesController extends Controller
{
	public function create(Request $request)
	{
		$data = $this->validate($request, NotificationPreferences::rules('create'));

		$user = User::findOrFail($data['user']);

		$prefrences = new NotificationPreferences($data);
		$preferences->id = Uuid::uuid4()->toString();
		$preferences->user()->associate($user); 
		$preferences->save();

		return NotificatoinPreferencesResource::make($preferences)
    			->response()
    			->setStatusCode(Response::HTTP_OK);
	}

	public function update(Request $request, string $userId)
	{
		$data = $this->validate($request, NotificationPreferences::rules('update'));

		$preferences = NotificationPreferences::firstOrCreate(['userId' => $userId]);

		$this->authorize($preferences, 'update');

		$preferences->fill($data);
		$preferences->save();

		return response($preferences, Response::HTTP_OK);
	}

	public function delete(Request $request, string $notificationPreferencesId)
	{
		$preferences = NotificationPreferences::findOrFail($notificationPreferencesId);

    	$prefrences->delete();

    	return response('', Response::HTTP_NO_CONTENT);
	}

    public function view(string $userId = null)
    {
    	$preferences = NotificationPreferences::where('userId', '=', $userId)->firstOrFail();

    	$this->authorize($preferences, 'view');

    	return NotificationPreferencesResource::make($preferences)
    			->response()
    			->setStatusCode(Response::HTTP_OK);
    }
}
