<?php
// Fungsi bantu umum: paginasi dan perhitungan biaya parkir.

/**
 * Hitung durasi tagihan: dibulatkan ke atas per jam, minimal 1 jam.
 *
 * @param int $selisih_detik Lama parkir dalam detik
 * @return int Durasi tagihan (jam)
 */
function hitung_durasi_jam($selisih_detik)
{
    return max(1, (int) ceil($selisih_detik / 3600));
}

/**
 * Hitung tarif per jam yang dikenakan setelah diskon (dibulatkan ke rupiah).
 *
 * @param float $tarif_dasar    Tarif dasar per jam
 * @param float $diskon_persen  Diskon 0-100 (0 untuk non-member)
 * @return float Tarif per jam yang dikenakan
 */
function hitung_tarif_dikenakan($tarif_dasar, $diskon_persen)
{
    $diskon_persen = min(100, max(0, (float) $diskon_persen));
    return round((float) $tarif_dasar * (1 - $diskon_persen / 100));
}

/**
 * Siapkan data paginasi dari total baris.
 *
 * @param int    $total Total baris seluruhnya
 * @param int    $per   Baris per halaman
 * @param string $key   Nama parameter GET untuk nomor halaman
 * @return array ['hal','per','offset','total_hal','total','key']
 */
function siapkan_paginasi($total, $per = 10, $key = 'hal')
{
    $total = max(0, (int) $total);
    $per = max(1, (int) $per);
    $total_hal = max(1, (int) ceil($total / $per));
    $hal = min($total_hal, max(1, intval($_GET[$key] ?? 1)));
    return [
        'hal' => $hal,
        'per' => $per,
        'offset' => ($hal - 1) * $per,
        'total_hal' => $total_hal,
        'total' => $total,
        'key' => $key,
    ];
}

/**
 * Cetak tautan Sebelumnya / Berikutnya. Parameter GET lain (mis. filter tanggal) dipertahankan.
 *
 * @param array $p Hasil siapkan_paginasi()
 */
function render_paginasi($p)
{
    if ($p['total_hal'] <= 1) {
        return;
    }
    $link = function ($n) use ($p) {
        return '?' . htmlspecialchars(http_build_query(array_merge($_GET, [$p['key'] => $n])));
    };
    echo '<div class="btn-row" style="margin-top: 12px; align-items: center;">';
    if ($p['hal'] > 1) {
        echo '<a href="' . $link($p['hal'] - 1) . '" class="btn secondary sm">&laquo; Sebelumnya</a>';
    }
    echo '<span style="margin: 0 12px;">Halaman ' . $p['hal'] . ' dari ' . $p['total_hal'] . ' (' . $p['total'] . ' data)</span>';
    if ($p['hal'] < $p['total_hal']) {
        echo '<a href="' . $link($p['hal'] + 1) . '" class="btn secondary sm">Berikutnya &raquo;</a>';
    }
    echo '</div>';
}
