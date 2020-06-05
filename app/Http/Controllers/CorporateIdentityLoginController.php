<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\CorporateIdentityLogin;
use App\CorporateIdentity;
use App\Company;
use App\Http\Resources\CorporateIdentityResource;
use App\Media;

class CorporateIdentityLoginController extends Controller
{
    public function create(Request $request)
    {
        $data = $this->validate($request, CorporateIdentityLogin::rules('create'));

        $ciLogin = new CorporateIdentityLogin();
        $ciLogin->id = $data['id'];
        $company = Company::findOrFail($data['company']);
        $ci = CorporateIdentity::where('companyId', '=', $company->id)->firstOrFail();
    	$ciLogin->corporateIdentity()->associate($ci);
    	$ciLogin->save();

    	return Response(['data' => ['id' => $ciLogin->id, 'ci' => $ciLogin->corporateIdentity->id]],Response::HTTP_CREATED);
    }

    public function update(Request $request, string $id)
    {
        $data = $this->validate($request, CorporateIdentityLogin::rules('update'));

        $ciLogin = CorporateIdentityLogin::find($id);
        $ciLogin->id = $data['id'];

        $corporateIdentity = CorporateIdentity::find($ciLogin->corporateIdentity->id);

        //$this->authorize($corporateIdentity->company->id);

        $ciLogin->save();

        return Response('', Response::HTTP_NO_CONTENT);
    }

    public function delete(Request $request, string $id)
    {
        $ciLogin = CorporateIdentityLogin::findOrFail($id);

        $corporateIdentity = CorporateIdentity::findOrFail($ciLogin->corporateIdentity->id);

        //$this->authorize($corporateIdentity->company->id);

        $ciLogin->delete();

        return Response('', Response::HTTP_NO_CONTENT);
    }

    public function view(Request $request, string $id)
    {
        $ciLogin = CorporateIdentityLogin::findOrFail($id);

        $corporateIdentity = Media::changeImageFilenameToBase64String(CorporateIdentity::findOrFail($ciLogin->corporateIdentity->id));

        return CorporateIdentityResource::make($corporateIdentity)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
