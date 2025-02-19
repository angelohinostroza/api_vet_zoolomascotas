<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Mascota</th>
            <th>Especie</th>
            <th>Veterinario</th>
            <th>Fecha de la cirujía</th>
            <th>Estado de la cirujía</th>
            <th>Estado de pago</th>
            <th>Horario</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($surgeries as $key=>$surgerie)
            <tr>
                <td>{{$key+1}}</td>
                <td> {{ $surgerie->pet->name }} </td>
                <td> {{ $surgerie->pet->specie }} </td>
                <td> {{ $surgerie->veterinarie->name.' '.$surgerie->veterinarie->surname }} </td>
                <td> {{ Carbon\Carbon::parse($surgerie->date_appointment)->format("Y/m/d") }} </td>
                    @php
                        $state_surgerie = "";
                        switch ($surgerie->state) {
                            case 1:
                                $state_surgerie = "Pendiente";
                                break;
                            case 2:
                                $state_surgerie = "Cancelado";
                                break;
                            case 3:
                                $state_surgerie = "Atendido";
                                break;
                            default:
                                # code...
                                break;
                        }
                    @endphp
                @if ($surgerie->state == 1)
                <td style="background: #ebee08">
                    {{$state_surgerie}}
                </td>
                @endif
                @if ($surgerie->state == 2)
                <td style="background: #f91818">
                    {{$state_surgerie}}
                </td>
                @endif
                @if ($surgerie->state == 3)
                <td style="background: #8ced31">
                    {{$state_surgerie}}
                </td>
                @endif
                    @php
                        $state_payment = "";
                        switch ($surgerie->state_pay) {
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
                @if ($surgerie->state_pay == 1)
                <td style="background: #d63be4">
                    {{$state_payment}}
                </td>
                @endif
                @if ($surgerie->state_pay == 2)
                <td style="background: #05beed">
                    {{$state_payment}}
                </td>
                @endif
                @if ($surgerie->state_pay == 3)
                <td style="background: #0707ea">
                    {{$state_payment}}
                </td>
                @endif

                <td>
                    <ul>
                        @foreach ($surgerie->schedules as $schedule)
                            <li>
                                {{Carbon\Carbon::parse(date("Y-m-d").' '.$schedule->schedule_hour->hour_start)->format("h:i A") .' '.Carbon\Carbon::parse(date("Y-m-d").' '.$schedule->schedule_hour->hour_end)->format("h:i A")}}
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endforeach

    </tbody>
</table>
