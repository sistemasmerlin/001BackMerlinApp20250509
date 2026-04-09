<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tratamiento de Datos</title>
    <style>
        @page { margin: 26px 28px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
            line-height: 1.45;
        }
        .header {
            border: 1px solid #222;
            margin-bottom: 14px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header td {
            border: 1px solid #222;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .title {
            font-weight: bold;
            text-align: center;
        }
        .meta {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
        }
        .fieldline {
            margin-bottom: 8px;
        }
        .field {
            display: inline-block;
            border-bottom: 1px solid #222;
            min-width: 180px;
            padding: 0 4px 2px 4px;
        }
        .block {
            margin-top: 12px;
            text-align: justify;
        }
        .small {
            font-size: 10px;
        }
        .sign-box {
            margin-top: 24px;
        }
        .sign-line {
            margin-top: 18px;
            width: 220px;
            border-bottom: 1px solid #222;
            height: 18px;
        }
    </style>
</head>
<body>

<div class="header">
    <table>
        <tr>
            <td class="meta" style="width:20%;">DIRECCIONAMIENTO ESTRATÉGICO</td>
            <td class="meta title" rowspan="2">AUTORIZACIÓN PARA EL TRATAMIENTO DE DATOS PERSONALES DE CLIENTES, PROVEEDORES, CONTRATISTAS, CLIENTES FUTUROS Y OTROS TERCEROS GEES GLOBAL S.A.S.</td>
            <td class="meta" style="width:18%;">Código: Form-Destr-007</td>
        </tr>
        <tr>
            <td class="meta">POLÍTICA</td>
            <td class="meta">Versión N°: 1</td>
        </tr>
    </table>
</div>

<div class="fieldline">
    Ciudad <span class="field">{{ $solicitud->autorizacion_ciudad }}</span>
    &nbsp;&nbsp;&nbsp;
    Fecha <span class="field">{{ optional($solicitud->autorizacion_fecha)->format('d/m/Y') }}</span>
</div>

<div class="block">
    Yo, <span class="field" style="min-width: 420px;">{{ $solicitud->autorizacion_nombre_1 }}</span>,
    mayor de edad, identificado(a) con documento de identidad N°. 
    <span class="field" style="min-width: 120px;">{{ $solicitud->autorizacion_documento_1 }}</span>
    expedida en
    <span class="field" style="min-width: 150px;">{{ $solicitud->autorizacion_lugar_expedicion_1 }}</span>,
    actuando en nombre propio y/o como Representante Legal de
    <span class="field" style="min-width: 260px;">{{ $solicitud->autorizacion_razon_social ?: $solicitud->razon_social }}</span>,
    con NIT
    <span class="field" style="min-width: 120px;">{{ $solicitud->autorizacion_nit_cc ?: $solicitud->nit_cc }}</span>,
    manifiesto que de conformidad con la Ley 1581 de 2012, el Decreto Reglamentario 1377 de 2013 y la política interna de manejo de la información implementada por GEES GLOBAL S.A.S., autorizo el tratamiento de mis datos personales conforme a las finalidades descritas en el presente documento. 
</div>

<div class="block">
    He sido informado de manera libre, previa, expresa e informada sobre la recolección, almacenamiento, uso, circulación, consulta, análisis, cesión, supresión y demás tratamientos aplicables a mis datos personales, en el marco de relaciones comerciales, contractuales, administrativas y legales con GEES GLOBAL S.A.S. También he sido informado de mis derechos como titular de los datos y de los canales habilitados para ejercerlos.
</div>

<div class="block">
    Finalidades del tratamiento: gestión financiera, contable, fiscal, administrativa y de facturación; verificación de información crediticia y financiera; consulta y reporte ante centrales de riesgo; verificación de referencias; facturación electrónica; atención de PQRS; campañas de actualización de datos; envío de promociones, mensajes, llamadas, WhatsApp y publicidad; uso de registros fotográficos o audiovisuales; y demás finalidades comerciales y contractuales descritas por la compañía.
</div>

<div class="block">
    Así mismo, certifico que la información suministrada es verídica y que me obligo a mantenerla actualizada, reportando oportunamente cualquier cambio. También declaro que los recursos que manejo provienen de actividades lícitas y autorizo a GEES GLOBAL S.A.S. a dar por terminada la relación comercial en caso de información errónea, falsa o inexacta.
</div>

<div class="block">
    En virtud de lo anterior, autorizo __X__  a GEES GLOBAL S.A.S. para que realice tratamiento de mis datos personales, conforme a las finalidades descritas anteriormente.
</div>

<div class="sign-box">
    <div class="fieldline">
        Nombre y Apellidos Completos:
        <span class="field" style="min-width: 360px;">{{ $solicitud->autorizacion_nombre_2 ?: $solicitud->autorizacion_nombre_1 }}</span>
    </div>

    <div class="fieldline">
        Documento de Identificación N°:
        <span class="field" style="min-width: 160px;">{{ $solicitud->autorizacion_documento_2 ?: $solicitud->autorizacion_documento_1 }}</span>
        expedida en
        <span class="field" style="min-width: 140px;">{{ $solicitud->autorizacion_lugar_expedicion_2 ?: $solicitud->autorizacion_lugar_expedicion_1 }}</span>
    </div>

    <div class="sign-line"></div>
    <div class="small">Firma</div>

    <div class="fieldline" style="margin-top: 14px;">
        Teléfono fijo <span class="field" style="min-width: 120px;">{{ $solicitud->autorizacion_telefono_fijo }}</span>
        Número Celular <span class="field" style="min-width: 140px;">{{ $solicitud->autorizacion_celular }}</span>
    </div>

    <div class="fieldline">
        Dirección Correo electrónico
        <span class="field" style="min-width: 300px;">{{ $solicitud->autorizacion_correo }}</span>
    </div>

    <div class="fieldline">
        Dirección domicilio
        <span class="field" style="min-width: 360px;">{{ $solicitud->autorizacion_direccion }}</span>
    </div>
</div>

</body>
</html>