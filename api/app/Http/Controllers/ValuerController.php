<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\ValuationReport;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\User;
use Validator;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;
use App\Mail\sendValuationFirUserInviteMail;

use setasign\Fpdi\Fpdi;
use DB;

class ValuerController extends Controller
{
    //
    public function __construct()
    {

        $this->middleware('auth:api', ['except' => ['generateQRCode']]);


    }
    public function uploadValuationReport(Request $request){
        
        $file = $request->file("report_pdf");
        $fileName = time() . rand(1, 99) . '.' . $file->extension();
        $file->move(public_path('reports'), $fileName);

        return response()->json(['file_name'=>$fileName], 200);
    }
    public function uploadReport(Request $request)
    {


        if (auth()->user()) {
            $user = auth()->user();
            if ($user->hasPermissionTo(Permission::where("slug", "upload report")->first())) {
                $validator = Validator::make($request->all(), [
                    'report_description' => 'required|string',
                    'market_value' => 'required|integer',
                    'forced_market_value' => 'required|integer',
                    'property_lr' => 'required|string',
                    'valuation_date' => 'required|string',
                    'encumberrence_details' => 'required|string',
                    'receiving_company_id' => 'required|string',
                    'report_pdf' => 'required|mimes:pdf|max:2048',
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors()->toJson(), 400);
                }
                $orgdetails = $user->UploaderOrganization()->first();
                $datatosave=$request->except(['report_pdf','valuation_data']);
                $file = $request->file("report_pdf");
                $fileName = time() . rand(1, 99) . '.' . $file->extension();
                $file->move(public_path('reports'), $fileName);
               
                $datatosave['report_uploading_user']=$user->id;
                $datatosave['valuation_date']= date('Y-m-d', strtotime($request->valuation_date));
                $datatosave['upload_link']=$fileName;
                $datatosave['report_uploading_from']=$orgdetails->id;
                $datatosave['approving_director']=0;
                $datatosave['receiving_company_read_code']=9999;
                $datatosave['unique_random_code']=999;
                $datatosave['receiving_company_id']=$request->post('receiving_company_id');
                $lastrow=(ValuationReport::latest()->first()) ? (ValuationReport::latest()->first()): ['id'=>1];
                $datatosave['id']=$lastrow['id']+1;
                $qrcode=$this->generateQRCode($orgdetails,$datatosave);
                $datatosave['qr_code']= $qrcode;                
                $reportd=ValuationReport::create($datatosave);
                //append qr code
                $filePath = public_path("reports/".$fileName);
                $outputFilePath = public_path("reports/".$fileName."_signed.pdf");
                $this->appendQRCODE($filePath, $outputFilePath,$qrcode);
                //append qr code
                //generate qr code
                return response()->json(['reportdetails'=>$reportd]);
            } else {
                return response()->json(['message' => 'unauthorized access'], 401);
            }
        } else {
            return response()->json(['message' => 'Please login'], 401);
        }
    }
    public function appendQRCODE($file, $outputFilePath,$qrcode){
        $fpdi = new FPDI;          
        $count = $fpdi->setSourceFile($file);  
        for ($i=1; $i<=$count; $i++) {  
            $template = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($template);
            $fpdi->AddPage($size['orientation'], array($size['width'], $size['height']));
            $fpdi->useTemplate($template);              
            $fpdi->SetFont("helvetica", "", 15);
            $fpdi->SetTextColor(153,0,153);  
            $left = -0;
            $top = 0;  
            $fpdi->Image(public_path("reportqr_codes/".$qrcode.".png"), $top, $left, -350);
           
        }
        $fpdi->Output($outputFilePath, 'F');
    }

