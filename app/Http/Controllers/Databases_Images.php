<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DATABASE_IMAGES;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class Databases_Images extends Controller
{
        public function storeImages(Request $request)
            {
                $request->validate([
                    'databases_id' => 'required|exists:database_data,id_database',
                    'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
                ]);

                foreach ($request->file('images') as $image) {
                    if ($image->isValid()) {
                        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                        $image->move(public_path('img/databases_img'), $filename);

                        $path = 'img/databases_img/' . $filename; // tanpa 'public/' agar bisa dipakai di <img src>

                        DATABASE_IMAGES::create([
                            'databases_id' => $request->databases_id,
                            'link' => $path,
                        ]);
                    }
                }

                return back()->with('success', 'Gambar berhasil disimpan');
            }

    public function deleteImages($id)
        {
            $image = DATABASE_IMAGES::findOrFail($id);

            // Hapus file dari folder publik
            $fullPath = public_path($image->link);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }

            // Hapus dari database
            $image->delete();

            return back()->with('success', 'Gambar berhasil dihapus');
        }

}
