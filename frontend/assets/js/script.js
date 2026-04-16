// Konfigurasi API
const API_BASE_URL = 'http://localhost/anggota-manager/backend/anggota';
let currentPage = 1;
let currentSearch = '';
let debounceTimer;

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    loadData();
    setupSearchListener();
});

// Setup search dengan debounce
function setupSearchListener() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            currentSearch = e.target.value;
            currentPage = 1;
            loadData();
        }, 500);
    });
}

// Load data dari API
async function loadData() {
    try {
        const url = `${API_BASE_URL}?page=${currentPage}&search=${encodeURIComponent(currentSearch)}`;
        const response = await fetch(url);
        const result = await response.json();

        if (response.ok) {
            renderTable(result.data);
            renderPagination(result.pagination);
        } else {
            showNotification(result.message || 'Gagal memuat data', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan jaringan', 'error');
    }
}

// Render tabel
function renderTable(data) {
    const tbody = document.getElementById('tableBody');
    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data anggota</td></tr>`;
        return;
    }

    tbody.innerHTML = data.map(anggota => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(anggota.nama)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${escapeHtml(anggota.email)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${escapeHtml(anggota.no_hp)}</td>
            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">${escapeHtml(anggota.alamat)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="editAnggota(${anggota.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteAnggota(${anggota.id})" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Render pagination
function renderPagination(pagination) {
    const { current_page, per_page, total, total_pages } = pagination;
    
    document.getElementById('startRange').textContent = total === 0 ? 0 : (current_page - 1) * per_page + 1;
    document.getElementById('endRange').textContent = Math.min(current_page * per_page, total);
    document.getElementById('totalItems').textContent = total;

    const container = document.getElementById('paginationButtons');
    if (total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    
    // Previous button
    html += `<button onclick="goToPage(${current_page - 1})" ${current_page === 1 ? 'disabled' : ''} 
             class="px-3 py-1 border rounded-md ${current_page === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'}">
             <i class="fas fa-chevron-left"></i></button>`;

    // Page numbers (show max 5 pages)
    let startPage = Math.max(1, current_page - 2);
    let endPage = Math.min(total_pages, current_page + 2);

    if (startPage > 1) {
        html += `<button onclick="goToPage(1)" class="px-3 py-1 border rounded-md hover:bg-gray-100">1</button>`;
        if (startPage > 2) html += `<span class="px-2">...</span>`;
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<button onclick="goToPage(${i})" 
                 class="px-3 py-1 border rounded-md ${i === current_page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}">${i}</button>`;
    }

    if (endPage < total_pages) {
        if (endPage < total_pages - 1) html += `<span class="px-2">...</span>`;
        html += `<button onclick="goToPage(${total_pages})" class="px-3 py-1 border rounded-md hover:bg-gray-100">${total_pages}</button>`;
    }

    // Next button
    html += `<button onclick="goToPage(${current_page + 1})" ${current_page === total_pages ? 'disabled' : ''} 
             class="px-3 py-1 border rounded-md ${current_page === total_pages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'}">
             <i class="fas fa-chevron-right"></i></button>`;

    container.innerHTML = html;
}

// Navigasi halaman
function goToPage(page) {
    currentPage = page;
    loadData();
}

// Buka modal untuk tambah
function openModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Anggota';
    document.getElementById('anggotaForm').reset();
    document.getElementById('anggotaId').value = '';
    document.getElementById('modal').classList.remove('hidden');
}

// Tutup modal
function closeModal() {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('anggotaForm').reset();
}

// Edit anggota
async function editAnggota(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/${id}`);
        const data = await response.json();

        if (response.ok) {
            document.getElementById('modalTitle').textContent = 'Edit Anggota';
            document.getElementById('anggotaId').value = data.id;
            document.getElementById('nama').value = data.nama;
            document.getElementById('email').value = data.email;
            document.getElementById('no_hp').value = data.no_hp;
            document.getElementById('alamat').value = data.alamat;
            document.getElementById('modal').classList.remove('hidden');
        } else {
            showNotification('Data anggota tidak ditemukan', 'error');
        }
    } catch (error) {
        showNotification('Gagal mengambil data', 'error');
    }
}

// Simpan data (create/update)
async function saveAnggota(event) {
    event.preventDefault();

    const id = document.getElementById('anggotaId').value;
    const formData = {
        nama: document.getElementById('nama').value.trim(),
        email: document.getElementById('email').value.trim(),
        no_hp: document.getElementById('no_hp').value.trim(),
        alamat: document.getElementById('alamat').value.trim()
    };

    // Validasi client-side tambahan
    if (!formData.nama || !formData.email || !formData.no_hp || !formData.alamat) {
        showNotification('Semua field harus diisi', 'error');
        return;
    }
    if (!/^[0-9]{10,15}$/.test(formData.no_hp)) {
        showNotification('Nomor HP harus 10-15 digit angka', 'error');
        return;
    }

    const url = id ? `${API_BASE_URL}/${id}` : API_BASE_URL;
    const method = id ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.ok) {
            showNotification(result.message, 'success');
            closeModal();
            loadData();
        } else {
            showNotification(result.message || 'Gagal menyimpan data', 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan jaringan', 'error');
    }
}

// Hapus anggota
async function deleteAnggota(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus anggota ini?')) return;

    try {
        const response = await fetch(`${API_BASE_URL}/${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (response.ok) {
            showNotification(result.message, 'success');
            // Jika halaman saat ini kosong setelah hapus, kembali ke halaman sebelumnya
            const totalItems = parseInt(document.getElementById('totalItems').textContent);
            if ((totalItems - 1) % 5 === 0 && currentPage > 1) {
                currentPage--;
            }
            loadData();
        } else {
            showNotification(result.message || 'Gagal menghapus data', 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan jaringan', 'error');
    }
}

// Fungsi notifikasi
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white ${bgColor} transition-opacity`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    notification.classList.remove('hidden');

    setTimeout(() => {
        notification.classList.add('hidden');
    }, 3000);
}

// Helper untuk escape HTML (mencegah XSS)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Tutup modal jika klik di luar
window.onclick = function(event) {
    const modal = document.getElementById('modal');
    if (event.target === modal) {
        closeModal();
    }
}