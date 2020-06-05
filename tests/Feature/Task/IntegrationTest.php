<?php

use App\Task;
use App\TaskType;
use App\TaskPriority;
use App\TaskState;
use App\Location;
use App\LocationType;
use App\LocationState;
use App\Company;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Notification;

class TaskIntegrationTest extends TestCase {

    use DatabaseMigrations;


    public function testCreateAndReadTest() {
        $this->actingAs($this->getUser(self::$ADMIN));

        $fileLocation = sprintf("%s/files/logo.jpg", dirname(__FILE__));
        $imageValue = '';

        if (file_exists($fileLocation)) {
            #$imageValue = base64_encode(file_get_contents($fileLocation));
            $imageValue = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($fileLocation));
        }

        for ($i = 0; $i < 3; $i++) {
            $r = $this->json('POST', '/tasks', [
                'name' => 'test',
                'assignee' => $this->admin->id,
                'issuer' => $this->admin->id,
                'company' => $this->admin->company->id,
                'source_b64' => $imageValue,
                'doneAt' => date('Y-m-d 23:59:59', time()),
                'image' => null,
                'giveNotice'=>0,
                'description' => 'bla',
                'location' => null,
                'priority' => TaskPriority::where('name', '=', 'low')->first(),
                'type' => TaskType::where('name', '=', 'offer')->first(),
                'state' => TaskState::where('name', '=', 'todo')->first()
            ]);

            $this->seeJsonNotNull([
                'data' => [
                    'image'
                ]
            ]);
        }
        $this->seeStatusCode(Response::HTTP_CREATED);

        $this->seeJsonNotNull([
            'data' => [
                'image'
            ]
        ]);

        $r = $this->json('GET', '/tasks/company/' . $this->admin->company->id);
        $this->seeStatusCode(Response::HTTP_OK);
    }
}