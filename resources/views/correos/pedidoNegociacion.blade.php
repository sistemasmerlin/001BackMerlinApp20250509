<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Pedido de Negociación Especial</title>
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

  <h2>Confirmación de Pedido de Negociación Especial</h2>

  <p>Pedido de {{ $pedido->nit }} - {{ $pedido->razon_social }}</p>

  <div class="seccion">
    <h4>Datos del Pedido</h4>
    <p><strong>ID:</strong> {{ $pedido->id }}</p>
    <p><strong>NIT:</strong> {{ $pedido->nit }}</p>
    <p><strong>Cliente:</strong> {{ $pedido->razon_social ?? '-' }}</p>
    <p><strong>Condición de pago:</strong> {{ $pedido->condicion_pago ?? '-' }}</p>
    <p><strong>Notas:</strong> {{ $pedido->observaciones ?? 'N/A' }}</p>
  </div>

  <div class="seccion">
    <h4>Detalle del Pedido</h4>
    <a href="{{ url('https://aplicacion.merlinrod.com/admin/pedidos/' . $pedido->id . '/detalle') }}">
      Mirar pedido
    </a>
    <div class="seccion">
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
  </div>

  <p><strong>Notas del pedido:</strong> {{ $pedido->observaciones ?? 'Sin notas adicionales.' }}</p>

  <p>Precios sujetos a cambios sin previo aviso - Por favor no responder este correo.</p>
  <p>Gracias por tu pedido - <strong>Gees Global S.A.S - 2025</strong></p>

</body>
</html>