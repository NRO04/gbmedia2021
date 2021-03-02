<?php

namespace App\Http\Controllers\Loans;

use App\Http\Controllers\Controller;
use App\Models\Payrolls\PayrollMovement;
use Illuminate\Http\Request;
use App\Models\Loans\Loan;
use App\Models\Loans\LoanInstallments;
use DB;
use Auth;
use DataTables;
use Carbon\Carbon;
use App\Traits\TraitGlobal;
use App\User;

class LoanController extends Controller
{
	use TraitGlobal;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:loans')->only('list');
    }

    public function list($status)
    {
    	$users = User::select('id','first_name','last_name', 'second_last_name')->where('setting_role_id', '!=', 14)->get();
    	return view('adminModules.loan.list')->with([
    		"status" => $status,
    		"users" => $users,
    	]);
    }

    public function getLoans(Request $request)
    {
        $status = $request->status;
        $data = Loan::where('status', $request->status)->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('user', function($row){

                $src = is_null($row->user->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" .
                    $row->user->avatar);

                $full_name = $row->user->userFullName();
                $result = "<div class='d-flex'><div class='c-avatar' style='height:26px'>
                			<img class='c-avatar-img' src='{$src}'>
               			 </div> <p class='pl-2'>$full_name</p> </div>";
                return $result;
            })
            ->addColumn('created_at', function($row){
                $date = Carbon::parse($row->created_at, 'UTC');
                $result = $date->isoFormat('D MMMM YYYY');
                return $result;
            })
            ->addColumn('amount', function($row){

                $result = "<span>$ ".$this->convertToPesos($row->amount)."</span>";
                return $result;
            })
            ->addColumn('amount_due', function($row){
            	$amount_due = $this->amountDue($row->id);
                $result = "<span class='text-danger'>$ ".$this->convertToPesos($amount_due)."</span>";
                return $result;
            })
            ->addColumn('installment', function($row){
                $result = "<span>$ ".$this->convertToPesos($row->installment)."</span>";
                return $result;
            })
            ->addColumn('actions', function($row){

                $result = "<button type='button' class='btn btn-sm btn-primary' onclick='openLoanInstallments({$row->id})'><i class='fa fa-dollar-sign'></i></button>";
                return $result;
            })
            ->rawColumns(['user','created_at','amount','amount_due','installment','actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->validate($request,
            [
                'user_id' => 'required',
                'amount' => 'required|numeric',
                'interest' => 'required|numeric',
                'installment' => 'required|numeric',
            ],
            [
                'user_id.required' => 'Este campo es obligatorio',
                'amount.required' => 'Este campo es obligatorio',
                'interest.required' => 'Este campo es obligatorio',
                'installment.required' => 'Este campo es obligatorio',
                'amount.numeric' => 'Este campo debe ser numerico',
                'interest.numeric' => 'Este campo debe ser numerico',
                'installment.numeric' => 'Este campo debe ser numerico',
            ]);

            $loan = new Loan();
            $loan->user_id = $request->user_id;
            $loan->amount = $request->amount;
            $loan->interest = $request->interest;
            $loan->installment = $request->installment;
            $loan->status = 0;
            $loan->save();

            $loan_installment = new LoanInstallments();
            $loan_installment->loan_id = $loan->id;
            $loan_installment->type = 0;
            $loan_installment->installment = ($request->amount * $request->interest / 100);
            $loan_installment->created_by = Auth::user()->id;
            $loan_installment->save();


            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function storeInstallment(Request $request)
    {
    	try {
    		DB::beginTransaction();

            $result = [
                'success' => true,
                'bigger'  => false,
                'terminated'  => false,
                'permission' => true,
            ];
    		$this->validate($request,
	        [
	            'loan_id' => 'required',
	            'amount_installment' => 'required|numeric',
	        ],
	        [
	            'loan_id.required' => 'Este campo es obligatorio',
                'amount_installment.required' => 'Este campo es obligatorio',
	            'amount_installment.numeric' => 'Este campo debe ser numerico',
	        ]);

            $amount_due = $this->amountDue($request->loan_id);
            if ($amount_due < $request->amount_installment)
            {
                $result = [
                    'success' => false,
                    'bigger'  => true
                ];

                return response()->json($result);
            }

            if (!Auth::user()->can('loans-edit'))
            {
                $result = [
                    'success' => false,
                    'permission'  => false
                ];
                return response()->json($result);
            }

            $loan = Loan::find($request->loan_id);
            if ($amount_due == $request->amount_installment)
            {
                $loan->status = 1;
                $loan->save();
                $result['terminated'] = true;
            }

	    	$loan_installment = new LoanInstallments();
	    	$loan_installment->loan_id = $request->loan_id;
	    	$loan_installment->type = 1;
	    	$loan_installment->installment = $request->amount_installment;
	    	$loan_installment->created_by = Auth::user()->id;
	    	$loan_installment->save();


	    	$for_date = (date("d") < 15) ? date("Y")."-".date("m")."-07 00:00:00" : date("Y")."-".date("m")."-27 00:00:00";
            if (date("d") == 15)
                $for_date = date("Y")."-".date("m")."-27 00:00:00";
            if (date("d") == cal_days_in_month(CAL_GREGORIAN, date("m"), date("Y")))
                $for_date = date("Y")."-".date("m")."-07 00:00:00";

            $movements = new PayrollMovement;
            $movements->user_id = $loan->user_id;
            $movements->payroll_type_id = 15;
            $movements->amount = $request->amount_installment;
            $movements->created_by = Auth::user()->id;
            $movements->comment = "Abonado el ".date("Y-m-d H:i");
            $movements->for_date = $for_date;
            $movements->save();

    		DB::commit();
    		return response()->json($result);
    	} catch (Exception $e) {
    		DB::rollback();
    		return response()->json(['success' => false]);
    	}
    }

    public function amountDue($id)
    {
    	$loan = Loan::find($id);
    	$amount = $loan->amount;
    	$sum = LoanInstallments::where('loan_id', $id)->where('type', 0)->sum('installment');
    	$subtraction = LoanInstallments::where('loan_id', $id)->where('type', 1)->sum('installment');
    	$result = $amount + $sum - $subtraction;
    	return $result;
    }

    public function getLoanInstallment(Request $request)
    {
        $result["user_info"] = "";
        $result["installment_info"] = "";


        $data = Loan::find($request->id);

        $result["terminated"] = ($data->status == 0)? false : true;

        $src = url("assets/img/avatars/{$data->user->avatar}");
        $full_name = $data->user->userFullName();
        $result["user_info"] = "<tr>";
        $result["user_info"] .= "<td>
                                <div class='d-flex'>
                                    <div class='c-avatar' style='height:26px'>
                                        <img class='c-avatar-img' src='{$src}'>
                                    </div>
                                    <p class='pl-2'>$full_name</p>
                                </div></td>";

        $date = Carbon::parse($data->created_at, 'UTC');
        $date = $date->isoFormat('D MMMM YYYY');

        $result["user_info"] .= "<td>".$date."</td>";
        $result["user_info"] .= "<td><span>$ ".$this->convertToPesos($data->amount)."</span></td>";
        $result["user_info"] .= "<td>".$data->interest."</td>";

        $amount_due = $this->amountDue($data->id);
        $result["user_info"] .= "<td><span class='text-danger' >$ ".$this->convertToPesos($amount_due)."</span></td>";
        $result["user_info"] .= "<td><span>$ ".$this->convertToPesos($data->installment)."</span></td>";
        $result["user_info"] .= "</tr>";

        $total = $data->amount;
        $loan_installments = LoanInstallments::where('loan_id', $request->id)->get();

        foreach ($loan_installments as $loan_installment) {

            $date = Carbon::parse($loan_installment->created_at, 'UTC');
            $date = $date->isoFormat('D MMMM YYYY');

            if ($loan_installment->type == 0)
            {
                $total = $total + $loan_installment->installment;
                $result["installment_info"] .= "<tr class='table-danger'>";
                $result["installment_info"] .= "<td>".$date."</td>";
                $result["installment_info"] .= "<td class='text-danger font-weight-bold'>$".$this->convertToPesos($loan_installment->installment)."</td>";
                $result["installment_info"] .= "<td></td>";
                $result["installment_info"] .= "<td class='font-weight-bold'>$".$this->convertToPesos($total)."</td>";
                $result["installment_info"] .= "</tr>";
            }
            else
            {
                $total = $total - $loan_installment->installment;
                $result["installment_info"] .= "<tr class='table-success'>";
                $result["installment_info"] .= "<td>".$date."</td>";
                $result["installment_info"] .= "<td></td>";
                $result["installment_info"] .= "<td class='text-success font-weight-bold'>$".$this->convertToPesos($loan_installment->installment)."</td>";
                $result["installment_info"] .= "<td class='font-weight-bold'>$".$this->convertToPesos($total)."</td>";
                $result["installment_info"] .= "</tr>";
            }
        }

        return $result;
    }

    public function scriptLoans()
    {
        /*$min_id = 1;
        $max_id = 75;*/
        try {
            DB::beginTransaction();
            $prestamos = DB::connection('gbmedia')->table('p_prestamo')->whereBetween('p_id', [$min_id, $max_id])->get();
            foreach ($prestamos as $value)
            {
                $p_id = $value->p_id;
                $loan = new Loan();
                $loan->old_id = $p_id;
                $user = User::select('id')->where('old_user_id', $value->p_fk_u_id)->first();
                $loan->user_id = $user->id;
                $loan->amount = $value->p_valor_inicial;
                $loan->interest = $value->p_xc_interes;
                $loan->installment = $value->p_desea_pagar;
                $loan->status = $value->p_estado;
                $loan->created_at = $value->p_fecha_inicio." 00:00:00";
                $loan->save();

                $pagos = DB::connection('gbmedia')->table('p_abono_interes')->where('pa_fk_p_id', $p_id)->get();
                foreach ($pagos as $pag)
                {
                    $pa_id = $pag->pa_id;
                    $loan_installment = new LoanInstallments();
                    $loan_installment->inst_old_id = $pa_id;
                    $loan_installment->loan_id = $loan->id;
                    $loan_installment->type = $pag->pa_tipo;
                    $loan_installment->installment = $pag->pa_valor;
                    $created_by = 0;
                    if ($pag->pa_user_ejecuta != 0)
                    {
                        $user = User::select('id')->where('old_user_id', $value->p_fk_u_id)->first();
                        $created_by = $user->id;
                    }
                    $loan_installment->created_by = $created_by;
                    $loan_installment->created_at = $pag->pa_fecha_abono." 00:00:00";
                    $loan_installment->save();
                }
            }

            DB::Commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }
}
