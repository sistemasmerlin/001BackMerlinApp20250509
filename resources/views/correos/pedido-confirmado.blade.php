<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Pedido Confirmado</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      color: #333;
      margin: 20px;
    }
    h2 {
      color: #444;
    }
    .tabla {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    .tabla th, .tabla td {
      border: 1px solid #ccc;
      padding: 8px 10px;
      text-align: left;
    }
    .tabla th {
      background-color: #f9f9f9;
    }
    .seccion {
      margin-bottom: 25px;
    }
  </style>
</head>
<body>

@foreach($encabezados as $encabezado)

  <h2>Confirmación de Pedido</h2>

  <p>Hola {{ $encabezado->razon_social ?? 'Cliente' }},</p>

  <p>Tu pedido <strong>{{ $encabezado->documento ?? '---' }}</strong> ha sido recibido correctamente, a continuación se encuentra el resumen.</p>

  <div class="seccion">
    <h4>Datos del Pedido</h4>

    <p><strong>NIT:</strong> {{ $encabezado->nit_cliente ?? '-' }}</p>
    <p><strong>Cliente:</strong> {{ $encabezado->razon_social ?? '-' }}</p>
    <p><strong>Dirección:</strong> {{ $encabezado->direccion ?? '-' }} {{ $encabezado->ciudad ?? '-' }} {{ $encabezado->depto ?? '-' }}</p>
    <p><strong>Notas:</strong> {{ $encabezado->notas ?? 'N/A' }}</p>
  </div>

  <div class="seccion">
    <h4>Detalle del Pedido</h4>
    <table class="tabla">
      <thead>
        <tr>
          <th>#</th>
          <th>Referencia</th>
          <th>Descripción</th>
          <th>Unidades</th>
          <th>Precio</th>
          <th>% Desc</th>
          <th>Descuento Valor</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($detalles as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->referencia }}</td>
            <td>{{ $item->descripcion }}</td>
            <td style="text-align: center">{{ $item->cantidad }}</td>
            <td style="text-align: right">${{ number_format($item->valor_unitario, 0, ',', '.') }}</td>
            <td style="text-align: center">{{ number_format($item->descuento, 0, ',', '.') }}%</td>
            <td style="text-align: right">${{ number_format($item->total_descuento, 0, ',', '.') }}</td>
            <td style="text-align: right">${{ number_format($item->subtotal, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="7">Unidades Totales</th><td style="text-align: right">10</td>
        </tr>
        <tr>
          <th colspan="7">Subtotal Neto</th><td style="text-align: right">${{ number_format($subtotal_pedido, 0, ',', '.') }}</td>
        </tr>
        <tr>
          <th colspan="7">Descuento</th><td style="text-align: right">${{ number_format($subtotal_descuento, 0, ',', '.') }}</td>
        </tr>
        <tr>
          <th colspan="7">Subtotal</th><td style="text-align: right">${{ number_format($encabezado->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
          <th colspan="7">IVA</th><td style="text-align: right">${{ number_format((($encabezado->subtotal * 1.19) - $encabezado->subtotal), 0, ',', '.') }}</td>
        </tr>
        <tr>
          <th colspan="7">Total</th><td style="text-align: right">${{ number_format(($encabezado->subtotal * 1.19), 0, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>
  </div>

  <p><strong>Notas del pedido:</strong> {{ $encabezado->notas ?? 'Sin notas adicionales.' }}</p>

  @endforeach

  <p>Precios sujetos a cambios sin previo aviso - Por favor no responder este correo.</p>
  <p>Gracias por tu pedido - <strong> Gees Global S.A.S - 2025</strong></p>

</body>
</html>
