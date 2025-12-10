/**
 * WebGIS Pendidikan Lampung Selatan
 * Leaflet.js Integration
 */

// Inisialisasi Peta
const map = L.map('map').setView([-5.5, 105.3], 10);

// Tile Layer (OpenStreetMap)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
}).addTo(map);

// Layer Groups
const kecamatanLayer = L.layerGroup().addTo(map);
const analisisLayer = L.layerGroup().addTo(map);
const sekolahLayer = L.layerGroup().addTo(map);

// State
let allSekolahData = [];
let currentFilters = {
    jenjang: [],
    search: ''
};
let addModeActive = false;
let markerMap = new Map(); // Map untuk menyimpan marker dengan ID
let currentEditingId = null;
let mapClickHandler = null;
let kecamatanLayerVisible = true;
let analisisLayerVisible = true;

// Icon untuk Marker Sekolah berdasarkan Jenjang
function getIconByJenjang(jenjang) {
    const iconConfig = {
        'Menengah Pertama': {
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        },
        'Menengah Umum': {
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        },
        'Keagamaan': {
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        },
        'Tinggi': {
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-violet.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        },
        'Khusus': {
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        }
    };
    
    const config = iconConfig[jenjang] || iconConfig['Menengah Pertama'];
    return L.icon(config);
}

// Fungsi untuk mendapatkan warna berdasarkan jumlah sekolah
function getColorByJumlah(jumlah) {
    if (jumlah === 0) return '#1e293b';
    if (jumlah < 10) return '#3b82f6';
    if (jumlah < 20) return '#60a5fa';
    if (jumlah < 30) return '#93c5fd';
    return '#dbeafe';
}

// Load Kecamatan (Batas Administrasi)
async function loadKecamatan() {
    try {
        showLoading();
        const response = await fetch('api/get_kecamatan.php');
        const data = await response.json();
        
        kecamatanLayer.clearLayers();
        
        L.geoJSON(data, {
            style: function(feature) {
                return {
                    color: '#3b82f6',
                    weight: 2,
                    fillColor: '#1e3a8a',
                    fillOpacity: 0.3
                };
            },
            onEachFeature: function(feature, layer) {
                const props = feature.properties;
                layer.bindPopup(`
                    <h3>${props.nama_kecamatan}</h3>
                    <p><strong>Luas:</strong> ${props.luas_km} km²</p>
                `);
            }
        }).addTo(kecamatanLayer);
        
    } catch (error) {
        console.error('Error loading kecamatan:', error);
    } finally {
        hideLoading();
    }
}

// Load Kecamatan Analisis (Hasil Analisis)
async function loadKecamatanAnalisis() {
    try {
        showLoading();
        const response = await fetch('api/get_kecamatan_analisis.php');
        const data = await response.json();
        
        analisisLayer.clearLayers();
        
        L.geoJSON(data, {
            style: function(feature) {
                const jumlah = feature.properties.jumlah_sekolah || 0;
                return {
                    color: '#60a5fa',
                    weight: 2,
                    fillColor: getColorByJumlah(jumlah),
                    fillOpacity: 0.6
                };
            },
            onEachFeature: function(feature, layer) {
                const props = feature.properties;
                layer.bindPopup(`
                    <h3>${props.nama_kecamatan}</h3>
                    <p><strong>Jumlah Sekolah:</strong> ${props.jumlah_sekolah}</p>
                    <p><strong>Luas:</strong> ${props.luas_km} km²</p>
                `);
            }
        }).addTo(analisisLayer);
        
    } catch (error) {
        console.error('Error loading analisis:', error);
    } finally {
        hideLoading();
    }
}

