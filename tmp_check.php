<?php

$c = file('app/Filament/Resources/Purchases/Schemas/PurchaseForm.php');
foreach ($c as $i => $l) {
    if (preg_match('/Group::make|Section::make|Repeater::make|->schema\(\[|->components\(\[|->columns\(|->afterStateHydrated|->columnSpan|^\s*\]\),/', $l)) {
        echo ($i + 1).': '.trim($l).PHP_EOL;
    }
}
