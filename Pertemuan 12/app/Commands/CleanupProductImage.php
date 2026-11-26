<?php

namespace App\Commands;

use App\Libraries\ProductImageStorage;
use App\Models\ProductImageModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Throwable;

class CleanupProductImage extends BaseCommand
{
    protected $group = 'ShopEase';
    protected $name = 'products:cleanup-image';
    protected $description = 'Menghapus satu file upload yang sudah tidak dirujuk katalog.';
    protected $usage = 'products:cleanup-image <path-relatif>';

    public function run(array $params)
    {
        $storage = new ProductImageStorage();
        $path = $params[0] ?? '';
        if (count($params) !== 1 || ! $storage->isUploadPath($path)) {
            CLI::error('Gunakan satu path upload produk yang tercatat di log.');

            return EXIT_ERROR;
        }

        try {
            $db = Database::connect();
            if ($db->getDatabase() !== 'db_shopease_p12' || (new ProductImageModel($db))->references($path)) {
                CLI::error('Pastikan database Pertemuan 12 aktif dan gambar sudah tidak dirujuk.');

                return EXIT_ERROR;
            }
            $file = $storage->file($path);
            // Leave recent files alone while an upload transaction could still be running.
            if ($file !== null && (($updated = @filemtime($file)) === false || $updated > time() - 300)) {
                CLI::error('File masih baru. Tunggu lima menit sebelum mencoba lagi.');

                return EXIT_ERROR;
            }
            if ($storage->remove($path)) {
                CLI::write('File upload sudah bersih.', 'green');

                return EXIT_SUCCESS;
            }
        } catch (Throwable) {
            // Treat a failed reference check as a failed cleanup.
        }
        CLI::error('File belum bisa dibersihkan. Periksa akses penyimpanan dan koneksi database.');

        return EXIT_ERROR;
    }
}
