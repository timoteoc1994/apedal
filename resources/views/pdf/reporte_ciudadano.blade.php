<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            margin: 15px;
            color: #000000;
            line-height: 1.2;
            font-size: 11px;
        }
        
        .header {
            border-bottom: 2px solid #3d16b7;
            padding-bottom: 8px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .logo {
            width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 8px auto;
        }
        
        .header h1 {
            color: #3d16b7;
            font-size: 18px;
            margin: 0 0 5px 0;
        }
        
        .header .subtitle {
            color: #000000;
            font-size: 10px;
            margin: 0;
        }
        
        .fecha {
            text-align: right;
            color: #000000;
            font-size: 9px;
            margin-bottom: 10px;
        }
        
        .section {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 12px;
            border-left: 3px solid #3d16b7;
        }
        
        .section h2 {
            color: #3d16b7;
            font-size: 14px;
            margin: 0 0 8px 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        
        .info-row {
            display: inline-block;
            width: 48%;
            margin: 3px 1% 3px 0;
            vertical-align: top;
        }
        
        .info-box {
            background: white;
            padding: 6px;
            border: 1px solid #e1e5e9;
        }
        
        .info-box .label {
            font-weight: bold;
            color: #000000;
            font-size: 9px;
            text-transform: uppercase;
        }
        
        .info-box .value {
            font-size: 12px;
            color: #000000;
            margin-top: 2px;
        }
        
        .highlight {
            background: #3d16b7;
            color: white;
            padding: 10px;
            text-align: center;
            margin: 8px 0;
        }
        
        .highlight h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
        }
        
        .highlight .big-number {
            font-size: 20px;
            font-weight: bold;
        }

        .highlight-green {
            background: #28a745;
            color: white;
            padding: 10px;
            text-align: center;
            margin: 8px 0;
        }
        
        .highlight-green h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
        }
        
        .highlight-green .big-number {
            font-size: 20px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            background: white;
            font-size: 10px;
        }
        
        th {
            background-color: #3d16b7;
            color: white;
            padding: 5px 4px;
            text-align: left;
            font-size: 10px;
        }
        
        td {
            padding: 4px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .material-name {
            font-weight: bold;
            color: #000000;
        }
        
        .peso-value {
            text-align: right;
            font-weight: bold;
            color: #28a745;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #000000;
            font-size: 8px;
        }
        
        .impacto-item {
            background: #e8f5e8;
            padding: 4px 6px;
            margin: 2px 0;
            border-left: 3px solid #28a745;
            font-size: 9px;
            line-height: 1.3;
        }
        
        .impacto-categoria {
            background: #f0f0f0;
            padding: 6px;
            margin: 8px 0 4px 0;
            border-left: 3px solid #3d16b7;
            font-weight: bold;
            font-size: 10px;
        }
        
        .impacto-total {
            background: #fff3cd;
            padding: 8px;
            margin: 8px 0;
            border: 2px solid #ffc107;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }
        
        .clear {
            clear: both;
        }

        .ciudadano-info {
            background: #e8f4fd;
            padding: 8px;
            margin-bottom: 12px;
            border-left: 3px solid #007bff;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="{{ public_path('storage/plataforma/logopdf.png') }}" alt="Logo ADRI" class="logo">
        <h1>Reporte Personal de Reciclaje</h1>
        <p class="subtitle">Sistema ADRI - Análisis Personal de Recuperación de Materiales</p>
    </div>
    
    <!-- Fecha de generación -->
    <div class="fecha">
        Generado el: {{ date('d/m/Y H:i:s') }}
    </div>
    
    <!-- Filtros aplicados -->
    @if(isset($filtros) && !empty(array_filter($filtros)))
    <div class="section">
        <h2>Filtros Aplicados</h2>
        
        @if(isset($filtros['estado']) && !empty($filtros['estado']) && $filtros['estado'] !== 'todos')
        <div class="info-row">
            <div class="info-box">
                <div class="label">Estado de Solicitudes</div>
                <div class="value">{{ ucfirst($filtros['estado']) }}</div>
            </div>
        </div>
        @endif
        
        @if(isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde']))
        <div class="info-row">
            <div class="info-box">
                <div class="label">Fecha Desde</div>
                <div class="value">{{ date('d/m/Y', strtotime($filtros['fecha_desde'])) }}</div>
            </div>
        </div>
        @endif
        
        @if(isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta']))
        <div class="info-row">
            <div class="info-box">
                <div class="label">Fecha Hasta</div>
                <div class="value">{{ date('d/m/Y', strtotime($filtros['fecha_hasta'])) }}</div>
            </div>
        </div>
        @endif

        @if(isset($filtros['mes']) && !empty($filtros['mes']))
        <div class="info-row">
            <div class="info-box">
                <div class="label">Mes Consultado</div>
                <div class="value">
                    @php
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                    @endphp
                    {{ $meses[$filtros['mes']] ?? 'Mes ' . $filtros['mes'] }}
                </div>
            </div>
        </div>
        @endif
        
        @if(isset($filtros['anio']) && !empty($filtros['anio']))
        <div class="info-row">
            <div class="info-box">
                <div class="label">Año Consultado</div>
                <div class="value">{{ $filtros['anio'] }}</div>
            </div>
        </div>
        @endif
    </div>
    @endif
    
    <!-- Información del ciudadano -->
    <div class="ciudadano-info">
        <h2>Información Personal</h2>
        
        <div class="info-row">
            <div class="info-box">
                <div class="label">Nombre del Ciudadano</div>
                <div class="value">{{ $nombre ?? 'Sin especificar' }}</div>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-box">
                <div class="label">Email</div>
                <div class="value">{{ $email ?? 'Sin especificar' }}</div>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-box">
                <div class="label">Teléfono</div>
                <div class="value">{{ $telefono ?? 'Sin especificar' }}</div>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-box">
                <div class="label">Ciudad</div>
                <div class="value">{{ $ciudad ?? 'Sin especificar' }}</div>
            </div>
        </div>

        <!-- Puntos actuales destacados -->
        <div class="highlight-green">
            <h3>Puntos Acumulados Actuales</h3>
            <div class="big-number">{{ $puntos_actuales ?? 0 }} puntos</div>
        </div>
    </div>
    
    <!-- Estadísticas de solicitudes -->
    <div class="section">
        <h2>Estadísticas de Solicitudes</h2>
        
        <div class="info-row">
            <div class="info-box">
                <div class="label">Solicitudes Completadas</div>
                <div class="value">{{ $solicitudes_completadas ?? 0 }}</div>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-box">
                <div class="label">Solicitudes Pendientes</div>
                <div class="value">{{ $solicitudes_pendientes ?? 0 }}</div>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-box">
                <div class="label">Solicitudes Canceladas</div>
                <div class="value">{{ $solicitudes_canceladas ?? 0 }}</div>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-box">
                <div class="label">Total de Solicitudes</div>
                <div class="value">{{ $total_solicitudes ?? 0 }}</div>
            </div>
        </div>
        
        <!-- Peso total destacado -->
        <div class="highlight">
            <h3>Total de Material Reciclado</h3>
            <div class="big-number">{{ $suma_peso_kg ?? 0 }} Kg</div>
        </div>
    </div>
    
    <!-- Desglose de materiales -->
    <div class="section">
        <h2>Desglose de Materiales Reciclados</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Material</th>
                    <th style="text-align: right;">Peso (Kg)</th>
                    <th style="text-align: right;">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPeso = $suma_peso_kg ?? 1;
                    $materiales = [
                        ['nombre' => 'Papel', 'peso' => $papel ?? 0],
                        ['nombre' => 'Tetra Pak', 'peso' => $tetrapak ?? 0],
                        ['nombre' => 'Botellas PET', 'peso' => $botellasPET ?? 0],
                        ['nombre' => 'Plásticos Suaves', 'peso' => $plasticosSuaves ?? 0],
                        ['nombre' => 'Plásticos Soplado', 'peso' => $plasticosSoplado ?? 0],
                        ['nombre' => 'Plásticos Rígidos', 'peso' => $plasticosRigidos ?? 0],
                        ['nombre' => 'Vidrio', 'peso' => $vidrio ?? 0],
                        ['nombre' => 'Pilas', 'peso' => $pilas ?? 0],
                        ['nombre' => 'Latas', 'peso' => $latas ?? 0],
                        ['nombre' => 'Metales', 'peso' => $metales ?? 0],
                        ['nombre' => 'Electrodomésticos', 'peso' => $electrodomesticos ?? 0],
                        ['nombre' => 'Electrónicos', 'peso' => $eletronicos ?? 0],
                    ];
                @endphp
                
                @foreach($materiales as $material)
                    @if($material['peso'] > 0)
                    <tr>
                        <td class="material-name">{{ $material['nombre'] }}</td>
                        <td class="peso-value">{{ number_format($material['peso'], 2) }}</td>
                        <td class="peso-value">{{ $totalPeso > 0 ? number_format(($material['peso'] / $totalPeso) * 100, 1) : 0 }}%</td>
                    </tr>
                    @endif
                @endforeach
                
                @if(collect($materiales)->sum('peso') == 0)
                <tr>
                    <td colspan="3" style="text-align: center; color: #888; font-style: italic;">
                        No se registraron materiales reciclados en este período
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Impacto Ambiental Personal -->
    <div class="section">
        <h2>Tu Impacto Ambiental Personal</h2>
        
        @php
            // Obtener sumas de materiales
            $suma_papel = $papel ?? 0;
            $suma_botellasPET = $botellasPET ?? 0;
            $suma_plasticosSuaves = $plasticosSuaves ?? 0;
            $suma_plasticosSoplado = $plasticosSoplado ?? 0;
            $suma_plasticosRigidos = $plasticosRigidos ?? 0;
            $suma_latas = $latas ?? 0;
            
            // Cálculos para papel y cartón
            $evitado_tala_arboles = $suma_papel / 58.85;
            $oxigeno_producido = $evitado_tala_arboles * 4;
            $co2_captado = $evitado_tala_arboles * 21;
            $evitado_consumo_agua = $suma_papel / 1000 * 70000;
            $evitado_consumo_kwh = $suma_papel / 1000 * 2750;
            $evitado_c02_anio = $suma_papel * 2.5;
            $evitado_emitir_admosfera = $evitado_consumo_kwh * 0.65;
            
            // Cálculos para plásticos
            $total_plasticos_calculo = $suma_botellasPET + $suma_plasticosSuaves + $suma_plasticosSoplado + $suma_plasticosRigidos;
            $evitado_consumo_agua_plasticos = $total_plasticos_calculo * 233.67;
            $evitado_emitir_admosfera_plasticos = $total_plasticos_calculo * 1.45;
            $evitado_consumo_kwh_plasticos = $total_plasticos_calculo * 2.5;
            $recuperado_botellas_pet = $total_plasticos_calculo * (1000 / 18);
            
            // Cálculos para latas de aluminio
            $total_calculo_aluminio = $suma_latas;
            $evitado_explotar_bauxita = $total_calculo_aluminio * 4;
            $recuperado_latas_bebida = $total_calculo_aluminio * (1000 / 15);
            $evitado_emitir_admosfera_aluminio = $total_calculo_aluminio * 16;
            $evitado_consumo_kwh_aluminio = $total_calculo_aluminio * 14.9;
            
            // Evitado el desecho al relleno sanitario
            $suma_desechos_carros_recolector = ($suma_papel + $total_plasticos_calculo + $total_calculo_aluminio) / 100 / 15;
            
            // Resumen eco-equivalencia
            $total_evitado_consumo_agua = $evitado_consumo_agua + $evitado_consumo_agua_plasticos;
            $total_evitado_emitir_admosfera = $evitado_emitir_admosfera + $evitado_emitir_admosfera_plasticos + $evitado_emitir_admosfera_aluminio + $evitado_c02_anio;
            $total_evitado_consumo_kwh = $evitado_consumo_kwh + $evitado_consumo_kwh_plasticos + $evitado_consumo_kwh_aluminio;
        @endphp
        
        <!-- PAPEL Y CARTÓN -->
        @if($suma_papel > 0)
        <div class="impacto-categoria">PAPEL Y CARTÓN - Tu Contribución: {{ number_format($suma_papel, 2) }} Kg</div>
        
        @if($evitado_tala_arboles > 0)
            <div class="impacto-item">• Has evitado la tala de {{ round($evitado_tala_arboles, 2) }} árboles adultos</div>
        @endif
        @if($oxigeno_producido > 0)
            <div class="impacto-item">• Tu aporte produce el oxígeno necesario para {{ round($oxigeno_producido, 0) }} personas</div>
        @endif
        @if($co2_captado > 0)
            <div class="impacto-item">• Tu contribución captará {{ number_format($co2_captado, 1) }} Kg de CO2 al año</div>
        @endif
        @if($evitado_consumo_agua > 0)
            <div class="impacto-item">• Has evitado el consumo de {{ number_format($evitado_consumo_agua, 0) }} litros de agua</div>
        @endif
        @if($evitado_consumo_kwh > 0)
            <div class="impacto-item">• Has evitado el consumo de {{ number_format($evitado_consumo_kwh, 0) }} KWh de energía</div>
        @endif
        @if($evitado_c02_anio > 0)
            <div class="impacto-item">• Has evitado la emisión de {{ round($evitado_c02_anio, 0) }} Kg de CO2 al año</div>
        @endif
        @if($evitado_emitir_admosfera > 0)
            <div class="impacto-item">• Has evitado emitir a la atmósfera {{ round($evitado_emitir_admosfera, 0) }} Kg de CO2</div>
        @endif
        @endif
        
        <!-- PLÁSTICOS -->
        @if($total_plasticos_calculo > 0)
        <div class="impacto-categoria">PLÁSTICOS - Tu Contribución: {{ number_format($total_plasticos_calculo, 2) }} Kg</div>
        
        @if($evitado_consumo_agua_plasticos > 0)
            <div class="impacto-item">• Has evitado el consumo de {{ number_format($evitado_consumo_agua_plasticos, 0) }} litros de agua</div>
        @endif
        @if($evitado_emitir_admosfera_plasticos > 0)
            <div class="impacto-item">• Has evitado emitir a la atmósfera {{ round($evitado_emitir_admosfera_plasticos, 0) }} Kg de CO2</div>
        @endif
        @if($evitado_consumo_kwh_plasticos > 0)
            <div class="impacto-item">• Has evitado el consumo de {{ number_format($evitado_consumo_kwh_plasticos, 0) }} KWh de energía</div>
        @endif
        @if($recuperado_botellas_pet > 0)
            <div class="impacto-item">• Has recuperado el equivalente a {{ number_format($recuperado_botellas_pet, 0) }} botellas PET de 500 ml</div>
        @endif
        @endif
        
        <!-- ALUMINIO (LATAS) -->
        @if($total_calculo_aluminio > 0)
        <div class="impacto-categoria">LATAS DE ALUMINIO - Tu Contribución: {{ number_format($total_calculo_aluminio, 2) }} Kg</div>
        
        @if($evitado_explotar_bauxita > 0)
            <div class="impacto-item">• Has evitado explotar {{ round($evitado_explotar_bauxita, 0) }} Kg de Bauxita (mineral del aluminio)</div>
        @endif
        @if($recuperado_latas_bebida > 0)
            <div class="impacto-item">• Has recuperado el equivalente a {{ number_format($recuperado_latas_bebida, 0) }} latas de bebida de 355 ml</div>
        @endif
        @if($evitado_emitir_admosfera_aluminio > 0)
            <div class="impacto-item">• Has evitado emitir a la atmósfera {{ round($evitado_emitir_admosfera_aluminio, 0) }} Kg de CO2</div>
        @endif
        @if($evitado_consumo_kwh_aluminio > 0)
            <div class="impacto-item">• Has evitado el consumo de {{ number_format($evitado_consumo_kwh_aluminio, 0) }} KWh de energía</div>
        @endif
        @endif
        
        <!-- IMPACTO EN RELLENOS SANITARIOS -->
        @if($suma_desechos_carros_recolector > 0)
        <div class="impacto-categoria">TU REDUCCIÓN DE DESECHOS AL RELLENO SANITARIO</div>
        <div class="impacto-item">• Has evitado enviar {{ number_format($suma_desechos_carros_recolector, 2) }} carros de basura al relleno sanitario</div>
        <div style="font-size: 8px; color: #666; margin: 3px 0 0 6px; font-style: italic;">
            *Cálculo basado en {{ number_format(($suma_papel + $total_plasticos_calculo + $total_calculo_aluminio), 2) }} Kg total de tus materiales reciclados
        </div>
        @endif
        
        <!-- TU RESUMEN TOTAL DE IMPACTO -->
        @if($total_evitado_consumo_agua > 0 || $total_evitado_emitir_admosfera > 0 || $total_evitado_consumo_kwh > 0)
        <div class="impacto-total">
            <strong>TU IMPACTO AMBIENTAL TOTAL</strong><br><br>
            @if($total_evitado_consumo_agua > 0)
                <strong>Agua que Has Ahorrado:</strong> {{ number_format($total_evitado_consumo_agua, 0) }} litros<br>
            @endif
            @if($total_evitado_emitir_admosfera > 0)
                <strong>CO2 que Has Evitado:</strong> {{ number_format($total_evitado_emitir_admosfera, 0) }} Kg<br>
            @endif
            @if($total_evitado_consumo_kwh > 0)
                <strong>Energía que Has Ahorrado:</strong> {{ number_format($total_evitado_consumo_kwh, 0) }} KWh<br>
            @endif
            @if($evitado_tala_arboles > 0)
                <strong>Árboles que Has Salvado:</strong> {{ round($evitado_tala_arboles, 2) }} árboles adultos<br>
            @endif
            @if($suma_desechos_carros_recolector > 0)
                <strong>Carros de Basura que Has Evitado:</strong> {{ number_format($suma_desechos_carros_recolector, 2) }} carros
            @endif
        </div>
        @endif
        
        @if(($suma_papel + $total_plasticos_calculo + $total_calculo_aluminio) == 0)
        <div style="text-align: center; color: #888; font-style: italic; padding: 20px;">
            No se registraron materiales reciclados en este período para calcular tu impacto ambiental.
        </div>
        @endif
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>¡Gracias por contribuir al cuidado del medio ambiente!</p>
        <p>Reporte personal generado automáticamente por el sistema ADRI</p>
    </div>
</body>

</html>