// Load Sekolah (Titik Digitasi)
async function loadSekolah() {
    try {
        showLoading();
        
        // Build query parameters
        const params = new URLSearchParams();
        if (currentFilters.jenjang.length > 0 && !currentFilters.jenjang.includes('All')) {
            // If multiple jenjang selected, we need to filter client-side
            // For now, we'll load all and filter client-side
        }
        if (currentFilters.search) {
            params.append('search', currentFilters.search);
        }
        
        const url = 'api/get_sekolah.php' + (params.toString() ? '?' + params.toString() : '');
        const response = await fetch(url);
        const data = await response.json();
        
        // Store all data
        allSekolahData = data.features || [];
        
        // Filter by jenjang if needed
        let filteredData = allSekolahData;
        if (currentFilters.jenjang.length > 0 && !currentFilters.jenjang.includes('All')) {
            filteredData = allSekolahData.filter(feature => 
                currentFilters.jenjang.includes(feature.properties.jenjang)
            );
        }
        
        sekolahLayer.clearLayers();
        
        L.geoJSON(filteredData, {
            pointToLayer: function(feature, latlng) {
                const jenjang = feature.properties.jenjang;
                const icon = getIconByJenjang(jenjang);
                return L.marker(latlng, { icon: icon });
            },
            onEachFeature: function(feature, layer) {
                const props = feature.properties;
                
                // Popup tanpa kecamatan
                const popupContent = `
                    <div style="min-width: 200px;">
                        <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: bold;">${props.nama_sekolah}</h3>
                        <p style="margin: 5px 0;"><strong>Jenjang:</strong> ${props.jenjang}</p>
                        <p style="margin: 5px 0;"><strong>Koordinat:</strong> ${props.latitude.toFixed(6)}, ${props.longitude.toFixed(6)}</p>
                        <div style="margin-top: 10px; display: flex; gap: 5px;">
                            <button onclick="editSekolah(${props.id})" style="flex: 1; padding: 5px 10px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">✏️ Edit</button>
                            <button onclick="deleteSekolah(${props.id})" style="flex: 1; padding: 5px 10px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">🗑️ Hapus</button>
                        </div>
                    </div>
                `;
                layer.bindPopup(popupContent);
                
                // Store marker with ID
                markerMap.set(props.id, layer);
            }
        }).addTo(sekolahLayer);
        
    } catch (error) {
        console.error('Error loading sekolah:', error);
    } finally {
        hideLoading();
    }
}

// Load Statistik
async function loadStatistik() {
    try {
        const response = await fetch('api/get_statistik.php');
        const data = await response.json();
        
        const statistikHTML = `
            <div class="space-y-3">
                <div class="stat-card rounded-lg p-3">
                    <p class="text-sm text-gray-400">Total Sekolah</p>
                    <p class="text-2xl font-bold text-blue-400">${data.total_sekolah || 0}</p>
                </div>
                
                <div class="stat-card rounded-lg p-3">
                    <p class="text-sm text-gray-400">Total Kecamatan</p>
                    <p class="text-2xl font-bold text-blue-400">${data.total_kecamatan || 0}</p>
                </div>
                
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-300 mb-2">Per Jenjang:</p>
                    <div class="space-y-2">
                        ${(data.per_jenjang || []).map(item => `
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-300">${item.jenjang}</span>
                                <span class="font-semibold text-blue-400">${item.jumlah}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-300 mb-2">Top 5 Kecamatan:</p>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        ${(data.per_kecamatan || []).slice(0, 5).map(item => `
                            <div class="text-xs bg-dark-blue-800 rounded p-2">
                                <p class="font-medium text-gray-200">${item.nama_kecamatan}</p>
                                <p class="text-gray-400">${item.jumlah_sekolah} sekolah</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('statistikContent').innerHTML = statistikHTML;
        
    } catch (error) {
        console.error('Error loading statistik:', error);
        document.getElementById('statistikContent').innerHTML = '<p class="text-red-400">Error loading statistik</p>';
    }
}

// Loading Functions
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

// Geocoding Function
async function geocodeLocation(query) {
    try {
        showLoading();
        const response = await fetch(`api/geocode.php?q=${encodeURIComponent(query)}&limit=5`);
        const data = await response.json();
        
        if (data.error) {
            alert('Error: ' + data.message);
            return;
        }
        
        const resultsDiv = document.getElementById('geocodeResults');
        resultsDiv.classList.remove('hidden');
        
        if (data.results.length === 0) {
            resultsDiv.innerHTML = '<p class="text-sm text-gray-400 p-2">Tidak ada hasil ditemukan</p>';
            return;
        }
        
        resultsDiv.innerHTML = data.results.map(result => `
            <div class="bg-dark-blue-900 p-2 mb-2 rounded cursor-pointer hover:bg-dark-blue-600 transition" 
                 onclick="selectGeocodeResult(${result.latitude}, ${result.longitude}, '${result.display_name.replace(/'/g, "\\'")}')">
                <p class="text-sm text-white font-medium">${result.display_name}</p>
                <p class="text-xs text-gray-400">${result.latitude.toFixed(6)}, ${result.longitude.toFixed(6)}</p>
            </div>
        `).join('');
        
    } catch (error) {
        console.error('Geocoding error:', error);
        alert('Error saat mencari lokasi');
    } finally {
        hideLoading();
    }
}

