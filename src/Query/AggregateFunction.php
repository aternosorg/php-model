<?php

namespace Aternos\Model\Query;

enum AggregateFunction
{
    case COUNT;
    case SUM;
    case AVERAGE;
    case MIN;
    case MAX;
}