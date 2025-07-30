<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UAT_IMAGES;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


class UAT_Image extends Controller
{
 
    public function storeImages(Request $request)
    {
        $request->validate([
            'uats_id' => 'required|exists:uat_data,id_uat',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        foreach ($request->file('images') as $image) {
            if ($image->isValid()) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('img/uats_img'), $filename);

                $path = 'img/uats_img/' . $filename; // tanpa 'public/' agar bisa dipakai di <img src>

                UAT_IMAGES::create([
                    'uats_id' => $request->uats_id,
                    'link' => $path,
                ]);
            }
        }

        return back()->with('success', 'Gambar berhasil disimpan');
    }

    public function deleteImage($id)
        {
            $image = UAT_IMAGES::findOrFail($id);

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