// Select Geocode Result
function selectGeocodeResult(lat, lng, displayName) {
    map.setView([lat, lng], 15);
    
    // Fill form if modal is open
    if (!document.getElementById('sekolahModal').classList.contains('hidden')) {
        document.getElementById('latitudeSekolah').value = lat;
        document.getElementById('longitudeSekolah').value = lng;
        if (!document.getElementById('namaSekolah').value) {
            document.getElementById('namaSekolah').value = displayName;
        }
    }
    
    // Hide results
    document.getElementById('geocodeResults').classList.add('hidden');
}

// Toggle Add Mode
function toggleAddMode() {
    // Hapus validasi layer visibility - bisa tambah marker walaupun layer ON
    addModeActive = !addModeActive;
    const btn = document.getElementById('toggleAddModeBtn');
    const status = document.getElementById('addModeStatus');
    
    if (addModeActive) {
        btn.textContent = '❌ Batal Tambah Marker';
        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
        btn.classList.add('bg-red-600', 'hover:bg-red-700');
        status.textContent = 'Klik di peta untuk menambah marker';
        status.classList.remove('hidden');
        
        // Change cursor
        map.getContainer().style.cursor = 'crosshair';
        
        // Add click handler
        if (!mapClickHandler) {
            const clickHandler = function(e) {
                if (addModeActive) {
                    openSekolahModal(null, e.latlng.lat, e.latlng.lng);
                }
            };
            map.on('click', clickHandler);
            mapClickHandler = clickHandler; // Store function reference, not the event object
        }
    } else {
        btn.textContent = '➕ Tambah Marker';
        btn.classList.remove('bg-red-600', 'hover:bg-red-700');
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
        status.classList.add('hidden');
        
        // Reset cursor
        map.getContainer().style.cursor = '';
        
        // Remove click handler
        if (mapClickHandler) {
            map.off('click', mapClickHandler);
            mapClickHandler = null;
        }
    }
}

