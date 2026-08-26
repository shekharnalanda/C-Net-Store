<?php

namespace App\Enums;

enum BusinessType: string
{
    case Retail = 'retail';
    case Grocery = 'grocery';
    case Restaurant = 'restaurant';
    case MultiCategory = 'multi_category';
}

