<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Mascota</th>
            <th>Especie</th>
            <th>Veterinario</th>
            <th>Tipo de servicio</th>
            <th>Fecha del servicio</th>
            <th>Estado del servicio</th>
            <th>Estado del pago servicio</th>
            <th>Costo del servicio</th>
            <th>Monto Cancelado</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($medical_records as $key=>$medical_record)
            @php
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
            @endphp
            <tr>
                <td>{{$key+1}}</td>
                <td> {{ $medical_record->pet->name }} </td>
                <td> {{ $medical_record->pet->specie }} </td>
                <td> {{ $medical_record->veterinarie->name.' '.$medical_record->veterinarie->surname }} </td>
                <td>
                    @php
                        $type_service = "";
                        switch ($medical_record->event_type) {
                            case 1:
                                $type_service = "Cita Medica";
                                break;
                            case 2:
                                $type_service = "Vacuna";
                            break;
                            case 3:
                                $type_service = "Cirujía";
                            break;
                            default:
                                # code...
                                break;
                        }
                    @endphp
                    {{$type_service}}
                </td>
                <td> {{ Carbon\Carbon::parse($medical_record->event_date)->format("Y/m/d") }} </td>
                    @php
                        $state_service = "";
                        switch ( $resource->state) {
                            case 1:
                                $state_service = "Pendiente";
                                break;
                            case 2:
                                $state_service = "Cancelado";
                                break;
                            case 3:
                                $state_service = "Atendido";
                                break;
                            default:
                                # code...
                                break;
                        }
                    @endphp
                @if ( $resource->state == 1)
                <td style="background: #ebee08">
                    {{$state_service}}
                </td>
                @endif
                @if ( $resource->state == 2)
                <td style="background: #f91818">
                    {{$state_service}}
                </td>
                @endif
                @if ( $resource->state == 3)
                <td style="background: #8ced31">
                    {{$state_service}}
                </td>
                @endif
                    @php
                        $state_payment = "";
                        switch ( $resource->state_pay) {
                            case 1:
                                $state_payment = "Pendiente";
                                break;
                            case 2:
                                $state_payment = "Parcial";
                                break;
                            case 3:
                                $state_payment = "Completo";
                                break;
                            default:
                                # code...
                                break;
                        }
                    @endphp
                @if ( $resource->state_pay == 1)
                <td style="background: #d63be4">
                    {{$state_payment}}
                </td>
                @endif
                @if ( $resource->state_pay == 2)
                <td style="background: #05beed">
                    {{$state_payment}}
                </td>
                @endif
                @if ( $resource->state_pay == 3)
                <td style="background: #0707ea">
                    {{$state_payment}}
                </td>
                @endif
                <td>
                    {{ $resource->amount }} PEN
                </td>
                <td>
                    {{$resource->payments->sum("amount")}} PEN
                </td>
                
            </tr>
        @endforeach

    </tbody>
</table>
