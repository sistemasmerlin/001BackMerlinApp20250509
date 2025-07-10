<div>
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="importarPromo" enctype="multipart/form-data">
        <div class="flex mb-4">
            <input type="file" wire:model="excel_promo" accept=".xls,.xlsx" class="mt-2 bg-yellow-100 block  border border-gray-300 rounded-md shadow-sm mx-2" required>
            <button type="submit" class="bg-green-500 hover:bg-green-700 font-bold text-white px-4 py-1 rounded">Importar</button>
        </div>
    </form>
    
</div>
