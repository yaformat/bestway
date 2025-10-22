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

class FillTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Заполнение переводов по-умолчанию';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // // Получите все записи из Resource
        $items = Resource::all();
        foreach ($items as $item) {

            $check = ResourceTranslation::where('resource_id', $item->id)->first();
            if ($check) continue;

            try {
                $translate = new ResourceTranslation;
                $translate->resource_id = $item->id;
                $translate->locale = 'ru';
                $translate->name = $item->name;
                $translate->save();

            } catch(\Exception $e) {
                echo 'error: RESOURCE ID '.$item->id;
            }

        }

        
        $items = ResourceCategory::all();
        foreach ($items as $item) {

            $check = ResourceCategoryTranslation::where('resource_category_id', $item->id)->first();
            if ($check) continue;

            try {
                $translate = new ResourceCategoryTranslation;
                $translate->resource_category_id = $item->id;
                $translate->locale = 'ru';
                $translate->name = $item->name;
                $translate->save();

            } catch(\Exception $e) {
                echo 'error: RESOURCE CATEGORY ID '.$item->id;
            }

        }

        
        $items = TechCard::all();
        foreach ($items as $item) {

            $check = TechCardTranslation::where('tech_card_id', $item->id)->first();
            if ($check) continue;

            try {
                $translate = new TechCardTranslation;
                $translate->tech_card_id = $item->id;
                $translate->locale = 'ru';
                $translate->name = $item->name;
                $translate->description = $item->description;
                $translate->save();

            } catch(\Exception $e) {
                echo 'error: TECH CARD ID '.$item->id;
            }

        }


        $items = TechCard::all();
        foreach ($items as $item) {

            $check = TechCardTranslation::where('tech_card_id', $item->id)->first();
            if ($check) continue;

            try {
                $translate = new TechCardTranslation;
                $translate->tech_card_id = $item->id;
                $translate->locale = 'ru';
                $translate->name = $item->name;
                $translate->description = $item->description;
                $translate->save();

            } catch(\Exception $e) {
                echo 'error: TECH CARD ID '.$item->id;
            }

        }

        $items = TechCardCategory::all();
        foreach ($items as $item) {

            $check = TechCardCategoryTranslation::where('tech_card_category_id', $item->id)->first();
            if ($check) continue;

            try {
                $translate = new TechCardCategoryTranslation;
                $translate->tech_card_category_id = $item->id;
                $translate->locale = 'ru';
                $translate->name = $item->name;
                $translate->save();

            } catch(\Exception $e) {
                echo 'error: TECH CARD CATEGORY ID '.$item->id;
            }

        }

        $items = TechCardStep::all();
        foreach ($items as $item) {

            $check = TechCardStepTranslation::where('tech_card_step_id', $item->id)->first();
            if ($check) continue;

            try {
                $translate = new TechCardStepTranslation;
                $translate->tech_card_step_id = $item->id;
                $translate->locale = 'ru';
                $translate->title = $item->title;
                $translate->description = $item->description;
                $translate->description_warning = $item->description_warning;
                $translate->save();

            } catch(\Exception $e) {
                echo 'error: TECH CARD STEP ID '.$item->id;
                echo PHP_EOL;
                echo $e->getMessage();
                echo PHP_EOL;
            }

        }


    }
}
