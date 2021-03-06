<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderDetail;
use App\Evaluation;
use App\EvaluationDetail;
use App\Transfer;
use App\TransferDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;


class EvaluationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productionOrders = Order::where('approved', true)->get();
        $route = 'evaluation';
        return view('production_order_details')
            ->with('productionOrders', $productionOrders)
            ->with('route', $route);
    }

    public function find($orderDetailId)
    {
        $orderDetail = OrderDetail::find($orderDetailId);
        if (Evaluation::where('order_detail_id', $orderDetailId)->first() == null) {
            $evaluation = new Evaluation();
            $evaluation->order_detail_id = $orderDetailId;
            $evaluation->state = 0;
            $evaluation->user_id_created = Auth::id();
            $evaluation->user_id_updated = Auth::id();
            $evaluation->save();
        }
        return view('evaluation')
            ->with('orderDetail', $orderDetail);
    }

    public function notApply($orderDetailId)
    {
        $orderDetail = OrderDetail::find($orderDetailId);
        $orderDetail->apply_evaluation = 0;
        $orderDetail->save();
        return redirect()->route('evaluation_po');
    }

    public function store($orderDetailId, Request $request)
    {

        if ($request->input('btnEliminar') == "1"){
            $evaluation_details = EvaluationDetail::find($request->input('txtEvaluation_id'));
            $evaluation_details->delete();
        }else{
            $data = request()->validate([
                'txtAnimal_id' => 'required',
                'txtChapeta' => 'required',
                'txtDiagnostic' => 'required',
                'cmbFit' => 'required'
            ], [
                'txtAnimal_id.required' => 'El campo Id. Animal es obligatorio',
                'txtChapeta.required' => 'El campo Chapeta es obligatorio',
                'txtDiagnostic.required' => 'El campo Diagnóstico es obligatorio',
                'cmbFit.required' => 'El campo Apta es obligatorio'
            ]);

            if($request->input('txtEvaluation_id') == null){
                $evaluation = Evaluation::where('order_detail_id', $orderDetailId)->first();
                $evaluation_details = new EvaluationDetail();
                $evaluation_details->evaluation_id = $evaluation->id;
                $evaluation_details->user_id_created = Auth::id();
            }
            else{
                $evaluation_details = EvaluationDetail::find($request->input('txtEvaluation_id'));
            }
            $evaluation_details->animal_id = $request->input('txtAnimal_id');
            $evaluation_details->chapeta = $request->input('txtChapeta');
            $evaluation_details->diagnostic = $request->input('txtDiagnostic');
            $evaluation_details->fit = $request->input('cmbFit');
            $evaluation_details->synchronized = $request->input('cmbSynchronized');
            $evaluation_details->synchronized = $request->input('cmbSynchronized');
            $evaluation_details->other_procedures = $request->input('txtOther_procedures');
            $evaluation_details->comments = $request->input('txtComments');
            $evaluation_details->user_id_updated = Auth::id();
            $evaluation_details->save();
        }

        return redirect()->route('evaluation', $orderDetailId);
    }

    public function finish($orderDetailId) {
        $evaluation = Evaluation::where('order_detail_id', $orderDetailId)->first();
        $evaluation->state = 1;
        $evaluation->user_id_updated = Auth::id();
        $evaluation->save();

        $evaluation_details = EvaluationDetail::where('evaluation_id', $evaluation->id)
                                ->where('synchronized', true)
                                ->get();

        foreach ( $evaluation_details as $evaluation_detail ){
            if (Transfer::where('order_detail_id', $orderDetailId)->first() == null) {
                $transfer = new Transfer();
                $transfer->order_detail_id = $orderDetailId;
                $transfer->state = 0;
                $transfer->user_id_created = Auth::id();
                $transfer->user_id_updated = Auth::id();
                $transfer->save();
            }else{
                $transfer = Transfer::where('order_detail_id', $orderDetailId)->first();
            };

            $transfer_details = new TransferDetail();
            $transfer_details->transfer_id = $transfer->id;
            $transfer_details->evaluation_detail_id = $evaluation_detail->id;
            $transfer_details->receiver = $evaluation_detail->animal_id." ".$evaluation_detail->chapeta;
            $transfer_details->user_id_created = Auth::id();
            $transfer_details->user_id_updated = Auth::id();
            $transfer_details->save();
        }

        return redirect()->route('evaluation', $orderDetailId);
    }
}
