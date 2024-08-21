<?php

date_default_timezone_set('America/Sao_Paulo');

require __DIR__.'/../vendor/autoload.php';

use FuturesSpread\Calculation\RsiBtcChartData;
use FuturesSpread\Calculation\RsiData;
use FuturesSpread\Calculation\SpreadData;
use FuturesSpread\Calculation\PerpetualChartData;
use FuturesSpread\Calculation\StrategyTypeSeriousMoney;
use FuturesSpread\Calculation\TableRowSeriousMoney;
use FuturesSpread\Calculation\TableSeriousMoney;
use FuturesSpread\Controller\JsonResponse;
use FuturesSpread\Controller\NotificationController;
use FuturesSpread\Deribit\Instruments;
use FuturesSpread\Deribit\InstrumentsFilter;
use FuturesSpread\Deribit\TradingViewChartData;
use FuturesSpread\Deribit\TradingViewChartDataFilter;
use FuturesSpread\Notification\NotificationMessage;
use FuturesSpread\Notification\NotificationNextStatus;
use FuturesSpread\Notification\NotificationStatus;
use FuturesSpread\View\NumberBrFormat;
use Telegram\Bot\Api;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$aYearAgo = (new DateTimeImmutable())->modify('-1 year');
$tomorrow = (new DateTimeImmutable())->modify('+1 day');

$btcData = new TradingViewChartData(
    new FuturesSpread\Http\HttpRequest(),
    new TradingViewChartDataFilter(
        startTimestamp: $aYearAgo,
        endTimestamp: $tomorrow,
        instrumentName: 'BTC_USDC',
        resolution: 120,
    )
);

$rsiBtcData = new RsiData($btcData);

$paramNotifyRsi = $_GET['notify-rsi'] ?? null;
$configTelegramToken = $_ENV['TELEGRAM_TOKEN'] ?? null;
$configChatId = $_ENV['TELEGRAM_CHAT_ID'] ?? null;

if ($paramNotifyRsi === '1') {
    echo new JsonResponse(
        new NotificationController(
            $status = new NotificationStatus(
                $apiTelegram = new Api($configTelegramToken),
                $configChatId
            ),
            new NotificationNextStatus(
                $status,
                new DateTimeImmutable(),
                $btcData,
                $rsiBtcData,
            ),
            new NotificationMessage(
                $apiTelegram,
                $configChatId
            )
        )
    );
    die;
}

$instruments = new Instruments(
    new \FuturesSpread\Http\HttpRequest(),
    new InstrumentsFilter('BTC', 'future')
);
$selectedInstrument = $_GET['instrument'] ?? $instruments[0];

if (! in_array($selectedInstrument, $instruments->getArrayCopy(), true)) {
    $selectedInstrument = $instruments[0];
}

$futures = new TradingViewChartData(
    new FuturesSpread\Http\HttpRequest(),
    new TradingViewChartDataFilter(
        startTimestamp: $aYearAgo,
        endTimestamp: $tomorrow,
        instrumentName: $selectedInstrument,
        resolution: '1D',
    )
);

$perpetuals = new TradingViewChartData(
    new FuturesSpread\Http\HttpRequest(),
    new TradingViewChartDataFilter(
        startTimestamp: $aYearAgo,
        endTimestamp: $tomorrow,
        instrumentName: 'BTC-PERPETUAL',
        resolution: '1D',
    )
);

$spreadData = new SpreadData($perpetuals, $futures);
$perpetualChartData = new PerpetualChartData($perpetuals, $futures);
$rsiBtcChartData = new RsiBtcChartData($rsiBtcData, $spreadData);

$tableData = new TableSeriousMoney(
    $spreadData,
    $futures,
    $perpetualChartData,
    $rsiBtcChartData
);
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        clifford: '#da373d',
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
        }
    </style>
