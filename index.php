<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGIS - Analisis Sebaran Fasilitas Pendidikan Kabupaten Lampung Selatan</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Tailwind Custom Colors (Dark Blue Theme) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'dark-blue': {
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#1e3a8a',
                            600: '#1e40af',
                            500: '#3b82f6',
                            400: '#60a5fa',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-blue-900 text-gray-100">
    <!-- Header -->
    <header class="bg-dark-blue-800 border-b border-dark-blue-700 shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-blue-400">🗺️ WebGIS Pendidikan</h1>
                    <p class="text-sm text-gray-400 mt-1">Analisis Sebaran Fasilitas Pendidikan Kabupaten Lampung Selatan</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-400">QGIS + PHP + MySQL + Leaflet.js</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex h-[calc(100vh-80px)]">
        <!-- Sidebar -->
        <aside class="w-96 bg-dark-blue-800 border-r border-dark-blue-700 overflow-y-auto">
            <div class="p-4 space-y-4">
                <!-- Filter Section -->
                <div class="bg-dark-blue-700 rounded-lg p-4 border border-dark-blue-600">
                    <h2 class="text-lg font-semibold text-blue-400 mb-4">🔍 Filter & Pencarian</h2>
                    
                    <!-- Geocoding Search -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">🔍 Cari Lokasi</label>
                        <div class="flex space-x-2">
                            <input 
                                type="text" 
                                id="geocodeInput" 
                                placeholder="Cari alamat/tempat..."
                                class="flex-1 px-3 py-2 bg-dark-blue-900 border border-dark-blue-600 rounded-md text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <button 
                                id="geocodeBtn"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-md text-white text-sm font-medium transition"
                            >
                                Cari
                            </button>
                        </div>
                        <div id="geocodeResults" class="mt-2 max-h-40 overflow-y-auto hidden"></div>
                    </div>
                    
                    <!-- Search Input -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Cari Sekolah</label>
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="Masukkan nama sekolah..."
                            class="w-full px-3 py-2 bg-dark-blue-900 border border-dark-blue-600 rounded-md text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    
                    <!-- CRUD Actions -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Aksi</label>
                        <button 
                            id="toggleAddModeBtn"
                            class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 rounded-md text-white text-sm font-medium transition mb-2"
                        >
                            ➕ Tambah Marker
                        </button>
                        <div id="addModeStatus" class="text-xs text-gray-400 hidden"></div>
                    </div>
                    
                    <!-- Jenjang Filter -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Jenjang Pendidikan</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="jenjang-filter mr-2 rounded" value="All" checked>
                                <span class="text-sm text-gray-300">Semua</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="jenjang-filter mr-2 rounded" value="Menengah Pertama" checked>
                                <span class="text-sm text-gray-300">Menengah Pertama</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="jenjang-filter mr-2 rounded" value="Menengah Umum" checked>
                                <span class="text-sm text-gray-300">Menengah Umum</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="jenjang-filter mr-2 rounded" value="Keagamaan" checked>
                                <span class="text-sm text-gray-300">Keagamaan</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="jenjang-filter mr-2 rounded" value="Tinggi" checked>
                                <span class="text-sm text-gray-300">Tinggi</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="jenjang-filter mr-2 rounded" value="Khusus" checked>
                                <span class="text-sm text-gray-300">Khusus</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Layer Toggle -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Layer</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" id="toggleKecamatan" checked class="mr-2 rounded">
                                <span class="text-sm text-gray-300">Batas Kecamatan</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="toggleAnalisis" checked class="mr-2 rounded">
                                <span class="text-sm text-gray-300">Hasil Analisis</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="toggleSekolah" checked class="mr-2 rounded">
                                <span class="text-sm text-gray-300">Titik Sekolah</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Statistik Section -->
                <div class="bg-dark-blue-700 rounded-lg p-4 border border-dark-blue-600">
                    <h2 class="text-lg font-semibold text-blue-400 mb-4">📊 Statistik</h2>
                    <div id="statistikContent">
                        <div class="animate-pulse space-y-2">
                            <div class="h-4 bg-dark-blue-600 rounded"></div>
                            <div class="h-4 bg-dark-blue-600 rounded"></div>
                            <div class="h-4 bg-dark-blue-600 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Map Container -->
        <main class="flex-1 relative">
            <div id="map" class="w-full h-full"></div>
            
            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="absolute inset-0 bg-dark-blue-900 bg-opacity-75 flex items-center justify-center z-[1000] hidden">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-400 mx-auto mb-4"></div>
                    <p class="text-gray-300">Memuat data...</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Form Sekolah -->
    <div id="sekolahModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[2000] hidden">
        <div class="bg-dark-blue-800 rounded-lg p-6 w-full max-w-md border border-dark-blue-600">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-xl font-bold text-blue-400">Tambah Sekolah Baru</h3>
                <button id="closeModal" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="sekolahForm" class="space-y-4">
                <input type="hidden" id="sekolahId" name="id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nama Sekolah *</label>
                    <input 
                        type="text" 
                        id="namaSekolah" 
                        name="nama_sekolah"
                        required
                        class="w-full px-3 py-2 bg-dark-blue-900 border border-dark-blue-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Jenjang Pendidikan *</label>
                    <select 
                        id="jenjangSekolah" 
                        name="jenjang"
                        required
                        class="w-full px-3 py-2 bg-dark-blue-900 border border-dark-blue-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Pilih Jenjang</option>
                        <option value="Menengah Pertama">Menengah Pertama</option>
                        <option value="Menengah Umum">Menengah Umum</option>
                        <option value="Keagamaan">Keagamaan</option>
                        <option value="Tinggi">Tinggi</option>
                        <option value="Khusus">Khusus</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">
                        Kode Identifikasi Sekolah
                        <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <input 
                        type="number" 
                        id="fggpdkSekolah" 
                        name="fggpdk"
                        placeholder="Masukkan kode identifikasi (jika ada)"
                        class="w-full px-3 py-2 bg-dark-blue-900 border border-dark-blue-600 rounded-md text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <p class="text-xs text-gray-400 mt-1">
                        Kode identifikasi unik untuk sekolah (biasanya dari sistem administrasi pendidikan)
                    </p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Latitude *</label>
                        <input 
                            type="number" 
                            step="any"
                            id="latitudeSekolah" 
                            name="latitude"
                            required
                            class="w-full px-3 py-2 bg-dark-blue-900 border border-dark-blue-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Longitude *</label>
                        <input 
                            type="number" 
                            step="any"
                            id="longitudeSekolah" 
                            name="longitude"
                            required
                            class="w-full px-3 py-2 bg-dark-blue-900 border border-dark-blue-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Kecamatan</label>
                    <select 
                        id="kecamatanSekolah" 
                        name="kecamatan"
                        class="w-full px-3 py-2 bg-dark-blue-900 border border-dark-blue-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Pilih Kecamatan</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        Pilih kecamatan tempat sekolah berada
                    </p>
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button 
                        type="submit"
                        id="submitBtn"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-md text-white font-medium transition"
                    >
                        Simpan
                    </button>
                    <button 
                        type="button"
                        id="cancelBtn"
                        class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded-md text-white font-medium transition"
                    >
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/map.js"></script>
</body>
</html>

