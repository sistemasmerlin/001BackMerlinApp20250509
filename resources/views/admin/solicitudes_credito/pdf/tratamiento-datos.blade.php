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
        .controlled {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
            margin-right: 70px;
            border: none;
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

            <td class="logo" rowspan="3">
                <img src="{{ public_path('storage/logo/logo-merlin.png') }}" alt="Merlin">
            </td>

            <td class="meta title-main">
                DIRECCIONAMIENTO ESTRATÉGICO
            </td>

            <td class="meta code">
                Código:&nbsp;&nbsp;&nbsp; Form-Destr-007
            </td>
        </tr>

        <tr>
            <td class="meta">
                POLÍTICA
            </td>

            <td class="meta code">
                Versión N°:&nbsp;&nbsp;&nbsp; 1
            </td>
        </tr>

        <tr>
            <td class="meta title-doc">
                AUTORIZACIÓN PARA EL TRATAMIENTO DE<br>
                DATOS PERSONALES DE CLIENTES,<br>
                PROVEEDORES, CONTRATISTAS, CLIENTES<br>
                FUTUROS Y OTROS TERCEROS GEES GLOBAL<br>
                S.A.S.
            </td>

            <td class="meta code page">
                Página:&nbsp;&nbsp;&nbsp; 1 de 3
            </td>
        </tr>
    </table>
</div>

    <div class="controlled">
        DOCUMENTO CONTROLADO
    </div>

    <br>

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
    manifiesto que de conformidad con la Ley 1581 de 2012, el Decreto Reglamentario 1377 de 2013 y la política interna de manejo 
    de la información implementada por <strong>GEES GLOBAL S.A.S.</strong>, sociedad legalmente constituida e identificada con NIT.
    901368337-5, con domicilio principal en la ciudad de Bogotá y las demás normas concordantes, a través de las cuales se
    establecen disposiciones generales en materia de habeas data y se regula el tratamiento de la información que contenga
    datos personales, me permito declarar, que:
</div>

<div class="block">
 He sido informado en cuanto a que debo autorizar de manera libre, voluntaria, previa, explícita, informada e inequívoca a
<strong>GEES GLOBAL S.A.S.</strong>, para que en los términos legalmente establecidos realice la recolección, almacenamiento, uso,
circulación, cesión, supresión, archivo, copia, análisis y consulta de los datos que sean recolectados, todos relacionados con
el ejercicio de su objeto social y en virtud de las relaciones legales, contractuales y comerciales y/o de cualquier otra que
surja.
</div>

<div class="block">
He sido informado en cuanto a que debo autorizar a que dicha información sea compartida con terceros para los efectos
propios de la labor o actividad comercial o de negocios que desarrollé, desarrolló o llegaré a desarrollar en favor de GEES
GLOBAL S.A.S., Igualmente me fue informado que los datos sensibles y/o personales podrán ser alojados en herramientas
tecnológicas, software y bases de datos adquiridas o licenciadas por <strong>GEES GLOBAL S.A.S.</strong>
</div>

<div class="block">
He sido informado en cuanto a que dicha autorización para adelantar el tratamiento de mis datos personales, se extiende
durante la totalidad del tiempo en el que pueda llegar consolidarse un vínculo o este persista por cualquier circunstancia con
<strong>GEES GLOBAL S.A.S.</strong>, y con posterioridad al finiquito del mismo, siempre que tal tratamiento se encuentre relacionado con
las finalidades para las cuales los datos personales, fueron inicialmente suministrados.
</div>

<div class="block">
De igual forma, declaro que me han sido informados los derechos que el ordenamiento legal y la jurisprudencia, conceden
al titular de los datos personales, así: (i) Conocer, actualizar y rectificar datos personales frente a los responsables o
encargados del tratamiento. Este derecho se podrá ejercer, entre otros frente a datos parciales, inexactos, incompletos,
fraccionados, que induzcan a error, o aquellos cuyo tratamiento esté expresamente prohibido o no haya sido autorizado; (ii)
solicitar prueba de la autorización otorgada al responsable del tratamiento salvo cuando expresamente se exceptúe como
requisito para el tratamiento; (iii) ser informado por el responsable del tratamiento o el encargado del tratamiento, previa
solicitud, respecto del uso que le ha dado a mis datos personales; (iv) presentar ante la Superintendencia de Industria y
Comercio quejas por infracciones al régimen de protección de datos personales; (v) revocar la autorización y/o solicitar la
supresión del dato personal cuando en el tratamiento no se respeten los principios, derechos y garantías constitucionales y
legales, (vi) acceder en forma gratuita a mis datos personales que hayan sido objeto de Tratamiento.
</div>

<div class="block">
Haber sido informado sobre el carácter facultativo de otorgar consentimiento para el tratamiento de datos sensible, sobre
los derechos que me asisten como titular de los datos y los canales habilitados por la sociedad para ejercer mis derechos.
</div>

<div class="block">
Haber leído cuidadosamente el contenido de esta autorización y haberla comprendido a cabalidad, razón por la cual entiendo
sus alcances e implicaciones. Así mismo, certifico que me fue informado que la política de manejo de datos personales
adoptada por <strong>GEES GLOBAL S.A.S.</strong>, se encuentran en el portal web: https://merlinrod.com y allí se podrá consultar cualquier
actualización a la misma.
</div>

<div class="block">
Conocer que en los casos en que requiera ejercer los derechos anteriormente mencionados, la solicitud respectiva podrá
ser elevada a través de los mecanismos dispuestos para tal fin por <strong>GEES GLOBAL S.A.S.</strong>, que corresponden a los siguientes:
<br>

<ul>
  <li><strong>Razón social:</strong> GEES GLOBAL S.A.S.</li>
  <li><strong>NIT:</strong> 901368337-5</li>
  <li><strong>Página Web:</strong> <a href="https://merlinrod.com" target="_blank">https://merlinrod.com</a></li>
  <li><strong>Celular:</strong> +57 3124618483</li>
  <li><strong>Correo electrónico:</strong> <a href="mailto:cosorio@merlinrod.com">cosorio@merlinrod.com</a></li>
  <li><strong>Domicilio principal y dirección de notificación judicial:</strong> Edificio Ecotek Cl 99 # 10 57 Bogotá</li>
</ul>

</div>

<div class="block">
<strong>RECOLECCIÓN DE DATOS PERSONALES DE LOS NIÑOS, NIÑAS Y ADOLESCENTES Y/O DATOS SENSIBLES: </strong> En el
evento de llegar a solicitar información sobre este tipo de datos, se deja constancia que la respuesta es totalmente facultativa.
Lo anterior de conformidad con el artículo 5 de la Ley 1581 de 2012. Los datos personales de niños, niñas y adolescentes
recolectados, serán tratados de conformidad con lo estipulado en el Decreto 1377 de 2013.
</div>

<div class="block">
<strong>FINALIDADES PARA EL TRATAMIENTO GENERAL DE INFORMACIÓN</strong>
<br>
1) Cumplir con las solicitudes de productos y/o de servicios realizados por <strong>GEES GLOBAL S.A.S.</strong> 2) Realizar gestión,
verificación y manejo de información financiera, contable, fiscal, administrativa y de facturación; 3) Verificar toda la información
contable, crediticia y financiera suministrada a la empresa. 4) Para consultar, reportar y obtener de las centrales de información
y de las demás entidades autorizadas para tales efectos, la información relacionada con el comportamiento crediticio de la
empresa o establecimiento en representación, producto de toda clase de operaciones que efectúe o haya efectuado con
entidades del sector financiero, y que en general, sirvan de referencia o base para el análisis del comportamiento crediticio.
<br> 
Así mismo los faculto para verificar las referencias con todas aquellas entidades o personas que estimen conveniente. Esta
autorización comprende el reporte de la información referente a la realización de pagos de obligaciones y su permanencia
hasta que <strong>GEES GLOBAL S.A.S.</strong> lo considere necesario.. 5) Consultar en listas restrictivas y vinculantes a todas las personas
relacionadas y los que posteriormente se suministren, por lo cual la información podrá ser transferida a terceros, tales como
entidades financieras, centrales de riesgo, autoridades judiciales, listas de terrorismo y narcotráfico; 6) Realizar facturación
electrónica. 7) Realizar gestión y manejo de las relaciones contractuales establecidas o por establecer con los terceros y los
vinculados cuyo objetivo sea el ofrecimiento de productos y/o servicios, así como de noticias y nuevos lanzamientos por
cualquier canal de comunicación; 8) Realizar gestión para la atención de PQRS, campañas de actualización de datos y
cambios, encuestas de opinión; 9) Captura de datos biométricos (datos sensibles) a través de registros fotográficos, video o
imágenes para ser publicados en nuestro portal web, redes sociales, documentos internos, en puntos de venta propios o de
distribuidores, para fines administrativos, comerciales y de publicidad; 10) Transmitir y/o transferir todos los datos personales
a terceras personas según se requiera la vinculación contractual; 11) Atender visitas en el establecimiento de comercio. 12)
Cumplimiento de decisiones judiciales y disposiciones administrativas legales, fiscales y regulatorias.13) Envío de
promocionales por vía emails, mensajes de texto, whatsapp, llamadas; o redes sociales. 14) Publicidad de nuestra app por
vía emails, mensajes de texto, whatsapp, llamada; redes sociales. 15). Encuestas sobre: infraestructura, productos, usabilidad
de nuestro portal web, y atención ofrecida en: cualquier punto, centros autorizados de servicio, o cualquier canal de
distribución, entre otros, por vía emails, mensajes de texto, whatsapp, llamadas; redes sociales. 16). Envío de
correspondencia, llamadas, correos electrónicos, mensajes, whatsapp, de sus distintos programas en desarrollo de
actividades publicitarias, promocionales, de mercadeo (principalmente para planes de fidelidad y relacionales), de ejecución
de ventas o estudios de mercado enfocados a la actividad económica de la empresa. 17). Para compartir información con
aliados comerciales para el ofrecimiento de servicios con beneficios para sus clientes. 18) Solicitar, consultar, procesar,
suministrar, reportar o divulgar a cualquier entidad válidamente autorizada para manejar o administrar bases de datos,
incluidas las entidades gubernamentales, información contenida en este formulario y demás información relativa al
cumplimiento de mis obligaciones legalmente adquiridas y las establecidas en las finalidades para el tratamiento general de
información. 19) Tratar todos los datos que aquí se suministran y de los que posteriormente se suministren en desarrollo de
la vinculación contractual y/o comercial de los cuales sea titular quien suscribe en nombre propio y/o que correspondan a
todos los tipos de terceros vinculados, frente a los cuales el firmante declara haber obtenido autorización para el tratamiento
en los términos de la normatividad vigente.
</div>

