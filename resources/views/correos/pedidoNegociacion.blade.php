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
    <p><strong>Sucursal:</strong> {{ $pedido->id_sucursal }}</p>
    <p><strong>Condición de pago:</strong> {{ $pedido->condicion_pago ?? '-' }}</p>
    <p><strong>Notas:</strong> {{ $pedido->observaciones ?? 'N/A' }}</p>
  </div>

  <div class="seccion">
    <h4>Detalle del Pedido</h4>
    <a href="{{ url('https://aplicacion.merlinrod.com/admin/pedidos/' . $pedido->id . '/detalle') }}">
      Mirar pedido
    </a>
  </div>

  <p><strong>Notas del pedido:</strong> {{ $pedido->observaciones ?? 'Sin notas adicionales.' }}</p>

  <p>Precios sujetos a cambios sin previo aviso - Por favor no responder este correo.</p>
  <p>Gracias por tu pedido - <strong>Gees Global S.A.S - 2025</strong></p>

</body>
</html>