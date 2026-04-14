<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Crédito</title>
    <style>
        @page { margin: 22px 24px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }
        .page {
            width: 100%;
        }
        .header-table,
        .form-table,
        .simple-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td,
        .header-table th,
        .form-table td,
        .form-table th,
        .simple-table td,
        .simple-table th {
            border: 1px solid #222;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .header-gray,
        .section-title {
            background: #7d7d7d;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        .top-meta {
            font-size: 10px;
            text-align: center;
            font-weight: bold;
        }
        .logo-box {
            text-align: center;
            font-size: 22px;
            color: #d71920;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .label {
            font-weight: bold;
        }
        .line {
            border-bottom: 1px solid #222;
            display: inline-block;
            min-width: 120px;
            height: 14px;
        }
        .grid-2 {
            width: 100%;
            border-collapse: separate;
            border-spacing: 14px 10px;
        }
        .mini-box {
            width: 100%;
            border: 2px solid #222;
            border-collapse: collapse;
        }
        .mini-box th, .mini-box td {
            border: 1px solid #222;
            padding: 5px 6px;
        }
        .mini-box th {
            background: #8d8d8d;
            color: #fff;
            text-align: center;
            font-weight: bold;
        }
        .mt-12 { margin-top: 12px; }
        .mt-18 { margin-top: 18px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .small { font-size: 10px; }
        .firma {
            margin-top: 24px;
            text-align: center;
            font-weight: bold;
        }
        .firma-linea {
            margin: 0 auto 6px auto;
            width: 260px;
            border-bottom: 1px solid #222;
            height: 20px;
        }
        .notes td {
            vertical-align: top;
            line-height: 1.4;
        }
    </style>
</head>
<body>
<div class="page">

    <table class="header-table">
        <tr>
            <td rowspan="4" style="width:28%;" class="logo-box">
                <img src="{{ public_path('storage/logo/logo-merlin.png') }}" alt="Merlin" style="max-width: 140px; max-height: 60px;">
            </td>
            <td class="top-meta">CARTERA</td>
            <td class="top-meta" style="width:20%;">Código:</td>
            <td class="top-meta" style="width:18%;">Form-Cart-001</td>
        </tr>
        <tr>
            <td class="top-meta">FORMATO</td>
            <td class="top-meta">Versión No:</td>
            <td class="top-meta">6</td>
        </tr>
        <tr>
            <td class="top-meta">SOLICITUD DE CRÉDITO</td>
            <td class="top-meta">Fecha Aprobación:</td>
            <td class="top-meta">27/11/2024</td>
        </tr>
        <tr>
            <td class="top-meta" colspan="3">DOCUMENTO CONTROLADO</td>
        </tr>
    </table>

    <table class="form-table mt-12">
        <tr><th colspan="4" class="section-title">1. DATOS DE LA EMPRESA</th></tr>
        <tr>
            <td><span class="label">Ciudad:</span> {{ $solicitud->ciudad }}</td>
            <td><span class="label">Fecha:</span> {{ optional($solicitud->fecha_solicitud)->format('d/m/Y') }}</td>
            <td><span class="label">NIT / C.C.:</span> {{ $solicitud->nit_cc }}</td>
            <td><span class="label">Celular:</span> {{ $solicitud->celular }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Razón Social:</span> {{ $solicitud->razon_social }}</td>
            <td colspan="2"><span class="label">Nombre Comercial:</span> {{ $solicitud->nombre_comercial }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Representante Legal:</span> {{ $solicitud->representante_legal }}</td>
            <td colspan="2"><span class="label">No Identificación:</span> {{ $solicitud->identificacion_representante }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Dirección del negocio:</span> {{ $solicitud->direccion_negocio }}</td>
            <td><span class="label">Barrio:</span> {{ $solicitud->barrio }}</td>
            <td><span class="label">Correo:</span> {{ $solicitud->correo_electronico }}</td>
        </tr>
        <tr>
            <td><span class="label">Teléfono fijo:</span> {{ $solicitud->telefono_fijo }}</td>
            <td><span class="label">Departamento:</span> {{ $solicitud->depto }}</td>
            <td colspan="2"></td>
        </tr>
    </table>

    <table class="form-table mt-12">
        <tr><th colspan="3" class="section-title">2. CONTACTOS SECUNDARIOS</th></tr>
        <tr>
            <td><span class="label">Contacto Compras:</span> {{ $solicitud->contacto_compras }}</td>
            <td><span class="label">Teléfono:</span> {{ $solicitud->telefono_compras }}</td>
            <td><span class="label">Correo:</span> {{ $solicitud->correo_compras }}</td>
        </tr>
        <tr>
            <td><span class="label">Contacto Tesorería:</span> {{ $solicitud->contacto_tesoreria }}</td>
            <td><span class="label">Teléfono:</span> {{ $solicitud->telefono_tesoreria }}</td>
            <td><span class="label">Correo:</span> {{ $solicitud->correo_tesoreria }}</td>
        </tr>
        <tr>
            <td><span class="label">Contacto Factura Electrónica:</span> {{ $solicitud->contacto_factura_electronica }}</td>
            <td><span class="label">Teléfono:</span> {{ $solicitud->telefono_factura_electronica }}</td>
            <td><span class="label">Correo:</span> {{ $solicitud->correo_factura_electronica }}</td>
        </tr>
    </table>

    <table class="form-table mt-12">
        <tr><th colspan="3" class="section-title">3. RETENCIONES QUE APLICA</th></tr>
        <tr>
            <td><span class="label">Retención en la fuente:</span> {{ $solicitud->rte_fuente ? 'SI' : 'NO' }}</td>
            <td><span class="label">Rete IVA:</span> {{ $solicitud->rte_iva ? 'SI' : 'NO' }}</td>
            <td><span class="label">Rete ICA:</span> {{ $solicitud->rte_ica ? 'SI' : 'NO' }}</td>
        </tr>
    </table>

    <table class="simple-table mt-12">
        <tr><th colspan="6" class="section-title">4. REFERENCIAS COMERCIALES</th></tr>
        <tr>
            <th>#</th>
            <th>Empresa</th>
            <th>NIT</th>
            <th>Ciudad</th>
            <th>Teléfono</th>
            <th>Cupo de Crédito</th>
        </tr>
        @forelse($solicitud->referencias as $i => $ref)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $ref->empresa }}</td>
                <td>{{ $ref->nit }}</td>
                <td>{{ $ref->ciudad }}</td>
                <td>{{ $ref->telefono }}</td>
                <td class="text-right">$ {{ number_format((float) $ref->cupo_credito, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">Sin referencias registradas</td>
            </tr>
        @endforelse
    </table>

    <table class="grid-2 mt-12">
        <tr>
            <td style="width:33%; vertical-align:top;">
                <table class="mini-box">
                    <tr><th colspan="2">ANTIGÜEDAD COMERCIAL</th></tr>
                    <tr><td  colspan="2"style="width:70%;">{{ $solicitud->antiguedad_comercial }}</td></tr>
                    <tr><td>TIEMPO</td><td>{{ $solicitud->tiempo_antiguedad }}</td></tr>
                </table>
            </td>
            <td style="width:33%; vertical-align:top;">
                <table class="mini-box">
                    <tr><th colspan="2">TIPO DE NEGOCIO</th></tr>
                    <tr><td colspan="2">{{ is_array($solicitud->tipo_negocio) ? implode(', ', $solicitud->tipo_negocio) : $solicitud->tipo_negocio }}</td></tr>
                </table>
            </td>
            <td style="width:33%; vertical-align:top;">
                <table class="mini-box">
                    <tr><th colspan="2">PUNTOS DE VENTA</th></tr>
                    <tr><td colspan="2">{{ $solicitud->puntos_venta }}</td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;">
                <table class="mini-box">
                    <tr><th colspan="2">CANAL / SUBCANAL</th></tr>
                    <tr><td>Canal Tradicional</td><td>{{ $solicitud->canal_tradicional }}</td></tr>
                    <tr><td>Canal Corporativo</td><td>{{ $solicitud->canal_corporativo }}</td></tr>
                </table>
            </td>
            <td style="vertical-align:top;">
                <table class="mini-box">
                    <tr><th colspan="2">NO DE EMPLEADOS</th></tr>
                    <tr><td colspan="2">{{ $solicitud->numero_empleados }}</td></tr>
                </table>
            </td>
            <td></td>
        </tr>
    </table>

    <table class="simple-table mt-18">
        <tr><th colspan="4" class="section-title">6. DATOS DE LAS DIRECCIONES ADICIONALES DE ENTREGA DEL CLIENTE</th></tr>
        <tr>
            <th>Contacto</th>
            <th>Dirección</th>
            <th>Ciudad / Municipio</th>
            <th>Teléfono</th>
        </tr>
        @forelse($solicitud->direcciones as $dir)
            <tr>
                <td>{{ $dir->contacto }}</td>
                <td>{{ $dir->direccion }}</td>
                <td>{{ $dir->ciudad }}</td>
                <td>{{ $dir->telefono }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">Sin direcciones adicionales registradas</td>
            </tr>
        @endforelse
    </table>

    <table class="form-table mt-12">
        <tr>
            <td class="text-center">
                <span class="label">VENTAS PROYECTADAS AL CLIENTE POR MES</span><br>
                $ {{ number_format((float) $solicitud->ventas_proyectadas_mes, 0, ',', '.') }}
            </td>
            <td class="text-center">
                <span class="label">CUPO SUGERIDO</span><br>
                $ {{ number_format((float) $solicitud->cupo_sugerido, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <table class="simple-table mt-12 notes">
        <tr>
            <td style="width:50%;">
                <strong>Recuerde solicitar la siguiente documentación para la vinculación del cliente:</strong><br><br>
                Formato autorización para el tratamiento de datos personales.<br>
                Pagaré y carta de instrucciones diligenciados.<br>
                Cámara de Comercio no superior a 90 días.<br>
                RUT con fecha de impresión máximo de 90 días.<br>
                Fotocopia de la cédula del representante legal.<br>
                Fotocopias de facturas de compra, mínimo de 2 proveedores, no mayor a 2 meses.<br>
                Referencias comerciales por escrito cuando aplique.<br>
                Estados financieros para cupos iguales o superiores a $25.000.000.
            </td>
            <td style="width:50%;">
                <strong>Documentos adicionales cuando aplique:</strong><br><br>
                Formato de carta de recomendación de persona que actúa como fiador.<br>
                Fotocopia de la cédula de persona que actúa como fiador.<br><br>
                <strong>Anexar fotografías</strong><br>
                En caso de requerirse por la dirección comercial, se deben adjuntar fotografías del establecimiento y enviarlas por correo.
            </td>
        </tr>
    </table>

    <div class="firma">
        <div class="firma-linea"></div>
        Firma y Cédula del ASESOR COMERCIAL
    </div>

</div>
</body>
</html>