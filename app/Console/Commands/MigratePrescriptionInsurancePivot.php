<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrescriptionRequest;

class MigratePrescriptionInsurancePivot extends Command
{
    protected $signature = 'migrate:prescription-insurance-pivot';
    protected $description = 'Migrate prescription_insurance_id/referral_code to the new pivot table';

    public function handle()
    {
        $count = 0;
        $prescriptions = PrescriptionRequest::whereNotNull('prescription_insurance_id')->get();
        foreach ($prescriptions as $presc) {
            if ($presc->insurances()->where('prescription_insurance_id', $presc->prescription_insurance_id)->doesntExist()) {
                $presc->insurances()->attach($presc->prescription_insurance_id, [
                    'referral_code' => $presc->referral_code ?? null,
                ]);
                $count++;
            }
        }
        $this->info("Migrated $count prescriptions to pivot table.");
    }
}
