<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PaymentsExport implements FromView, WithEvents
{
    use Exportable;

    public function view(): \Illuminate\Contracts\View\View
    {
        return view('exports.payments', [
            'payments' => Payment::with('user:id,name')->get()
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Make sheet right to left
                $event->sheet->setRightToLeft(true);
                // $event->sheet->getDelegate()->getStyle('A1:G1')->getFont()->setBold(true);
            }
        ];
    }

    
}
