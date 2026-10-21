<?php

namespace App\Controllers;

use App\Models\CucianModel;
use CodeIgniter\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;

class Laporan extends Controller
{
    public function index()
    {
        $mulai  = $this->request->getGet('mulai') ?? date('Y-m-d', strtotime('-6 days'));
        $sampai = $this->request->getGet('sampai') ?? date('Y-m-d');

        $cucianModel = new CucianModel();

        $data = [
            'title'    => 'Laporan & Rekap Pendapatan',
            'mulai'    => $mulai,
            'sampai'   => $sampai,
            'rekap'    => $cucianModel->rekapPendapatanHarian($mulai, $sampai),
            'populer'  => $cucianModel->layananTerpopuler(),
            'diproses' => $cucianModel->jumlahDiproses(),
            'pendapatan_hari_ini'  => $cucianModel->pendapatanHariIni(),
            'pendapatan_bulan_ini' => $cucianModel->pendapatanBulanIni(),
        ];

        return view('laporan/index', $data);
    }

    /** Export rekap pendapatan ke PDF. Butuh: composer require dompdf/dompdf */
    public function exportPdf()
    {
        $mulai  = $this->request->getGet('mulai') ?? date('Y-m-d', strtotime('-6 days'));
        $sampai = $this->request->getGet('sampai') ?? date('Y-m-d');

        $cucianModel = new CucianModel();
        $rekap       = $cucianModel->rekapPendapatanHarian($mulai, $sampai);
        $total       = array_sum(array_column($rekap, 'total'));

        $html = view('laporan/pdf', [
            'rekap'  => $rekap,
            'mulai'  => $mulai,
            'sampai' => $sampai,
            'total'  => $total,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $namaFile = 'rekap-pendapatan-' . $mulai . '-sd-' . $sampai . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $namaFile . '"')
            ->setBody($dompdf->output());
    }
}
