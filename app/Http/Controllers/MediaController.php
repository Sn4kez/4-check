<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\User;
use App\Group;
use App\Task;
use App\CorporateIdentity;


class MediaController extends Controller
{
    /**
     * The parameter name for the file name.
     *
     * file name is like a uuid4-string, e.g.
     * f127abb4-a844-11e8-98d0-529269fb1459.jpg
     * f127ae84-a844-11e8-98d0-529269fb1459.png
     *
     * Later it also could be something like
     * 20e77d92-a846-11e8-98d0-529269fb1459.pdf
     * 20e7830a-a846-11e8-98d0-529269fb1459.mp3
     */
    const REQUEST_PARAM_FILENAME = 'name';

    /**
     * The parameter which contains the base64 encoded
     * data.
     */
    const REQUEST_PARAM_BASE64_SOURCE = 'source_b64';

    /**
     * The parameter which makes the response output
     * base64 encoded datra
     */
    const REQUEST_PARAM_BASE64_OUTPUT = 'output_b64';

    public function create(Request $request)
    {
        if ($request->has(self::REQUEST_PARAM_BASE64_SOURCE)) {
            $name = Media::uploadImageBase64($request->input(self::REQUEST_PARAM_BASE64_SOURCE));

            if ($name !== null) {
                return response(json_encode(['name' => $name, 'link' => sprintf('/media?name=%s', $name)]), Response::HTTP_CREATED);
            }
        }

        return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($request->has(self::REQUEST_PARAM_FILENAME)) {
            $mediaName = $request->input(self::REQUEST_PARAM_FILENAME);

            if($this->checkAccess($user, $mediaName)){
                $path = Media::getPath($mediaName);

                if (Media::doesFileExist($path)) {
                    if ($request->has(self::REQUEST_PARAM_BASE64_OUTPUT)) {
                        return response(['data' => Media::getBase64String($path)], Response::HTTP_OK);
                    } else {
                        $contentType = Media::getHeaderForImages($path);

                        $response = new BinaryFileResponse($path, 200, ['Content-Type' => $contentType]);
                        return $response;
                    }
                } else {
                    return response('', Response::HTTP_NOT_FOUND);
                }
            } else {
                return response('', Response::HTTP_NOT_FOUND);
            }
        }

        return response('', Response::HTTP_NOT_FOUND);
    }

    /**
     * Check
     * - user profile image (current user attribute 'image')
     * - task (tasks, by companyId, attribute 'image')
     * - CI (corporate_identities, by companyId, attribute 'image')
     * - group (groups, by companyId, attribute 'image')
     *
     * @param User $user
     * @param $mediaName
     * @return bool
     */
    private function checkAccess(User $user, $mediaName)
    {
        /** @var Company $company */
        $companyId = $user->companyId;

        $currentUserImage = $user->image;
        $resultGroup = Group::all()->where('image', '=', $mediaName)->where('companyId', '=', $companyId);
        $resultTask = Task::all()->where('image', '=', $mediaName)->where('companyId', '=', $companyId);
        $resultCI = CorporateIdentity::all()->where('image', '=', $mediaName)->where('companyId', '=', $companyId);

        return $currentUserImage === $mediaName ||
            $resultCI->count() > 0 ||
            $resultTask->count() > 0 ||
            $resultGroup->count() > 0;
    }
}
