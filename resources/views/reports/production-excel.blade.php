<table>
    {{-- Title Section --}}
    @php
        $titleLines = explode("\n", $title);
        // Determine colspan based on mixer type
        $colspan = 12; // Default for CM3
        if (strpos($mixer, 'FM5') !== false || strpos($mixer, 'Mixer FM5') !== false) {
            $colspan = 11; // FM5 has 11 columns
        } elseif (strpos($mixer, 'CM4') !== false || strpos($mixer, 'Mixer CM4') !== false) {
            $colspan = 13; // CM4 has 13 columns (includes Machine)
        }
    @endphp
    @foreach($titleLines as $line)
        <tr>
            <td colspan="{{ $colspan }}" style="font-weight: bold; font-size: 14pt; text-align: center;">
                {{ $line }}
            </td>
        </tr>
    @endforeach
    <tr><td colspan="{{ $colspan }}">&nbsp;</td></tr>

    {{-- Headers based on mixer type --}}
    @if(strpos($mixer, 'FM5') !== false || strpos($mixer, 'Mixer FM5') !== false)
        {{-- FM5 Header Row 1 --}}
        <tr>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">No</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Date</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Time</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Batch ID</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Mixer</th>
            <th colspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Semen (Kg)</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Pasir beton (Galunggung)</th>
            <th colspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Pigmen</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Air</th>
        </tr>
        {{-- FM5 Header Row 2 --}}
        <tr>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Abu</th>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Putih</th>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Warna</th>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Qty (Kg)</th>
        </tr>
    @else
        {{-- CM3/CM4 Header Row 1 --}}
        <tr>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">No</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Date</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Time</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Batch ID</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Mixer</th>
            <th colspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Semen (Kg)</th>
            <th colspan="1" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Semen HC (Kg)</th>
            <th colspan="3" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Pasir (Pulsa)</th>
            <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Air</th>
            @if(strpos($mixer, 'CM4') !== false || strpos($mixer, 'Mixer CM4') !== false)
                <th rowspan="2" style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Machine</th>
            @endif
        </tr>
        {{-- CM3/CM4 Header Row 2 --}}
        <tr>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Silo 1</th>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Silo 3</th>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Silo 2</th>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Ciloseh / Kuarsa</th>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Giling 5 / Giling 6</th>
            <th style="font-weight: bold; background-color: #F3F4F6; text-align: center;">Screening</th>
        </tr>
    @endif

    {{-- Data Rows --}}
    @foreach($reportData['batches'] as $index => $batch)
        @if(strpos($mixer, 'FM5') !== false || strpos($mixer, 'Mixer FM5') !== false)
            {{-- FM5 Data Row --}}
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($batch['batchTime'])->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($batch['batchTime'])->format('H:i:s') }}</td>
                <td>#{{ $batch['idBatch'] }}</td>
                <td>{{ $batch['mixerCode'] }}</td>
                
                {{-- Semen Abu --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Semen FM5 Abu')->where('storageCode', 'Abu')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Semen Putih --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Semen FM5 Putih')->where('storageCode', 'Putih')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Pasir Galunggung --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Pasir FM5')->where('storageCode', 'Galunggung')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Pigmen Warna --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Pigmen Warna')->first()['quantity'] ?? '-';
                    @endphp
                    {{ $qty }}
                </td>
                
                {{-- Pigmen Qty --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Pigmen Qty')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Air --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Air')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
            </tr>
        @else
            {{-- CM3/CM4 Data Row --}}
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($batch['batchTime'])->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($batch['batchTime'])->format('H:i:s') }}</td>
                <td>#{{ $batch['idBatch'] }}</td>
                <td>{{ $batch['mixerCode'] }}</td>
                
                {{-- Silo 1 --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Semen (Kg)')->where('storageCode', 'Silo 1')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Silo 3 --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Semen (Kg)')->where('storageCode', 'Silo 3')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Silo 2 (HC) --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Semen HC (Kg)')->where('storageCode', 'Silo 2')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Ciloseh / Kuarsa --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Pasir (Pulsa)')->where('storageCode', 'Ciloseh / Kuarsa')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Giling 5 / Giling 6 --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Pasir (Pulsa)')->where('storageCode', 'Giling 5 / Giling 6')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Screening --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Pasir (Pulsa)')->where('storageCode', 'Screening')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Air --}}
                <td style="text-align: right;">
                    @php
                        $qty = collect($batch['details'])->where('materialCode', 'Air')->first()['quantity'] ?? 0;
                    @endphp
                    {{ $qty > 0 ? number_format($qty) : '-' }}
                </td>
                
                {{-- Machine (CM4 only) --}}
                @if(strpos($mixer, 'CM4') !== false || strpos($mixer, 'Mixer CM4') !== false)
                    <td>-</td>
                @endif
            </tr>
        @endif
    @endforeach
</table>
