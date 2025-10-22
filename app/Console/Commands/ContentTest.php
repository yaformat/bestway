<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{
    Kitchen,
    KitchenTranslation,
    Supplier,
    SupplierTranslation,
    Resource,
    ResourceTranslation,
    ResourceCategory,
    ResourceCategoryTranslation,
    TechCard,
    TechCardTranslation,
    TechCardCategory,
    TechCardCategoryTranslation,
    TechCardStep,
    TechCardStepTranslation
};

class ContentTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:content-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Песочника по контенту';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //$this->fillTechCardStepsNotice();
        //$this->fillTechCardStepsTimers();
        //$this->fillTechCardStepsResource();

        //
    }

    private function fillTechCardStepsResource()
    {

    }

    private function fillTechCardStepsTimers()
    {
        $items = TechCardStep::whereHas('translations', function($query){
            $query->whereNotNull('description_notice');
            $query->whereNull('timer_name');
        })->get();

        foreach ($items as $item) {

            $techCardNextStepsIds = TechCardStep::where('tech_card_id', $item->tech_card_id)->where('id', '>', $item->id)->pluck('id')->toArray();

            $timer_hours_a = [0,1];
            $timer_minutes_a = [15,20,30,45];

            $timer_names_a = [
                'Варка мяса',
                'Тесто',
                'Маринование',
                'Тушение овощей',
                'Тестовый таймер',
                '',
            ];
            
            $timer_hours = $timer_hours_a[array_rand($timer_hours_a)];
            $timer_minutes = $timer_minutes_a[array_rand($timer_minutes_a)];

            $timer_name = $timer_names_a[array_rand($timer_names_a)];

            if (count($techCardNextStepsIds) <= 2) {
                $timer_next_step_id = 0;
            } else {
                $timer_next_step_id = (count($techCardNextStepsIds) > 3) ? $techCardNextStepsIds[2] : $techCardNextStepsIds[1];
            }

            echo $item->id.' '.$timer_name.' -> '.$timer_next_step_id.' ('.$timer_hours.':'.$timer_minutes.') :'.count($techCardNextStepsIds);
            echo PHP_EOL;

            $item->timer_name = $timer_name;
            $item->timer_next_step_id = $timer_next_step_id;
            $item->timer_hours = $timer_hours;
            $item->timer_minutes = $timer_minutes;
            $item->save();

        }
    }

    private function fillTechCardStepsNotice()
    {
        //fill tech card steps description notice...
        //$items = TechCardStep::whereNull('description_notice')->get();
        $items = TechCardStep::whereHas('translations', function($query){
            $query->whereNull('description_notice');
        })->get();

        foreach ($items as $item) {
            $stepDescription = $item->description;
            $parts = explode(PHP_EOL, $stepDescription);
            if (count($parts) > 1) {
                $lastPart = array_pop($parts);
                $lastPart = trim($lastPart);
                if (mb_strlen($lastPart) > 70) {
                    echo $item->tech_card_id.' : '.$lastPart;
                    echo PHP_EOL;


                    $item->description = implode(PHP_EOL, $parts);
                    $item->description_notice = $lastPart;
                    $item->save();
                }
            }
        }
    }
}