    public function generateQRCode($orgdetails, $repoertdetais)
    {
        $writer = new PngWriter();
        // Create QR code
        $qrfilelabel=$this->generateValuationCode($repoertdetais['id']);
        $qrCode = QrCode::create('Valuation Firm:' . $orgdetails['organization_name'] . '
        Property:' . $orgdetails['organization_phone'] . '
        ISK NO:' . $orgdetails['organization_phone'] . '
        VRB No:' . $orgdetails['organization_phone'] . '
        Market Value:' . number_format((float) $repoertdetais['market_value']) . '
        Forced Market Value:' . number_format((float) $repoertdetais['forced_market_value']) . '
        Valuation Date:' . $repoertdetais['valuation_date'] . '
        Valuation Code:' . $qrfilelabel)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        //         $logo = Logo::create(public_path('reportqr_codes'),'symfony.png')
        // ->setResizeToWidth(50);      
        // Create generic label
        $label = Label::create('ISK Verified')
            ->setTextColor(new Color(255, 0, 0));
        $result = $writer->write($qrCode, null, $label);
        // Validate the result
        // $writer->validateResult($result, 'Life is too short to be generating QR codes');
        $result->saveToFile(public_path('reportqr_codes/'.$qrfilelabel.'.png'));
        return $qrfilelabel;
    }
    public function generateValuationCode($reportid)
    {
        $today = date('YmdHi');
        $startDate = date('YmdHi', strtotime('-'.$reportid.' days'));
        $range = $today - $startDate;
        $rand = rand(0, $range);
        return $rand;
    }
    public function updatePersonalInfromation(Request $request){
        $user = auth()->user();   
        $role=auth()->user()->roles()->first(["id", "name","name as role_name"]);
        $id=$user->id;    
        $userid=['user_id'=>auth()->user()->id];
            try {
                DB::beginTransaction();  
                $userup['full_name'] = $request->full_name;
                $userup['email'] =$request->email;
                $userup['vrb_number'] = $request->vrb_number;
                $userup['isk_number'] = $request->isk_number;
                $userup['profile_pic'] = $request->isk_number;                
                $userupdate = User::findOrFail(trim($id));
                $userupdate->fill($userup);
                $userupdate->save();                 
                DB::commit();

                $user = auth()->user(); 
                $bearerToken = $request->header('Authorization');
                // $bearerToken should be in the format 'Bearer {token}'            
                // Extract the actual token from the header value
                $token = str_replace('Bearer ', '', $bearerToken);
                $role=auth()->user()->roles()->first(["id", "name","name as role_name"]);
                return response()->json([
                    'message' => 'Updated successfully.',
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'user' => array_merge($userupdate->toArray(),$role->toArray(),$userid),
                    'role' => $role,
                    'user_id' => $user,
                    'roles' => $user->roles()->get(["id", "name"]),
                    'permissions' => array_merge($user->permissions()->get(["id", "slug as name"])->toArray(),$role->permissions()->get(['id','slug as name'])->toArray())
        
                ], 201);
             
            } catch (\Exception $exception) {
                DB::rollBack(); // Tell Laravel, "It's not you, it's me. Please don't persist to DB"
                return response()->json([
                    'message' => 'Failed.'.$exception->getMessage().'.Please contact admin.',
                    'error' => $exception,
                    'payload' => $request->all()
                ], 400);
            }
       
    }
   public function  retriveValuerOrgDetails(){
    $user = auth()->user();    
    try {       
        return response()->json($user->UploaderOrganization()->first()
           , 201);
     
    } catch (\Exception $exception) {
        DB::rollBack(); // Tell Laravel, "It's not you, it's me. Please don't persist to DB"
        return response()->json([
            'message' => 'Failed.'.$exception->getMessage().'.Please contact admin.',
            'error' => $exception
        ], 400);
    }

    }
    public function updateCompanyInfromation(Request $request){
        $user = auth()->user();   
        $role=auth()->user()->roles()->first(["id", "name","name as role_name"]);
        $id=$user->id;  
        $companyinfo=$user->  UploaderOrganization()->first();
        $userid=['user_id'=>auth()->user()->id];
            try {
                DB::beginTransaction();  
                $compup['organization_name'] = $request->organization_name;
                $compup['organization_phone'] =$request->organization_phone;
                $compup['organization_email'] = $request->organization_email;
                $compup['directors_vrb'] = $request->directors_vrb;
                $compup['isk_number'] = $request->isk_number;  
                $compup['idemnity_amount'] = $request->idemnity_amount;  
                $compup['idemnity_expiry'] = $request->idemnity_expiry;                
                $compupdate = Organization::findOrFail(trim($companyinfo->id));
                $compupdate->fill($compup);
                $compupdate->save();                 
                DB::commit();
                $user = auth()->user();                 
                return response()->json([
                    'message' => 'Updated successfully.'
                ], 201);
             
            } catch (\Exception $exception) {
                DB::rollBack(); // Tell Laravel, "It's not you, it's me. Please don't persist to DB"
                return response()->json([
                    'message' => 'Failed.'.$exception->getMessage().'.Please contact admin.',
                    'error' => $exception,
                    'payload' => $request->all()
                ], 400);
            }
       
    }
    public function sendUserInvite(REquest $request){

    }
}