<?php

namespace App\Livewire\Admin\SolicitudesCredito;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\SolicitudCredito;
use App\Models\SolicitudCreditoDocumento;
use App\Models\TipoDocumentoCredito;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
    public function mount(SolicitudCredito $solicitud): void
    {
        $this->solicitud = $solicitud;
        $this->cargarDatos();
        $this->reporteCentralesRiesgo = $this->solicitud->reporte_centrales_riesgo ?: 'sin_estado';
        $this->comentarioReporteCentrales = $this->solicitud->comentario_reporte_centrales;
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
    public function render()
    {
        return view('livewire.admin.solicitudes-credito.detalle');
    }
}