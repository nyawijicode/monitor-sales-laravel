<div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $info['alamat'] ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Wilayah</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $info['wilayah'] ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Kontak</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $info['nama_kontak'] ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Telepon</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $info['telepon'] ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jabatan</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $info['jabatan'] ?? '-' }}</dd>
        </div>
    </dl>
</div>