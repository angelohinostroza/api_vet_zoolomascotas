<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\MedicalRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationScheduleMail;

class NotificationScheduleMessajeEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-schedule-message-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluación de los servicios, para notificar al cliente 1 hora antes de la atención';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        date_default_timezone_set('America/Lima');
        //
        $today_date = now()->format("Y-m-d");
        $medical_records = MedicalRecord::whereDate("event_date",$today_date)
                                            ->where("cron_state",0)
                                            ->get();

        foreach ($medical_records as $key => $medical_record) {
            // ESTADO DE SERVICO - PENDIENTE
            // HORA
            $resource = null;
            if($medical_record->appointment_id){
                $resource = $medical_record->appointment;
            }
            if($medical_record->vaccination_id){
                $resource = $medical_record->vaccination;
            }
            if($medical_record->surgerie_id){
                $resource = $medical_record->surgerie;
            }
            if($resource->state == 1){

                // OBTIENE EL TIEMPO ACTUAL Y CAPTURA LA HORA
                $time_current = now();
                $hour = $time_current->format("h");// 13

                // CALCULA LA HORA DE INICIO DEL SERVICIO
                $hour_start = "";
                $schedule_hour_start = $resource->schedules->sortBy("veterinarie_schedule_hour_id")->first();
                if($schedule_hour_start){
                    $hour_start = Carbon::parse(date("Y-m-d")." ".$schedule_hour_start->schedule_hour->hour_start)->format("h");
                }

                //if($hour == ($hour_start- 1)){
                    // PUEDO ENVIAR EL MENSAJE DE TEXTO O ENVIAR EL CORREO
                    if($medical_record->pet->owner->email){
                        $data = [
                            "full_name" => $medical_record->pet->owner->names.' '.$medical_record->pet->owner->surnames,
                            "name_pet" => $medical_record->pet->name,
                            "imagen" => env("APP_URL")."storage/".$medical_record->pet->photo,
                            "event_type" => $medical_record->event_type,
                            "event_date" => Carbon::parse($medical_record->event_date)->format("Y/m/d"),
                            "hour_start" => Carbon::parse(date("Y-m-d")." ".$schedule_hour_start->schedule_hour->hour_start)->format("h:i A")
                        ];
                        Mail::to($medical_record->pet->owner->email)->send(new NotificationScheduleMail($data));
                        $medical_record->update(["cron_state" => 1]);
                    }
                //}
            }
        }
    }
}
