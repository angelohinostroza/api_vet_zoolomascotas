<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Mascota</th>
            <th>Especie</th>
            <th>Veterinario</th>
            <th>Fecha de la vacunación</th>
            <th>Estado de la vacunación</th>
            <th>Estado de pago</th>
            <th>Horario</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($vaccinations as $key=>$vaccination)
            <tr>
                <td>{{$key+1}}</td>
                <td> {{ $vaccination->pet->name }} </td>
                <td> {{ $vaccination->pet->specie }} </td>
                <td> {{ $vaccination->veterinarie->name.' '.$vaccination->veterinarie->surname }} </td>
                <td> {{ Carbon\Carbon::parse($vaccination->date_appointment)->format("Y/m/d") }} </td>
                    @php
                        $state_vaccination = "";
                        switch ($vaccination->state) {
                            case 1:
                                $state_vaccination = "Pendiente";
                                break;
                            case 2:
                                $state_vaccination = "Cancelado";
                                break;
                            case 3:
                                $state_vaccination = "Atendido";
                                break;
                            default:
                                # code...
                                break;
                        }
                    @endphp
                @if ($vaccination->state == 1)
                <td style="background: #ebee08">
                    {{$state_vaccination}}
                </td>
                @endif
                @if ($vaccination->state == 2)
                <td style="background: #f91818">
                    {{$state_vaccination}}
                </td>
                @endif
                @if ($vaccination->state == 3)
                <td style="background: #8ced31">
                    {{$state_vaccination}}
                </td>
                @endif
                    @php
                        $state_payment = "";
                        switch ($vaccination->state_pay) {
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
                @if ($vaccination->state_pay == 1)
                <td style="background: #d63be4">
                    {{$state_payment}}
                </td>
                @endif
                @if ($vaccination->state_pay == 2)
                <td style="background: #05beed">
                    {{$state_payment}}
                </td>
                @endif
                @if ($vaccination->state_pay == 3)
                <td style="background: #0707ea">
                    {{$state_payment}}
                </td>
                @endif

                <td>
                    <ul>
                        @foreach ($vaccination->schedules as $schedule)
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