<div class="block">
<strong>VIGENCIA: </strong>La vigencia de la autorización para tratamiento de datos personales corresponderá al término que dure la relación
entre las partes y/o al término que disponga la ley aplicable según la naturaleza de los datos.
</div>

<div class="block">
<strong>ACUERDO DE PROTECCIÓN Y TRATAMIENTO DE DATOS PERSONALES:</strong> En relación con los datos personales que las
partes se hayan comunicado o llegaren a comunicar como consecuencia o con ocasión de un vínculo o relación contractual
vigente, estas se obligan a: 1) Efectuar el tratamiento de los datos personales de conformidad con las Políticas de Protección
y Tratamiento de Datos Personales de cada una de ellas y la normatividad vigente que resulte aplicable; 2) Salvaguardar la
seguridad de las bases de datos en las que se almacenen los datos personales, empleando medidas de seguridad razonables;
3) Guardar máxima confidencialidad respecto de los datos personales a los que tenga acceso en virtud de la relación
contractual que las une, 4) realizar el tratamiento exclusivamente para las finalidades autorizadas; 5) cumplir con todas las
obligaciones que resulten a su cargo, en la calidad de responsable o encargado del tratamiento de los datos personales,
según el caso.
</div>

<div class="block">
<strong>CERTIFICACION: </strong>Certifico que, la información que aquí consigno, la he suministrado de manera voluntaria y es verídica, por
tanto, me obligo para con GEES GLOBAL S.A.S a mantener actualizada dicha información y me comprometo a reportar, los
cambios que se hayan generado respecto de la información aquí contenida. De igual manera, certifico que los recursos que
manejo o mis recursos propios provienen de actividades licitas y no provienen de ninguna actividad ilícita de las contempladas
en el Código Penal Colombiano o en cualquier norma que lo modifique o adicione. No admitiré que terceros efectúen depósitos
a nombre mío, con fondos provenientes de las actividades ilícitas contempladas en el Código Penal Colombiano o en cualquier
norma que lo modifique o adicione, ni efectuaré transacciones destinadas a tales actividades o a favor de personas
relacionadas con las mismas. Autorizo a terminar la relación comercial con <strong>GEES GLOBAL S.A.S.</strong>, en el caso de infracción
de cualquiera de los numerales contenidos en este documento eximiendo a <strong>GEES GLOBAL S.A.S.</strong> de toda responsabilidad
que se derive por información errónea, falsa o inexacta que yo hubiere proporcionado en este documento o de la violación
del mismo.
</div>

<div class="block">

</div>

<div class="block">
    En virtud de lo anterior, autorizo __X__  a <strong>GEES GLOBAL S.A.S.</strong> para que realice tratamiento de mis datos personales, conforme a las finalidades descritas anteriormente.
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