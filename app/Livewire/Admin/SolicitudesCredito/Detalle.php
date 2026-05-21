<?php

namespace App\Livewire\Admin\SolicitudesCredito;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\SolicitudCredito;
use App\Models\SolicitudCreditoDocumento;
use App\Models\TipoDocumentoCredito;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\SolicitudCreditoReferencia;
use App\Models\FleteCiudad;
class Detalle extends Component
{
    use WithFileUploads;

    public SolicitudCredito $solicitud;

    public $tiposDocumentos = [];
    public array $archivos = [];
    public array $observaciones = [];

    public string $comentarioRevision = '';
    public $cupoAsignado = null;
    public string $condicionPagoAprobada = '';
    public string $comentarioCierreAprobado = '';
    public string $reporteCentralesRiesgo = 'sin_estado';
    public ?string $comentarioReporteCentrales = null;
    public ?string $numero_cotizacion = null;    
    public bool $modalInfoReferencia = false;

    public ?int $referenciaId = null;

public $departamentosReferencia = [];
public $ciudadesReferencia = [];
    public bool $modalReferencia = false;

    public array $referenciaForm = [
        'empresa' => '',
        'nit' => '',
        'cod_depto' => '',
        'depto' => '',
        'cod_ciudad' => '',
        'ciudad' => '',
        'telefono' => '',
        'cupo_credito' => null,
    ];
    public array $referenciacionForm = [
        'quien_da_referencia' => '',
        'cupo_asignado' => null,
        'antiguedad_comercial' => '',
        'promedio_pago' => '',
        'cheques_devueltos' => '',
        'activo' => '',
        'concepto' => '',
        'fecha_referencia' => '',
        'ultimo_despacho' => '',
    ];
    public function mount(SolicitudCredito $solicitud): void
    {
        $this->solicitud = $solicitud;
        $this->cargarDatos();
        $this->reporteCentralesRiesgo = $this->solicitud->reporte_centrales_riesgo ?: 'sin_estado';
        $this->comentarioReporteCentrales = $this->solicitud->comentario_reporte_centrales;

        $this->departamentosReferencia = FleteCiudad::query()
            ->select('cod_depto', 'depto')
            ->whereNotNull('cod_depto')
            ->whereNotNull('depto')
            ->where('cod_depto', '<>', '')
            ->where('depto', '<>', '')
            ->groupBy('cod_depto', 'depto')
            ->orderBy('depto')
            ->get()
            ->toArray();
    }

public function updatedReferenciaFormCodDepto($value): void
{
    $depto = FleteCiudad::where('cod_depto', $value)->first();

    $this->referenciaForm['depto'] = $depto?->depto ?? '';
    $this->referenciaForm['cod_ciudad'] = '';
    $this->referenciaForm['ciudad'] = '';

    $this->ciudadesReferencia = FleteCiudad::query()
        ->select('cod_ciudad', 'ciudad')
        ->where('cod_depto', $value)
        ->whereNotNull('cod_ciudad')
        ->whereNotNull('ciudad')
        ->where('cod_ciudad', '<>', '')
        ->where('ciudad', '<>', '')
        ->groupBy('cod_ciudad', 'ciudad')
        ->orderBy('ciudad')
        ->get()
        ->toArray();
}
    public function cargarDatos(): void
    {
        $this->solicitud->load([
            'user',
            'referencias',
            'direcciones',
            'documentos.tipoDocumento',
            'documentos.aprobadoPor',
        ]);

        $this->tiposDocumentos = TipoDocumentoCredito::where('estado', true)
            ->orderBy('orden')
            ->get();
    }

