<?php

namespace App\Livewire\Admin\ImagenesProductos;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public string $buscarReferencia = '';

    #[Locked]
    public string $referencia = '';

    public array $archivos = [];

    #[Locked]
    public int $versionInputs = 0;

    private const PERMISO = 'Administrar imagenes productos';

    public function mount(): void
    {
        Gate::authorize(self::PERMISO);
    }

    private function autorizar(): void
    {
        Gate::authorize(self::PERMISO);
    }

    private function carpeta(): string
    {
        return public_path('storage/productos');
    }

    public function buscar(): void
    {
        $this->autorizar();

        $this->buscarReferencia = trim($this->buscarReferencia);

        $this->validate([
            'buscarReferencia' => [
                'required',
                'string',
                'max:100',
                'regex:/\A[A-Za-z0-9][A-Za-z0-9_-]*\z/',
            ],
        ], [
            'buscarReferencia.required' => 'Ingrese una referencia.',
            'buscarReferencia.regex' =>
                'Use únicamente letras, números, guiones o guion bajo.',
        ]);

        $this->referencia = $this->buscarReferencia;
        $this->archivos = [];
        $this->versionInputs++;
        $this->resetValidation();
    }

    private function validarPosicion(int $posicion): void
    {
        abort_unless(
            $this->referencia !== ''
                && $posicion >= 1
                && $posicion <= 4,
            422,
            'Referencia o posición inválida.'
        );
    }

    /**
     * Devuelve todas las variantes de una posición.
     * Reconoce también extensiones en mayúscula.
     */
    private function existentes(int $posicion): array
    {
        $carpeta = $this->carpeta();

        if (!is_dir($carpeta)) {
            return [];
        }

        $patron = '/\A'
            . preg_quote($this->referencia . '-' . $posicion, '/')
            . '\.(jpg|jpeg|png|webp)\z/i';

        $resultados = [];

        foreach (new \DirectoryIterator($carpeta) as $archivo) {
            if (
                $archivo->isFile()
                && !$archivo->isLink()
                && preg_match($patron, $archivo->getFilename())
            ) {
                $resultados[] = $archivo->getPathname();
            }
        }

        sort($resultados);

        return $resultados;
    }

    public function guardar(int $posicion): void
    {
        $this->autorizar();
        $this->validarPosicion($posicion);

        $this->validate([
            "archivos.$posicion" => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            "archivos.$posicion.required" => 'Seleccione una imagen.',
            "archivos.$posicion.image" => 'El archivo debe ser una imagen.',
            "archivos.$posicion.mimes" => 'Formatos permitidos: JPG, PNG o WebP.',
            "archivos.$posicion.max" => 'La imagen no puede superar 5 MB.',
        ]);

        $imagen = $this->archivos[$posicion];

        // La extensión se determina por el contenido, no por el nombre original.
        $extension = match ($imagen->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };

        if (!$extension) {
            throw ValidationException::withMessages([
                "archivos.$posicion" => 'Formato de imagen no permitido.',
            ]);
        }

        try {
            Cache::lock(
                'imagenes-productos:' . sha1(strtolower($this->referencia)),
                60
            )->block(5, function () use ($imagen, $extension, $posicion) {
                File::ensureDirectoryExists($this->carpeta(), 0755);

                $anteriores = $this->existentes($posicion);

                $destino = $this->carpeta()
                    . DIRECTORY_SEPARATOR
                    . $this->referencia
                    . '-' . $posicion
                    . '.' . $extension;

                // Primero se copia a un temporal en la misma carpeta.
                // Solo se sustituye la imagen cuando la copia está completa.
                $temporal = tempnam($this->carpeta(), '.imagen-');

                if ($temporal === false) {
                    throw new \RuntimeException('No se pudo crear el temporal.');
                }

                try {
                    if (!copy($imagen->getRealPath(), $temporal)) {
                        throw new \RuntimeException('No se pudo copiar la imagen.');
                    }

                    if (!chmod($temporal, 0644)) {
                        throw new \RuntimeException('No se pudieron asignar permisos.');
                    }

                    if (!rename($temporal, $destino)) {
                        throw new \RuntimeException('No se pudo guardar la imagen.');
                    }

                    // Retira variantes anteriores con otra extensión o mayúsculas.
                    foreach ($anteriores as $anterior) {
                        if ($anterior !== $destino && !File::delete($anterior)) {
                            throw new \RuntimeException(
                                'La imagen se guardó, pero no se pudo retirar una variante anterior.'
                            );
                        }
                    }
                } finally {
                    if (is_file($temporal)) {
                        File::delete($temporal);
                    }
                }
            });
        } catch (\Throwable $e) {
            report($e);

            $this->addError(
                "archivos.$posicion",
                'No se completó la operación. Revise las imágenes y los permisos de la carpeta e intente nuevamente.'
            );

            return;
        }

        unset($this->archivos[$posicion]);

        $this->versionInputs++;

        session()->flash(
            'success',
            "Imagen {$posicion} guardada correctamente."
        );
    }

    public function eliminar(int $posicion): void
    {
        $this->autorizar();
        $this->validarPosicion($posicion);

        try {
            Cache::lock(
                'imagenes-productos:' . sha1(strtolower($this->referencia)),
                60
            )->block(5, function () use ($posicion) {
                foreach ($this->existentes($posicion) as $ruta) {
                    if (!File::delete($ruta)) {
                        throw new \RuntimeException('No se pudo eliminar la imagen.');
                    }
                }
            });
        } catch (\Throwable $e) {
            report($e);

            $this->addError(
                "archivos.$posicion",
                'No se completó la eliminación. Actualice y revise los permisos.'
            );

            return;
        }

        unset($this->archivos[$posicion]);

        $this->versionInputs++;

        session()->flash('success', "Imagen {$posicion} eliminada.");
    }


    public function administrar(string $referencia): void
{
    $this->buscarReferencia = $referencia;
    $this->buscar();
}

