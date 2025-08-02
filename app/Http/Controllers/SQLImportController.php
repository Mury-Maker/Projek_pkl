<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocColumns;
use App\Models\DocTables;
use App\Models\DocSqlFile;
use App\Models\DocRelations;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SQLImportController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt',
            'navmenu_id' => 'required|exists:navmenu,menu_id',
        ]);

        $navmenuId = $request->input('navmenu_id');
        $uploadedFile = $request->file('sql_file');

        $filename = $request->sql_file->getClientOriginalName();

        // Simpan ke disk 'public' → storage/app/public/sql_files/...
        $path = $uploadedFile->storeAs('sql_files', $filename, 'public');

        // Simpan metadata file
        $sqlFile = DocSqlFile::updateOrCreate(
            ['navmenu_id' => $navmenuId],
            ['file_name' => $filename, 'file_path' => 'public/' . $path]
        );

        $exists = Storage::disk('public')->exists('sql_files/' . $filename);
        Log::info("File disimpan di: public/sql_files/{$filename} | Exists? " . ($exists ? 'yes' : 'no'));

        Log::info("Upload SQL berhasil untuk navmenu_id: {$navmenuId}, file: {$filename}");

        return redirect()->back()->with('success', 'File SQL berhasil diupload.');
    }
public function parse(Request $request, $navmenuId)
{
    $parserType = $request->input('parser', 'phpmyadmin');
    $sqlFile = DocSqlFile::where('navmenu_id', $navmenuId)->first();

    if (!$sqlFile || !Storage::disk('public')->exists('sql_files/' . $sqlFile->file_name)) {
        Log::error("SQL file tidak ditemukan untuk navmenu_id: {$navmenuId}");
        return back()->with('error', 'File SQL tidak ditemukan.');
    }

    $fullPath = Storage::disk('public')->path('sql_files/' . $sqlFile->file_name);
    $sqlContent = file_get_contents($fullPath);

    Log::info("Memulai parsing SQL file: {$sqlFile->file_name} untuk navmenu_id: {$navmenuId} dengan parser: {$parserType}");

    // Hapus data sebelumnya
    DocTables::where('menu_id', $navmenuId)->delete();

    // Support: CREATE TABLE [IF NOT EXISTS]
    preg_match_all('/CREATE TABLE(?: IF NOT EXISTS)? `(.*?)`\s*\((.*?)\)\s*(ENGINE|TYPE)=/si', $sqlContent, $matches, PREG_SET_ORDER);
    $tableMap = [];

    foreach ($matches as $match) {
        $tableName = $match[1];
        $rawColumns = $match[2];

        Log::info("Tabel ditemukan: {$tableName}");

        $table = DocTables::create([
            'menu_id' => $navmenuId,
            'nama_tabel' => $tableName,
        ]);
        $tableMap[$tableName] = $table;

        // PRIMARY KEY
        preg_match_all('/PRIMARY KEY\s+\(`(.*?)`\)/i', $rawColumns, $pkMatches);
        $primaryKeys = isset($pkMatches[1][0]) ? explode('`,`', $pkMatches[1][0]) : [];

        // Kolom
        preg_match_all('/`([^`]+)`\s+([^\n,]+)/i', $rawColumns, $columnMatches, PREG_SET_ORDER);
        foreach ($columnMatches as $col) {
            $name = $col[1];
            $type = $col[2];
            $isPrimary = in_array($name, $primaryKeys);
            $isNullable = !str_contains(strtolower($type), 'not null');
            $isUnique = str_contains(strtolower($type), 'unique');

            DocColumns::create([
                'table_id' => $table->id,
                'nama_kolom' => $name,
                'tipe' => $type,
                'is_primary' => $isPrimary,
                'is_nullable' => $isNullable,
                'is_unique' => $isUnique,
            ]);
        }

        // Inline FK (umum di SQLyog dan HeidiSQL)
        preg_match_all('/CONSTRAINT `.*?` FOREIGN KEY \(`(.*?)`\) REFERENCES `(.*?)` \(`(.*?)`\)/i', $rawColumns, $inlineFks, PREG_SET_ORDER);
        foreach ($inlineFks as $fk) {
            $fromColumn = $fk[1];
            $toTableName = $fk[2];
            $toColumn = $fk[3];

            $toTable = $tableMap[$toTableName] ?? DocTables::where('menu_id', $navmenuId)->where('nama_tabel', $toTableName)->first();
            if ($toTable) {
                DocRelations::create([
                    'from_tableid' => $table->id,
                    'from_columnid' => $fromColumn,
                    'to_tableid' => $toTable->id,
                    'to_columnid' => $toColumn,
                ]);
                Log::info("→ Relasi inline: {$tableName}.{$fromColumn} → {$toTableName}.{$toColumn}");
            }
        }
    }

    // Alter FK (phpMyAdmin & SQLyog)
    if ($parserType === 'phpmyadmin' || $parserType === 'sqlyog') {
        preg_match_all('/ALTER TABLE `(.*?)` ADD CONSTRAINT `(.*?)` FOREIGN KEY \(`(.*?)`\) REFERENCES `(.*?)` \(`(.*?)`\)/i', $sqlContent, $fkMatches, PREG_SET_ORDER);
        foreach ($fkMatches as $rel) {
            $fromTableName = $rel[1];
            $fromColumn = $rel[3];
            $toTableName = $rel[4];
            $toColumn = $rel[5];

            $fromTable = $tableMap[$fromTableName] ?? DocTables::where('menu_id', $navmenuId)->where('nama_tabel', $fromTableName)->first();
            $toTable = $tableMap[$toTableName] ?? DocTables::where('menu_id', $navmenuId)->where('nama_tabel', $toTableName)->first();

            if ($fromTable && $toTable) {
                DocRelations::create([
                    'from_tableid' => $fromTable->id,
                    'from_columnid' => $fromColumn,
                    'to_tableid' => $toTable->id,
                    'to_columnid' => $toColumn,
                ]);
                Log::info("→ Relasi ALTER: {$fromTableName}.{$fromColumn} → {$toTableName}.{$toColumn}");
            }
        }
    }

    // HeidiSQL: FK inline tapi bisa tanpa CONSTRAINT NAME
    if ($parserType === 'heidisql') {
        preg_match_all('/FOREIGN KEY \(`(.*?)`\) REFERENCES `(.*?)` \(`(.*?)`\)/i', $sqlContent, $fkMatches, PREG_SET_ORDER);
        foreach ($fkMatches as $rel) {
            $fromColumn = $rel[1];
            $toTableName = $rel[2];
            $toColumn = $rel[3];

            // Coba tebak nama tabel asal dari kontekstual `CREATE TABLE`
            $fromTableName = null;
            if (preg_match('/CREATE TABLE(?: IF NOT EXISTS)? `(.*?)`.*?FOREIGN KEY \(`' . preg_quote($fromColumn, '/') . '`\)/si', $sqlContent, $contextMatch)) {
                $fromTableName = $contextMatch[1];
            }

            if ($fromTableName) {
                $fromTable = $tableMap[$fromTableName] ?? DocTables::where('menu_id', $navmenuId)->where('nama_tabel', $fromTableName)->first();
                $toTable = $tableMap[$toTableName] ?? DocTables::where('menu_id', $navmenuId)->where('nama_tabel', $toTableName)->first();

                if ($fromTable && $toTable) {
                    DocRelations::create([
                        'from_tableid' => $fromTable->id,
                        'from_columnid' => $fromColumn,
                        'to_tableid' => $toTable->id,
                        'to_columnid' => $toColumn,
                    ]);
                    Log::info("→ Relasi HeidiSQL: {$fromTableName}.{$fromColumn} → {$toTableName}.{$toColumn}");
                }
            }
        }
    }

    return back()->with('success', 'Berhasil memparsing SQL dan membentuk relasi.');
}

    public function uploadAndParse(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt',
            'navmenu_id' => 'required|exists:navmenu,menu_id',
        ]);

        $navmenuId = $request->input('navmenu_id');
        $uploadedFile = $request->file('sql_file');
        $filename = $uploadedFile->getClientOriginalName();

        // Simpan file
        $path = $uploadedFile->storeAs('sql_files', $filename, 'public');

        // Simpan metadata
        $sqlFile = DocSqlFile::updateOrCreate(
            ['navmenu_id' => $navmenuId],
            ['file_name' => $filename, 'file_path' => 'public/' . $path]
        );

        Log::info("File SQL berhasil diupload: {$filename}");

        // Ambil isi SQL
        $fullPath = Storage::disk('public')->path('sql_files/' . $filename);
        $sqlContent = file_get_contents($fullPath);

        Log::info("Memulai parsing SQL untuk navmenu_id: {$navmenuId}");

        // Kosongkan tabel dan relasi lama
        $deletedTables = DocTables::where('menu_id', $navmenuId)->delete();
        Log::info("Menghapus {$deletedTables} tabel lama untuk navmenu_id: {$navmenuId}");

        preg_match_all('/CREATE TABLE `(.*?)`\s*\((.*?)\)\s*ENGINE=/si', $sqlContent, $matches, PREG_SET_ORDER);

        $tableMap = [];
        foreach ($matches as $match) {
            $tableName = $match[1];
            $rawColumns = $match[2];

            $table = DocTables::create([
                'menu_id' => $navmenuId,
                'nama_tabel' => $tableName,
            ]);
            $tableMap[$tableName] = $table;

            preg_match_all('/PRIMARY KEY\s+\(`(.*?)`\)/i', $rawColumns, $pkMatches);
            $primaryKeys = isset($pkMatches[1][0]) ? explode('`,`', $pkMatches[1][0]) : [];

            preg_match_all('/`([^`]+)`\s+([^\n,]+)/i', $rawColumns, $columnMatches, PREG_SET_ORDER);

            foreach ($columnMatches as $colMatch) {
                $name = $colMatch[1];
                $type = $colMatch[2];

                DocColumns::create([
                    'table_id' => $table->id,
                    'nama_kolom' => $name,
                    'tipe' => $type,
                    'is_primary' => in_array($name, $primaryKeys),
                    'is_nullable' => !str_contains(strtolower($type), 'not null'),
                    'is_unique' => str_contains(strtolower($type), 'unique'),
                ]);
            }

            preg_match_all('/CONSTRAINT `.*?` FOREIGN KEY \(`(.+?)`\) REFERENCES `(.+?)` \(`(.+?)`\)/i', $rawColumns, $inlineFks, PREG_SET_ORDER);

            foreach ($inlineFks as $fk) {
                $fromColumn = $fk[1];
                $toTableName = $fk[2];
                $toColumn = $fk[3];

                $toTable = $tableMap[$toTableName] ?? DocTables::where('menu_id', $navmenuId)->where('nama_tabel', $toTableName)->first();

                if ($toTable) {
                    DocRelations::create([
                        'from_tableid' => $table->id,
                        'from_columnid' => $fromColumn,
                        'to_tableid' => $toTable->id,
                        'to_columnid' => $toColumn,
                    ]);
                }
            }
        }

        preg_match_all('/ALTER TABLE `(.*?)` ADD CONSTRAINT `(.*?)` FOREIGN KEY \(`(.*?)`\) REFERENCES `(.*?)` \(`(.*?)`\)/i', $sqlContent, $alterFks, PREG_SET_ORDER);

        foreach ($alterFks as $rel) {
            $fromTableName = $rel[1];
            $fromColumn = $rel[3];
            $toTableName = $rel[4];
            $toColumn = $rel[5];

            $fromTable = $tableMap[$fromTableName] ?? DocTables::where('menu_id', $navmenuId)->where('nama_tabel', $fromTableName)->first();
            $toTable = $tableMap[$toTableName] ?? DocTables::where('menu_id', $navmenuId)->where('nama_tabel', $toTableName)->first();

            if ($fromTable && $toTable) {
                DocRelations::create([
                    'from_tableid' => $fromTable->id,
                    'from_columnid' => $fromColumn,
                    'to_tableid' => $toTable->id,
                    'to_columnid' => $toColumn,
                ]);
            }
        }

        Log::info("Parsing selesai untuk navmenu_id: {$navmenuId}");

        return redirect()->back()->with('success', 'File SQL berhasil diupload dan diparsing.');
    }



    // Hapus File dari penyimpanan lokal dan Database
    public function destroy($navmenuId)
    {
        $sqlFile = DocSqlFile::where('navmenu_id', $navmenuId)->first();

        if (!$sqlFile) {
            return back()->with('error', 'File SQL tidak ditemukan di database.');
        }

        // Hapus file fisik
        $filePath = 'sql_files/' . $sqlFile->file_name;
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        // Hapus data dari database
        $tableIds = DocTables::where('menu_id', $navmenuId)->pluck('id');

        DocRelations::whereIn('from_tableid', $tableIds)
                    ->orWhereIn('to_tableid', $tableIds)
                    ->delete();

        DocColumns::whereIn('table_id', $tableIds)->delete();
        DocTables::where('menu_id', $navmenuId)->delete();
        $sqlFile->delete();

        return back()->with('success', 'File SQL dan datanya berhasil dihapus.');
    }


}
