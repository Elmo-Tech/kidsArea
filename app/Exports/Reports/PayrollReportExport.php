<?php

declare(strict_types=1);

namespace App\Exports\Reports;

use App\Enums\SalaryTypeEnum;
use App\Models\EmployeePayroll;
use App\Models\PayrollPeriod;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class PayrollReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithEvents,
    WithStrictNullComparison
{
    public function __construct(
        private readonly PayrollPeriod $period
    ) {
    }

    public function collection(): Collection
    {
        return EmployeePayroll::query()
            ->where(
                'payroll_period_id',
                $this->period->id
            )
            ->with([
                'employee.jobTitle',
            ])
            ->latest('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'اسم الموظف',
            'المسمى الوظيفي',
            'نوع الراتب',
            'من',
            'إلى',
            'أيام الاستحقاق',
            'الراتب الأساسي',
            'الراتب المحتسب',
            'سعر الساعة',
            'دقائق العمل',
            'مستحقات الساعات',
            'سعر الحصة',
            'عدد الحصص',
            'مستحقات الحصص',
            'أيام الغياب',
            'دقائق التأخير غير المبررة',
            'دقائق الخروج المبكر غير المبررة',
            'خصومات الحضور',
            'الإضافات',
            'الخصومات',
            'إجمالي الراتب',
            'صافي الراتب',
        ];
    }

    public function map($payroll): array
    {
        return [
            $payroll->employee->name,

            $payroll->employee->jobTitle?->name,

            $this->getSalaryTypeName(
                $payroll->salary_type
            ),

            $payroll->payable_from?->format('Y-m-d'),

            $payroll->payable_to?->format('Y-m-d'),

            $payroll->proration_days,

            $payroll->basic_salary,

            $payroll->prorated_basic_salary,

            $payroll->hourly_rate,

            $payroll->worked_minutes,

            $payroll->hourly_earnings,

            $payroll->session_rate,

            $payroll->completed_sessions,

            $payroll->session_earnings,

            $payroll->absence_days,

            $payroll->unexcused_late_minutes,

            $payroll->unexcused_early_leave_minutes,

            $payroll->attendance_deductions,

            $payroll->additions_total,

            $payroll->deductions_total,

            $payroll->gross_salary,

            $payroll->net_salary,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                /*
                 * Arabic / RTL
                 */
                $sheet->setRightToLeft(true);

                $sheet->freezePane('A2');

                /*
                 * Header styling
                 */
                $sheet->getStyle('A1:V1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],

                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'D9EAD3',
                        ],
                    ],

                    'alignment' => [
                        'horizontal' =>
                            Alignment::HORIZONTAL_CENTER,

                        'vertical' =>
                            Alignment::VERTICAL_CENTER,
                    ],

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                                Border::BORDER_THIN,

                            'color' => [
                                'rgb' => 'CCCCCC',
                            ],
                        ],
                    ],
                ]);

                /*
                 * Align all report cells.
                 */
                $lastRow = $sheet->getHighestRow();

                $sheet
                    ->getStyle("A1:V{$lastRow}")
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                /*
                 * Number format for salary columns.
                 */
                foreach ([
                    'G',
                    'H',
                    'I',
                    'K',
                    'L',
                    'N',
                    'R',
                    'S',
                    'T',
                    'U',
                    'V',
                ] as $column) {
                    $sheet
                        ->getStyle(
                            "{$column}2:{$column}{$lastRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }
            },
        ];
    }

    private function getSalaryTypeName(
        SalaryTypeEnum $salaryType
    ): string {
        return match ($salaryType) {
            SalaryTypeEnum::MONTHLY =>
                'شهري',

            SalaryTypeEnum::HOURLY =>
                'بالساعة',

            SalaryTypeEnum::SESSION =>
                'بالحصة',

            SalaryTypeEnum::MONTHLY_PLUS_SESSION =>
                'شهري + حصة',
        };
    }
}
