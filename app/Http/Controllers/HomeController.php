<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected array $data = [];
    protected array $totalByMenu = [];
    protected array $totalByBulan = [];

    protected array $totalPerKategori = [];

    public function index(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $tahun = $request['tahun'];
        if ($tahun != null) {
            $this->hitungData($tahun);

            return view('index', [
                'data' => $this->data,
                'menuTotal' => $this->totalByMenu,
                'bulanTotal' => $this->totalByBulan,
                'menuTotalKategori' => $this->totalPerKategori,
                'tahun' => $tahun
            ]);
        }

        return view('index');
    }

    public function menu(): void
    {
        var_dump(file_get_contents("https://tes-web.landa.id/intermediate/menu"));
    }

    public function transaksi($tahun): void
    {
        var_dump(file_get_contents("https://tes-web.landa.id/intermediate/transaksi?tahun=" . $tahun));
    }

    public function hitungData($tahun): void
    {
        $menu = json_decode(file_get_contents("https://tes-web.landa.id/intermediate/menu"), true);
        $transaksi = json_decode(file_get_contents("https://tes-web.landa.id/intermediate/transaksi?tahun=" . $tahun), true);

        foreach ($menu as $value) {
            $this->totalByMenu[$value['menu']] = 0;

            for ($i = 1; $i <= 12; $i++) {
                $this->data[$value['kategori']][$value['menu']][$i] = 0;
                $this->totalByBulan[$i] = 0;
                $this->totalPerKategori[$value['kategori']][$i] = 0;
            }
        }

        foreach ($transaksi as $value) {
            $kategori = $this->cariKategori($value['menu']);
            $bulan = substr($value['tanggal'], 5, 2);
            $bulan = ltrim($bulan, '0');

            $this->data[$kategori][$value['menu']][$bulan] += $value['total'];
            $this->totalByMenu[$value['menu']] += $value['total'];
            $this->totalByBulan[$bulan] += $value['total'];
            $this->totalPerKategori[$kategori][$bulan] += $value['total'];
        }
    }

    public function cariKategori($menu): string|null
    {
        foreach ($this->data as $key => $value) {
            if (isset($this->data[$key][$menu])) {
                return $key;
            }
        }

        return null;
    }
}
