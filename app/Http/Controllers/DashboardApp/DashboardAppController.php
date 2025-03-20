<?php

namespace App\Http\Controllers\DashboardApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardAppController extends Controller
{
    //
    public function getDashboardAppMetrics()
    {
        try {
            $metrics = [
                'total_users' => DB::table('users')->whereNull('deleted_at')->count(),
                'total_owners' => DB::table('owners')->whereNull('deleted_at')->count(),
                'total_pets' => DB::table('pets')->whereNull('deleted_at')->count(),
                'total_appointments' => DB::table('appointments')->whereNull('deleted_at')->count(),
                'total_surgeries' => DB::table('surgeries')->whereNull('deleted_at')->count(),
                'total_vaccinations' => DB::table('vaccinations')->whereNull('deleted_at')->count(),
                'total_appointment_payments' => DB::table('appointment_payments')->whereNull('deleted_at')->count(),
                'total_surgerie_payments' => DB::table('surgerie_payments')->whereNull('deleted_at')->count(),
                'total_vaccination_payments' => DB::table('vaccination_payments')->whereNull('deleted_at')->count(),
                'appointments_schedule' => DB::table('appointments')->where('reprogramar', 0)->whereNull('deleted_at')->count(),
                'appointments_reschedule' => DB::table('appointments')->where('reprogramar', 1)->whereNull('deleted_at')->count(),
                'appointments_statepayment_pending' => DB::table('appointments')->where('state_pay', 1)->whereNull('deleted_at')->count(),
                'appointments_statepayment_partial' => DB::table('appointments')->where('state_pay', 2)->whereNull('deleted_at')->count(),
                'appointments_statepayment_complete' => DB::table('appointments')->where('state_pay', 3)->whereNull('deleted_at')->count(),
            ];
            return response()->json([
                'success' => true,
                'metrics' => $metrics ?? [], // Retorna un objeto con las métricas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo métricas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