    public function subirDocumento($tipoDocumentoId): void
    {
        $this->validate([
            "archivos.$tipoDocumentoId" => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $tipo = TipoDocumentoCredito::findOrFail($tipoDocumentoId);

        $cantidadActual = SolicitudCreditoDocumento::where('solicitud_credito_id', $this->solicitud->id)
            ->where('tipo_documento_credito_id', $tipoDocumentoId)
            ->whereNull('deleted_at')
            ->count();

        if ($cantidadActual >= $tipo->cantidad_maxima) {
            session()->flash('error', 'Ya alcanzaste la cantidad máxima permitida para este tipo de documento.');
            return;
        }

        $file = $this->archivos[$tipoDocumentoId];

        $path = $file->store(
            'solicitudes_credito/documentos/' . $this->solicitud->id,
            'public'
        );

        SolicitudCreditoDocumento::create([
            'solicitud_credito_id' => $this->solicitud->id,
            'tipo_documento_credito_id' => $tipoDocumentoId,
            'nombre_original' => $file->getClientOriginalName(),
            'archivo' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'peso' => $file->getSize(),
            'estado' => 'pendiente',
        ]);

        unset($this->archivos[$tipoDocumentoId]);

        $this->cargarDatos();

        session()->flash('success', 'Documento subido correctamente.');
    }

    public function aprobarDocumento($documentoId): void
    {
        if ($this->documentosBloqueados()) {
            session()->flash('error', 'No se pueden modificar documentos cuando la solicitud ya avanzó de revisión.');
            return;
        }

        $documento = SolicitudCreditoDocumento::findOrFail($documentoId);

        $documento->update([
            'estado' => 'aprobado',
            'observacion' => $this->observaciones[$documentoId] ?? null,
            'aprobado_por' => Auth::id(),
            'fecha_revision' => now(),
        ]);

        $this->pasarARevisionSiAplica();

        $this->cargarDatos();

        session()->flash('success', 'Documento aprobado correctamente.');
    }

    public function rechazarDocumento($documentoId): void
    {
        if ($this->documentosBloqueados()) {
            session()->flash('error', 'No se pueden modificar documentos cuando la solicitud ya avanzó de revisión.');
            return;
        }

        $documento = SolicitudCreditoDocumento::findOrFail($documentoId);

        $documento->update([
            'estado' => 'no_aprobado',
            'observacion' => $this->observaciones[$documentoId] ?? null,
            'aprobado_por' => Auth::id(),
            'fecha_revision' => now(),
        ]);

        $this->pasarARevisionSiAplica();

        $this->cargarDatos();

        session()->flash('success', 'Documento rechazado correctamente.');
    }

    public function eliminarDocumento($documentoId): void
    {
        $documento = SolicitudCreditoDocumento::findOrFail($documentoId);

        if ($documento->archivo && Storage::disk($documento->disk)->exists($documento->archivo)) {
            Storage::disk($documento->disk)->delete($documento->archivo);
        }

        $documento->delete();

        $this->cargarDatos();

        session()->flash('success', 'Documento eliminado correctamente.');
    }

    private function pasarARevisionSiAplica(): void
{
    if (!in_array($this->solicitud->estado, ['en_revision', 'aprobado_parcial', 'rechazado', 'aprobado'])) {
        $this->solicitud->update([
            'estado' => 'en_revision',
        ]);
    }

    $this->cargarDatos();
}

public function getTodosDocumentosRevisadosProperty(): bool
{
    $documentos = $this->solicitud->documentos;

    if ($documentos->count() === 0) {
        return false;
    }

    return $documentos->where('estado', 'pendiente')->count() === 0;
}

public function pasarSegundaAprobacion(): void
{
    if (!$this->todosDocumentosRevisados) {
        session()->flash('error', 'Aún hay documentos pendientes por revisar.');
        return;
    }

    $this->validate([
        'comentarioRevision' => 'required|string|min:5',
    ]);

    $this->solicitud->update([
        'estado' => 'aprobado_parcial',
        'comentario_revision_documentos' => $this->comentarioRevision,
        'fecha_revision_documentos' => now(),
        'revision_documentos_por' => auth()->id(),
    ]);

    $this->cargarDatos();

    session()->flash('success', 'Solicitud enviada a segunda aprobación.');
}

public function rechazarSolicitudRevision(): void
{
    if (!$this->todosDocumentosRevisados) {
        session()->flash('error', 'Aún hay documentos pendientes por revisar.');
        return;
    }

    $this->validate([
        'comentarioRevision' => 'required|string|min:5',
    ]);

    $this->solicitud->update([
        'estado' => 'rechazado',
        'comentario_revision_documentos' => $this->comentarioRevision,
        'fecha_revision_documentos' => now(),
        'revision_documentos_por' => auth()->id(),
    ]);

    $this->cargarDatos();

    session()->flash('success', 'Solicitud rechazada correctamente.');
}

    public function cerrarAprobacion(): void
    {
        $this->validate([
            'cupoAsignado' => 'required|numeric|min:0',
            'condicionPagoAprobada' => 'required|string',
            'comentarioCierreAprobado' => 'nullable|string',
        ]);

        $this->solicitud->update([
            'estado' => 'aprobado',
            'cupo_asignado' => $this->cupoAsignado,
            'condicion_pago_aprobada' => $this->condicionPagoAprobada,
            'comentario_cierre_aprobado' => $this->comentarioCierreAprobado,
        ]);

        $this->cargarDatos();

        session()->flash('success', 'Solicitud cerrada como aprobada.');
    }

    public function actualizarReporteCentrales(): void
    {
        if ($this->reporteCentralesBloqueado) {
            session()->flash('error', 'El reporte en centrales ya fue definido y no se puede modificar.');
            return;
        }

        $this->validate([
            'reporteCentralesRiesgo' => 'required|in:sin_estado,positivo,negativo',
            'comentarioReporteCentrales' => 'nullable|string|max:1000',
        ]);

        $this->solicitud->update([
            'reporte_centrales_riesgo' => $this->reporteCentralesRiesgo,
            'comentario_reporte_centrales' => $this->comentarioReporteCentrales,
        ]);

        $this->cargarDatos();

        session()->flash('success', 'Reporte en centrales actualizado correctamente.');
    }

    public function getReporteCentralesBloqueadoProperty(): bool
    {
        return in_array($this->solicitud->reporte_centrales_riesgo, [
            'positivo',
            'negativo',
        ]);
    }
    private function documentosBloqueados(): bool
    {
        return in_array($this->solicitud->estado, [
            'aprobado_parcial',
            'aprobado',
            'rechazado',
        ]);
    }
    public function abrirInfoReferenciacion(int $referenciaId): void
    {
        $ref = SolicitudCreditoReferencia::findOrFail($referenciaId);

        $this->referenciaId = $ref->id;

        $this->referenciacionForm = [
            'quien_da_referencia' => $ref->quien_da_referencia ?? '',
            'cupo_asignado' => $ref->cupo_asignado,
            'antiguedad_comercial' => $ref->antiguedad_comercial ?? '',
            'promedio_pago' => $ref->promedio_pago ?? '',
            'cheques_devueltos' => $ref->cheques_devueltos ?? '',
            'activo' => $ref->activo ?? '',
            'concepto' => $ref->concepto ?? '',
            'fecha_referencia' => optional($ref->fecha_referencia)->format('Y-m-d'),
            'ultimo_despacho' => optional($ref->ultimo_despacho)->format('Y-m-d'),
        ];

        $this->modalInfoReferencia = true;
    }

    public function guardarInfoReferenciacion(): void
    {
        $this->validate([
            'referenciacionForm.quien_da_referencia' => 'nullable|string|max:255',
            'referenciacionForm.cupo_asignado' => 'nullable|numeric|min:0',
            'referenciacionForm.antiguedad_comercial' => 'nullable|string|max:255',
            'referenciacionForm.promedio_pago' => 'nullable|string|max:255',
            'referenciacionForm.cheques_devueltos' => 'nullable|string|max:255',
            'referenciacionForm.activo' => 'nullable|string|max:255',
            'referenciacionForm.concepto' => 'nullable|string',
            'referenciacionForm.fecha_referencia' => 'nullable|date',
            'referenciacionForm.ultimo_despacho' => 'nullable|date',
        ]);

        $ref = SolicitudCreditoReferencia::findOrFail($this->referenciaId);

        $ref->update([
            ...$this->referenciacionForm,
            'verifico_referencia' => auth()->id(),
        ]);

        $this->modalInfoReferencia = false;
        $this->cargarDatos();

        session()->flash('success', 'Información de referenciación guardada correctamente.');
    }

    public function eliminarReferencia(int $referenciaId): void
    {
        SolicitudCreditoReferencia::where('solicitud_credito_id', $this->solicitud->id)
            ->where('id', $referenciaId)
            ->delete();

        $this->cargarDatos();

        session()->flash('success', 'Referencia eliminada correctamente.');
    }
    public function render()
    {
        return view('livewire.admin.solicitudes-credito.detalle');
    }

    public function abrirModalReferencia(): void
{
    $this->referenciaForm = [
        'empresa' => '',
        'nit' => '',
        'ciudad' => '',
        'telefono' => '',
        'cupo_credito' => null,
    ];

    $this->modalReferencia = true;
}

public function updatedReferenciaFormCodCiudad($value): void
{
    $ciudad = FleteCiudad::where('cod_depto', $this->referenciaForm['cod_depto'])
        ->where('cod_ciudad', $value)
        ->first();

    $this->referenciaForm['ciudad'] = $ciudad?->ciudad ?? '';
}


    public function guardarReferencia(): void
    {
        $this->validate([
            'referenciaForm.empresa' => 'required|string|max:255',
            'referenciaForm.nit' => 'required|string|max:50',
            'referenciaForm.ciudad' => 'nullable|string|max:255',
            'referenciaForm.telefono' => 'required|string|max:20',
            'referenciaForm.cupo_credito' => 'required|numeric|min:0',
        ]);

        SolicitudCreditoReferencia::create([
            'solicitud_credito_id' => $this->solicitud->id,
            'empresa' => $this->referenciaForm['empresa'],
            'nit' => $this->referenciaForm['nit'],
            'cod_depto' => $this->referenciaForm['cod_depto'],
            'depto' => $this->referenciaForm['depto'],
            'cod_ciudad' => $this->referenciaForm['cod_ciudad'],
            'ciudad' => $this->referenciaForm['ciudad'],
            'telefono' => $this->referenciaForm['telefono'],
            'cupo_credito' => $this->referenciaForm['cupo_credito'],
        ]);

        $this->modalReferencia = false;
        $this->solicitud->refresh();

        session()->flash('success', 'Referencia creada correctamente.');
    }
}