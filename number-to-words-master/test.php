<?php 

    include "autoload.php";
    use NumberToWords\NumberToWords;
    $numberToWords = new NumberToWords;

// build a new number transformer using the RFC 3066 language identifier
    $numberTransformer = $numberToWords->getNumberTransformer('en');
    $currencyTransformer = $numberToWords->getCurrencyTransformer('en');
    echo $currencyTransformer->toWords(2323, 'usd');



?>
