<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Http\Response;

class CreateMediaTest extends TestCase
{
    use DatabaseMigrations;

    public function provideValidEntities()
    {
        return [
            ['logo.jpg', 'base64'],
            ['logo.png', 'base64']
        ];
    }

    /**
     * @dataProvider provideValidEntities
     * @param $fileName
     * @param $encoding
     */
    public function testValidEntities($fileName, $encoding)
    {
        $this->actingAs($this->user);

        $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $fileName);
        $fileContent = file_get_contents($fileLocation);
        $paramName = "source";

        switch ($encoding) {
            case "base64":
                $fileContent = base64_encode($fileContent);
                $paramName = "source_b64";
                break;
        }

        $this->json('POST', '/media', [$paramName => $fileContent]);
        $this->seeStatusCode(Response::HTTP_CREATED);
    }

    public function provideInvalidEntities()
    {
        return [
            ['source_b64', null, Response::HTTP_UNPROCESSABLE_ENTITY],
            ['source_b64', false, Response::HTTP_UNPROCESSABLE_ENTITY],
            ['source_b64', null, Response::HTTP_UNPROCESSABLE_ENTITY],
            ['source_b64', false, Response::HTTP_UNPROCESSABLE_ENTITY],
            ['illegal_source', null, Response::HTTP_UNPROCESSABLE_ENTITY],
            ['illegal_source', false, Response::HTTP_UNPROCESSABLE_ENTITY],
            ['illegal_source', null, Response::HTTP_UNPROCESSABLE_ENTITY],
            ['illegal_source', false, Response::HTTP_UNPROCESSABLE_ENTITY],
        ];
    }

    /**
     * @param $paramName
     * @param $fileContent
     * @param $awaitedResponse
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($paramName, $fileContent, $awaitedResponse)
    {
        $this->actingAs($this->user);

        $this->json('POST', '/media', [$paramName => $fileContent]);
        $this->seeStatusCode($awaitedResponse);
    }
}
