<?php
// File: app/Http/Controllers/UseCaseController.php (Full Code)

namespace App\Http\Controllers;

use App\Models\UseCase;
use App\Models\UatData;
use App\Models\ReportData; // Ubah dari PengkodeanData
use App\Models\DatabaseData;
use App\Models\NavMenu; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Untuk upload file

class UseCaseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:navmenu,menu_id',
            'nama_proses' => 'required|string|max:255',
            'deskripsi_aksi' => 'nullable|string',
            'aktor' => 'nullable|string|max:255',
            'tujuan' => 'nullable|string',
            'kondisi_awal' => 'nullable|string',
            'kondisi_akhir' => 'nullable|string',
            'aksi_reaksi' => 'nullable|string',
            'reaksi_sistem' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // --- Logika Generate usecase_id otomatis PER MENU_ID ---
                $lastUseCaseForMenu = UseCase::where('menu_id', $request->menu_id)
                                             ->orderBy('id', 'desc')
                                             ->first();
                
                $newIdNumber = 1;
                
                if ($lastUseCaseForMenu) {
                    if (preg_match('/-(\d+)$/', $lastUseCaseForMenu->usecase_id, $matches)) {
                        $lastIdNumber = (int)$matches[1];
                        $newIdNumber = $lastIdNumber + 1;
                    }
                }

                $generatedUsecaseId = 'UC-' . $request->menu_id . '-' . str_pad($newIdNumber, 3, '0', STR_PAD_LEFT);

                $useCase = UseCase::create([
                    'menu_id' => $request->menu_id,
                    'usecase_id' => $generatedUsecaseId,
                    'nama_proses' => $request->nama_proses,
                    'deskripsi_aksi' => $request->deskripsi_aksi,
                    'aktor' => $request->aktor,
                    'tujuan' => $request->tujuan,
                    'kondisi_awal' => $request->kondisi_awal,
                    'kondisi_akhir' => $request->kondisi_akhir,
                    'aksi_reaksi' => $request->aksi_reaksi,
                    'reaksi_sistem' => $request->reaksi_sistem,
                ]);
            });

            return response()->json(['success' => 'Data tindakan berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan tindakan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data tindakan.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, UseCase $useCase)
    {
        $request->validate([
            'nama_proses' => 'required|string|max:255',
            'deskripsi_aksi' => 'nullable|string',
            'aktor' => 'nullable|string|max:255',
            'tujuan' => 'nullable|string',
            'kondisi_awal' => 'nullable|string',
            'kondisi_akhir' => 'nullable|string',
            'aksi_reaksi' => 'nullable|string',
            'reaksi_sistem' => 'nullable|string',
        ]);

        try {
            $dataToUpdate = $request->except('usecase_id');
            $useCase->update($dataToUpdate);
            return response()->json(['success' => 'Data tindakan berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui tindakan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data tindakan.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(UseCase $useCase)
    {
        try {
            DB::transaction(function () use ($useCase) {
                $useCase->uatData()->delete();
                $useCase->reportData()->delete(); // Ubah dari pengkodeanData()
                $useCase->databaseData()->delete();
                $useCase->delete();
            });
            return response()->json(['success' => 'Data tindakan berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus tindakan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data tindakan.', 'error' => $e->getMessage()], 500);
        }
    }

    // --- CRUD untuk UAT Data ---
    public function storeUatData(Request $request)
    {
        $request->validate([
            'use_case_id' => 'required|exists:use_cases,id',
            'keterangan_uat' => 'nullable|string',
            'status_uat' => 'nullable|string|in:success,failed,pending',
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);

            // nama_proses_usecase diambil dari nama_proses di UseCase
            $namaProsesUseCase = $useCase->nama_proses;

            $uatData = $useCase->uatData()->create([
                'nama_proses_usecase' => $namaProsesUseCase, // Simpan nama proses dari UseCase
                'keterangan_uat' => $request->keterangan_uat,
                'status_uat' => $request->status_uat,
            ]);

            return response()->json(['success' => 'Data UAT berhasil ditambahkan!', 'uat_data' => $uatData]);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data UAT: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data UAT.', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateUatData(Request $request, UatData $uatData)
    {
        // Validasi input
        $request->validate([
            'nama_proses_usecase' => 'required|string|max:255', // Ini sekarang akan dari input form
            'keterangan_uat' => 'nullable|string',
            'status_uat' => 'nullable|string|in:success,failed,pending',
            'gambar_uat' => 'nullable|string', // CKEditor content is string (HTML)
        ]);

        try {
            $uatData->update([
                'nama_proses_usecase' => $request->nama_proses_usecase,
                'keterangan_uat' => $request->keterangan_uat,
                'status_uat' => $request->status_uat,
                'gambar_uat' => $request->gambar_uat, // Simpan HTML dari CKEditor
            ]);

            return response()->json(['success' => 'Data UAT berhasil diperbarui!', 'uat_data' => $uatData]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data UAT: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data UAT.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyUatData(UatData $uatData)
    {
        try {
            // Jika gambar disimpan sebagai HTML, tidak perlu hapus dari storage.
            // Jika gambar masih disimpan sebagai file di CKEditor, CKEditor harus menghapusnya.
            $uatData->delete();
            return response()->json(['success' => 'Data UAT berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data UAT: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data UAT.', 'error' => $e->getMessage()], 500);
        }
    }

    // --- CRUD untuk DatabaseData ---
    public function storeDatabaseData(Request $request)
    {
        $request->validate([
            'use_case_id' => 'required|exists:use_cases,id',
            'keterangan' => 'nullable|string',
            'relasi' => 'nullable|string',
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            $databaseData = $useCase->databaseData()->create([
                'keterangan' => $request->keterangan,
                'relasi' => $request->relasi,
            ]);

            return response()->json(['success' => 'Data Database berhasil ditambahkan!', 'database_data' => $databaseData], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi gagal saat menyimpan data Database: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data Database.', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateDatabaseData(Request $request, DatabaseData $databaseData)
    {
        $request->validate([
            'keterangan' => 'nullable|string',
            'gambar_database' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // <-- VALIDASI FILE UNTUK DATABASE
            'relasi' => 'nullable|string',
            'gambar_database_current' => 'nullable|string', // Untuk path gambar lama jika tidak ada upload baru
        ]);

        try {
            $imagePath = $databaseData->gambar_database; // Default: pertahankan gambar lama

            if ($request->hasFile('gambar_database')) {
                if ($imagePath) { 
                    $oldPath = str_replace('/storage/', 'public/', $imagePath); 
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $path = $request->file('gambar_database')->store('database_images', 'public');
                $imagePath = '/storage/' . $path;
            } else if ($request->filled('gambar_database_current')) {
                $imagePath = $request->input('gambar_database_current');
            } else {
                if ($imagePath) { 
                    $oldPath = str_replace('/storage/', 'public/', $imagePath);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $imagePath = null;
            }

            $databaseData->update([
                'keterangan' => $request->keterangan,
                'gambar_database' => $imagePath, 
                'relasi' => $request->relasi,
            ]);

            return response()->json(['success' => 'Data Database berhasil diperbarui!', 'database_data' => $databaseData]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi gagal saat memperbarui data Database: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data Database.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyDatabaseData(DatabaseData $databaseData)
    {
        try {
            if ($databaseData->gambar_database) { 
                $pathToDelete = str_replace('/storage/', 'public/', $databaseData->gambar_database); 
                if (Storage::disk('public')->exists($pathToDelete)) {
                    Storage::disk('public')->delete($pathToDelete);
                }
            }
            $databaseData->delete();
            return response()->json(['success' => 'Data Database berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data Database.', 'error' => $e->getMessage()], 500);
        }
    }

        // --- CRUD untuk ReportData ---
    public function storeReportData(Request $request)
    {
        $request->validate([
            'use_case_id' => 'required|exists:use_cases,id',
            'aktor' => 'required|string|max:255',
            'nama_report' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            
            $reportData = $useCase->reportData()->create([
                'aktor' => $request->aktor,
                'nama_report' => $request->nama_report,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json(['success' => 'Data Report berhasil ditambahkan!', 'report_data' => $reportData]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi gagal saat menyimpan data Report: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data Report: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data Report.', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateReportData(Request $request, ReportData $reportData)
    {
        $request->validate([
            'aktor' => 'required|string|max:255',
            'nama_report' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $reportData->update([
                'aktor' => $request->aktor,
                'nama_report' => $request->nama_report,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json(['success' => 'Data Report berhasil diperbarui!', 'report_data' => $reportData]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi gagal saat memperbarui data Report: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data Report: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data Report.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyReportData(ReportData $reportData)
    {
        try {
            $reportData->delete();
            return response()->json(['success' => 'Data Report berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data Report: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data Report.', 'error' => $e->getMessage()], 500);
        }
    }
}