private function listadoReferencias(): array
{
    if (!is_dir($this->carpeta())) {
        return [];
    }

    $referencias = [];

    foreach (new \DirectoryIterator($this->carpeta()) as $archivo) {
        if (!$archivo->isFile() || $archivo->isLink()) {
            continue;
        }

        // Ejemplo: 612000011-2.jpg
        if (!preg_match(
            '/\A([A-Za-z0-9][A-Za-z0-9_-]{0,99})-([1-4])\.(jpg|jpeg|png|webp)\z/i',
            $archivo->getFilename(),
            $coincidencia
        )) {
            continue;
        }

        $referencia = $coincidencia[1];
        $posicion = (int) $coincidencia[2];

        // El prefijo evita que PHP convierta referencias numéricas en enteros.
        $clave = 'ref:' . $referencia;

        if (!isset($referencias[$clave])) {
            $referencias[$clave] = [
                'referencia' => $referencia,
                'posiciones' => [],
                'archivos' => 0,
            ];
        }

        $referencias[$clave]['posiciones'][$posicion] = $posicion;
        $referencias[$clave]['archivos']++;
    }

    foreach ($referencias as &$fila) {
        sort($fila['posiciones']);
        $fila['cantidad'] = count($fila['posiciones']);
    }

    unset($fila);

    $resultado = array_values($referencias);

    usort(
        $resultado,
        fn ($a, $b) => strnatcasecmp($a['referencia'], $b['referencia'])
    );

    return $resultado;
}

public function render()
{
    $this->autorizar();

    $imagenes = [];

    if ($this->referencia !== '') {
        for ($posicion = 1; $posicion <= 4; $posicion++) {
            $rutas = $this->existentes($posicion);

            $imagenes[$posicion] = array_map(function ($ruta) {
                clearstatcache(true, $ruta);

                return [
                    'nombre' => basename($ruta),
                    'url' => asset(
                        'storage/productos/' . rawurlencode(basename($ruta))
                    ) . '?v=' . filemtime($ruta),
                    'kb' => round(filesize($ruta) / 1024, 1),
                ];
            }, $rutas);
        }
    }

    $todasLasReferencias = $this->listadoReferencias();

    $totalReferencias = count($todasLasReferencias);

    $totalImagenes = array_sum(
        array_column($todasLasReferencias, 'archivos')
    );

    // El buscador también filtra el listado mientras escribe.
    $filtro = trim($this->buscarReferencia);

    $referenciasListado = array_values(array_filter(
        $todasLasReferencias,
        fn ($fila) => $filtro === ''
            || stripos($fila['referencia'], $filtro) !== false
    ));

    return view('livewire.admin.imagenes-productos.index', compact(
        'imagenes',
        'referenciasListado',
        'totalReferencias',
        'totalImagenes'
    ));
}
}