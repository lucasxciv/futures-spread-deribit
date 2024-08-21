<?php

namespace FuturesSpread\Calculation;

enum StrategyTypeSeriousMoney: string
{
    case Buy = 'Compra';
    case Sell = 'Venda';
    case Wait = 'Aguarde';
    case Skip = 'Pular';
}