// Load Kecamatan List untuk Dropdown
let kecamatanList = [];
async function loadKecamatanList() {
    try {
        const response = await fetch('api/get_kecamatan_list.php');
        const data = await response.json();
        
        if (data.success && data.kecamatan) {
            kecamatanList = data.kecamatan;
            
            // Populate dropdown
            const select = document.getElementById('kecamatanSekolah');
            if (select) {
                // Clear existing options except first
                select.innerHTML = '<option value="">Pilih Kecamatan</option>';
                
                // Add kecamatan options
                kecamatanList.forEach(kec => {
                    const option = document.createElement('option');
                    option.value = kec;
                    option.textContent = kec;
                    select.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error loading kecamatan list:', error);
    }
}

// Open Modal for Create/Edit
async function openSekolahModal(id = null, lat = null, lng = null) {
    const modal = document.getElementById('sekolahModal');
    const form = document.getElementById('sekolahForm');
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtn');
    
    // Pastikan dropdown kecamatan sudah ter-load
    if (kecamatanList.length === 0) {
        await loadKecamatanList();
    }
    
    // Reset form
    form.reset();
    document.getElementById('sekolahId').value = '';
    
    // Pastikan dropdown ter-populate
    const kecamatanSelect = document.getElementById('kecamatanSekolah');
    if (kecamatanSelect && kecamatanSelect.options.length <= 1) {
        // Re-populate jika kosong
        kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
        kecamatanList.forEach(kec => {
            const option = document.createElement('option');
            option.value = kec;
            option.textContent = kec;
            kecamatanSelect.appendChild(option);
        });
    }
    
    if (id) {
        // Edit mode
        currentEditingId = id;
        title.textContent = 'Edit Sekolah';
        submitBtn.textContent = 'Update';
        
        // Find data
        const sekolah = allSekolahData.find(f => f.properties.id === id);
        if (sekolah) {
            const props = sekolah.properties;
            document.getElementById('sekolahId').value = props.id;
            document.getElementById('namaSekolah').value = props.nama_sekolah;
            document.getElementById('jenjangSekolah').value = props.jenjang;
            document.getElementById('kecamatanSekolah').value = props.kecamatan || '';
            document.getElementById('fggpdkSekolah').value = props.fggpdk || '';
            document.getElementById('latitudeSekolah').value = props.latitude;
            document.getElementById('longitudeSekolah').value = props.longitude;
        }
    } else {
        // Create mode
        currentEditingId = null;
        title.textContent = 'Tambah Sekolah Baru';
        submitBtn.textContent = 'Simpan';
        
        if (lat !== null && lng !== null) {
            document.getElementById('latitudeSekolah').value = lat.toFixed(8);
            document.getElementById('longitudeSekolah').value = lng.toFixed(8);
        }
    }
    
    // Re-enable submit button
    submitBtn.disabled = false;
    
    modal.classList.remove('hidden');
    
    // Disable add mode if active
    if (addModeActive) {
        toggleAddMode();
    }
}

// Close Modal
function closeSekolahModal() {
    const modal = document.getElementById('sekolahModal');
    const submitBtn = document.getElementById('submitBtn');
    
    modal.classList.add('hidden');
    currentEditingId = null;
    
    // Reset submit button
    submitBtn.disabled = false;
    if (currentEditingId) {
        submitBtn.textContent = 'Update';
    } else {
        submitBtn.textContent = 'Simpan';
    }
}

// Create Sekolah
async function createSekolah(data) {
    try {
        showLoading();
        const response = await fetch('api/create_sekolah.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        // Get response text first
        const responseText = await response.text();
        
        // Cek jika response tidak OK
        if (!response.ok) {
            console.error('Response error:', responseText);
            alert('Error: Server mengembalikan error. Cek console untuk detail.');
            return false;
        }
        
        // Try to parse JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.error('Response was:', responseText);
            alert('Error: Server mengembalikan response yang tidak valid. Cek console untuk detail.');
            return false;
        }
        
        if (result.error) {
            alert('Error: ' + result.message);
            return false;
        }
        
        alert('Sekolah berhasil ditambahkan!');
        closeSekolahModal();
        loadSekolah();
        loadKecamatanAnalisis(); // Reload analisis untuk update jumlah sekolah
        loadStatistik();
        return true;
    } catch (error) {
        console.error('Create error:', error);
        alert('Error saat menambah sekolah: ' + error.message);
        return false;
    } finally {
        hideLoading();
    }
}

// Update Sekolah
async function updateSekolah(id, data) {
    try {
        showLoading();
        const response = await fetch(`api/update_sekolah.php?id=${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        // Get response text first
        const responseText = await response.text();
        
        // Cek jika response tidak OK
        if (!response.ok) {
            console.error('Response error:', responseText);
            alert('Error: Server mengembalikan error. Cek console untuk detail.');
            return false;
        }
        
        // Try to parse JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.error('Response was:', responseText);
            alert('Error: Server mengembalikan response yang tidak valid. Cek console untuk detail.');
            return false;
        }
        
        if (result.error) {
            alert('Error: ' + result.message);
            return false;
        }
        
        alert('Sekolah berhasil diupdate!');
        closeSekolahModal();
        loadSekolah();
        loadKecamatanAnalisis(); // Reload analisis untuk update jumlah sekolah
        loadStatistik();
        return true;
    } catch (error) {
        console.error('Update error:', error);
        alert('Error saat mengupdate sekolah: ' + error.message);
        return false;
    } finally {
        hideLoading();
    }
}

// Edit Sekolah (called from popup) - harus di window scope agar bisa diakses dari onclick
window.editSekolah = function(id) {
    openSekolahModal(id);
};

// Delete Sekolah (called from popup) - harus di window scope agar bisa diakses dari onclick
window.deleteSekolah = async function(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus sekolah ini?')) {
        return;
    }
    
    try {
        showLoading();
        const response = await fetch(`api/delete_sekolah.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.error) {
            alert('Error: ' + result.message);
            return;
        }
        
        alert('Sekolah berhasil dihapus!');
        
        // Remove marker from map
        if (markerMap.has(id)) {
            sekolahLayer.removeLayer(markerMap.get(id));
            markerMap.delete(id);
        }
        
        loadSekolah();
        loadKecamatanAnalisis(); // Reload analisis untuk update jumlah sekolah
        loadStatistik();
    } catch (error) {
        console.error('Delete error:', error);
        alert('Error saat menghapus sekolah');
    } finally {
        hideLoading();
    }
};

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadKecamatan();
    loadKecamatanAnalisis();
    loadKecamatanList(); // Load kecamatan list for dropdown - HARUS dipanggil di sini
    loadSekolah();
    loadStatistik();
    
    // Filter by Jenjang
    const allCheckbox = document.querySelector('.jenjang-filter[value="All"]');
    const otherCheckboxes = document.querySelectorAll('.jenjang-filter:not([value="All"])');
    
    // Handle "All" checkbox
    allCheckbox.addEventListener('change', function() {
        if (this.checked) {
            // Check all other checkboxes
            otherCheckboxes.forEach(cb => cb.checked = true);
        } else {
            // Uncheck all other checkboxes
            otherCheckboxes.forEach(cb => cb.checked = false);
        }
        updateFilters();
    });
    
    // Handle other checkboxes
    otherCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // If any checkbox is unchecked, uncheck "All"
            const allChecked = Array.from(otherCheckboxes).every(cb => cb.checked);
            allCheckbox.checked = allChecked;
            updateFilters();
        });
    });
    
    function updateFilters() {
        const checked = Array.from(document.querySelectorAll('.jenjang-filter:checked'))
            .map(cb => cb.value)
            .filter(v => v !== 'All'); // Remove "All" from filter array
        currentFilters.jenjang = checked;
        loadSekolah();
    }
    
    // Search Input
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentFilters.search = e.target.value;
            loadSekolah();
        }, 500);
    });
    
    // Layer Toggle
    document.getElementById('toggleKecamatan').addEventListener('change', function(e) {
        kecamatanLayerVisible = e.target.checked;
        if (e.target.checked) {
            map.addLayer(kecamatanLayer);
        } else {
            map.removeLayer(kecamatanLayer);
        }
        // Hapus validasi - tidak perlu matikan add mode jika layer dinyalakan
    });
    
    document.getElementById('toggleAnalisis').addEventListener('change', function(e) {
        analisisLayerVisible = e.target.checked;
        if (e.target.checked) {
            map.addLayer(analisisLayer);
        } else {
            map.removeLayer(analisisLayer);
        }
        // Hapus validasi - tidak perlu matikan add mode jika layer dinyalakan
    });
    
    document.getElementById('toggleSekolah').addEventListener('change', function(e) {
        if (e.target.checked) {
            map.addLayer(sekolahLayer);
        } else {
            map.removeLayer(sekolahLayer);
        }
    });
    
    // Toggle Add Mode Button
    document.getElementById('toggleAddModeBtn').addEventListener('click', toggleAddMode);
    
    // Geocoding
    document.getElementById('geocodeBtn').addEventListener('click', function() {
        const query = document.getElementById('geocodeInput').value.trim();
        if (query) {
            geocodeLocation(query);
        }
    });
    
    document.getElementById('geocodeInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = e.target.value.trim();
            if (query) {
                geocodeLocation(query);
            }
        }
    });
    
    // Modal handlers
    document.getElementById('closeModal').addEventListener('click', closeSekolahModal);
    document.getElementById('cancelBtn').addEventListener('click', closeSekolahModal);
    
    // Close modal on outside click
    document.getElementById('sekolahModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSekolahModal();
        }
    });
    
    // Form Submit
    document.getElementById('sekolahForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable submit button untuk prevent double submit
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';
        
        try {
            const lat = parseFloat(document.getElementById('latitudeSekolah').value);
            const lng = parseFloat(document.getElementById('longitudeSekolah').value);
            
            // Validate coordinates
            if (isNaN(lat) || isNaN(lng)) {
                alert('Koordinat tidak valid!');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }
            
            const formData = {
                nama_sekolah: document.getElementById('namaSekolah').value.trim(),
                jenjang: document.getElementById('jenjangSekolah').value,
                kecamatan: document.getElementById('kecamatanSekolah').value.trim(),
                fggpdk: parseInt(document.getElementById('fggpdkSekolah').value) || 0,
                latitude: lat,
                longitude: lng
            };
            
            // Validate
            if (!formData.nama_sekolah || !formData.jenjang) {
                alert('Nama sekolah dan jenjang wajib diisi!');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }
            
            const id = document.getElementById('sekolahId').value;
            
            let success = false;
            if (id) {
                // Update
                success = await updateSekolah(parseInt(id), formData);
            } else {
                // Create
                success = await createSekolah(formData);
            }
            
            if (!success) {
                // Jika gagal, re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        } catch (error) {
            console.error('Form submit error:', error);
            alert('Error: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
});