</head>
</head>
<body class="container mx-auto bg-gray-100 max-w-6xl">
<div class="text-gray-800 px-4 max-w-6xl">
    <main class="p-1 sm:p-10 space-y-6">
        <div class="flex flex-col space-y-6 md:space-y-0 md:flex-row justify-between">
            <div class="mr-6">
                <h1 class="text-4xl font-semibold mb-2">Spread de Futuros</h1>
                <h2 class="text-gray-600 ml-0.5">Gráficos experimentais para aplicar estratégia <b>spread de futuros</b>.</h2>
            </div>
        </div>

        <!-- Telegram -->
        <br />
        <a href="https://t.me/+K9XX3mo_WLA1NGMx" target="_blank" class="mt-8">
            <button type="button" data-twe-ripple-init data-twe-ripple-color="light" class="mb-2 flex rounded bg-[#1da1f2] px-6 py-2.5 text-sm font-medium items-center leading-normal text-white shadow-md transition duration-150 ease-in-out hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-0 active:shadow-lg">
                <span class="me-2 [&>svg]:h-8 [&>svg]:w-8 [&>svg]:fill-[#ffffff]">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                        <path d="M248 8C111 8 0 119 0 256S111 504 248 504 496 393 496 256 385 8 248 8zM363 176.7c-3.7 39.2-19.9 134.4-28.1 178.3-3.5 18.6-10.3 24.8-16.9 25.4-14.4 1.3-25.3-9.5-39.3-18.7-21.8-14.3-34.2-23.2-55.3-37.2-24.5-16.1-8.6-25 5.3-39.5 3.7-3.8 67.1-61.5 68.3-66.7 .2-.7 .3-3.1-1.2-4.4s-3.6-.8-5.1-.5q-3.3 .7-104.6 69.1-14.8 10.2-26.9 9.9c-8.9-.2-25.9-5-38.6-9.1-15.5-5-27.9-7.7-26.8-16.3q.8-6.7 18.5-13.7 108.4-47.2 144.6-62.3c68.9-28.6 83.2-33.6 92.5-33.8 2.1 0 6.6 .5 9.6 2.9a10.5 10.5 0 0 1 3.5 6.7A43.8 43.8 0 0 1 363 176.7z" />
                    </svg>
                </span>
                Notificações de BTC e RSI
            </button>
        </a>

        <form action="/" method="get">
            <section class="grid md:grid-cols-1 xl:grid-cols-1 xl:grid-rows-2 xl:grid-flow-col gap-6">
                <div class="flex flex-col md:row-span-2 bg-white shadow rounded-lg p-4">
                    <label for="instrument" class="block text-sm font-medium leading-6 text-gray-900">Instrumentos futuros</label>
                    <div class="mt-2 flex items-center gap-x-3">
                        <select id="instrument" name="instrument" autocomplete="instrument-name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6">
                            <?php foreach ($instruments as $instrument): ?>
                                <option<?=$selectedInstrument === $instrument ? ' selected' : ''?>><?=$instrument?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Atualizar</button>
                    </div>
                </div>
            </section>
        </form>

        <section class="grid md:grid-cols-1 xl:grid-cols-1 xl:grid-rows-2 xl:grid-flow-col gap-6">
            <div class="flex flex-col md:row-span-2 bg-white shadow rounded-lg">
                <div class="px-6 py-5 font-semibold border-b border-gray-100">BTC/USDC</div>
                <div class="p-1">
                    <canvas id="btcUsdcChart"></canvas>
                </div>
            </div>
        </section>
        <section class="grid md:grid-cols-1 xl:grid-cols-1 xl:grid-rows-2 xl:grid-flow-col gap-6">
            <div class="flex flex-col md:row-span-2 bg-white shadow rounded-lg">
                <div class="px-6 py-5 font-semibold border-b border-gray-100"><?=$selectedInstrument?> x BTC-PERPETUAL</div>
                <div class="p-1">
                    <canvas id="futureChart"></canvas>
                </div>
            </div>
        </section>
        <section class="grid md:grid-cols-1 xl:grid-cols-1 xl:grid-rows-6 xl:grid-flow-col gap-6">
            <div class="flex flex-col md:row-span-6 bg-white shadow rounded-lg">
                <div class="px-6 py-5 font-semibold border-b border-gray-100">Spread (<?=$selectedInstrument?> x BTC-PERPETUAL)</div>
                <div class="p-1">
                    <canvas id="spreadChart"></canvas>
                </div>
            </div>
        </section>
        <section class="grid md:grid-cols-1 xl:grid-cols-1 xl:grid-rows-6 xl:grid-flow-col gap-6">
            <div class="flex flex-col md:row-span-6 bg-white shadow rounded-lg">
                <div class="px-6 py-5 font-semibold border-b border-gray-100">BTC - Índice de Força Relativa (RSI)</div>
                <div class="p-1">
                    <canvas id="rsiBtcChart"></canvas>
                </div>
            </div>
        </section>
        <section class="grid md:grid-cols-1 xl:grid-cols-1 xl:grid-rows-2 xl:grid-flow-col gap-6">
            <div class="flex flex-col md:row-span-2 bg-white shadow rounded-lg relative overflow-x-auto shadow-md">
                <div class="px-6 py-5 font-semibold border-b border-gray-100">Serious Money 2024 - Live 04 - Spread de futuros</div>
                <div class="p-4">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-center">Data</th>
                            <th scope="col" class="px-6 py-3 text-center">Estratégia</th>
                            <th scope="col" class="px-6 py-3 text-center">Contrato</th>
                            <th scope="col" class="px-6 py-3 text-center">Valor Perpétuo</th>
                            <th scope="col" class="px-6 py-3 text-center">Valor Futuro</th>
                            <th scope="col" class="px-6 py-3 text-center">Spread</th>
                            <th scope="col" class="px-6 py-3 text-center">Spread (%)</th>
                            <th scope="col" class="px-6 py-3 text-center">Lucro</th>
                            <th scope="col" class="px-6 py-3 text-center">Lucro (%)</th>
                            <th scope="col" class="px-6 py-3 text-center">Dias</th>
                            <th scope="col" class="px-6 py-3 text-center">% Lucro a.m</th>
                            <th scope="col" class="px-6 py-3 text-center">% Lucro a.a</th>
                            <th scope="col" class="px-6 py-3 text-center">Valorização BTC</th>
                        </thead>
                        <tbody>
                        <?php
                        /**
                         * @var int $index
                         * @var TableRowSeriousMoney $data
                         */
                        foreach ($tableData as $index => $data):
                        ?>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-4 py-2 text-sm text-center"><?=date('d/m/Y', strtotime($data->date))?></td>
                                <td class="px-4 py-2 text-sm text-center">
                                    <?php if ($data->strategy === StrategyTypeSeriousMoney::Buy): ?>
                                        <span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300"><?=$data->strategy->value?></span>
                                    <?php elseif ($data->strategy === StrategyTypeSeriousMoney::Sell): ?>
                                        <span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300"><?=$data->strategy->value?></span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300"><?=$data->strategy->value?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2 text-sm text-center"><?=str_replace('BTC-', '', $selectedInstrument)?></td>
                                <td class="px-4 py-2 text-sm text-right">$<?=new NumberBrFormat($data->perpetual)?></td>
                                <td class="px-4 py-2 text-sm text-right">$<?=new NumberBrFormat($data->future)?></td>
                                <td class="px-4 py-2 text-sm text-right">$<?=new NumberBrFormat($data->spread)?></td>
                                <td class="px-4 py-2 text-sm text-right"><?=new NumberBrFormat($data->spreadPercent)?>%</td>
                                <td class="px-4 py-2 text-sm text-right">$<?=new NumberBrFormat($data->profit)?></td>
                                <td class="px-4 py-2 text-sm text-right"><?=new NumberBrFormat($data->profitPercent)?>%</td>
                                <td class="px-4 py-2 text-sm text-right"><?=$data->days?></td>
                                <td class="px-4 py-2 text-sm text-right"><?=new NumberBrFormat($data->profitMonth)?>%</td>
                                <td class="px-4 py-2 text-sm text-right"><?=new NumberBrFormat($data->profitYear)?>%</td>
                                <td class="px-4 py-2 text-sm text-right"><?=new NumberBrFormat($data->btcDiff)?>%</td>
                            </tr>

                            <?php if (($index + 1) % 2 === 0): ?>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                <td class="px-4 py-2 text-sm font-medium text-center" colspan="13"></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <td class="px-4 py-2 text-sm font-medium text-center" colspan="13"></td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <td class="px-4 py-2 text-sm font-medium text-right" colspan="7">Total:</td>
                            <td class="px-4 py-2 text-sm font-medium text-right">$<?=new NumberBrFormat($data->totalProfit)?></td>
                            <td class="px-4 py-2 text-sm font-medium text-right"><?=new NumberBrFormat($data->totalProfitPercent)?>%</td>
                            <td class="px-4 py-2 text-sm font-medium text-right"><?=$data->totalDays?></td>
                            <td class="px-4 py-2 text-sm font-medium text-right"><?=new NumberBrFormat($data->totalProfitPercentMonth)?>%</td>
                            <td class="px-4 py-2 text-sm font-medium text-right"><?=new NumberBrFormat($data->totalProfitPercentYear)?>%</td>
                            <td class="px-4 py-2 text-sm font-medium text-right"><?=new NumberBrFormat($data->totalBtcDiff)?>%</td>
                        </tr>
                        </tfoot>
                    </table>
                </div class="p-4">
            </div>
        </section>
        <section class="text-right font-semibold text-gray-500">
            <a href="https://github.com/lucasxciv/futures-spread-deribit" target="_blank" class="text-sm leading-5 font-medium text-gray-500 mr-1">
                <i class="fab fa-github h-5 w-5 mr-1"></i>
                <span>GitHub</span>
            </a>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const chartScales = {
        x: {
            display: true,
            ticks: {
                maxRotation: 0,
                callback: function(val, index) {
                    // Hide every 2nd tick label
                    return index % 6 === 0 ? this.getLabelForValue(val) : '';
                },
            }
        },
        y: {
            display: true,
        }
    };

    const ctxBtcUsdc = document.getElementById('btcUsdcChart');

    new Chart(ctxBtcUsdc, {
        type: 'line',
        responsive: true,
        data: {
            labels: <?=json_encode(array_keys($btcData->getArrayCopy()))?>,
            datasets: [
                {
                    label: 'BTC_USDC',
                    data: <?=json_encode(array_values($btcData->getArrayCopy()))?>,
                    pointStyle: false,
                    borderWidth: 2,
                    borderColor: '#a67c00',
                    backgroundColor: '#a67c00'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'nearest',
            },
            scales: chartScales
        }
    });

    const ctxFutures = document.getElementById('futureChart');

    new Chart(ctxFutures, {
        type: 'line',
        responsive: true,
        data: {
            labels: <?=json_encode(array_keys($futures->getArrayCopy()))?>,
            datasets: [
                {
                    label: '<?=$selectedInstrument?>',
                    data: <?=json_encode(array_values($futures->getArrayCopy()))?>,
                    pointStyle: false,
                    borderWidth: 2
                },
                {
                    label: 'BTC-PERPETUAL',
                    data: <?=json_encode(array_values($perpetualChartData->getArrayCopy()))?>,
                    pointStyle: false,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'nearest',
            },
            scales: chartScales
        }
    });

    const ctxSpread = document.getElementById('spreadChart');

    new Chart(ctxSpread, {
        type: 'line',
        responsive: true,
        data: {
            labels: <?=json_encode(array_keys($spreadData->getArrayCopy()))?>,
            datasets: [
                {
                    label: 'Spread',
                    data: <?=json_encode(array_values($spreadData->getArrayCopy()))?>,
                    pointStyle: false,
                    backgroundColor: '#36A2EB',
                    borderColor: '#36A2EB',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'nearest',
            },
            scales: chartScales
        }
    });

    const ctxRsiBtc = document.getElementById('rsiBtcChart');

    new Chart(ctxRsiBtc, {
        type: 'line',
        responsive: true,
        data: {
            labels: <?=json_encode(array_keys($spreadData->getArrayCopy()))?>,
            datasets: [
                {
                    label: 'RSI',
                    data: <?=json_encode(array_values($rsiBtcChartData->getArrayCopy()))?>,
                    pointStyle: false,
                    backgroundColor: '#36A2EB',
                    borderColor: '#36A2EB',
                    borderWidth: 2
                },
                {
                    label: 'RSI Upper Band: 65',
                    data: <?=json_encode(array_fill(0, $rsiBtcChartData->count(), 65))?>,
                    pointStyle: false,
                    borderColor: 'gray',
                    borderWidth: 2
                },
                {
                    label: 'RSI Lower Band: 35',
                    data: <?=json_encode(array_fill(0, $rsiBtcChartData->count(), 35))?>,
                    pointStyle: false,
                    borderColor: 'gray',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'nearest',
            },
            scales: chartScales
        }
    });
</script>

</body>
</html>
