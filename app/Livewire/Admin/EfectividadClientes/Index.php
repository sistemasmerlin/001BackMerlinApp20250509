<?php

namespace App\Livewire\Admin\EfectividadClientes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    public string $periodo = '';
    public array $periodos = [];

    public array $rows = [];
    public int $totalClientes = 0;
    public int $totalClientesConVenta = 0;
    public float $efectividadGlobal = 0;

    public function mount(): void
    {
        $this->periodo = Carbon::now()->format('Ym');

        $this->periodos = collect(range(0, 11))
            ->map(function ($i) {
                $p = Carbon::now()->subMonths($i);
                return [
                    'value' => $p->format('Ym'),
                    'label' => $p->translatedFormat('F Y'),
                ];
            })
            ->values()
            ->toArray();

        $this->cargar();
    }

    public function updatedPeriodo(): void
    {
        $this->cargar();
    }

    public function cargar(): void
    {
        $periodo = trim((string) $this->periodo);

        if (!preg_match('/^\d{6}$/', $periodo)) {
            $this->rows = [];
            $this->totalClientes = 0;
            $this->totalClientesConVenta = 0;
            $this->efectividadGlobal = 0;
            return;
        }

        $anio = (int) substr($periodo, 0, 4);
        $mes  = (int) substr($periodo, 4, 2);

        $clientesPorAsesor = collect(DB::connection('sqlsrv')->select("
            SELECT
                RTRIM(t201.f201_id_vendedor) AS vendedor,
                COUNT(DISTINCT t200.f200_nit) AS total_clientes
            FROM t200_mm_terceros t200
            LEFT JOIN t201_mm_clientes t201
                ON t200.f200_rowid = t201.f201_rowid_tercero
            WHERE t200.f200_id_cia = 3
                AND t201.f201_id_cia = 3
                AND t200.f200_ind_cliente = 1
                AND t200.f200_ind_estado = 1
                AND t201.f201_id_cond_pago IN ('30D','10D','15D','30E')
            GROUP BY RTRIM(t201.f201_id_vendedor)
        "))->keyBy('vendedor');

        $ventasPorAsesor = collect(DB::connection('sqlsrv')->select("
            SELECT
                RTRIM(t201.f201_id_vendedor) AS vendedor,
                COUNT(DISTINCT t461.f461_rowid_tercero_fact) AS clientes_con_venta
            FROM t461_cm_docto_factura_venta t461
            INNER JOIN t201_mm_clientes t201
                ON t201.f201_rowid_tercero = t461.f461_rowid_tercero_fact
            WHERE t461.f461_id_cia = 3
                AND YEAR(t461.f461_id_fecha) = ?
                AND MONTH(t461.f461_id_fecha) = ?
                AND t461.f461_id_concepto = '501'
                AND t461.f461_id_clase_docto = '523'
                AND t201.f201_id_cond_pago IN ('30D','10D','15D','30E')
            GROUP BY RTRIM(t201.f201_id_vendedor)
        ", [$anio, $mes]))->keyBy('vendedor');

        $asesores = User::role('asesor')
            ->select('id', 'name', 'email', 'codigo_asesor')
            ->whereNotNull('codigo_asesor')
            ->get()
            ->map(function ($u) {
                $u->codigo_asesor = trim((string) $u->codigo_asesor);
                return $u;
            })
            ->filter(fn ($u) => $u->codigo_asesor !== '')
            ->sortBy('name')
            ->values();

        $rows = $asesores->map(function ($u) use ($clientesPorAsesor, $ventasPorAsesor) {
            $codigo = $u->codigo_asesor;

            $totalClientes = (int) ($clientesPorAsesor[$codigo]->total_clientes ?? 0);
            $clientesConVenta = (int) ($ventasPorAsesor[$codigo]->clientes_con_venta ?? 0);

            $cumplimiento = 0;
            if ($totalClientes > 0) {
                $cumplimiento = round(($clientesConVenta / $totalClientes) * 100, 2);
            }

            return [
                'codigo_asesor' => $codigo,
                'nombre' => $u->name,
                'email' => $u->email,
                'total_clientes' => $totalClientes,
                'clientes_con_venta' => $clientesConVenta,
                'cumplimiento' => $cumplimiento,
            ];
        })->toArray();

        $this->rows = $rows;

        $this->totalClientes = array_sum(array_column($rows, 'total_clientes'));
        $this->totalClientesConVenta = array_sum(array_column($rows, 'clientes_con_venta'));

        $this->efectividadGlobal = $this->totalClientes > 0
            ? round(($this->totalClientesConVenta / $this->totalClientes) * 100, 2)
            : 0;
    }

    public function exportar(): StreamedResponse
    {
        if (empty($this->rows)) {
            $this->cargar();
        }

        $periodo = $this->periodo ?: Carbon::now()->format('Ym');
        $filename = "efectividad_clientes_{$periodo}.csv";

        $rows = $this->rows;
        $totalClientes = $this->totalClientes;
        $totalConVenta = $this->totalClientesConVenta;
        $efectividadGlobal = $this->efectividadGlobal;

        return response()->streamDownload(function () use ($rows, $totalClientes, $totalConVenta, $efectividadGlobal) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 para Excel
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['Codigo', 'Asesor', 'Email', 'Total clientes', 'Clientes con venta', 'Cumplimiento %'], ';');

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['codigo_asesor'],
                    $r['nombre'],
                    $r['email'],
                    $r['total_clientes'],
                    $r['clientes_con_venta'],
                    number_format((float) $r['cumplimiento'], 2, ',', '.'),
                ], ';');
            }

            fputcsv($out, [], ';');
            fputcsv($out, ['TOTALES', '', '', $totalClientes, $totalConVenta, number_format((float) $efectividadGlobal, 2, ',', '.')], ';');

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.efectividad-clientes.index');
    }
}
